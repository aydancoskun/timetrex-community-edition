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
 * @package Modules\PayrollRemittanceAgencyEvent
 */
class PayrollRemittanceAgencyEventFactory extends Factory {
	protected $table = 'payroll_remittance_agency_event';
	protected $pk_sequence_name = 'payroll_remittance_agency_event_id_seq'; //PK Sequence name

	protected $agency_obj = null;
	protected $reminder_user_obj = null;

	/**
	 * @param bool $name
	 * @param null $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name = false, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Enabled - Self Service' ),
						15 => TTi18n::gettext( 'Enabled - Full Service' ),
						20 => TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'type':
				$retval = [];

				$praf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $praf */
				$praf->getById( $params['payroll_remittance_agency_id'] );
				if ( $praf->getRecordCount() > 0 ) {
					$pra_obj = $praf->getCurrent();
					$agency_id = $pra_obj->getAgency();
					Debug::Text( 'Agency ID: ' . $agency_id, __FILE__, __LINE__, __METHOD__, 10 );

					$remittance_agency_event_data = include( 'PayrollRemittanceAgencyEventFactory.data.php' ); //Contains large array of necessary data.

					if ( isset( $remittance_agency_event_data[$agency_id] ) ) {
						$events_arr = $remittance_agency_event_data[$agency_id];
						foreach ( $events_arr as $type_id => $event_data ) {
							$retval[$type_id] = $event_data['form_name'];
						}
						unset( $remittance_agency_event_data, $events_arr, $type_id, $event_data );
					} else {
						$retval = [
								'PAYMENT+REPORT' => TTi18n::gettext( 'Payment & Report' ),
								'PAYMENT'        => TTi18n::gettext( 'Payment' ),
								'REPORT'         => TTi18n::gettext( 'Report' ),
								'AUDIT'          => TTi18n::gettext( 'Audit (Internal)' ),
						];
					}
				}
				break;
			case 'frequency':
				$retval = [
						1000 => TTi18n::gettext( 'Each Pay Period' ),

						2000 => TTi18n::gettext( 'Annually' ),
						2100 => TTi18n::gettext( 'Annual (YTD)' ), //Current year up to todays date with Primary Month and Primary Day of Month specified.
						2200 => TTi18n::gettext( 'Semi-Annually' ), //Twice per Year (Specify: 1st Month/Day and 2nd Month/Day)

						3000 => TTi18n::gettext( 'Quarterly' ),

						//4000 => TTi18n::gettext('Bi-Monthly'), //Every two months, similar to BiWeekly, need to pick the Even/Odd month, and Day of Month.
						4100 => TTi18n::gettext( 'Monthly' ),
						4200 => TTi18n::gettext( 'Semi-Monthly' ), //Pick 1st Day of Month, and 2nd Day of Month. What about a "Gap" days, then split each month based on that? See New Hire reporting.

						//5000 => TTi18n::gettext('Bi-Weekly'),
						5100 => TTi18n::gettext( 'Weekly' ),

						50000 => TTi18n::gettext( 'CA - Accelerated (Threshold 1)' ), //10th and 25th of each month. If transaction date falls between 1-15th of the month, pay by 25th. If it falls between 16th and last day, pay on the 10th of the next month.
						51000 => TTi18n::gettext( 'CA - Accelerated (Threshold 2)' ), //1st - 7th, 8th to 14th, 15th to 21st, 22nd to last day of month. Pay 3rd working day after end of period.

						59000 => TTi18n::gettext( 'US - Quarterly (1-3 Only)' ), //Due the last day of the month following the end of the quarter. (April 30, July 31, and October 31).
						60000 => TTi18n::gettext( 'US - Monthly (15th, 30th on Last MoQ)' ), //MoQ=Month of Quarter, Due the 15th day of the month following the monthly withholding period, except for March, June, September and December; then due the last day of the month following the withholding period.
						60100 => TTi18n::gettext( 'US - Monthly (15th, skip Last MoQ)' ), //MoQ=Month of Quarter, Due 15th day of the following month for the 1st and 2nd months of the quarter. So it excludes January, April, July and October.
						//60200 => TTi18n::gettext('US - Monthly (15th, skip December)'), //Due the 15th of the following month, for January through November. Skip December, which is really the January 15th payment. Used for KY
						61000 => TTi18n::gettext( 'US - Twice (2x) Monthly' ), //January liability is due on February 10th. February through November liabilities for the 1st through the 15th are due on the 25th of the same month. February through November liabilites for the 16th through the end of the month are due on the 10th of the following month. December liabilities for the 1st through the 15th are due on December 26th, and December liabilities for the 16th through the 31 are due on January 31.
						62000 => TTi18n::gettext( 'US - Quarter (4x) Monthly' ), // 1.The first seven days of the calendar month. 2.The 8th to the 15th day of the calendar month. 3.The 16th to the 22nd day of the calendar month. 4.The 23rd day to the end of the calendar month.  As a quarter-monthly filer, you are required to pay at least 90 percent of the actual tax due within three banking day following the end of the quarter-monthly period.
						63000 => TTi18n::gettext( 'US - Eighth (8x) Monthly' ), ///Due within 3 days after the appropriate tax periods. The tax periods end on the 3rd, 7th, 11th, 15th, 19th, 22nd, 25th and the last day of the month.

						64000 => TTi18n::gettext( 'US - Semi-Weekly' ), //If transaction date falls on: Wednesday, Thursday, and/or Friday = Wednesday If it falls on: Saturday, Sunday, Monday, and/or Tuesday = Friday. Essentially 3 business days, if it falls on a holiday its the next business day.

						//X days after -- How do we handle start/end dates? Maybe have the reminder maintenance job somehow fill these in based on hire/termination dates within the X day period?
						//  Simply find all users with a hire/termination date within the last X days (minus reminder time?) then set the dates for this?
						90100 => TTi18n::gettext( 'Upon Hire' ),
						90200 => TTi18n::gettext( 'Upon Termination' ),
						//90300 => TTi18n::gettext('Upon Termination (Pay Period Start)'), //X days after the pay period end date they are terminated in. (ROEs)
						90310 => TTi18n::gettext( 'Upon Termination (Pay Period End)' ), //X days after the pay period end date they are terminated in. (ROEs)
						//90320 => TTi18n::gettext('Upon Termination (Pay Period Transaction)'), //X days after the pay period end date they are terminated in. (ROEs)

						//99900 => TTi18n::gettext('As Needed'), //Never any start/end/due dates. No reminders.
				];
				break;
			case 'week_interval':
				$retval = [
						1 => TTi18n::gettext( '1st' ),
						2 => TTi18n::gettext( '2nd' ),
						3 => TTi18n::gettext( '3rd' ),
						4 => TTi18n::gettext( '4th' ),
						5 => TTi18n::gettext( '5th' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-payroll_remittance_agency_name' => TTi18n::gettext( 'Payroll Remittance Agency' ),
						'-1010-status'                         => TTi18n::gettext( 'Status' ),
						'-1025-type'                           => TTi18n::gettext( 'Type' ),
						'-1350-frequency'                      => TTi18n::gettext( 'Frequency' ),
						//'-1351-quarter_month'                  => TTi18n::getText( 'Quarter Month' ),
						//'-1360-week'                           => TTi18n::gettext( 'Week' ),
						//'-1365-primary_month'                  => TTi18n::gettext( 'Primary Month' ),
						//'-1370-primary_day_of_month'           => TTi18n::gettext( 'Primary Day Of Month' ),
						//'-1365-secondary_month'                => TTi18n::gettext( 'Secondary Month' ),
						//'-1370-secondary_day_of_month'         => TTi18n::gettext( 'Secondary Day Of Month' ),
						//'-1380-day_of_week'                    => TTi18n::gettext( 'Day Of Week' ),
						'-1385-due_date_delay_days'            => TTi18n::gettext( 'Due Date Delay Days' ),
						'-1390-effective_date'                 => TTi18n::gettext( 'Effective Date' ),
						'-1400-reminder_days'                  => TTi18n::gettext( 'Reminder Days' ),
						'-1410-note'                           => TTi18n::gettext( 'Notes' ),
						//'-1415-user_report_data_id'            => TTi18n::gettext( 'Saved Report' ),

						'-1420-due_date'           => TTi18n::gettext( 'Due Date' ),
						'-1430-next_reminder_date' => TTi18n::gettext( 'Next Reminder Date' ),
						'-1440-reminder_user_id'   => TTi18n::gettext( 'Send Reminder To' ),

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
					//'payroll_remittance_agency_name',
					'status',
					'type',
					'frequency',
					'due_date',
					'next_reminder_date',
				];
				break;

//			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
//				$retval = array(
//						'country',
//						'province',
//				);
//				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                           => 'ID',
				'payroll_remittance_agency_id' => 'PayrollRemittanceAgencyId',
				'pay_period_schedule_id'       => 'PayPeriodSchedule',

				'status_id' => 'Status',
				'status'    => false,

				'type_id' => 'Type',
				'type'    => false,

				'reminder_user_id' => 'ReminderUser',
				'frequency_id'     => 'Frequency',
				'frequency'        => 'FrequencyName',

				'quarter_month'          => 'QuarterMonth',
				'week'                   => 'Week',
				'primary_month'          => 'PrimaryMonth',
				'secondary_month'        => 'SecondaryMonth',
				'primary_day_of_month'   => 'PrimaryDayOfMonth',
				'secondary_day_of_month' => 'SecondaryDayOfMonth',
				'day_of_week'            => 'DayOfWeek',
				'due_date_delay_days'    => 'DueDateDelayDays',
				'effective_date'         => 'EffectiveDate',
				'reminder_days'          => 'ReminderDays',

				'note'                => 'Note',
				'user_report_data_id' => 'UserReportData',

				'start_date'    => 'StartDate',
				'end_date'      => 'EndDate',
				'due_date'      => 'DueDate',
				'last_due_date' => 'LastDueDate',

				'next_reminder_date' => 'NextReminderDate',
				'last_reminder_date' => 'NextReminderDate',

				'legal_entity_legal_name'        => false,
				'payroll_remittance_agency_name' => false,

				'enable_recalculate_dates' => false,
				'recalculate_date'         => false,

				'in_use'         => false,
				'in_time_period' => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		$pra_obj = $this->getPayrollRemittanceAgencyObject();
		if ( is_object( $pra_obj ) ) {
			$le_obj = $pra_obj->getLegalEntityObject();
			if ( is_object( $le_obj ) ) {
				return $le_obj->getCompanyObject();
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getPayrollRemittanceAgencyObject() {
		return $this->getGenericObject( 'PayrollRemittanceAgencyListFactory', $this->getPayrollRemittanceAgencyId(), 'agency_obj' );
	}

	/**
	 * @return bool
	 */
	function getReminderUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getReminderUser(), 'reminder_user_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayrollRemittanceAgencyId() {
		return $this->getGenericDataValue( 'payroll_remittance_agency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayrollRemittanceAgencyId( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'payroll_remittance_agency_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return string
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFrequencyName() {
		$value = $this->getFrequency();
		if ( $value !== false ) {
			$frequencies = $this->_getFactoryOptions( 'frequency' );
			foreach ( $frequencies as $n => $f ) {
				if ( $n == (int)$value ) {
					return $f;
				}
			}
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getFrequency() {
		return $this->getGenericDataValue( 'frequency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFrequency( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'frequency_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPrimaryMonth() {
		return $this->getGenericDataValue( 'primary_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimaryMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'primary_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondaryMonth() {
		return $this->getGenericDataValue( 'secondary_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondaryMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'secondary_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getQuarterMonth() {
		return $this->getGenericDataValue( 'quarter_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setQuarterMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'quarter_month', $value );
	}


	/**
	 * @param $val
	 * @return int
	 */
	function convertLastDayOfMonth( $val ) {
		if ( $val == -1 ) {
			return 31;
		}

		return $val;
	}

	/**
	 * @return bool|mixed
	 */
	function getPrimaryDayOfMonth() {
		return $this->getGenericDataValue( 'primary_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimaryDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'primary_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondaryDayOfMonth() {
		return $this->getGenericDataValue( 'secondary_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondaryDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'secondary_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDayOfWeek() {
		return $this->getGenericDataValue( 'day_of_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDayOfWeek( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'day_of_week', $value );
	}

	/*
	 * @return bool|mixed
	 */
	/**
	 * @return bool|mixed
	 */
	function getDueDateDelayDays() {
		return $this->getGenericDataValue( 'due_date_delay_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDueDateDelayDays( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'due_date_delay_days', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getEffectiveDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'effective_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEffectiveDate( $value ) {
		if ( $value != '' ) { //Allow blank/NULL values to be saved.
			$value = TTDate::getBeginDayEpoch( trim( $value ) );
		}

		Debug::Text( 'Effective Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'effective_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getReminderUser() {
		return $this->getGenericDataValue( 'reminder_user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReminderUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'reminder_user_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getReminderDays() {
		$value = $this->getGenericDataValue( 'reminder_days' );
		if ( $value !== false ) {
			return Misc::removeTrailingZeros( round( TTDate::getDays( (int)$value ), 3 ), 0 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReminderDays( $value ) {
		$value = (float)$value; // Do not cast to INT, need to support partial days.

		return $this->setGenericDataValue( 'reminder_days', ( $value * 86400 ) );//Convert to seconds to support partial days. Do not cast to INT!
	}

	/**
	 * @return bool|string
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		return $this->setGenericDataValue( 'note', trim( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserReportData() {
		return $this->getGenericDataValue( 'user_report_data_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserReportData( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_report_data_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getLastDueDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'last_due_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastDueDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'last_due_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDueDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'due_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDueDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'due_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getNextReminderDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'next_reminder_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNextReminderDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'next_reminder_date', $value );
	}


	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getLastReminderDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'last_reminder_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastReminderDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'last_reminder_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getEnableRecalculateDates() {
		return $this->getGenericTempDataValue( 'enable_recalculate_dates' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableRecalculateDates( $value ) {
		return $this->setGenericTempDataValue( 'enable_recalculate_dates', (bool)$value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getRecalculateDate( $raw = false ) {
		$value = $this->getGenericTempDataValue( 'recalculate_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRecalculateDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericTempDataValue( 'recalculate_date', $value );
	}

	/**
	 * @return array|bool
	 */
	function getPayPeriodSchedule() {
		$agency_obj = $this->getPayrollRemittanceAgencyObject();
		if ( is_object( $agency_obj ) ) {
			$company_obj = $agency_obj->getCompanyObject();
			if ( is_object( $company_obj ) ) {
				return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_obj->getId(), 5010, $this->getID() );
			}
		}

		return false;
	}

	/**
	 * @param array $ids UUID
	 * @return bool
	 */
	function setPayPeriodSchedule( $ids ) {
		Debug::Arr( $ids, 'Setting Pay Period Schedule IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
		$agency_obj = $this->getPayrollRemittanceAgencyObject();
		if ( is_object( $agency_obj ) ) {
			$company_obj = $agency_obj->getCompanyObject();
			if ( is_object( $company_obj ) ) {
				return CompanyGenericMapFactory::setMapIDs( $company_obj->getId(), 5010, $this->getID(), (array)$ids );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getInTimePeriod() {
		$current_epoch = time();

		//if ( $current_epoch > $this->getStartDate() AND $current_epoch < $this->getEndDate() ) {
		if ( $current_epoch < $this->getEndDate() ) { //per Pay Period frequencies should show a few days in advance, but not be highlighted (black font), so here we just need to check if we haven't passed the end date or not, and ignore the start date.
			return true;
		}

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Remittance agency
		$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
		$this->Validator->isResultSetWithRows( 'payroll_remittance_agency_id',
											   $pralf->getByID( $this->getPayrollRemittanceAgencyId() ),
											   TTi18n::gettext( 'Remittance agency is invalid' )
		);

		if ( $this->getDeleted() != true ) {
			// Status
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);

			// Type
			$type_params = [
					'payroll_remittance_agency_id' => $this->getPayrollRemittanceAgencyObject()->getId(),
			];
			$type_array = $this->getOptions( 'type', $type_params );
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $type_array
			);
//

			// Frequency
			$this->Validator->inArrayKey( 'frequency_id',
										  $this->getFrequency(),
										  TTi18n::gettext( 'Incorrect frequency' ),
										  $this->getOptions( 'frequency' )
			);

			if ( in_array( $this->getFrequency(), [ 2000 ] ) ) { //20=Annual
				// Month
				if ( $this->getPrimaryMonth() !== false ) {
					$this->Validator->inArrayKey( 'primary_month',
												  $this->getPrimaryMonth(),
												  TTi18n::gettext( 'Incorrect month' ),
												  TTDate::getMonthOfYearArray()
					);
				}
			}

			if ( in_array( $this->getFrequency(), [ 3000 ] ) ) { //30=Quarterly
				// Quarter month
				if ( $this->getQuarterMonth() !== false ) {
					$this->Validator->isGreaterThan( 'quarter_month',
													 $this->getQuarterMonth(),
													 TTi18n::gettext( 'Incorrect quarter month' ),
													 1
					);
					if ( $this->Validator->isError( 'quarter_month' ) == false ) {
						$this->Validator->isLessThan( 'quarter_month',
													  $this->getQuarterMonth(),
													  TTi18n::gettext( 'Incorrect quarter month' ),
													  3
						);
					}
				}
			}

			if ( in_array( $this->getFrequency(), [ 2000, 3000, 4100 ] ) ) { //20=Annual, 30=Quarterly, 40=Monthly
				// Day of month
				if ( $this->getPrimaryDayOfMonth() !== false ) {
					$this->Validator->inArrayKey( 'primary_day_of_month',
												  $this->getPrimaryDayOfMonth(),
												  TTi18n::gettext( 'Incorrect day of month' ),
												  TTDate::getDayOfMonthArray( true )
					);
				}
			}
			// Day of week
			if ( in_array( $this->getFrequency(), [ 5100 ] ) ) { //90=Weekly
				if ( $this->getDayOfWeek() !== false ) {
					$this->Validator->inArrayKey( 'day_of_week',
												  $this->getDayOfWeek(),
												  TTi18n::gettext( 'Incorrect day of week' ),
												  TTDate::getDayOfWeekArray()
					);
				}
			}

			//  Days After Transaction Date
			if ( in_array( $this->getFrequency(), [ 1000 ] ) ) { //10=Pay Period
				if ( $this->getDueDateDelayDays() !== false ) {
					$this->Validator->isTrue( 'after_transaction_date',
											  is_numeric( $this->getDueDateDelayDays() ),
											  TTi18n::gettext( 'Incorrect Days After Transaction Date' )
					);
				}
			}

			// Effective Date
			if ( $this->getEffectiveDate() !== false ) {
				$this->Validator->isDate( 'effective_date',
										  $this->getEffectiveDate(),
										  TTi18n::gettext( 'Incorrect Effective Date' )
				);
			}

			// Reminder Employee - Allow this to be NONE in cases where creating it during a fresh install when a user may not even exist yet.
			if ( $this->getReminderUser() != '' && $this->getReminderUser() != TTUUID::getZeroId() ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'reminder_user_id',
													   $ulf->getByID( $this->getReminderUser() ),
													   TTi18n::gettext( 'Invalid Reminder Employee' )
				);
			}
		}

		// Reminder days
		$this->Validator->isNumeric( 'reminder_days',
									 $this->getReminderDays(),
									 TTi18n::gettext( 'Incorrect reminder days' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.

			if ( $this->getType() == false ) {
				$this->Validator->isTrue( 'type_id',
										  false,
										  TTi18n::gettext( 'Please specify type' ) );
			}

			if ( $this->getFrequency() == false ) {
				$this->Validator->isTrue( 'frequency_id',
										  false,
										  TTi18n::gettext( 'Please specify frequency' ) );
			}
		}

		if ( $this->getDeleted() != true ) {
			if ( $this->getStatus() == 15 ) { //15=Enabled - Full Service
				//Make sure autopay OR autofile is enabled first.
				$event_data = $this->getEventData();
				if ( is_array( $event_data ) && isset( $event_data['flags'] ) && ( $event_data['flags']['auto_file'] == false && $event_data['flags']['auto_pay'] == false ) ) {
					$this->Validator->isTrue( 'status_id',
											  false,
											  TTi18n::gettext( 'Agency or Event Type is not eligible for Full Service yet, try again later' ) );
				}

				//If the agency/event is eligible for auto_pay, then make sure its linked to a source account of the proper type.
				if ( is_array( $event_data ) && isset( $event_data['flags'] ) && ( $event_data['flags']['auto_pay'] == true ) ) {
					//Make sure the RemittanceAgency is linked to a source account, which is required to at least get a PaymentServices API username/password
					if ( is_object( $this->getPayrollRemittanceAgencyObject() )
							&& !is_object( $this->getPayrollRemittanceAgencyObject()->getRemittanceSourceAccountObject() ) ) {
						$this->Validator->isTrue( 'status_id',
												  false,
												  TTi18n::gettext( 'Remittance Agency must have Source Account specified' ) );
					}

					//Make sure the source account is of TimeTrex Payment Services format.
					if ( is_object( $this->getPayrollRemittanceAgencyObject() )
							&& is_object( $this->getPayrollRemittanceAgencyObject()->getRemittanceSourceAccountObject() )
							&& $this->getPayrollRemittanceAgencyObject()->getRemittanceSourceAccountObject()->getDataFormat() != 5 ) { //5=TimeTrex Payment Services
						$this->Validator->isTrue( 'status_id',
												  false,
												  TTi18n::gettext( 'Remittance Agency Source Account Format is incorrect' ) );
					}
				}

				//Make sure the remittance agency has a contact specified.
				if ( is_object( $this->getPayrollRemittanceAgencyObject() )
						&& ( $this->getPayrollRemittanceAgencyObject()->getContactUser() == false || $this->getPayrollRemittanceAgencyObject()->getContactUser() == TTUUID::getZeroID() ) ) { //5=TimeTrex Payment Services
					$this->Validator->isTrue( 'status_id',
											  false,
											  TTi18n::gettext( 'Remittance Agency must have a contact person specified' ) );
				}

				if ( is_object( $this->getPayrollRemittanceAgencyObject() ) && is_object( $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject() ) ) {
					$this->Validator->isTrue( 'status_id',
											  $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject()->checkPaymentServicesCredentials(),
											  TTi18n::gettext( 'Payment Services User Name or API Key is incorrect, or service not activated' ) );
				}
				//Confirm the Agency/Event is valid, then setup authorization record if its not already.

			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDueDateDelayDays() == '' ) {
			$this->setDueDateDelayDays( 0 );
		}

		//The recalculate dates checkbox must be checked to recalculate dates.
		if ( $this->isNew( true ) || $this->getEnableRecalculateDates() == true ) {
			Debug::Text( 'Recalculating dates...', __FILE__, __LINE__, __METHOD__, 10 );
			$due_date_array = $this->calculateNextDate(); //Don't pass any arguments in so it matches what the UI shows based on APIPayrollRemittanceAgencyEvent->calculateNextRunDate()
			if ( isset( $due_date_array['start_date'] ) && isset( $due_date_array['end_date'] ) && isset( $due_date_array['due_date'] ) ) {
				$this->setLastDueDate( $this->getDueDate() );

				$this->setStartDate( $due_date_array['start_date'] );
				$this->setEndDate( $due_date_array['end_date'] );
				$this->setDueDate( $due_date_array['due_date'] );

				$this->setNextReminderDate( $this->calculateNextReminderDate( $due_date_array['due_date'] ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getStatus() == 15 ) { //15=Full Service
			//Send data to TimeTrex Payment Services.
			$le_obj = $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject();
			if ( PRODUCTION == true && is_object( $le_obj ) && $le_obj->getPaymentServicesStatus() == 10 && $le_obj->getPaymentServicesUserName() != '' && $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
				try {
					$tt_ps_api = $le_obj->getPaymentServicesAPIObject();
					$retval = $tt_ps_api->setAgencyAuthorization( $tt_ps_api->convertRemittanceAgencyEventObjectToAgencyAuthorizationArray( $this ) );
					if ( $retval === false ) {
						Debug::Text( 'ERROR! Unable to upload remittance agency event data... (a)', __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					}
				} catch ( Exception $e ) {
					Debug::Text( 'ERROR! Unable to upload remittance agency event data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'ERROR! Payment Services not enabled in legal entity!', __FILE__, __LINE__, __METHOD__, 10 );
			}
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
						case 'start_date':
						case 'end_date':
						case 'effective_date':
						case 'due_date':
						case 'last_due_date':
						case 'next_reminder_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'last_reminder_date': //Skip this as should only be set internally.
							break;
						case 'enable_recalculate_dates':
							$this->setEnableRecalculateDates( $data[$key] );
							break;
						case 'recalculate_date':
							$this->setRecalculateDate( TTDate::parseDateTime( $data[$key] ) );
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
						case 'legal_entity_legal_name':
						case 'payroll_remittance_agency_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable, [ 'payroll_remittance_agency_id' => $this->getPayrollRemittanceAgencyId() ] ) );
							}
							break;
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'effective_date':
						case 'start_date':
						case 'end_date':
						case 'due_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'last_reminder_date':
						case 'next_reminder_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							}
							break;
						case 'in_time_period':
							$data[$variable] = $this->getInTimePeriod();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Payroll Remittance Agency Event' ), null, $this->getTable(), $this );
	}

	/**
	 * Gets next event date based on the frequency type & other frequency signals provided.
	 * @param null $last_due_date
	 * @param null $current_epoch
	 * @return array|bool
	 */
	function calculateNextDate( $last_due_date = null, $current_epoch = null ) {
		if ( $current_epoch == '' ) {
			$current_epoch = time();
		}

		if ( $last_due_date == '' && $this->getRecalculateDate() != '' ) {
			Debug::Text( '  reCalculate Date specified, using it instead of Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $this->getRecalculateDate() ), __FILE__, __LINE__, __METHOD__, 10 );
			$last_due_date = $this->getRecalculateDate();
		} else {
			if ( $last_due_date == '' ) {
				$last_due_date = $this->getLastDueDate();
			}
			if ( $last_due_date == '' || $this->getEffectiveDate() > $last_due_date ) {
				$last_due_date = $this->getEffectiveDate();
			}
		}

		if ( $last_due_date == '' ) {
			$last_due_date = $current_epoch;
		}

		//Ensure mid-day epoch
		$last_due_date = TTDate::getMiddleDayEpoch( $last_due_date );
		$retval = false;
		$frequency_type_id = $this->getFrequency();
		Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ) . ' (' . TTDate::getDate( 'DATE+TIME', $this->getLastDueDate() ) . ') Frequency: ' . $frequency_type_id, __FILE__, __LINE__, __METHOD__, 10 );

		switch ( $frequency_type_id ) {
			case 1000: //each pay period
				//For pay periods, we need to start from the last end date if there is one specified, so there is never any gaps between the periods when using the tax wizard to complete an event and advance the dates.
				if ( $this->getEndDate() != '' && empty( $this->getRecalculateDate() ) ) { //Only do this if we are not trying to recalculate the dates to some other date.
					$last_due_date = TTDate::getMiddleDayEpoch( $this->getEndDate() );
					Debug::Text( '  Using End Date as Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $this->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				}
				$last_due_date -= 86400;

				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				if ( is_object( $this->getPayrollRemittanceAgencyObject() ) && is_object( $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject() ) ) {
					$le_obj = $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject();
					if ( is_object( $le_obj ) ) {
						//Employees must be assigned to Tax/Deduction records that are associated with this remittance agency event as well, so we can determine which pay periods to use.
						$pplf->getByCompanyIdAndRemittanceAgencyIdAndTransactionDateAndPayPeriodSchedule( $le_obj->getCompany(), $this->getPayrollRemittanceAgencyId(), $last_due_date, $this->getPayPeriodSchedule() );
						if ( $pplf->getRecordCount() > 0 ) {
							Debug::Text( 'Looping over Pay Periods: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
							foreach ( $pplf as $pp_obj ) {
								//#1187 - Pay period based event dates are all based on the relevant pay period transaction dates because that's what the subsequent reports (ie: Pay Stub Summary ) will need to see
								//  However we need to handle cases where terminated employees may be paid earlier in the pay period. Which can be handled two ways:
								//     1. Remit to the agency immediately after an early payroll run.
								//     2. Wait until the end of the pay period and remit everyone together.
								//  We will opt of #2 by default. So the start date must be the day after the previous transaction date (so we don't overlap and double report information)
								//  and end_date is the end day epoch of the current transaction date.
								//
								//The due date is the transaction date day plus the current event's due date delay days
								if ( $pp_obj->getStartDate() <= $last_due_date && $pp_obj->getEndDate() >= $last_due_date ) {
									Debug::Text( 'Found: Pay Period: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ) . ' Due Date Delay: ' . $this->getDueDateDelayDays(), __FILE__, __LINE__, __METHOD__, 10 );
									$retval = [
											'start_date'    => TTDate::getBeginDayEpoch( TTDate::incrementDate( $last_due_date, 2, 'day' ) ), //$last_due_date is -86400, and we need the day after it, so add 2.
											'end_date'      => TTDate::getEndDayEpoch( $pp_obj->getTransactionDate() ),
											'due_date'      => TTDate::incrementDate( $pp_obj->getTransactionDate(), (int)$this->getDueDateDelayDays(), 'day' ),
											'pay_period_id' => $pp_obj->getId(),
									];

									break;
								} else {
									Debug::Text( 'Skipping: Pay Period: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						} else {
							Debug::Text( '  No pay periods found!', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}
				unset( $pplf, $pp_obj, $le_obj );
				break;
			case 2000: //Annually
				$day_of_month = $this->getPrimaryDayOfMonth();
				$days_in_month = TTDate::getDaysInMonth( mktime( 0, 0, 0, $this->getPrimaryMonth(), 1, date( 'Y', $last_due_date ) ) );

				if ( $day_of_month > $days_in_month ) {
					$day_of_month = $days_in_month;
				}

				$due_date = mktime( 0, 0, 0, $this->getPrimaryMonth(), $day_of_month, date( 'Y', $last_due_date ) );
				if ( $last_due_date >= $due_date ) {
					$due_date = TTDate::incrementDate( $due_date, 1, 'year' );
				}

				$due_date_prev_year = TTDate::incrementDate( $due_date, -1, 'year' );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => TTDate::getBeginYearEpoch( $due_date_prev_year ),
						'end_date'   => TTDate::getEndYearEpoch( $due_date_prev_year ),
				];
				break;
			case 2100: //year to date
				//$last_due_date -= 86400; //This prevents a last_due_date on the same day as the previous due_date from pushing it over into the next year.
				$end_date = mktime( 0, 0, 0, $this->getPrimaryMonth(), $this->getPrimaryDayOfMonth(), date( 'Y', ( $last_due_date - 86400 ) ) );

				if ( TTDate::getMiddleDayEpoch( $end_date ) <= TTDate::getMiddleDayEpoch( $last_due_date ) ) {
					$end_date = TTDate::incrementDate( $end_date, 1, 'year' );
				}

				$start_date = mktime( 0, 0, 0, 1, 1, date( 'Y', $end_date ) );
				$due_date = TTDate::incrementDate( $end_date, $this->getDueDateDelayDays(), 'day' );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 2200: //semi-annually
				$primary_date = mktime( 0, 0, 0, $this->getPrimaryMonth(), $this->getPrimaryDayOfMonth(), date( 'Y', $last_due_date ) );
				$secondary_date = mktime( 0, 0, 0, $this->getSecondaryMonth(), $this->getSecondaryDayOfMonth(), date( 'Y', $last_due_date ) );

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $last_due_date <= $primary_date ) {
					//earlier this year
					$start_date = mktime( 0, 0, 0, date( 'm', $secondary_date ), ( date( 'd', $secondary_date ) + 1 ), ( date( 'Y', $last_due_date ) - 1 ) );
					$end_date = $primary_date;
					$due_date = TTDate::incrementDate( $end_date, $this->getDueDateDelayDays(), 'day' );
				} else if ( $last_due_date <= $secondary_date ) {
					//later this year
					$start_date = TTDate::incrementDate( $primary_date, 1, 'day' );
					$end_date = $secondary_date;
					$due_date = TTDate::incrementDate( $end_date, $this->getDueDateDelayDays(), 'day' );
				} else {
					//early next year
					$start_date = TTDate::incrementDate( $secondary_date, 1, 'day' );
					$end_date = TTDate::incrementDate( $primary_date, 1, 'year' );
					$due_date = TTDate::incrementDate( $end_date, $this->getDueDateDelayDays(), 'day' );
				}

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 3000: //Quarterly
				$due_date = TTDate::getDateOfNextQuarter( $last_due_date, $this->getPrimaryDayOfMonth(), $this->getQuarterMonth() );
				$due_date_prev_quarter = TTDate::incrementDate( $due_date, -1, 'quarter' );
				$retval = [
						'due_date'   => $due_date,
						'start_date' => TTDate::getBeginMonthEpoch( TTDate::getBeginQuarterEpoch( $due_date_prev_quarter ) ),
						'end_date'   => TTDate::getEndMonthEpoch( TTDate::getEndQuarterEpoch( $due_date_prev_quarter ) ),
				];
				break;
			case 4100: //Monthly
				//$last_due_date += 86400;
				$last_due_date = TTDate::incrementDate( $last_due_date, 1, 'day' );
				$due_date = TTDate::getDateOfNextDayOfMonth( $last_due_date, false, $this->getPrimaryDayOfMonth() );
				$month_before_due_date = TTDate::incrementDate( $due_date, -1, 'month' );

				$retval = [
						'start_date' => TTDate::getBeginMonthEpoch( $month_before_due_date ),
						'end_date'   => TTDate::getEndMonthEpoch( $month_before_due_date ),
						'due_date'   => $due_date,
				];
				break;
			case 4200: //semi-monthly
				$day_of_month = TTDate::getDayOfMonth( $last_due_date );
				$month = TTDate::getMonth( $last_due_date );

				//catch last day of month.
				$secondary_day_of_month = $this->getSecondaryDayOfMonth();
				if ( $secondary_day_of_month > TTDate::getDaysInMonth( $last_due_date ) ) {
					$secondary_day_of_month = TTDate::getDaysInMonth( $last_due_date );
				}
				$primary_day_of_month = $this->getPrimaryDayOfMonth();
				if ( $primary_day_of_month > TTDate::getDaysInMonth( $last_due_date ) ) {
					$primary_day_of_month = TTDate::getDaysInMonth( $last_due_date );
				}

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $day_of_month <= $primary_day_of_month ) {
					//early this month
					$secondary_day_of_last_month = $this->getSecondaryDayOfMonth();
					if ( $secondary_day_of_last_month > TTDate::getDaysInMonth( TTDate::incrementDate( TTDate::incrementDate( $last_due_date, 1, 'day' ), -1, 'month' ) ) ) {
						$secondary_day_of_last_month = TTDate::getDaysInMonth( TTDate::incrementDate( TTDate::incrementDate( $last_due_date, 1, 'day' ), -1, 'month' ) );
					}

					$start_date = mktime( 0, 0, 0, ( $month - 1 ), ( $secondary_day_of_last_month + 1 ), date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, $primary_day_of_month, date( 'Y', $last_due_date ) );
					$due_date = mktime( 0, 0, 0, $month, ( $primary_day_of_month + $this->getDueDateDelayDays() ), date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= $secondary_day_of_month ) {
					//late this month
					$start_date = mktime( 0, 0, 0, $month, ( $primary_day_of_month + 1 ), date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, $secondary_day_of_month, date( 'Y', $last_due_date ) );
					$due_date = mktime( 0, 0, 0, $month, ( $secondary_day_of_month + $this->getDueDateDelayDays() ), date( 'Y', $last_due_date ) );
				} else {
					//early next month
					$primary_day_of_next_month = $this->getPrimaryDayOfMonth();
					if ( $primary_day_of_next_month > TTDate::getDaysInMonth( TTDate::getDaysInMonth( TTDate::incrementDate( $last_due_date, -1, 'month' ) ) ) ) {
						$primary_day_of_next_month = TTDate::getDaysInMonth( TTDate::getDaysInMonth( TTDate::incrementDate( $last_due_date, -1, 'month' ) ) );
					}
					$start_date = mktime( 0, 0, 0, $month, ( $secondary_day_of_month + 1 ), date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, ( $month + 1 ), $primary_day_of_next_month, date( 'Y', $last_due_date ) );
					$due_date = mktime( 0, 0, 0, ( $month + 1 ), ( $primary_day_of_next_month + $this->getDueDateDelayDays() ), date( 'Y', $last_due_date ) );
				}

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
//			case 5000: //Bi-Weekly
//				//day of week
//				$last_due_date -= 86400;
//				$due_date = TTDate::getDateOfNextDayOfWeek( $last_due_date, TTDate::getBeginWeekEpoch( $last_due_date, $this->getDayOfWeek() ) );
//
//				$effective_week_num = TTDate::getWeekDifference(TTDate::getBeginYearEpoch($this->getEffectiveDate()), $this->getEffectiveDate());
//				$increment_amount = -2;
//				if( ($increment_amount % 2 == 0 AND $effective_week_num %2 != 0 ) OR ($increment_amount % 2 != 0 AND $effective_week_num %2 == 0 ) ) {
//					$increment_amount = -1;
//				}
//
//				$due_date_prev_bw = TTDate::incrementDate($due_date, $increment_amount, 'week');
//
//				$retval = array(
//						'due_date' => $due_date,
//						'start_date' => TTDate::getBeginWeekEpoch($due_date_prev_bw),
//						'end_date' => TTDate::incrementDate(TTDate::getBeginWeekEpoch($due_date_prev_bw), 2, 'week'),
//				);
//				break;
			case 5100: //Weekly
				//$last_due_date += 86400;
				$last_due_date = TTDate::incrementDate( $last_due_date, 1, 'day' );
				$due_date = TTDate::getDateOfNextDayOfWeek( $last_due_date, TTDate::getBeginWeekEpoch( $last_due_date, $this->getDayOfWeek() ) );
				$due_date_prev_week = TTDate::incrementDate( $due_date, -1, 'week' );
				$retval = [
						'due_date'   => $due_date,
						'start_date' => TTDate::getBeginWeekEpoch( $due_date_prev_week ),
						'end_date'   => TTDate::getEndWeekEpoch( $due_date_prev_week ),
				];
				break;
			case 50000: //CA - Accelerated threshold 1
				$tmp_praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $tmp_praef */
				$tmp_praef->setFrequency( 4200 );
				$tmp_praef->setPrimaryDayOfMonth( 15 );
				$tmp_praef->setSecondaryDayOfMonth( 31 );
				$tmp_praef->setDueDateDelayDays( 10 );
				$tmp_praef->setPayrollRemittanceAgencyId( $this->getPayrollRemittanceAgencyId() );

				return $tmp_praef->calculateNextDate( $last_due_date );
				break;
			case 51000: //CA - Accelerated threshold 2
				//1st - 7th, 8th to 14th, 15th to 21st, 22nd to last day of month. Pay 3rd *working day* after end of period.
				$day_of_month = TTDate::getDayOfMonth( $last_due_date );
				$month = TTDate::getMonth( $last_due_date );

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $day_of_month <= 7 ) {
					$start_date = mktime( 0, 0, 0, $month, 1, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 7, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 14 ) {
					$start_date = mktime( 0, 0, 0, $month, 8, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 14, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 21 ) {
					$start_date = mktime( 0, 0, 0, $month, 15, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 21, date( 'Y', $last_due_date ) );
				} else {
					$start_date = mktime( 0, 0, 0, $month, 22, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, TTDate::getDaysInMonth( $start_date ), date( 'Y', $last_due_date ) );
				}
				$due_date = TTDate::incrementDate( $end_date, 1, 'day' ); //3rd working day *after* the last day of the period, so start us on the day after the end date.

				$tmp_holiday_policy_dates = $this->getRecurringHolidayDates( $end_date );

				$i = 1;
				$x = 0;
				while ( $i < 3 && $x < 100 ) { //Loop at least 3x for 3 working days. If its not a working day we have to continue looping.
					$tmp_due_date = TTDate::getNearestWeekDay( $due_date, 2, $tmp_holiday_policy_dates );
					if ( $tmp_due_date == $due_date ) {
						$due_date = TTDate::incrementDate( $due_date, 1, 'day' );
						$i++; //Only increment the counter when the date itself changes.
					} else {
						$due_date = $tmp_due_date;
					}

					$x++; //Prevent infinite loops just in case.
				}
				unset( $tmp_due_date, $tmp_holiday_policy_dates );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 59000:// => TTi18n::gettext('US - Quarterly (1-3 Only)'), //Due the last day of the month following the end of the quarter. (April 30, July 31, and October 31).
				if ( TTDate::getMonth( TTDate::getBeginQuarterEpoch( $last_due_date ) ) >= 10 ) {
					//Jump 1 more quarter into the future if q4.
					$last_due_date = TTDate::incrementDate( $last_due_date, 1, 'quarter' );
				}
				$due_date = TTDate::getEndMonthEpoch( TTDate::getDateOfNextQuarter( $last_due_date, 1, 1 ) );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => TTDate::getBeginQuarterEpoch( $last_due_date ),
						'end_date'   => TTDate::getEndQuarterEpoch( $last_due_date ),
				];
				break;
			case 60000: //US - Monthly (Quarter Exceptions)
				//Due the 15th day of the month following the monthly withholding period, except for March, June, September and December; then due the last day of the month following the withholding period.
				$month = date( 'm', $last_due_date );
				$day = 15;
				$exception_months = [ 3, 6, 9, 12 ]; //March, June, September and December

				if ( in_array( $month, $exception_months ) ) {
					$day = TTDate::getDaysInMonth( mktime( 0, 0, 0, ( $month + 1 ), 1, date( 'Y', $last_due_date ) ) ); //last day of the month following the withholding period.
				}
				$month++;
				$start_date = TTDate::getBeginMonthEpoch( $last_due_date );
				$end_date = TTDate::getEndMonthEpoch( $last_due_date );
				$due_date = mktime( 0, 0, 0, $month, $day, date( 'Y', $last_due_date ) );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 60100:// US - Monthly (15th, skip Last Month of Quarter), Due 15th day of the following month for the 1st and 2nd months of the quarter. So it excludes January, April, July and October.//US - Monthly (Quarter Exceptions)
				$month = date( 'm', $last_due_date );
				$day = 15;
				$exception_months = [ 3, 6, 9, 12 ]; //March, June, September and December

				if ( in_array( $month, $exception_months ) ) {
					$month++; //skip to next month for payment
					$last_due_date = TTDate::incrementDate( $last_due_date, 1, 'month' );
				}
				$month++;

				if ( $month > 12 ) {
					$month -= 12;
				}

				$start_date = TTDate::getBeginMonthEpoch( $last_due_date );
				$end_date = TTDate::getEndMonthEpoch( $last_due_date );
				$due_date = mktime( 0, 0, 0, $month, $day, date( 'Y', $last_due_date ) );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 61000: //twice-monthly
				//January liability is due on February 10th. February through November liabilities for the 1st through the 15th are due on the 25th of the same month. February through November liabilites for the 16th through the end of the month are due on the 10th of the following month. December liabilities for the 1st through the 15th are due on December 26th, and December liabilities for the 16th through the 31 are due on January 31.
				$day_of_month = TTDate::getDayOfMonth( $last_due_date );
				$month = TTDate::getMonth( $last_due_date );
				$year = TTDate::getYear( $last_due_date );

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $month == 1 ) {
					$start_date = mktime( 0, 0, 0, 1, 1, $year );
					$end_date = mktime( 0, 0, 0, 1, TTDate::getDaysInMonth( $start_date ), $year );
					$due_date = mktime( 0, 0, 0, 2, 10, $year );
				} else if ( $month >= 2 && $month <= 11 ) {
					if ( $day_of_month <= 15 ) {
						$start_date = mktime( 0, 0, 0, $month, 1, $year );
						$end_date = mktime( 0, 0, 0, $month, 15, $year );
						$due_date = mktime( 0, 0, 0, $month, 25, $year );
					} else {
						$start_date = mktime( 0, 0, 0, $month, 16, date( 'Y', $last_due_date ) );
						$end_date = mktime( 0, 0, 0, $month, TTDate::getDaysInMonth( $start_date ), date( 'Y', $start_date ) );
						$due_date = mktime( 0, 0, 0, ( $month + 1 ), 10, date( 'Y', $last_due_date ) );
					}
				} else { //December
					if ( $day_of_month <= 15 ) {
						$start_date = mktime( 0, 0, 0, 12, 1, $year );
						$end_date = mktime( 0, 0, 0, 12, 15, $year );
						$due_date = mktime( 0, 0, 0, 12, 26, $year );
					} else {
						$start_date = mktime( 0, 0, 0, 12, 16, date( 'Y', $last_due_date ) );
						$end_date = mktime( 0, 0, 0, 12, TTDate::getDaysInMonth( $start_date ), date( 'Y', $start_date ) );
						$due_date = mktime( 0, 0, 0, 1, 31, ( $year + 1 ) );
					}
				}

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 62000: //quarter-monthly (1-7,8-15,16-22,23-end)
				//As a quarter-monthly filer, you are required to pay at least 90 percent of the actual tax due within 3 banking day following the end of the quarter-monthly period.
				$day_of_month = TTDate::getDayOfMonth( $last_due_date );
				$month = TTDate::getMonth( $last_due_date );

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $day_of_month <= 7 ) {
					$start_date = mktime( 0, 0, 0, $month, 1, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 7, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 15 ) {
					$start_date = mktime( 0, 0, 0, $month, 8, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 15, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 22 ) {
					$start_date = mktime( 0, 0, 0, $month, 16, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 22, date( 'Y', $last_due_date ) );
				} else {
					$start_date = mktime( 0, 0, 0, $month, 23, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, TTDate::getDaysInMonth( $start_date ), date( 'Y', $last_due_date ) );
				}

				$due_date = TTDate::incrementDate( $end_date, 3, 'day' );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 63000: //US - Eighth Monthly
				///Due within 3 days after the appropriate tax periods. The tax periods end on the 3rd, 7th, 11th, 15th, 19th, 22nd, 25th and the last day of the month.
				$day_of_month = TTDate::getDayOfMonth( $last_due_date );
				$month = TTDate::getMonth( $last_due_date );

				Debug::Text( 'Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $day_of_month <= 3 ) {
					$start_date = mktime( 0, 0, 0, $month, 1, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 3, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 7 ) {
					$start_date = mktime( 0, 0, 0, $month, 4, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 7, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 11 ) {
					$start_date = mktime( 0, 0, 0, $month, 8, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 11, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 15 ) {
					$start_date = mktime( 0, 0, 0, $month, 12, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 15, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 19 ) {
					$start_date = mktime( 0, 0, 0, $month, 16, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 19, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 22 ) {
					$start_date = mktime( 0, 0, 0, $month, 20, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 22, date( 'Y', $last_due_date ) );
				} else if ( $day_of_month <= 25 ) {
					$start_date = mktime( 0, 0, 0, $month, 23, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, 25, date( 'Y', $last_due_date ) );
				} else {
					$start_date = mktime( 0, 0, 0, $month, 26, date( 'Y', $last_due_date ) );
					$end_date = mktime( 0, 0, 0, $month, TTDate::getDaysInMonth( $start_date ), date( 'Y', $last_due_date ) );
				}

				$due_date = TTDate::incrementDate( $end_date, 3, 'day' );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 64000: //Semi-Weekly (US)
				//If transaction date falls on: Wednesday, Thursday, and/or Friday = Wednesday If it falls on: Saturday, Sunday, Monday, and/or Tuesday = Friday. Essentially 3 business days, if it falls on a holiday its the next business day.
				$temp_dow = TTDate::getDayOfWeek( $last_due_date );
				if ( $temp_dow >= 3 && $temp_dow <= 5 ) {
					$due_date_day_of_week = 3;
					$start_date_week_day = 3;
					$end_date_week_day = 5;
				} else {
					$due_date_day_of_week = 5;
					$start_date_week_day = 6;
					$end_date_week_day = 2;
				}

				$day_of_week_epoch = TTDate::getBeginWeekEpoch( $last_due_date, $due_date_day_of_week );
				$next_day_of_week_epoch = TTDate::getDateOfNextDayOfWeek( $last_due_date, $day_of_week_epoch );//returns today if the day of week is equal...
				$due_date = TTDate::getMiddleDayEpoch( $next_day_of_week_epoch );
				if ( $due_date == TTDate::getMiddleDayEpoch( $last_due_date ) ) {
					$due_date = TTDate::incrementDate( $due_date, 1, 'week' );
				}

				$start_date_week_day_epoch = TTDate::getBeginWeekEpoch( $last_due_date, $start_date_week_day );
				$next_start_date_week_day_epoch = TTDate::getDateOfNextDayOfWeek( $last_due_date, $start_date_week_day_epoch ); //returns today if the day of week is equal...
				$start_date = TTDate::getMiddleDayEpoch( $next_start_date_week_day_epoch );
				if ( $start_date != TTDate::getMiddleDayEpoch( $last_due_date ) ) {
					$start_date = TTDate::incrementDate( $start_date, -1, 'week' );
				}

				$end_date_week_day_epoch = TTDate::getBeginWeekEpoch( $last_due_date, $end_date_week_day );
				$next_end_date_week_day_epoch = TTDate::getDateOfNextDayOfWeek( $last_due_date, $end_date_week_day_epoch );
				$end_date = TTDate::getMiddleDayEpoch( $next_end_date_week_day_epoch );

				$retval = [
						'due_date'   => $due_date,
						'start_date' => $start_date,
						'end_date'   => $end_date,
				];
				break;
			case 90100: //Upon Hire
				$retval = [ //By default clear out these dates.
							'due_date'   => '',
							'start_date' => '',
							'end_date'   => '',
				];

				Debug::Text( 'Upon Hire: Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ) . ' Delay Days: ' . $this->getDueDateDelayDays(), __FILE__, __LINE__, __METHOD__, 10 );

				// need to use getLastDueDate here to ensure our data is coming from the DB object and not the $last_due_date argument.
				if ( TTDate::getMiddleDayEpoch( $last_due_date ) == TTDate::getMiddleDayEpoch( $current_epoch ) || ( $this->getDueDate() == '' || TTDate::getMiddleDayEpoch( $this->getDueDate() ) == TTDate::getMiddleDayEpoch( $this->getLastDueDate() ) ) ) {
					$start_date = TTDate::incrementDate( $current_epoch, ( ( $this->getDueDateDelayDays() - 1 ) * -1 ), 'day' );

					if ( $this->getLastDueDate() != '' ) {
						if ( $start_date < $this->getLastDueDate() ) {
							$start_date = $this->getLastDueDate();
						}
					}
				} else {
					$start_date = $last_due_date;
				}
				$start_date = TTDate::getBeginDayEpoch( $start_date );
				$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $start_date, ( $this->getDueDateDelayDays() - 1 ), 'day' ) );
				Debug::Text( 'Checking for newly hired users between: Start Date: ' . TTDate::getDATE( 'DATE+TIME', $start_date ) . ' End Date: ' . TTDate::getDATE( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( is_object( $this->getCompanyObject() ) ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

					//As of schema version 1093A, the hire date column was changed to a date_stamp rather than epoch, and additional columns have also been added, which causes a SQL error during upgrade from old versions of TimeTrex.
					// So when called through the installer, use a less optimized SQL query that won't trigger that error.
					global $config_vars;
					if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) {
						Debug::Text( 'Installer Enabled, skipping optimized WHERE clause...', __FILE__, __LINE__, __METHOD__, 10 );
						$ulf->getByCompanyId( $this->getCompanyObject()->getId(), 1, null, null, [ 'hire_date' => 'asc' ] ); //Limit 1 so we get the earliest termination date first.
					} else {
						$filter_data = [ 'hire_start_date' => $start_date, 'hire_end_date' => $end_date ];
						$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompanyObject()->getId(), $filter_data, 1, null, null, [ 'hire_date' => 'asc' ] ); //Limit 1 so we get the earliest termination date first.
					}


					Debug::Text( '  Hired users: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $ulf->getRecordCount() > 0 ) {
						//Find the earliest termination date so we can start from there.
						$earliest_hire_date = false;
						foreach ( $ulf as $u_obj ) {
							if ( $earliest_hire_date == false || $u_obj->getHireDate() < $earliest_hire_date ) {
								$earliest_hire_date = $u_obj->getHireDate();
								Debug::Text( '    Setting earliest Hire Date: ' . TTDate::getDATE( 'DATE+TIME', $u_obj->getHireDate() ) . ' User ID: ' . $u_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							}
						}

						//This helps make sure we never use a hire_date before the start_date filters specified above, specifically when installer is enabled.
						if ( $earliest_hire_date < $start_date ) {
							$earliest_hire_date = $start_date;
						}

						$start_date = TTDate::getBeginDayEpoch( $earliest_hire_date );
						$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $start_date, ( $this->getDueDateDelayDays() - 1 ), 'day' ) );

						$retval = [
								'due_date'   => TTDate::incrementDate( $start_date, $this->getDueDateDelayDays(), 'day' ),
								'start_date' => $start_date,
								'end_date'   => $end_date,
						];
					}
				}
				break;
			case 90200: //Upon Termination
				//Due Date Delay Days is the maximum number of days that can elapse after the termination date when the event is triggered.
				//  Example: First Employee is Terminated on Oct 1st. Delay Due Date=10days. Time Period should be Oct 1st -> Oct 9th Due: Oct 10th.
				//           If no employee is terminated after Oct 9th, then dates all go to NULL.
				//           If an employee is terminated on Oct 12th, the dates should go from the 12th -> 21st Due: Oct 22nd.
				//				The start date should never be earlier than the previous last_due_date though.

				$retval = [ //By default clear out these dates.
							'due_date'   => '',
							'start_date' => '',
							'end_date'   => '',
				];

				Debug::Text( 'Upon Termination: Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ) . ' Delay Days: ' . $this->getDueDateDelayDays(), __FILE__, __LINE__, __METHOD__, 10 );
				// need to use getLastDueDate here to ensure our data is coming from the DB object and not the $last_due_date argument.
				if ( TTDate::getMiddleDayEpoch( $last_due_date ) == TTDate::getMiddleDayEpoch( $current_epoch ) || ( $this->getDueDate() == '' || TTDate::getMiddleDayEpoch( $this->getDueDate() ) == TTDate::getMiddleDayEpoch( $this->getLastDueDate() ) ) ) {

					$start_date = TTDate::incrementDate( $current_epoch, ( ( $this->getDueDateDelayDays() - 1 ) * -1 ), 'day' );

					if ( $this->getLastDueDate() != '' ) {
						if ( $start_date < $this->getLastDueDate() ) {
							$start_date = $this->getLastDueDate();
						}
					}
				} else {
					$start_date = $last_due_date;
				}
				$start_date = TTDate::getBeginDayEpoch( $start_date );
				$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $start_date, ( $this->getDueDateDelayDays() - 1 ), 'day' ) );
				Debug::Text( 'Checking for terminated users between: Start Date: ' . TTDate::getDATE( 'DATE+TIME', $start_date ) . ' End Date: ' . TTDate::getDATE( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( is_object( $this->getCompanyObject() ) ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

					//As of schema version 1093A, the hire date column was changed to a date_stamp rather than epoch, which causes a SQL error during upgrade from old versions of TimeTrex.
					// So when called through the installer, use a less optimized SQL query that won't trigger that error.
					// As of schema version 1093A, the hire date column was changed to a date_stamp rather than epoch, and additional columns have also been added, which causes a SQL error during upgrade from old versions of TimeTrex.
					global $config_vars;
					if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) {
						Debug::Text( 'Installer Enabled, skipping optimized WHERE clause...', __FILE__, __LINE__, __METHOD__, 10 );
						$ulf->getByCompanyId( $this->getCompanyObject()->getId(), 1, null, null, [ 'termination_date' => 'asc' ] ); //Limit 1 so we get the earliest termination date first.
					} else {
						$filter_data = [ 'termination_start_date' => $start_date, 'termination_end_date' => $end_date ];
						$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompanyObject()->getId(), $filter_data, 1, null, null, [ 'termination_date' => 'asc' ] ); //Limit 1 so we get the earliest termination date first.
					}

					Debug::Text( '  Terminated users: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $ulf->getRecordCount() > 0 ) {
						//Find the earliest termination date so we can start from there.
						$earliest_termination_date = false;
						foreach ( $ulf as $u_obj ) {
							if ( $earliest_termination_date == false || $u_obj->getTerminationDate() < $earliest_termination_date ) {
								$earliest_termination_date = $u_obj->getTerminationDate();
								Debug::Text( '    Setting earliest Termination Date: ' . TTDate::getDATE( 'DATE+TIME', $u_obj->getTerminationDate() ), __FILE__, __LINE__, __METHOD__, 10 );
							}
						}

						//This helps make sure we never use a hire_date before the start_date filters specified above, specifically when installer is enabled.
						if ( $earliest_termination_date < $start_date ) {
							$earliest_termination_date = $start_date;
						}

						$start_date = TTDate::getBeginDayEpoch( $earliest_termination_date );
						$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $start_date, ( $this->getDueDateDelayDays() - 1 ), 'day' ) );

						$retval = [
								'due_date'   => TTDate::incrementDate( $start_date, $this->getDueDateDelayDays(), 'day' ),
								'start_date' => $start_date,
								'end_date'   => $end_date,
						];
					}
				}
				break;
			case 90310: //Upon Termination (Pay Period End)
				//Due Date Delay Days is the maximum number of days that can elapse after the end date of the pay period that the termination date falls within, when the event is triggered.
				//  Example: Pay Period: Oct 1st to Oct 15th (BiWeekly)
				// 			 First Employee is Terminated on Oct 3rd. Delay Due Date=5days. Time Period should be Oct 1st -> Oct 15th Due: Oct 20th.
				//           If no employee is terminated after Oct 15th, then dates all go to NULL.
				//           If an employee is terminated on Oct 16th, the dates should go from the 16th -> 30st Due: Nov4th.
				//				The start date should never be earlier than the previous last_due_date though.

				$retval = [ //By default clear out these dates.
							'due_date'   => '',
							'start_date' => '',
							'end_date'   => '',
				];

				Debug::Text( 'Upon Termination: Last Due Date: ' . TTDate::getDate( 'DATE+TIME', $last_due_date ) . ' Delay Days: ' . $this->getDueDateDelayDays(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( is_object( $this->getCompanyObject() ) ) {
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$pplf->getThisPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate( $this->getCompanyObject()->getId(), $this->getPayPeriodSchedule(), $current_epoch );
					Debug::Text( '  Pay periods found: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $pplf->getRecordCount() > 0 ) {
						$pp_obj = $pplf->getCurrent();

						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompanyObject()->getId(), [ 'pay_period_schedule_id' => $this->getPayPeriodSchedule(), 'termination_start_date' => $pp_obj->getStartDate(), 'termination_end_date' => $pp_obj->getEndDate() ], 1, null, null, [ 'termination_date' => 'asc' ] ); //Limit 1 so we get the earliest termination date first.
						Debug::Text( '  Terminated users from ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' to ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ) . ': ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $ulf->getRecordCount() > 0 ) { //If at least one user is terminated in the pay period, return the dates.
							$start_date = TTDate::getBeginDayEpoch( $pp_obj->getStartDate() );
							$end_date = TTDate::getEndDayEpoch( $pp_obj->getEndDate() );
							$due_date = TTDate::getMiddleDayEpoch( TTDate::incrementDate( $end_date, $this->getDueDateDelayDays(), 'day' ) );

							$retval = [
									'due_date'   => $due_date,
									'start_date' => $start_date,
									'end_date'   => $end_date,
							];
						}
					}
				}
				break;
		}

		if ( isset( $retval['start_date'] ) && $retval['start_date'] != '' && isset( $retval['end_date'] ) && $retval['end_date'] != '' && isset( $retval['due_date'] ) && $retval['due_date'] != '' ) {
			$holiday_policy_dates = $this->getRecurringHolidayDates( $retval['due_date'] );
			$always_on_week_day = $this->getPayrollRemittanceAgencyObject()->getAlwaysOnWeekDay();
			$due_date = TTDate::getNearestWeekDay( $retval['due_date'], $always_on_week_day, $holiday_policy_dates );

			Debug::Text( 'Due Date: ' . TTDate::getDate( 'DATE+TIME', $retval['due_date'] ) . ' moved to: ' . TTDate::getDate( 'DATE+TIME', $due_date ) . ' direction: ' . $always_on_week_day, __FILE__, __LINE__, __METHOD__, 10 );

			$retval['start_date'] = TTDate::getBeginDayEpoch( $retval['start_date'] );
			$retval['end_date'] = TTDate::getEndDayEpoch( $retval['end_date'] );
			$retval['due_date'] = TTDate::getMiddleDayEpoch( $due_date );

			Debug::Text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $retval['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $retval['end_date'] ) . ' Due Date: ' . TTDate::getDate( 'DATE+TIME', $retval['due_date'] ), __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else if ( isset( $retval['start_date'] ) && $retval['start_date'] == '' && isset( $retval['end_date'] ) && $retval['end_date'] == '' && isset( $retval['due_date'] ) && $retval['due_date'] == '' ) {
			//Return blank dates to clear them out, for things like On Hire/On Termination.
			return $retval;
		}

		Debug::Text( 'Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $due_date
	 * @return bool|false|int|mixed
	 */
	function calculateNextReminderDate( $due_date ) {
		if ( $due_date != '' ) {
			Debug::Text( 'Due Date: ' . TTDate::getDate( 'DATE+TIME', $due_date ), __FILE__, __LINE__, __METHOD__, 10 );

			return TTDATE::incrementDate( $due_date, ( $this->getReminderDays() * -1 ), 'day' );
		}

		return false;
	}

	/**
	 * @param bool $start_epoch
	 * @return array
	 */
	function getRecurringHolidayDates( $start_epoch = false ) {
		$holiday_dates = [];
		$pra_obj = $this->getPayrollRemittanceAgencyObject();
		if ( is_object( $pra_obj ) ) {
			$selected_holidays = $pra_obj->getRecurringHoliday();
			if ( $start_epoch != false && is_array( $selected_holidays ) && count( $selected_holidays ) > 0 ) {
				$company_obj = $this->getCompanyObject();
				if ( is_object( $company_obj ) ) {

					$company_id = $company_obj->getId();
					$rhlf = TTnew( 'RecurringHolidayListFactory' ); /** @var RecurringHolidayListFactory $rhlf */
					$rhlf->getByIdAndCompanyId( $selected_holidays, $company_id );

					if ( $rhlf->getRecordCount() > 0 ) {
						foreach ( $rhlf as $rh_obj ) {
							$holiday_dates[] = TTDate::getBeginDayEpoch( $rh_obj->getNextDate( $start_epoch ) );
						}
					} else {
						Debug::Text( 'No results from database.', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'Company is not an object', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'No holidays selected or no epoch provided.', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		Debug::Arr( $holiday_dates, 'Holiday Dates: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $holiday_dates;
	}

	/**
	 * @return array|bool
	 */
	function getPayrollRemittanceAgencyEventReminderAddresses() {
		if ( $this->getReminderUser() != '' ) {
			$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
			$uplf->getByUserIdAndStatus( $this->getReminderUser(), 10 ); //Only email ACTIVE employees/supervisors when login is enabled. (checked below)
			if ( $uplf->getRecordCount() > 0 ) {
				$retarr = [];
				foreach ( $uplf as $up_obj ) {
					if ( $up_obj->getEnableEmailNotificationMessage() == true && is_object( $up_obj->getUserObject() ) && $up_obj->getUserObject()->getStatus() == 10 && $up_obj->getUserObject()->getEnableLogin() == true ) {
						if ( $up_obj->getUserObject()->getWorkEmail() != '' && $up_obj->getUserObject()->getWorkEmailIsValid() == true ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getWorkEmail(), $up_obj->getUserObject() );
						}

						if ( $up_obj->getEnableEmailNotificationHome() && is_object( $up_obj->getUserObject() ) && $up_obj->getUserObject()->getHomeEmail() != '' && $up_obj->getUserObject()->getHomeEmailIsValid() == true ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getHomeEmail(), $up_obj->getUserObject() );
						}
					}
				}

				if ( isset( $retarr ) ) {
					Debug::Arr( $retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__, 10 );

					return array_unique( $retarr );
				}
			} else {
				Debug::Text( 'No user preferences available, or user is not active...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function emailReminder() {
		Debug::Text( 'Email PayrollRemittanceAgencyEvent reminder: ', __FILE__, __LINE__, __METHOD__, 10 );

		$u_obj = $this->getReminderUserObject();
		if ( is_object( $u_obj ) === false ) {
			return false;
		}

		if ( $u_obj->getStatus() != 10 ) {
			Debug::Text( 'User ID: ' . $u_obj->getId() . ' Login is disabled, or user record is not active, so not emailing...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$pra_obj = $this->getPayrollRemittanceAgencyObject();
		if ( is_object( $pra_obj ) === false ) {
			return false;
		}
		$company_obj = $u_obj->getCompanyObject();
		if ( is_object( $company_obj ) === false ) {
			return false;
		}
		$legal_entity_obj = $pra_obj->getLegalEntityObject();
		if ( is_object( $legal_entity_obj ) === false ) {
			return false;
		}

		$email_to_arr = $this->getPayrollRemittanceAgencyEventReminderAddresses();
		if ( $email_to_arr == false ) {
			return false;
		}

		$from = '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Remittance Agency Event' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';
		Debug::Text( 'From: ' . $from, __FILE__, __LINE__, __METHOD__, 10 );

		$to = array_shift( $email_to_arr );
		Debug::Text( 'To: ' . $to, __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $email_to_arr ) && count( $email_to_arr ) > 0 ) {
			$bcc = implode( ',', $email_to_arr );
		} else {
			$bcc = null;
		}
		Debug::Text( 'Bcc: ' . $bcc, __FILE__, __LINE__, __METHOD__, 10 );

		$event_data = $this->getEventData();

		//Define subject/body variables here.
		$search_arr = [
				'#reminder_first_name#',
				'#reminder_last_name#',
				'#agency_name#',
				'#event_form_name#',
				'#event_form_description#',
				'#company_name#',
				'#legal_entity_name#',
				'#due_date#',
		];

		$replace_arr = [ //If changed, update $replace_arr references below.
						 $u_obj->getFirstName(),
						 $u_obj->getLastName(),
						 ( is_object( $pra_obj ) ) ? $pra_obj->getName() : null,
						 ( $event_data['form_name'] ) ? $event_data['form_name'] : null,
						 ( $event_data['form_description'] ) ? $event_data['form_description'] : null,
						 $company_obj->getName(),
						 $legal_entity_obj->getLegalName(),
						 TTDate::getDate( 'DATE+TIME', $this->getDueDate() ),
		];

		Debug::Arr( $this->data, 'Updating Remittance Agency Event reminder dates from: ' . TTDate::getDate( 'DATE+TIME', $this->getLastReminderDate() ) . ' to: ' . TTDate::getDate( 'DATE+TIME', $this->getNextReminderDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->setLastReminderDate( time() );

		$email_subject = TTi18n::gettext( '#event_form_name# reminder for #agency_name#' );

		$email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$email_body .= TTi18n::gettext( 'Reminder of upcoming remittance event, #event_form_name# for #agency_name# due by #due_date#.' ) . "\n";
		$email_body .= "\n\n";

		$email_body .= TTi18n::gettext( 'Link' ) . ': <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . '">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Login' ) . '</a>' . "\n\n";
		$email_body .= TTi18n::gettext( 'Due Date' ) . ': ' . TTDate::getDate( "DATE", $this->getDueDate() ) . "\n";
		$email_body .= TTi18n::gettext( 'Legal Entity' ) . ': ' . $replace_arr[6] . "\n";
		$email_body .= TTi18n::gettext( 'Company' ) . ': ' . $replace_arr[5] . "\n\n";
		$email_body .= TTi18n::gettext( 'Email Sent' ) . ': ' . TTDate::getDate( 'DATE+TIME', time() ) . "\n";

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text( 'Subject: ' . $subject, __FILE__, __LINE__, __METHOD__, 10 );

		$headers = [
				'From'    => $from,
				'Subject' => $subject,
				'Bcc'     => $bcc,
				//Reply-To/Return-Path are handled in TTMail.
		];

		$body = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $email_body ) . '</pre></body></html>';
		Debug::Text( 'Body: ' . $body, __FILE__, __LINE__, __METHOD__, 10 );

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody( $body );

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == true ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Emailed remittance agency event reminder to' ) . ': ' . $to . ' Bcc: ' . $headers['Bcc'], null, $this->getTable() );

			return true;
		}

		return true; //Always return true
	}

	/**
	 * @return bool
	 */
	function getEventData() {
		$remittance_agency_event_data = include( 'PayrollRemittanceAgencyEventFactory.data.php' ); //Contains large array of necessary data.

		$agency_id = $this->getPayrollRemittanceAgencyObject()->getAgency();
		$event_type_id = $this->getType();
		if ( isset( $remittance_agency_event_data[$agency_id][$event_type_id] ) ) {
			return $remittance_agency_event_data[$agency_id][$event_type_id];
		}

		Debug::Text( 'Error: Agency/Type does not exist... Agency ID: ' . $agency_id . ' Event Type ID: ' . $event_type_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param $action_id - 'file' or 'payment'
	 * @return string
	 */
	function getURL( $action_id ) {
		$agency_id = $this->getPayrollRemittanceAgencyObject()->getAgency();
		$event_type_id = $this->getType();

		$url = 'https://www.timetrex.com/rt.php?agency_id=' . $agency_id . '&event_type_id=' . $event_type_id . '&action_id=' . $action_id;
		Debug::Text( 'Action ID: ' . $action_id . ' Agency ID: ' . $agency_id . ' Event Type ID: ' . $event_type_id . ' URL: ' . $url, __FILE__, __LINE__, __METHOD__, 10 );

		return $url;
	}

	/**
	 * @param $report_id
	 * @param $data
	 * @param $user_obj
	 * @param $permission_obj
	 * @return bool|object
	 */
	function getReport( $report_id, $data, $user_obj, $permission_obj ) {
		$report_obj_name = null;
		$report_data = [];
		$report_data_override = []; //Overrides report data.
		$tmp_config = [];

		$agency_id = $this->getPayrollRemittanceAgencyObject()->getAgency();

		$event_data = $this->getEventData();
		$event_type_id = $this->getType();
		Debug::Text( 'Report ID: ' . $report_id . ' Agency ID: ' . $agency_id . ' Event Type ID: ' . $event_type_id, __FILE__, __LINE__, __METHOD__, 10 );

		$user_data_id = $this->getUserReportData();
		if ( TTUUID::isUUID( $user_data_id ) && $user_data_id != TTUUID::getZeroID() ) {
			$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
			$urdlf->getById( $user_data_id );
			if ( $urdlf->getRecordCount() == 1 ) {
				$urd_obj = $urdlf->getCurrent();

				$report_obj_name = $urd_obj->getScript();

				$report_obj = $urd_obj->getObjectHandler();

				$report_obj->setUserObject( $urd_obj->getUserObject() );
				$report_obj->setPermissionObject( new Permission() );

				//Flex saves the data as stdClass objects, so convert them to arrays instead.
				$report_data = Misc::convertObjectToArray( $urd_obj->getData() );
				$report_data['config']['other']['report_name'] = $urd_obj->getName();
			}
		} else {
			switch ( $agency_id ) {
				//Canada
				case '10:CA:00:00:0010':
					switch ( $event_type_id ) {
						case 'PIER':
							switch ( $report_id ) {
								case 'html':
									$template_name = 'pier';
									$report_obj_name = 'RemittanceSummaryReport';
									break;
								default:
									// $this->>report_obj is null
									break;
							}
							break;
						case 'T4SD':
							switch ( $report_id ) {
								case 'html':
									$template_name = 'by_pay_period_by_employee';
									$report_obj_name = 'RemittanceSummaryReport';
									break;
								case 'raw':
									$template_name = 'by_pay_period';
									$report_obj_name = 'RemittanceSummaryReport';
									break;
							}
							break;
						case 'T4':
							switch ( $report_id ) {
								case 'html':
								case 'pdf_form':
								case 'pdf_form_government':
								case 'raw':
								case 'efile_xml':
								case 'pdf_form_publish_employee':
									$template_name = 'by_employee';
									$report_obj_name = 'T4SummaryReport';
									break;
								default:
									// $this->>report_obj is null
									break;
							}
							break;
						case 'T4A':
							switch ( $report_id ) {
								case 'html':
								case 'pdf_form':
								case 'pdf_form_government':
								case 'raw':
								case 'efile_xml':
								case 'pdf_form_publish_employee':
									$template_name = 'by_employee';
									$report_obj_name = 'T4ASummaryReport';
									break;
								default:
									// $this->>report_obj is null
									break;
							}
							break;
					}
					break;
				case '10:CA:00:00:0020':
					switch ( $event_type_id ) {
						case 'ROE':
							$report_obj_name = 'ROEReport';
							$report_data_override['config']['termination_start_date'] = $this->getStartDate();
							$report_data_override['config']['termination_end_date'] = $this->getEndDate();
							$report_data_override['config']['time_period']['start_date'] = null;
							$report_data_override['config']['time_period']['end_date'] = null;
							break;
					}
					break;

				//US
				case '10:US:00:00:0010': // IRS
					switch ( $event_type_id ) {
						case 'F940':
							$template_name = 'by_quarter';
							$report_obj_name = 'Form940Report';
							break;
						case 'P940':
							$template_name = 'by_month';
							$report_obj_name = 'Form940Report';
							break;
						case 'F941':
							$template_name = 'by_month';
							$report_obj_name = 'Form941Report';
							break;
						case 'P941':
							$template_name = 'by_pay_period';
							$report_obj_name = 'Form941Report';
							break;
						case 'F1099NEC':
							switch ( $report_id ) {
								case 'html':
								case 'pdf_form':
								case 'pdf_form_government':
								case 'efile':
								case 'pdf_form_publish_employee':
									$template_name = 'by_employee';
									$report_obj_name = 'Form1099NecReport';
									break;
							}
							break;
					}
					break;
				case '10:US:00:00:0020': // SSA W2 (Federal)
					switch ( $event_type_id ) {
						case 'FW2':
							switch ( $report_id ) {
								case 'html':
								case 'pdf_form':
								case 'pdf_form_government':
								case 'efile':
								case 'pdf_form_publish_employee':
									$template_name = 'by_employee';
									$report_obj_name = 'FormW2Report';
									$report_data_override['config']['form']['form_type'] = 'w2'; //Always force W2 rather than W2C
									break;
							}
							break;
					}
					break;
				case '10:US:00:00:0100': //CMS PBJ
					switch ( $event_type_id ) {
						case 'PBJ':
							switch ( $report_id ) {
								case 'html':
								case 'payroll_export':
									$template_name = 'by_employee+all_time';
									$report_obj_name = 'PayrollExportReport';
									break;
							}
							break;
					}
					break;
				default:
					//Use default report specified below.
					Debug::Text( 'Using default case for Agency ID...', __FILE__, __LINE__, __METHOD__, 10 );
					switch ( $event_type_id ) {
						case 'FW2': //State W2's
							switch ( $report_id ) {
								case 'html':
								case 'pdf_form':
								case 'pdf_form_government':
								case 'efile':
								case 'pdf_form_publish_employee':
									$template_name = 'by_employee';
									$report_obj_name = 'FormW2Report';

									$report_data_override['config']['form']['form_type'] = 'w2'; //Always force W2 rather than W2C

									//This is required so FormW2Report knows what state we are trying to eFile for, so it can narrow down its choices.
									$report_data_override['config']['form']['efile_state'] = $this->getPayrollRemittanceAgencyObject()->getProvince();

									if ( $this->getPayrollRemittanceAgencyObject()->getType() == 30 ) {
										$report_data_override['config']['form']['efile_district'] = true;
									}

									$report_data_override['config']['form']['payroll_remittance_agency_id'] = $this->getPayrollRemittanceAgencyObject()->getId();

									break;
							}
							break;
						case 'NEWHIRE': //State new hires
							$template_name = 'by_employee+new_hire';
							$report_obj_name = 'UserSummaryReport';

							//Default start_date/end_date elements will not be added below due to !isset($report_data_override['config']['hire_time_period']) check.
							$report_data_override['config']['hire_time_period']['time_period'] = 'custom_date';
							$report_data_override['config']['hire_start_date'] = $this->getStartDate();
							$report_data_override['config']['hire_end_date'] = $this->getEndDate();

							$report_data_override['config']['other']['show_duplicate_values'] = true; //Reduces confusion for the user and there is no grouping anyways.
							break;
					}
					break;
			}
		}

		//Handle default report here if one is not already specified. This was handled in a "default" case above, but it wouldn't work if there was an agency specified.
		if ( $report_obj_name == '' ) {
			Debug::Text( '  No report specified, using default report...', __FILE__, __LINE__, __METHOD__, 10 );

			$template_name = 'by_employee+taxes';
			switch ( $report_id ) {
				default:
					$report_obj_name = 'TaxSummaryReport';
					$report_data['filter']['company_deduction_id'] = [];

					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyIdAndPayrollRemittanceAgencyId( $this->getPayrollRemittanceAgencyObject()->getLegalEntityObject()->getCompany(), $this->getPayrollRemittanceAgencyObject()->getId() );
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach ( $cdlf as $cd_obj ) {
							$tmp_config['company_deduction_id'][] = $cd_obj->getId();
						}
					}

					$report_data_override['config']['other']['show_duplicate_values'] = true; //Reduces confusion for the user and there is no grouping anyways.
					break;
			}
		}

		//Try to use the event form name as the report name, rather than just "Tax Summary"
		if ( isset( $event_data['form_name'] ) && ( !isset( $report_data['config']['other']['report_name'] ) || $report_data['config']['other']['report_name'] == '' ) ) {
			$report_data['config']['other']['report_name'] = $event_data['form_name'];
		}

		if ( isset( $report_obj_name ) && $report_obj_name != ''
				&& is_object( $user_obj ) && is_object( $permission_obj ) ) {
			$report_obj = TTNew( $report_obj_name );
			$report_obj->setUserObject( $user_obj );
			$report_obj->setPermissionObject( $permission_obj );

			if ( isset( $template_name ) && $template_name != '' ) {
				$report_data['config'] = Misc::trimSortPrefix( $report_obj->getOptions( 'template_config', [ 'template' => $template_name ] ) );
			}

			if ( isset( $report_data['config'] ) && isset( $tmp_config ) && count( $tmp_config ) > 0 ) {
				foreach ( $report_data['config'] as $key => $value ) {
					foreach ( $tmp_config as $tmp_key => $tmp_value ) {
						if ( Misc::trimSortPrefix( $key ) == $tmp_key ) {
							$report_data['config'][$key] = $tmp_config[$tmp_key];
						}
					}
				}
			}


			//Force legal entity to always be set.
			$report_data['config']['legal_entity_id'] = [ $this->getPayrollRemittanceAgencyObject()->getLegalEntity() ];

			//Force the start/end dates based on the event start/end dates.
			//FIXME: Need to handle "Per Pay Period" frequencies by filtering on the pay_period_id instead of start/end dates.
			if ( !isset( $report_data_override['config']['hire_time_period'] ) ) { //Make sure if new hire report filting based on hire_time_period is specified, we don't override other dates.
				$report_data['config']['time_period']['time_period'] = 'custom_date';

				$report_data['config']['time_period']['start_date'] = $this->getStartDate();
				$report_data['config']['time_period']['end_date'] = $this->getEndDate();
			}


			$tmp_form_config = Misc::convertObjectToArray( $report_obj->getCompanyFormConfig() );
			if ( is_array( $tmp_form_config ) ) {
				$report_data['config']['form'] = $tmp_form_config; //This is included in the rest of the config set below.
				unset( $tmp_form_config );
			}

			//Allow each agency/report to override specific report data at the very end as necessary.
			//$report_data = array_merge_recursive( $report_data, $report_data_override );
			$report_data = Misc::arrayMergeRecursive( $report_data, $report_data_override ); //array_merge_recursive() will combine a FALSE and a STRING into an array, rather than have one overwrite the other.

			//Set any remaining config.
			$report_obj->setConfig( (array)$report_data['config'] );

			Debug::Arr( $report_data, 'Report data: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$validation_obj = $report_obj->validateConfig( $report_id );
		if ( $validation_obj->isValid() == true ) {
			return $report_obj;
		} else {
			Debug::Text( '  Report config validation failed!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}
}

?>
