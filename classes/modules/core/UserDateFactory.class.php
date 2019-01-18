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
 * @package Core
 */
class UserDateFactory extends Factory {
	protected $table = 'user_date';
	protected $pk_sequence_name = 'user_date_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $pay_period_obj = NULL;

	/**
	 * @return bool
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
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function findPayPeriod() {
		if ( $this->getDateStamp() > 0
				AND TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID() ) {
			//FIXME: With MySQL since it doesn't handle timezones very well I think we need to
			//get the timezone of the payperiod schedule for this user, and set the timezone to that
			//before we go searching for a pay period, otherwise the wrong payperiod might be returned.
			//This might happen when the MySQL server is in one timezone (ie: CST) and the pay period
			//schedule is set to another timezone (ie: PST)
			//This could severely slow down a lot of operations though, so make this specific to MySQL only.
			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getDateStamp() );
			if ( $pplf->getRecordCount() == 1 ) {
				$pay_period_id = $pplf->getCurrent()->getID();
				Debug::Text('Pay Period Id: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10);
				return $pay_period_id;
			}
			/*
			$pay_period = $pplf->getCurrent();

			Debug::Text('Pay Period Id: '. $pay_period->getId(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $pay_period->getId() !== FALSE ) {
				return $pay_period->getId();
			}
			*/
		}

		Debug::Text('Unable to find pay period for User ID: '. $this->getUser() .' Date Stamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
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
	function setPayPeriod( $value = NULL) {
		$value = trim($value);
		if ( $value == NULL ) {
			$value = $this->findPayPeriod();
		}
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date EPOCH
	 * @param null $timezone
	 * @return bool
	 */
	static function findOrInsertUserDate( $user_id, $date, $timezone = NULL ) {
		//Allow	 user_id=0 for saving open schedule shifts.
		$user_id = TTUUID::castUUID($user_id);
		if ( $user_id != '' AND $date > 0 ) {
			$date = TTDate::getMiddleDayEpoch( $date ); //Use mid day epoch so the timezone conversion across DST doesn't affect the date.

			if ( $timezone == NULL ) {
				//Find the employees preferred timezone, base the user date off that instead of the pay period timezone,
				//as it can be really confusing to the user if they punch in at 10AM on Sept 27th, but it records as Sept 26th because
				//the PP Schedule timezone is 12hrs different or something.
				$uplf = TTnew( 'UserPreferenceListFactory' );
				$uplf->getByUserID( $user_id );
				if ( $uplf->getRecordCount() > 0 ) {
					$timezone = $uplf->getCurrent()->getTimeZone();
				}
			}

			$date = TTDate::convertTimeZone( $date, $timezone );
			//Debug::text(' Using TimeZone: '. $timezone .' Date: '. TTDate::getDate('DATE+TIME', $date) .' ('.$date.')', __FILE__, __LINE__, __METHOD__, 10);

			$udlf = TTnew( 'UserDateListFactory' );
			$udlf->getByUserIdAndDate( $user_id, $date );
			if ( $udlf->getRecordCount() == 1 ) {
				$id = $udlf->getCurrent()->getId();
				//Debug::text(' Found Already Existing User Date ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
				return $id;
			} elseif ( $udlf->getRecordCount() == 0 ) {
				Debug::text(' Inserting new UserDate row. User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__, 10);

				//Insert new row
				$udf = TTnew( 'UserDateFactory' );
				$udf->setUser( $user_id );
				$udf->setDateStamp( $date );
				$udf->setPayPeriod();

				if ( $udf->isValid() ) {
					return $udf->Save();
				} else {
					Debug::text(' INVALID user date row. Pay Period Locked?', __FILE__, __LINE__, __METHOD__, 10);
				}
			} elseif ( $udlf->getRecordCount() > 1 ) {
				Debug::text(' More then 1 user date row was detected!!: '. $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text(' Invalid arguments... User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text(' Cant find or insert User Date ID. User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date EPOCH
	 * @return bool
	 */
	static function getUserDateID( $user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date);
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__, 10);
		if ( $user_date_id != '' ) {
			return $user_date_id;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//This function deletes all rows from other tables that require a user_date row.
	//We need to keep this in its own function so we can call it BEFORE
	//actually deleting the user_date row. As we need to have a unique
	//index on user_id, date_stamp so we never get duplicate rows, essentially making the deleted
	//column useless.
	/**
	 * @param string $user_date_id UUID
	 * @return bool
	 */
	static function deleteChildren( $user_date_id ) {
		if (  $user_date_id == '' ) {
			return FALSE;
		}

	}

	/**
	 * @return bool
	 */
	function isUnique() {
		//Allow user_id=0 for OPEN scheduled shifts.
		if ( $this->getUser() === FALSE ) {
			return FALSE;
		}

		if ( $this->getDateStamp() == FALSE	 ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => $this->getUser(),
					'date_stamp' => $this->db->BindDate( $this->getDateStamp() ),
					);

		$query = 'select id from '. $this->getTable() .' where user_id = ? AND date_stamp = ? AND deleted=0';
		$user_date_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_date_id, 'Unique User Date.', __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_date_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_date_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		// User
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Invalid Employee')
														);
		}
		// Pay Period
		if ( $this->getPayPeriod() != FALSE ) {
			$pplf = TTnew( 'PayPeriodListFactory' );
			$this->Validator->isResultSetWithRows(	'pay_period',
															$pplf->getByID($this->getPayPeriod()),
															TTi18n::gettext('Invalid Pay Period')
														);
		}
		// Date
		$this->Validator->isDate(		'date_stamp',
												$this->getDateStamp(),
												TTi18n::gettext('Incorrect date')
											);
		if ( $this->Validator->isError('date_stamp') == FALSE ) {
			if ( $this->getDateStamp() <= 0 ) {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date')
											);
			}
 		}


		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//Make sure pay period isn't locked!
		if ( TTUUID::isUUID( $this->getPayPeriod() ) AND $this->getPayPeriod() != TTUUID::getZeroID() AND $this->getPayPeriod() != TTUUID::getNotExistID() ) {
			if ( is_object( $this->getPayPeriodObject() ) AND $this->getPayPeriodObject()->getIsLocked() == TRUE ) {
				$this->Validator->isTRUE(	'pay_period',
											FALSE,
											TTi18n::gettext('Pay Period is Currently Locked') );
			}
		}

		//Make sure this is a UNIQUE user_date row.
		$this->Validator->isTRUE(	'date_stamp',
									$this->isUnique(),
									TTi18n::gettext('Employee can not have duplicate entries on the same day') );


		//Make sure the date isn't BEFORE the first pay period.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByUserID( $this->getUser(), 1, NULL, NULL, array('a.start_date' => 'asc') );
		if ( $pplf->getRecordCount() > 0 ) {
			$first_pp_obj = $pplf->getCurrent();
			if ( $this->getDateStamp() < $first_pp_obj->getStartDate() ) {
				$this->Validator->isTRUE(	'pay_period',
											FALSE,
											TTi18n::gettext('Date specified is before the first pay period started') );
			}
		}
			//else {
			//This causes a validation error when saving a record without a pay period (ie: in the future a few weeks)
			//Therefore its breaking critical functionality and should be disabled.
			//This also affects saving OPEN shifts when as no user is assigned to them and therefore no pay period.
			/*
			$this->Validator->isTRUE(	'pay_period',
										FALSE,
										TTi18n::gettext('Pay period missing or employee is not assigned to a pay period schedule') );
			*/
			//}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDeleted() == TRUE ) {
			//Delete (for real) any already deleted rows in hopes to prevent a
			//unique index conflict across user_id, date_stamp, deleted
			$udlf = TTnew( 'UserDateListFactory' );
			$udlf->deleteByUserIdAndDateAndDeleted( $this->getUser(), $this->getDateStamp(), TRUE );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		//Debug::Text('Post Save... Deleted: '. (int)$this->getDeleted(), __FILE__, __LINE__, __METHOD__, 10);

		//Delete punch control/schedules assigned to this.
		if ( $this->getDeleted() == TRUE ) {

			//Delete schedules assigned to this user date.
			//Turn off any re-calc's
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getByUserDateID( $this->getId() );
			if ( $slf->getRecordCount() > 0 ) {
				foreach( $slf as $schedule_obj ) {
					$schedule_obj->setDeleted(TRUE);
					$schedule_obj->Save();
				}
			}

			$pclf = TTnew( 'PunchControlListFactory' );
			$pclf->getByUserDateID( $this->getId() );
			if ( $pclf->getRecordCount() > 0 ) {
				foreach( $pclf as $pc_obj ) {
					$pc_obj->setDeleted(TRUE);
					$pc_obj->Save();
				}
			}

			//Delete exceptions
			$elf = TTnew( 'ExceptionListFactory' );
			$elf->getByUserDateID( $this->getId() );
			if ( $elf->getRecordCount() > 0 ) {
				foreach( $elf as $e_obj ) {
					$e_obj->setDeleted(TRUE);
					$e_obj->Save();
				}
			}

			//Delete user_date_total rows too
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByUserDateID( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				foreach( $udtlf as $udt_obj ) {
					$udt_obj->setDeleted(TRUE);
					$udt_obj->Save();
				}
			}
		}

		return TRUE;
	}
}
?>
