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
class RoundIntervalPolicyFactory extends Factory {
	protected $table = 'round_interval_policy';
	protected $pk_sequence_name = 'round_interval_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	//Just need relations for each actual Punch Type

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'round_type':
				$retval = array(
										10 => TTi18n::gettext('Down'),
										20 => TTi18n::gettext('Average'),
										25 => TTi18n::gettext('Average (Partial Min. Down)'),
										27 => TTi18n::gettext('Average (Partial Min. Up)'),
										30 => TTi18n::gettext('Up')
									);
				break;
			case 'punch_type':
				$retval = array(
										10 => TTi18n::gettext('All Punches'),
										20 => TTi18n::gettext('All In (incl. Lunch/Break)'),
										30 => TTi18n::gettext('All Out (incl. Lunch/Break)'),
										40 => TTi18n::gettext('Normal - In'),
										50 => TTi18n::gettext('Normal - Out'),
										60 => TTi18n::gettext('Lunch - In'),
										70 => TTi18n::gettext('Lunch - Out'),
										80 => TTi18n::gettext('Break - In'),
										90 => TTi18n::gettext('Break - Out'),
										95 => TTi18n::gettext('Transfer Punches'),
										100 => TTi18n::gettext('Lunch Total'),
										110 => TTi18n::gettext('Break Total'),
										120 => TTi18n::gettext('Day Total'),
									);
				break;
			case 'punch_type_relation':
				$retval = array(
										40 => array(10, 20),
										50 => array(10, 30, 120),
										60 => array(10, 20, 100),
										70 => array(10, 30),
										80 => array(10, 20, 110),
										90 => array(10, 30),
										95 => array(), //Return blank array, which will automatically append 95 in getByPolicyGroupUserIdAndTypeId().
									);
				break;
			case 'condition_type':
				$retval = array(
										0 => TTi18n::gettext('Disabled'),
										10 => TTi18n::gettext('Scheduled Time'),
										20 => TTi18n::gettext('Scheduled Time or Not Scheduled'),
										30 => TTi18n::gettext('Static Time'), //For specific time of day, ie: 8AM
										40 => TTi18n::gettext('Static Total Time'), //For Day/Lunch/Break total.
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-punch_type' => TTi18n::gettext('Punch Type'),
										'-1020-round_type' => TTi18n::gettext('Round Type'),
										'-1030-name' => TTi18n::gettext('Name'),
										'-1035-description' => TTi18n::gettext('Description'),

										'-1040-round_interval' => TTi18n::gettext('Interval'),

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
								'punch_type',
								'round_type',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'round_type_id' => 'RoundType',
										'round_type' => FALSE,
										'punch_type_id' => 'PunchType',
										'punch_type' => FALSE,
										'round_interval' => 'Interval',
										'grace' => 'Grace',
										'strict' => 'Strict',

										'condition_type_id' => 'ConditionType',
										'condition_static_time' => 'ConditionStaticTime',
										'condition_static_total_time' => 'ConditionStaticTotalTime',
										'condition_start_window' => 'ConditionStartWindow',
										'condition_stop_window' => 'ConditionStopWindow',

										'in_use' => FALSE,
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
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );

