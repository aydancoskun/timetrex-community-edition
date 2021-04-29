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
 * @package Modules\Department
 */
class DepartmentListFactory extends DepartmentFactory implements IteratorAggregate {

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
	 * @return bool|DepartmentListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$this->rs = $this->getCache( $id );
		if ( $this->rs === false ) {
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

			$this->saveCache( $this->rs, $id );
		}

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByCompanyId( $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
					AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $status_id
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByCompanyIdAndStatus( $company_id, $status_id, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}
		if ( $status_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'status_id'  => (int)$status_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	status_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $order = null ) {
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
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByManualIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'id'         => (int)$this->Validator->stripNon32bitInteger( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	manual_id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $status_id
	 * @param int $date          EPOCH
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByCompanyIDAndStatusAndDate( $company_id, $status_id, $date = null, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = 0;
		}

		if ( $order == null ) {
			$order = [ 'a.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'status_id'  => (int)$status_id,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.status_id = ?
					';

		if ( isset( $date ) && $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = $date;
			$ph[] = $date;
			$query .= ' AND ( a.created_date >= ? OR a.updated_date >= ? )';
		}

		$query .= ' AND ( a.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $status_id
	 * @param int $date          EPOCH
	 * @param array $valid_ids
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getByCompanyIDAndStatusAndDateAndValidIDs( $company_id, $status_id, $date = null, $valid_ids = [], $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = 0;
		}

		if ( $order == null ) {
			$order = [ 'a.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'status_id'  => (int)$status_id,
		];

		//Make sure we return distinct rows so there aren't duplicates.
		$query = '
					select	distinct a.*
					from	' . $this->getTable() . ' as a

					where	a.company_id = ?
						AND a.status_id = ?
						AND (
								1=1
							';

		if ( isset( $date ) && $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$query .= '		AND ( a.created_date >= ? OR a.updated_date >= ? ) ';
		}

		if ( isset( $valid_ids ) && is_array( $valid_ids ) && count( $valid_ids ) > 0 ) {
			$query .= ' OR a.id in (' . $this->getListSQL( $valid_ids, $ph, 'uuid' ) . ') ';
		}

		$query .= '	)
					AND ( a.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
	 */
	function getHighestManualIDByCompanyId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id'  => TTUUID::castUUID( $id ),
				'id2' => $id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND id = ( select id
									from ' . $this->getTable() . '
									where company_id = ?
										AND manual_id IS NOT NULL
										AND deleted = 0
									ORDER BY manual_id DESC
									LIMIT 1
									)
						AND deleted = 0
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $include_blank
	 * @param bool $include_disabled
	 * @return array|bool
	 */
	function getByCompanyIdArray( $company_id, $include_blank = true, $include_disabled = true ) {

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $company_id );

		return $dlf->getArrayByListFactory( $dlf, $include_blank, $include_disabled );
	}

	/**
	 * @param $lf
	 * @param bool $include_blank
	 * @param bool $include_disabled
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf, $include_blank = true, $include_disabled = true ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $lf as $obj ) {
			if ( $obj->getStatus() == 20 ) {
				$status = '(DISABLED) ';
			} else {
				$status = null;
			}

			if ( $include_disabled == true || ( $include_disabled == false && $obj->getStatus() == 10 ) ) {
				$list[$obj->getID()] = $status . $obj->getName();
			}
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $date          EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getIsModifiedByCompanyIdAndDate( $company_id, $date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		$ph = [
				'company_id'   => TTUUID::castUUID( $company_id ),
				'created_date' => $date,
				'updated_date' => $date,
				'deleted_date' => $date,
		];

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	' . $this->getTable() . '
					where
							company_id = ?
						AND
							( created_date >= ? OR updated_date >= ? OR ( deleted = 1 AND deleted_date >= ? ) )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text( 'Rows have been modified: ' . $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}
		Debug::text( 'Rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|DepartmentListFactory
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

		$additional_order_fields = [ 'status_id' ];

		$sort_column_aliases = [
				'status' => 'status_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'name' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['status_id'] ) ) {
				$order = Misc::prependArray( [ 'status_id' => 'asc' ], $order );
			}
			//Always sort by last name, first name after other columns
			if ( !isset( $order['name'] ) ) {
				$order['name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

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
					where	a.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text_metaphone', $ph ) : null;
		$query .= ( isset( $filter_data['manual_id'] ) ) ? $this->getWhereClauseSQL( 'a.manual_id', $this->Validator->stripNon32bitInteger( $filter_data['manual_id'] ), 'numeric', $ph ) : null;

		$query .= ( isset( $filter_data['tag'] ) ) ? $this->getWhereClauseSQL( 'a.id', [ 'company_id' => TTUUID::castUUID( $company_id ), 'object_type_id' => 120, 'tag' => $filter_data['tag'] ], 'tag', $ph ) : null;

		$query .= ( isset( $filter_data['created_date'] ) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['updated_date'] ) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date'], 'date_range', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= '
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>
