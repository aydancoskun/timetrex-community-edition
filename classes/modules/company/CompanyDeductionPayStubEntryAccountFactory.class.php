<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
class CompanyDeductionPayStubEntryAccountFactory extends Factory {
	protected $table = 'company_deduction_pay_stub_entry_account';
	protected $pk_sequence_name = 'company_deduction_pay_stub_entry_account_id_seq'; //PK Sequence name

	protected $pay_stub_entry_account_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Include' ),
						20 => TTi18n::gettext( 'Exclude' ),
				];
				break;
		}

		return $retval;
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountObject() {
		if ( is_object( $this->pay_stub_entry_account_obj ) ) {
			return $this->pay_stub_entry_account_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$psealf->getById( $this->getPayStubEntryAccount() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_obj = $psealf->getCurrent();

				return $this->pay_stub_entry_account_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyDeduction() {
		return $this->getGenericDataValue( 'company_deduction_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyDeduction( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value != TTUUID::getZeroID() ) {
			return $this->setGenericDataValue( 'company_deduction_id', $value );
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getPayStubEntryAccount() {
		return $this->getGenericDataValue( 'pay_stub_entry_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayStubEntryAccount( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'pay_stub_entry_account_id', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		// Tax / Deduction

		//Because this is a child class, don't validate the parent record here as it may be not be saved yet.
//		if ( $this->getCompanyDeduction() !== FALSE AND $this->getCompanyDeduction() != TTUUID::getZeroID() ) {
//			$cdlf = TTnew( 'CompanyDeductionListFactory' );
//			$this->Validator->isResultSetWithRows(	'company_deduction',
//															$cdlf->getByID($this->getCompanyDeduction()),
//															TTi18n::gettext('Tax / Deduction is invalid')
//														);
//		}
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);

		// Pay Stub Account
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$this->Validator->isResultSetWithRows( 'pay_stub_entry_account',
											   $psealf->getByID( $this->getPayStubEntryAccount() ),
											   TTi18n::gettext( 'Pay Stub Account is invalid' )
		);
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * This table doesn't have any of these columns, so overload the functions.
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
		$obj = $this->getPayStubEntryAccountObject();
		if ( is_object( $obj ) ) {
			$type = Option::getByKey( $this->getType(), Misc::TrimSortPrefix( $this->getOptions( 'type' ) ) );

			return TTLog::addEntry( $this->getCompanyDeduction(), $log_action, $type . ' ' . TTi18n::getText( 'Pay Stub Account' ) . ': ' . $obj->getName(), null, $this->getTable() );
		}

		return false;
	}
}

?>
