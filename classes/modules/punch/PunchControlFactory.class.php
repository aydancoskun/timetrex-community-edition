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
 * @package Modules\Punch
 */
class PunchControlFactory extends Factory {
	protected $table = 'punch_control';
	protected $pk_sequence_name = 'punch_control_id_seq'; //PK Sequence name

	public $old_date_stamps = [];
	protected $shift_data = null;

	protected $user_obj = null;
	protected $pay_period_obj = null;
	protected $pay_period_schedule_obj = null;
	protected $job_obj = null;
	protected $job_item_obj = null;
	protected $meal_policy_obj = null;
	protected $punch_obj = null;

	protected $in_punch_obj = null;
	protected $out_punch_obj = null;

	protected $plf = null;
	protected $is_total_time_calculated = false;

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                => 'ID',
				//'user_date_id' => 'UserDateID',
				'user_id'           => 'User',
				'date_stamp'        => 'DateStamp',
				'pay_period_id'     => 'PayPeriod',
				'branch_id'         => 'Branch',
				'department_id'     => 'Department',
				'job_id'            => 'Job',
				'job_item_id'       => 'JobItem',
				'quantity'          => 'Quantity',
				'bad_quantity'      => 'BadQuantity',
				'total_time'        => 'TotalTime',
				'actual_total_time' => 'ActualTotalTime',
				//'meal_policy_id' => 'MealPolicyID',
				'note'              => 'Note',
				'other_id1'         => 'OtherID1',
				'other_id2'         => 'OtherID2',
				'other_id3'         => 'OtherID3',
				'other_id4'         => 'OtherID4',
				'other_id5'         => 'OtherID5',
				'deleted'           => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|UserFactory
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	/**
	 * @return null|object
	 */
	function getPLFByPunchControlID() {
		if ( $this->plf == null && $this->getID() != false ) {
			$this->plf = TTnew( 'PunchListFactory' );
			$this->plf->getByPunchControlID( $this->getID() );
		}

		return $this->plf;
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodScheduleObject() {
		if ( is_object( $this->pay_period_schedule_obj ) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if ( TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();

					return $this->pay_period_schedule_obj;
				}
			}

			return false;
		}
	}

