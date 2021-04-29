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

/*
 * Handle remittance agency events, and send out email reminders.
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

$clf = new CompanyListFactory();
$clf->getByStatusID( [ 10, 20, 23 ], null, [ 'a.id' => 'asc' ] );
if ( $clf->getRecordCount() > 0 ) {
	Debug::Text( 'PROCESSING Payroll Remittance Agency Event Reminders.', __FILE__, __LINE__, __METHOD__, 10 );
	foreach ( $clf as $c_obj ) {
		if ( $c_obj->getStatus() != 30 ) {
			$praelf = new PayrollRemittanceAgencyEventListFactory();
			//Handle pay period/on hire/on termination event frequencies that have no dates (update them)
			$praelf->getByCompanyIdAndFrequencyIdAndDueDateIsNull( $c_obj->getId(), [ 1000, 90100, 90200, 90310 ] );
			if ( $praelf->getRecordCount() > 0 ) {
				Debug::Text( '  Payroll Remittance Agency Event Reminders for ' . $c_obj->getName() . ': ' . $praelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $praelf as $prae_obj ) {
					if ( ( $prae_obj->getStatus() == 10 || $prae_obj->getStatus() == 15 ) ) {
						Debug::Text( 'Updating pay-period/on hire/on termination frequency dates for Event: ' . $prae_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$prae_obj->setEnableRecalculateDates( true );
						if ( $prae_obj->isValid() ) {
							$prae_obj->Save();
						}
					}
				}
				unset( $praelf, $prae_obj );
			}

			$praelf = new PayrollRemittanceAgencyEventListFactory();
			$praelf->getPendingReminder( $c_obj->getId() );
			if ( $praelf->getRecordCount() > 0 ) {
				Debug::Text( '  Found ' . $praelf->getRecordCount() . ' Payroll Remittance Agency Event Reminders for ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $praelf as $prae_obj ) {
					if ( ( $prae_obj->getStatus() == 10 || $prae_obj->getStatus() == 15 ) && $prae_obj->getNextReminderDate() != '' ) {
						$prae_obj->emailReminder();
						//Need to track last reminder date so we don't spam every time this runs.
						$prae_obj->setLastReminderDate( time() );
						if ( $prae_obj->isValid() ) {
							$prae_obj->save();
						}
					} else {
						Debug::Text( '  Next reminder date is blank, or event is disabled...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
				unset( $praelf, $prae_obj );
			} else {
				Debug::Text( 'No pending reminders found for ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}
	}
}
Debug::writeToLog();
Debug::Display();
?>