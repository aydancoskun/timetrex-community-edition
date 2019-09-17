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
 * @package Modules\Hierarchy
 */
class HierarchyLevelListFactory extends HierarchyLevelFactory implements IteratorAggregate {

	/**
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					where	deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getById( $id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByCompanyId( $company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$hcf = new HierarchyControlFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id)
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $hcf->getTable() .' as b ON a.hierarchy_control_id = b.id
					where	 b.company_id = ?
						AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$hcf = new HierarchyControlFactory();

		$ph = array(
					'id' => TTUUID::castUUID($id),
					'company_id' => TTUUID::castUUID($company_id)
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $hcf->getTable() .' as b ON a.hierarchy_control_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByHierarchyControlId( $id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$query = '
					select	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $user_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByHierarchyControlIdAndUserId( $id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = ?
						AND user_id = ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $user_id UUID
	 * @param string $exclude_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByHierarchyControlIdAndUserIdAndExcludeID( $id, $user_id, $exclude_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					'user_id' => TTUUID::castUUID($user_id),
					'exclude_id' => TTUUID::castUUID($exclude_id)
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = ?
						AND user_id = ?
						AND id != ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $user_id UUID
	 * @return bool
	 */
	function getLevelsByHierarchyControlIdAndUserId( $id, $user_id ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					'idb' => $id,
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	distinct(level)
					from	'. $this->getTable() .'
					where	hierarchy_control_id = ?
						AND level >= (
										select	level
										from	'. $this->getTable() .'
										where	hierarchy_control_id = ?
											AND user_id = ?
											AND deleted = 0
										LIMIT 1
									)
						AND deleted = 0
					ORDER BY level ASC
				';

		$retarr = $this->db->GetCol($query, $ph);

		return $retarr;

	}

	/**
	 * @param string $user_id UUID
	 * @param int $object_type_id
	 * @return bool|HierarchyLevelListFactory
	 */
	function getByUserIdAndObjectTypeID( $user_id, $object_type_id = 50 ) { //50 = Requests

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$hotf = new HierarchyObjectTypeFactory();

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
				select	a.*
				from	'. $this->getTable() .' as a
					LEFT JOIN '. $hotf->getTable() .' as b ON a.hierarchy_control_id = b.hierarchy_control_id
				where a.user_id = ?
					AND b.object_type_id in ('. $this->getListSQL( $object_type_id, $ph, 'int' ) .')
					AND ( a.deleted = 0 )
				';

		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getObjectTypeAndHierarchyAppendedListByCompanyIDAndUserID( $company_id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$additional_order_fields = array( 'object_type_id', 'hierarchy_control_name' );
		if ( $order == NULL ) {
			$order = array( 'object_type_id' => 'asc', 'hierarchy_control_name' => 'asc', 'level' => 'asc', 'user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$hcf = new HierarchyControlFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	hlf.*,
							hcf.name as hierarchy_control_name,
							hotf.object_type_id
					from '. $this->getTable() .' as hlf
					LEFT JOIN '. $hcf->getTable() .' as hcf ON hcf.id = hlf.hierarchy_control_id
					LEFT JOIN '. $hotf->getTable() .' as hotf ON hcf.id = hotf.hierarchy_control_id
					LEFT JOIN '. $huf->getTable() .' as huf ON hcf.id = huf.hierarchy_control_id
					where	hcf.company_id = ?
							AND huf.user_id = ?
							AND ( hlf.deleted = 0 AND hcf.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $object_type_id
	 * @return array|bool
	 */
	function getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $user_id, $object_type_id = 50 ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					);

		//Include object_type_ids for each hierarchy_control_id, because we need to do additional filtering by hierarchy_control_id, level, object_type_ids
		$query = '
				select
						x.hierarchy_control_id as hierarchy_control_id,
						x.level as level,
						z.object_type_id as object_type_id
				from	'. $this->getTable() .' as x,
						'. $hcf->getTable() .' as y,
					(
								select	a.hierarchy_control_id, a.level, b.object_type_id
								from	'. $this->getTable() .' as a
									LEFT JOIN '. $hotf->getTable() .' as b ON a.hierarchy_control_id = b.hierarchy_control_id
								where a.user_id = ?
									AND b.object_type_id in ('. $this->getListSQL( $object_type_id, $ph, 'int' ) .')
									AND a.deleted = 0
					) as z
				where
					x.hierarchy_control_id = y.id
					AND x.hierarchy_control_id = z.hierarchy_control_id
					AND x.level >= z.level
					AND ( x.deleted = 0 AND y.deleted = 0 )
				ORDER BY x.level asc
				';

		$rs = $this->ExecuteSQL($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__, 10);

		$hierarchy_to_level_map = array();
		$hierarchy_to_object_type_map = array();
		if ( $rs->RecordCount() > 0 ) {
			foreach( $rs as $row ) {
				$hierarchy_to_level_map[$row['hierarchy_control_id']][] = (int)$row['level'];
				$hierarchy_to_object_type_map[$row['hierarchy_control_id']][] = (int)$row['object_type_id'];
			}
			//Debug::Arr($hierarchy_to_level_map, ' Hierarchy To Level Map: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($hierarchy_to_object_type_map, ' Hierarchy To Object Type Map: ', __FILE__, __LINE__, __METHOD__, 10);

			//Take each hierarchy_control and level element and convert it into virtual levels, where the first level (regardless of what it is in the actual hierarchy)
			//is always virtual_level 1, so the supervisor can see all necessary requests that are waiting on them at level 1. Dropping down any other levels
			//is looking and requests waiting on OTHER supervisors.
			//Track the last level for each hierarchy, so we know when to include all requests that may be higher than that level, so if the hierarchy is changed
			//and levels are taken out, requests don't sit in limbo forever.
			$retarr = array();
			foreach( $hierarchy_to_level_map as $hierarchy_control_id => $level_arr ) {
				//Unique each level arr so we don't start creating extra virtual levels when multiple superiors are at the same level.
				//This fixes a bug where if there were 5 superiors at the same level, 5 virtual levels would be created.
				$level_arr = array_unique($level_arr);

				$i = 1;
				foreach( $level_arr as $level ) {
					if ( $level == end($hierarchy_to_level_map[$hierarchy_control_id]) ) {
						$last_level = TRUE;
					} else {
						$last_level = FALSE;
					}

					$retarr[$i][] = array('hierarchy_control_id' => TTUUID::castUUID($hierarchy_control_id), 'level' => $level, 'last_level' => $last_level, 'object_type_id' => array_unique( $hierarchy_to_object_type_map[$hierarchy_control_id] ) );

					$i++;
				}
			}

			//Debug::Arr($retarr, ' Final Hierarchy To Level Map: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		return FALSE;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|HierarchyLevelListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array();

		$sort_column_aliases = array();

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'level' => 'asc');
			$strict = FALSE;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset($order['level']) ) {
				$order['level'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$hcf = new HierarchyControlFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $hcf->getTable() .' as b ON ( a.hierarchy_control_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['hierarchy_control_id']) ) ? $this->getWhereClauseSQL( 'a.hierarchy_control_id', $filter_data['hierarchy_control_id'], 'uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}
?>
