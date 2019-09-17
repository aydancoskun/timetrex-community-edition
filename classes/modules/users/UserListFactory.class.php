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
 * @package Modules\Users
 */
class UserListFactory extends UserFactory implements IteratorAggregate {

	/**
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'company_id' => 'asc', 'status_id' => '= 10 desc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	/**
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getByStatus( $status, $where = NULL, $order = NULL) {
		$ph = array(
			'status_id' => $status,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getUniqueCountryByCompanyId( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
			'id' => TTUUID::castUUID($id),
		);


		$query = '
					select	distinct a.country
					from	'. $uf->getTable() .' as a
					where	a.company_id = ?
						AND ( a.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		return $this->db->GetCol( $query, $ph );
	}

	/**
	 * @param string $company_id UUID
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getByCompanyIdAndStatus( $company_id, $status, $where = NULL, $order = NULL) {
		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'status_id' => $status,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	static function getFullNameById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$ulf = new UserListFactory();
		$ulf = $ulf->getById( $id );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent();
			return $u_obj->getFullName();
		}

		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool|UserListFactory
	 */
	function getById( $id) {
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

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND	id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		//This supports a list of IDs, so we need to make sure paging is also available.
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByLegalEntityIdAndCompanyId( $id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND	legal_entity_id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//This supports a list of IDs, so we need to make sure paging is also available.
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * Security measure, only returns user_ids that are valid for the specific company.
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	function getCompanyValidUserIds( $id, $company_id ) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	id
					from	'. $this->getTable() .'
					where company_id = ?
						AND id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';

		//This supports a list of IDs, so we need to make sure paging is also available.
		return $this->db->GetCol( $query, $ph );
	}

	/**
	 * @param $user_name
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByUserName( $user_name, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		$ph = array(
					'user_name' => strtolower( trim( $user_name ) ),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_name = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}


	/**
	 * @param $email
	 * @return bool|UserListFactory
	 */
	function getByHomeEmailOrWorkEmail( $email ) {
		$email = trim(strtolower($email));

		if ( $email == '') {
			return FALSE;
		}

		if ( $this->Validator->isEmail('email', $email ) == FALSE ) {
			return FALSE;
		}

		$cf = new CompanyFactory();

		$ph = array(
			'home_email' => $email,
			'work_email' => $email,
		);

		//Only return users of active companies and active users, as they are the only ones that can login anyways.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN	'. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
					where cf.status_id = 10
						AND a.status_id = 10
						AND ( lower(a.home_email) = ? OR lower(a.work_email) = ? )
						AND a.deleted = 0';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $key
	 * @return bool|UserListFactory
	 */
	function getByEmailIsValidKey( $key ) {
		$key = trim($key);

		if ( $this->Validator->isRegEx('email', $key, NULL, '/^[a-z0-9]{40}$/i' ) == FALSE ) {
			return FALSE;
		}

		$ph = array(
			'key1' => $key,
			'key2' => $key,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						( work_email_is_valid_key = ? OR home_email_is_valid_key = ? )
						AND deleted = 0';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $key
	 * @return bool|UserListFactory
	 */
	function getByPasswordResetKey( $key ) {
		$key = trim($key);

		if ( $this->Validator->isRegEx('key', $key, NULL, '/^[a-z0-9]{40}$/i' ) == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'key' => $this->encryptPasswordResetKey( $key ),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						password_reset_key = ?
						AND deleted = 0';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $user_name
	 * @param string $company_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByUserNameAndCompanyId( $user_name, $company_id, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
					'user_name' => strtolower( trim( $user_name ) ),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND user_name = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $user_name
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByUserNameAndStatus( $user_name, $status, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		$ph = array(
			'user_name' => strtolower( trim( $user_name ) ),
			'status' => $status,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_name = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $phone_id UUID
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByPhoneIdAndStatus( $phone_id, $status, $where = NULL, $order = NULL) {
		if ( $phone_id == '') {
			return FALSE;
		}

		$ph = array(
			'phone_id' => $phone_id,
			'status' => (int)$status,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	phone_id = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByIdAndStatus( $id, $status, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
			'id' => TTUUID::castUUID($id),
			'status' => $status,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCurrencyID( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
			'id' => TTUUID::castUUID($id),
		);


		$query = '
					select	*
					from	'. $this->getTable() .'
					where	currency_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $level
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndPermissionLevel( $company_id, $level, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $level == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'level' => $level,
		);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN
					(
						SELECT g2.*, g1.user_id
						FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
						WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
					) as g ON ( a.id = g.user_id )
					where	a.company_id = ?
						AND g.level = ?
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndGroupID( $company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'id' => TTUUID::castUUID($id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND group_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $employee_number
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndEmployeeNumber( $company_id, $employee_number, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $employee_number == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'employee_number' => $employee_number,
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND employee_number = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $station_id UUID
	 * @param int $status_id
	 * @param int $date EPOCH
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndStationIDAndStatusAndDate( $company_id, $station_id, $status_id, $date = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'station_id' => TTUUID::castUUID($station_id),
			'status_id' => (int)$status_id,
			//'date' => $date,
			//'date2' => $date,
		);

		$query = '
					select	_ADODB_COUNT
						a.*
						_ADODB_COUNT
					from	'. $this->getTable() .' as a,
							'. $sf->getTable() .' as z
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND
							(
								(
									(
										z.user_group_selection_type_id = 10
											OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
									)
									AND
									(
										z.branch_selection_type_id = 10
											OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
									)
									AND
									(
										z.department_selection_type_id = 10
											OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
									)
									AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
								)
								OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
							)';

		if ( isset($date) AND $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = $date;
			$ph[] = $date;
			$query	.=	' AND ( a.created_date >= ? OR a.updated_date >= ? )';
			unset($date_filter);
		}

		$query .= ' AND ( a.deleted = 0 AND z.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	/**
	 * @param string $company_id UUID
	 * @param string $station_id UUID
	 * @param int $status_id
	 * @param int $date EPOCH
	 * @param array $valid_user_ids
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndStationIDAndStatusAndDateAndValidUserIDs( $company_id, $station_id, $status_id, $date = NULL, $valid_user_ids = array(), $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();
		$uif = new UserIdentificationFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'station_id' => TTUUID::castUUID($station_id),
			'status_id' => (int)$status_id,
			//'date' => $date,
			//'date2' => $date,
		);

		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		//Make sure we return distinct user rows so there aren't duplicates.
		$query = '
					select	distinct a.*
					from	'. $this->getTable() .' as a

					LEFT JOIN '. $sf->getTable() .' as z ON (1=1)
					LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND
							(
								(
									(
										(
											(
												z.user_group_selection_type_id = 10
													OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											)
											AND
											(
												z.branch_selection_type_id = 10
													OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											)
											AND
											(
												z.department_selection_type_id = 10
													OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											)
											AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
										)
										OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
									)

							';

		if ( isset($date) AND $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$query	.=	'		AND ( a.created_date >= ? OR a.updated_date >= ? OR uif.created_date >= ? )
								)';
		} else {
			$query	.=	'	)';
		}

		if ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) {
			$query	.=	' OR a.id in ('. $this->getListSQL( $valid_user_ids, $ph, 'uuid') .') ';
		}

		$query .= '			)
						AND ( a.deleted = 0 AND z.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $station_id UUID
	 * @param int $status_id
	 * @param $employee_number
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIDAndStationIDAndStatusAndEmployeeNumber( $company_id, $station_id, $status_id, $employee_number, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();
		//$uif = new UserIdentificationFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'station_id' => TTUUID::castUUID($station_id),
			'status_id' => (int)$status_id,
			'employee_number' => $employee_number,
			//'date' => $date,
			//'date2' => $date,
		);

		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		$query = '
					select	_ADODB_COUNT
						a.*
						_ADODB_COUNT
					from	'. $this->getTable() .' as a

					LEFT JOIN '. $sf->getTable() .' as z ON (1=1)
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND a.employee_number = ?
						AND
							(
								(
									(
										(
											(
												z.user_group_selection_type_id = 10
													OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											)
											AND
											(
												z.branch_selection_type_id = 10
													OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											)
											AND
											(
												z.department_selection_type_id = 10
													OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											)
											AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
										)
										OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
									)
								)
							)
							';

		$query .= '	AND ( a.deleted = 0 AND z.deleted = 0 )';

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyId( $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $longitude
	 * @param $latitude
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getByCompanyIdAndLongitudeAndLatitude( $company_id, $longitude, $latitude, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'longitude' => 'asc', 'latitude' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ? ';

		//isset() returns false on NULL.
		$query .= $this->getWhereClauseSQL( 'longitude', $longitude, 'numeric', $ph );
		$query .= $this->getWhereClauseSQL( 'latitude', $latitude, 'numeric', $ph );
		$query .= '	AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $include_blank
	 * @param bool $include_disabled
	 * @param bool $last_name_first
	 * @return bool
	 */
	static function getByCompanyIdArray( $company_id, $include_blank = TRUE, $include_disabled = TRUE, $last_name_first = TRUE ) {

		$ulf = new UserListFactory();
		$ulf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$user_list[TTUUID::getZeroID()] = '--';
		}

		foreach ($ulf as $user) {
			if ( $user->getStatus() > 10 ) { //INACTIVE
				$status = '('.Option::getByKey( $user->getStatus(), $user->getOptions('status') ).') ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $user->getStatus() == 10 ) ) {
				$user_list[$user->getID()] = $status.$user->getFullName($last_name_first);
			}
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return FALSE;
	}

	/**
	 * @param $lf
	 * @param bool $include_blank
	 * @param bool $include_disabled
	 * @return bool
	 */
	static function getArrayByListFactory( $lf, $include_blank = TRUE, $include_disabled = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ($lf as $obj) {
			if ( !isset($status_options) ) {
				$status_options = $obj->getOptions('status');
			}

			if ( $obj->getStatus() > 10 ) { //INACTIVE
				$status = '('.Option::getByKey( $obj->getStatus(), $status_options ).') ';
				//$status = '(INACTIVE) ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $obj->getStatus() == 10 ) ) {
				$list[$obj->getID()] = $status.$obj->getFullName(TRUE);
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $date EPOCH
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getDeletedByCompanyIdAndDate( $company_id, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'created_date' => $date,
			'updated_date' => $date,
			'deleted_date' => $date,
		);

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	'. $this->getTable() .'
					where
							company_id = ?
						AND
							( created_date >= ? OR updated_date >= ? OR deleted_date >= ? )
						AND deleted = 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = $this->ExecuteSQL( $query, $ph );
		} else {
			$this->rs = $this->db->PageExecute($query, (int)$limit, (int)$page, $ph);

		}

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $date EPOCH
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getIsModifiedByCompanyIdAndDate( $company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'created_date' => $date,
			'updated_date' => $date,
			'uif_created_date' => $date,
			'deleted_date' => $date,
		);

		$uif = new UserIdentificationFactory();

		//INCLUDE Deleted rows in this query.
		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
					where
							a.company_id = ?
						AND
							( a.created_date >= ? OR a.updated_date >= ? OR uif.created_date >= ? OR ( a.deleted = 1 AND a.deleted_date >= ? ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->SelectLimit($query, 1, -1, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('User rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getHighestEmployeeNumberByCompanyId( $id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
			'id' => TTUUID::castUUID($id),
			'id2' => TTUUID::castUUID($id),
		);

		//employee_number is a varchar field, so we can't reliably cast it to an integer
		//however if we left pad it, we can get a similar effect.
		//Eventually we can change it to an integer field.
		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND id = ( select id
									from '. $this->getTable() .'
									where company_id = ?
										AND employee_number != \'\'
										AND employee_number IS NOT NULL
										AND deleted = 0
									ORDER BY LPAD( employee_number, 10, \'0\' ) DESC
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
	 * @param string $user_ids UUID
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserListFactory
	 */
	function getReportByCompanyIdAndUserIDList( $company_id, $user_ids, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_ids == '') {
			return FALSE;
		}
		/*
				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}
		*/

		//		$utf = new UserTaxFactory();
		//					LEFT JOIN '. $utf->getTable() .' as b ON a.id = b.user_id AND (b.deleted=0 OR b.deleted IS NULL)
		$baf = new BankAccountFactory();

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					select	c.*, a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $baf->getTable() .' as c ON a.id = c.user_id AND (c.deleted=0 OR c.deleted IS NULL)
					where
						a.company_id = ?
						AND a.id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .')
						AND ( a.deleted = 0 )
				';
		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param null $filter_data
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAPIEmailAddressDataByArrayCriteria( $filter_data = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		$ph = array();

		$cf = new CompanyFactory();
		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$query = '
					select
							cf.id as company_id,
							cf.parent_id as company_parent_id,
							cf.name as company_name,
							cf.status_id as company_status_id,
							g.level as permission_level,
							cf.created_date as company_created_date,
							cf.updated_date as company_updated_date,
							h.company_last_login_date,
							a.id,
							a.status_id,
							a.first_name,
							a.last_name,
							a.user_name,
							a.country,
							a.province,
							a.work_email,
							a.home_email,
							a.birth_date,
							a.hire_date,
							a.termination_date,
							a.created_date,
							a.last_login_date
					from	'. $this->getTable() .' as a
					LEFT JOIN	'. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
					LEFT JOIN
					(
						SELECT g2.*, g1.user_id
						FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
						WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
					) as g ON ( a.id = g.user_id )
					LEFT JOIN
					(
					SELECT company_id, max(last_login_date) as company_last_login_date FROM '. $this->getTable() .' as h1 WHERE h1.deleted = 0 GROUP BY h1.company_id
					) as h ON ( a.company_id = h.company_id )
					where ( 1 = 1 )
				';

		$query .= ( isset($filter_data['company_id']) ) ? $this->getWhereClauseSQL( 'a.company_id', $filter_data['company_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['company_status_id']) ) ? $this->getWhereClauseSQL( 'cf.status_id', $filter_data['company_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['permission_level']) ) ? $this->getWhereClauseSQL( 'g.level', $filter_data['permission_level'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['product_edition_id']) ) ? $this->getWhereClauseSQL( 'cf.product_edition_id', $filter_data['product_edition_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['created_date']) AND !is_array($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		if ( isset($filter_data['last_login_date']) AND !is_array($filter_data['last_login_date']) AND trim($filter_data['last_login_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['last_login_date'], 'a.last_login_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		if ( isset($filter_data['updated_date']) AND !is_array($filter_data['updated_date']) AND trim($filter_data['updated_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['updated_date'], 'a.updated_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		$query .= $this->getSortSQL( $order, FALSE );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * Return user records based on advanced filter criteria.
	 *
	 * @param int $company_id Company ID
	 * @param array $filter_data Filter criteria in array('id' => array(1, 2), 'last_name' => 'smith' ) format, with possible top level array keys as follows: id, exclude_id, status_id, user_group_id, default_branch_id, default_department_id, title_id, currency_id, permission_control_id, pay_period_schedule_id, policy_group_id, sex_id, first_name, last_name, home_phone, work_phone, country, province, city, address1, address2, postal_code, employee_number, user_name, sin, work_email, home_email, tag, last_login_date, created_by, created_date, updated_by, updated_date
	 * @param int $limit Optional. Restrict the number of records returned
	 * @param int $page Optional. Specify the page of records to return
	 * @param array $where Optional. Additional WHERE clauses in array( 'column' => 'value', 'column' => 'value' ) format.
	 * @param array $order Optional. Sort order in array( 'column' => ASC, 'column2' => DESC ) format.
	 * @param bool $include_last_punch_time
	 * @return bool|object $this
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL, $include_last_punch_time = FALSE ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		if ( isset($filter_data['user_status_id']) ) {
			$filter_data['status_id'] = $filter_data['user_status_id'];
		}

		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['exclude_user_id']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		//Some of these are passed from Flex Schedule view.
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}

		if ( isset($filter_data['group_id']) ) {
			$filter_data['user_group_id'] = $filter_data['group_id'];
		}

		if ( isset($filter_data['user_title_id']) ) {
			$filter_data['title_id'] = $filter_data['user_title_id'];
		}

		if ( isset($filter_data['user_tag']) ) {
			$filter_data['tag'] = $filter_data['user_tag'];
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array(	'default_branch',
			'default_department',
			'default_job',
			'default_job_item',
			'sex',
			'user_group',
			'title',
			'currency',
			'permission_control',
			'pay_period_schedule',
			'policy_group',
		);

		if ( $include_last_punch_time == TRUE ) {
			$additional_order_fields[] = 'max_punch_time_stamp';
		}

		$sort_column_aliases = array(
			'type' => 'type_id',
			'status' => 'status_id',
			'sex' => 'sex_id',
			'full_name' => 'last_name',
		);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.

			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name, first name after other columns
			if ( !isset($order['last_name']) ) {
				$order['last_name'] = 'asc';
			}
			if ( !isset($order['first_name']) ) {
				$order['first_name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$compf = new CompanyFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();
		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$egf = new EthnicGroupFactory();
		$lef = new LegalEntityFactory();

		$punchf = new PunchFactory();
		$punchcf = new PunchControlFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
		);

		$query = '
					SELECT
							a.*,
							compf.name as company,
							b.name as default_branch,
							b.manual_id as default_branch_manual_id,
							c.name as default_department,
							c.manual_id as default_department_manual_id,
							d.name as user_group,
							e.name as title,
							f.name as currency,
							f.conversion_rate as currency_rate,
							g.id as permission_control_id,
							g.name as permission_control,
							h.id as pay_period_schedule_id,
							h.name as pay_period_schedule,
							i.id as policy_group_id,
							i.name as policy_group,
							lef.legal_name,
							lef.trade_name,
							egf.name as ethnic_group, ';

		$query .= Permission::getPermissionIsChildIsOwnerSQL( ( isset($filter_data['permission_current_user_id']) ) ? $filter_data['permission_current_user_id'] : TTUUID::getZeroID(), 'a.id' );

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	jf.name as default_job,
						jf.manual_id as default_job_manual_id,
						jif.name as default_job_item,
						jif.manual_id as default_job_item_manual_id, ';
		}

		if ( $include_last_punch_time == TRUE ) {
			$query .= '	punch.max_punch_time_stamp as max_punch_time_stamp, ';
		}

		//We need to use SUB-SELECTs when joining to permission_control/permission_user, pay_period_schedule/pay_period_schedule_user, policy_group/policy_group_user
		//Since the employee may not be assigned to any of them, or they may be assigned to ones that were already deleted, and a basic LEFT JOIN
		//will return multiple records for a single employee unless its done with a sub-select. This can be tested by created a new pay period schedule,
		//assigning the employee to it, then deleting the pay period schedule, doing that a few times. Then the LEFT JOIN will return one record for each pay period schedule that was created.
		$query .= '			y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					FROM	'. $this->getTable() .' as a
						LEFT JOIN '. $compf->getTable() .' as compf ON ( a.company_id = compf.id AND compf.deleted = 0)
						LEFT JOIN '. $bf->getTable() .' as b ON ( a.company_id = b.company_id AND a.default_branch_id = b.id AND b.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as c ON ( a.company_id = c.company_id AND a.default_department_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as d ON ( a.company_id = d.company_id AND a.group_id = d.id AND d.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as e ON ( a.company_id = e.company_id AND a.title_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as f ON ( a.company_id = f.company_id AND a.currency_id = f.id AND f.deleted = 0 )
						LEFT JOIN '. $lef->getTable() .' as lef ON ( a.company_id = lef.company_id AND a.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN '. $egf->getTable() .' as egf ON ( a.company_id = egf.company_id AND a.ethnic_group_id = egf.id AND egf.deleted = 0 )
						LEFT JOIN
						(
								SELECT g2.*, g1.user_id
								FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
								WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
						) as g ON ( a.id = g.user_id )
						LEFT JOIN
						(
								SELECT h2.*, h1.user_id
								FROM '. $ppsuf->getTable() .' as h1, '. $ppsf->getTable() .' as h2
								WHERE ( h1.pay_period_schedule_id = h2.id AND h2.deleted = 0)
						) as h ON ( a.id = h.user_id )
						LEFT JOIN
						(
								SELECT i2.*, i1.user_id
								FROM '. $pguf->getTable() .' as i1, '. $pgf->getTable() .' as i2
								WHERE ( i1.policy_group_id = i2.id AND i2.deleted = 0)
						) as i ON ( a.id = i.user_id )
						';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as jf ON ( a.company_id = jf.company_id AND a.default_job_id = jf.id AND jf.deleted = 0 )
						LEFT JOIN '. $jif->getTable() .' as jif ON ( a.company_id = jif.company_id AND a.default_job_item_id = jif.id AND jif.deleted = 0 ) ';
		}

		if ( $include_last_punch_time == TRUE ) {
			$query .= '
						LEFT JOIN
						(
								SELECT tmp2_d.id as user_id, max(tmp2_a.time_stamp) as max_punch_time_stamp
								FROM	'. $punchf->getTable() .' as tmp2_a
								LEFT JOIN '. $punchcf->getTable() .' as tmp2_b ON tmp2_a.punch_control_id = tmp2_b.id
								LEFT JOIN '. $this->getTable() .' as tmp2_d ON tmp2_b.user_id = tmp2_d.id
								WHERE tmp2_d.company_id = \''. TTUUID::castUUID($company_id) .'\'
									AND tmp2_d.status_id = 10
									AND tmp2_b.date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginDayEpoch( time() - (86400 * 31 ) ) ) ) .'
									AND tmp2_b.date_stamp <= '. $this->db->qstr( $this->db->BindDate( TTDate::getEndDayEpoch( time() ) ) ) .'
									AND tmp2_a.time_stamp IS NOT NULL
									AND ( tmp2_a.deleted = 0 AND tmp2_b.deleted = 0 )
								GROUP BY tmp2_d.id
						) as punch ON ( a.id = punch.user_id )
					';
		}

		$query .= Permission::getPermissionHierarchySQL( $company_id, ( isset($filter_data['permission_current_user_id']) ) ? $filter_data['permission_current_user_id'] : 0, 'a.id' );

		$query .= '
						LEFT JOIN '. $this->getTable() .' as y ON ( a.company_id = y.company_id AND a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $this->getTable() .' as z ON ( a.company_id = z.company_id AND a.updated_by = z.id AND z.deleted = 0 )
					WHERE	a.company_id = ?
					';

		$query .= Permission::getPermissionIsChildIsOwnerFilterSQL( $filter_data, 'a.id' );
		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND !is_array($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'a.group_id', $filter_data['user_group_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_group']) ) ? $this->getWhereClauseSQL( 'd.name', $filter_data['user_group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['legal_entity_id']) ) ? $this->getWhereClauseSQL( 'a.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['legal_name']) ) ? $this->getWhereClauseSQL( 'lef.legal_name', $filter_data['legal_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'a.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch']) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['default_branch'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'a.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department']) ) ? $this->getWhereClauseSQL( 'c.name', $filter_data['default_department'], 'text', $ph ) : NULL;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset($filter_data['default_job_id']) ) ? $this->getWhereClauseSQL( 'a.default_job_id', $filter_data['default_job_id'], 'uuid_list', $ph ) : NULL;
			$query .= ( isset($filter_data['default_job']) ) ? $this->getWhereClauseSQL( 'jf.name', $filter_data['default_job'], 'text', $ph ) : NULL;

			$query .= ( isset($filter_data['default_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.default_job_item_id', $filter_data['default_job_item_id'], 'uuid_list', $ph ) : NULL;
			$query .= ( isset($filter_data['default_job_item']) ) ? $this->getWhereClauseSQL( 'jif.name', $filter_data['default_job_item'], 'text', $ph ) : NULL;
		}

		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'a.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title']) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['title'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['ethnic_group_id']) ) ? $this->getWhereClauseSQL( 'a.ethnic_group_id', $filter_data['ethnic_group_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['ethnic_group']) ) ? $this->getWhereClauseSQL( 'egf.name', $filter_data['ethnic_group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['currency_id']) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['currency']) ) ? $this->getWhereClauseSQL( 'f.name', $filter_data['currency'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['permission_control_id']) ) ? $this->getWhereClauseSQL( 'g.id', $filter_data['permission_control_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['permission_control']) ) ? $this->getWhereClauseSQL( 'g.name', $filter_data['permission_control'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_schedule_id']) ) ? $this->getWhereClauseSQL( 'h.id', $filter_data['pay_period_schedule_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_schedule']) ) ? $this->getWhereClauseSQL( 'h.name', $filter_data['pay_period_schedule'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['policy_group_id']) ) ? $this->getWhereClauseSQL( 'i.id', $filter_data['policy_group_id'], 'uuid_list', $ph ) : NULL;
		$query .= ( isset($filter_data['policy_group']) ) ? $this->getWhereClauseSQL( 'i.name', $filter_data['policy_group'], 'text', $ph ) : NULL;

		if ( isset($filter_data['sex']) AND !is_array($filter_data['sex']) AND trim($filter_data['sex']) != '' AND !isset($filter_data['sex_id']) ) {
			$filter_data['sex_id'] = Option::getByFuzzyValue( $filter_data['sex'], $this->getOptions('sex') );
		}
		$query .= ( isset($filter_data['sex_id']) ) ? $this->getWhereClauseSQL( 'a.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'a.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['full_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['full_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['home_phone']) ) ? $this->getWhereClauseSQL( 'a.home_phone', $filter_data['home_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['any_phone']) ) ? $this->getWhereClauseSQL( array('a.work_phone', 'a.home_phone', 'a.mobile_phone' ), $filter_data['any_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ? $this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['employee_number']) AND !is_array($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) ? $this->getWhereClauseSQL( 'a.employee_number', $filter_data['employee_number'], 'numeric', $ph ) : NULL;
		$query .= ( isset($filter_data['user_name']) ) ? $this->getWhereClauseSQL( 'a.user_name', $filter_data['user_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['sin']) ) ? $this->getWhereClauseSQL( 'a.sin', $filter_data['sin'], 'numeric_string', $ph ) : NULL;

		$query .= ( isset($filter_data['email']) AND !is_array($filter_data['email']) AND $filter_data['email'] != '' ) ? 'AND ('. $this->getWhereClauseSQL( 'a.work_email', $filter_data['email'], 'text', $ph, NULL, FALSE ).' OR '. $this->getWhereClauseSQL( 'a.home_email', $filter_data['email'], 'text', $ph, NULL, FALSE ) .')' : NULL;
		$query .= ( isset($filter_data['work_email']) ) ? $this->getWhereClauseSQL( 'a.work_email', $filter_data['work_email'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['home_email']) ) ? $this->getWhereClauseSQL( 'a.home_email', $filter_data['home_email'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['any_email']) ) ? $this->getWhereClauseSQL( array('a.work_email', 'a.home_email' ), $filter_data['any_email'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => TTUUID::castUUID($company_id), 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		//$query .= ( isset($filter_data['longitude']) ) ? $this->getWhereClauseSQL( 'a.longitude', $filter_data['longitude'], 'numeric', $ph ) : NULL;

		//Basic start/end dates assume that the employee is *employed* for the entire date range.
		//Use employed_start/end_date rather than just start/end_date to prevents other reports that use start/end_date from conflicting with this.
		//As a time period selection of "all years" will always return no results.
		if ( isset($filter_data['employed_start_date']) AND (int)$filter_data['employed_start_date'] != 0 ) {
			$query .= ' AND ( a.hire_date IS NULL OR a.hire_date <= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['employed_start_date'] ) ) .' ) ';
		}
		if ( isset($filter_data['employed_end_date']) AND (int)$filter_data['employed_end_date'] != 0 ) {
			$query .= ' AND ( a.termination_date IS NULL OR a.termination_date >= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['employed_end_date'] ) ) .' ) ';
		}

		//employed_[start|end]_date requires the employee to be employed the entire time. This just requires them to be employed for any part of the range.
		if ( isset($filter_data['partial_employed_end_date']) AND (int)$filter_data['partial_employed_end_date'] != 0 ) {
			$query .= ' AND ( a.hire_date IS NULL OR a.hire_date <= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['partial_employed_end_date'] ) ) .' ) ';
		}
		if ( isset($filter_data['partial_employed_start_date']) AND (int)$filter_data['partial_employed_start_date'] != 0 ) {
			$query .= ' AND ( a.termination_date IS NULL OR a.termination_date >= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['partial_employed_start_date'] ) ) .' ) ';
		}

		if ( isset($filter_data['hire_start_date']) AND (int)$filter_data['hire_start_date'] != 0 AND isset($filter_data['hire_end_date']) AND (int)$filter_data['hire_end_date'] != 0 ) {
			$query .= ' AND ( a.hire_date >= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['hire_start_date'] ) ) .' AND a.hire_date <= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['hire_end_date'] ) ) .' ) ';
		}
		if ( isset($filter_data['termination_start_date']) AND (int)$filter_data['termination_start_date'] != 0 AND isset($filter_data['termination_end_date']) AND (int)$filter_data['termination_end_date'] != 0 ) {
			$query .= ' AND ( a.termination_date >= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['termination_start_date'] ) ) .' AND a.termination_date <= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['termination_end_date'] ) ) .' ) ';
		}

		if ( isset($filter_data['birth_start_date']) AND (int)$filter_data['birth_start_date'] != 0 AND isset($filter_data['birth_end_date']) AND (int)$filter_data['birth_end_date'] != 0 ) {
			$query .= ' AND ( a.birth_date >= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['birth_start_date'] ) ) .' AND a.birth_date <= '. $this->db->qstr( $this->db->BindDate( (int)$filter_data['birth_end_date'] ) ) .' ) ';
		}
		if ( isset($filter_data['last_login_start_date']) AND (int)$filter_data['last_login_start_date'] != 0 AND isset($filter_data['last_login_end_date']) AND (int)$filter_data['last_login_end_date'] != 0 ) {
			$query .= ' AND ( a.last_login_date >= '. (int)$filter_data['last_login_start_date'].' AND a.last_login_date <= '. (int)$filter_data['last_login_end_date'].' ) ';
		}
		if ( isset($filter_data['password_start_date']) AND (int)$filter_data['password_start_date'] != 0 AND isset($filter_data['password_end_date']) AND (int)$filter_data['password_end_date'] != 0 ) {
			$query .= ' AND ( a.password_updated_date >= '. (int)$filter_data['password_start_date'].' AND a.password_updated_date <= '. (int)$filter_data['password_end_date'].' ) ';
		}


		if ( isset($filter_data['last_login_date']) AND !is_array($filter_data['last_login_date']) AND trim($filter_data['last_login_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['last_login_date'], 'a.last_login_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		if ( isset($filter_data['created_date']) AND !is_array($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}
		if ( isset($filter_data['updated_date']) AND !is_array($filter_data['updated_date']) AND trim($filter_data['updated_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['updated_date'], 'a.updated_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	'
						AND ( a.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

}
?>