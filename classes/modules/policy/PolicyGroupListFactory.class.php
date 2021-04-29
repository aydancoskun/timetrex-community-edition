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
 * @package Modules\Policy
 */
class PolicyGroupListFactory extends PolicyGroupFactory implements IteratorAggregate {

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
	 * @return bool|PolicyGroupListFactory
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
	 * @return bool|PolicyGroupListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $ids  UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PolicyGroupListFactory
	 */
	function getByUserIds( $ids, $where = null, $order = null ) {
		if ( $ids == '' ) {
			return false;
		}
		/*
				if ( $order == NULL ) {
					$order = array( 'type_id' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}
		*/
		$pguf = new PolicyGroupUserFactory();

		$ph = [];

		$query = '
					select	a.*,
							b.user_id as user_id
					from	' . $this->getTable() . ' as a,
							' . $pguf->getTable() . ' as b
					where	a.id = b.policy_group_id
						AND b.user_id in  (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_ids   UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PolicyGroupListFactory
	 */
	function getByCompanyIdAndUserId( $company_id, $user_ids, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_ids == '' ) {
			return false;
		}
		/*
				if ( $order == NULL ) {
					$order = array( 'type_id' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}
		*/
		$pguf = new PolicyGroupUserFactory();

		$ph = [ 'company_id' => TTUUID::castUUID( $company_id ), ];

		$query = '
					select	a.*,
							b.user_id as user_id
					from	' . $this->getTable() . ' as a,
							' . $pguf->getTable() . ' as b
					where	a.id = b.policy_group_id
						AND a.company_id = ? ';

		if ( $user_ids && is_array( $user_ids ) && isset( $user_ids[0] ) ) {
			$query .= ' AND b.user_id in (' . $this->getListSQL( $user_ids, $ph, 'uuid' ) . ') ';
		}

		$query .= '	AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PolicyGroupListFactory
	 */
	function getByCompanyId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getByCompanyIdArray( $company_id, $include_blank = true ) {

		$pglf = new PolicyGroupListFactory();
		$pglf->getByCompanyId( $company_id );


		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $pglf as $pg_obj ) {
			$list[$pg_obj->getID()] = $pg_obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param $lf
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf, $include_blank = true ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $lf as $obj ) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param $lf
	 * @return array|bool
	 */
	function getUserToPolicyGroupMapArrayByListFactory( $lf ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		$retarr = [];
		foreach ( $lf as $obj ) {
			$retarr[$obj->getColumn( 'user_id' )] = $obj->getId();
		}

		if ( empty( $retarr ) == false ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PolicyGroupListFactory
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

		if ( isset( $filter_data['user'] ) ) {
			$filter_data['user_id'] = $filter_data['user'];
		}

		$additional_order_fields = [];

		$sort_column_aliases = [
				'total_users' => false, //Don't sort
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'name' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset( $order['name'] ) ) {
				$order['name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['exception_policy_control_id'] ) ) {
			$filter_data['exception_policy_control'] = $filter_data['exception_policy_control_id'];
		}
		if ( isset( $filter_data['round_interval_policy_id'] ) ) {
			$filter_data['round_interval_policy'] = $filter_data['round_interval_policy_id'];
		}
		if ( isset( $filter_data['over_time_policy_id'] ) ) {
			$filter_data['over_time_policy'] = $filter_data['over_time_policy_id'];
		}
		if ( isset( $filter_data['premium_policy_id'] ) ) {
			$filter_data['premium_policy'] = $filter_data['premium_policy_id'];
		}
		if ( isset( $filter_data['accrual_policy_id'] ) ) {
			$filter_data['accrual_policy'] = $filter_data['accrual_policy_id'];
		}

		$uf = new UserFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//Count total users in PolicyGroup factory, so we can disable it when needed. That way it doesn't slow down Policy Group dropdown boxes.
		//(select count(*) from '. $pguf->getTable() .' as pguf_tmp where pguf_tmp.policy_group_id = a.id ) as total_users,
		$query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ? ';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		//Optmize query using subselects rather than LEFT JOIN as that results in hundreds of thousands of records being returned and having to use DISTINCT.
		$query .= ( isset( $filter_data['user_id'] ) ) ? ' AND ( a.id in ( SELECT policy_group_id FROM ' . $pguf->getTable() . ' as b WHERE a.id = b.policy_group_id ' . $this->getWhereClauseSQL( 'b.user_id', $filter_data['user_id'], 'uuid_list', $ph ) . ' ) ) ' : null;

		$query .= ( isset( $filter_data['exception_policy_control'] ) ) ? $this->getWhereClauseSQL( 'a.exception_policy_control_id', $filter_data['exception_policy_control'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['over_time_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as d WHERE a.id = d.object_id AND d.company_id = a.company_id AND d.object_type_id = 110 ' . $this->getWhereClauseSQL( 'd.map_id', $filter_data['over_time_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['holiday_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as g WHERE a.id = g.object_id AND g.company_id = a.company_id AND g.object_type_id = 180 ' . $this->getWhereClauseSQL( 'g.map_id', $filter_data['holiday_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['round_interval_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as c WHERE a.id = c.object_id AND c.company_id = a.company_id AND c.object_type_id = 130 ' . $this->getWhereClauseSQL( 'c.map_id', $filter_data['round_interval_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['premium_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as e WHERE a.id = e.object_id AND e.company_id = a.company_id AND e.object_type_id = 120 ' . $this->getWhereClauseSQL( 'e.map_id', $filter_data['premium_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['accrual_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as f WHERE a.id = f.object_id AND f.company_id = a.company_id AND f.object_type_id = 140 ' . $this->getWhereClauseSQL( 'f.map_id', $filter_data['accrual_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['absence_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as h WHERE a.id = h.object_id AND h.company_id = a.company_id AND h.object_type_id = 170 ' . $this->getWhereClauseSQL( 'h.map_id', $filter_data['absence_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['expense_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as i WHERE a.id = i.object_id AND i.company_id = a.company_id AND i.object_type_id = 200 ' . $this->getWhereClauseSQL( 'i.map_id', $filter_data['expense_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['regular_time_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as j WHERE a.id = j.object_id AND j.company_id = a.company_id AND j.object_type_id = 100 ' . $this->getWhereClauseSQL( 'j.map_id', $filter_data['regular_time_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['break_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as k WHERE a.id = k.object_id AND k.company_id = a.company_id AND k.object_type_id = 160 ' . $this->getWhereClauseSQL( 'k.map_id', $filter_data['break_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;
		$query .= ( isset( $filter_data['meal_policy'] ) ) ? ' AND ( a.id in ( SELECT object_id FROM ' . $cgmf->getTable() . ' as l WHERE a.id = l.object_id AND l.company_id = a.company_id AND l.object_type_id = 150 ' . $this->getWhereClauseSQL( 'l.map_id', $filter_data['meal_policy'], 'uuid_list', $ph ) . ' ) ) ' : null;

		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= '
						 AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

}

?>
