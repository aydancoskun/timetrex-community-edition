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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 1 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: fix_recurring_schedule.php [options] [company_id]\n";
	$help_output .= "    -n				Dry-run\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '-n', $argv ) ) {
		$dry_run = true;
		echo "Using DryRun!\n";
	} else {
		$dry_run = false;
	}

	if ( isset( $argv[$last_arg] ) && is_numeric( $argv[$last_arg] ) ) {
		$company_id = $argv[$last_arg];
	}

	//
	//
	// See maint/AddRecurringScheduleShift.php when making changes.
	//
	//

	//Force flush after each output line.
	ob_implicit_flush( true );
	ob_end_flush();

	$current_epoch = time();

	//Initial Start/End dates need to cover all timezones, we narrow it done further once we change to each users timezone later on.
	$initial_start_date = TTDate::getBeginDayEpoch( $current_epoch - ( 3600 * 10 ) );
	$initial_end_date = ( $current_epoch + ( 86400 * 365 ) );
	Debug::text( 'Initial Start Date: ' . TTDate::getDate( 'DATE+TIME', $initial_start_date ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $initial_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

	$clf = new CompanyListFactory();
	$clf->getAll();
	if ( $clf->getRecordCount() > 0 ) {
		foreach ( $clf as $c_obj ) {
			if ( isset( $company_id ) && $company_id != '' && $company_id != $c_obj->getId() ) {
				continue;
			}

			if ( $c_obj->getStatus() != 30 ) {
				Debug::text( 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				echo 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID() . "\n";


				$rsclf = new RecurringScheduleControlListFactory();

				//
				// Add new recurring schedules.
				//
				$rsclf->getByCompanyIdAndStartDateAndEndDate( $c_obj->getId(), $initial_start_date, $initial_end_date );
				if ( $rsclf->getRecordCount() > 0 ) {
					Debug::text( 'Recurring Schedule Control List Record Count: ' . $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $rsclf as $rsc_obj ) {
						$rsclf->StartTransaction(); // Wrap each individual schedule in its own transaction instead.

						//Since cron jobs run in system timezone (ie: PST8PDT) and date_stamp and start_date/end_date (timestamptz) columns being different data types
						//we need to try to switch into a timezone at least within the same day as the final timezone before we get the recurring schedules.
						//Once we do something with the date_stamp column or store timezones, we can remove this, as its not a 100% fix.
						$rstc_obj = $rsc_obj->getRecurringScheduleTemplateControlObject();
						if ( is_object( $rstc_obj ) ) {
							Debug::text( 'Recurring Schedule Template Control last updated by: ' . $rstc_obj->getUpdatedBy(), __FILE__, __LINE__, __METHOD__, 10 );
							echo "  Recurring Schedule Template Control ID: " . $rstc_obj->getID() . " Name: " . $rstc_obj->getName() . "\n";
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
						$maximum_end_date = ( ( TTDate::getEndWeekEpoch( $current_epoch ) + 1 ) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) - 1 );
						if ( $rsc_obj->getEndDate() != '' && $maximum_end_date > $rsc_obj->getEndDate() ) {
							$maximum_end_date = $rsc_obj->getEndDate();
						}
						Debug::text( 'Recurring Schedule ID: ' . $rsc_obj->getID() . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

						$rsf = TTnew( 'RecurringScheduleFactory' );
						$rslf = TTNew( 'RecurringScheduleListFactory' );

						//Clear out recurring schedules for anything older than 1 week.
						$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), $maximum_end_date );
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), ( $current_epoch - ( 86400 * 2 ) ), $maximum_end_date );

						if ( $dry_run == true ) {
							$rsclf->FailTransaction();
						}
						$rsclf->CommitTransaction();
					}
				}
			}
		}
	}
}
echo "Done...\n";
Debug::WriteToLog();
//Debug::Display();
?>