	/**
	 * @return null
	 */
	function getShiftData() {
		if ( $this->shift_data == null && is_object( $this->getPunchObject() )
				&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) {
			if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
				$this->shift_data = $this->getPayPeriodScheduleObject()->getShiftData( null, $this->getUser(), $this->getPunchObject()->getTimeStamp(), 'nearest_shift', $this );
			} else {
				Debug::Text( 'No pay period schedule found for user ID: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $this->shift_data;
	}

	/**
	 * @return bool
	 */
	function getJobObject() {
		return $this->getGenericObject( 'JobListFactory', $this->getJob(), 'job_obj' );
	}

	/**
	 * @return bool
	 */
	function getJobItemObject() {
		return $this->getGenericObject( 'JobItemListFactory', $this->getJobItem(), 'job_item_obj' );
	}

	/**
	 * @return bool|null
	 */
	function getPunchObject() {
		if ( is_object( $this->punch_obj ) ) {
			return $this->punch_obj;
		}

		return false;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setPunchObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->punch_obj = $obj;

			//Set the user/datestamp based on the punch.
			if ( $obj->getUser() != false && $obj->getUser() != $this->getUser() ) {
				$this->setUser( $obj->getUser() );
			}
			if ( $obj->getTimeStamp() != false && ( $this->getDateStamp() == false || TTDate::getMiddleDayEpoch( $obj->getTimeStamp() ) != TTDate::getMiddleDayEpoch( $this->getDateStamp() ) ) ) {
				$this->setDateStamp( $obj->getTimeStamp() );
			}

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param null $value
	 * @return bool
	 */
	function setPayPeriod( $value = null ) {
		if ( $value == null ) {
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}
		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|false|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::getMiddleDayEpoch( TTDate::strtotime( $value ) );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = (int)$value;
		if ( $value > 0 ) {
			$value = TTDate::getMiddleDayEpoch( $value );
			if ( $this->getDateStamp() !== $value && $this->getOldDateStamp() != $this->getDateStamp() && (int)$this->getDateStamp() != 0 ) {
				//Only set OldDateStamp if its not empty, that way it won't override an already set OldDateStamp that is valid.
				Debug::Text( ' Setting Old DateStamp... Current Old DateStamp: ' . (int)$this->getOldDateStamp() . ' Current DateStamp: ' . (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->setOldDateStamp( $this->getDateStamp() );
			}
		}

		$retval = $this->setGenericDataValue( 'date_stamp', $value );

		if ( $value > 0 ) {
			$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
		}

		return $retval;
	}

	/**
	 * This must be called after PunchObject() has been set and before isValid() is called.
	 * @return bool
	 */
	function findUserDate() {
		/*
			Issues to consider:
				** Timezones, if one employee is in PST and the payroll administrator/pay period is in EST, if the employee
				** punches in at 11:00PM PST, its actually 2AM EST on the next day, so which day does the time get assigned to?
				** Use the employees preferred timezone to determine the proper date, otherwise if we use the PP schedule timezone it may
				** be a little confusing to employees because they may punch in on one day and have the time appears under different day.

				1. Employee punches out at 11:00PM, then is called in early at 4AM to start a new shift.
				Don't want to pair these punches.

				2. Employee starts 11:00PM shift late at 1:00AM the next day. Works until 7AM, then comes in again
				at 11:00PM the same day and works until 4AM, then 4:30AM to 7:00AM. The 4AM-7AM punches need to be paired on the same day.

				3. Ambulance EMT works 36hours straight in a single punch.

				*Perhaps we should handle lunch punches and normal punches differently? Lunch punches have
				a different "continuous time setting then normal punches.

				*Change daily continuous time to:
				* Group (Normal) Punches: X hours before midnight to punches X hours after midnight
				* Group (Lunch/Break) Punches: X hours before midnight to punches X hours after midnight
				*	Normal punches X hours after midnight group to punches X hours before midnight.
				*	Lunch/Break punches X hours after midnight group to punches X hours before midnight.

				OR, what if we change continuous time to be just the gap between punches that cause
					a new day to start? Combine this with daily cont. time so we know what the window
					is for punches to begin the gap search. Or we can always just search for a previous
					punch Xhrs before the current punch.
					- Do we look back to a In punch, or look back to an Out punch though? I think an Out Punch.
						What happens if they forgot to punch out though?
					Logic:
						If this is an Out punch:
							Find previous punch back to maximum shift time to find an In punch to pair it with.
						Else, if this is an In punch:
							Find previous punch back to maximum shift time to find an Out punch to combine it with.
							If out punch is found inside of new_shift trigger time, we place this punch on the previous day.
							Else: we place this punch on todays date.


				* Minimum time between punches to cause a new shift to start: Xhrs (default: 4hrs)
					new_day_trigger_time
					Call it: Minimum time-off that triggers new shift:
						Minimum Time-Off Between Shifts:
				* Maximum shift time: Xhrs (for ambulance service) default to 16 or 24hrs?
					This is essentially how far back we look for In punch to pair out punches with.
					maximum_shift_length
					- Add checks to ensure that no punch pair exceeds the maximum_shift_length
		*/

		//Don't allow user_id=0, that is only used for open scheduled shifts, and sometimes this can sneak through during import.
		if ( TTUUID::castUUID( $this->getUser() ) == TTUUID::getZeroID() ) {
			Debug::Text( 'ERROR: User ID is 0!: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		/*
		This needs to be able to run before Validate is called, so we can validate the pay period schedule.
		*/
		if ( $this->getDateStamp() == false ) {
			$this->setDateStamp( $this->getPunchObject()->getTimeStamp() );
		}

		Debug::Text( ' Finding DateStamp: ' . TTDate::getDate( 'DATE+TIME', $this->getPunchObject()->getTimeStamp() ) . ' Punch Control: ' . $this->getID() . ' User: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
		$shift_data = $this->getShiftData();
		if ( is_array( $shift_data ) ) {
			switch ( $this->getPayPeriodScheduleObject()->getShiftAssignedDay() ) {
				default:
				case 10: //Day they start on
				case 40: //Split at midnight
					if ( !isset( $shift_data['first_in']['time_stamp'] ) ) {
						$shift_data['first_in']['time_stamp'] = $shift_data['last_out']['time_stamp'];
					}
					//Can't use the First In user_date_id because it may need to be changed when editing a punch.
					//Debug::Text('Assign Shifts to the day they START on... Date: '. TTDate::getDate('DATE', $shift_data['first_in']['time_stamp']), __FILE__, __LINE__, __METHOD__, 10);
					$user_date_epoch = $shift_data['first_in']['time_stamp'];
					break;
				case 20: //Day they end on
					if ( !isset( $shift_data['last_out']['time_stamp'] ) ) {
						$shift_data['last_out']['time_stamp'] = $shift_data['first_in']['time_stamp'];
					}
					Debug::Text( 'Assign Shifts to the day they END on... Date: ' . TTDate::getDate( 'DATE', $shift_data['last_out']['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );
					$user_date_epoch = $shift_data['last_out']['time_stamp'];
					break;
				case 30: //Day with most time worked
					Debug::Text( 'Assign Shifts to the day they WORK MOST on... Date: ' . TTDate::getDate( 'DATE', $shift_data['day_with_most_time'] ), __FILE__, __LINE__, __METHOD__, 10 );
					$user_date_epoch = $shift_data['day_with_most_time'];
					break;
			}
		} else {
			Debug::Text( 'Not using shift data...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->getPunchObject()->getDeleted() == true ) {
				//Check to see if there is another punch in the punch pair, and use that timestamp to assign days instead.
				Debug::Text( 'Punch is being deleted, use timestamp from other punch in pair if it exists...', __FILE__, __LINE__, __METHOD__, 10 );

				$plf = TTNew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
				$plf->getByPunchControlId( $this->getId() );
				if ( $plf->getRecordCount() > 0 ) {
					foreach ( $plf as $p_obj ) {
						if ( $p_obj->getId() != $this->getPunchObject()->getId() ) {
							$user_date_epoch = $p_obj->getTimeStamp();
							Debug::Text( 'Using timestamp from Punch: ' . $this->getPunchObject()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							break;
						}
					}
				} else {
					Debug::Text( 'No punches left in punch pair...', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
				unset( $plf, $p_obj );
			} else {
				$user_date_epoch = $this->getPunchObject()->getTimeStamp();
			}
		}

		if ( isset( $user_date_epoch ) && $user_date_epoch > 0 ) {
			Debug::Text( 'Found DateStamp: ' . $user_date_epoch . ' Based On: ' . TTDate::getDate( 'DATE+TIME', $user_date_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

			return $this->setDateStamp( $user_date_epoch );
		}

		Debug::Text( 'No shift data to use to find DateStamp, using timestamp only: ' . TTDate::getDate( 'DATE+TIME', $this->getPunchObject()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool
	 */
	function getOldDateStamp() {
		return $this->getGenericTempDataValue( 'old_date_stamp' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOldDateStamp( $value ) {
		Debug::Text( ' Setting Old DateStamp: ' . TTDate::getDate( 'DATE', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericTempDataValue( 'old_date_stamp', TTDate::getMiddleDayEpoch( $value ) );
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

		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultBranch();
			Debug::Text( 'Using Default Branch: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDepartment() {
		return $this->getGenericDataValue( 'department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDepartment( $value ) {
		$value = TTUUID::castUUID( $value );

		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultDepartment();
			Debug::Text( 'Using Default Department: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->setGenericDataValue( 'department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJob() {
		return $this->getGenericDataValue( 'job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJob( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJob();
			Debug::Text( 'Using Default Job: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->setGenericDataValue( 'job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJobItem() {
		return $this->getGenericDataValue( 'job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJobItem();
			Debug::Text( 'Using Default Job Item: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->setGenericDataValue( 'job_item_id', $value );
	}

	/**
	 * @return bool|float
	 */
	function getQuantity() {
		return $this->getGenericDataValue( 'quantity' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setQuantity( $value ) {
		if ( $value == false || $value == 0 || $value == '' ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'quantity', $value );
	}

	/**
	 * @return bool|float
	 */
	function getBadQuantity() {
		return $this->getGenericDataValue( 'bad_quantity' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBadQuantity( $value ) {
		if ( $value == false || $value == 0 || $value == '' ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'bad_quantity', $value );
	}

	/**
	 * @return bool|int
	 */
	function getTotalTime() {
		return $this->getGenericDataValue( 'total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTotalTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'total_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getActualTotalTime() {
		return $this->getGenericDataValue( 'actual_total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setActualTotalTime( $value ) {
		$value = (int)$value;
		if ( $value < 0 ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'actual_total_time', $value );
	}
	/*
		function getMealPolicyID() {
			return $this->getGenericDataValue( 'meal_policy_id' );
		}
		function setMealPolicyID($id) {
			$id = trim($id);

			if ( $id == '' OR empty($id) ) {
				$id = NULL;
			}

			$mplf = TTnew( 'MealPolicyListFactory' );

			if ( $id == NULL
					OR
					$this->Validator->isResultSetWithRows(	'meal_policy',
															$mplf->getByID($id),
															TTi18n::gettext('Meal Policy is invalid')
														) ) {

				$this->setGenericDataValue( 'meal_policy_id', $id );

				return TRUE;
			}

			return FALSE;
		}
	*/
	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID1() {
		return $this->getGenericDataValue( 'other_id1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id1', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID2() {
		return $this->getGenericDataValue( 'other_id2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id2', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID3() {
		return $this->getGenericDataValue( 'other_id3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID3( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id3', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID4() {
		return $this->getGenericDataValue( 'other_id4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id4', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID5() {
		return $this->getGenericDataValue( 'other_id5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id5', $value );
	}

	/**
	 * @param bool $force
	 * @return bool
	 * @noinspection PhpUndefinedVariableInspection
	 */
	function calcTotalTime( $force = true ) {
		if ( $force == true || $this->is_total_time_calculated == false ) {
			$this->is_total_time_calculated = true;

			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$plf->getByPunchControlId( $this->getId() );
			//Make sure punches are in In/Out pairs before we bother calculating.
			if ( $plf->getRecordCount() > 0 && ( $plf->getRecordCount() % 2 ) == 0 ) {
				Debug::text( ' Found Punches to calculate.', __FILE__, __LINE__, __METHOD__, 10 );
				$in_pair = false;
				foreach ( $plf as $punch_obj ) {
					//Check for proper in/out pairs
					//First row should be an Out status (reverse ordering)
					Debug::text( ' Punch: Status: ' . $punch_obj->getStatus() . ' TimeStamp: ' . $punch_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $punch_obj->getStatus() == 20 ) {
						//Debug::text(' Found Out Status, starting pair: ', __FILE__, __LINE__, __METHOD__, 10);
						$this->out_punch_obj = $punch_obj;

						$out_stamp = $punch_obj->getTimeStamp();
						$out_actual_stamp = $punch_obj->getActualTimeStamp();
						$in_pair = true;
					} else if ( $in_pair == true ) {
						$this->in_punch_obj = $punch_obj;

						$punch_obj->setScheduleID( $punch_obj->findScheduleID( null, $this->getUser() ) ); //Find Schedule Object for this Punch
						$in_stamp = $punch_obj->getTimeStamp();
						$in_actual_stamp = $punch_obj->getActualTimeStamp();
						//Got a pair... Totaling.
						//Debug::text(' Found a pair... Totaling: ', __FILE__, __LINE__, __METHOD__, 10);
						if ( $out_stamp != '' && $in_stamp != '' ) {
							//Due to DST, always pay the employee based on the time they actually worked,
							//which is handled automatically by simple epoch math.
							//Therefore in fall they get paid one hour more, and spring one hour less.
							$total_time = ( $out_stamp - $in_stamp );// + TTDate::getDSTOffset( $in_stamp, $out_stamp );
						}
						if ( $out_actual_stamp != '' && $in_actual_stamp != '' ) {
							$actual_total_time = ( $out_actual_stamp - $in_actual_stamp );
						}
					}
				}

				if ( isset( $total_time ) ) {
					Debug::text( ' Setting TotalTime: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10 );

					$this->setTotalTime( $total_time );
					$this->setActualTotalTime( $actual_total_time );

					return true;
				}
			} else {
				Debug::text( ' No Punches to calculate, or punches arent in pairs. Set total to 0', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setTotalTime( 0 );
				$this->setActualTotalTime( 0 );

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function changePreviousPunchType() {
		Debug::text( ' Previous Punch to Lunch/Break...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( is_object( $this->getPunchObject() ) ) {
			if ( $this->getPunchObject()->getType() == 20 && $this->getPunchObject()->getStatus() == 10 ) {
				Debug::text( ' bbPrevious Punch to Lunch...', __FILE__, __LINE__, __METHOD__, 10 );

				//We used to use getShiftData() then pull out the previous punch from that, however that can cause problems
				//based on the Minimum Time-Off Between Shifts. Either way though that can't be less than the lunch/break autodetection time.
				$previous_punch_obj = $this->getPunchObject()->getPreviousPunchObject( $this->getPunchObject()->getActualTimeStamp() );
				if ( is_object( $previous_punch_obj ) && $previous_punch_obj->getType() != 20 ) {
					Debug::text( ' Previous Punch ID: ' . $previous_punch_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->getPunchObject()->setScheduleID( $this->getPunchObject()->findScheduleID() );
					if ( $this->getPunchObject()->inMealPolicyWindow( $this->getPunchObject()->getTimeStamp(), $previous_punch_obj->getTimeStamp(), $previous_punch_obj->getStatus() ) == true ) {
						Debug::text( ' Previous Punch needs to change to Lunch...', __FILE__, __LINE__, __METHOD__, 10 );

						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getById( $previous_punch_obj->getId() );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( ' Modifying previous punch...', __FILE__, __LINE__, __METHOD__, 10 );
							$pf = $plf->getCurrent();
							$pf->setUser( $this->getUser() );
							$pf->setType( 20 );                       //Lunch
							//If we start re-rounding this punch we have to recalculate the total for the previous punch_control too.
							$pf->setTimeStamp( $pf->getTimeStamp() ); //Re-round timestamp now that its a lunch punch.
							if ( $pf->Save( false ) == true ) {
								$pcf = $pf->getPunchControlObject();
								$pcf->setPunchObject( $pf );
								$pcf->setEnableCalcUserDateID( true );
								$pcf->setEnableCalcTotalTime( true );
								$pcf->setEnableCalcSystemTotalTime( true );
								$pcf->setEnableCalcWeeklySystemTotalTime( true );
								$pcf->setEnableCalcUserDateTotal( true );
								if ( $pcf->isValid() == true ) {
									Debug::Text( ' Punch Control is valid, saving...', __FILE__, __LINE__, __METHOD__, 10 );
									if ( $pcf->Save( true, true ) == true ) { //Force isNew() lookup.\
										Debug::text( ' Returning TRUE!', __FILE__, __LINE__, __METHOD__, 10 );

										return true;
									}
								}
							}
						}
					}
				}
			} else if ( $this->getPunchObject()->getType() == 30 && $this->getPunchObject()->getStatus() == 10 ) {
				Debug::text( ' bbPrevious Punch to Break...', __FILE__, __LINE__, __METHOD__, 10 );

				//We used to use getShiftData() then pull out the previous punch from that, however that can cause problems
				//based on the Minimum Time-Off Between Shifts. Either way though that can't be less than the lunch/break autodetection time.
				$previous_punch_obj = $this->getPunchObject()->getPreviousPunchObject( $this->getPunchObject()->getActualTimeStamp() );
				if ( is_object( $previous_punch_obj ) && $previous_punch_obj->getType() != 30 ) {
					Debug::text( ' Previous Punch ID: ' . $previous_punch_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->getPunchObject()->setScheduleID( $this->getPunchObject()->findScheduleID() );
					if ( $this->getPunchObject()->inBreakPolicyWindow( $this->getPunchObject()->getTimeStamp(), $previous_punch_obj->getTimeStamp(), $previous_punch_obj->getStatus() ) == true ) {
						Debug::text( ' Previous Punch needs to change to Break...', __FILE__, __LINE__, __METHOD__, 10 );

						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getById( $previous_punch_obj->getId() );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( ' Modifying previous punch...', __FILE__, __LINE__, __METHOD__, 10 );

							$pf = $plf->getCurrent();
							$pf->setUser( $this->getUser() );
							$pf->setType( 30 );                       //Break
							//If we start re-rounding this punch we have to recalculate the total for the previous punch_control too.
							$pf->setTimeStamp( $pf->getTimeStamp() ); //Re-round timestamp now that its a break punch.
							if ( $pf->Save( false ) == true ) {
								$pcf = $pf->getPunchControlObject();
								$pcf->setPunchObject( $pf );
								$pcf->setEnableCalcUserDateID( true );
								$pcf->setEnableCalcTotalTime( true );
								$pcf->setEnableCalcSystemTotalTime( true );
								$pcf->setEnableCalcWeeklySystemTotalTime( true );
								$pcf->setEnableCalcUserDateTotal( true );
								if ( $pcf->isValid() == true ) {
									Debug::Text( ' Punch Control is valid, saving...', __FILE__, __LINE__, __METHOD__, 10 );
									if ( $pcf->Save( true, true ) == true ) { //Force isNew() lookup.\
										Debug::text( ' Returning TRUE!', __FILE__, __LINE__, __METHOD__, 10 );

										return true;
									}
								}
							}
						}
					}
				}
			}
		}

		Debug::text( ' Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcSystemTotalTime() {
		if ( isset( $this->calc_system_total_time ) ) {
			return $this->calc_system_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcSystemTotalTime( $bool ) {
		$this->calc_system_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcWeeklySystemTotalTime() {
		if ( isset( $this->calc_weekly_system_total_time ) ) {
			return $this->calc_weekly_system_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcWeeklySystemTotalTime( $bool ) {
		$this->calc_weekly_system_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcException() {
		if ( isset( $this->calc_exception ) ) {
			return $this->calc_exception;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcException( $bool ) {
		$this->calc_exception = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnablePreMatureException() {
		if ( isset( $this->premature_exception ) ) {
			return $this->premature_exception;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnablePreMatureException( $bool ) {
		$this->premature_exception = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcUserDateTotal() {
		if ( isset( $this->calc_user_date_total ) ) {
			return $this->calc_user_date_total;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcUserDateTotal( $bool ) {
		$this->calc_user_date_total = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcUserDateID() {
		if ( isset( $this->calc_user_date_id ) ) {
			return $this->calc_user_date_id;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcUserDateID( $bool ) {
		$this->calc_user_date_id = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcTotalTime() {
		if ( isset( $this->calc_total_time ) ) {
			return $this->calc_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcTotalTime( $bool ) {
		$this->calc_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableStrictJobValidation() {
		if ( isset( $this->strict_job_validiation ) ) {
			return $this->strict_job_validiation;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableStrictJobValidation( $bool ) {
		$this->setIsValid( false ); //Force revalidation when data is changed.
		$this->strict_job_validiation = $bool;

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		Debug::text( 'Validating...', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
		// Pay Period
		if ( $this->getPayPeriod() !== false && $this->getPayPeriod() != TTUUID::getZeroID() ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$this->Validator->isResultSetWithRows( 'pay_period',
												   $pplf->getByID( $this->getPayPeriod() ),
												   TTi18n::gettext( 'Invalid Pay Period' )
			);
		}


		// Date
		$this->Validator->isDate( 'date_stamp',
								  $this->getDateStamp(),
								  TTi18n::gettext( 'Incorrect date' ) . '(a)'
		);
		if ( $this->Validator->isError( 'date_stamp' ) == false ) {
			if ( $this->getDateStamp() == '' || $this->getDateStamp() <= 0 ) {
				$this->Validator->isTRUE( 'date_stamp',
										  false,
										  TTi18n::gettext( 'Incorrect date' ) . '(b)' );
			}
		}

		// Branch
		if ( $this->getBranch() !== false && $this->getBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows( 'branch',
												   $blf->getByID( $this->getBranch() ),
												   TTi18n::gettext( 'Branch does not exist' )
			);
		}
		// Department
		if ( $this->getDepartment() !== false && $this->getDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows( 'department',
												   $dlf->getByID( $this->getDepartment() ),
												   TTi18n::gettext( 'Department does not exist' )
			);
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			// Job
			if ( $this->getJob() !== false && $this->getJob() != TTUUID::getZeroID() ) {
				$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
				$this->Validator->isResultSetWithRows( 'job',
													   $jlf->getByID( $this->getJob() ),
													   TTi18n::gettext( 'Job does not exist' )
				);
			}
			// Job Item
			if ( $this->getJobItem() !== false && $this->getJobItem() != TTUUID::getZeroID() ) {
				$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
				$this->Validator->isResultSetWithRows( 'job_item',
													   $jilf->getByID( $this->getJobItem() ),
													   TTi18n::gettext( 'Job Item does not exist' )
				);
			}
			// Quantity
			if ( $this->getQuantity() != '' ) {
				$this->Validator->isFloat( 'quantity',
										   $this->getQuantity(),
										   TTi18n::gettext( 'Incorrect quantity' )
				);
			}
			// Bad quantity
			if ( $this->getBadQuantity() != '' ) {
				$this->Validator->isFloat( 'bad_quantity',
										   $this->getBadQuantity(),
										   TTi18n::gettext( 'Incorrect bad quantity' )
				);
			}
		}

		// Total time
		if ( $this->getTotalTime() !== false ) {
			$this->Validator->isNumeric( 'total_time',
										 $this->getTotalTime(),
										 TTi18n::gettext( 'Incorrect total time' )
			);
		}
		// Actual total time
		if ( $this->getActualTotalTime() !== false ) {
			$this->Validator->isNumeric( 'actual_total_time',
										 $this->getActualTotalTime(),
										 TTi18n::gettext( 'Incorrect actual total time' )
			);
		}
		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too long' ),
										0,
										1024
			);
		}
		// Other ID 1
		if ( $this->getOtherID1() != '' ) {
			$this->Validator->isLength( 'other_id1',
										$this->getOtherID1(),
										TTi18n::gettext( 'Other ID 1 is invalid' ),
										1, 255
			);
		}
		// Other ID 2
		if ( $this->getOtherID2() != '' ) {
			$this->Validator->isLength( 'other_id2',
										$this->getOtherID2(),
										TTi18n::gettext( 'Other ID 2 is invalid' ),
										1, 255
			);
		}
		// Other ID 3
		if ( $this->getOtherID3() != '' ) {
			$this->Validator->isLength( 'other_id3',
										$this->getOtherID3(),
										TTi18n::gettext( 'Other ID 3 is invalid' ),
										1, 255
			);
		}
		// Other ID 4
		if ( $this->getOtherID4() != '' ) {
			$this->Validator->isLength( 'other_id4',
										$this->getOtherID4(),
										TTi18n::gettext( 'Other ID 4 is invalid' ),
										1, 255
			);
		}
		// Other ID 5
		if ( $this->getOtherID5() != '' ) {
			$this->Validator->isLength( 'other_id5',
										$this->getOtherID5(),
										TTi18n::gettext( 'Other ID 5 is invalid' ),
										1, 255
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//See if the user_id changed, if so prevent it from being saved, as the user_id should never be changed on a punch_control record as it will cause problems with recalculating.
		if ( $this->getGenericOldDataValue( 'user_id' ) != false && $this->getUser() != $this->getGenericOldDataValue( 'user_id' ) ) {
			$this->Validator->isTRUE( 'user_id',
									  false,
									  TTi18n::gettext( 'Punch cannot be assigned to a different employee once created' ) );
		}

		//Call this here so getShiftData can get the correct total time, before we call findUserDate.
		if ( $this->getEnableCalcTotalTime() == true ) {
			$this->calcTotalTime();
		}

		if ( is_object( $this->getPunchObject() ) ) {
			$this->findUserDate();
		}
		Debug::text( 'DateStamp: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );

		//Don't check for a valid pay period here, do that in PunchFactory->Validate(), as we need to allow users to delete punches that were created outside pay periods in legacy versions.
		if ( $this->getDeleted() == false && $this->getDateStamp() == false ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
		} else if ( $this->getDateStamp() != false && is_object( $this->getPayPeriodObject() ) && $this->getPayPeriodObject()->getIsLocked() == true ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Pay Period is Currently Locked' ) );
		}

		//Make sure the user isn't entering punches before the employees hire or after termination date, as its likely they wouldn't have a wage
		//set for that anyways and wouldn't get paid for it.
		//We must allow deleting punches after their termination date so timesheets can be cleaned up if necessary.
		if ( ( $this->getDeleted() == false && ( is_object( $this->getPunchObject() ) && $this->getPunchObject()->getDeleted() == false ) ) && $this->getDateStamp() != false && is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getHireDate() != '' && TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTRUE( 'date_stamp',
										  false,
										  TTi18n::gettext( 'Punch is before employees hire date' ) );
			}

			if ( $this->getUserObject()->getTerminationDate() != '' && TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
				$this->Validator->isTRUE( 'date_stamp',
										  false,
										  TTi18n::gettext( 'Punch is after employees termination date' ) );
			} else if ( $this->getUserObject()->getStatus() != 10 && $this->getUserObject()->getTerminationDate() == '' ) {
				$this->Validator->isTRUE( 'user_id',
										  false,
										  TTi18n::gettext( 'Employee is not currently active' ) );
			}
		}

		//Skip these checks if they are deleting a punch.
		if ( is_object( $this->getPunchObject() ) && $this->getPunchObject()->getDeleted() == false ) {
			$plf = $this->getPLFByPunchControlID();
			if ( $plf !== null && ( ( $this->isNew() && $plf->getRecordCount() == 2 ) || $plf->getRecordCount() > 2 ) ) {
				//TTi18n::gettext('Punch Control can not have more than two punches. Please use the Add Punch button instead')
				//They might be trying to insert a punch inbetween two others?
				$this->Validator->isTRUE( 'punch_control',
										  false,
										  TTi18n::gettext( 'Time conflicts with another punch on this day (c)' ) );
			}

			//Sometimes shift data won't return all the punches to proper check for conflicting punches.
			//So we need to make sure other punches assigned to this punch_control record are proper.
			//This fixes the bug of having shifts: 2:00AM Lunch Out, 2:30AM Lunch In, 6:00AM Out, 10:00PM In (in that order), then trying to move the 10PM punch to the open IN slot before the 2AM punch.
			if ( $plf->getRecordCount() > 0 ) {
				foreach ( $plf as $p_obj ) {
					if ( $p_obj->getID() != $this->getPunchObject()->getID() ) {
						if ( $this->getPunchObject()->getStatus() == 10 && $p_obj->getStatus() == 20 && $this->getPunchObject()->getTimeStamp() > $p_obj->getTimeStamp() ) {
							//Make sure we match on status==10 for both sides, otherwise this fails to catch the problem case.
							// Also test $p_obj->getStatus() == 20, to catch cases where a Break In punch is followed by a Lunch Out punch, but the Break In timestamp is AFTER the Lunch Out timestamp.
							$this->Validator->isTRUE( 'time_stamp',
													  false,
													  TTi18n::gettext( 'In punches cannot occur after an out punch, in the same punch pair (a)' ) );
						} else if ( $this->getPunchObject()->getStatus() == 20 && $p_obj->getStatus() == 10 && $this->getPunchObject()->getTimeStamp() < $p_obj->getTimeStamp() ) {
							$this->Validator->isTRUE( 'time_stamp',
													  false,
													  TTi18n::gettext( 'Out punches cannot occur before an in punch, in the same punch pair (a)' ) );
						}
					}
				}
			}
			unset( $p_obj );

			if ( $this->Validator->isValid() == true ) { //Don't bother checking these resource intensive issues if there are already validation errors.

				$shift_data = $this->getShiftData();
				if ( is_array( $shift_data ) && $this->Validator->hasError( 'time_stamp' ) == false ) {
					foreach ( $shift_data['punches'] as $punch_data ) {
						//Make sure there aren't two In punches, or two Out punches in the same pair.
						//This fixes the bug where if you have an In punch, then click the blank cell below it
						//to add a new punch, but change the status from Out to In instead.
						if ( isset( $punches[$punch_data['punch_control_id']][$punch_data['status_id']] ) ) {
							if ( $punch_data['status_id'] == 10 ) {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'In punches cannot occur twice in the same punch pair, you may want to make this an out punch instead' ) . '(b)' );
							} else {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'Out punches cannot occur twice in the same punch pair, you may want to make this an in punch instead' ) . '(b)' );
							}
						}

						//Debug::text(' Current Punch Object: ID: '. $this->getPunchObject()->getId() .' TimeStamp: '. $this->getPunchObject()->getTimeStamp() .' Status: '. $this->getPunchObject()->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
						//Debug::text(' Looping Punch Object: ID: '. $punch_data['id'] .' TimeStamp: '. $punch_data['time_stamp'] .' Status: '.$punch_data['status_id'], __FILE__, __LINE__, __METHOD__, 10);

						//Check for another punch that matches the timestamp and status.
						if ( $this->getPunchObject()->getID() != $punch_data['id'] ) {
							if ( $this->getPunchObject()->getTimeStamp() == $punch_data['time_stamp'] && $this->getPunchObject()->getStatus() == $punch_data['status_id'] ) {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'Time and status match that of another punch, this could be due to rounding' ) . ' (' . TTDate::getDate( 'DATE+TIME', $punch_data['time_stamp'] ) . ')' );
								break; //Break the loop on validation error, so we don't get multiple errors that may be confusing.
							}
						}

						//Check for another punch that matches the timestamp and NOT status in the SAME punch pair.
						if ( $this->getPunchObject()->getID() != $punch_data['id'] && $this->getID() == $punch_data['punch_control_id'] ) {
							if ( $this->getPunchObject()->getTimeStamp() == $punch_data['time_stamp'] && $this->getPunchObject()->getStatus() != $punch_data['status_id'] ) {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'Time matches another punch in the same punch pair, this could be due to rounding' ) . ' (' . TTDate::getDate( 'DATE+TIME', $punch_data['time_stamp'] ) . ')' );
								break; //Break the loop on validation error, so we don't get multiple errors that may be confusing.
							}
						}

						$punches[$punch_data['punch_control_id']][$punch_data['status_id']] = $punch_data;
					}
					unset( $punch_data );

					if ( isset( $punches[$this->getID()] ) ) {
						Debug::text( 'Current Punch ID: ' . $this->getPunchObject()->getId() . ' Punch Control ID: ' . $this->getID() . ' Status: ' . $this->getPunchObject()->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );
						//Debug::Arr($punches, 'Punches Arr: ', __FILE__, __LINE__, __METHOD__, 10);

						if ( $this->getPunchObject()->getStatus() == 10 && isset( $punches[$this->getID()][20] ) && $this->getPunchObject()->getTimeStamp() > $punches[$this->getID()][20]['time_stamp'] ) {
							$this->Validator->isTRUE( 'time_stamp',
													  false,
													  TTi18n::gettext( 'In punches cannot occur after an out punch, in the same punch pair' ) );
						} else if ( $this->getPunchObject()->getStatus() == 20 && isset( $punches[$this->getID()][10] ) && $this->getPunchObject()->getTimeStamp() < $punches[$this->getID()][10]['time_stamp'] ) {
							$this->Validator->isTRUE( 'time_stamp',
													  false,
													  TTi18n::gettext( 'Out punches cannot occur before an in punch, in the same punch pair' ) );
						} else {
							Debug::text( 'bPunch does not match any other punch pair.', __FILE__, __LINE__, __METHOD__, 10 );

							$punch_neighbors = Misc::getArrayNeighbors( $punches, $this->getID(), 'both' );
							//Debug::Arr($punch_neighbors, ' Punch Neighbors: ', __FILE__, __LINE__, __METHOD__, 10);

							if ( isset( $punch_neighbors['next'] ) && isset( $punches[$punch_neighbors['next']] ) ) {
								Debug::text( 'Found Next Punch...', __FILE__, __LINE__, __METHOD__, 10 );
								if ( ( isset( $punches[$punch_neighbors['next']][10] ) && $this->getPunchObject()->getTimeStamp() > $punches[$punch_neighbors['next']][10]['time_stamp'] )
										|| ( isset( $punches[$punch_neighbors['next']][20] ) && $this->getPunchObject()->getTimeStamp() > $punches[$punch_neighbors['next']][20]['time_stamp'] ) ) {
									$this->Validator->isTRUE( 'time_stamp',
															  false,
															  TTi18n::gettext( 'Time conflicts with another punch on this day' ) . ' (a)' );
								}
							}

							if ( isset( $punch_neighbors['prev'] ) && isset( $punches[$punch_neighbors['prev']] ) ) {
								Debug::text( 'Found prev Punch...', __FILE__, __LINE__, __METHOD__, 10 );

								//This needs to take into account DST. Specifically if punches are like this:
								//03-Nov-12: IN: 10:00PM
								//04-Nov-12: OUT: 1:00AM L
								//04-Nov-12: IN: 1:30AM L
								//04-Nov-12: OUT: 6:30AM L
								//Since the 1AM to 2AM occur twice due to the "fall back" DST change, we need to allow those punches to be entered.
								if ( ( isset( $punches[$punch_neighbors['prev']][10] ) && ( $this->getPunchObject()->getTimeStamp() < $punches[$punch_neighbors['prev']][10]['time_stamp'] && TTDate::doesRangeSpanDST( $this->getPunchObject()->getTimeStamp(), $punches[$punch_neighbors['prev']][10]['time_stamp'] ) == false ) )
										||
										( isset( $punches[$punch_neighbors['prev']][20] ) && ( $this->getPunchObject()->getTimeStamp() < $punches[$punch_neighbors['prev']][20]['time_stamp'] && TTDate::doesRangeSpanDST( $this->getPunchObject()->getTimeStamp(), $punches[$punch_neighbors['prev']][20]['time_stamp'] ) == false ) )
								) {
									$this->Validator->isTRUE( 'time_stamp',
															  false,
															  TTi18n::gettext( 'Time conflicts with another punch on this day' ) . ' (b)' );
								}
							}
						}

						//Check to make sure punches don't exceed maximum shift time.
						$maximum_shift_time = $plf->getPayPeriodMaximumShiftTime( $this->getPunchObject()->getUser() );
						Debug::text( 'Maximum shift time: ' . $maximum_shift_time, __FILE__, __LINE__, __METHOD__, 10 );
						if ( $shift_data['total_time'] > $maximum_shift_time ) {
							$this->Validator->isTRUE( 'time_stamp',
													  false,
													  TTi18n::gettext( 'Punch exceeds maximum shift time of' ) . ' ' . TTDate::getTimeUnit( $maximum_shift_time ) . ' ' . TTi18n::getText( 'hrs set for this pay period schedule' ) );
						}
					}
					unset( $punches );
				}
			}
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE && $this->getEnableStrictJobValidation() == true ) {
			if ( TTUUID::isUUID( $this->getJob() ) && $this->getJob() != TTUUID::getZeroID() && $this->getJob() != TTUUID::getNotExistID() ) {
				$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
				$jlf->getById( $this->getJob() );
				if ( $jlf->getRecordCount() > 0 ) {
					$j_obj = $jlf->getCurrent();

					if ( $this->getDateStamp() != false && $j_obj->isAllowedUser( $this->getUser() ) == false ) {
						$this->Validator->isTRUE( 'job',
												  false,
												  TTi18n::gettext( 'Employee is not assigned to this job' ) );
					}

					if ( $j_obj->isAllowedItem( $this->getJobItem() ) == false ) {
						$this->Validator->isTRUE( 'job_item',
												  false,
												  TTi18n::gettext( 'Task is not assigned to this job' ) );
					}
				}
			}
		}

		if ( $ignore_warning == false ) {
			//Warn users if they are trying to insert punches too far in the future.
			if ( $this->getDateStamp() != false && $this->getDateStamp() > ( time() + ( 86400 * 366 ) ) ) {
				$this->Validator->Warning( 'date_stamp', TTi18n::gettext( 'Date is more than one year in the future' ) );
			}

			//Check to see if timesheet is verified, if so show warning to notify the user.
			if ( is_object( $this->getPayPeriodScheduleObject() )
					&& $this->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified
					$this->Validator->Warning( 'date_stamp', TTi18n::gettext( 'Pay period is already verified, saving these changes will require it to be reverified' ) );
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getBranch() === false ) {
			$this->setBranch( TTUUID::getZeroID() );
		}

		if ( $this->getDepartment() === false ) {
			$this->setDepartment( TTUUID::getZeroID() );
		}

		if ( $this->getJob() === false ) {
			$this->setJob( TTUUID::getZeroID() );
		}

		if ( $this->getJobItem() === false ) {
			$this->setJobItem( TTUUID::getZeroID() );
		}

		if ( $this->getQuantity() === false ) {
			$this->setQuantity( 0 );
		}

		if ( $this->getBadQuantity() === false ) {
			$this->setBadQuantity( 0 );
		}

		if ( $this->getPayPeriod() == false ) {
			$this->setPayPeriod();
		}

		//Set Job default Job Item if required.
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE
				&& TTUUID::isUUID( $this->getJob() ) && $this->getJob() != TTUUID::getZeroID()
				&& ( $this->getJobItem() == TTUUID::getZeroID() || $this->getJobItem() == '' ) ) {
			Debug::text( ' Job is set (' . $this->getJob() . '), but no task is... Using default job item...', __FILE__, __LINE__, __METHOD__, 10 );

			if ( is_object( $this->getJobObject() ) ) {
				Debug::text( ' Default Job Item: ' . $this->getJobObject()->getDefaultItem(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->setJobItem( $this->getJobObject()->getDefaultItem() );
			}
		}

		if ( $this->getEnableCalcTotalTime() == true ) {
			$this->calcTotalTime();
		}

		if ( is_object( $this->getPunchObject() ) ) {
			$this->findUserDate();
		}

		//Check to see if timesheet is verified, if so unverify it on modified punch.
		//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
		if ( is_object( $this->getPayPeriodScheduleObject() )
				&& $this->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
			//Find out if timesheet is verified or not.
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
			$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				//Pay period is verified, delete all records and make log entry.
				Debug::text( 'Pay Period is verified, deleting verification records: ' . $pptsvlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $pptsvlf as $pptsv_obj ) {
					if ( is_object( $this->getPunchObject() ) ) {
						TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText( 'TimeSheet Modified After Verification' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Punch' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->getPunchObject()->getTimeStamp() ), null, $pptsvlf->getTable() );
					}
					$pptsv_obj->setDeleted( true );
					if ( $pptsv_obj->isValid() ) {
						$pptsv_obj->Save();
					}
				}
			}
		}

		$this->changePreviousPunchType();

		return true;
	}

	/**
	 * @return bool
	 */
	function calcUserDate() {
		if ( $this->getEnableCalcUserDateID() == true ) {
			$date_stamp = TTDate::getMiddleDayEpoch( $this->getDateStamp() ); //preSave should already be called before running this function.

			Debug::Text( ' Calculating User ID: ' . $this->getUser() . ' DateStamp: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );

			$shift_data = $this->getShiftData();
			if ( is_array( $shift_data ) ) {
				//Don't re-arrange shifts until all punches are paired and we have enough information.
				//Thats what the count() % 2 is used for.
				if ( $this->getUser() != false
						&& isset( $date_stamp ) && $date_stamp > 0
						&& ( isset( $shift_data['punch_control_ids'] ) && is_array( $shift_data['punch_control_ids'] ) )
						&& ( isset( $shift_data['punches'] ) && count( $shift_data['punches'] ) % 2 == 0 ) ) {
					Debug::Text( 'Assigning all punch_control_ids to User ID: ' . $this->getUser() . ' DateStamp: ' . $date_stamp, __FILE__, __LINE__, __METHOD__, 10 );

					$this->old_date_stamps[] = $date_stamp;
					if ( $this->getOldDateStamp() != false ) {
						$this->old_date_stamps[] = $this->getOldDateStamp();
					}

					$processed_punch_control_ids = [];
					foreach ( $shift_data['punch_control_ids'] as $punch_control_id ) {
						$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
						$pclf->getById( $punch_control_id );
						if ( $pclf->getRecordCount() == 1 ) {
							$processed_punch_control_ids[] = $punch_control_id;
							$pc_obj = $pclf->getCurrent();
							if ( TTDate::getMiddleDayEpoch( $pc_obj->getDateStamp() ) != $date_stamp ) {
								Debug::Text( ' Saving Punch Control ID: ' . $punch_control_id . ' with new DateStamp: ' . $date_stamp . ' Old DateStamp: ' . $pc_obj->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );

								$this->old_date_stamps[] = $pc_obj->getDateStamp();
								$pc_obj->setDateStamp( $date_stamp );
								$pc_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
								$pc_obj->setEnableCalcUserDateTotal( true );
								$pc_obj->setEnableCalcTotalTime( true ); //This is required to make sure Start/End timestamps are populated. This help fix strange bugs with OT being calculated incorrectly due to missing timestamps.
								if ( $pc_obj->isValid() == true ) {
									$pc_obj->Save();
								}
							} else {
								Debug::Text( ' NOT Saving Punch Control ID, as DateStamp didnt change: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
					unset( $pclf, $pc_obj );
					//Debug::Arr($this->old_date_stamps, 'aOld User Date IDs: ', __FILE__, __LINE__, __METHOD__, 10);

					//Handle cases where shift times change enough to cause shifts spanning midnight to be reassigned to different days.
					//For example the punches may look like this:
					// Nov 12th 1:00PM
					// Nov 12th 11:30PM
					// Nov 13th 12:30AM
					// Nov 13th 2:00AM
					//Then the Nov12th 11:30PM punch is modified to be say 2PM, the Nov 13th 12:30AM punch should then be moved to 13th rather than combined with the 12th.
					if ( count( $processed_punch_control_ids ) > 0 ) {
						$plf = TTNew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getByUserIdAndDateStampAndNotPunchControlId( $this->getUser(), $date_stamp, $processed_punch_control_ids );
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								if ( !in_array( $p_obj->getPunchControlID(), $processed_punch_control_ids ) ) {
									Debug::Text( 'aPunches from other shifts exist on this day still... Punch ID: ' . $p_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

									$src_punch_control_obj = $p_obj->getPunchControlObject();
									if ( is_object( $src_punch_control_obj ) ) {
										$src_punch_control_obj->setPunchObject( $p_obj );
										if ( $src_punch_control_obj->isValid() == true ) {
											//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
											//in the case of a drag & drop or something.
											$src_punch_control_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
											$src_punch_control_obj->setEnableCalcUserDateID( false );
											$src_punch_control_obj->setEnableCalcTotalTime( true );
											$src_punch_control_obj->setEnableCalcSystemTotalTime( true );
											$src_punch_control_obj->setEnableCalcWeeklySystemTotalTime( true );
											$src_punch_control_obj->setEnableCalcUserDateTotal( true );
											$src_punch_control_obj->setEnableCalcException( true );
											if ( $src_punch_control_obj->isValid() == true ) {
												$src_punch_control_obj->Save();
												$processed_punch_control_ids[] = $src_punch_control_obj->getID();
											}
										}
									} else {
										Debug::Text( 'ERROR: Unable to get punch control object! Punch Control ID: ' . $p_obj->getPunchControlId(), __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}
						unset( $plf, $src_punch_control_obj, $p_obj );
					}

					Debug::Text( 'Returning TRUE', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				} else {
					Debug::Text( 'Punches are not paired, not re-arranging days...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'No shift data, check for other punches on the same day in case they need to be moved back...', __FILE__, __LINE__, __METHOD__, 10 );

				//Handle cases where a punch pair was moved from one day to this day, then the punches that caused that were deleted, and now
				//it needs to be moved back to the original day.
				$plf = TTNew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
				$plf->getByUserIdAndDateStamp( $this->getUser(), $date_stamp );
				if ( $plf->getRecordCount() > 0 ) {
					foreach ( $plf as $p_obj ) {
						Debug::Text( 'bPunches from other shifts exist on this day still... Punch ID: ' . $p_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

						$src_punch_control_obj = $p_obj->getPunchControlObject();
						$src_punch_control_obj->setPunchObject( $p_obj );
						if ( $src_punch_control_obj->isValid() == true ) {
							//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
							//in the case of a drag & drop or something.
							$src_punch_control_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
							$src_punch_control_obj->setEnableCalcUserDateID( false );
							$src_punch_control_obj->setEnableCalcTotalTime( true );
							$src_punch_control_obj->setEnableCalcSystemTotalTime( true );
							$src_punch_control_obj->setEnableCalcWeeklySystemTotalTime( true );
							$src_punch_control_obj->setEnableCalcUserDateTotal( true );
							$src_punch_control_obj->setEnableCalcException( true );
							if ( $src_punch_control_obj->isValid() == true ) {
								$src_punch_control_obj->Save();
								$processed_punch_control_ids[] = $src_punch_control_obj->getID();
							}
						}
					}
				}
				unset( $plf, $src_punch_control_obj, $p_obj );

				return true;
			}
		}

		Debug::Text( 'Returning FALSE', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function calcUserDateTotal() {
		if ( $this->getEnableCalcUserDateTotal() == true ) {
			Debug::Text( ' Calculating User Date Total...', __FILE__, __LINE__, __METHOD__, 10 );

			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			//Always include OldDateStamp, as punches can move between days (timezone differences), and we need to always update proper records based on punch_control_id.
			$udtlf->getByUserIdAndDateStampAndOldDateStampAndPunchControlId( $this->getUser(), $this->getDateStamp(), $this->getOldDateStamp(), $this->getId() );
			Debug::text( ' Checking for Conflicting User Date Total Records, count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->getDeleted() == true ) {
				//Add a row to the user date total table, as "worked" hours.
				//Edit if it already exists and is not set as override.
				if ( $udtlf->getRecordCount() > 0 ) {
					foreach ( $udtlf as $udt_obj ) {
						if ( $udt_obj->getOverride() == false ) {
							Debug::text( ' Found Conflicting User Date Total Record, removing it before re-calc: ' . $udt_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$udt_obj->Delete( true );
						}
					}
				}
			} else {
				if ( $udtlf->getRecordCount() > 0 ) {
					//Delete all but the first row, in case there happens to be multiple rows with the same punch_control_id?
					$found_first_record = false;
					foreach ( $udtlf as $udt_obj ) {
						//Only keep the first record for the current date stamp. Delete all other records, or records on other dates.
						//This is required due to getting records from OldDateStamp, as commented on above.
						if ( $found_first_record == false
								&& TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $this->getDateStamp() ) ) {
							$udtf = $udt_obj;
							$found_first_record = true;
							continue;
						}

						if ( $udt_obj->getOverride() == false ) {
							Debug::text( ' Found Conflicting User Date Total Records, removing it before re-calc: ID: ' . $udt_obj->getID() . ' Date: ' . TTDate::getDate( 'DATE', $udt_obj->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
							$udt_obj->Delete( true );
						} else {
							Debug::text( ' Found overridden User Date Total Records, not removing...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}

				if ( !isset( $udtf ) ) {
					Debug::text( ' No Conflicting User Date Total Records, inserting the first one.', __FILE__, __LINE__, __METHOD__, 10 );
					$udtf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */
				} else {
					Debug::text( ' Updating UserDateTotal row ID: \'' . TTUUID::castUUID( $udtf->getId() ) . '\'', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$udtf->setUser( $this->getUser() );
				$udtf->setDateStamp( $this->getDateStamp() );
				$udtf->setPunchControlID( $this->getId() );
				$udtf->setObjectType( 10 ); //Worked

				$udtf->setBranch( $this->getBranch() );
				$udtf->setDepartment( $this->getDepartment() );

				$udtf->setJob( $this->getJob() );
				$udtf->setJobItem( $this->getJobItem() );
				$udtf->setQuantity( $this->getQuantity() );
				$udtf->setBadQuantity( $this->getBadQuantity() );

				$udtf->setTotalTime( $this->getTotalTime() );
				$udtf->setActualTotalTime( $this->getActualTotalTime() );

				//We always need to make sure both Start/End timestamps are set, we can't necessarily get this
				//from just getPunchObject(), we have to get it from calcTotalTime() instead.
				if ( is_object( $this->in_punch_obj ) ) {
					$udtf->setStartType( $this->in_punch_obj->getType() );
					$udtf->setStartTimeStamp( $this->in_punch_obj->getTimeStamp() );
				} else {
					Debug::text( 'No IN PunchObject!', __FILE__, __LINE__, __METHOD__, 10 );
					if ( is_object( $this->getPunchObject() ) && $this->getPunchObject()->getStatus() == 10 ) {
						Debug::text( '  Using passed PunchObject instead... Deleted: ' . $this->getPunchObject()->getDeleted(), __FILE__, __LINE__, __METHOD__, 10 );
						//Make sure when deleting a punch we clear out the timestamp from the UDT record.
						if ( $this->getPunchObject()->getDeleted() == true ) {
							$udtf->setStartType( null );
							$udtf->setStartTimeStamp( null );
						} else {
							$udtf->setStartType( $this->getPunchObject()->getType() );
							$udtf->setStartTimeStamp( $this->getPunchObject()->getTimeStamp() );
						}
					} else {
						Debug::text( '  ERROR: No PunchObject!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
				if ( is_object( $this->out_punch_obj ) ) {
					$udtf->setEndType( $this->out_punch_obj->getType() );
					$udtf->setEndTimeStamp( $this->out_punch_obj->getTimeStamp() );
				} else {
					Debug::text( 'No OUT PunchObject!', __FILE__, __LINE__, __METHOD__, 10 );
					if ( is_object( $this->getPunchObject() ) && $this->getPunchObject()->getStatus() == 20 ) {
						Debug::text( '  Using passed PunchObject instead... Deleted: ' . $this->getPunchObject()->getDeleted(), __FILE__, __LINE__, __METHOD__, 10 );
						//Make sure when deleting a punch we clear out the timestamp from the UDT record.
						if ( $this->getPunchObject()->getDeleted() == true ) {
							$udtf->setEndType( null );
							$udtf->setEndTimeStamp( null );
						} else {
							$udtf->setEndType( $this->getPunchObject()->getType() );
							$udtf->setEndTimeStamp( $this->getPunchObject()->getTimeStamp() );
						}
					} else {
						Debug::text( '  ERROR: No PunchObject!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}

				//Let smartReCalculate handle calculating totals/exceptions.
				if ( $udtf->isValid() ) {
					return $udtf->Save();
				} else {
					Debug::text( 'ERROR: Validation error saving UDT row!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return false;
	}

	/**
	 * This function handles when th UI wants to drag and drop punches around the time sheet.
	 * @param string $company_id   UUID
	 * @param string $src_punch_id UUID
	 * @param string $dst_punch_id UUID
	 * @param int $dst_status_id   ID 10 (In), 20 (Out), this is the status of the row the punch is being dragged too, or the resulting status_id in *most* (not all) cases. It is really only needed when using the overwrite position setting, and dragging a punch to a blank cell. Other than that it can be left NULL.
	 * @param int $position        -1 (Before), 0 (Overwrite), 1 (After)
	 * @param int $action          0 (Copy), 1 (Move)
	 * @param int $dst_date        EPOCH
	 * @return bool
	 */
	function dragNdropPunch( $company_id, $src_punch_id, $dst_punch_id, $dst_status_id = null, $position = 0, $action = 0, $dst_date = null ) {
		/*
			FIXME: This needs to handle batches to be able to handle all the differnet corner cases.
			Operations to handle:
				- Moving punch from Out to In, or In to Out in same punch pair, this is ALWAYS a move, and not a copy.
				- Move punch from one pair to another in the same day, this can be a copy or move.
					- Check moving AND copying Out punch from one punch pair to In in another on the same day. ie: In 8:00AM, Out 1:00PM, Out 5:00PM. Move the 1PM punch to pair with 5PM.
				- Move punch from one day to another, inserting inbetween other punches if necessary.
				- Move punch from one day to another without any other punches.


				- Inserting BEFORE on a dst_punch_id that is an In punch doesn't do any splitting.
				- Inserting AFTER on a dst_punch_id that is on a Out punch doesn't do any splitting.
				- Overwriting should just take the punch time and overwrite the existing punch time.
				- The first thing this function does it check if there are two punches assigned to the punch control of the destination punch, if there is, it splits the punches
					across two punch_controls, it then attaches the src_punch_id to the same punch_control_id as the dst_punch_id.
				- If no dst_punch_id is specified, assume copying to a blank cell, just copy the punch to that date along with the punch_control?
				- Copying punches that span midnight work, however moving punches does not always
					since we don't move punches in batches, we do it one at a time, and when the first punch punch
					gets moved, it can cause other punches to follow it automatically.
		*/
		$dst_date = TTDate::getMiddleDayEpoch( $dst_date );
		Debug::text( 'Src Punch ID: ' . $src_punch_id . ' Dst Punch ID: ' . $dst_punch_id . ' Dst Status ID: ' . $dst_status_id . ' Position: ' . $position . ' Action: ' . $action . ' Dst Date: ' . $dst_date, __FILE__, __LINE__, __METHOD__, 10 );

		$retval = false;

		$transaction_function = function () use ( $company_id, $src_punch_id, $dst_punch_id, $dst_status_id, $position, $action, $dst_date, $retval ) {
			//Get source and destination punch objects.
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$plf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
			$plf->StartTransaction();

			$plf->getByCompanyIDAndId( $company_id, $src_punch_id );
			if ( $plf->getRecordCount() == 1 ) {
				$src_punch_obj = $plf->getCurrent();

				$src_time_stamp = $src_punch_obj->getTimeStamp(); //Save this so we can add it to the audit log.
				$src_status_name = ( $src_punch_obj->getStatus() == 10 ? TTi18n::getText( 'In' ) : TTi18n::getText( 'Out' ) );

				//Get the PunchControlObject as early as possible, before the punch is deleted, as it will be cleared even if Save(FALSE) is called below.
				$src_punch_control_obj = clone $src_punch_obj->getPunchControlObject();

				if ( is_object( $src_punch_control_obj ) ) {
					$src_punch_date = TTDate::getMiddleDayEpoch( $src_punch_control_obj->getDateStamp() );
					Debug::text( 'Found SRC punch ID: ' . $src_punch_id . ' Source Punch Date: ' . $src_punch_date, __FILE__, __LINE__, __METHOD__, 10 );


					if ( TTDate::getMiddleDayEpoch( $src_punch_date ) != TTDate::getMiddleDayEpoch( $src_punch_obj->getTimeStamp() ) ) {
						Debug::text( 'Punch spans midnight... Source Punch Date: ' . TTDate::getDATE( 'DATE+TIME', $src_punch_date ) . ' Source Punch TimeStamp: ' . TTDate::getDATE( 'DATE+TIME', $src_punch_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
						$dst_date_modifier = 86400; //Bump day by 24hrs.
					} else {
						$dst_date_modifier = 0;
					}

					//If we are moving the punch, we need to delete the source punch first so it doesn't conflict with the new punch.
					//Especially if we are just moving a punch to fill a gap in the same day.
					//If the punch being moved is in the same day, or within the same punch pair, we don't want to delete the source punch, instead we just modify
					//the necessary bits later on. So we need to short circuit the move functionality when copying/moving punches within the same day.
					if (
							( $action == 1 && $src_punch_id != $dst_punch_id && $src_punch_date != $dst_date )
							||
							( $action == 1 && $src_punch_id != $dst_punch_id && $src_punch_date == $dst_date )
						//OR
						//( $action == 0 AND $src_punch_id != $dst_punch_id AND $src_punch_date == $dst_date ) //Since we have dst_status_id, we don't need to force-move punches even though the user selected copy.
					) { //Move
						Debug::text( 'Deleting original punch ID: ' . $src_punch_id . ' User Date: ' . TTDate::getDate( 'DATE', $src_punch_control_obj->getDateStamp() ) . ' ID: ' . $src_punch_control_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

						$src_punch_obj->setUser( $src_punch_control_obj->getUser() );
						$src_punch_obj->setDeleted( true );

						$punch_image_data = $src_punch_obj->getImage();

						//These aren't doing anything because they aren't acting on the PunchControl object?
						$src_punch_obj->setEnableCalcTotalTime( true );
						$src_punch_obj->setEnableCalcSystemTotalTime( true );
						$src_punch_obj->setEnableCalcWeeklySystemTotalTime( true );
						$src_punch_obj->setEnableCalcUserDateTotal( true );
						$src_punch_obj->setEnableCalcException( true );
						$src_punch_obj->Save( false ); //Keep object around for later.
					} else {
						Debug::text( 'NOT Deleting original punch, either in copy mode or condition is not met...', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( $src_punch_id == $dst_punch_id || $dst_punch_id == '' ) {
						//Assume we are just moving a punch within the same punch pair, unless a new date is specfied.
						//However if we're simply splitting an existing punch pair, like dragging the Out punch from an In/Out pair into its own separate pair.
						if ( $src_punch_date != $dst_date || $src_punch_date == $dst_date && $dst_punch_id == '' ) {
							Debug::text( 'aCopying punch to new day...', __FILE__, __LINE__, __METHOD__, 10 );

							//Moving punch to a new date.
							//Copy source punch to proper location by destination punch.
							$src_punch_obj->setId( false );
							$src_punch_obj->setPunchControlId( $src_punch_control_obj->getNextInsertId() );
							$src_punch_obj->setDeleted( false ); //Just in case it was marked deleted by the MOVE action.

							$new_time_stamp = TTDate::getTimeLockedDate( $src_punch_obj->getTimeStamp(), ( $dst_date + $dst_date_modifier ) );
							Debug::text( 'SRC TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $src_punch_obj->getTimeStamp() ) . ' DST TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $new_time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );

							$src_punch_obj->setTimeStamp( $new_time_stamp, false );
							$src_punch_obj->setActualTimeStamp( $new_time_stamp );
							$src_punch_obj->setOriginalTimeStamp( $new_time_stamp );
							if ( $dst_status_id != '' ) {
								$src_punch_obj->setStatus( $dst_status_id ); //Change the status to fit in the proper place.
							}

							//When drag&drop copying punches, clear some fields that shouldn't be copied.
							if ( $action == 0 ) { //Copy
								$src_punch_obj->setStation( null );
								$src_punch_obj->setHasImage( false );
								$src_punch_obj->setLongitude( null ); //Make sure we clear out long/lat as the location shouldn't carry across with copies.
								$src_punch_obj->setLatitude( null );  //Make sure we clear out long/lat as the location shouldn't carry across with copies.

								//When copying, make sure we clear the original created information to avoid confusion in the audit log.
								$src_punch_obj->setCreatedBy( null );
								$src_punch_obj->setCreatedDate( null );
							} else if ( isset( $punch_image_data ) && $punch_image_data != '' ) {
								$src_punch_obj->setImage( $punch_image_data );
							}

							//When moving or copying, always touch the updated information.
							$src_punch_obj->setUpdatedBy( null );
							$src_punch_obj->setUpdatedDate( null );

							if ( $src_punch_obj->isValid() == true ) {
								$insert_id = $src_punch_obj->Save( false );

								TTLog::addEntry( $src_punch_obj->getID(), 500, TTi18n::getText( 'Drag & Drop' ) . ': ' . TTi18n::getText( 'Action' ) . ': ' . ( $action == 0 ? TTi18n::getText( 'Copy' ) : TTi18n::getText( 'Move' ) ) . ' ' . TTi18n::getText( 'From' ) . ': ' . TTDate::getDate( 'DATE+TIME', $src_time_stamp ) . ' ' . TTi18n::getText( 'Status' ) . ': ' . $src_status_name, null, $src_punch_obj->getTable() );

								$src_punch_control_obj->shift_data = null;    //Need to clear the shift data so its obtained from the DB again, otherwise shifts will appear on strange days.
								$src_punch_control_obj->user_date_obj = null; //Need to clear user_date_obj from cache so a new one is obtained.
								$src_punch_control_obj->setId( $src_punch_obj->getPunchControlID() );
								$src_punch_control_obj->setPunchObject( $src_punch_obj );

								if ( $src_punch_control_obj->isValid() == true ) {
									Debug::Text( ' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__, 10 );

									//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
									//in the case of a drag & drop or something.
									$src_punch_control_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
									$src_punch_control_obj->setEnableCalcUserDateID( true );
									$src_punch_control_obj->setEnableCalcTotalTime( true );
									$src_punch_control_obj->setEnableCalcSystemTotalTime( true );
									$src_punch_control_obj->setEnableCalcWeeklySystemTotalTime( true );
									$src_punch_control_obj->setEnableCalcUserDateTotal( true );
									$src_punch_control_obj->setEnableCalcException( true );
									if ( $src_punch_control_obj->isValid() == true ) {
										if ( $src_punch_control_obj->Save( true, true ) == true ) {
											//Return newly inserted punch_id, so Flex can base other actions on it.
											$retval = $insert_id;
										}
									}
								}
							}
						} else {
							Debug::text( 'Copying punch within the same pair/day...', __FILE__, __LINE__, __METHOD__, 10 );
							//Moving punch within the same punch pair.
							$src_punch_obj->setStatus( $src_punch_obj->getNextStatus() ); //Change just the punch status.
							//$src_punch_obj->setDeleted(FALSE); //Just in case it was marked deleted by the MOVE action.
							if ( $src_punch_obj->isValid() == true ) {
								//Return punch_id, so Flex can base other actions on it.
								$retval = $src_punch_obj->Save( false );

								TTLog::addEntry( $src_punch_obj->getId(), 500, TTi18n::getText( 'Drag & Drop' ) . ': ' . TTi18n::getText( 'Action' ) . ': ' . ( $action == 0 ? TTi18n::getText( 'Copy' ) : TTi18n::getText( 'Move' ) ) . ' ' . TTi18n::getText( 'From' ) . ': ' . TTDate::getDate( 'DATE+TIME', $src_time_stamp ) . ' ' . TTi18n::getText( 'Status' ) . ': ' . $src_status_name, null, $src_punch_obj->getTable() );

								$src_punch_control_obj->shift_data = null;    //Need to clear the shift data so its obtained from the DB again, otherwise shifts will appear on strange days.
								$src_punch_control_obj->user_date_obj = null; //Need to clear user_date_obj from cache so a new one is obtained.
								$src_punch_control_obj->setId( $src_punch_obj->getPunchControlID() );
								$src_punch_control_obj->setPunchObject( $src_punch_obj );

								if ( $src_punch_control_obj->isValid() == true ) {
									Debug::Text( ' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__, 10 );
									//Need to make sure we calculate the exceptions if they are moving punches from in/out, as there is likely to be a missing punch exception either way.
									$src_punch_control_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
									$src_punch_control_obj->setEnableCalcUserDateID( false );
									$src_punch_control_obj->setEnableCalcTotalTime( false );
									$src_punch_control_obj->setEnableCalcSystemTotalTime( true );
									$src_punch_control_obj->setEnableCalcWeeklySystemTotalTime( false );
									$src_punch_control_obj->setEnableCalcUserDateTotal( false );
									$src_punch_control_obj->setEnableCalcException( true );
									if ( $src_punch_control_obj->isValid() == true ) {
										$src_punch_control_obj->Save( true, true );
									}
								}
							}
						}
					} else {
						Debug::text( 'bCopying punch to new day...', __FILE__, __LINE__, __METHOD__, 10 );
						$plf->getByCompanyIDAndId( $company_id, $dst_punch_id );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( 'Found DST punch ID: ' . $dst_punch_id, __FILE__, __LINE__, __METHOD__, 10 );
							$dst_punch_obj = $plf->getCurrent();
							$dst_punch_control_obj = $dst_punch_obj->getPunchControlObject();
							Debug::text( 'aSRC TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $src_punch_obj->getTimeStamp() ) . ' DST TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $dst_punch_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

							$is_punch_control_split = false;
							if ( $position == 0 ) { //Overwrite
								Debug::text( 'Overwriting...', __FILE__, __LINE__, __METHOD__, 10 );
								//All we need to do is update the time of the destination punch.
								$punch_obj = $dst_punch_obj;
							} else { //Before or After
								//Determine if the destination punch needs to split from another punch
								//Check to make sure that when splitting an existing punch pair, the new punch is after the IN punch and before the OUT punch.
								//Otherwise don't split the punch pair and just put it in its own pair.
								if ( ( $position == -1 && $dst_punch_obj->getStatus() == 20 && ( $dst_status_id == false || $src_punch_obj->getTimeStamp() < $dst_punch_obj->getTimeStamp() ) )
										|| ( $position == 1 && $dst_punch_obj->getStatus() == 10 && ( $dst_status_id == false || $src_punch_obj->getTimeStamp() > $dst_punch_obj->getTimeStamp() ) ) ) { //Before on Out punch, After on In Punch,
									Debug::text( 'Need to split destination punch out to its own Punch Control row...', __FILE__, __LINE__, __METHOD__, 10 );
									$is_punch_control_split = PunchControlFactory::splitPunchControl( $dst_punch_obj->getPunchControlID() );

									//Once a split occurs, we need to re-get the destination punch as the punch_control_id may have changed.
									//We could probably optimize this to only occur when the destination punch is an In punch, as the
									//Out punch is always the one to be moved to a new punch_control_id
									if ( $src_punch_obj->getStatus() != $dst_punch_obj->getStatus() ) {
										$plf->getByCompanyIDAndId( $company_id, $dst_punch_id );
										if ( $plf->getRecordCount() == 1 ) {
											$dst_punch_obj = $plf->getCurrent();
											Debug::text( 'Found DST punch ID: ' . $dst_punch_id . ' Punch Control ID: ' . $dst_punch_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10 );
										}
									}

									$punch_control_id = $dst_punch_obj->getPunchControlID();
								} else {
									Debug::text( 'No Need to split destination punch, simply add a new punch/punch_control all on its own.', __FILE__, __LINE__, __METHOD__, 10 );
									//Check to see if the src and dst punches are the same status though.
									$punch_control_id = $dst_punch_control_obj->getNextInsertId();
								}

								//Take the source punch and base our new punch on that.
								$punch_obj = $src_punch_obj;

								//Copy source punch to proper location by destination punch.
								$punch_obj->setId( false );
								$punch_obj->setDeleted( false ); //Just in case it was marked deleted by the MOVE action.
								$punch_obj->setPunchControlId( $punch_control_id );
							}

							//$new_time_stamp = TTDate::getTimeLockedDate($src_punch_obj->getTimeStamp(), $dst_punch_obj->getTimeStamp()+$dst_date_modifier );
							$new_time_stamp = TTDate::getTimeLockedDate( $src_punch_obj->getTimeStamp(), ( $dst_punch_obj->getPunchControlObject()->getDateStamp() + $dst_date_modifier ) );
							Debug::text( 'SRC TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $src_punch_obj->getTimeStamp() ) . ' DST TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $dst_punch_obj->getTimeStamp() ) . ' New TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $new_time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );

							$punch_obj->setTimeStamp( $new_time_stamp, false );
							$punch_obj->setActualTimeStamp( $new_time_stamp );
							$punch_obj->setOriginalTimeStamp( $new_time_stamp );
							$punch_obj->setTransfer( false ); //Always set transfer to FALSE so we don't try to create In/Out punch automatically later.

							//When drag&drop copying punches, clear some fields that shouldn't be copied.
							if ( $action == 0 ) { //Copy
								$punch_obj->setStation( null );
								$punch_obj->setHasImage( false );
								$punch_obj->setLongitude( null ); //Make sure we clear out long/lat as the location shouldn't carry across with copies.
								$punch_obj->setLatitude( null );  //Make sure we clear out long/lat as the location shouldn't carry across with copies.

								//When copying, make sure we clear the original created information to avoid confusion in the audit log.
								$punch_obj->setCreatedBy( null );
								$punch_obj->setCreatedDate( null );
							} else if ( isset( $punch_image_data ) && $punch_image_data != '' ) {
								$punch_obj->setImage( $punch_image_data );
							}

							//When moving or copying, always touch the updated information.
							$punch_obj->setUpdatedBy( null );
							$punch_obj->setUpdatedDate( null );

							//Need to take into account copying a Out punch and inserting it BEFORE another Out punch in a punch pair.
							//In this case a split needs to occur, and the status needs to stay the same.
							//Status also needs to stay the same when overwriting an existing punch.
							Debug::text( 'Punch Status: ' . $punch_obj->getStatus() . ' DST Punch Status: ' . $dst_punch_obj->getStatus() . ' Split Punch Control: ' . (int)$is_punch_control_split, __FILE__, __LINE__, __METHOD__, 10 );
							if ( ( $position != 0 && $is_punch_control_split == false && $punch_obj->getStatus() == $dst_punch_obj->getStatus() && $punch_obj->getPunchControlID() == $dst_punch_obj->getPunchControlID() ) ) {
								Debug::text( 'Changing punch status to opposite: ' . $dst_punch_obj->getNextStatus(), __FILE__, __LINE__, __METHOD__, 10 );
								$punch_obj->setStatus( $dst_punch_obj->getNextStatus() ); //Change the status to fit in the proper place.
							}

							if ( $punch_obj->isValid() == true ) {
								$insert_id = $punch_obj->Save( false );

								TTLog::addEntry( $punch_obj->getId(), 500, TTi18n::getText( 'Drag & Drop' ) . ': ' . TTi18n::getText( 'Action' ) . ': ' . ( $action == 0 ? TTi18n::getText( 'Copy' ) : TTi18n::getText( 'Move' ) ) . ' ' . TTi18n::getText( 'From' ) . ': ' . TTDate::getDate( 'DATE+TIME', $src_time_stamp ) . ' ' . TTi18n::getText( 'Status' ) . ': ' . $src_status_name, null, $punch_obj->getTable() );

								$dst_punch_control_obj->shift_data = null; //Need to clear the shift data so its obtained from the DB again, otherwise shifts will appear on strange days, or cause strange conflicts.
								$dst_punch_control_obj->setID( $punch_obj->getPunchControlID() );
								$dst_punch_control_obj->setPunchObject( $punch_obj );

								if ( $dst_punch_control_obj->isValid() == true ) {
									Debug::Text( ' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__, 10 );

									//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
									//in the case of a drag & drop or something.
									$dst_punch_control_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
									$dst_punch_control_obj->setEnableCalcUserDateID( true );
									$dst_punch_control_obj->setEnableCalcTotalTime( true );
									$dst_punch_control_obj->setEnableCalcSystemTotalTime( true );
									$dst_punch_control_obj->setEnableCalcWeeklySystemTotalTime( true );
									$dst_punch_control_obj->setEnableCalcUserDateTotal( true );
									$dst_punch_control_obj->setEnableCalcException( true );
									if ( $dst_punch_control_obj->isValid() == true ) {
										if ( $dst_punch_control_obj->Save( true, true ) == true ) { //Force isNew() lookup.
											//Return newly inserted punch_id, so Flex can base other actions on it.
											$retval = $insert_id;
											//$retval = TRUE;
										}
									}
								}
							}
						}
					}
				} else {
					Debug::text( 'Punch Control object does not exist, unable to continue... Punch Control ID: ' . $src_punch_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			if ( $retval == false ) {
				$plf->FailTransaction();
			}
			//$plf->FailTransaction();
			$plf->CommitTransaction();
			$plf->setTransactionMode(); //Back to default isolation level.

			return [ $retval ];
		};

		list( $retval ) = $this->RetryTransaction( $transaction_function );

		Debug::text( 'Returning: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * When passed a punch_control_id, if it has two punches assigned to it, a new punch_control_id row is created and the punches are split between the two.
	 * @param string $punch_control_id UUID
	 * @return bool
	 */
	static function splitPunchControl( $punch_control_id ) {
		$retval = false;
		if ( $punch_control_id != '' ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$plf->StartTransaction();
			$plf->getByPunchControlID( $punch_control_id, null, [ 'time_stamp' => 'desc' ] ); //Move out punch to new punch_control_id.
			if ( $plf->getRecordCount() == 2 ) {
				$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
				$new_punch_control_id = $pclf->getNextInsertId();
				Debug::text( ' Punch Control ID: ' . $punch_control_id . ' only has two punches assigned, splitting... New Punch Control ID: ' . $new_punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
				$i = 0;
				foreach ( $plf as $p_obj ) {
					if ( $i == 0 ) {
						//First punch (out)
						//Get the PunchControl Object before we change to the new punch_control_id
						$pc_obj = $p_obj->getPunchControlObject();

						$p_obj->setPunchControlId( $new_punch_control_id );
						if ( $p_obj->isValid() == true ) {
							$p_obj->Save( false );

							$pc_obj->setId( $new_punch_control_id );
							$pc_obj->setPunchObject( $p_obj );

							if ( $pc_obj->isValid() == true ) {
								Debug::Text( ' Punch Control is valid, saving Punch ID: ' . $p_obj->getID() . ' To new Punch Control ID: ' . $new_punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );

								//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
								//in the case of a drag & drop or something.
								$pc_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
								$pc_obj->setEnableCalcUserDateID( true );
								$pc_obj->setEnableCalcTotalTime( true );
								$pc_obj->setEnableCalcSystemTotalTime( false );       //Do this for In punch only.
								$pc_obj->setEnableCalcWeeklySystemTotalTime( false ); //Do this for In punch only.
								$pc_obj->setEnableCalcUserDateTotal( true );
								$pc_obj->setEnableCalcException( true );
								if ( $pc_obj->isValid() ) {
									$retval = $pc_obj->Save( true, true ); //Force isNew() lookup.
								}
							}
						}
					} else {
						//Second punch (in), need to recalculate user_date_total for this one to clear the total time, as well as recalculate the entire week
						//for system totals so those are updated as well.
						Debug::text( ' ReCalculating total time for In punch...', __FILE__, __LINE__, __METHOD__, 10 );
						$pc_obj = $p_obj->getPunchControlObject();
						$pc_obj->setEnableStrictJobValidation( false ); //Make sure we relax as many validation criteria as possible when making this change since its often called from PunchControlFactory->postSave() and we can't show the errors to the user.
						$pc_obj->setEnableCalcUserDateID( true );
						$pc_obj->setEnableCalcTotalTime( true );
						$pc_obj->setEnableCalcSystemTotalTime( true );
						$pc_obj->setEnableCalcWeeklySystemTotalTime( true );
						$pc_obj->setEnableCalcUserDateTotal( true );
						$pc_obj->setEnableCalcException( true );
						if ( $pc_obj->isValid() ) {
							$retval = $pc_obj->Save();
						}
					}

					$i++;
				}
			} else {
				Debug::text( ' Punch Control ID: ' . $punch_control_id . ' only has one punch assigned, doing nothing...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			//$plf->FailTransaction();
			$plf->CommitTransaction();
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		$this->calcUserDate();
		$this->calcUserDateTotal();

		if ( $this->getEnableCalcSystemTotalTime() == true && is_object( $this->getUserObject() ) ) {
			//old_date_stamps can contain other dates from calcUserDate() as well.
			$this->old_date_stamps[] = $this->getDateStamp(); //Make sure the current date is calculated
			if ( $this->getOldDateStamp() != '' ) {
				$this->old_date_stamps[] = $this->getOldDateStamp(); //Make sure the old date is calculated
			}
			UserDateTotalFactory::reCalculateDay( $this->getUserObject(), $this->old_date_stamps, $this->getEnableCalcException(), $this->getEnablePreMatureException() );
		}

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
						//Ignore any user_date_id, as we will figure it out on our own based on the time_stamp and pay period settings ($pcf->setEnableCalcUserDateID(TRUE))
						//This breaks smartRecalculate() as it doesn't know the previous user_date_id to calculate.	So when shifts are reassigned to new days
						//the old days are not recalculated properly.
						//case 'user_date_id':
						//	break;
						case 'date_stamp': //HTML5 interface sends punch_date rather than date_stamp when saving a new punch.
							break;
						case 'punch_date':
							$this->setDateStamp( $data[$key] );
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

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'total_time':    //Ignore total time, as its calculated later anyways, so if its set here it will cause a validation error.
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Punch Control - Employee' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ), null, $this->getTable(), $this );
	}
}

?>
