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
class UserIdentificationFactory extends Factory {
	protected $table = 'user_identification';
	protected $pk_sequence_name = 'user_identification_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											1	=> TTi18n::gettext('Employee Sequence'), //Company specific employee sequence number, primarily for timeclocks. Should be less than 65535.
											5	=> TTi18n::gettext('Password History'), //Web interface password history
											10	=> TTi18n::gettext('iButton'),
											20	=> TTi18n::gettext('USB Fingerprint'),
											//25	=> TTi18n::gettext('LibFingerPrint'),
											30	=> TTi18n::gettext('Barcode'), //For barcode readers and USB proximity card readers.
											35	=> TTi18n::gettext('QRcode'), //For cameras to read QR code badges.
											40	=> TTi18n::gettext('Proximity Card'), //Mainly for proximity cards on timeclocks.
											70	=> TTi18n::gettext('Face Image'), //Raw image of cropped face in as high of quality as possible, and cropped 10-20% larger than the face itself.
											75	=> TTi18n::gettext('Facial Recognition'), //Luxand v5 SDK templates.
											76	=> TTi18n::gettext('Facial Recognition (v2)'), //Luxand v5 SDK templates, App v3.3+
											100	=> TTi18n::gettext('TimeClock FingerPrint (v9)'), //TimeClocks v9 algo
											101	=> TTi18n::gettext('TimeClock FingerPrint (v10)'), //TimeClocks v10 algo
									);
				break;

		}

		return $retval;
	}

	/**
	 * @return null
	 */
	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim($value);
		//This needs to be stay as TimeTrex Client application still uses names rather than IDs.
		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}
		return $this->setGenericDataValue( 'type_id', $value );
	}

	/*
		For fingerprints,
			10 = Fingerprint 1	Pass 0.
			11 = Fingerprint 1	Pass 1.
			12 = Fingerprint 1	Pass 2.

			20 = Fingerprint 2	Pass 0.
			21 = Fingerprint 2	Pass 1.
			...
	*/
	/**
	 * @return bool|mixed
	 */
	function getNumber() {
		return $this->getGenericDataValue( 'number' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNumber( $value) {
		$value = trim($value);
		//Pull out only digits
		$value = $this->Validator->stripNonNumeric($value);
		return $this->setGenericDataValue( 'number', $value );
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param $value
	 * @return bool
	 */
	function isUniqueValue( $user_id, $type_id, $value) {
		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					'type_id' => (int)$type_id,
					'value' => (string)$value,
					);

		$uf = TTnew( 'UserFactory' );

		$query = 'select a.id
					from '. $this->getTable() .' as a,
						'. $uf->getTable() .' as b
					where a.user_id = b.id
						AND b.company_id = ( select z.company_id from '. $uf->getTable() .' as z where z.id = ? and z.deleted = 0 )
						AND a.type_id = ?
						AND a.value = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$id = $this->db->GetOne($query, $ph);
		//Debug::Arr($id, 'Unique Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
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
	function setValue( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getExtraValue() {
		return $this->getGenericDataValue( 'extra_value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExtraValue( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'extra_value', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Invalid Employee')
														);
		}
		// Type
		$this->Validator->inArrayKey(	'type',
												$this->getType(),
												TTi18n::gettext('Incorrect Type'),
												$this->getOptions('type')
											);
		// Number
		$this->Validator->isFloat(	'number',
											$this->getNumber(),
											TTi18n::gettext('Incorrect Number')
										);
		// Value
		$this->Validator->isLength(			'value',
													$this->getValue(),
													TTi18n::gettext('Value is too short or too long'),
													1,
													1024000); //Need relatively large face images.
		// Extra Value
		if ( $this->getExtraValue() !== FALSE ) {
			$this->Validator->isLength(			'extra_value',
														$this->getExtraValue(),
														TTi18n::gettext('Extra Value is too long'),
														1,
												   		1024000
													);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getValue() == FALSE ) {
				$this->Validator->isTRUE(			'value',
													FALSE,
													TTi18n::gettext('Value is not defined') );

		} else {
			$this->Validator->isTrue(		'value',
											$this->isUniqueValue( $this->getUser(), $this->getType(), $this->getValue() ),
											TTi18n::gettext('Value is already in use, please enter a different one'));
		}
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if (  $this->getNumber() == '' ) {
			$this->setNumber( 0 );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		//Don't do detail logging for this, as it will store entire figerprints in the log table.
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Identification - Employee'). ': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Type') . ': '. Option::getByKey($this->getType(), $this->getOptions('type') ), NULL, $this->getTable() );
	}
}
?>
