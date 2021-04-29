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
class UserDateTotalFactory extends Factory {
	protected $table = 'user_date_total';
	protected $pk_sequence_name = 'user_date_total_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $pay_period_obj = null;
	protected $punch_control_obj = null;
	protected $job_obj = null;
	protected $job_item_obj = null;
	protected $pay_code_obj = null;

	public $alternate_date_stamps = null; //Stores alternate date stamps that also need to be recalculated.

	protected $calc_system_total_time = false;
	protected $timesheet_verification_check = false;
	static $calc_future_week = false; //Used for BiWeekly overtime policies to schedule future week recalculating.

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {
		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = null;
		switch ( $name ) {
			case 'start_type':
			case 'end_type':
				$retval = [
						10 => TTi18n::gettext( 'Normal' ),
						20 => TTi18n::gettext( 'Lunch' ),
						30 => TTi18n::gettext( 'Break' ),
				];
				break;
			case 'object_type':
				//In order to not have to dig into punches when calculating policies, we would need to create user_date_total rows for lunch/break
				//time taken.

				//We have to continue to use two columns to determine the type of hours and the pay code its associated with.
				//Otherwise we have no idea what is Lunch Time vs Total Time vs Break Time, since they could all go to one pay code.
				$retval = [
						5  => TTi18n::gettext( 'System' ),
						10 => TTi18n::gettext( 'Worked' ), //Used to be "Total"
						20 => TTi18n::gettext( 'Regular' ),
						25 => TTi18n::gettext( 'Absence' ),
						30 => TTi18n::gettext( 'Overtime' ),
						40 => TTi18n::gettext( 'Premium' ),

						//We need to treat Absence time like Worked Time, and calculate policies (ie: Overtime) based on it, without affecting the original entry.
						//As it can be split between regular,overtime policies just like worked time can.
						50 => TTi18n::gettext( 'Absence (Taken)' ),

						100 => TTi18n::gettext( 'Lunch' ), //Lunch Policy (auto-add/deduct)
						101 => TTi18n::gettext( 'Lunch (Taken)' ), //Time punched out for lunch.

						110 => TTi18n::gettext( 'Break' ), //Break Policy (auto-add/deduct)
						111 => TTi18n::gettext( 'Break (Taken)' ), //Time punched out for break.
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						'-1005-user_status'        => TTi18n::gettext( 'Employee Status' ),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1039-group'              => TTi18n::gettext( 'Group' ),
						'-1040-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1050-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1160-branch'             => TTi18n::gettext( 'Branch' ),
						'-1170-department'         => TTi18n::gettext( 'Department' ),

						'-1200-object_type' => TTi18n::gettext( 'Type' ),
						'-1205-name'        => TTi18n::gettext( 'Pay Code' ),
						'-1210-date_stamp'  => TTi18n::gettext( 'Date' ),
						'-1290-total_time'  => TTi18n::gettext( 'Time' ),

						'-1300-quantity'     => TTi18n::gettext( 'QTY' ),
						'-1300-bad_quantity' => TTi18n::gettext( 'Bad QTY' ),

						'-1800-note' => TTi18n::gettext( 'Note' ),

						'-1900-override' => TTi18n::gettext( 'O/R' ), //Override

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				if ( $product_edition_id >= 20 ) {
					$retval['-1180-job'] = TTi18n::gettext( 'Job' );
					$retval['-1190-job_item'] = TTi18n::gettext( 'Task' );
				}
				ksort( $retval );
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				if ( $product_edition_id >= 20 ) {
					$retval = [
							'date_stamp',
							'total_time',
							'object_type',
							'name',
							'branch',
							'department',
							'job',
							'job_item',
							'note',
							'override',
					];
				} else {
					$retval = [
							'date_stamp',
							'total_time',
							'object_type',
							'name',
							'branch',
							'department',
							'note',
							'override',
					];
				}
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [];
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
				'id'            => 'ID',
				'user_id'       => 'User',
				'date_stamp'    => 'DateStamp',
				'pay_period_id' => 'PayPeriod',

				//Legacy status/type functions.
				'status_id'     => 'Status',
				'type_id'       => 'Type',

				'object_type_id' => 'ObjectType',
				'object_type'    => false,
				'pay_code_id'    => 'PayCode',
				'src_object_id'  => 'SourceObject', //This must go after PayCodeID, so if the user is saving an absence we overwrite any previously selected PayCode
				'policy_name'    => false,

				'punch_control_id'  => 'PunchControlID',
				'branch_id'         => 'Branch',
				'branch'            => false,
				'department_id'     => 'Department',
				'department'        => false,
				'job_id'            => 'Job',
				'job'               => false,
				'job_item_id'       => 'JobItem',
				'job_item'          => false,
				'quantity'          => 'Quantity',
				'bad_quantity'      => 'BadQuantity',
				'start_type_id'     => 'StartType',
				'start_time_stamp'  => 'StartTimeStamp',
				'end_type_id'       => 'EndType',
				'end_time_stamp'    => 'EndTimeStamp',
				'total_time'        => 'TotalTime',
				'actual_total_time' => 'ActualTotalTime',

				'currency_id'                   => 'Currency',
				'currency_rate'                 => 'CurrencyRate',
				'base_hourly_rate'              => 'BaseHourlyRate',
				'hourly_rate'                   => 'HourlyRate',
				'total_time_amount'             => 'TotalTimeAmount',
				'hourly_rate_with_burden'       => 'HourlyRateWithBurden',
				'total_time_amount_with_burden' => 'TotalTimeAmountWithBurden',

				'name'     => false,
				'override' => 'Override',
				'note'     => 'Note',

				'first_name'            => false,
				'last_name'             => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'group'                 => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return UserFactory|bool
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
	 * @return bool
	 */
	function getPunchControlObject() {
		return $this->getGenericObject( 'PunchControlListFactory', $this->getPunchControlID(), 'punch_control_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayCodeObject() {
		return $this->getGenericObject( 'PayCodeListFactory', $this->getPayCode(), 'pay_code_obj' );
	}

	/**
	 * @return bool
	 */
	function getJobObject() {
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			return $this->getGenericObject( 'JobListFactory', $this->getJob(), 'job_obj' );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getJobItemObject() {
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			return $this->getGenericObject( 'JobItemListFactory', $this->getJobItem(), 'job_item_obj' );
		}

		return false;
	}


	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
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
	 * @param string $value UUID
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
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				if ( !is_numeric( $value ) ) {                                         //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::getMiddleDayEpoch( TTDate::strtotime( $value ) ); //Make sure we use middle day epoch when pulling the value from the DB the first time, to match setDateStamp() below. Otherwise setting the datestamp then getting it again before save won't match the same value after its saved to the DB.
					$this->setGenericDataValue( 'date_stamp', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = (int)$value;

		if ( $value > 0 ) {
			//Use middle day epoch to help avoid confusion with different timezones/DST. -- getDateStamp() needs to use middle day epoch too then.
			//See comments about timezones in CalculatePolicy->_calculate().
			$retval = $this->setGenericDataValue( 'date_stamp', TTDate::getMiddleDayEpoch( $value ) );

			$this->setPayPeriod(); //Force pay period to be set as soon as the date is.

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchControlID() {
		return $this->getGenericDataValue( 'punch_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPunchControlID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'punch_control_id', $value );
	}

	//Legacy functions for now:

	/**
	 * @return int
	 */
	function getStatus() {
		if ( in_array( $this->getObjectType(), [ 5, 20, 25, 30, 40, 100, 110 ] ) ) {
			return 10;
		} else if ( $this->getObjectType() == 20 ) {
			return 20;
		} else if ( $this->getObjectType() == 50 ) {
			return 30;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		if ( in_array( $this->getObjectType(), [ 5, 10, 50 ] ) ) {
			return 10;
		} else {
			return $this->getObjectType();
		}
	}

	/**
	 * @return bool|int
	 */
	function getObjectType() {
		$retval = $this->getGenericDataValue( 'object_type_id' );
		if ( $retval !== false ) {
			return (int)$retval;
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		return $this->setGenericDataValue( 'object_type_id', (int)$value );
	}

	/**
	 * @param int $object_type_id
	 * @return bool|object
	 */
	function getSourceObjectListFactory( $object_type_id ) {
		//Debug::Text('Object Type: '. $object_type_id, __FILE__, __LINE__, __METHOD__, 10);
		switch ( $object_type_id ) {
			case 20:
				$lf = TTNew( 'RegularTimePolicyListFactory' ); /** @var RegularTimePolicyListFactory $lf */
				break;
			case 30:
				$lf = TTNew( 'OverTimePolicyListFactory' ); /** @var OverTimePolicyListFactory $lf */
				break;
			case 40:
				$lf = TTNew( 'PremiumPolicyListFactory' ); /** @var PremiumPolicyListFactory $lf */
				break;
			case 25:
			case 50:
				$lf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $lf */
				break;
			case 100:
			case 101:
				$lf = TTNew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $lf */
				break;
			case 110:
			case 111:
				$lf = TTNew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $lf */
				break;
			default:
				$lf = false;
				Debug::Text( 'Invalid Object Type: ' . $object_type_id, __FILE__, __LINE__, __METHOD__, 10 );
				break;
		}

		return $lf;
	}

	/**
	 * @return bool
	 */
	function getSourceObjectObject() {
		$lf = $this->getSourceObjectListFactory( $this->getObjectType() );
		if ( is_object( $lf ) ) {
			$lf->getByID( $this->getSourceObject() );
			if ( $lf->getRecordCount() == 1 ) {
				return $lf->getCurrent();
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getSourceObject() {
		return $this->getGenericDataValue( 'src_object_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setSourceObject( $value ) {
		$value = TTUUID::castUUID( $value );
		//Debug::Text('Object Type: '. $this->getObjectType() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		$retval = $this->setGenericDataValue( 'src_object_id', $value );

		//Absences need to have pay codes set for the user created entry, then other policies can also be calculated on them too.
		//This is so they can be linked directly with accrual policies rather than having to go through regular time policies first.
		//But in cases where OT is calculated on absence time it may need to not have any pay code and just go through regular/OT policies instead.
		//Do this here rather than in preSave() like it used to be since that could cause the validation checks to fail and the user wouldnt see the message.
		//However we have to setSourceObject *after* setPayCode(), otherwise there is potential for the wrong pay code to be used.
		if ( $value != TTUUID::getZeroID() ) {
			if ( $this->getObjectType() == 50 ) {
				$lf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $lf */
			} else {
				$lf = null;
			}

			if ( is_object( $lf ) ) {
				$lf->getByID( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$obj = $lf->getCurrent();
					Debug::text( 'Setting PayCode To: ' . $obj->getPayCode(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->setPayCode( $obj->getPayCode() );
				}
			}
		}

		return $retval;
	}

	/**
	 * @return bool|mixed
	 */
	function getPayCode() {
		return $this->getGenericDataValue( 'pay_code_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayCode( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_code_id', $value );
	}

	/**
	 * Returns an array of time categories that the object_type fits in.
	 * @param bool $include_total
	 * @param bool $report_columns
	 * @return array
	 */
	function getTimeCategory( $include_total = true, $report_columns = false ) {

		$retarr = [];
		switch ( $this->getObjectType() ) {
			case 5: //System Time
				if ( $include_total == true ) {
					$retarr[] = 'total';
				}
				break;
			case 10: //Worked
				$retarr[] = 'worked';
				break;
			case 20: //Regular
				$retarr[] = 'regular';
				break;
			case 25: //Absence
				$retarr[] = 'absence';
				break;
			case 30: //Overtime
				$retarr[] = 'overtime';
				break;
			case 40: //Premium
				$retarr[] = 'premium';
				break;
			case 50: //Absence (Taken)
				$retarr[] = 'absence_taken';
				break;
			case 100: //Lunch
				$retarr[] = 'worked';
				break;
			case 101: //Lunch (Taken)
				//During the transition from v7 -> v8, Lunch/Break time wasnt being assigned to branch/departments in v7, so it caused
				//blank lines to appear on reports. This prevents Lunch/Break time taken from causing blank lines or even being included
				//unless the report displays these columns.
				if ( $report_columns == false || isset( $report_columns['lunch_time'] ) ) {
					$retarr[] = 'lunch';
				}
				break;
			case 110: //Break
				$retarr[] = 'worked';
				break;
			case 111: //Break (Taken)
				//During the transition from v7 -> v8, Lunch/Break time wasnt being assigned to branch/departments in v7, so it caused
				//blank lines to appear on reports. This prevents Lunch/Break time taken from causing blank lines or even being included
				//unless the report displays these columns.
				if ( $report_columns == false || isset( $report_columns['break_time'] ) ) {
					$retarr[] = 'break';
				}
				break;
		}

		//Don't include Absence Time Taken (ID: 50) with other 'pay_code-' categories, as that will double up on the absence time often. (ID:25 + ID:50).
		//Include Lunch(100)/Break(110) so they can be displayed as their own separate column on reports.
		if ( in_array( $this->getObjectType(), [ 20, 25, 30, 40, 100, 110 ] ) ) {
			$retarr[] = 'pay_code:' . $this->getColumn( 'pay_code_id' );
		} else if ( $this->getObjectType() == 50 ) { //Break out absence time taken so we can have separate columns for it in reports. Prevents doubling up as described above.
			$retarr[] = 'absence_taken_pay_code:' . $this->getColumn( 'pay_code_id' );
		}

		//Make sure we don't include Absence (Taken) [50] in gross time, use Absence [25] instead so we don't double up on absence time.
		if ( $this->getObjectType() != 50 && $this->getColumn( 'pay_code_type_id' ) != '' && in_array( $this->getColumn( 'pay_code_type_id' ), [ 10, 12, 30 ] ) ) {
			$retarr[] = 'gross'; //Use 'gross' instead of 'paid' so we don't have to special case it in each report.
		}

		return $retarr;
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
		return (float)$this->getGenericDataValue( 'quantity' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setQuantity( $value ) {
		$value = TTi18n::parseFloat( $value );
		if ( $value == false || $value == 0 || $value == '' ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'quantity', $value );
	}

	/**
	 * @return bool|float
	 */
	function getBadQuantity() {
		return (float)$this->getGenericDataValue( 'bad_quantity' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBadQuantity( $value ) {
		$value = TTi18n::parseFloat( $value );
		if ( $value == false || $value == 0 || $value == '' ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'bad_quantity', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStartType() {
		return (int)$this->getGenericDataValue( 'start_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartType( $value ) {
		$value = (int)$value;
		if ( $value === 0 ) {
			$value = '';
		}

		return $this->setGenericDataValue( 'start_type_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return int|bool
	 */
	function getStartTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				if ( !is_numeric( $value ) ) { //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::strtotime( $value );
					$this->setGenericDataValue( 'start_time_stamp', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartTimeStamp( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_time_stamp', $value );
	}

	/**
	 * @return bool|int
	 */
	function getEndType() {
		return (int)$this->getGenericDataValue( 'end_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndType( $value ) {
		$value = (int)$value;
		if ( $value === 0 ) {
			$value = '';
		}

		return $this->setGenericDataValue( 'end_type_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool
	 */
	function getEndTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				if ( !is_numeric( $value ) ) { //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::strtotime( $value );
					$this->setGenericDataValue( 'end_time_stamp', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndTimeStamp( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_time_stamp', $value );
	}

	/**
	 * @return bool|int
	 */
	function getTotalTime() {
		return (int)$this->getGenericDataValue( 'total_time' );
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
	 * @return bool
	 */
	function calcTotalTime() {
		if ( $this->getEndTimeStamp() != '' && $this->getStartTimeStamp() != '' ) {
			$retval = ( $this->getEndTimeStamp() - $this->getStartTimeStamp() );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getActualTotalTime() {
		return (int)$this->getGenericDataValue( 'actual_total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setActualTotalTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'actual_total_time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $id UUID
	 * @param bool $disable_rate_lookup
	 * @return bool
	 */
	function setCurrency( $id, $disable_rate_lookup = false ) {
		$id = trim( $id );

		//Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
		$old_currency_id = $this->getCurrency();
		if ( is_object( $culf->getByID( $id ) ) ) {
			$rs = $culf->getByID( $id );
			if ( isset( $rs->rs ) && is_object( $rs->rs ) && isset( $rs->rs->_numOfRows ) && $rs->rs->_numOfRows > 0 ) {
				$this->setGenericDataValue( 'currency_id', $id );

				if ( $disable_rate_lookup == false
						&& $culf->getRecordCount() == 1
						&& TTDate::isValidDate( $this->getDateStamp() ) == true
						&& ( $this->isNew() || $old_currency_id != $id ) ) {
					$crlf = TTnew( 'CurrencyRateListFactory' ); /** @var CurrencyRateListFactory $crlf */
					$crlf->getByCurrencyIdAndDateStamp( $id, $this->getDateStamp() );
					if ( $crlf->getRecordCount() > 0 ) {
						$this->setCurrencyRate( $crlf->getCurrent()->getReverseConversionRate() );
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrencyRate() {
		return $this->getGenericDataValue( 'currency_rate' );
	}

	/**
	 * Currency exchange rate to convert the amount back to the base currency. Rate=1 would usually only happen if the current currency is the base currency.
	 * @param $value
	 * @return bool
	 */
	function setCurrencyRate( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );
		if ( $value == 0 ) {
			$value = 1;
		}

		return $this->setGenericDataValue( 'currency_rate', $value );
	}

	/**
	 * This the base hourly rate used to obtain the final hourly rate from. Primarily used for FLSA calculations when adding overtime wages.
	 * @return bool|mixed
	 */
	function getBaseHourlyRate() {
		return $this->getGenericDataValue( 'base_hourly_rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBaseHourlyRate( $value ) {
		if ( $value === false || $value === '' || $value === null ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'base_hourly_rate', number_format( (float)$value, 4, '.', '' ) );//Always make sure there are 4 decimal places.
	}

	/**
	 * @return bool|mixed
	 */
	function getHourlyRate() {
		return $this->getGenericDataValue( 'hourly_rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHourlyRate( $value ) {
		if ( $value === false || $value === '' || $value === null ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'hourly_rate', number_format( (float)$value, 4, '.', '' ) ); //Always make sure there are 4 decimal places.
	}

	/**
	 * @return bool|mixed
	 */
	function getTotalTimeAmount() {
		return $this->getGenericDataValue( 'total_time_amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTotalTimeAmount( $value ) {
		if ( $value === false || $value === '' || $value === null ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'total_time_amount', $value );
	}

	/**
	 * @return string
	 */
	function calcTotalTimeAmount() {
		//When TTi18n::setLocale() used to set LC_NUMERIC, then bcmath() breaks in locales such as es_ES, due to float values being converted to a string with comma decimal separators. This also causes SQL errors on insert.
		//$retval = ( TTDate::getHours( $this->getTotalTime() ) * $this->getHourlyRate() );
		$retval = bcmul( TTDate::getHours( $this->getTotalTime() ), $this->getHourlyRate() );

		return $retval;
	}

	/**
	 * @return bool|mixed
	 */
	function getHourlyRateWithBurden() {
		return $this->getGenericDataValue( 'hourly_rate_with_burden' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHourlyRateWithBurden( $value ) {
		if ( $value === false || $value === '' || $value === null ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'hourly_rate_with_burden', number_format( (float)$value, 4, '.', '' ) );//Always make sure there are 4 decimal places.
	}

	/**
	 * @return bool|mixed
	 */
	function getTotalTimeAmountWithBurden() {
		return $this->getGenericDataValue( 'total_time_amount_with_burden' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTotalTimeAmountWithBurden( $value ) {
		if ( $value === false || $value === '' || $value === null ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'total_time_amount_with_burden', $value );
	}

	/**
	 * @return string
	 */
	function calcTotalTimeAmountWithBurden() {
		$retval = ( TTDate::getHours( $this->getTotalTime() ) * $this->getHourlyRateWithBurden() );

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getOverride() {
		return $this->fromBool( $this->getGenericDataValue( 'override' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOverride( $value ) {
		return $this->setGenericDataValue( 'override', $this->toBool( $value ) );
	}

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
	 * @return bool|string
	 */
	function getName() {
		switch ( $this->getObjectType() ) {
			case 5:
				$name = TTi18n::gettext( 'Total Time' );
				break;
			case 10: //Worked Time
				$name = TTi18n::gettext( 'Worked Time' );
				break;
			case 20: //Regular Time
			case 25:
			case 30:
			case 40:
			case 100:
			case 110:
				if ( is_object( $this->getPayCodeObject() ) ) {
					$name = $this->getPayCodeObject()->getName();
				} else if ( $this->getObjectType() == 20 ) {                     //Regular Time
					$name = TTi18n::gettext( 'ERROR: UnAssigned Regular Time' ); //No regular time policies to catch all worked time.
				} else {
					$name = TTi18n::gettext( 'ERROR: INVALID PAY CODE' );
				}
				break;
			case 101: //Lunch (Taken)
				$name = TTi18n::gettext( 'Lunch (Taken)' );
				break;
			case 111: //Break (Taken)
				$name = TTi18n::gettext( 'Break (Taken)' );
				break;
			case 50:
				//Absence taken time use the policy name, *not* pay code name.
				$lf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $lf */
				$lf->getByID( $this->getSourceObject() );
				if ( $lf->getRecordCount() == 1 ) {
					$name = $lf->getCurrent()->getName();
				} else {
					$name = TTi18n::gettext( 'ERROR: Invalid Absence Policy' ); //No regular time policies to catch all worked time.
				}
				break;
			default:
				$name = TTi18n::gettext( 'N/A' );
				break;
		}

		if ( isset( $name ) ) {
			return $name;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getIsPartialShift() {
		if ( isset( $this->is_partial_shift ) ) {
			return $this->is_partial_shift;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setIsPartialShift( $bool ) {
		$this->is_partial_shift = $bool;

		return true;
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
	function getEnableCalcAccrualPolicy() {
		if ( isset( $this->calc_accrual_policy ) ) {
			return $this->calc_accrual_policy;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcAccrualPolicy( $bool ) {
		$this->calc_accrual_policy = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	static function getEnableCalcFutureWeek() {
		if ( isset( self::$calc_future_week ) ) {
			return self::$calc_future_week;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	static function setEnableCalcFutureWeek( $bool ) {
		self::$calc_future_week = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableTimeSheetVerificationCheck() {
		if ( isset( $this->timesheet_verification_check ) ) {
			return $this->timesheet_verification_check;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableTimeSheetVerificationCheck( $bool ) {
		$this->timesheet_verification_check = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalculatePolicy() {
		if ( isset( $this->is_calculate_policy ) ) {
			return $this->is_calculate_policy;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalculatePolicy( $bool ) {
		$this->is_calculate_policy = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function calcSystemTotalTime() {
		global $profiler;

		$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 1' );

		if ( $this->getUser() == false || $this->getDateStamp() == false ) {
			Debug::text( ' User/DateStamp not found!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( is_object( $this->getPayPeriodObject() )
				&& $this->getPayPeriodObject()->getStatus() == 20 ) {
			Debug::text( ' Pay Period is closed!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}


		//$this->deleteSystemTotalTime(); //Handled in calculatePolicy now.

		$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
		$cp->setFlag( 'exception', $this->getEnableCalcException() );
		$cp->setFlag( 'exception_premature', $this->getEnablePreMatureException() );
		$cp->setUserObject( $this->getUserObject() );
		$cp->addPendingCalculationDate( array_merge( (array)$this->getDateStamp(), (array)$this->alternate_date_stamps ) );
		$cp->calculate(); //This sets timezone itself.
		$cp->Save();

		return true;
	}

	/**
	 * @return bool
	 */
	function calcWeeklySystemTotalTime() {
		if ( $this->getEnableCalcWeeklySystemTotalTime() == true ) {
			//Used to call reCalculateRange() for the remainder of the week, but this is handled automatically now.
			return true;
		}

		return false;
	}

	/**
	 * @param UserFactory $user_obj
	 * @param int|int[] $date_stamps EPOCH
	 * @param bool $enable_exception
	 * @param bool $enable_premature_exceptions
	 * @param bool $enable_future_exceptions
	 * @param bool $enable_holidays
	 * @return bool
	 */
	static function reCalculateDay( $user_obj, $date_stamps, $enable_exception = false, $enable_premature_exceptions = false, $enable_future_exceptions = true, $enable_holidays = false ) {
		if ( !is_object( $user_obj ) ) {
			return false;
		}

		Debug::text( 'Re-calculating User ID: ' . $user_obj->getId() . ' Enable Exception: ' . (int)$enable_exception, __FILE__, __LINE__, __METHOD__, 10 );

		if ( !is_array( $date_stamps ) ) {
			$date_stamps = [ $date_stamps ];
		}
		Debug::Arr( $date_stamps, 'bDate Stamps: ', __FILE__, __LINE__, __METHOD__, 10 );

		$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */

		$cp->setFlag( 'exception', $enable_exception );
		$cp->setFlag( 'exception_premature', $enable_premature_exceptions );
		$cp->setFlag( 'exception_future', $enable_future_exceptions );

		$cp->setUserObject( $user_obj );
		$cp->addPendingCalculationDate( $date_stamps );
		$cp->calculate(); //This sets timezone itself.

		return $cp->Save();
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User


		if ( $this->Validator->getValidateOnly() == false || $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}

		// Pay Period
		if ( $this->getPayPeriod() !== false && $this->getPayPeriod() != TTUUID::getZeroID() ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$this->Validator->isResultSetWithRows( 'pay_period',
												   $pplf->getByID( $this->getPayPeriod() ),
												   TTi18n::gettext( 'Invalid Pay Period' )
			);
		}

		// Date
		if ( $this->getDateStamp() !== false ) {
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
		}

		// Punch Control ID
		if ( $this->getPunchControlID() !== false && $this->getPunchControlID() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'punch_control_id',
												   $pclf->getByID( $this->getPunchControlID() ),
												   TTi18n::gettext( 'Invalid Punch Control ID' )
			);
		}
		// Object Type
		if ( $this->getObjectType() !== false ) {
			$this->Validator->inArrayKey( 'object_type',
										  $this->getObjectType(),
										  TTi18n::gettext( 'Incorrect Object Type' ),
										  $this->getOptions( 'object_type' )
			);
		}
		// Source Object
		if ( $this->getSourceObject() !== false && $this->getSourceObject() != TTUUID::getZeroID() ) {
			$lf = $this->getSourceObjectListFactory( $this->getObjectType() );
			$this->Validator->isResultSetWithRows( 'src_object_id',
												   ( is_object( $lf ) ) ? $lf->getByID( $this->getSourceObject() ) : false,
												   TTi18n::gettext( 'Invalid Source Object' )
			);
		}
		// Pay Code
		if ( $this->getPayCode() !== false && $this->getPayCode() != TTUUID::getZeroID() ) {
			$lf = TTNew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $lf */
			$this->Validator->isResultSetWithRows( 'pay_code_id',
												   $lf->getByID( $this->getPayCode() ),
												   TTi18n::gettext( 'Invalid Pay Code' )
			);
		}
		// Branch
		if ( $this->getBranch() !== false && $this->getBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows( 'branch_id',
												   $blf->getByID( $this->getBranch() ),
												   TTi18n::gettext( 'Branch does not exist' )
			);
		}
		// Department
		if ( $this->getDepartment() !== false && $this->getDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows( 'department_id',
												   $dlf->getByID( $this->getDepartment() ),
												   TTi18n::gettext( 'Department does not exist' )
			);
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			// Job
			if ( $this->getJob() !== false && $this->getJob() != TTUUID::getZeroID() ) {
				$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
				$this->Validator->isResultSetWithRows( 'job_id',
													   $jlf->getByID( $this->getJob() ),
													   TTi18n::gettext( 'Job does not exist' )
				);
			}
			// Job Item
			if ( $this->getJobItem() !== false && $this->getJobItem() != TTUUID::getZeroID() ) {
				$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
				$this->Validator->isResultSetWithRows( 'job_item_id',
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
			// Bad Quantity
			if ( $this->getBadQuantity() != '' ) {
				$this->Validator->isFloat( 'bad_quantity',
										   $this->getBadQuantity(),
										   TTi18n::gettext( 'Incorrect bad quantity' )
				);
			}
		}

		// Start Type -- This is an int, so it can return 0 in some cases.
		if ( !empty( $this->getStartType() ) ) {
			$this->Validator->inArrayKey( 'start_type',
										  $this->getStartType(),
										  TTi18n::gettext( 'Incorrect Start Type' ),
										  $this->getOptions( 'start_type' )
			);
		}
		// Start Time Stamp
		if ( !empty( $this->getStartTimeStamp() ) ) {
			$this->Validator->isDate( 'start_time_stamp',
									  $this->getStartTimeStamp(),
									  TTi18n::gettext( 'Incorrect start time stamp' )
			);
		}
		// End Type -- This is an int, so it can return 0 in some cases.
		if ( !empty( $this->getEndType() ) ) {
			$this->Validator->inArrayKey( 'end_type',
										  $this->getEndType(),
										  TTi18n::gettext( 'Incorrect End Type' ),
										  $this->getOptions( 'end_type' )
			);
		}
		// End Time Stamp
		if ( $this->getEndTimeStamp() != '' ) {
			$this->Validator->isDate( 'end_time_stamp',
									  $this->getEndTimeStamp(),
									  TTi18n::gettext( 'Incorrect end time stamp' )
			);
		}
		// Total time
		$this->Validator->isNumeric( 'total_time',
									 $this->getTotalTime(),
									 TTi18n::gettext( 'Incorrect total time' )
		);
		$this->Validator->isLessThan( 'total_time',
									 $this->getTotalTime(),
									 TTi18n::gettext( 'Total time is too high' ),
				 					 ( 100000 * 3600 ) //100,000hrs is maximum for any single UDT record. Should be more than enough, but needs to be well lower than the SQL maximum integer value.
		);

		// Actual total time
		$this->Validator->isNumeric( 'actual_total_time',
									 $this->getActualTotalTime(),
									 TTi18n::gettext( 'Incorrect actual total time' )
		);
		$this->Validator->isLessThan( 'actual_total_time',
									  $this->getTotalTime(),
									  TTi18n::gettext( 'Actual total time is too high' ),
									  ( 100000 * 3600 ) //100,000hrs is maximum for any single UDT record. Should be more than enough, but needs to be well lower than the SQL maximum integer value.
		);

		// Currency
		if ( $this->getCurrency() !== false && $this->getCurrency() != TTUUID::getZeroId() ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}
		// Currency Rate
		if ( $this->getCurrencyRate() !== false ) {
			$this->Validator->isFloat( 'currency_rate',
									   $this->getCurrencyRate(),
									   TTi18n::gettext( 'Incorrect Currency Rate' )
			);
		}
		// Base Hourly Rate
		if ( $this->getBaseHourlyRate() !== false ) {
			$this->Validator->isFloat( 'base_hourly_rate',
									   $this->getBaseHourlyRate(),
									   TTi18n::gettext( 'Incorrect Base Hourly Rate' )
			);
		}
		// Hourly Rate
		if ( $this->getHourlyRate() !== false ) {
			$this->Validator->isFloat( 'hourly_rate',
									   $this->getHourlyRate(),
									   TTi18n::gettext( 'Incorrect Hourly Rate' )
			);
		}
		// Total Time Amount
		if ( $this->getTotalTimeAmount() !== false ) {
			$this->Validator->isFloat( 'total_time_amount',
									   $this->getTotalTimeAmount(),
									   TTi18n::gettext( 'Incorrect Total Time Amount' )
			);
		}
		// Hourly Rate with Burden
		if ( $this->getHourlyRateWithBurden() !== false ) {
			$this->Validator->isFloat( 'hourly_rate_with_burden',
									   $this->getHourlyRateWithBurden(),
									   TTi18n::gettext( 'Incorrect Hourly Rate with Burden' )
			);
		}

		// Total Time Amount with Burden
		if ( $this->getTotalTimeAmountWithBurden() !== false ) {
			$this->Validator->isFloat( 'total_time_amount_with_burden',
									   $this->getTotalTimeAmountWithBurden(),
									   TTi18n::gettext( 'Incorrect Total Time Amount with Burden' )
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

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//See if the user_id changed, if so prevent it from being saved, as the user_id should never be changed on a user_date_total record as it will cause problems with recalculating.
		if ( $this->getGenericOldDataValue( 'user_id' ) != false && $this->getUser() != $this->getGenericOldDataValue( 'user_id' ) ) {
			$this->Validator->isTRUE( 'user_id',
									  false,
									  TTi18n::gettext( 'Accumulated Time cannot be assigned to a different employee once created' ) );
		}

		if ( $this->Validator->getValidateOnly() == false && $this->getObjectType() == false ) {
			$this->Validator->isTRUE( 'object_type_id',
									  false,
									  TTi18n::gettext( 'Type is invalid' ) );
		}

		//Check to make sure if this is an absence row, the absence policy is actually set.
		if ( $this->getDeleted() == false && $this->getObjectType() == 50 ) {
			if ( $this->getSourceObject() == '' ) {
				$this->Validator->isTRUE( 'src_object_id',
										  false,
										  TTi18n::gettext( 'Please specify an absence type' ) );
			}
		}

		//When calculating all the policies from CalculatePolicy class, skip some validation checks that can't be resolved anyways, so we don't cause the transaction to rollback.
		// Check to make sure absences and manual timesheet time can't be entered before the employees hire date or after termination date.
		if ( $this->getDeleted() == false && ( $this->getObjectType() == 10 || $this->getObjectType() == 50 ) ) { //10=Worked Time, 50=Absence
			if ( $this->getEnableCalculatePolicy() == false ) {
				if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getHireDate() != '' && TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
					$this->Validator->isTRUE( 'date_stamp',
											  false,
											  TTi18n::gettext( 'Date is before employees hire date' ) );
				}

				if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getTerminationDate() != '' && TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
					$this->Validator->isTRUE( 'date_stamp',
											  false,
											  TTi18n::gettext( 'Date is after employees termination date' ) );
				}
			}
		}


		//Check to make sure if this is an absence row, the absence policy is actually set.
		//if ( $this->getObjectType() == 50 AND $this->getPayCode() == FALSE ) {
		if ( $this->getObjectType() == 50 && $this->getSourceObject() == '' && $this->getOverride() == false ) {
			$this->Validator->isTRUE( 'src_object_id',
									  false,
									  TTi18n::gettext( 'Please specify an absence type' ) );
		}
		//Check to make sure if this is an overtime row, the overtime policy is actually set.
		if ( $this->getObjectType() == 30 && $this->getSourceObject() == '' && $this->getOverride() == false ) {
			$this->Validator->isTRUE( 'over_time_policy_id',
									  false,
									  TTi18n::gettext( 'Invalid Overtime Policy' ) );
		}
		//Check to make sure if this is an premium row, the premium policy is actually set.
		if ( $this->getObjectType() == 40 && $this->getSourceObject() == '' && $this->getOverride() == false ) {
			$this->Validator->isTRUE( 'premium_policy_id',
									  false,
									  TTi18n::gettext( 'Invalid Premium Policy' ) );
		}
		//Check to make sure if this is an meal row, the meal policy is actually set.
		if ( $this->getObjectType() == 100 && $this->getSourceObject() == '' && $this->getOverride() == false ) {
			$this->Validator->isTRUE( 'meal_policy_id',
									  false,
									  TTi18n::gettext( 'Invalid Meal Policy' ) );
		}
		//Check to make sure if this is an break row, the break policy is actually set.
		if ( $this->getObjectType() == 110 && $this->getSourceObject() == '' && $this->getOverride() == false ) {
			$this->Validator->isTRUE( 'break_policy_id',
									  false,
									  TTi18n::gettext( 'Invalid Break Policy' ) );
		}

		//When calculating all the policies from CalculatePolicy class, skip some validation checks that can't be resolved anyways, so we don't cause the transaction to rollback.
		if ( $this->getEnableCalculatePolicy() == false ) {
			//Check that the user is allowed to be assigned to the absence policy
			// Only do this when creating a new record, as the user may have had entries made then later have the absence policy disabled from the policy group.
			//   In that case it would cause this record from not being saved properly then and possibly prevent recalculations from finishing.
			if ( $this->getDeleted() == false && $this->getObjectType() == 50 && $this->getSourceObject() != '' && is_object( $this->getUserObject() ) ) {
				$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
				$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [ 'user_id' => [ $this->getUser() ], 'absence_policy' => [ $this->getSourceObject() ] ] );
				if ( $pglf->getRecordCount() == 0 ) {
					$this->Validator->isTRUE( 'absence_policy_id',
											  false,
											  TTi18n::gettext( 'This absence policy is not available for this employee' ) );
				}
			}

			if ( $this->getDateStamp() != false && is_object( $this->getPayPeriodObject() ) && $this->getPayPeriodObject()->getIsLocked() == true ) {
				//Make sure we only check for pay period being locked when *NOT* called from CalculatePolicy otherwise it can prevent recalculations from occurring
				//after the pay period is locked (ie: recalculating exceptions each day from maintenance jobs?)
				//We need to be able to stop absences (non-overridden ones too) from being deleted in closed pay periods.
				$this->Validator->isTRUE( 'date_stamp',
										  false,
										  TTi18n::gettext( 'Pay Period is Currently Locked' ) );
			}
		}

		//This is likely caused by employee not being assigned to a pay period schedule?
		//Make sure to allow entries in the future (ie: absences) where no pay period exists yet.
		if ( $this->Validator->getValidateOnly() == false && $this->getDeleted() == false && $this->getDateStamp() == false ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
		}
//		This was moved above, so we can skip checking if the pay period is locked when run through CalculatePolicy.
//		elseif ( ( $this->getOverride() == TRUE OR ( $this->getOverride() == FALSE AND $this->getObjectType() == 50 ) )
//					AND $this->getDateStamp() != FALSE AND is_object( $this->getPayPeriodObject() ) AND $this->getPayPeriodObject()->getIsLocked() == TRUE ) {
//			//Make sure we only check for pay period being locked if override is TRUE, otherwise it can prevent recalculations from occurring
//			//after the pay period is locked (ie: recalculating exceptions each day from maintenance jobs?)
//			//We need to be able to stop absences (non-overridden ones too) from being deleted in closed pay periods.
//			$this->Validator->isTRUE(	'date_stamp',
//										FALSE,
//										TTi18n::gettext('Pay Period is Currently Locked') );
//		}


		if ( $this->getDeleted() == false ) {
			//Make sure the total time matches the start/end time stamps when handling overridden records.
			//This should avoid setting the total_time=0 when the start/end times show 2hrs or something.
			if ( $this->getOverride() == true && $this->getStartTimeStamp() != '' && $this->getEndTimeStamp() != '' ) {
				if ( abs( $this->getEndTimeStamp() - $this->getStartTimeStamp() ) != abs( $this->getTotalTime() ) ) {
					$this->Validator->isTRUE( 'total_time',
											  false,
											  TTi18n::gettext( 'Time does not match Start Date/Time and End Date/Time' ) );
				}
			}
		}

		//Make sure that we aren't trying to overwrite an already overridden entry made by the user for some special purpose.
		if ( $this->getDeleted() == false
				&& $this->isNew() == true
				&& is_object( $this->getUserObject() ) ) {
			//Debug::text('Checking for already existing overridden entries ... User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp() .' Object Type ID: '. $this->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);

			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			if ( ( $this->getObjectType() == 5 || $this->getObjectType() == 10 || $this->getObjectType() == 20 )
					&& TTUUID::isUUID( $this->getPunchControlID() ) && $this->getPunchControlID() != TTUUID::getZeroID() && $this->getPunchControlID() != TTUUID::getNotExistID() ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPunchControlIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPunchControlID(), true );
			} else if ( $this->getObjectType() != 10 ) { //10=Worked Time. This is often used to import manual timesheet data, and some customers require importing of "duplicate" entries, since the data is converted from other timeclock systems.
				//Allow object_type_id=50 (absence taken) entries to override object_type_id=25 entries.
				//So users can create an absence schedule shift, then override it to a smaller number of hours.
				//However how do we handle cases where an undertime absence policy creates a object_type_id=25 record and the user wants to override it?

				//Allow employee to have multiple entries on the same day as long as the branch, department, job, task are all different.
				if ( $this->getDateStamp() != false && $this->getUser() != false ) {
					$udtlf->getByUserIdAndDateStampAndObjectTypeAndSrcObjectAndPayCodeAndBranchAndDepartmentAndJobAndJobItemOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getSourceObject(), $this->getPayCode(), $this->getBranch(), $this->getDepartment(), $this->getJob(), $this->getJobItem(), true );
				}
			}

			//Debug::text('Record Count: '. (int)$udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text( 'Found an overridden row... NOT SAVING: ' . $udtlf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->Validator->isTRUE( ( ( $this->getObjectType() == 25 || $this->getObjectType() == 50 ) ? 'absence_policy_id' : 'object_type_id' ),
						false,
						TTi18n::gettext( 'Similar entry already exists, not overriding' ) );
			}
			unset( $udtlf );
		}

		if ( $ignore_warning == false ) {
			//Check to see if timesheet is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != false
					&& is_object( $this->getPayPeriodObject() )
					&& is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					&& $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
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
		if ( $this->getPayPeriod() == false ) {
			$this->setPayPeriod(); //Not specifying pay period forces it to be looked up.
		}

		if ( $this->getPayCode() === false ) {
			$this->setPayCode( TTUUID::getZeroID() );
		}

		if ( $this->getPunchControlID() === false ) {
			$this->setPunchControlID( TTUUID::getZeroID() );
		}

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

		$this->setTotalTimeAmount( $this->calcTotalTimeAmount() );
		$this->setTotalTimeAmountWithBurden( $this->calcTotalTimeAmountWithBurden() );

		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if timesheet is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != false
					&& is_object( $this->getPayPeriodObject() )
					&& is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					&& $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text( 'Pay Period is verified, deleting verification records: ' . $pptsvlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $pptsvlf as $pptsv_obj ) {
						if ( $this->getObjectType() == 50 && is_object( $this->getSourceObjectObject() ) ) {
							TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText( 'TimeSheet Modified After Verification' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Absence' ) . ': ' . $this->getSourceObjectObject()->getName() . ' - ' . TTDate::getDate( 'DATE', $this->getDateStamp() ), null, $pptsvlf->getTable() );
						}
						$pptsv_obj->setDeleted( true );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getEnableCalcSystemTotalTime() == true ) {
			Debug::text( 'Calc System Total Time Enabled: ', __FILE__, __LINE__, __METHOD__, 10 );
			$this->calcSystemTotalTime();
		} else {
			Debug::text( 'Calc System Total Time Disabled: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	static function sortAccumulatedTimeByOrder( $a, $b ) {
		if ( $a['order'] == $b['order'] ) {
			return strnatcmp( $a['label'], $b['label'] );
		} else {
			return ( $a['order'] - $b['order'] );
		}
	}

	/**
	 * Takes UserDateTotal rows, and calculate the accumlated time sections
	 * @param $data
	 * @param bool $include_daily_totals
	 * @return array|bool
	 */
	static function calcAccumulatedTime( $data, $include_daily_totals = true ) {
		if ( is_array( $data ) && count( $data ) > 0 ) {
			$retval = [];
			//Keep track of item ids for each section type so we can decide later on if we can eliminate unneeded data.
			$section_ids = [ 'branch' => [], 'department' => [], 'job' => [], 'job_item' => [] ];

			//Sort data by date_stamp at the top, so it works for multiple days at a time.
			//Keep a running total of all days, mainly for 'weekly total" purposes.
			//
			//The 'order' array element is used by JS to sort the rows displayed to the user.
			foreach ( $data as $row ) {
				//Skip rows with a 0 total_time.
				if ( $row['total_time'] == 0 && ( ( isset( $row['override'] ) && $row['override'] == false ) || !isset( $row['override'] ) ) ) {
					continue;
				}

				switch ( $row['object_type_id'] ) {
					//Section: Accumulated Time:
					//	Includes: Total Time, Regular Time, Overtime, Meal Policy Time, Break Policy Time.
					case 5: //System Total Time row.
						$order = 80;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'total';
						$label_suffix = '';
						break;
					case 10: //System Worked Time row.
						$order = 10;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'worked_time';
						$label_suffix = '';
						break;
					case 20: //Regular Time row.
						$order = 50;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'regular_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					//Section: Absence Time:
					//	Includes: All Absence Time
					case 25: //Absence Policy Row.
						$order = 75;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'absence_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					case 30: //Over Time row.
						$order = 60;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'over_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					case 100: //Meal Policy Row.
						$order = 30;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'meal_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					case 110: //Break Policy Row.
						$order = 20;
						$primary_array_key = 'accumulated_time';
						$secondary_array_key = 'break_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					//Section: Premium Time:
					//	Includes: All Premium Time
					case 40: //Premium Policy Row.
						$order = 85;
						$primary_array_key = 'premium_time';
						$secondary_array_key = 'premium_time_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					//Section: Absence Time (Taken):
					//	Includes: All Absence Time
					case 50: //Absence Time (Taken) Row.
						$order = 90;
						$primary_array_key = 'absence_time_taken';
						$secondary_array_key = 'absence_' . $row['pay_code_id'];
						$label_suffix = '';
						break;
					default:
						//Skip Lunch/Break Taken records, as those are handled in a different section.
						//Debug::text('Skipping Object Type ID... User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Object Type ID: '. $row['object_type_id'], __FILE__, __LINE__, __METHOD__, 10);
						continue 2; //Must continue(2) to break out of the switch statement and foreach() loop.
						break;
				}
				//Debug::text('User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Object Type ID: '. $row['object_type_id'] .' Keys: Primary: '. $primary_array_key .' Secondary: '. $secondary_array_key, __FILE__, __LINE__, __METHOD__, 10);

				if ( $include_daily_totals == true ) {
					if ( !isset( $retval[$row['date_stamp']][$primary_array_key][$secondary_array_key] ) ) {
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key] = [ 'label' => $row['name'] . $label_suffix, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
					}
					$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time'] += $row['total_time'];
					if ( $row['object_type_id'] == 10 ) {
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time_amount'] = false;
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['hourly_rate'] = false;
					} else {
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time_amount'] = bcadd( $retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['hourly_rate'] = bcdiv( $retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time_amount'], ( ( $retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time'] > 0 ) ? TTDate::getHours( $retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['total_time'] ) : 1 ) );

						//Calculate Accumulated Time Total.
						if ( in_array( $row['object_type_id'], [ 20, 25, 30 ] ) ) {
							if ( !isset( $retval[$row['date_stamp']]['accumulated_time']['total']['label'] ) ) {
								$retval[$row['date_stamp']]['accumulated_time']['total']['label'] = TTi18n::getText( 'Total Time' );
							}
							if ( !isset( $retval[$row['date_stamp']]['accumulated_time']['total']['order'] ) ) {
								$retval[$row['date_stamp']]['accumulated_time']['total']['order'] = 999; //Always goes at the end.
							}

							if ( !isset( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time_amount'] ) ) {
								$retval[$row['date_stamp']]['accumulated_time']['total']['total_time_amount'] = 0;
							}
							if ( !isset( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] ) ) {
								$retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] = 0;
							}

							$retval[$row['date_stamp']]['accumulated_time']['total']['total_time_amount'] = bcadd( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
							$retval[$row['date_stamp']]['accumulated_time']['total']['hourly_rate'] = bcdiv( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time_amount'], ( ( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] > 0 ) ? TTDate::getHours( $retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] ) : 1 ) );
						}
					}

					if ( isset( $row['override'] ) && $row['override'] == true ) {
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['override'] = true;
					}
					if ( isset( $row['note'] ) && $row['note'] == true ) {
						$retval[$row['date_stamp']][$primary_array_key][$secondary_array_key]['note'] = true;
					}
				}

				if ( $row['object_type_id'] != 50 ) { //Don't show Absences (Taken) in Weekly/Pay Period totals.
					if ( !isset( $retval['total'][$primary_array_key][$secondary_array_key] ) ) {
						$retval['total'][$primary_array_key][$secondary_array_key] = [ 'label' => $row['name'] . $label_suffix, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
					}
					$retval['total'][$primary_array_key][$secondary_array_key]['total_time'] += $row['total_time'];
					if ( $row['object_type_id'] == 10 ) {
						$retval['total'][$primary_array_key][$secondary_array_key]['total_time_amount'] = false;
						$retval['total'][$primary_array_key][$secondary_array_key]['hourly_rate'] = false;
					} else {
						$retval['total'][$primary_array_key][$secondary_array_key]['total_time_amount'] = bcadd( $retval['total'][$primary_array_key][$secondary_array_key]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
						$retval['total'][$primary_array_key][$secondary_array_key]['hourly_rate'] = bcdiv( $retval['total'][$primary_array_key][$secondary_array_key]['total_time_amount'], ( ( $retval['total'][$primary_array_key][$secondary_array_key]['total_time'] > 0 ) ? TTDate::getHours( $retval['total'][$primary_array_key][$secondary_array_key]['total_time'] ) : 1 ) );

						//Calculate Accumulated Time Total.
						if ( in_array( $row['object_type_id'], [ 20, 25, 30 ] ) ) {
							//If there is no time on the 2nd (current) week in the pay period, but there is time on the first week, we need to make sure there is a label.
							if ( !isset( $retval['total']['accumulated_time']['total']['label'] ) ) {
								$retval['total']['accumulated_time']['total']['label'] = TTi18n::getText( 'Total Time' );
							}
							if ( !isset( $retval['total']['accumulated_time']['total']['order'] ) ) {
								$retval['total']['accumulated_time']['total']['order'] = 999; //Always goes at the end.
							}

							if ( !isset( $retval['total']['accumulated_time']['total']['total_time_amount'] ) ) {
								$retval['total']['accumulated_time']['total']['total_time_amount'] = 0;
							}
							if ( !isset( $retval['total']['accumulated_time']['total']['total_time'] ) ) {
								$retval['total']['accumulated_time']['total']['total_time'] = 0;
							}
							$retval['total']['accumulated_time']['total']['total_time_amount'] = bcadd( $retval['total']['accumulated_time']['total']['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
							$retval['total']['accumulated_time']['total']['hourly_rate'] = bcdiv( $retval['total']['accumulated_time']['total']['total_time_amount'], ( ( $retval['total']['accumulated_time']['total']['total_time'] > 0 ) ? TTDate::getHours( $retval['total']['accumulated_time']['total']['total_time'] ) : 1 ) );
						}
					}
				}


				//Section: Accumulated Time by Branch, Department, Job, Task
				if ( $include_daily_totals == true && $row['object_type_id'] == 20 || $row['object_type_id'] == 30 ) {
					//Branch
					$branch_name = $row['branch'];
					if ( $branch_name == '' ) {
						$branch_name = TTi18n::gettext( 'No Branch' );
					}
					if ( !isset( $retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']] ) ) {
						$retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']] = [ 'label' => $branch_name, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
					}
					$retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']]['total_time'] += $row['total_time'];
					$retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']]['total_time_amount'] = bcadd( $retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
					$section_ids['branch'][] = TTUUID::castUUID( $row['branch_id'] );

					//Department
					$department_name = $row['department'];
					if ( $department_name == '' ) {
						$department_name = TTi18n::gettext( 'No Department' );
					}
					if ( !isset( $retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']] ) ) {
						$retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']] = [ 'label' => $department_name, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
					}
					$retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']]['total_time'] += $row['total_time'];
					$retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']]['total_time_amount'] = bcadd( $retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
					$section_ids['department'][] = TTUUID::castUUID( $row['department_id'] );

					if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
						//Job
						$job_name = $row['job'];
						if ( $job_name == '' ) {
							$job_name = TTi18n::gettext( 'No Job' );
						}
						if ( !isset( $retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']] ) ) {
							$retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']] = [ 'label' => $job_name, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
						}
						$retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']]['total_time'] += $row['total_time'];
						$retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']]['total_time_amount'] = bcadd( $retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
						$section_ids['job'][] = TTUUID::castUUID( $row['job_id'] );

						//Job Item/Task
						$job_item_name = $row['job_item'];
						if ( $job_item_name == '' ) {
							$job_item_name = TTi18n::gettext( 'No Task' );
						}
						if ( !isset( $retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']] ) ) {
							$retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']] = [ 'label' => $job_item_name, 'total_time' => 0, 'total_time_amount' => 0, 'hourly_rate' => 0, 'order' => $order ];
						}
						$retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']]['total_time'] += $row['total_time'];
						$retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']]['total_time_amount'] = bcadd( $retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']]['total_time_amount'], ( isset( $row['total_time_amount'] ) ) ? $row['total_time_amount'] : 0 );
						$section_ids['job_item'][] = TTUUID::castUUID( $row['job_item_id'] );
					}
					//Debug::text('ID: '. $row['id'] .' User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Branch: '. $branch_name .' Job: '. $job_name, __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( empty( $retval ) == false ) {
				//Remove any unneeded data, such as "No Branch" for all dates in the range
				foreach ( $section_ids as $section => $ids ) {
					$ids = array_unique( $ids );
					sort( $ids );
					if ( isset( $ids[0] ) && $ids[0] == TTUUID::getZeroID() && count( $ids ) == 1 ) {
						foreach ( $retval as $date_stamp => $day_data ) {
							unset( $retval[$date_stamp][$section . '_time'] );
						}
					} else {
						foreach ( $retval as $date_stamp => $day_data ) {
							if ( isset( $retval[$date_stamp]['accumulated_time'] ) ) {
								uasort( $retval[$date_stamp]['accumulated_time'], [ 'self', 'sortAccumulatedTimeByOrder' ] ); //Sort by Order then label.
							}
						}
						unset( $day_data );
					}
				}

				//Sort the accumulated time so its always in the same order.
				if ( isset( $retval['total']['accumulated_time'] ) ) {
					uasort( $retval['total']['accumulated_time'], [ 'self', 'sortAccumulatedTimeByOrder' ] ); //Sort by Order then label.
				}

				return $retval;
			}
		}

		return false;
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
						case 'pay_period_id': //Ignore this if its set, as its should be determined in preSave().
							break;
						case 'date_stamp':
							$this->setDateStamp( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'start_time_stamp':
							$this->setStartTimeStamp( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'end_time_stamp':
							$this->setEndTimeStamp( TTDate::parseDateTime( $data[$key] ) );
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
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */

		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {
					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'group':
						case 'title':
						case 'default_branch':
						case 'default_department':
						case 'branch':
						case 'department':
						case 'over_time_policy':
						case 'absence_policy':
						case 'premium_policy':
						case 'meal_policy':
						case 'break_policy':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'title_id':
						case 'user_id':
						case 'group_id':
						case 'pay_period_id':
						case 'default_branch_id':
						case 'default_department_id':
						case 'absence_policy_type_id':
							$data[$variable] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'object_type':
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
							break;
						case 'user_status_id':
							$data[$variable] = (int)$this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_time_stamp':
						case 'end_time_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() ); //Include both date+time
							break;
						case 'name':
							$data[$variable] = $this->getName();
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
		if ( $this->getOverride() == true && $this->getDateStamp() != false ) {
			if ( $this->getObjectType() == 50 ) { //Absence
				return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Absence - Employee ' ) . ' : '. UserListFactory::getFullNameById( $this->getUser() ) .' ' . TTi18n::getText( 'Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getDateStamp() ) . ' ' . TTi18n::getText( 'Total Time' ) . ': ' . TTDate::getTimeUnit( $this->getTotalTime() ), null, $this->getTable(), $this );
			} else {
				return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Accumulated Time - Employee' ) . ': '. UserListFactory::getFullNameById( $this->getUser() ) .' ' . TTi18n::getText( 'Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getDateStamp() ) . ' ' . TTi18n::getText( 'Total Time' ) . ': ' . TTDate::getTimeUnit( $this->getTotalTime() ), null, $this->getTable(), $this );
			}
		}

		return false;
	}
}

?>
