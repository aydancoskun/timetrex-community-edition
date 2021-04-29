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

/*
 * Adds time to employee accruals based on calendar milestones
 * This file should run once a day.
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//Debug::setVerbosity(11);

$current_epoch = TTDate::getTime();
//$current_epoch = strtotime('01-Aug-17 1:00 AM');

$offset = ( 86400 - ( 3600 * 2 ) ); //22hrs of variance. Must be less than 24hrs which is how often this script runs.

$clf = new CompanyListFactory();
$clf->getByStatusID( [ 10, 20, 23 ], null, [ 'a.id' => 'asc' ] );
if ( $clf->getRecordCount() > 0 ) {
	foreach ( $clf as $c_obj ) {
		if ( in_array( $c_obj->getStatus(), [ 10, 20, 23 ] ) ) { //10=Active, 20=Hold, 23=Expired
			$aplf = new AccrualPolicyListFactory();
			$aplf->getByCompanyIdAndTypeId( $c_obj->getId(), [ 20, 30 ] ); //Include hour based accruals so rollover adjustments can be calculated.
			if ( $aplf->getRecordCount() > 0 ) {
				foreach ( $aplf as $ap_obj ) {
					//Accrue for the previous day rather than the current day. So if an employee is hired on August 1st and entered on August 1st,
					// the next morning they will see accruals if it happens to be a frequency date.
					//This will make it seem like accruals are delayed by one day though in all other cases, but see #2334
					$ap_obj->addAccrualPolicyTime( ( $current_epoch - 86400 ), $offset );
				}
			}
		}
	}
}
Debug::writeToLog();
Debug::Display();
?>
