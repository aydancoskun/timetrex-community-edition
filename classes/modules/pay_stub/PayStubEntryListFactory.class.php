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
 * @package Modules\PayStub
 */
class PayStubEntryListFactory extends PayStubEntryFactory implements IteratorAggregate {

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
	 * @return bool|PayStubEntryListFactory
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
	 * @return bool|PayStubEntryListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$psf = new PayStubFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
						LEFT JOIN ' . $psf->getTable() . ' as psf ON a.pay_stub_id = psf.id
						LEFT JOIN ' . $uf->getTable() . ' as uf ON psf.user_id = uf.id
					WHERE
							uf.company_id = ?
							AND ( a.deleted = 0 AND psf.deleted = 0 AND uf.deleted = 0)
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}


	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {

		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$psf = new PayStubFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
						LEFT JOIN ' . $psf->getTable() . ' as psf ON a.pay_stub_id = psf.id
						LEFT JOIN ' . $uf->getTable() . ' as uf ON psf.user_id = uf.id
					WHERE
							a.id = ?
							AND
							uf.company_id = ?
							AND ( a.deleted = 0 AND psf.deleted = 0 AND uf.deleted = 0)
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id          UUID
	 * @param string $pay_stub_id UUID
	 * @param string $company_id  UUID
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByIdAndPayStubIdAndCompanyId( $id, $pay_stub_id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$psf = new PayStubFactory();

		$ph = [
				'id'          => TTUUID::castUUID( $id ),
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
				'company_id'  => TTUUID::castUUID( $company_id ),
		];


		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
						LEFT JOIN ' . $psf->getTable() . ' as psf ON a.pay_stub_id = psf.id
						LEFT JOIN ' . $uf->getTable() . ' as uf ON psf.user_id = uf.id
					WHERE
							a.id = ? AND a.pay_stub_id = ?
							AND
							uf.company_id = ?
							AND ( a.deleted = 0 AND psf.deleted = 0 AND uf.deleted = 0)
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByPayStubId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}


		$additional_order_fields = [ 'ps_order' ];
		if ( $order == null ) {
			$strict = false;

			$order = [ 'b.ps_order' => 'asc', 'abs(a.ytd_amount)' => 'asc', 'a.id' => 'asc' ];
		} else {
			$strict = true;
		}

		//This is needed to ensure the proper order of entries for pay stubs
		//VERY IMPORTANT!
		//notice b."order" in the query.
		//
		// NOTICE: For accruals, if we order ytd_amount asc negative values come before
		// 0.00 or postitive values. Keep this in mind when calculating overall YTD totals.
		// abs(a.ytd_amount) is CRITICAL here, this keeps negative amounts after 0.00 values
		// and keeps negative accrual amounts from being calculated incorrectly.

		$psealf = new PayStubEntryAccountListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		//Also need to make sure we include PS Amendments so we know if its a YTD Adjustment or not. Important for recalculating YTD amounts on newer pay stubs.
		//  Used to filter out deleted pay stub amendments with: AND ( c.deleted is NULL OR c.deleted = 0 )
		//  However this causes a problem where if the user generates a pay stub with the pay stub amendment, then deletes the pay stub amendment before
		//  marking the pay stub as paid, the pay stub looks like the totals are incorrect because its missing a line item, at least until the pay stub is generated again.
		//  So instead always show deleted pay stub amendments on pay stubs.
		$query = '
					select	a.*,
							c.ytd_adjustment
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $psealf->getTable() . ' as b ON ( a.pay_stub_entry_name_id = b.id )
							LEFT JOIN ' . $psalf->getTable() . ' as c ON ( a.pay_stub_amendment_id = c.id )
					where	a.pay_stub_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param $ytd_adjustment
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByPayStubIdAndYTDAdjustment( $id, $ytd_adjustment, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$strict = true;
		if ( $order == null ) {
			$strict = false;

			$order = [ 'b.ps_order' => 'asc', 'abs(a.ytd_amount)' => 'asc', 'a.id' => 'asc' ];
		}

		//This is needed to ensure the proper order of entries for pay stubs
		//VERY IMPORTANT!
		//notice b."order" in the query.
		//
		// NOTICE: For accruals, if we order ytd_amount asc negative values come before
		// 0.00 or postitive values. Keep this in mind when calculating overall YTD totals.
		// abs(a.ytd_amount) is CRITICAL here, this keeps negative amounts after 0.00 values
		// and keeps negative accrual amounts from being calculated incorrectly.

		$psealf = new PayStubEntryAccountListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = [
				'id'             => TTUUID::castUUID( $id ),
				'ytd_adjustment' => $this->toBool( $ytd_adjustment ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $psealf->getTable() . ' as b ON ( a.pay_stub_entry_name_id = b.id )
					LEFT JOIN ' . $psalf->getTable() . ' as c ON ( a.pay_stub_amendment_id = c.id )
					where	a.pay_stub_id = ?
						AND ( c.ytd_adjustment is NULL OR c.ytd_adjustment = ? )
						AND ( a.deleted = 0 AND b.deleted = 0 AND ( c.deleted is NULL OR c.deleted = 0 ) )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $account_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByPayStubIdAndEntryNameId( $id, $account_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $account_id == '' || $account_id == 0 || $account_id == TTUUID::getZeroID() ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'account_id' => TTUUID::castUUID( $account_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND a.pay_stub_id = ?
						AND b.id = ?
						AND a.deleted = 0
					ORDER BY b.ps_order ASC, a.id ASC
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}


	/**
	 * @param string $id            UUID
	 * @param string $entry_name_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getAmountSumByPayStubIdAndEntryNameID( $id, $entry_name_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	sum(a.amount) as amount, sum(a.units) as units
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND a.pay_stub_id = ?
						AND b.id in (' . $this->getListSQL( $entry_name_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		Debug::text( 'Over All Sum for Pay Stub: ' . $id . ' Entry Name ID:' . $entry_name_id . ': Amount ' . $row['amount'] . ' Units: ' . $row['units'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $id   UUID
	 * @param $type
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByPayStubIdAndType( $id, $type, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $type == '' ) {
			return false;
		}

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND a.pay_stub_id = ?
						AND b.type_id in (' . $this->getListSQL( $type, $ph ) . ')
						AND a.deleted = 0
					ORDER BY b.ps_order ASC, a.id ASC
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id            UUID
	 * @param string $entry_name_id UUID
	 * @param int $date             EPOCH
	 * @param string $exclude_id    UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getYTDAmountSumByUserIdAndEntryNameIdAndDate( $id, $entry_name_id, $date = null, $exclude_id = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = TTDate::getTime();
		}

		if ( $exclude_id == '' ) {
			$exclude_id = TTUUID::getZeroId(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$begin_year_epoch = TTDate::getBeginYearEpoch( $date );

		$pplf = new PayPeriodListFactory();
		$pslf = new PayStubListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'begin_year' => $this->db->BindTimeStamp( $begin_year_epoch ),
				'end_date'   => $this->db->BindTimeStamp( $date ),
				'exclude_id' => TTUUID::castUUID( $exclude_id ),
		];

		$a_pay_stub_entry_name_id_sql = $this->getListSQL( $entry_name_id, $ph, 'uuid' );

		$ph['id2'] = $id;
		$ph['begin_year2'] = $begin_year_epoch;
		$ph['end_date2'] = $date;

		$m_pay_stub_entry_name_id_sql = $this->getListSQL( $entry_name_id, $ph, 'uuid' );

		//For advances, the pay stub transaction date in Dec is before the year end,
		//But it must be included in the next year. So we have to
		//base this query off the PAY PERIOD transaction date, NOT the pay stub transaction date.
		$query = '

					select	sum(amount) as amount, sum(units) as units
					from (
						select	sum(amount) as amount, sum(units) as units
						from	' . $this->getTable() . ' as a,
								' . $pslf->getTable() . ' as b,
								' . $pplf->getTable() . ' as c
						where	a.pay_stub_id = b.id
							AND b.pay_period_id = c.id
							AND b.user_id = ?
							AND c.transaction_date >= ?
							AND c.transaction_date <= ?
							AND a.id != ?
							AND	a.pay_stub_entry_name_id in (' . $a_pay_stub_entry_name_id_sql . ')
							AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0 )
						UNION
						select sum(amount) as amount, sum(units) as units
						from ' . $psalf->getTable() . ' as m
						where m.user_id = ?
							AND m.effective_date >= ?
							AND m.effective_date <= ?
							AND	m.pay_stub_entry_name_id in (' . $m_pay_stub_entry_name_id_sql . ')
							AND m.ytd_adjustment = 1
							AND m.deleted=0
						) as tmp_table

				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}
		//Debug::text('YTD Sum Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text( 'YTD Sum for User ID: ' . $id . ' Entry Name ID: ' . $entry_name_id . ': Amount ' . $row['amount'] . ' Units: ' . $row['units'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $id            UUID
	 * @param string $entry_name_id UUID
	 * @param int $date             EPOCH
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getYTDAmountSumByUserIdAndEntryNameIDAndYear( $id, $entry_name_id, $date = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		//return $this->getYTDAmountSumByUserIdAndEntryNameIdAndYear($id, $entry_name_id, $year, $where, $order);
		return $this->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $id, $entry_name_id, $date, $where, $order );
	}

	/**
	 * @param string $user_id     UUID
	 * @param string $pay_stub_id UUID
	 * @param null $year
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getOtherEntryNamesByUserIdAndPayStubIdAndYear( $user_id, $pay_stub_id, $year = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $year == '' ) {
			$year = TTDate::getTime();
		}

		$begin_year_epoch = TTDate::getBeginYearEpoch( $year );

		$pplf = new PayPeriodListFactory();

		$pslf = new PayStubListFactory();

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'transaction_date' => $this->db->BindTimeStamp( $begin_year_epoch ),
				'user_id'          => TTUUID::castUUID( $user_id ),
				'pay_stub_id'      => TTUUID::castUUID( $pay_stub_id ),
		];

		//Make sure we don't include entries that have a sum of 0.
		//This is YTD for the EMPLOYEES... So it should always be when they are paid.
		$query = '
					select	distinct(pay_stub_entry_name_id)
					from	' . $this->getTable() . ' as a,
							' . $pslf->getTable() . ' as b,
							' . $psealf->getTable() . ' as c
					where	a.pay_stub_id = b.id
						AND a.pay_stub_entry_name_id = c.id
						AND b.pay_period_id in (select id from ' . $pplf->getTable() . ' as y
													where y.pay_period_schedule_id = ( select pay_period_schedule_id from ' . $pplf->getTable() . ' as z where z.id = b.pay_period_id ) AND y.transaction_date >= ? AND y.deleted=0)
						AND b.user_id = ?
						AND c.type_id in (10, 20, 30)
						AND a.pay_stub_entry_name_id NOT IN ( select distinct(pay_stub_entry_name_id) from ' . $this->getTable() . ' as x WHERE x.pay_stub_id = ?)
						AND a.deleted = 0
						AND b.deleted = 0
					GROUP BY pay_stub_entry_name_id
					HAVING sum(amount) > 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$result = $this->rs = $this->db->GetCol( $query, $ph );

		return $result;
	}

	/**
	 * @param string $id                     UUID
	 * @param string|string[] $entry_name_id UUID
	 * @param string|string[] $pay_period_id UUID
	 * @param string $exclude_id             UUID
	 * @param array $where                   Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                   Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getAmountSumByUserIdAndEntryNameIdAndPayPeriodId( $id, $entry_name_id, $pay_period_id, $exclude_id = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $exclude_id == '' ) {
			$exclude_id = TTUUID::getZeroId(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$pslf = new PayStubListFactory();

		$ph = [
				'user_id'    => TTUUID::castUUID( $id ),
				'exclude_id' => TTUUID::castUUID( $exclude_id ),
		];

		$query = '
					select	sum(amount) as amount, sum(units) as units
					from	' . $this->getTable() . ' as a,
							' . $pslf->getTable() . ' as b
					where	a.pay_stub_id = b.id
						AND b.user_id = ?
						AND a.id != ?
						AND b.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND	a.pay_stub_entry_name_id in (' . $this->getListSQL( $entry_name_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0
						AND b.deleted=0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		Debug::Arr( $entry_name_id, 'Over All Sum: Amount ' . $row['amount'] . ' Units: ' . $row['units'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $id            UUID
	 * @param string $entry_name_id UUID
	 * @param int $start_date       EPOCH
	 * @param int $end_date         EPOCH
	 * @param string $exclude_id    UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getAmountSumByUserIdAndEntryNameIdAndStartDateAndEndDate( $id, $entry_name_id, $start_date = null, $end_date = null, $exclude_id = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			$start_date = 0;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime();
		}

		if ( $exclude_id == '' ) {
			$exclude_id = TTUUID::getZeroId(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$pplf = new PayPeriodListFactory();
		$pslf = new PayStubListFactory();

		$ph = [
				'start_date' => $this->db->BindTimeStamp( $start_date ),
				'end_date'   => $this->db->BindTimeStamp( $end_date ),
				'user_id'    => TTUUID::castUUID( $id ),
				'exclude_id' => TTUUID::castUUID( $exclude_id ),
		];

		$query = '
					select	sum(amount) as amount, sum(units) as units
					from	' . $this->getTable() . ' as a,
							' . $pslf->getTable() . ' as b
					where	a.pay_stub_id = b.id
						AND b.pay_period_id in (select id from ' . $pplf->getTable() . ' as y
													where y.pay_period_schedule_id = ( select pay_period_schedule_id from ' . $pplf->getTable() . ' as z where z.id = b.pay_period_id ) AND y.start_date >= ? AND y.start_date < ? and y.deleted =0)
						AND b.user_id = ?
						AND a.id != ?
						AND	a.pay_stub_entry_name_id in (' . $this->getListSQL( $entry_name_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0
						AND b.deleted=0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		Debug::text( 'Over All Sum for ' . $entry_name_id . ': Amount ' . $row['amount'] . ' Units: ' . $row['units'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $user_id       UUID
	 * @param string $entry_name_id UUID
	 * @param int $date             EPOCH
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getLastSumByUserIdAndEntryNameIdAndDate( $user_id, $entry_name_id, $date = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		$pslf = new PayStubListFactory();

		$ph = [
				'user_id'       => TTUUID::castUUID( $user_id ),
				'date'          => $this->db->BindDate( $date ),
				'entry_name_id' => TTUUID::castUUID( $entry_name_id ),
		];

		$query = '
					SELECT	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units, max(end_date) as end_date
					FROM (
						SELECT	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units, max(end_date) as end_date
						FROM	' . $this->getTable() . ' as a
						LEFT JOIN ' . $pslf->getTable() . ' as ps ON ( a.pay_stub_id = ps.id )
						WHERE
							a.pay_stub_id = ( SELECT id FROM ' . $pslf->getTable() . ' as ps_b WHERE ps_b.user_id = ? AND ps_b.start_date <= ? AND ps_b.status_id in (40,100) AND ps_b.deleted = 0 ORDER BY ps_b.start_date DESC, ps_b.run_id DESC LIMIT 1 )
							AND a.pay_stub_entry_name_id = ?
							AND ( a.deleted = 0 AND ps.deleted = 0 )
						GROUP BY a.pay_stub_entry_name_id
						) as ytd_sum
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$row = $this->db->GetRow( $query, $ph );

		//Need to return NULL values so we know if any data is returned or if its 0 instead.
		Debug::text( 'Entry Name ID: ' . $entry_name_id . ' Amount Sum: ' . $row['amount'] . ' - YTD Amount Sum: ' . $row['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $pay_stub_id   UUID
	 * @param string $entry_name_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getSumByPayStubIdAndEntryNameId( $pay_stub_id, $entry_name_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		$ph = [
				'pay_stub_id'   => TTUUID::castUUID( $pay_stub_id ),
				'entry_name_id' => TTUUID::castUUID( $entry_name_id ),
		];

		$query = '
					select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
					from (
						select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
						from	' . $this->getTable() . ' as a
						where
							a.pay_stub_id = ?
							AND a.pay_stub_entry_name_id = ?
							AND a.deleted = 0
						group by a.pay_stub_entry_name_id
						) as ytd_sum
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::text('YTD Sum by Entry Name Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		if ( $row['ytd_amount'] === null ) {
			$row['ytd_amount'] = 0;
		}

		if ( $row['ytd_units'] === null ) {
			$row['ytd_units'] = 0;
		}

		Debug::text( 'Entry Name ID: ' . $entry_name_id . ' Amount Sum: ' . $row['amount'] . ' - YTD Amount Sum: ' . $row['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $pay_stub_id   UUID
	 * @param string $entry_name_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getSumByPayStubIdAndEntryNameIdAndNotPSAmendment( $pay_stub_id, $entry_name_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		$ph = [
				'pay_stub_id'   => TTUUID::castUUID( $pay_stub_id ),
				'entry_name_id' => TTUUID::castUUID( $entry_name_id ),
		];

		//Ignore all PS amendments when doing this.
		//This is mainly for PayStub Calc Diff function.
		/*
				$query = '
							select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
							from	'. $this->getTable() .' as a
							where
								a.pay_stub_id = ?
								AND a.pay_stub_entry_name_id = ?
								AND a.pay_stub_amendment_id is NULL
								AND a.deleted = 0
						';
		*/
		$query = '
					select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
					from (
						select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
						from	' . $this->getTable() . ' as a
						where
							a.pay_stub_id = ?
							AND a.pay_stub_entry_name_id = ?
							AND a.pay_stub_amendment_id is NULL
							AND a.deleted = 0
						group by a.pay_stub_entry_name_id
						) as ytd_sum
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::text('YTD Sum by Entry Name Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$row = $this->db->GetRow( $query, $ph );
		//var_dump($row);

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		if ( $row['ytd_amount'] === null ) {
			$row['ytd_amount'] = 0;
		}

		if ( $row['ytd_units'] === null ) {
			$row['ytd_units'] = 0;
		}

		/*
		if ( $sum !== FALSE OR $sum !== NULL) {
			Debug::text('Amount Sum: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
			return $sum;
		}
		*/

		Debug::text( 'Entry Name ID: ' . $entry_name_id . ' Amount Sum: ' . $row['amount'] . ' - YTD Amount Sum: ' . $row['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param int $type_id
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getSumByPayStubIdAndType( $pay_stub_id, $type_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
		];
		/*
				$query = '
							select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
							from	'. $this->getTable() .' as a,
									'. $psealf->getTable() .' as b
							where	a.pay_stub_entry_name_id = b.id
								AND a.pay_stub_id = ?
								AND b.type_id in ('. $this->getListSQL( $type_id, $ph, 'int' ) .')
								AND a.deleted = 0
						';
		*/

		//Account for cases where the same entry is made twice, the YTD amount will be doubled up
		//so when calculating the sum by type we need to ignore this.
		$query = '
					select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
					from (
						select	sum(amount) as amount, sum(units) as units, sum(ytd_amount) as ytd_amount, sum(ytd_units) as ytd_units
						from	' . $this->getTable() . ' as a,
								' . $psealf->getTable() . ' as b
						where	a.pay_stub_entry_name_id = b.id
							AND a.pay_stub_id = ?
							AND b.type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
							AND a.deleted = 0
						group by a.pay_stub_entry_name_id
						) as ytd_sum
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::text('Pay Stub Sum by type Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$row = $this->db->GetRow( $query, $ph );

		if ( $row['amount'] === null ) {
			$row['amount'] = 0;
		}

		if ( $row['units'] === null ) {
			$row['units'] = 0;
		}

		if ( $row['ytd_amount'] === null ) {
			$row['ytd_amount'] = 0;
		}

		if ( $row['ytd_units'] === null ) {
			$row['ytd_units'] = 0;
		}

		/*
		if ( $sum !== FALSE OR $sum !== NULL) {
			Debug::text('Amount Sum: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
			return $sum;
		}
		*/

		Debug::text( 'Type ID: ' . $type_id . ' Amount Sum: ' . $row['amount'] . ' - YTD Amount Sum: ' . $row['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $row;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param int $type_id
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getAmountSumByPayStubIdAndType( $pay_stub_id, $type_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
				'type_id'     => (int)$type_id,
		];

		$query = '
					select	sum(amount)
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND a.pay_stub_id = ?
						AND b.type_id = ?
						AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$sum = $this->db->GetOne( $query, $ph );

		if ( $sum !== false || $sum !== null ) {
			Debug::text( 'Amount Sum: ' . $sum, __FILE__, __LINE__, __METHOD__, 10 );

			return $sum;
		}

		Debug::text( 'Amount Sum is NULL', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param int $type_id
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getUnitSumByPayStubIdAndType( $pay_stub_id, $type_id, $where = null, $order = null ) {
		if ( $pay_stub_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'pay_stub_id' => TTUUID::castUUID( $pay_stub_id ),
				'type_id'     => (int)$type_id,
		];

		$query = '
					select	sum(units)
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND a.pay_stub_id = ?
						AND b.type_id = ?
						AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$sum = $this->db->GetOne( $query, $ph );

		if ( $sum !== false || $sum !== null ) {
			Debug::text( 'Unit Sum: ' . $sum, __FILE__, __LINE__, __METHOD__, 10 );

			return $sum;
		}

		Debug::text( 'Unit Sum is NULL', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $name
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByName( $name, $where = null, $order = null ) {
		if ( $name == '' ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'name' => $name,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND b.name = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $entry_name_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getByEntryNameId( $entry_name_id, $where = null, $order = null ) {
		if ( $entry_name_id == '' ) {
			return false;
		}

		$ph = [
				'entry_name_id' => TTUUID::castUUID( $entry_name_id ),
		];

		$psf = new PayStubFactory();

		//Make sure we ignore pay stub entries attached to deleted pay stubs.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $psf->getTable() . ' as psf ON ( a.pay_stub_id = psf.id )
					where	a.pay_stub_entry_name_id = ?
						AND ( a.deleted = 0 AND psf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id          UUID
	 * @param string $user_ids            UUID
	 * @param int $transaction_start_date EPOCH
	 * @param int $transaction_end_date   EPOCH
	 * @param array $where                Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getReportByCompanyIdAndUserIdAndTransactionStartDateAndTransactionEndDate( $company_id, $user_ids, $transaction_start_date, $transaction_end_date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_ids == '' ) {
			return false;
		}

		if ( $transaction_start_date == '' ) {
			return false;
		}

		if ( $transaction_end_date == '' ) {
			return false;
		}

		$psf = new PayStubFactory();
		$uf = new UserFactory();

		$ph = [
				'company_id'             => TTUUID::castUUID( $company_id ),
				'transaction_start_date' => $this->db->BindTimeStamp( strtolower( trim( $transaction_start_date ) ) ),
				'transaction_end_date'   => $this->db->BindTimeStamp( strtolower( trim( $transaction_end_date ) ) ),
		];

		$query = '
					select	b.user_id as user_id,
							a.pay_stub_entry_name_id as pay_stub_entry_name_id,
							sum(amount) as amount,
							sum(ytd_amount) as ytd_amount
					from	' . $this->getTable() . ' as a,
							' . $psf->getTable() . ' as b,
							' . $uf->getTable() . ' as c
					where	a.pay_stub_id = b.id
						AND b.user_id = c.id
						AND	c.company_id = ?
						AND b.transaction_date >= ?
						AND b.transaction_date <= ?
					';

		$query .= '
						AND b.user_id in (' . $this->getListSQL( $user_ids, $ph, 'uuid' ) . ')
						AND (a.deleted = 0 AND b.deleted=0)
					group by b.user_id, a.pay_stub_entry_name_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id            UUID
	 * @param string $entry_name_id UUID
	 * @param int $start_date       EPOCH
	 * @param int $end_date         EPOCH
	 * @param string $exclude_id    UUID
	 * @param bool $exclude_ytd_adjustment
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubEntryListFactory
	 */
	function getPayPeriodReportByUserIdAndEntryNameIdAndStartDateAndEndDate( $id, $entry_name_id, $start_date = null, $end_date = null, $exclude_id = null, $exclude_ytd_adjustment = false, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $entry_name_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			$start_date = 0;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime();
		}

		if ( $exclude_id == '' ) {
			$exclude_id = TTUUID::getZeroId(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$psaf = new PayStubAmendmentFactory();
		$ppf = new PayPeriodFactory();
		$psf = new PayStubFactory();
		$uf = new UserFactory();

		$ph = [
				'start_date' => $this->db->BindTimeStamp( $start_date ),
				'end_date'   => $this->db->BindTimeStamp( $end_date ),
				'user_id'    => TTUUID::castUUID( $id ),
				'exclude_id' => TTUUID::castUUID( $exclude_id ),
		];

		//Include pay periods with no pay stubs for ROEs.
		//If the company has multiple pay period schedules, this will include pay periods from all schedules, even if the employee was never assigned
		//to a different one. Therefore only include pay periods that have at least one user_date entry assigned to it.
		// When checking pay period start_date, need to use >= and <=, otherwise if the pay stub starts and ends on the same date (PP start date), it won't match.
		//FIXME: This doesnt handle the case where they may be added at a later date, then some time is manually added earlier, but still with a pay period have no time on it.
		//  ROEFactory->getInsurablePayPeriodStartDate() should handle pay periods without any earnings instead.
		// --AND EXISTS (select 1 from '. $udtf->getTable() .' as ud WHERE x.id = ud.pay_eriod_id AND y.id = ud.user_id )
		$query = '
					select	x.id as pay_period_id,
							y.id as user_id,
							x.start_date as pay_period_start_date,
							x.end_date as pay_period_end_date,
							x.transaction_date as pay_period_transaction_date,
							tmp.amount as amount,
							tmp.units as units
					from	' . $ppf->getTable() . ' x
						LEFT JOIN ' . $uf->getTable() . ' as y ON x.company_id = y.company_id
						LEFT JOIN	(
										select	b.user_id as user_id,
												b.pay_period_id as pay_period_id,
												sum(a.amount) as amount,
												sum(a.units) as units
										from	' . $this->getTable() . ' as a
												LEFT JOIN ' . $psf->getTable() . ' as b ON ( a.pay_stub_id = b.id )
												LEFT JOIN ' . $ppf->getTable() . ' as c ON ( b.pay_period_id = c.id )
												LEFT JOIN ' . $psaf->getTable() . ' as d ON a.pay_stub_amendment_id = d.id
										where
											c.start_date >= ?
											AND c.start_date <= ?
											AND b.user_id = ?
											AND a.id != ?
											AND	a.pay_stub_entry_name_id in (' . $this->getListSQL( $entry_name_id, $ph, 'uuid' ) . ') ';

		if ( isset( $exclude_ytd_adjustment ) && (bool)$exclude_ytd_adjustment == true ) {
			$query .= ' AND ( d.ytd_adjustment is NULL OR d.ytd_adjustment = 0 )';
		}

		$query .= '
											AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
										group by b.user_id, b.pay_period_id
									) as tmp ON y.id = tmp.user_id AND x.id = tmp.pay_period_id ';

		$ph[] = TTUUID::castUUID( $id );
		$ph[] = $this->db->BindTimeStamp( $start_date );
		$ph[] = $this->db->BindTimeStamp( $end_date );
		$query .= '
					where y.id = ?
						AND x.start_date >= ?
						AND x.start_date <= ?
						AND ( amount != 0 OR units != 0 )
						AND x.deleted = 0
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, false );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

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
	 * @return bool|PayStubEntryListFactory
	 */
	function getAPIReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
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
				'default_branch',
				'default_department',
				'group',
				'title',
				'currency',
				'pay_period_transaction_date',
				'pay_stub_transaction_date',
				'user_id',
		];

		if ( $order == null ) {
			$order = [ 'c.last_name' => 'asc', 'c.first_name' => 'asc', 'b.user_id' => 'asc', 'pay_period_transaction_date' => 'asc', 'pay_stub_run_id' => 'asc', 'ps_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['user_group_id'] ) ) {
			$filter_data['group_id'] = $filter_data['user_group_id'];
		}

		$ppf = new PayPeriodFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$psf = new PayStubFactory();
		$uf = new UserFactory();
		$psaf = new PayStubAmendmentFactory();
		$pseaf = new PayStubEntryAccountFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//Used to max(aa.ytd_amount), but that caused bugs when YTD or Accrual Balances (for loans specifically)
		//were in the negative for one row and $0 for the next row as it would always select the $0
		$query = '
					SELECT a.*,
							c.legal_entity_id as legal_entity_id,
							b.user_id as user_id,
							c.birth_date as birth_date,
							b.pay_period_id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							b.start_date as pay_stub_start_date,
							b.end_date as pay_stub_end_date,
							b.transaction_date as pay_stub_transaction_date,
							b.status_id as pay_stub_status_id,
							b.type_id as pay_stub_type_id,
							b.run_id as pay_stub_run_id,
							b.currency_id as currency_id,
							b.currency_rate as currency_rate
					FROM 	(
							SELECT aa.pay_stub_id as pay_stub_id,
								kk.id as payroll_remittance_agency_id,
								aa.pay_stub_entry_name_id as pay_stub_entry_name_id,
								ii.ps_order as ps_order,
								avg(aa.rate) as rate,
								sum(aa.units) as units,
								sum(aa.amount) as amount,
								sum(aa.ytd_amount) as ytd_amount
							FROM ' . $this->getTable() . ' as aa
							LEFT JOIN ' . $pseaf->getTable() . ' as ii ON aa.pay_stub_entry_name_id = ii.id
							LEFT JOIN ' . $psaf->getTable() . ' as hh ON aa.pay_stub_amendment_id = hh.id
							LEFT JOIN ' . $psf->getTable() . ' as bb ON aa.pay_stub_id = bb.id
							LEFT JOIN ' . $uf->getTable() . ' as cc ON bb.user_id = cc.id
							LEFT JOIN ' . $bf->getTable() . ' as dd ON cc.default_branch_id = dd.id
							LEFT JOIN ' . $df->getTable() . ' as ee ON cc.default_department_id = ee.id
							LEFT JOIN ' . $ugf->getTable() . ' as ff ON cc.group_id = ff.id
							LEFT JOIN ' . $utf->getTable() . ' as gg ON cc.title_id = gg.id
							LEFT JOIN (
								SELECT
								  CAST( MIN( CAST(jj.payroll_remittance_agency_id AS CHAR(36)) ) AS UUID ) as payroll_remittance_agency_id,
								  jj.pay_stub_entry_account_id,
								  jj.legal_entity_id
								FROM company_deduction AS jj
								GROUP BY jj.pay_stub_entry_account_id, jj.legal_entity_id
								) as jj on aa.pay_stub_entry_name_id = jj.pay_stub_entry_account_id AND cc.legal_entity_id = jj.legal_entity_id
							LEFT JOIN payroll_remittance_agency as kk on jj.payroll_remittance_agency_id = kk.id

							where cc.company_id = ? ';

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'cc.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		//Can't filter by payroll_remittance_agency_id or company_deduction_id as that just filters the result, it wouldn't include all the earnings that go into them. This filtering needs to be done in PHP instead.
		//$query .= ( isset($filter_data['payroll_remittance_agency_id']) ) ? $this->getWhereClauseSQL( 'kk.id', $filter_data['payroll_remittance_agency_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'cc.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_status_id'] ) ) ? $this->getWhereClauseSQL( 'bb.status_id', $filter_data['pay_stub_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_type_id'] ) ) ? $this->getWhereClauseSQL( 'bb.type_id', $filter_data['pay_stub_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_run_id'] ) ) ? $this->getWhereClauseSQL( 'bb.run_id', $filter_data['pay_stub_run_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_id'] ) ) ? $this->getWhereClauseSQL( 'aa.pay_stub_id', $filter_data['pay_stub_id'], 'uuid_list', $ph ) : null; //Critical for exporting payroll withholding to TT Payment Services

		if ( isset( $filter_data['exclude_ytd_adjustment'] ) && (bool)$filter_data['exclude_ytd_adjustment'] == true ) {
			$query .= ' AND ( hh.ytd_adjustment is NULL OR hh.ytd_adjustment = 0 )';
		}

		if ( isset( $filter_data['include_subgroups'] ) && (bool)$filter_data['include_subgroups'] == true ) {
			$uglf = new UserGroupListFactory();
			$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], true );
		}
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'cc.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'cc.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'cc.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'cc.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['sex_id'] ) ) ? $this->getWhereClauseSQL( 'cc.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'bb.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'bb.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['run_id'] ) ) ? $this->getWhereClauseSQL( 'bb.run_id', $filter_data['run_id'], 'numeric_list', $ph ) : null;

		//$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => TTUUID::castUUID($company_id), 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( $filter_data['start_date'] );
			$query .= ' AND bb.transaction_date >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( $filter_data['end_date'] );
			$query .= ' AND bb.transaction_date <= ?';
		}
		/*
		if ( isset($filter_data['transaction_date']) AND !is_array($filter_data['transaction_date']) AND trim($filter_data['transaction_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_date'])) );
			$query	.=	' AND bb.transaction_date = ?';
		}
		*/

		$query .= '
								AND (aa.deleted = 0 AND bb.deleted = 0 AND cc.deleted=0)
							group by aa.pay_stub_id, kk.id, kk.name, aa.pay_stub_entry_name_id, ii.ps_order
							) a
						LEFT JOIN ' . $psf->getTable() . ' as b ON a.pay_stub_id = b.id
						LEFT JOIN ' . $uf->getTable() . ' as c ON b.user_id = c.id
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON b.pay_period_id = ppf.id
					where	1=1
					';

		$query .= '
						AND ( c.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
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
	 * @return bool|PayStubEntryListFactory
	 */
	function getAPIGeneralLedgerReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
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
				'default_branch',
				'default_department',
				'group',
				'title',
				'currency',
				'pay_period_transaction_date',
				'pay_stub_transaction_date',
				'user_id',
		];

		if ( $order == null ) {
			$order = [ 'b.user_id' => 'asc', 'pseaf.ps_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$ppf = new PayPeriodFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$psf = new PayStubFactory();
		$uf = new UserFactory();
		$psaf = new PayStubAmendmentFactory();
		$pseaf = new PayStubEntryAccountFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							b.user_id as user_id,
							b.pay_period_id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

							b.start_date as pay_stub_start_date,
							b.end_date as pay_stub_end_date,
							b.transaction_date as pay_stub_transaction_date,
							b.status_id as pay_stub_status_id,
							b.type_id as pay_stub_type_id,
							b.run_id as pay_stub_run_id,
							b.currency_id as currency_id,
							b.currency_rate as currency_rate,
							a.pay_stub_entry_name_id as pay_stub_entry_name_id,
							a.rate as rate,
							a.units as units,
							a.amount as amount,
							a.ytd_amount as ytd_amount
					from	(
							select aa.pay_stub_id as pay_stub_id,
								aa.pay_stub_entry_name_id as pay_stub_entry_name_id,
								aa.rate as rate,
								aa.units as units,
								aa.amount as amount,
								aa.ytd_amount as ytd_amount
							from ' . $this->getTable() . ' as aa
							LEFT JOIN ' . $psaf->getTable() . ' as hh ON aa.pay_stub_amendment_id = hh.id
							LEFT JOIN ' . $psf->getTable() . ' as bb ON aa.pay_stub_id = bb.id
							LEFT JOIN ' . $uf->getTable() . ' as cc ON bb.user_id = cc.id
							LEFT JOIN ' . $bf->getTable() . ' as dd ON cc.default_branch_id = dd.id
							LEFT JOIN ' . $df->getTable() . ' as ee ON cc.default_department_id = ee.id
							LEFT JOIN ' . $ugf->getTable() . ' as ff ON cc.group_id = ff.id
							LEFT JOIN ' . $utf->getTable() . ' as gg ON cc.title_id = gg.id

							where cc.company_id = ? ';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'cc.id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'cc.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_status_id'] ) ) ? $this->getWhereClauseSQL( 'bb.status_id', $filter_data['pay_stub_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_type_id'] ) ) ? $this->getWhereClauseSQL( 'bb.type_id', $filter_data['pay_stub_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_run_id'] ) ) ? $this->getWhereClauseSQL( 'bb.run_id', $filter_data['pay_stub_run_id'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['exclude_ytd_adjustment'] ) && (bool)$filter_data['exclude_ytd_adjustment'] == true ) {
			$query .= ' AND ( hh.ytd_adjustment is NULL OR hh.ytd_adjustment = 0 )';
		}

		if ( isset( $filter_data['include_subgroups'] ) && (bool)$filter_data['include_subgroups'] == true ) {
			$uglf = new UserGroupListFactory();
			$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], true );
		}
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'cc.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'cc.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'cc.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'cc.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['sex_id'] ) ) ? $this->getWhereClauseSQL( 'cc.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'bb.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'bb.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		//$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => TTUUID::castUUID($company_id), 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower( trim( $filter_data['start_date'] ) ) );
			$query .= ' AND bb.transaction_date >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower( trim( $filter_data['end_date'] ) ) );
			$query .= ' AND bb.transaction_date <= ?';
		}
		/*
		if ( isset($filter_data['transaction_date']) AND !is_array($filter_data['transaction_date']) AND trim($filter_data['transaction_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_date'])) );
			$query	.=	' AND bb.transaction_date = ?';
		}
		*/

		$query .= '
								AND (aa.deleted = 0 AND bb.deleted = 0 AND cc.deleted=0)
							) a
						LEFT JOIN ' . $psf->getTable() . ' as b ON a.pay_stub_id = b.id
						LEFT JOIN ' . $pseaf->getTable() . ' as pseaf ON a.pay_stub_entry_name_id = pseaf.id
						LEFT JOIN ' . $uf->getTable() . ' as c ON b.user_id = c.id
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON b.pay_period_id = ppf.id
					where	1=1
					';

		//Don't group line items as that causes "net" accounting, which works, but can be frowned upon.
		//							group by aa.pay_stub_id, aa.pay_stub_entry_name_id

		$query .= '
						AND ( c.deleted=0 AND pseaf.deleted = 0 )
					';
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
	 * @return bool|PayStubEntryListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$strict = true;
		if ( $order == null ) {
			$strict = false;

			$order = [ 'b.ps_order' => 'asc', 'abs(a.ytd_amount)' => 'asc', 'a.id' => 'asc' ];
		}

		$uf = new UserFactory();
		$psf = new PayStubFactory();
		$psealf = new PayStubEntryAccountListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							b.type_id,
							b.name,
							b.id as pay_stub_entry_account_id 
					FROM	' . $this->getTable() . ' as a
						LEFT JOIN ' . $psealf->getTable() . ' as b ON ( a.pay_stub_entry_name_id = b.id AND b.deleted = 0 )
						LEFT JOIN ' . $psalf->getTable() . ' as c ON ( a.pay_stub_amendment_id = c.id AND c.deleted = 0 ) ';

		if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
			$uef = new UserExpenseFactory();
			$query .= ' LEFT JOIN ' . $uef->getTable() . ' as uef ON ( a.user_expense_id = uef.id AND uef.deleted = 0 ) ';
		}

		$query .= '
						LEFT JOIN ' . $psf->getTable() . ' as d ON ( a.pay_stub_id = d.id AND d.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( d.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = ?';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_stub_id', $filter_data['pay_stub_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_entry_name_id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['pay_stub_entry_name_id'], 'uuid_list', $ph ) : null;

		if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
			$query .= ( isset( $filter_data['user_expense_id'] ) ) ? $this->getWhereClauseSQL( 'uef.id', $filter_data['user_expense_id'], 'uuid_list', $ph ) : null;
		}

		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'b.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['name'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'd.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'd.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}


}

?>
