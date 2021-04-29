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
 * @package Modules\Policy
 */
class AccrualPolicyListFactory extends AccrualPolicyFactory implements IteratorAggregate {

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
	 * @return AccrualPolicyListFactory|bool
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
	 * @param string[] $id       UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
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
				//'id' => TTUUID::castUUID($id),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int|int[] $type_id
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByCompanyIdAndTypeId( $id, $type_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByCompanyId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.type_id' => 'asc', 'a.name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$pgf = new PolicyGroupFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];

		$query = '
					select	a.*,
							(
								( select count(*) from ' . $cgmf->getTable() . ' as w, ' . $pgf->getTable() . ' as v where w.company_id = a.company_id AND w.object_type_id = 140 AND w.map_id = a.id AND w.object_id = v.id AND v.deleted = 0)
							) as assigned_policy_groups
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id                        UUID
	 * @param string $accrual_policy_account_id UUID
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByCompanyIdAndAccrualPolicyAccount( $id, $accrual_policy_account_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $accrual_policy_account_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND accrual_policy_account_id in (' . $this->getListSQL( $accrual_policy_account_id, $ph, 'uuid' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByPolicyGroupUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $accrual_policy_account_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'd.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
		];


		$query = '
					select	d.*
					from	' . $pguf->getTable() . ' as a,
							' . $pgf->getTable() . ' as b,
							' . $cgmf->getTable() . ' as c,
							' . $this->getTable() . ' as d
					where	a.policy_group_id = b.id
						AND ( b.id = c.object_id AND b.company_id = c.company_id AND c.object_type_id = 140 )
						AND c.map_id = d.id
						AND a.user_id = ?
						AND d.accrual_policy_account_id in (' . $this->getListSQL( $accrual_policy_account_id, $ph, 'uuid' ) . ')
						AND ( b.deleted = 0 AND d.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id                           UUID
	 * @param string $contributing_shift_policy_id UUID
	 * @param array $where                         Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                         Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByCompanyIdAndContributingShiftPolicyId( $id, $contributing_shift_policy_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $contributing_shift_policy_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND contributing_shift_policy_id in (' . $this->getListSQL( $contributing_shift_policy_id, $ph, 'uuid' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByPolicyGroupUserId( $user_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'd.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	d.*
					from	' . $pguf->getTable() . ' as a,
							' . $pgf->getTable() . ' as b,
							' . $cgmf->getTable() . ' as c,
							' . $this->getTable() . ' as d
					where	a.policy_group_id = b.id
						AND ( b.id = c.object_id AND b.company_id = c.company_id AND c.object_type_id = 140 )
						AND c.map_id = d.id
						AND a.user_id = ?
						AND ( b.deleted = 0 AND d.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualPolicyListFactory|bool
	 */
	function getByPolicyGroupUserIdAndType( $user_id, $type_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'd.type_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
				'type_id' => (int)$type_id,
		];

		$query = '
					select	d.*
					from	' . $pguf->getTable() . ' as a,
							' . $pgf->getTable() . ' as b,
							' . $cgmf->getTable() . ' as c,
							' . $this->getTable() . ' as d
					where	a.policy_group_id = b.id
						AND ( b.id = c.object_id AND b.company_id = c.company_id AND c.object_type_id = 140 )
						AND c.map_id = d.id
						AND a.user_id = ?
						AND d.type_id = ?
						AND ( b.deleted = 0 AND d.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
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

		$list = [];
		foreach ( $lf as $obj ) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getByCompanyIdArray( $company_id, $include_blank = true ) {

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyId( $company_id );

		$list = [];
		if ( $include_blank == true ) {
			$list[0] = TTi18n::gettext( '-- None --' );
		}

		foreach ( $aplf as $ap_obj ) {
			$list[$ap_obj->getID()] = $ap_obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
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
	 * @return AccrualPolicyListFactory|bool
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
		if ( isset( $filter_data['accrual_policy_type_id'] ) ) {
			$filter_data['type_id'] = $filter_data['accrual_policy_type_id'];
		}
		if ( isset( $filter_data['accrual_policy_id'] ) ) {
			$filter_data['id'] = $filter_data['accrual_policy_id'];
		}
		$additional_order_fields = [ 'type_id', 'in_use' ];

		$sort_column_aliases = [
				'type' => 'type_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'type_id' => 'asc', 'name' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by type
			if ( !isset( $order['type_id'] ) ) {
				$order['type_id'] = 'asc';
			}
			//Always sort by name after other columns
			if ( !isset( $order['name'] ) ) {
				$order['name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$pgf = new PolicyGroupFactory();
		$cgmf = new CompanyGenericMapFactory();
		$otpf = new OverTimePolicyFactory();
		$ppf = new PremiumPolicyFactory();
		$apf = new AbsencePolicyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];
		/*
										( select count(*) from '. $cgmf->getTable() .' as w, '. $pgf->getTable() .' as v where w.company_id = a.company_id AND w.object_type_id = 140 AND w.map_id = a.id AND w.object_id = v.id AND v.deleted = 0)+
										( select count(*) from '. $otpf->getTable() .' as x where x.accrual_policy_id = a.id and x.deleted = 0)+
										( select count(*) from '. $ppf->getTable() .' as y where y.accrual_policy_id = a.id and y.deleted = 0)+
										( select count(*) from '. $apf->getTable() .' as z where z.accrual_policy_id = a.id and z.deleted = 0)
		*/
		$query = '
					select	
							_ADODB_COUNT
							a.*,
							(
								CASE WHEN EXISTS
									( select 1 from ' . $cgmf->getTable() . ' as w, ' . $pgf->getTable() . ' as v where w.company_id = a.company_id AND w.object_type_id = 140 AND w.map_id = a.id AND w.object_id = v.id AND v.deleted = 0)
								THEN 1
								ELSE
									CASE WHEN EXISTS
										( select 1 from ' . $otpf->getTable() . ' as x where x.accrual_policy_id = a.id and x.deleted = 0)
									THEN 1
									ELSE
										CASE WHEN EXISTS
											( select 1 from ' . $ppf->getTable() . ' as y where y.accrual_policy_id = a.id and y.deleted = 0)
										THEN 1
										ELSE
											CASE WHEN EXISTS
												( select 1 from ' . $apf->getTable() . ' as z where z.accrual_policy_id = a.id and z.deleted = 0)
											THEN 1
											ELSE 0
											END
										END
									END
								END
							) as in_use,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							_ADODB_COUNT
					from	' . $this->getTable() . ' as a

						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['description'] ) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['length_of_service_contributing_pay_code_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.length_of_service_contributing_pay_code_policy_id', $filter_data['length_of_service_contributing_pay_code_policy_id'], 'uuid_list', $ph ) : null;

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