		Debug::Text('Company ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param $status
	 * @param $type
	 * @param $transfer
	 * @return bool|int
	 */
	function getPunchTypeFromPunchStatusAndType( $status, $type, $transfer) {
		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $transfer == TRUE ) {
			$punch_type = 95; //Transfer
		} else {
			switch ( $type ) {
				default:
				case 10: //Normal
					if ( $status == 10 ) { //In
						$punch_type = 40;
					} else {
						$punch_type = 50;
					}
					break;
				case 20: //Lunch
					if ( $status == 10 ) { //In
						$punch_type = 60;
					} else {
						$punch_type = 70;
					}
					break;
				case 30: //Break
					if ( $status == 10 ) { //In
						$punch_type = 80;
					} else {
						$punch_type = 90;
					}
				break;
			}
		}

		return $punch_type;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique: '. $name, __FILE__, __LINE__, __METHOD__, 10);

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
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function setName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $description
	 * @return bool
	 */
	function setDescription( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|int
	 */
	function getRoundType() {
		return $this->getGenericDataValue( 'round_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRoundType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'round_type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getPunchType() {
		return $this->getGenericDataValue( 'punch_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPunchType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'punch_type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getInterval() {
		return $this->getGenericDataValue( 'round_interval' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInterval( $value) {
		$value = trim($value);
		//If someone is using hour parse format ie: 0.12 we need to round to the nearest
		//minute other wise it'll be like 7mins and 23seconds messing up rounding.
		//$this->setGenericDataValue( 'round_interval', $value );
		return $this->setGenericDataValue( 'round_interval', TTDate::roundTime($value, 60, 20) );
	}

	/**
	 * @return bool|mixed
	 */
	function getGrace() {
		return $this->getGenericDataValue( 'grace' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setGrace( $value) {
		$value = trim($value);
		//If someone is using hour parse format ie: 0.12 we need to round to the nearest
		//minute other wise it'll be like 7mins and 23seconds messing up rounding.
		//$this->setGenericDataValue( 'grace', $value );
		return $this->setGenericDataValue( 'grace', TTDate::roundTime($value, 60, 20) );
	}

	/**
	 * @return bool
	 */
	function getStrict() {
		return $this->fromBool( $this->getGenericDataValue( 'strict' ) );
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setStrict( $value) {
		return $this->setGenericDataValue( 'strict', $this->toBool($value) );
	}

	/**
	 * @param int $epoch EPOCH
	 * @param int $window_epoch EPOCH
	 * @return bool
	 */
	function inConditionWindow( $epoch, $window_epoch ) {
		if (
				$epoch >= ( $window_epoch - $this->getConditionStartWindow() )
				AND
				$epoch <= ( $window_epoch + $this->getConditionStopWindow() )
			) {
			return TRUE;
		}

		Debug::Text('Not in Condition Window... Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' Window Epoch: '. TTDate::getDate('DATE+TIME', $window_epoch ) .' Window Start: '. $this->getConditionStartWindow() .' Stop: '. $this->getConditionStopWindow(), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @param $schedule_time
	 * @return bool
	 */
	function isConditionTrue( $epoch, $schedule_time ) {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		Debug::Text('Punch Time: '. TTDate::getDate('DATE+TIME', $epoch ) .' Schedule Time: '. TTDate::getDate('DATE+TIME', $schedule_time ), __FILE__, __LINE__, __METHOD__, 10);
		$retval = FALSE;
		switch( $this->getConditionType() ) {
			case 10: //Scheduled Time
			case 20: //Scheduled Time or Not Scheduled.
				if ( $this->getConditionType() == 20 AND $schedule_time == '' ) {
					Debug::Text('Not scheduled, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
					$retval = TRUE;
				} else {
					Debug::Text('Scheduled...', __FILE__, __LINE__, __METHOD__, 10);
					if ( $this->inConditionWindow( $epoch, $schedule_time ) == TRUE ) {
						$retval = TRUE;
					}
				}
				break;
			case 30: //Static Time
				//If static time after start/stop window is near midnight, due to using TimeLockedDate we need check the current day and the day after when evaluating this condition.
				if ( $this->inConditionWindow( $epoch, TTDate::getTimeLockedDate( $this->getConditionStaticTime(), $epoch) ) == TRUE
					OR ( $this->inConditionWindow( $epoch, TTDate::getTimeLockedDate( $this->getConditionStaticTime(), ( $epoch + 86400 ) ) ) == TRUE ) ) {
					$retval = TRUE;
				}
				break;
			case 40: //Static Total Time
				if ( $this->inConditionWindow( $epoch, $this->getConditionStaticTotalTime() ) == TRUE ) {
					$retval = TRUE;
				}
				break;
			case 0: //Disabled
				$retval = TRUE;
				break;
		}

		Debug::Text('Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @return bool|int
	 */
	function getConditionType() {
		return $this->getGenericDataValue( 'condition_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConditionType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'condition_type_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getConditionStaticTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'condition_static_time' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE) {
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
	function setConditionStaticTime( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'condition_static_time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getConditionStaticTotalTime() {
		return $this->getGenericDataValue( 'condition_static_total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConditionStaticTotalTime( $value) {
		$value = trim($value);
		//If someone is using hour parse format ie: 0.12 we need to round to the nearest
		//minute other wise it'll be like 7mins and 23seconds messing up rounding.
		//$this->setGenericDataValue( 'round_interval', $value );
		return $this->setGenericDataValue( 'condition_static_total_time', TTDate::roundTime($value, 60, 20) );
	}

	/**
	 * @return bool|mixed
	 */
	function getConditionStartWindow() {
		return $this->getGenericDataValue( 'condition_start_window' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConditionStartWindow( $value) {
		$value = trim($value);
		//If someone is using hour parse format ie: 0.12 we need to round to the nearest
		//minute other wise it'll be like 7mins and 23seconds messing up rounding.
		//$this->setGenericDataValue( 'round_interval', $value );
		return $this->setGenericDataValue( 'condition_start_window', TTDate::roundTime($value, 60, 20) );
	}

	/**
	 * @return bool|mixed
	 */
	function getConditionStopWindow() {
		return $this->getGenericDataValue( 'condition_stop_window' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConditionStopWindow( $value) {
		$value = trim($value);
		//If someone is using hour parse format ie: 0.12 we need to round to the nearest
		//minute other wise it'll be like 7mins and 23seconds messing up rounding.
		//$this->setGenericDataValue( 'round_interval', $value );
		return $this->setGenericDataValue( 'condition_stop_window', TTDate::roundTime($value, 60, 20) );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
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
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			//Don't check the below when mass editing.
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE(	'name',
											FALSE,
											TTi18n::gettext('Please specify a name') );
			}
		}
		if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isLength(	'name',
												$this->getName(),
												TTi18n::gettext('Name is too short or too long'),
												2, 50
											);
		}
		if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isTrue(	'name',
												$this->isUniqueName($this->getName()),
												TTi18n::gettext('Name is already in use')
											);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength(	'description',
												$this->getDescription(),
												TTi18n::gettext('Description is invalid'),
												1, 250
											);
		}
		// Round Type
		if ( $this->getRoundType() !== FALSE ) {
			$this->Validator->inArrayKey(	'round_type',
												$this->getRoundType(),
												TTi18n::gettext('Incorrect Round Type'),
												$this->getOptions('round_type')
											);
		}
		// Punch Type
		if ( $this->getPunchType() !== FALSE ) {
			$this->Validator->inArrayKey(	'punch_type',
												$this->getPunchType(),
												TTi18n::gettext('Incorrect Punch Type'),
												$this->getOptions('punch_type')
											);
		}
		// Interval
		if ( $this->getInterval() !== FALSE ) {
			$this->Validator->isNumeric(		'interval',
												$this->getInterval(),
												TTi18n::gettext('Incorrect Interval')
											);
		}
		// Grace
		if ( $this->getGrace() !== FALSE ) {
			$this->Validator->isNumeric(		'grace',
													$this->getGrace(),
													TTi18n::gettext('Incorrect grace value')
												);
		}
		// Condition Type
		$this->Validator->inArrayKey(	'condition_type',
												$this->getConditionType(),
												TTi18n::gettext('Incorrect Condition Type'),
												$this->getOptions('condition_type')
											);
		// Static time
		if ( $this->getConditionStaticTime() != '' ) {
			$this->Validator->isDate(		'condition_static_time',
													$this->getConditionStaticTime(),
													TTi18n::gettext('Incorrect Static time')
												);
		}
		// Static Total Time
		if ( $this->getConditionStaticTotalTime() !== FALSE ) {
			$this->Validator->isNumeric(		'condition_static_total_time',
													$this->getConditionStaticTotalTime(),
													TTi18n::gettext('Incorrect Static Total Time')
												);
		}
		// Start Window
		if ( $this->getConditionStartWindow() !== FALSE ) {
			$this->Validator->isNumeric(		'condition_start_window',
													$this->getConditionStartWindow(),
													TTi18n::gettext('Incorrect Start Window')
												);
		}
		// Stop Window
		if ( $this->getConditionStopWindow() !== FALSE ) {
			$this->Validator->isNumeric(		'condition_stop_window',
													$this->getConditionStopWindow(),
													TTi18n::gettext('Incorrect Stop Window')
												);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array('round_interval_policy' => $this->getId() ), 1 );
			if ( $pglf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  FALSE,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by policy groups' ) );
			}
		}

		if ( $ignore_warning == FALSE ) {
			if ( $this->getInterval() == 0 ) {
				$this->Validator->Warning( 'round_interval', TTi18n::gettext( 'An interval of 0 will result in punches being saved to the nearest second, consider using 1 minute instead' ) );
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
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
						case 'condition_static_time':
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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'punch_type':
						case 'round_type':
							$function = 'get'.str_replace('_', '', $variable);
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'condition_static_time':
							$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Round Interval Policy'), NULL, $this->getTable(), $this );
	}
}
?>
