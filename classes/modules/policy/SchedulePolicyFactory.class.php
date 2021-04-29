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
 * @package Modules\Policy
 */
class SchedulePolicyFactory extends Factory {
	protected $table = 'schedule_policy';
	protected $pk_sequence_name = 'schedule_policy_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $meal_policy_obj = null;
	protected $break_policy_obj = null;
	protected $full_shift_absence_policy_obj = null;
	protected $partial_shift_absence_policy_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1020-name'                         => TTi18n::gettext( 'Name' ),
						'-1025-description'                  => TTi18n::gettext( 'Description' ),
						'-1040-full_shift_absence_policy'    => TTi18n::gettext( 'Full Shift Undertime Absence Policy' ),
						'-1041-partial_shift_absence_policy' => TTi18n::gettext( 'Partial Shift Undertime Absence Policy' ),
						'-1060-start_stop_window'            => TTi18n::gettext( 'Window' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'description',
						'start_stop_window',
						'updated_date',
						'updated_by',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'          => 'ID',
				'company_id'  => 'Company',
				'name'        => 'Name',
				'description' => 'Description',

				'full_shift_absence_policy_id'    => 'FullShiftAbsencePolicyID',
				'full_shift_absence_policy'       => false,
				'partial_shift_absence_policy_id' => 'PartialShiftAbsencePolicyID',
				'partial_shift_absence_policy'    => false,

				'meal_policy'  => 'MealPolicy',
				'break_policy' => 'BreakPolicy',

				'include_regular_time_policy' => 'IncludeRegularTimePolicy',
				'exclude_regular_time_policy' => 'ExcludeRegularTimePolicy',
				'include_over_time_policy'    => 'IncludeOverTimePolicy',
				'exclude_over_time_policy'    => 'ExcludeOverTimePolicy',
				'include_premium_policy'      => 'IncludePremiumPolicy',
				'exclude_premium_policy'      => 'ExcludePremiumPolicy',

				'start_stop_window' => 'StartStopWindow',
				'deleted'           => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
	 */
	function getFullShiftAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getFullShiftAbsencePolicyID(), 'full_shift_absence_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getPartialShiftAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getPartialShiftAbsencePolicyID(), 'partial_shift_absence_policy_obj' );
	}

