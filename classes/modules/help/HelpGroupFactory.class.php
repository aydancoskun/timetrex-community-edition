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
 * @package Modules\Help
 */
class HelpGroupFactory extends Factory {
	protected $table = 'help_group';
	protected $pk_sequence_name = 'help_group_id_seq'; //PK Sequence name

	/**
	 * @return mixed
	 */
	function getHelpGroupControl() {
		return $this->getGenericDataValue( 'help_group_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHelpGroupControl( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'help_group_control_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getHelp() {
		return $this->getGenericDataValue( 'help_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHelp( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'help_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getOrder() {
		return $this->getGenericDataValue( 'order_value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOrder( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'order_value', $value );
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
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Help Group Control
		$hgclf = TTnew( 'HelpGroupControlListFactory' ); /** @var HelpGroupControlListFactory $hgclf */
		$this->Validator->isResultSetWithRows(	'help_group_control',
														$hgclf->getByID($this->getHelpGroupControl()),
														TTi18n::gettext('Help Group Control is invalid')
													);
		// Help Entry
		$hlf = TTnew( 'HelpListFactory' ); /** @var HelpListFactory $hlf */
		$this->Validator->isResultSetWithRows(	'help',
														$hlf->getByID($this->getHelp()),
														TTi18n::gettext('Help Entry is invalid')
													);
		// Order
		$this->Validator->isNumeric(	'order',
												$this->getOrder(),
												TTi18n::gettext('Order is invalid')
											);


		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return TRUE;
	}
}
?>
