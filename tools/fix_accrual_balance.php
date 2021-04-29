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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 1 OR ( isset( $argv[1] ) AND in_array( $argv[1], array('--help', '-help', '-h', '-?') ) ) ) {
	$help_output = "Usage: fix_accrual_balance.php [options] [company_id]\n";
	$help_output .= "    -timesheet		Recalculate Accrual Policies\n";
	$help_output .= "    -balance		Recalculate Accrual Balances\n";
	$help_output .= "    -recalc		Recalculate Accrual Policies\n";
	$help_output .= "    -n				Dry-run\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '-balance', $argv ) ) {
		$recalc_balance = true;
	} else {
		$recalc_balance = false;
	}

	if ( in_array( '-timesheet', $argv ) ) {
		$recalc_timesheet = true;
	} else {
		$recalc_timesheet = false;
	}

	if ( in_array( '-recalc', $argv ) ) {
		$recalc = true;
	} else {
		$recalc = false;
	}

	if ( in_array( '-n', $argv ) ) {
		$dry_run = true;
		echo "Using DryRun!\n";
	} else {
		$dry_run = false;
	}

	if ( isset( $argv[ $last_arg ] ) AND $argv[ $last_arg ] != '' AND TTUUID::isUUID( $argv[ $last_arg ] ) ) {
		$company_id = $argv[ $last_arg ];
	}


	$filter_start_date = strtotime( '2015-01-01' );
	//$filter_start_date = strtotime('2000-01-01');

	$filter_end_date = ( time() + ( 86400 * ( 365 * 2 ) ) );
	//$filter_end_date = strtotime('2015-01-02');


	//Force flush after each output line.
	ob_implicit_flush( true );
	ob_end_flush();

	//TTDate::setTimeZone( 'UTC' ); //Always force the timezone to be set.

	$clf = new CompanyListFactory();
	$clf->getAll();
	if ( $clf->getRecordCount() > 0 ) {
		foreach ( $clf as $c_obj ) {
			if ( isset( $company_id ) AND $company_id != '' AND $company_id != $c_obj->getId() ) {
				continue;
			}

			//if ( !in_array( $c_obj->getID(), array(1310,1347) ) ) {
			//	continue;
			//}

			if ( $c_obj->getStatus() != 30 ) {
				Debug::text( 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				echo 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID() . "\n";
				//Check to see if there any absence entries that are linked to an accrual, but the accrual record does not exist.

				if ( $recalc_balance == true ) {
					echo ' Recalculating Accrual Balances...' . "\n";

					$ablf = new AccrualBalanceListFactory();
					$ablf->getByCompanyId( $c_obj->getID() );
					$ablf->StartTransaction();

					if ( $ablf->getRecordCount() > 0 ) {
						foreach ( $ablf as $ab_obj ) {
							$before_balance = $ab_obj->getBalance();

							if ( $recalc == true ) {
								$alf = new AccrualListFactory();
								$alf->getOrphansByUserId( $ab_obj->getUser() );
								if ( $alf->getRecordCount() > 0 ) {
									foreach ( $alf as $a_obj ) {
										Debug::text( 'Orphan Record ID: ' . $a_obj->getID() . ' User ID: ' . $ab_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
										$a_obj->Delete();
									}
								}

								Debug::text( 'Recalculating Balance for User ID: ' . $ab_obj->getUser() . ' Accrual Account ID: ' . $ab_obj->getAccrualPolicyAccount(), __FILE__, __LINE__, __METHOD__, 10 );
								echo '  Recalculaing balance for: ' . $ab_obj->getUserObject()->getUsername() . ' (ID: ' . $ab_obj->getUserObject()->getID() . ') Accrual Account ID: ' . $ab_obj->getAccrualPolicyAccount() . ' Orphans: ' . $alf->getRecordCount() . "...\n";
								AccrualBalanceFactory::calcBalance( $ab_obj->getUser(), $ab_obj->getAccrualPolicyAccount() );
							} else {
								echo '  Checking balance for: ' . $ab_obj->getUserObject()->getUsername() . "...\n";
							}

							$after_balance = false;
							$ablf_tmp = new AccrualBalanceListFactory();
							$ablf_tmp->getByUserIdAndAccrualPolicyAccount( $ab_obj->getUser(), $ab_obj->getAccrualPolicyAccount() );
							if ( $ablf_tmp->getRecordCount() > 0 ) {
								foreach ( $ablf_tmp as $ab_obj_tmp ) {
									$after_balance = $ab_obj_tmp->getBalance();
								}
							}
							unset( $ablf_tmp, $ab_obj_tmp );

							Debug::text( '  Before Balance: ' . $before_balance . ' After: ' . $after_balance, __FILE__, __LINE__, __METHOD__, 10 );
							if ( $before_balance != $after_balance ) {
								echo '    Balance changed... Before: ' . TTDate::getHours( $before_balance ) . ' After: ' . TTDate::getHours( $after_balance ) . "\n";
								//break 1;
							}
						}
					}

					if ( $dry_run == true ) {
						$ablf->FailTransaction();
					}
					$ablf->CommitTransaction();
				}

				if ( $recalc_timesheet == true ) {
					echo ' Recalculating TimeSheet Balances...' . "\n";

					$flags = array(
							'meal'              => false,
							'undertime_absence' => true, //Needs to calculate undertime absences, otherwise it won't update accruals.
							'break'             => false,
							'holiday'           => false,
							'schedule_absence'  => true, //Required to calculate absences created from the schedule that may be causing duplicate entries in the accrual.
							'absence'           => true,
							'regular'           => false,
							'overtime'          => false,
							'premium'           => false,
							'accrual'           => true,

							'exception'           => false,
							//Exception options
							'exception_premature' => false, //Calculates premature exceptions
							'exception_future'    => false, //Calculates exceptions in the future.

							//Calculate policies for future dates.
							'future_dates'        => false, //Calculates dates in the future.
					);

					//Get all absence policies, then find their pay formula to see if its linked to an accrual.
					$aplf = new AbsencePolicyListFactory();
					$aplf->getByCompanyId( $c_obj->getID() );

					$aplf->StartTransaction();
					if ( $aplf->getRecordCount() > 0 ) {
						foreach ( $aplf as $ap_obj ) {
							$pf_obj = $ap_obj->getPayFormulaPolicyObject();
							if ( is_object( $pf_obj ) AND $pf_obj->getAccrualPolicyAccount() > 0 AND is_object( $pf_obj->getAccrualPolicyAccountObject() ) AND $pf_obj->getAccrualRate() != 0 ) {
								Debug::text( 'Found Absence Policy linked to Accrual: ' . $ap_obj->getName() . ' Accrual Account ID: ' . $pf_obj->getAccrualPolicyAccountObject()->getName(), __FILE__, __LINE__, __METHOD__, 10 );

								$ulf = new UserListFactory();
								$ulf->getByCompanyId( $c_obj->getID() );
								if ( $ulf->getRecordCount() > 0 ) {
									foreach ( $ulf as $user_obj ) {
										//if ( !in_array( $user_obj->getID(), array(496) ) ) {
										//	continue;
										//}

										if ( $user_obj->getStatus() == 20 ) {
											continue;
										}

										$user_obj_prefs = $user_obj->getUserPreferenceObject();
										if ( is_object( $user_obj_prefs ) ) {
											$user_obj_prefs->setTimeZonePreferences();
										} else {
											//Use system timezone.
											TTDate::setTimeZone();
										}
										Debug::text( 'User Name: ' . $user_obj->getUserName() . ' ID: ' . $user_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

										$before_balance = false;
										$ablf_tmp = new AccrualBalanceListFactory();
										$ablf_tmp->getByUserIdAndAccrualPolicyAccount( $user_obj->getId(), $pf_obj->getAccrualPolicyAccount() );
										if ( $ablf_tmp->getRecordCount() > 0 ) {
											foreach ( $ablf_tmp as $ab_obj_tmp ) {
												$before_balance = $ab_obj_tmp->getBalance();
											}
										}
										unset( $ablf_tmp, $ab_obj_tmp );

										$udtlf = new UserDateTotalListFactory();
										$udtlf->getAccrualOrphansByUserIdAndPayCodeIdAndAccrualPolicyAccountIdAndStartDateAndEndDate( $user_obj->getId(), $ap_obj->getPayCode(), $pf_obj->getAccrualPolicyAccount(), $filter_start_date, $filter_end_date );
										Debug::text( 'Orphaned UDT Rows: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
										if ( $udtlf->getRecordCount() > 0 ) {
											if ( $recalc == true ) {
												//Calculate pre-mature exceptions, so pre-mature Missing Out Punch exceptions arn't made active until they are ready.
												//Don't calculate future exceptions though.
												$cp = TTNew( 'CalculatePolicy' );
												$cp->setFlag( $flags );
												$cp->setUserObject( $user_obj );
											}

											foreach ( $udtlf as $udt_obj ) {
												Debug::text( '  UDT ID: ' . $udt_obj->getId() . ' User: ' . $user_obj->getUsername() . ' Date: ' . TTDate::getDate( 'DATE', $udt_obj->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
												if ( $recalc == true ) {
													if ( is_object( $udt_obj->getPayPeriodObject() ) AND $udt_obj->getPayPeriodObject()->getIsLocked() ) {
														echo ' Pay Period is Locked: ' . $udt_obj->getPayPeriodObject()->getID() . "... Unlocking...\n";
														$pay_period_status_ids[ $udt_obj->getPayPeriodObject()->getID() ] = $udt_obj->getPayPeriodObject()->getStatus();
														if ( $udt_obj->getPayPeriodObject()->getStatus() == 20 ) { //Closed
															$udt_obj->getPayPeriodObject()->setStatus( 30 ); //Post Adjustment
														} else {
															$udt_obj->getPayPeriodObject()->setStatus( 10 ); //Open
														}
														$udt_obj->getPayPeriodObject()->Save();
													} else {
														//echo ' Pay Period is OPEN: '. $udt_obj->getPayPeriodObject()->getID() ."... NOT Unlocking...\n";
													}

													echo '  Employee: ' . $user_obj->getFullName() . ' Date: ' . TTDate::getDate( 'DATE', $udt_obj->getDateStamp() ) . ' Accrual Account: ' . $pf_obj->getAccrualPolicyAccount() . ' recalculating...' . "\n";
													$cp->addPendingCalculationDate( $udt_obj->getDateStamp() );

													//STOP
													//break;
												} else {
													echo '  Employee: ' . $user_obj->getFullName() . ' Date: ' . TTDate::getDate( 'DATE', $udt_obj->getDateStamp() ) . ' Accrual Account: ' . $pf_obj->getAccrualPolicyAccount() . ' may need recalculating...' . "\n";
												}
											}

											if ( $recalc == true ) {
												$cp->calculate(); //This sets timezone itself.
												$cp->Save();

												//STOP
												//break 1;
											}
										}

										$after_balance = false;
										$ablf_tmp = new AccrualBalanceListFactory();
										$ablf_tmp->getByUserIdAndAccrualPolicyAccount( $user_obj->getId(), $pf_obj->getAccrualPolicyAccount() );
										if ( $ablf_tmp->getRecordCount() > 0 ) {
											foreach ( $ablf_tmp as $ab_obj_tmp ) {
												$after_balance = $ab_obj_tmp->getBalance();
											}
										}
										unset( $ablf_tmp, $ab_obj_tmp );

										Debug::text( '  Before Balance: ' . $before_balance . ' After: ' . $after_balance, __FILE__, __LINE__, __METHOD__, 10 );
										if ( $before_balance != $after_balance ) {
											echo '      Balance changed... Before: ' . TTDate::getHours( $before_balance ) . ' After: ' . TTDate::getHours( $after_balance ) . ' Accrual Account: ' . $pf_obj->getAccrualPolicyAccount() . "\n";

											//STOP
											//break 2;
										}
									}
								}
							}
						}

						if ( isset( $pay_period_status_ids ) ) {
							foreach ( $pay_period_status_ids as $pay_period_id => $status_id ) {
								$pplf = new PayPeriodListFactory();
								$pplf->getById( $pay_period_id );
								if ( $pplf->getRecordCount() == 1 ) {
									foreach ( $pplf as $pp_obj ) {
										Debug::text( 'ReLocking Pay Period ID: ' . $pay_period_id . ' ID: ' . $status_id, __FILE__, __LINE__, __METHOD__, 10 );
										echo ' ReLocking Pay Period: ' . $pay_period_id . "...\n";
										$pp_obj->setStatus( $status_id );
										$pp_obj->Save();
									}
								}
							}
						}
						unset( $pay_period_status_ids, $pay_period_id, $status_id, $pp_obj, $pplf );
					}

					if ( $dry_run == true ) {
						$aplf->FailTransaction();
					}
					$aplf->CommitTransaction();
				}
			}
		}
	}
}
echo "Done...\n";
Debug::WriteToLog();
Debug::Display();
?>
