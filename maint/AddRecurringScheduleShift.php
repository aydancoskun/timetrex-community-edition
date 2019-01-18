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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

//
//
// See tools/fix_recurring_schedule.php when making changes.
//
//


$minimum_add_shift_offset = (3600 * 2) ; //Add shifts at least 2hrs before they start.
$lookup_shift_offset = (3600 * 10); //Lookup shifts that started X hrs before now.

$current_epoch = TTDate::getTime();
//$current_epoch = strtotime('13-Sep-2015 10:00 PM');
Debug::text('Current Epoch: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);

//Initial Start/End dates need to cover all timezones, we narrow it done further once we change to each users timezone later on.
$initial_start_date = TTDate::getBeginDayEpoch( $current_epoch - $lookup_shift_offset );
$initial_end_date = ($current_epoch + 86400);
Debug::text('Initial Start Date: '. TTDate::getDate('DATE+TIME', $initial_start_date ) .' End Date: '. TTDate::getDate('DATE+TIME', $initial_end_date ), __FILE__, __LINE__, __METHOD__, 10);

$clf = new CompanyListFactory();
$clf->getByStatusID( array(10,20,23), NULL, array('a.id' => 'asc') );
if ( $clf->getRecordCount() > 0 ) {
	foreach ( $clf as $c_obj ) {
		if ( $c_obj->getStatus() != 30 ) {
			Debug::text('Company: '. $c_obj->getName() .' ID: '. $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

			//
			// Purge recurring schedules control rows 90 days after end date has passed.
			//
			$rsclf = new RecurringScheduleControlListFactory();
			$rsclf->getByCompanyIdAndEndDate( $c_obj->getId(), ( $current_epoch - (86400 * 91) ) );
			Debug::text('  Recurring Schedule Control records 90days past its end date: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $rsclf->getRecordCount() ) {
				foreach( $rsclf as $rsc_obj ) {
					Debug::text('    Deleting RSC... ID: '. $rsc_obj->getID() .' End Date: '. TTDate::getDate('DATE', $rsc_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);
					$rsc_obj->setDeleted(TRUE);
					if ( $rsc_obj->isValid() ) {
						$rsc_obj->Save();
					}
				}
			}
			unset($rsc_obj);

			// FIXME: Purge recurring schedule control rows if all users assigned to them are inactive or past their termination date by 90days?


			//
			// Add new recurring schedules.
			//
			$rsclf->getByCompanyIdAndStartDateAndEndDate( $c_obj->getId(), $initial_start_date, $initial_end_date );
			if ( $rsclf->getRecordCount() > 0 ) {
				Debug::text('Recurring Schedule Control List Record Count: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $rsclf as $rsc_obj ) {
					$rsclf->StartTransaction(); // Wrap each individual schedule in its own transaction instead.

					//Since cron jobs run in system timezone (ie: PST8PDT) and date_stamp and start_date/end_date (timestamptz) columns being different data types
					//we need to try to switch into a timezone at least within the same day as the final timezone before we get the recurring schedules.
					//Once we do something with the date_stamp column or store timezones, we can remove this, as its not a 100% fix.
					$rstc_obj = $rsc_obj->getRecurringScheduleTemplateControlObject();
					if ( is_object( $rstc_obj ) ) {
						Debug::text('Recurring Schedule Template Control last updated by: '. $rstc_obj->getUpdatedBy(), __FILE__, __LINE__, __METHOD__, 10);
						if ( $rstc_obj->getUpdatedBy() > 0 ) {
							$ulf = TTnew( 'UserListFactory' );
							$ulf->getById( $rstc_obj->getUpdatedBy() );
							if ( $ulf->getRecordCount() > 0 ) {
									$ulf->getCurrent()->getUserPreferenceObject()->setTimeZonePreferences();
							} else {
									//Use system timezone.
									TTDate::setTimeZone();
							}
						} else {
							//Use system timezone.
							TTDate::setTimeZone();
						}
					}

					//Make sure its always at least the display weeks based on the end of the current week.
					$maximum_end_date = ( ( TTDate::getEndWeekEpoch($current_epoch) + 1 ) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) - 1 );
					if ( $rsc_obj->getEndDate() != '' AND $maximum_end_date > $rsc_obj->getEndDate() ) {
						$maximum_end_date = $rsc_obj->getEndDate();
					}
					Debug::text('Recurring Schedule ID: '. $rsc_obj->getID() .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);

					$rsf = TTnew('RecurringScheduleFactory');
					$rslf = TTNew('RecurringScheduleListFactory');
					
					//Clear out recurring schedules for anything older than 1 week.
					$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - (86400 * 720) ), TTDate::getEndWeekEpoch( ( TTDate::getBeginWeekEpoch( $current_epoch ) - ( 86400 * 8 ) ) ) );
					
					//Grab the earliest last day of the recurring schedule, so we can start from there and add the next week.
					//We actually want to get the last day of each recurring schedule, and just add to that. Rather then rebuilding the entire schedule.
					//$minimum_start_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $rslf->getMinimumStartTimeByRecurringScheduleControlID( $rsc_obj->getID() ) + 86400 ) ) );
					$minimum_start_date = $rslf->getMinimumStartTimeByRecurringScheduleControlID( $rsc_obj->getID() );
					if ( $minimum_start_date != '' ) {
						//$new_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getEndWeekEpoch( TTDate::getMiddleDayEpoch( $minimum_start_date ) ) + 86400 ) );
						//Use the exact date that we left off with the last recurring schedule.
						//This should fix a bug where if the recurring schedule last shift was on Mon Jun 12th, it would skip a week due to taking that time and starting in the next week.
						//I think we always need to overlap by at least a week in cases where the last schedule ends on a Wed or Mon or something.
						$new_week_start_date = TTDate::getBeginWeekEpoch( $minimum_start_date );
						Debug::text('  Starting from where we last left off: '. TTDate::getDate('DATE+TIME', $new_week_start_date ), __FILE__, __LINE__, __METHOD__, 10);
					} else {
						//$new_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $initial_start_date ) - (86400 * 8) ) );
						$new_week_start_date = TTDate::getBeginWeekEpoch( $initial_start_date );
						Debug::text('  Setting new week start date to: '. TTDate::getDate('DATE+TIME', $new_week_start_date ), __FILE__, __LINE__, __METHOD__, 10);
					}
					$new_week_end_date = TTDate::getEndWeekEpoch( $new_week_start_date );
					Debug::text('  Start Date: '. TTDate::getDate('DATE+TIME', $new_week_start_date ) .' End Date: '. TTDate::getDate('DATE+TIME', $new_week_end_date ) .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);
					
					if ( $new_week_end_date <= $maximum_end_date ) {
						//Add new schedules for the upcoming week.
						//$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), $new_week_start_date, $new_week_end_date );
						//$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $new_week_start_date, $new_week_end_date );

						//Rather than just add new schedules for one week, bring them up to the maximum end date, which in most cases should be just 1 week unless some kind of issue has occurred.
						//This will help in cases where maintenance jobs haven't run for a while, or in other strange cases too maybe.
						$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), $new_week_start_date, $maximum_end_date );
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $new_week_start_date, $maximum_end_date ); //Always create schedules out to the maximum end date each time.
					} else {
						Debug::text('  Already past maximum end date of: '. TTDate::getDate('DATE+TIME', $maximum_end_date ) .' Display Weeks: '. $rsc_obj->getDisplayWeeks() .' End Date: '. TTDate::getDate('DATE+TIME', $rsc_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);
					}

					//$rsclf->FailTransaction();
					$rsclf->CommitTransaction();
				}
			}
		} else {
			Debug::text('Company is not ACTIVE: '. $c_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
		}
	}
}
//$profiler->printTimers(TRUE);
Debug::writeToLog();
Debug::Display();
?>