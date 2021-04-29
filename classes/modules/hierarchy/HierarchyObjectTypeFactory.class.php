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
 * @package Modules\Hierarchy
 */
class HierarchyObjectTypeFactory extends Factory {
	protected $table = 'hierarchy_object_type';
	protected $pk_sequence_name = 'hierarchy_object_type_id_seq'; //PK Sequence name

	var $hierarchy_control_obj = null;

	/**
	 * @param $name
	 * @param null $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = null;
		switch ( $name ) {
			case 'object_type':
				$retval = [
					//10 => TTi18n::gettext('Recurring Schedule'),
					//20 => TTi18n::gettext('Schedule Amendment'),
					//30 => TTi18n::gettext('Shift Amendment'),
					//40 => TTi18n::gettext('Pay Stub Amendment')
					//50 => TTi18n::gettext('Request'),

					100  => TTi18n::gettext( 'Permission' ),
					80   => TTi18n::gettext( 'Exception' ),
					90   => TTi18n::gettext( 'TimeSheet' ),

					//Add 1000 to request type_id's. Make sure no other objects pass 1000.
					1010 => TTi18n::gettext( 'Request: Missed Punch' ),
					1020 => TTi18n::gettext( 'Request: Time Adjustment' ),
					1030 => TTi18n::gettext( 'Request: Absence (incl. Vacation)' ),
					1040 => TTi18n::gettext( 'Request: Schedule Adjustment' ),
					1100 => TTi18n::gettext( 'Request: Other' ),
				];

				if ( $product_edition_id >= 25 ) {
					$retval[200] = TTi18n::gettext( 'Expense' );
				}

				break;
			case 'short_object_type': //Defines a short form of the names.
				$retval = [
					//10 => TTi18n::gettext('Recurring Schedule'),
					//20 => TTi18n::gettext('Schedule Amendment'),
					//30 => TTi18n::gettext('Shift Amendment'),
					//40 => TTi18n::gettext('Pay Stub Amendment')
					//50 => TTi18n::gettext('Request'),

					100  => TTi18n::gettext( 'Permission' ),
					80   => TTi18n::gettext( 'Exception' ),
					90   => TTi18n::gettext( 'TimeSheet' ),

					//Add 1000 to request type_id's. Make sure no other objects pass 1000.
					1010 => TTi18n::gettext( 'R:Missed Punch' ),
					1020 => TTi18n::gettext( 'R:Adjustment' ),
					1030 => TTi18n::gettext( 'R:Absence' ),
					1040 => TTi18n::gettext( 'R:Schedule' ),
					1100 => TTi18n::gettext( 'R:Other' ),

					200 => TTi18n::gettext( 'Expense' ),
				];
				break;
		}

		$retval = Misc::addSortPrefix( $retval );

		return $retval;
	}

	/**
	 * @return null
	 */
	function getHierarchyControlObject() {
		if ( is_object( $this->hierarchy_control_obj ) ) {
			return $this->hierarchy_control_obj;
		} else {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$this->hierarchy_control_obj = $hclf->getById( $this->getHierarchyControl() )->getCurrent();

			return $this->hierarchy_control_obj;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getHierarchyControl() {
		return $this->getGenericDataValue( 'hierarchy_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHierarchyControl( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Hierarchy Control ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'hierarchy_control_id', $value );
	}

	/**
	 * @param $object_type
	 * @return bool
	 */
	function isUniqueObjectType( $object_type ) {
		/*
				$company_id = $this->getHierarchyControlObject()->getCompany();

				$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
				$hotlf->getByCompanyId( $company_id );
				foreach ( $hotlf as $object_type_obj) {
					if ( $object_type_obj->getId() !== $this->getId() ) {
						$assigned_object_types[] = $object_type_obj->getObjectType();
					}
				}

				if ( isset($assigned_object_types) AND is_array($assigned_object_types) AND in_array( $object_type, $assigned_object_types) ) {
					return FALSE;
				}
		*/
		return true;
	}

	/**
	 * @return int
	 */
	function getObjectType() {
		return $this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'object_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Hierarchy Control
		if ( $this->getHierarchyControl() == TTUUID::getZeroID() ) {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$this->Validator->isResultSetWithRows( 'hierarchy_control_id',
												   $hclf->getByID( $this->getHierarchyControl() ),
												   TTi18n::gettext( 'Invalid Hierarchy Control' )
			);
		}
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  Misc::trimSortPrefix( $this->getOptions( 'object_type' ) )
		);
		if ( $this->Validator->isError( 'object_type' ) == false ) {
			$this->Validator->isTrue( 'object_type',
									  $this->isUniqueObjectType( $this->getObjectType() ),
									  TTi18n::gettext( 'Object Type is already assigned to another hierarchy' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$cache_id = $this->getHierarchyControlObject()->getCompany() . $this->getObjectType();
		$this->removeCache( $cache_id );

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
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$object_type = Option::getByKey( $this->getObjectType(), Misc::TrimSortPrefix( $this->getOptions( 'object_type' ) ) );

		return TTLog::addEntry( $this->getHierarchyControl(), $log_action, TTi18n::getText( 'Object' ) . ': ' . $object_type, null, $this->getTable() );
	}
}

?>
