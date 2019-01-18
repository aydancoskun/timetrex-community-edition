<?php
/**
 * $License$
 */

/**
 * @package Modules\Company
 */
class PayrollRemittanceAgencyEventListFactory extends PayrollRemittanceAgencyEventFactory implements IteratorAggregate {

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
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayrollRemittanceAgencyEventListFactory
	 */
	function getById( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
				'id' => TTUUID::castUUID($id),
			);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayrollRemittanceAgencyEventListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$lef = new LegalEntityFactory();
		$praf = new PayrollRemittanceAgencyFactory();

		$ph = array(
			'id' => TTUUID::castUUID($id),
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN	'. $praf->getTable() .' as b ON ( a.payroll_remittance_agency_id = b.id )
						LEFT JOIN	'. $lef->getTable() .' as c ON ( b.legal_entity_id = c.id )
					where	a.id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0  AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );


		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayrollRemittanceAgencyEventListFactory
	 */
	function getByLegalEntityIdAndCompanyId( $id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$lef = new LegalEntityFactory();
		$praf = new PayrollRemittanceAgencyFactory();

		$ph = array(
			'legal_entity_id' => TTUUID::castUUID($id),
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN	'. $praf->getTable() .' as b ON ( a.payroll_remittance_agency_id = b.id )
						LEFT JOIN	'. $lef->getTable() .' as c ON ( b.legal_entity_id = b.id )
					where	b.legal_entity_id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );


		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * Return all records that need reminders sent.
	 *
	 * WHERE end of day today is past next_reminder_date and last_reminder_date is not equal to next_reminder_date
	 *
	 * WHEN the reminder is sent:
	 * 	  we expect last_reminder_date == next_reminder date;
	 *    update last_reminder date = next_reminder_date
	 *
	 * The process must be resumable.
	 */
	function getPendingReminder($company_id, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'next_reminder_date' => $this->db->BindTimeStamp( time() ),
		);

		$praf = new PayrollRemittanceAgencyFactory();
		$lef = new LegalEntityFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN	'. $praf->getTable() .' as praf ON ( a.payroll_remittance_agency_id = praf.id AND praf.deleted = 0 )
						LEFT JOIN '. $lef->getTable() .' as lef ON ( praf.legal_entity_id = lef.id AND lef.deleted = 0 )
					where	
						lef.company_id = ?
						AND a.status_id = 10
						AND ( a.next_reminder_date IS NOT NULL AND a.next_reminder_date <= ? )
						AND (a.last_reminder_date IS NULL OR a.last_reminder_date < a.next_reminder_date)
						AND ( a.deleted = 0 AND praf.deleted = 0 AND lef.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );


		$this->ExecuteSQL( $query, $ph );
		//Debug::Arr($ph, 'SQL: '.$query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param $company_id
	 * @param $frequency_id
	 * @param null $where
	 * @param null $order
	 * @return $this|bool
	 */
	function getByCompanyIdAndFrequencyIdAndDueDateIsNull ($company_id, $frequency_id,  $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
				'company_id' => TTUUID::castUUID($company_id),
				//'frequency_id' => (int)$frequency_id, //not a uuid.
		);

		$praf = new PayrollRemittanceAgencyFactory();
		$lef = new LegalEntityFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN	'. $praf->getTable() .' as praf ON ( a.payroll_remittance_agency_id = praf.id AND praf.deleted = 0 )
						LEFT JOIN '. $lef->getTable() .' as lef ON ( praf.legal_entity_id = lef.id AND lef.deleted = 0 )
					where	
						lef.company_id = ?
						AND a.status_id = 10
						AND a.frequency_id in ('. $this->getListSQL( $frequency_id, $ph, 'int' ) .')
						AND a.due_date IS NULL
						AND ( a.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayrollRemittanceAgencyEventListFactory
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

		$additional_order_fields = array('created_date');

		$sort_column_aliases = array(
			'status' => 'a.status_id',
			'type' => 'a.type_id',
			'due_date' => 'a.due_date',
			'legal_entity_id' => 'lef.id',
		);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'praf.legal_entity_id' => 'asc', 'a.type_id' => 'asc', 'a.created_date' => 'asc' );
			$strict = FALSE;
		} else {
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('a.status_id' => 'asc'), $order );
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$cdf = new CompanyDeductionFactory();
		$praf = new PayrollRemittanceAgencyFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	a.*,
							_ADODB_COUNT
							(
								CASE WHEN EXISTS ( select 1 from '. $cdf->getTable() .' as z where z.payroll_remittance_agency_id = a.id and z.deleted = 0 )
										THEN 1 ELSE 0
										END
							) as in_use,
							uf.first_name,
							uf.last_name,
							lef.legal_name as legal_entity_legal_name,
							praf.name as payroll_remittance_agency_name,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN	'. $praf->getTable() .' as praf ON ( a.payroll_remittance_agency_id = praf.id )
						LEFT JOIN '. $lef->getTable() .' as lef ON ( praf.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as uf ON ( a.reminder_user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	lef.company_id = ?';

		$query .= ( isset($filter_data['payroll_remittance_agency_id']) ) ? $this->getWhereClauseSQL( 'praf.id', $filter_data['payroll_remittance_agency_id'], 'uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : NULL;

		$query .= ( isset($filter_data['payroll_remittance_agency_status_id']) ) ? $this->getWhereClauseSQL( 'praf.status_id', $filter_data['payroll_remittance_agency_status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.start_date', $filter_data['start_date'], 'end_timestamp', $ph ) : NULL;//must be of type end_timestamp to ensure we get a <= in the where clause
		$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.end_date', $filter_data['end_date'], 'end_timestamp', $ph ) : NULL;

		$query .= ( isset($filter_data['created_date']) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date'], 'date_range', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_date']) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date'], 'date_range', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND ( a.deleted = 0 AND praf.deleted = 0 AND lef.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

}
?>
