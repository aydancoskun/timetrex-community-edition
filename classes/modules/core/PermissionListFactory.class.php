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
 * @package Core
 */
class PermissionListFactory extends PermissionFactory implements IteratorAggregate {
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
	 * @return bool|PermissionListFactory
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
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PermissionListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id            UUID
	 * @param string $permission_control_id UUID
	 * @param array $where                  Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                  Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PermissionListFactory
	 */
	function getByCompanyIdAndPermissionControlId( $company_id, $permission_control_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $permission_control_id == '' ) {
			return false;
		}

		$ph = [
				'company_id'            => TTUUID::castUUID( $company_id ),
				'permission_control_id' => TTUUID::castUUID( $permission_control_id ),
		];

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id            UUID
	 * @param string $permission_control_id UUID
	 * @param $section
	 * @param $name
	 * @param array $where                  Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                  Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PermissionListFactory
	 */
	function getByCompanyIdAndPermissionControlIdAndSectionAndName( $company_id, $permission_control_id, $section, $name, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $permission_control_id == '' ) {
			return false;
		}

		if ( $section == '' ) {
			return false;
		}

		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id'            => TTUUID::castUUID( $company_id ),
				'permission_control_id' => TTUUID::castUUID( $permission_control_id ),
				'section'               => $section,
				//'name' => $name, //Allow a list of names.
		];

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND a.section = ?
						AND a.name in (' . $this->getListSQL( $name, $ph ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id            UUID
	 * @param string $permission_control_id UUID
	 * @param $section
	 * @param $name
	 * @param $value
	 * @param array $where                  Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                  Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PermissionListFactory
	 */
	function getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_id, $permission_control_id, $section, $name, $value, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $permission_control_id == '' ) {
			return false;
		}

		if ( $section == '' ) {
			return false;
		}

		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id'            => TTUUID::castUUID( $company_id ),
				'permission_control_id' => TTUUID::castUUID( $permission_control_id ),
				'section'               => $section,
				'value'                 => (int)$value,
				//'name' => $name, //Allow a list of names.
		];

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND a.section = ?
						AND a.value = ?
						AND a.name in (' . $this->getListSQL( $name, $ph ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $section
	 * @param int $date          EPOCH
	 * @param array $valid_ids
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PermissionListFactory
	 */
	function getByCompanyIdAndSectionAndDateAndValidIDs( $company_id, $section, $date = null, $valid_ids = [], $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $section == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = 0;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND (
								(
								a.section in (' . $this->getListSQL( $section, $ph ) . ') ';

		//When the Mobile App/TimeClock are doing a reload database, $date should always be 0. That forces the query to just send data for $valid_user_ids.
		//  All other cases it will send data for all current users always, or records that were recently created/updated.
		if ( isset( $date ) && $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$query .= '		AND ( a.created_date >= ? OR a.updated_date >= ? ) ) ';
		} else {
			$query .= ' ) ';
		}

		if ( isset( $valid_ids ) && is_array( $valid_ids ) && count( $valid_ids ) > 0 ) {
			$query .= ' OR a.id in (' . $this->getListSQL( $valid_ids, $ph, 'uuid' ) . ') ';
		}

		$query .= '	)
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return bool|PermissionListFactory
	 */
	function getAllPermissionsByCompanyIdAndUserId( $company_id, $user_id ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
		];

		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();
		$uf = new UserFactory();

		//Make sure when the user is not ACTIVE that we switch to using the terminated permission group instead.
		$query = '
					SELECT pf.*,
						   pcf.level as level
					FROM ' . $this->getTable() . ' AS pf
					LEFT JOIN ' . $pcf->getTable() . ' as pcf ON ( pf.permission_control_id = pcf.id )
					WHERE 
						pf.permission_control_id = (  SELECT 
															( CASE WHEN uf.status_id = 10 THEN puf.permission_control_id ELSE uf.terminated_permission_control_id END ) AS permission_control_id
													  FROM ' . $uf->getTable() . ' AS uf
															   LEFT JOIN ' . $puf->getTable() . ' AS puf ON (uf.id = puf.user_id)
													  WHERE uf.company_id = ? 
															AND uf.id = ? 
															AND uf.deleted = 0
													)
						AND ( pf.deleted = 0 AND pcf.deleted = 0 )								  		
				';

		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

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

		$pcf = new PermissionControlFactory();

		//INCLUDE Deleted rows in this query.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pcf->getTable() . ' as b
					where
							b.company_id = ?
						AND
							( a.created_date >=	 ? OR a.updated_date >= ? OR ( a.deleted = 1 AND a.deleted_date >= ? ) )
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
}

?>
