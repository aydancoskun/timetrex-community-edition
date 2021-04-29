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
 * @package Modules\Request
 */
class RequestListFactory extends RequestFactory implements IteratorAggregate {

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
	 * @return bool|RequestListFactory
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
	 * @return bool|RequestListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							a.date_stamp as date_stamp
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where a.user_id = c.id
						AND a.id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByCompanyId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $uf->getTable() . ' as uf ON a.user_id = uf.id
					where	uf.company_id = ?
						AND ( a.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByUserIdAndCompanyId( $user_id, $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.status_id' => 'asc', 'a.date_stamp' => 'desc', 'a.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	a.*,
							a.date_stamp as date_stamp
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where a.user_id = c.id
						AND c.company_id = ?
						AND a.user_id = ?
						AND ( a.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByUserIdAndCompanyIdAndStartDateAndEndDate( $user_id, $company_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.status_id' => 'asc', 'a.date_stamp' => 'desc', 'a.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select	a.*,
							a.date_stamp as date_stamp
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND ( a.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $status_id
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByCompanyIdAndUserIdAndStatusAndStartDateAndEndDate( $company_id, $user_id, $status_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
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

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
				'status_id'  => (int)$status_id,
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select	a.*,
							a.date_stamp as date_stamp
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND a.user_id = ?
						AND a.status_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND ( a.deleted = 0 AND c.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $ids  UUID
	 * @param $status
	 * @param $level
	 * @param $max_level
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByUserIdListAndStatusAndLevelAndMaxLevelAndNotAuthorized( $ids, $status, $level, $max_level, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $ids == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}


		if ( $level == '' ) {
			return false;
		}

		if ( $max_level == '' ) {
			return false;
		}

		$additional_sort_fields = [ 'date_stamp', 'user_id' ];

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.user_id' => 'asc', 'a.date_stamp' => 'asc' ];
			$strict_order = false;
		}

		$ph = [
				'status'    => $status,
				'level'     => $level,
				'max_level' => $max_level,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.status_id = ?
						AND a.authorized = 0
						AND ( a.authorization_level = ? OR a.authorization_level > ? )
						AND a.user_id in (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param $hierarchy_level_map
	 * @param $status
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByHierarchyLevelMapAndStatusAndNotAuthorized( $hierarchy_level_map, $status, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $hierarchy_level_map == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		$additional_sort_fields = [ 'date_stamp', 'user_id' ];

		$sort_column_aliases = [
				'date_stamp' => 'date_stamp',
				'user_id'    => 'c.last_name',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.type_id' => 'asc', 'a.date_stamp' => 'desc', 'c.last_name' => 'asc' ];
			$strict_order = false;
		}

		$uf = new UserFactory();
		$huf = new HierarchyUserFactory();

		$ph = [
				'status' => $status,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c,
							' . $huf->getTable() . ' as z
					where	a.user_id = z.user_id
						AND a.user_id = c.id
						AND	a.status_id = ?
						AND a.authorized = 0
						AND ( ' . HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $hierarchy_level_map ) . ' )
						AND ( a.deleted = 0 AND c.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param $hierarchy_level_map
	 * @param int $type_id
	 * @param $status
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized( $hierarchy_level_map, $type_id, $status, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $hierarchy_level_map == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		$additional_sort_fields = [ 'date_stamp', 'user_id' ];

		$sort_column_aliases = [
				'date_stamp' => 'date_stamp',
				'user_id'    => 'c.last_name',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'desc', 'c.last_name' => 'asc' ];
			$strict_order = false;
		}

		$uf = new UserFactory();
		$huf = new HierarchyUserFactory();

		$ph = [
				'status'  => $status,
				'type_id' => (int)$type_id,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c,
							' . $huf->getTable() . ' as z
					where	a.user_id = z.user_id
						AND a.user_id = c.id
						AND	a.status_id = ?
						AND	a.type_id = ?
						AND a.authorized = 0
						AND ( ' . HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $hierarchy_level_map ) . ' )
						AND ( a.deleted = 0 AND c.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $pay_period_id UUID
	 * @param $status
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getSumByPayPeriodIdAndStatus( $pay_period_id, $status, $where = null, $order = null ) {
		$ph = [
				'status_id' => $status,
		];

		$query = '
					select	a.pay_period_id as pay_period_id, count(*) as total
					from	' . $this->getTable() . ' as a
					where	a.status_id = ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					GROUP By a.pay_period_id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param $status
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getSumByCompanyIDAndPayPeriodIdAndStatus( $company_id, $pay_period_id, $status, $where = null, $order = null ) {
		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'status_id'  => $status,
		];

		$query = '
					select	a.pay_period_id as pay_period_id, count(*) as total
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND	a.status_id = ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					GROUP By a.pay_period_id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $pay_period_id UUID
	 * @param $status
	 * @param int $before_date      EPOCH
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return int
	 */
	function getSumByPayPeriodIdAndStatusAndBeforeDate( $pay_period_id, $status, $before_date, $where = null, $order = null ) {

		$ph = [
				'pay_period_id' => TTUUID::castUUID( $pay_period_id ),
				'status_id'     => $status,
				'before_date'   => $this->db->BindDate( $before_date ),
		];

		$query = '
					select	count(*)
					from	' . $this->getTable() . ' as a
					where	a.pay_period_id = ?
						AND	a.status_id = ?
						AND a.date_stamp <= ?
						AND ( a.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//$this->rs = $this->db->PageExecute($query, $limit, $page);

		$total = $this->db->GetOne( $query, $ph );

		if ( $total === false ) {
			$total = 0;
		}
		Debug::text( 'Total: ' . $total, __FILE__, __LINE__, __METHOD__, 10 );

		return $total;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RequestListFactory
	 */
	function getByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.status_id' => 'asc', 'a.date_stamp' => 'desc', 'a.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							a.date_stamp as date_stamp
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ? ';
		if ( isset( $filter_data['permission_children_ids'] ) && isset( $filter_data['permission_children_ids'][0] ) && !in_array( TTUUID::getNotExistID(), (array)$filter_data['permission_children_ids'] ) ) {
			$query .= ' AND a.user_id in (' . $this->getListSQL( $filter_data['permission_children_ids'], $ph ) . ') ';
		}
		if ( isset( $filter_data['user_id'] ) && isset( $filter_data['user_id'][0] ) && !in_array( TTUUID::getNotExistID(), (array)$filter_data['user_id'] ) ) {
			$query .= ' AND a.user_id in (' . $this->getListSQL( $filter_data['user_id'], $ph ) . ') ';
		}
		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( $filter_data['start_date'] );
			$query .= ' AND a.date_stamp >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( $filter_data['end_date'] );
			$query .= ' AND a.date_stamp <= ?';
		}
		$query .= '		AND ( a.deleted = 0 AND c.deleted = 0 ) ';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

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
	 * @return bool|RequestListFactory
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

		$additional_order_fields = [ 'date_stamp', 'user_status_id', 'last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title' ];

		$sort_column_aliases = [
				'status' => 'status_id',
				'type'   => 'type_id',
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			//Sort by date_stamp ASC first, so most recent requests always appear at the top, then by type to try to keep similar requests together.
			//However when no pending requests exist, the most recent request is at the end of the list. Unless we can do conditional sorting,
			//always show most recent date at the top, even though its not ideal for pending requests.
			$order = [ 'status_id' => 'asc', 'date_stamp' => 'desc', 'type_id' => 'asc', 'last_name' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset( $order['date_stamp'] ) ) {
				$order['date_stamp'] = 'desc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$huf = new HierarchyUserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//Need to make this return DISTINCT records only, because if the same child is assigned to multiple hierarchies,
		//the join to table HUF will force it to return one row for each hierarchy they are a child of. This prevents that.
		$query = '
					select	_ADODB_COUNT
							DISTINCT
							a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							a.date_stamp as date_stamp,
							a.user_id as user_id,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as user_group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name

							_ADODB_COUNT
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN ' . $huf->getTable() . ' as huf ON ( a.user_id = huf.user_id )

						LEFT JOIN ' . $bf->getTable() . ' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as f ON ( b.title_id = f.id AND f.deleted = 0 )

						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'b.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['country'] ) ) ? $this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['province'] ) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : null;

		$query .= ( isset( $filter_data['authorized'] ) ) ? $this->getWhereClauseSQL( 'a.authorized', $filter_data['authorized'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['hierarchy_level_map'] ) && is_array( $filter_data['hierarchy_level_map'] ) ) {
			$query .= ' AND  huf.id IS NOT NULL '; //Make sure the user maps to a hierarchy.
			//$query	.= ' AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $filter_data['hierarchy_level_map'], 'a.', 'huf.', 'a.type_id' ) .' )';
			$hierarchy_level_sql = HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $filter_data['hierarchy_level_map'], 'a.', 'huf.', 'a.type_id' );
			if ( $hierarchy_level_sql != '' ) {
				$query .= ' AND ( ' . $hierarchy_level_sql . ' )';
			}
		} else if ( isset( $filter_data['hierarchy_level_map'] ) && $filter_data['hierarchy_level_map'] == false ) {
			//If hierarchy_level_map is not an array, don't return any requests.
			//$query	.= ' AND  huf.id = -1 '; //Make sure the user maps to a hierarchy.
			$query .= ' AND  huf.id = \'' . TTUUID::getNotExistID() . '\''; //Make sure the user maps to a hierarchy.
		}

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( (int)TTDate::parseDateTime( $filter_data['start_date'] ) );
			$query .= ' AND a.date_stamp >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( (int)TTDate::parseDateTime( $filter_data['end_date'] ) );
			$query .= ' AND a.date_stamp <= ?';
		}

		$query .= ( isset( $filter_data['created_date_start'] ) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date_start'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['created_date_end'] ) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date_end'], 'end_date', $ph ) : null;

		$query .= ( isset( $filter_data['updated_date_start'] ) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date_start'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['updated_date_end'] ) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date_end'], 'end_date', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= '
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>
