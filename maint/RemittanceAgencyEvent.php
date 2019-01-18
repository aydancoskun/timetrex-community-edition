<?php
/**
 * $License$
 */

/*
 * Handle remittance agency events, and send out email reminders.
 *
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$clf = new CompanyListFactory();
$clf->getByStatusID( array(10, 20, 23), NULL, array('a.id' => 'asc') );
if ( $clf->getRecordCount() > 0 ) {
	Debug::Text('PROCESSING Payroll Remittance Agency Event Reminders.', __FILE__, __LINE__, __METHOD__, 10);
	foreach ( $clf as $c_obj ) {
		if ( $c_obj->getStatus() != 30 ) {
			$praelf = new PayrollRemittanceAgencyEventListFactory();
			//Handle pay period/on hire/on termination event frequencies that have no dates (update them)
			$praelf->getByCompanyIdAndFrequencyIdAndDueDateIsNull( $c_obj->getId(), array(1000, 90100, 90200, 90310) );
			if ( $praelf->getRecordCount() > 0 ) {
				Debug::Text( '  Payroll Remittance Agency Event Reminders for ' . $c_obj->getName() . ': ' . $praelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				/** @var $prae_obj PayrollRemittanceAgencyEventFactory */
				foreach ( $praelf as $prae_obj ) {
					if ( ( $prae_obj->getStatus() == 10 OR $prae_obj->getStatus() == 15 ) ) {
						Debug::Text( 'Updating pay-period/on hire/on termination frequency dates for Event: ' . $prae_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$prae_obj->setEnableRecalculateDates( TRUE );
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
				/** @var $prae_obj PayrollRemittanceAgencyEventFactory */
				foreach ( $praelf as $prae_obj ) {
					if ( ( $prae_obj->getStatus() == 10 OR $prae_obj->getStatus() == 15 ) AND $prae_obj->getNextReminderDate() != '' ) {
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