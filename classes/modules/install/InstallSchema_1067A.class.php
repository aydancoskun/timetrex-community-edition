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
 * @package Module_Install
 */
class InstallSchema_1067A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//Remove existing AddRecurringSchedule cron job so we can re-add with new times.
		$cjlf = TTnew('CronJobListFactory'); /** @var CronJobListFactory $cjlf */
		$cjlf->getByName('AddRecurringScheduleShift');
		if ( $cjlf->getRecordCount() > 0 ) {
			foreach( $cjlf as $cj_obj ) {
				$cj_obj->setDeleted(TRUE);
				if ( $cj_obj->isValid() ) {
					$cj_obj->Save();
				}
			}
		}
		unset($cjlf, $cj_obj);

		//Add AddRecurringSchedule cronjob to database.
		$cjf = TTnew( 'CronJobFactory' ); /** @var CronJobFactory $cjf */
		$cjf->setName('AddScheduleShift');
		$cjf->setMinute(50);
		$cjf->setHour('2,6,10,14,18,22'); //Every 4hrs.
		$cjf->setDayOfMonth('*');
		$cjf->setMonth('*');
		$cjf->setDayOfWeek('*');
		$cjf->setCommand('AddScheduleShift.php');
		$cjf->Save();

		//Add MiscWeekly cronjob to database.
		$cjf = TTnew( 'CronJobFactory' ); /** @var CronJobFactory $cjf */
		$cjf->setName('AddRecurringScheduleShift');
		$cjf->setMinute(55);
		$cjf->setHour(4);
		$cjf->setDayOfMonth('*');
		$cjf->setMonth('*');
		$cjf->setDayOfWeek('0'); //Sunday morning.
		$cjf->setCommand('AddRecurringScheduleShift.php');
		$cjf->Save();

		$current_epoch = time();

		$clf = new CompanyListFactory();
		$clf->getByStatusID( array(10,20,23) );
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				Debug::text('Company: '. $c_obj->getName() .' ID: '. $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

				//
				//Populate Recurring Schedule table.
				//
				$rsclf = new RecurringScheduleControlListFactory();
				$rsclf->getByCompanyIdAndStartDateAndEndDate( $c_obj->getId(), $current_epoch, $current_epoch );
				if ( $rsclf->getRecordCount() > 0 ) {
					Debug::text('Recurring Schedule Control List Record Count: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $rsclf as $rsc_obj ) {
						//During the upgrade, just populate 2 weeks worth, let the maintenance job populate the rest to help speed up the schema upgrade process.
						//$maximum_end_date = ( TTDate::getBeginWeekEpoch($current_epoch) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) );
						$maximum_end_date = ( TTDate::getBeginWeekEpoch($current_epoch) + ( 2 * ( 86400 * 7 ) ) );
						if ( $rsc_obj->getEndDate() != '' AND $maximum_end_date > $rsc_obj->getEndDate() ) {
							$maximum_end_date = $rsc_obj->getEndDate();
						}
						Debug::text('Recurring Schedule ID: '. $rsc_obj->getID() .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);

						$rsf = TTnew('RecurringScheduleFactory'); /** @var RecurringScheduleFactory $rsf */
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $current_epoch, $maximum_end_date );
					}
				}
				unset($rsclf, $rsc_obj, $rsf);

				//
				//Migrate Recurring PS Amendments to Tax/Deductions
				//
				$rpsalf = TTnew('RecurringPayStubAmendmentListFactory'); /** @var RecurringPayStubAmendmentListFactory $rpsalf */
				$rpsalf->getByCompanyId( $c_obj->getId() );
				if ( $rpsalf->getRecordCount() > 0 ) {
					$i = 1;
					foreach( $rpsalf as $rpsa_obj ) {
						if ( $rpsa_obj->getStatus() == 50
								AND count( $rpsa_obj->getUser() ) > 0
								AND ( ( $rpsa_obj->getEndDate() == '' OR $rpsa_obj->getEndDate() == 0 )
										OR ( ( $rpsa_obj->getEndDate() != '' OR $rpsa_obj->getEndDate() == 0 ) AND $current_epoch < $rpsa_obj->getEndDate() ) )
							) {
							Debug::text(' Recurring PS Amendment is enabled and active... ID: '. $rpsa_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

							$cdf = TTnew('CompanyDeductionFactory'); /** @var CompanyDeductionFactory $cdf */

							$cdf->StartTransaction();
							$cdf->setCompany( $rpsa_obj->getCompany() );
							$cdf->setType( 30 ); //Other
							$cdf->setCalculationOrder( ( 1000 + $i ) );
							$cdf->setName( $rpsa_obj->getName() .' ('. TTi18n::getText('Migrated from Recurring PSA') .')' );
							$cdf->setPayStubEntryDescription( $rpsa_obj->getPayStubAmendmentDescription() );
							$cdf->setApplyPayrollRunType( 10 ); //Normal Payroll Runs only.
							$cdf->setMinimumLengthOfService( 0 );
							$cdf->setMaximumLengthOfService( 0 );
							$cdf->setMinimumUserAge( 0 );
							$cdf->setMaximumUserAge( 0 );

							switch( $rpsa_obj->getFrequency() ) {
								case 10: //Each Pay Period
									$cdf->setApplyFrequency( 10 ); //Each Pay Period
									break;
								case 30: //Weekly
									$cdf->setApplyFrequency( 10 ); //There isn't a Weekly option for this yet, use pay period for now.
									break;
								case 40: //Monthly
									$cdf->setApplyFrequency( 30 );
									$cdf->setApplyFrequencyDayOfMonth( TTDate::getDayOfMonth( $rpsa_obj->getStartDate() ) );
									break;
								case 70: //Yearly
									$cdf->setApplyFrequency( 20 );
									$cdf->setApplyFrequencyMonth( TTDate::getMonth( $rpsa_obj->getStartDate() ) );
									$cdf->setApplyFrequencyDayOfMonth( TTDate::getDayOfMonth( $rpsa_obj->getStartDate() ) );
									break;
							}

							if ( $rpsa_obj->getStartDate() != '' ) {
								$cdf->setStartDate( $rpsa_obj->getStartDate() );
							}
							if ( $rpsa_obj->getEndDate() != '' ) {
								$cdf->setEndDate( $rpsa_obj->getEndDate() );
							}

							$cdf->setPayStubEntryAccount( $rpsa_obj->getPayStubEntryNameId() );

							if ( $rpsa_obj->getType() == 10 ) { //Fixed Amount
								$cdf->setCalculation( 20 );
								$cdf->setUserValue1( $rpsa_obj->getAmount() );
							} else { //Percent
								$cdf->setCalculation( 10 );
								$cdf->setUserValue1( $rpsa_obj->getPercentAmount() );
							}

							if ( $cdf->isValid() ) {
								$cdf->Save(FALSE);
								$cdf->setUser( $rpsa_obj->getUser() );

								if ( $rpsa_obj->getType() == 20 ) { //Percent
									$cdf->setIncludePayStubEntryAccount( $rpsa_obj->getPercentAmountEntryNameId() );
								}

								$cdf->Save();
							}

							//Delete any ACTIVE Pay Stub Amendments created from this Recurring PSA that are ACTIVE. Otherwise the pay stub might double-up the amounts.
							$psamlf = TTnew('PayStubAmendmentListFactory'); /** @var PayStubAmendmentListFactory $psamlf */
							$psamlf->getAPISearchByCompanyIdAndArrayCriteria( $c_obj->getId(), array('recurring_ps_amendment_id' => $rpsa_obj->getId(), 'status_id' => 50 ) );
							if ( $psamlf->getRecordCount() > 0 ) {
								foreach( $psamlf as $psam_obj ) {
									Debug::text('  Deleting ACTIVE PayStub Amendment ID: '. $psam_obj->getID() .' with Effective Date: '. TTDate::getDate('DATE', $psam_obj->getEffectiveDate() ).' based on RPSA: '. $rpsa_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
									$psam_obj->setDeleted(TRUE);
									if ( $psam_obj->isValid() ) {
										$psam_obj->Save();
									}
								}
							}

							//Delete RPSA now that its been migrated.
							$rpsa_obj->setDeleted(TRUE);
							if ( $rpsa_obj->isValid() ) {
								$rpsa_obj->Save();
							}

							$cdf->CommitTransaction();
						} else {
							Debug::text(' Recurring PS Amendment is disabled, has no users, or after end date, skipping... ID: '. $rpsa_obj->getID() .' End Date: '. $rpsa_obj->getEndDate(), __FILE__, __LINE__, __METHOD__, 10);
						}
						$i++;
					}
				}
				unset($rpsalf, $rpsa_obj, $i, $cdf);

				//
				//Remove Accrual Policies of Type "Standard" as they serve no purpose anymore.
				//
				$aplf = TTnew('AccrualPolicyListFactory'); /** @var AccrualPolicyListFactory $aplf */
				$aplf->getByCompanyIdAndTypeId( $c_obj->getId(), 10 );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$ap_obj->setDeleted(TRUE);
						if ( $ap_obj->isValid() ) {
							Debug::text(' Deleting Standard Accrual Policy ID: '. $ap_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
							$ap_obj->Save();
						}
					}
				}
				unset($aplf, $ap_obj);
			}
		}

		//Remove existing AddRecurringPayStubAmendment cron job, as they will be migrated to Tax/Deductions instead.
		$cjlf = TTnew('CronJobListFactory'); /** @var CronJobListFactory $cjlf */
		$cjlf->getByName('AddRecurringPayStubAmendment');
		if ( $cjlf->getRecordCount() > 0 ) {
			foreach( $cjlf as $cj_obj ) {
				$cj_obj->setDeleted(TRUE);
				if ( $cj_obj->isValid() ) {
					$cj_obj->Save();
				}
			}
		}
		unset($cjlf, $cj_obj);

		//Push MiscWeekly cron back by one hour so it doesnt run at the same time as MiscDaily
		$cjlf = TTnew('CronJobListFactory'); /** @var CronJobListFactory $cjlf */
		$cjlf->getByName('MiscWeekly');
		if ( $cjlf->getRecordCount() > 0 ) {
			foreach( $cjlf as $cj_obj ) {
				$cj_obj->setHour( 2 );
				$cj_obj->setMinute( 30 );
				if ( $cj_obj->isValid() ) {
					$cj_obj->Save();
				}
			}
		}
		unset($cjlf, $cj_obj);

		$sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $sslf */
		$sslf->getByName( 'schema_version_group_T' );
		if ( $sslf->getRecordCount() > 0 ) {
			foreach( $sslf as $ss_obj ) {
				$ss_obj->Delete();
			}
		}

		return TRUE;
	}
}
?>
