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
 * @package Modules\Company
 */
class PayStubTransactionListFactory extends PayStubTransactionFactory implements IteratorAggregate {

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
	 * @return bool|PayStubTransactionListFactory
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
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByRemittanceSourceAccountId( $id, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'remittance_source_account_id' => TTUUID::castUUID( $id ),
		];

		$psf = new PayStubFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
					LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id)
					LEFT JOIN ' . $psf->getTable() . ' as psf on psf.id = a.pay_stub_id
					where	a.remittance_source_account_id = ?
						AND (a.deleted = 0 AND psf.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByRemittanceDestinationAccountId( $id, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'remittance_source_account_id' => TTUUID::castUUID( $id ),
		];

		$psf = new PayStubFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
					LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id)
					LEFT JOIN ' . $psf->getTable() . ' as psf on psf.id = a.pay_stub_id
					where	a.remittance_destination_account_id = ?
						AND (a.deleted = 0 AND psf.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByPayStubId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			//Similar sort order at RemittanceDestinationAccountListFactory::getByUserIdAndStatusId
			$order = [ 'b.amount_type_id' => 'desc', 'b.priority' => 'asc', 'b.type_id' => 'desc', 'b.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];

		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
					LEFT JOIN ' . $rdaf->getTable() . ' as b ON ( a.remittance_destination_account_id = b.id)
					where	a.pay_stub_id = ?
						AND ( a.deleted = 0 AND rsaf.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
						LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( rdaf.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( uf.legal_entity_id = lef.id AND lef.deleted = 0 )
					where	a.id = ?
						AND lef.company_id = ?
						AND ( a.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 AND lef.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$ph = [ 'company_id' => TTUUID::castUUID( $company_id ) ];

		$query = 'select a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
						LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( rdaf.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( uf.legal_entity_id = lef.id AND lef.deleted = 0 )
					where lef.company_id = ?
						AND ( a.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 AND lef.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param int|int[] $status_id
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByPayStubIdAndStatusId( $pay_stub_id, $status_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$ph = [
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
		];

		$query = '
					select	pstf.*
					from	' . $this->getTable() . ' as pstf
					LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( pstf.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
					LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( pstf.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
					where	pstf.pay_stub_id = ?
						AND	pstf.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND ( pstf.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		//This supports a list of IDs, so we need to make sure paging is also available.
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param int|int[] $type_id
	 * @param int|int[] $status_id
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */
	function getByPayStubIdAndTypeIdAndStatusId( $pay_stub_id, $type_id, $status_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();

		$ph = [
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
		];

		$query = '
					select	pstf.*
					from	' . $this->getTable() . ' as pstf
					LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( pstf.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
					LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( pstf.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
					where	pstf.pay_stub_id = ?
						AND	pstf.type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND	pstf.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND ( pstf.deleted = 0 AND rsaf.deleted = 0 AND rdaf.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		//This supports a list of IDs, so we need to make sure paging is also available.
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * Duplicated in getAPISearchByCompanyIdAndArrayCriteria
	 *
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
	 */

	function getAPISummaryByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [
				'transaction_date',
				'remittance_source_account',
				'remittance_destination_account',
				'destination_user_first_name',
				'destination_user_last_name',
				'pay_stub_transaction_date',
				'remittance_source_account_type_id',
				'currency_id',
				'currency',
				'currency_rate',
				'status_id',
				'pay_stub_start_date',
				'pay_stub_end_date',
				'pay_period_end_date',
				'pay_period_start_date',
				'pay_stub_run_id',
		];

		$sort_column_aliases = [
				'type'   => 'a.type_id',
				'status' => 'a.status_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'pay_period_id' => 'asc', 'remittance_source_account_id' => 'asc', 'remittance_source_account_last_transaction_number' => 'asc', 'remittance_source_account' => 'asc', 'currency_id' => 'asc', 'remittance_source_account_type' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		if ( isset( $filter_data['transaction_transaction_date-date_stamp'] ) ) {
			$filter_data['transaction_date'] = $filter_data['transaction_transaction_date-date_stamp'];
		}

		if ( isset( $filter_data['transaction_currency_id'] ) ) {
			$filter_data['currency_id'] = $filter_data['transaction_currency_id'];
		}

		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();
		$psf = new PayStubFactory();
		$ppf = new PayPeriodFactory();
		$cf = new CurrencyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = 'SELECT
					ppf.id AS pay_period_id,
					rsaf.id AS remittance_source_account_id,
					rsaf.last_transaction_number AS remittance_source_account_last_transaction_number,
					rsaf.name AS remittance_source_account,
					a.currency_id AS currency_id,
					rsaf.type_id AS remittance_source_account_type,
					SUM ( a.amount ) AS total_amount,
					COUNT ( DISTINCT a.id ) AS total_transactions

					FROM	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
						LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( rdaf.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( uf.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN ' . $psf->getTable() . ' as psf ON ( a.pay_stub_id = psf.id AND psf.deleted = 0 )
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( psf.pay_period_id = ppf.id AND ppf.deleted = 0 )
						LEFT JOIN ' . $cf->getTable() . ' as cf ON ( a.currency_id = cf.id )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE	lef.company_id = ?
					';
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'lef.id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_id'] ) ) ? $this->getWhereClauseSQL( 'psf.id', $filter_data['pay_stub_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_destination_account_type_id'] ) ) ? $this->getWhereClauseSQL( 'rdaf.type_id', $filter_data['remittance_destination_account_type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['remittance_destination_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.remittance_destination_account_id', $filter_data['remittance_destination_account_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_source_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.remittance_source_account_id', $filter_data['remittance_source_account_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_source_account_type_id'] ) ) ? $this->getWhereClauseSQL( 'rsaf.type_id', $filter_data['remittance_source_account_type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['transaction_date'] ) ) ? $this->getWhereClauseSQL( 'a.transaction_date', $filter_data['transaction_date'], 'date_range_timestamp', $ph ) : null;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['start_date'] );
			$query .= ' AND a.transaction_date >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['end_date'] );
			$query .= ' AND a.transaction_date <= ?';
		}

		$query .= ( isset( $filter_data['transaction_status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['transaction_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['transaction_type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['transaction_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'rdaf.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_group_id'] ) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_title_id'] ) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['sex_id'] ) ) ? $this->getWhereClauseSQL( 'uf.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_tag'] ) ) ? $this->getWhereClauseSQL( 'uf.id', [ 'company_id' => TTUUID::castUUID( $company_id ), 'object_type_id' => 200, 'tag' => $filter_data['user_tag'] ], 'tag', $ph ) : null;

		//$query .= ( isset($filter_data['transaction_date']) ) ? $this->getWhereClauseSQL( 'psf.transaction_date', $filter_data['transaction_date'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'ppf.id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_status_id'] ) ) ? $this->getWhereClauseSQL( 'psf.status_id', $filter_data['pay_stub_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_type_id'] ) ) ? $this->getWhereClauseSQL( 'psf.type_id', $filter_data['pay_stub_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_run_id'] ) ) ? $this->getWhereClauseSQL( 'psf.run_id', $filter_data['pay_stub_run_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['is_reprint'] ) && $filter_data['is_reprint'] ) ? ' AND a.parent_id != \'' . TTUUID::getZeroID() . '\' ' : null;

		if ( isset( $filter_data['include_subgroups'] ) && (bool)$filter_data['include_subgroups'] == true ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], true );
		}

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND ( lef.deleted = 0 AND rdaf.deleted = 0 AND a.deleted = 0 AND rsaf.deleted = 0 AND psf.deleted = 0 AND ppf.deleted = 0 )
					GROUP BY ppf.id, a.currency_id, rsaf.name, rsaf.id, rsaf.last_transaction_number,  rsaf.type_id ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * Duplicated in getAPISummaryByCompanyIdAndArrayCriteria
	 *
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubTransactionListFactory
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

		$additional_order_fields = [
				'transaction_date',
				'remittance_source_account',
				'remittance_destination_account',
				'destination_user_first_name',
				'destination_user_last_name',
				'pay_stub_transaction_date',
				'remittance_source_account_type_id',
				'currency_id',
				'currency',
				'currency_rate',
				'status_id',
				'pay_stub_start_date',
				'pay_stub_end_date',
				'pay_period_end_date',
				'pay_period_start_date',
				'pay_stub_run_id',
		];

		$sort_column_aliases = [
				'type'   => 'a.type_id',
				'status' => 'a.status_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'a.status_id' => 'asc', 'psf.transaction_date' => 'asc', 'uf.last_name' => 'asc', 'uf.first_name' => 'asc', 'rdaf.id' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset( $order['destination_user_last_name'] ) ) {
				$order['destination_user_last_name'] = 'asc';
			}
			if ( !isset( $order['destination_user_first_name'] ) ) {
				$order['destination_user_first_name'] = 'asc';
			}

			$strict = true;
		}

		if ( isset( $filter_data['transaction_transaction_date-date_stamp'] ) ) {
			$filter_data['transaction_date'] = $filter_data['transaction_transaction_date-date_stamp'];
		}

		if ( isset( $filter_data['transaction_currency_id'] ) ) {
			$filter_data['currency_id'] = $filter_data['transaction_currency_id'];
		}

		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$rdaf = new RemittanceDestinationAccountFactory();
		$psf = new PayStubFactory();
		$ppf = new PayPeriodFactory();
		$cf = new CurrencyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'company_id2' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					SELECT	a.*,
							a.type_id as transaction_type_id,
							a.status_id as transaction_status_id,

							lef.id as legal_entity_id,
							lef.legal_name as legal_entity_legal_name,
							lef.trade_name as legal_entity_trade_name,

							rsaf.type_id as remittance_source_account_type_id,
							rsaf.name as remittance_source_account,

							rdaf.user_id as user_id,
							uf.first_name as destination_user_first_name,
							uf.last_name as destination_user_last_name,

							rdaf.name as remittance_destination_account,

							cf.name as currency,

							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

							psf.run_id as pay_stub_run_id,

							psf.status_id as pay_stub_status_id,
							psf.start_date as pay_stub_start_date,
							psf.end_date as pay_stub_end_date,
							psf.transaction_date as pay_stub_transaction_date,
							CASE WHEN pst_status_totals.total_transactions_processed IS NULL THEN 0 ELSE pst_status_totals.total_transactions_processed END as pay_stub_total_transactions_processed,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					FROM	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
						LEFT JOIN ' . $rdaf->getTable() . ' as rdaf ON ( a.remittance_destination_account_id = rdaf.id AND rdaf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( rdaf.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( uf.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN ' . $psf->getTable() . ' as psf ON ( a.pay_stub_id = psf.id AND psf.deleted = 0 )
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( psf.pay_period_id = ppf.id AND ppf.deleted = 0 )
						LEFT JOIN ' . $cf->getTable() . ' as cf ON ( a.currency_id = cf.id )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
						LEFT JOIN ( 
									SELECT pst_a.pay_stub_id as pay_stub_id, count(*) as total_transactions_processed
									FROM ' . $this->getTable() . ' as pst_a
									LEFT JOIN '.  $psf->getTable() .' as psf_b ON ( pst_a.pay_stub_id = psf_b.id )
									LEFT JOIN ' . $rdaf->getTable() . ' as rdaf_b ON ( pst_a.remittance_destination_account_id = rdaf_b.id AND rdaf_b.deleted = 0 )
									LEFT JOIN ' . $uf->getTable() . ' as uf_b ON ( rdaf_b.user_id = uf_b.id AND uf_b.deleted = 0 )
									WHERE
										uf_b.company_id = ?
										AND psf_b.status_id = 25
										AND pst_a.status_id != 10
										AND pst_a.type_id = 10 
										AND pst_a.deleted = 0 
									GROUP BY pst_a.pay_stub_id 
									) as pst_status_totals ON ( a.pay_stub_id = pst_status_totals.pay_stub_id )
					WHERE	uf.company_id = ?
					';
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'lef.id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_id'] ) ) ? $this->getWhereClauseSQL( 'psf.id', $filter_data['pay_stub_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_destination_account_type_id'] ) ) ? $this->getWhereClauseSQL( 'rdaf.type_id', $filter_data['remittance_destination_account_type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['remittance_destination_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.remittance_destination_account_id', $filter_data['remittance_destination_account_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_source_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.remittance_source_account_id', $filter_data['remittance_source_account_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_source_account_type_id'] ) ) ? $this->getWhereClauseSQL( 'rsaf.type_id', $filter_data['remittance_source_account_type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['transaction_date'] ) ) ? $this->getWhereClauseSQL( 'a.transaction_date', $filter_data['transaction_date'], 'date_range_timestamp', $ph ) : null;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['start_date'] );
			$query .= ' AND a.transaction_date >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['end_date'] );
			$query .= ' AND a.transaction_date <= ?';
		}

		$query .= ( isset( $filter_data['transaction_status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['transaction_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['transaction_type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['transaction_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'rdaf.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_group_id'] ) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_title_id'] ) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['sex_id'] ) ) ? $this->getWhereClauseSQL( 'uf.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_tag'] ) ) ? $this->getWhereClauseSQL( 'uf.id', [ 'company_id' => TTUUID::castUUID( $company_id ), 'object_type_id' => 200, 'tag' => $filter_data['user_tag'] ], 'tag', $ph ) : null;

		//$query .= ( isset($filter_data['transaction_date']) ) ? $this->getWhereClauseSQL( 'psf.transaction_date', $filter_data['transaction_date'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'psf.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_status_id'] ) ) ? $this->getWhereClauseSQL( 'psf.status_id', $filter_data['pay_stub_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_type_id'] ) ) ? $this->getWhereClauseSQL( 'psf.type_id', $filter_data['pay_stub_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_run_id'] ) ) ? $this->getWhereClauseSQL( 'psf.run_id', $filter_data['pay_stub_run_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['is_reprint'] ) && $filter_data['is_reprint'] ) ? ' AND a.parent_id != \'' . TTUUID::getZeroID() . '\' ' : null;

		if ( isset( $filter_data['include_subgroups'] ) && (bool)$filter_data['include_subgroups'] == true ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], true );
		}

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;
		$query .= ' AND ( a.deleted = 0 AND rsaf.deleted = 0 AND lef.deleted = 0 AND rdaf.deleted = 0 AND psf.deleted = 0 AND ppf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>
