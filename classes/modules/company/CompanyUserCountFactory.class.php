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
class CompanyUserCountFactory extends Factory {
	protected $table = 'company_user_count';
	protected $pk_sequence_name = 'company_user_count_id_seq'; //PK Sequence name

	/**
	 * @return mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getDateStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getActiveUsers() {
		return $this->getGenericDataValue( 'active_users' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setActiveUsers( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'active_users', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getInActiveUsers() {
		return $this->getGenericDataValue( 'inactive_users' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInActiveUsers( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'inactive_users', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDeletedUsers() {
		return $this->getGenericDataValue( 'deleted_users' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDeletedUsers( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'deleted_users', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' );
			$this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($this->getCompany()),
															TTi18n::gettext('Company is invalid')
														);
		}
		// Date
		$this->Validator->isDate(		'date_stamp',
												$this->getDateStamp(),
												TTi18n::gettext('Incorrect date')
											);
		if ( $this->Validator->isError('date_stamp') == FALSE ) {
			if ( $this->getDateStamp() <= 0 ) {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date')
											);
			}
		}
		// Active users
		$this->Validator->isNumeric(	'active_users',
												$this->getActiveUsers(),
												TTi18n::gettext('Incorrect value')
											);
		// Inactive users
		$this->Validator->isNumeric(	'inactive_users',
												$this->getInActiveUsers(),
												TTi18n::gettext('Incorrect value')
											);
		// Deleted Users
		$this->Validator->isNumeric(	'deleted_users',
												$this->getDeletedUsers(),
												TTi18n::gettext('Incorrect value')
											);
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//$this->removeCache( $this->getId() );

		return TRUE;
	}

	//This table doesn't have any of these columns, so overload the functions.

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

}
?>
