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
class PermissionUserFactory extends Factory {
	protected $table = 'permission_user';
	protected $pk_sequence_name = 'permission_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $permission_control_obj = NULL;

	/**
	 * @return bool
	 */
	function getPermissionControlObject() {
		return $this->getGenericObject( 'PermissionControlListFactory', $this->getPermissionControl(), 'permission_control_obj' );
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return mixed
	 */
	function getPermissionControl() {
		return TTUUID::castUUID($this->getGenericDataValue( 'permission_control_id' ));
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPermissionControl( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'permission_control_id', $value );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqueUser( $id) {
		$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);

		$query = 'select a.id from '. $this->getTable() .' as a, '. $pclf->getTable() .' as b where a.permission_control_id = b.id AND a.user_id = ? AND b.deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id, 'Unique User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_id === FALSE ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return TTUUID::castUUID($this->getGenericDataValue( 'user_id' ));
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value) {
		$value = TTUUID::castUUID( $value );
		if ( $value != TTUUID::getZeroID() ) {
			return $this->setGenericDataValue( 'user_id', $value );
		}
		return FALSE;
	}

	/**
	 * This table doesn't have any of these columns, so overload the functions.
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Permission Group
		if ( $this->getPermissionControl() == TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'permission_control',
															$pclf->getByID($this->getPermissionControl()),
															TTi18n::gettext('Permission Group is invalid')
														);
		}
		// Employee
		if ( $this->getUser() !== FALSE AND $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Selected Employee is invalid')
														);
			if ( $this->Validator->isError('user') == FALSE ) {
				$this->Validator->isTrue(		'user',
														$this->isUniqueUser($this->getUser()),
														TTi18n::gettext('Selected Employee is already assigned to another Permission Group')
													);
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return TRUE;
	}
	/**
	 * @return bool
	 */
	function getDeleted() {
		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = NULL) {
		return FALSE;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = NULL) {
		return FALSE;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getPermissionControl(), $log_action, TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ), NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
