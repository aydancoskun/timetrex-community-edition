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
 * @package Core
 */
class OtherFieldListFactory extends OtherFieldFactory implements IteratorAggregate {

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
	 * @return bool|OtherFieldListFactory
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
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OtherFieldListFactory
	 */
	function getByCompanyId( $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int[] $type_id
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OtherFieldListFactory
	 */
	function getByCompanyIdAndTypeID( $id, $type_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
				//'type_id' => (int)$type_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OtherFieldListFactory
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
					select	a.*
					from	' . $this->getTable() . ' as a
					where
						a.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param int|int[] $type_id
	 * @param null $key_prefix
	 * @param null $name_prefix
	 * @return array|bool
	 */
	function getByCompanyIdAndTypeIDArray( $id, $type_id, $key_prefix = null, $name_prefix = null ) {
		$oflf = new OtherFieldListFactory();
		$oflf->getByCompanyIdAndTypeID( $id, $type_id, null, null, null, null );
		if ( $oflf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $oflf as $obj ) {
				if ( is_array( $key_prefix ) ) {
					if ( isset( $key_prefix[$obj->getType()] ) ) {
						$prefix = $key_prefix[$obj->getType()];
					} else {
						$prefix = null;
					}
				} else {
					$prefix = $key_prefix;
				}

				if ( is_array( $name_prefix ) ) {
					if ( isset( $name_prefix[$obj->getType()] ) ) {
						$prefix2 = $name_prefix[$obj->getType()];
					} else {
						$prefix2 = null;
					}
				} else {
					$prefix2 = $name_prefix;
				}

				if ( $obj->getOtherID1() != '' ) {
					$retarr[$prefix . 'other_id1'] = $prefix2 . $obj->getOtherID1();
				}
				if ( $obj->getOtherID2() != '' ) {
					$retarr[$prefix . 'other_id2'] = $prefix2 . $obj->getOtherID2();
				}
				if ( $obj->getOtherID3() != '' ) {
					$retarr[$prefix . 'other_id3'] = $prefix2 . $obj->getOtherID3();
				}
				if ( $obj->getOtherID4() != '' ) {
					$retarr[$prefix . 'other_id4'] = $prefix2 . $obj->getOtherID4();
				}
				if ( $obj->getOtherID5() != '' ) {
					$retarr[$prefix . 'other_id5'] = $prefix2 . $obj->getOtherID5();
				}
				if ( $obj->getOtherID6() != '' ) {
					$retarr[$prefix . 'other_id6'] = $prefix2 . $obj->getOtherID6();
				}
				if ( $obj->getOtherID7() != '' ) {
					$retarr[$prefix . 'other_id7'] = $prefix2 . $obj->getOtherID7();
				}
				if ( $obj->getOtherID8() != '' ) {
					$retarr[$prefix . 'other_id8'] = $prefix2 . $obj->getOtherID8();
				}
				if ( $obj->getOtherID9() != '' ) {
					$retarr[$prefix . 'other_id9'] = $prefix2 . $obj->getOtherID9();
				}
				if ( $obj->getOtherID10() != '' ) {
					$retarr[$prefix . 'other_id10'] = $prefix2 . $obj->getOtherID10();
				}
			}

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param int $date          EPOCH
	 * @param array $valid_ids
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|OtherFieldListFactory
	 */
	function getByCompanyIDAndTypeAndDateAndValidIDs( $company_id, $type_id, $date = null, $valid_ids = [], $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
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
				'type_id'    => (int)$type_id,
		];

		//Make sure we return distinct rows so there aren't duplicates.
		$query = '
					select	distinct a.*
					from	' . $this->getTable() . ' as a

					where	a.company_id = ?
						AND a.type_id = ?
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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->SelectLimit( $query, 1, -1, $ph );
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
	 * @return bool|OtherFieldListFactory
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

		$additional_order_fields = [ 'type_id' ];

		$sort_column_aliases = [
				'type' => 'type_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'type_id' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['type_id'] ) ) {
				$order = Misc::prependArray( [ 'type_id' => 'asc' ], $order );
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
					where	a.company_id = ?';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}

?>