	/**
	 * @param string $meal_policy_id UUID
	 * @return bool
	 */
	function getMealPolicyObject( $meal_policy_id ) {
		if ( $meal_policy_id == '' ) {
			return false;
		}

		Debug::Text( 'Meal Policy ID: ' . $meal_policy_id . ' Schedule Policy ID: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $this->meal_policy_obj[$meal_policy_id] )
				&& is_object( $this->meal_policy_obj[$meal_policy_id] ) ) {
			return $this->meal_policy_obj[$meal_policy_id];
		} else {
			$bplf = TTnew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $bplf */
			$bplf->getById( $meal_policy_id );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->meal_policy_obj[$meal_policy_id] = $bplf->getCurrent();

				return $this->meal_policy_obj[$meal_policy_id];
			}

			return false;
		}
	}

	/**
	 * @param string $break_policy_id UUID
	 * @return bool
	 */
	function getBreakPolicyObject( $break_policy_id ) {
		if ( $break_policy_id == '' ) {
			return false;
		}

		Debug::Text( 'Break Policy ID: ' . $break_policy_id . ' Schedule Policy ID: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $this->break_policy_obj[$break_policy_id] )
				&& is_object( $this->break_policy_obj[$break_policy_id] ) ) {
			return $this->break_policy_obj[$break_policy_id];
		} else {
			$bplf = TTnew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
			$bplf->getById( $break_policy_id );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->break_policy_obj[$break_policy_id] = $bplf->getCurrent();

				return $this->break_policy_obj[$break_policy_id];
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
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
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * Checks to see if we need to revert to the meal policies defined in the policy group, or use the ones defined in the schedule policy.
	 * @return bool
	 */
	function isUsePolicyGroupMealPolicy() {
		if ( in_array( TTUUID::getZeroId(), (array)$this->getMealPolicy() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getMealPolicy() {
		$retarr = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 155, $this->getID() );

		//Check if no CompanyGenericMap is *not* set at all, if so assume No Meal (-1)
		if ( $retarr === false ) {
			$retarr = [ -1 ];
		}

		return $retarr;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setMealPolicy( $ids ) {
		//If NONE(-1) or Use Policy Group(0) are defined, unset all other ids.
		if ( is_array( $ids ) ) {
			if ( in_array( TTUUID::getZeroID(), $ids ) ) {
				$ids = [ TTUUID::getZeroID() ];
			} else if ( in_array( TTUUID::getNotExistID(), $ids ) ) {
				$ids = [ TTUUID::getNotExistID() ];
			}
		}

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 155, $this->getID(), $ids, false, true ); //Use relaxed ID range.
	}

	/**
	 * Checks to see if we need to revert to the break policies defined in the policy group, or use the ones defined in the schedule policy.
	 * @return bool
	 */
	function isUsePolicyGroupBreakPolicy() {
		if ( in_array( TTUUID::getZeroId(), (array)$this->getBreakPolicy() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getBreakPolicy() {
		$retarr = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 165, $this->getID() );

		//Check if no CompanyGenericMap is *not* set at all, if so assume No Break (-1)
		if ( $retarr === false ) {
			$retarr = [ -1 ];
		}

		return $retarr;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setBreakPolicy( $ids ) {
		//If NONE(-1) or Use Policy Group (0) are defined, unset all other ids.
		if ( is_array( $ids ) ) {
			if ( in_array( TTUUID::getZeroID(), $ids ) ) {
				$ids = [ TTUUID::getZeroID() ];
			} else if ( in_array( -1, $ids ) ) {
				$ids = [ TTUUID::getNotExistID() ];
			}
		}

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 165, $this->getID(), $ids, false, true ); //Use relaxed ID range.
	}

	/**
	 * @return bool|mixed
	 */
	function getFullShiftAbsencePolicyID() {
		return $this->getGenericDataValue( 'full_shift_absence_policy_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFullShiftAbsencePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'full_shift_absence_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPartialShiftAbsencePolicyID() {
		return $this->getGenericDataValue( 'partial_shift_absence_policy_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPartialShiftAbsencePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'partial_shift_absence_policy_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getIncludeRegularTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 105, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setIncludeRegularTimePolicy( $ids ) {
		Debug::text( 'Setting Include Regular Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 105, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getExcludeRegularTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 106, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setExcludeRegularTimePolicy( $ids ) {
		Debug::text( 'Setting Exclude Regular Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 106, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getIncludeOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 115, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setIncludeOverTimePolicy( $ids ) {
		Debug::text( 'Setting Include Over Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 115, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getExcludeOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 116, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setExcludeOverTimePolicy( $ids ) {
		Debug::text( 'Setting Exclude Over Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 116, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getIncludePremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 125, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setIncludePremiumPolicy( $ids ) {
		Debug::text( 'Setting Include Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 125, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getExcludePremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 126, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setExcludePremiumPolicy( $ids ) {
		Debug::text( 'Setting Exclude Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 126, $this->getID(), $ids );
	}

	/**
	 * @return bool|int
	 */
	function getStartStopWindow() {
		return $this->getGenericDataValue( 'start_stop_window' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartStopWindow( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'start_stop_window', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Name
		if ( $this->Validator->getValidateOnly() == false ) {
			//Don't check the below when mass editing.
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}
		}
		if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2, 50
			);
		}
		if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name is already in use' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is invalid' ),
										1, 250
			);
		}
		// Full Shift Absence Policy
		if ( $this->getFullShiftAbsencePolicyID() !== false && $this->getFullShiftAbsencePolicyID() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows( 'full_shift_absence_policy',
												   $aplf->getByID( $this->getFullShiftAbsencePolicyID() ),
												   TTi18n::gettext( 'Invalid Full Shift Absence Policy' )
			);
		}
		// Partial Shift Absence Policy
		if ( $this->getPartialShiftAbsencePolicyID() !== false && $this->getPartialShiftAbsencePolicyID() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows( 'partial_shift_absence_policy',
												   $aplf->getByID( $this->getPartialShiftAbsencePolicyID() ),
												   TTi18n::gettext( 'Invalid Partial Shift Absence Policy' )
			);
		}
		// Start/Stop window
		if ( $this->getStartStopWindow() !== false ) {
			$this->Validator->isNumeric( 'start_stop_window',
										 $this->getStartStopWindow(),
										 TTi18n::gettext( 'Incorrect Start/Stop window' )
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
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getDeleted() == true ) {
			Debug::Text( 'UnAssign Schedule Policy from Schedule/Recurring Schedules...' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
			$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */

			$query = 'update ' . $sf->getTable() . ' set schedule_policy_id = \'' . TTUUID::getZeroID() . '\' where schedule_policy_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$query = 'update ' . $rstf->getTable() . ' set schedule_policy_id = \'' . TTUUID::getZeroID() . '\' where schedule_policy_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );
		}

		$this->removeCache( $this->getId() );

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'full_shift_absence_policy':
						case 'partial_shift_absence_policy':
							$data[$variable] = $this->getColumn( $variable );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Schedule Policy' ), null, $this->getTable(), $this );
	}
}

?>
