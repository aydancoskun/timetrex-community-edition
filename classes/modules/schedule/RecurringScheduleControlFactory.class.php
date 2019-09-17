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
class RecurringScheduleControlFactory extends Factory {
	protected $table = 'recurring_schedule_control';
	protected $pk_sequence_name = 'recurring_schedule_control_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $recurring_schedule_template_obj = NULL;

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
										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1030-recurring_schedule_template_control' => TTi18n::gettext('Template'),
										'-1040-recurring_schedule_template_control_description' => TTi18n::gettext('Description'),
										'-1050-start_date' => TTi18n::gettext('Start Date'),
										'-1060-end_date' => TTi18n::gettext('End Date'),
										'-1065-display_weeks' => TTi18n::gettext('Display Weeks'),
										'-1070-auto_fill' => TTi18n::gettext('Auto-Punch'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

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
								'first_name',
								'last_name',
								'recurring_schedule_template_control',
								'recurring_schedule_template_control_description',
								'start_date',
								'end_date',
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
										'user_id' => FALSE,
										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',
										'recurring_schedule_template_control' => FALSE,
										'recurring_schedule_template_control_description' => FALSE,
										'start_week' => 'StartWeek',
										'start_date' => 'StartDate',
										'end_date' => 'EndDate',
										'display_weeks' => 'DisplayWeeks',
										'auto_fill' => 'AutoFill',
										'user' => 'User',
										'deleted' => 'Deleted',
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
	 * @return bool
	 */
	function getRecurringScheduleTemplateControlObject() {
		return $this->getGenericObject( 'RecurringScheduleTemplateControlListFactory', $this->getRecurringScheduleTemplateControl(), 'recurring_schedule_template_control_obj' );
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
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRecurringScheduleTemplateControl() {
		return $this->getGenericDataValue( 'recurring_schedule_template_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringScheduleTemplateControl( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'recurring_schedule_template_control_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStartWeek() {
		return $this->getGenericDataValue( 'start_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWeek( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'start_week', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'start_date' );
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'end_date' );
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDisplayWeeks() {
		return $this->getGenericDataValue( 'display_weeks' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDisplayWeeks( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'display_weeks', $value );
	}

	/**
	 * @return bool
	 */
	function getAutoFill() {
		return $this->fromBool( $this->getGenericDataValue( 'auto_fill' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAutoFill( $value ) {
		return $this->setGenericDataValue( 'auto_fill', $this->toBool($value) );
	}

	/**
	 * @return array|bool
	 */
	function getUser() {
		$rsulf = TTnew( 'RecurringScheduleUserListFactory' );
		$rsulf->getByRecurringScheduleControlId( $this->getId() );
		$list = array();
		foreach ($rsulf as $obj) {
			$list[] = $obj->getUser();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUser( $ids) {
		if ( !is_array($ids) ) {
			global $current_user;
			if ( $ids == '' AND ( getTTProductEdition() == TT_PRODUCT_COMMUNITY OR $current_user->getCompanyObject()->getProductEdition() == 10 ) ) {
				$this->Validator->isTrue(		'user',
												FALSE,
												TTi18n::gettext('Please select at least one employee') );
				return FALSE;
			} else {
				$ids = array($ids);
			}
		}

		if ( is_array($ids) AND count($ids) > 0 ) {
			$tmp_ids = array();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$rsulf = TTnew( 'RecurringScheduleUserListFactory' );
				$rsulf->getByRecurringScheduleControlId( $this->getId() );
				foreach ($rsulf as $obj) {
					$id = $obj->getUser();
					Debug::text('Recurring Schedule ID: '. $obj->getRecurringScheduleControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$this->deleted_user_ids[] = $obj->getUser(); //Record deleted user_ids so we can recalculate their schedules in postSave();
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' );
			foreach ($ids as $id) {
				if ( $id === 0 ) {
					$id = TTUUID::getZeroID();
				}

				//if ( $id >= 0 AND isset($tmp_ids) AND !in_array($id, $tmp_ids) ) { //-1 is used as "NONE", so ignore it here.
				if ( TTUUID::isUUID($id) AND $id != TTUUID::getNotExistID() AND isset($tmp_ids) AND !in_array($id, $tmp_ids) ) { //-1 is used as "NONE", so ignore it here.
					//Handle OPEN shifts.
					$full_name = NULL;
					if ( $id == TTUUID::getZeroID() ) {
						$full_name = TTi18n::getText('OPEN');
					} else {
						$ulf->getById( $id );
						if ( $ulf->getRecordCount() > 0 ) {
							$full_name = $ulf->getCurrent()->getFullName();
						}
					}

					$rsuf = TTnew( 'RecurringScheduleUserFactory' );
					$rsuf->setRecurringScheduleControl( $this->getId() );
					$rsuf->setUser( $id );

					if ( $this->Validator->isTrue(		'user',
														$rsuf->Validator->isValid(),
														TTi18n::gettext('Selected employee is invalid').' ('. $full_name .')' )) {
						$rsuf->save();
					}
					unset($full_name);
				} elseif ( $id == TTUUID::getNotExistID() ) { //Make sure -1 isn't the only selected option.
					$this->Validator->isTrue(		'user',
													FALSE,
													TTi18n::gettext('Please select at least one employee') );
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param $current
	 * @param $start
	 * @param $max
	 * @return int
	 */
	function reMapWeek( $current, $start, $max ) {
		return ( ( ( ( $current - 1 ) + $max - ( $start - 1 ) ) % $max) + 1 );
	}

	/**
	 * @param $week_arr
	 * @return array
	 */
	function ReMapWeeks( $week_arr) {
		//We should be able to re-map weeks with simple math:
		//For example:
		//	Start Week = 3, Max Week = 5
		// If template week is less then start week, we add the start week.
		// If template week is greater or equal then start week, we minus the 1-start_week.
		//	Template Week 1 -- 1 + 3(start week)   = ReMapped Week 4
		//	Template Week 2 -- 2 + 3			   = ReMapped Week 5
		//	Template Week 3 -- 3 - 2(start week-1) = ReMapped Week 1
		//	Template Week 4 -- 4 - 2			   = ReMapped Week 2
		//	Template Week 5 -- 5 - 2			   = ReMapped Week 3

		//Remaps weeks based on start week
		Debug::text('Start Week: '.	 $this->getStartWeek(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getStartWeek() > 1 AND in_array( $this->getStartWeek(), $week_arr) ) {
			Debug::text('Weeks DO need reordering: ', __FILE__, __LINE__, __METHOD__, 10);
			$max_week = count($week_arr);

			$i = 1;
			$arr = array();
			foreach( $week_arr as $key => $val ) {
				$new_val = ( $key - ($this->getStartWeek() - 1) );

				if ( $key < $this->getStartWeek() ) {
					$new_val = ( $new_val + $max_week );
				}

				$arr[$new_val] = $key;

				$i++;
			}
			unset($val); //code standards
			//var_dump($arr);
			return $arr;
		}

		Debug::text('Weeks do not need reordering: ', __FILE__, __LINE__, __METHOD__, 10);

		return $week_arr;
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
		// Recurring Schedule Template
		if ( $this->getRecurringScheduleTemplateControl() !== FALSE ) {
			$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
			$this->Validator->isResultSetWithRows(	'recurring_schedule_template_control_id',
															$rstclf->getByID($this->getRecurringScheduleTemplateControl()),
															TTi18n::gettext('Recurring Schedule Template is invalid')
														);
		}
		// Start week
		if ( $this->getStartWeek() !== FALSE ) {
			$this->Validator->isGreaterThan(	'start_week',
														$this->getStartWeek(),
														TTi18n::gettext('Start week must be at least 1'),
														1
													);
			if ( $this->Validator->isError('start_week') == FALSE ) {
				$this->Validator->isNumeric(		'start_week',
															$this->getStartWeek(),
															TTi18n::gettext('Start week is invalid')
														);
			}
		}
		// Start date
		if ( $this->getStartDate() !== FALSE ) {
			$this->Validator->isDate(		'start_date',
													$this->getStartDate(),
													TTi18n::gettext('Incorrect start date')
												);
		}
		// End date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate(		'end_date',
													$this->getEndDate(),
													TTi18n::gettext('Incorrect end date')
												);
		}
		// Display Weeks
		if ( $this->Validator->getValidateOnly() == FALSE OR $this->getDisplayWeeks() !== FALSE ) {
			$this->Validator->isGreaterThan(	'display_weeks',
														$this->getDisplayWeeks(),
														TTi18n::gettext('Display Weeks must be at least 1'),
														1
													);
			if ( $this->Validator->isError('display_weeks') == FALSE ) {
				$this->Validator->isLessThan(		'display_weeks',
															$this->getDisplayWeeks(),
															TTi18n::gettext('Display Weeks cannot exceed 78'),
															78
														);
			}
			if ( $this->Validator->isError('display_weeks') == FALSE ) {
				$this->Validator->isNumeric(		'display_weeks',
															$this->getDisplayWeeks(),
															TTi18n::gettext('Display weeks is invalid')
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
	function preSave() {
		if ( $this->getStartWeek() < 1 ) {
			$this->setStartWeek( 1 );
		}

		if ( $this->getDisplayWeeks() < 1 ) {
			$this->setDisplayWeeks(1);
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

		//Handle generating recurring schedule rows, so they are as real-time as possible.
		//In case an issue arises (like holiday not appearing or something) and they need to recalculate schedules, always start from the prior week.
		//  so we at least have a chance of recalculating retroactively to some degree.
		$current_epoch = TTDate::getBeginWeekEpoch( TTDate::getBeginWeekEpoch( time() ) - 86400 );

		//FIXME: Put a cap on this perhaps, as 3mths into the future so we don't spend a ton of time doing this
		//if the user puts sets it to display 1-2yrs in the future. Leave creating the rest of the rows to the maintenance job?
		//Since things may change we will want to delete all schedules with each change, but only add back in X weeks at most unless from a maintenance job.
		$maximum_end_date = ( TTDate::getEndWeekEpoch($current_epoch + ( 86400 * 7 )) + ( $this->getDisplayWeeks() * ( 86400 * 7 ) ) );
		if ( $this->getEndDate() != '' AND $maximum_end_date > $this->getEndDate() ) {
			$maximum_end_date = $this->getEndDate();
		}

		$rsf = TTnew('RecurringScheduleFactory');
		$rsf->StartTransaction();
		$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $this->getID(), ( $current_epoch - (86400 * 720) ), ( $current_epoch + (86400 * 720) ) );
		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Recurring Schedule ID: '. $this->getID() .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);
			$rsf->addRecurringSchedulesFromRecurringScheduleControl( $this->getCompany(), $this->getID(), $current_epoch, $maximum_end_date );
		}

		//If a user has two templates assigned to them that happen to be conflicting with one another, and the recurring schedule for one is deleted, it could leave "blank" shifts on the days where the conflict was.
		//So when deleting recurring schedules OR when removing employees from recurring schedules, try to recalculate *all* recurring schedules of all the users that may be affected to ensure that any conflicting shifts will be recalculated.
		if ( $this->getDeleted() == TRUE OR ( isset($this->deleted_user_ids) AND is_array( $this->deleted_user_ids ) AND count( $this->deleted_user_ids ) > 0 ) ) {
			$user_ids = $this->getUser(); //This is all currently assigned users.

			//Merge in any deleted users.
			if ( isset($this->deleted_user_ids) AND is_array( $this->deleted_user_ids ) AND count( $this->deleted_user_ids ) > 0 ) {
	            $user_ids = array_merge( $user_ids, $this->deleted_user_ids );
			}

			if ( is_array( $user_ids ) AND count( $user_ids ) > 0 ) {
				$rsclf = TTnew( 'RecurringScheduleControlListFactory' );
				$rsclf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'user_id' => $user_ids ) );
				if ( $rsclf->getRecordCount() > 0 ) {
					foreach( $rsclf as $rsc_obj ) {
						if ( $this->getID() != $rsc_obj->getID() ) { //Don't recalculate the current recurring schedule record as thats already done above.
							Debug::text( 'Recalculating Recurring Schedule ID: ' . $this->getID() . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10 );
							$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), ( $current_epoch + ( 86400 * 720 ) ) );
							$rsf->addRecurringSchedulesFromRecurringScheduleControl( $this->getCompany(), $rsc_obj->getID(), $current_epoch, $maximum_end_date );
						} else {
							Debug::text('  Skipping recalculating ourself again...', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				}
			}
		}

		$rsf->CommitTransaction();

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
						case 'start_date':
						case 'end_date':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		//
		//When using the Recurring Schedule view, it returns the user list for every single row and runs out of memory at about 1000 rows.
		//Need to make the 'user' column explicitly defined instead perhaps?
		//
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = ( $this->getColumn( $variable ) == '' ) ? TTi18n::getText('OPEN') : $this->getColumn( $variable );
							break;
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'recurring_schedule_template_control':
						case 'recurring_schedule_template_control_description':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->$function() ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}

			//Handle expanded and non-expanded mode. In non-expanded mode we need to get all the users
			//so we can check is_owner/is_child permissions on them.
			if ( $this->getColumn( 'user_id' ) !== FALSE ) {
				$user_ids = $this->getColumn( 'user_id' );
			} else {
				$user_ids = $this->getUser();
			}

			$this->getPermissionColumns( $data, $user_ids, $this->getCreatedBy(), $permission_children_ids, $include_columns );
			//$this->getPermissionColumns( $data, $this->getColumn('user_id'), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Recurring Schedule'), NULL, $this->getTable(), $this );
	}
}
?>
