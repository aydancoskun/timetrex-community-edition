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
class StationBranchFactory extends Factory {
	protected $table = 'station_branch';
	protected $pk_sequence_name = 'station_branch_id_seq'; //PK Sequence name

	var $branch_obj = null;

	/**
	 * @return mixed
	 */
	function getStation() {
		return $this->getGenericDataValue( 'station_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setStation( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'station_id', $value );
	}

	/**
	 * @return bool|null
	 */
	function getBranchObject() {
		if ( is_object( $this->branch_obj ) ) {
			return $this->branch_obj;
		} else {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$blf->getById( $this->getBranch() );
			if ( $blf->getRecordCount() == 1 ) {
				$this->branch_obj = $blf->getCurrent();

				return $this->branch_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setBranch( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * This table doesn't have any of these columns, so overload the functions.
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Station
		if ( $this->getStation() != TTUUID::getZeroID() ) {
			$this->Validator->isUUID( 'station',
									  $this->getStation(),
									  TTi18n::gettext( 'Selected Station is invalid' )
			/*
							$this->Validator->isResultSetWithRows(	'station',
																$slf->getByID($id),
																TTi18n::gettext('Selected Station is invalid')
			*/
			);
		}
		// Branch
		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$this->Validator->isResultSetWithRows( 'branch',
											   $blf->getByID( $this->getBranch() ),
											   TTi18n::gettext( 'Selected Branch is invalid' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$b_obj = $this->getBranchObject();
		if ( is_object( $b_obj ) ) {
			return TTLog::addEntry( $this->getStation(), $log_action, TTi18n::getText( 'Branch' ) . ': ' . $b_obj->getName(), null, $this->getTable() );
		}

		return false;
	}
}

?>
