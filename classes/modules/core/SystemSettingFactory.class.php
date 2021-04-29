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
class SystemSettingFactory extends Factory {
	protected $table = 'system_setting';
	protected $pk_sequence_name = 'system_setting_id_seq'; //PK Sequence name

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$ph = [
				'name' => $name,
		];

		$query = 'select id from ' . $this->getTable() . ' where name = ?';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue() {
		return $this->getGenericDataValue( 'value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									1, 250
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name already exists' )
			);
		}
		// Value
		$this->Validator->isLength( 'value',
									$this->getValue(),
									TTi18n::gettext( 'Value is too short or too long' ),
									1, 4096
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	//This table doesn't have any of these columns, so overload the functions.

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
	 * @return bool
	 */
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( 'all' );
		$this->removeCache( $this->getName() );

		return true;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool|int|string
	 */
	static function setSystemSetting( $key, $value ) {
		$sslf = new SystemSettingListFactory();
		$sslf->getByName( $key );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = new SystemSettingListFactory();
		}
		$obj->setName( $key );
		$obj->setValue( $value );
		if ( $obj->isValid() ) {
			Debug::Text( 'Key: ' . $key . ' Value: ' . $value . ' isNew: ' . (int)$obj->isNew(), __FILE__, __LINE__, __METHOD__, 10 );

			return $obj->Save();
		}

		return false;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	static function getSystemSettingValueByKey( $key ) {
		$sslf = new SystemSettingListFactory();
		$sslf->getByName( $key );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();

			return $obj->getValue();
		} else if ( $sslf->getRecordCount() > 1 ) {
			Debug::Text( 'ERROR: ' . $sslf->getRecordCount() . ' SystemSetting record(s) exists with key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $key
	 * @return bool|mixed
	 */
	static function getSystemSettingObjectByKey( $key ) {
		$sslf = new SystemSettingListFactory();
		$sslf->getByName( $key );
		if ( $sslf->getRecordCount() == 1 ) {
			return $sslf->getCurrent();
		}

		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'System Setting - Name' ) . ': ' . $this->getName() . ' ' . TTi18n::getText( 'Value' ) . ': ' . $this->getValue(), null, $this->getTable() );
	}
}

?>
