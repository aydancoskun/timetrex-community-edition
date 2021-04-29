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
 * @package Modules\Users
 */
class UserDeductionListFactory extends UserDeductionFactory implements IteratorAggregate {

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
					WHERE deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( is_array( $id ) ) {
			$this->rs = false;
		} else {
			$this->rs = $this->getCache( $id );
		}

		if ( $this->rs === false ) {
			$ph = [];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where	id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			if ( !is_array( $id ) ) {
				$this->saveCache( $this->rs, $id );
			}
		}

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $order = null ) {
		return $this->getByCompanyIdAndId( $company_id, $id, $order );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyIdAndId( $company_id, $id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.id = ?
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $deduction_id UUID
	 * @param array $where         Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order         Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyDeductionId( $deduction_id, $where = null, $order = null ) {
		if ( $deduction_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'deduction_id' => TTUUID::castUUID( $deduction_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where
						a.user_id = b.id
						AND a.company_deduction_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b,
							' . $cdf->getTable() . ' as c
					where
						a.user_id = b.id
						AND a.company_deduction_id = c.id
						AND b.company_id = ?
						AND a.deleted = 0
					ORDER BY c.calculation_order
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id   UUID
	 * @param string $deduction_id UUID
	 * @param array $where         Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order         Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyIdAndCompanyDeductionId( $company_id, $deduction_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $deduction_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.company_deduction_id in (' . $this->getListSQL( $deduction_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id      UUID
	 * @param string $deduction_id UUID
	 * @param array $where         Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order         Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByUserIdAndCompanyDeductionId( $user_id, $deduction_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $deduction_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'deduction_id' => TTUUID::castUUID( $deduction_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where
						a.user_id = b.id
						AND a.company_deduction_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND (a.deleted = 0 AND b.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id      UUID
	 * @param $calculation_id
	 * @param string $country_code UUID
	 * @param array $where         Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order         Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 * @throws DBError
	 */
	function getByUserIdAndCalculationIdAndCountryID( $user_id, $calculation_id, $country_code, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $calculation_id == '' ) {
			return false;
		}

		if ( $country_code == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'b.status_id' => 'asc', 'b.calculation_id' => 'asc', 'b.calculation_order' => 'asc', 'b.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$cdf = new CompanyDeductionFactory();

		$ph = [
				'user_id'        => TTUUID::castUUID( $user_id ),
				'calculation_id' => (int)$calculation_id,
				'country_code'   => (string)$country_code,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $cdf->getTable() . ' as b
					where
						a.company_deduction_id = b.id
						AND a.user_id = ?
						AND b.calculation_id = ?
						AND b.country = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id        UUID
	 * @param string $pse_account_id UUID
	 * @param array $where           Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order           Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByUserIdAndPayStubEntryAccountID( $user_id, $pse_account_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $pse_account_id == '' ) {
			return false;
		}

		//$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $cdf->getTable() . ' as b
					where
						a.company_deduction_id = b.id
						AND a.user_id = ?
						AND b.pay_stub_entry_account_id in (' . $this->getListSQL( $pse_account_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyIdAndUserId( $company_id, $user_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'cdf.status_id' => 'asc', 'cdf.calculation_order' => 'asc', 'cdf.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					SELECT	a.id,
							a.user_id,
							a.company_deduction_id,

							CASE WHEN a.length_of_service_date IS NULL THEN uf.hire_date ELSE a.length_of_service_date END as length_of_service_date,
							CASE WHEN a.start_date IS NULL THEN cdf.start_date ELSE a.start_date END as start_date,
							CASE WHEN a.end_date IS NULL THEN cdf.end_date ELSE a.end_date END as end_date,

							a.user_value1,
							a.user_value2,
							a.user_value3,
							a.user_value4,
							a.user_value5,
							a.user_value6,
							a.user_value7,
							a.user_value8,
							a.user_value9,
							a.user_value10,

							a.created_date as created_date,
							a.created_by as created_by,
							a.updated_date as updated_date,
							a.updated_by as updated_by,
							a.deleted_date as deleted_date,
							a.deleted_by as deleted_by,
							a.deleted as deleted
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					LEFT JOIN ' . $cdf->getTable() . ' as cdf ON ( a.company_deduction_id = cdf.id AND cdf.deleted = 0 )
					WHERE
						a.user_id = uf.id
						AND a.company_deduction_id = cdf.id
						AND uf.company_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND (a.deleted = 0 AND cdf.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDeductionListFactory
	 */
	function getByCompanyIdAndUserIdAndId( $company_id, $user_id, $id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		$query = '
					SELECT	a.id,
							a.user_id,
							a.company_deduction_id,

							CASE WHEN a.length_of_service_date IS NULL THEN uf.hire_date ELSE a.length_of_service_date END as length_of_service_date,
							CASE WHEN a.start_date IS NULL THEN cdf.start_date ELSE a.start_date END as start_date,
							CASE WHEN a.end_date IS NULL THEN cdf.end_date ELSE a.end_date END as end_date,

							a.user_value1,
							a.user_value2,
							a.user_value3,
							a.user_value4,
							a.user_value5,
							a.user_value6,
							a.user_value7,
							a.user_value8,
							a.user_value9,
							a.user_value10,

							a.created_date as created_date,
							a.created_by as created_by,
							a.updated_date as updated_date,
							a.updated_by as updated_by,
							a.deleted_date as deleted_date,
							a.deleted_by as deleted_by,
							a.deleted as deleted
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					LEFT JOIN ' . $cdf->getTable() . ' as cdf ON ( a.company_deduction_id = cdf.id AND cdf.deleted = 0 )
					WHERE
						uf.company_id = ?
						AND a.user_id = ?
						AND a.id = ?
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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
	 * @return bool|UserDeductionListFactory
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

		$additional_order_fields = [ 'uf.status_id', 'cdf.type_id', 'cdf.name', 'cdf.calculation_id', 'uf.first_name', 'uf.last_name' ];

		$sort_column_aliases = [
				'type'        => 'cdf.type_id',
				'status'      => 'uf.status_id',
				'name'        => 'cdf.name',
				'calculation' => 'cdf.calculation_id',
				'first_name'  => 'uf.first_name',
				'last_name'   => 'uf.last_name',
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'uf.status_id' => 'asc', 'uf.last_name' => 'asc', 'uf.first_name' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by status, last name, first name after other columns
			if ( !isset( $order['uf.status_id'] ) ) {
				$order['uf.status_id'] = 'asc';
			}
			if ( !isset( $order['uf.last_name'] ) ) {
				$order['uf.last_name'] = 'asc';
			}
			if ( !isset( $order['uf.first_name'] ) ) {
				$order['uf.first_name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.id,
							a.user_id,
							a.company_deduction_id,

							CASE WHEN a.length_of_service_date IS NULL THEN uf.hire_date ELSE a.length_of_service_date END as length_of_service_date,
							CASE WHEN a.start_date IS NULL THEN cdf.start_date ELSE a.start_date END as start_date,
							CASE WHEN a.end_date IS NULL THEN cdf.end_date ELSE a.end_date END as end_date,

							a.user_value1,
							a.user_value2,
							a.user_value3,
							a.user_value4,
							a.user_value5,
							a.user_value6,
							a.user_value7,
							a.user_value8,
							a.user_value9,
							a.user_value10,

							uf.first_name as first_name,
							uf.last_name as last_name,
							uf.middle_name as middle_name,
							uf.status_id as user_status_id,
							uf.country as country,
							uf.province as province,
							
							cdf.name as name,
							cdf.status_id as status_id,
							cdf.type_id as type_id,
							cdf.calculation_id as calculation_id,

							a.created_date as created_date,
							a.created_by as created_by,
							a.updated_date as updated_date,
							a.updated_by as updated_by,
							a.deleted_date as deleted_date,
							a.deleted_by as deleted_by,
							a.deleted as deleted,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $cdf->getTable() . ' as cdf ON ( a.company_deduction_id = cdf.id AND cdf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	uf.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'cdf.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'cdf.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['company_deduction_id'] ) ) ? $this->getWhereClauseSQL( 'a.company_deduction_id', $filter_data['company_deduction_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['calculation_id'] ) ) ? $this->getWhereClauseSQL( 'cdf.calculation_id', $filter_data['calculation_id'], 'numeric_list', $ph ) : null;

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
