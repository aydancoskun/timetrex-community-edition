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
 * @package Modules\Schedule
 */
class RecurringScheduleTemplateControlFactory extends Factory {
	protected $table = 'recurring_schedule_template_control';
	protected $pk_sequence_name = 'recurring_schedule_template_control_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1030-name' => TTi18n::gettext('Name'),
										'-1040-description' => TTi18n::gettext('Description'),

										'-1900-in_use' => TTi18n::gettext('In Use'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'name' => 'Name',
										'description' => 'Description',
										'in_use' => FALSE,
										'deleted' => 'Deleted',
										'created_by' => 'CreatedBy', //Needed to change the "owner" of the template for permission purposes.
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'company_id', $value );
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
		$value = trim($value);
/*
				AND	$this->Validator->isTrue(	'name',
												$this->isUniqueName($name),
												TTi18n::gettext('Name is already in use')
												)
*/
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
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}
	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' );
		$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		// Name
		if ( $this->getName() !== FALSE ) {
			$this->Validator->isLength(	'name',
												$this->getName(),
												TTi18n::gettext('Name is invalid'),
												2, 50
											);
		}
		// Description
		$this->Validator->isLength(	'description',
											$this->getDescription(),
											TTi18n::gettext('Description is invalid'),
											0, 255
										);

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure nothing else references this, so we can be sure its okay to delete it.
			$rsclf = TTnew('RecurringScheduleControlListFactory');
			$rsclf->getByCompanyIdAndTemplateID( $this->getCompany(), $this->getId() );
			if ( $rsclf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  FALSE,
										  TTi18n::gettext( 'This recurring template is currently in use' ) );
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//
		//**THIS IS DONE IN RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
		//

		//Loop through all RecurringScheduleControl rows associated with this template, so we can recalculate the recurring schedules for them.
		$rsclf = TTNew('RecurringScheduleControlListFactory');
		$rsclf->getByCompanyIdAndTemplateID( $this->getCompany(), $this->getId() );
		if ( $rsclf->getRecordCount() > 0 ) {
			Debug::text('Found RecurringScheduleControl records assigned to this template: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			foreach( $rsclf as $rsc_obj ) {
				//Handle generating recurring schedule rows, so they are as real-time as possible.
				$current_epoch = TTDate::getBeginWeekEpoch( TTDate::getBeginWeekEpoch( time() ) - 86400 );

				$rsf = TTnew('RecurringScheduleFactory');
				$rsf->setAMFMessageID( $this->getAMFMessageID() );
				$rsf->StartTransaction();
				$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - (86400 * 720) ), ( $current_epoch + (86400 * 720) ) );
				if ( $this->getDeleted() == FALSE ) {
					//FIXME: Put a cap on this perhaps, as 3mths into the future so we don't spend a ton of time doing this
					//if the user puts sets it to display 1-2yrs in the future. Leave creating the rest of the rows to the maintenance job?
					//Since things may change we will want to delete all schedules with each change, but only add back in X weeks at most unless from a maintenance job.
					$maximum_end_date = ( TTDate::getEndWeekEpoch($current_epoch + ( 86400 * 7 ) ) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) );
					if ( $rsc_obj->getEndDate() != '' AND $maximum_end_date > $rsc_obj->getEndDate() ) {
						$maximum_end_date = $rsc_obj->getEndDate();
					}
					Debug::text('Recurring Schedule ID: '. $rsc_obj->getID() .' Start Date: '. TTDate::getDate('DATE+TIME', $current_epoch ) .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);

					$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $current_epoch, $maximum_end_date );
				}
				$rsf->CommitTransaction();
			}
		}

		return TRUE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data, $variable_function_map );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'in_use':
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
			$this->getPermissionColumns( $data, $this->getCreatedBy(), $this->getCreatedBy(), FALSE, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Recurring Schedule Template').': '. $this->getName(), NULL, $this->getTable(), $this );
	}
}
?>
