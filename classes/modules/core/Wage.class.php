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
class Wage {
	var $user_id = null;
	var $pay_period_id = null;
	var $advance = false;

	var $user_date_total_arr = null;

	var $user_obj = null;
	var $user_tax_obj = null;
	var $user_wage_obj = null;
	var $user_pay_period_total_obj = null;
	var $pay_stub_entry_account_link_obj = null;

	var $pay_period_obj = null;
	var $pay_period_schedule_obj = null;

	var $labor_standard_obj = null;
	var $holiday_obj = null;

	/**
	 * Wage constructor.
	 * @param string $user_id       UUID
	 * @param string $pay_period_id UUID
	 */
	function __construct( $user_id, $pay_period_id ) {
		$this->user_id = $user_id;
		$this->pay_period_id = $pay_period_id;

		return true;
	}

	/**
	 * @return null
	 */
	function getUser() {
		return $this->user_id;
	}

	/**
	 * @return null
	 */
	function getPayPeriod() {
		return $this->pay_period_id;
	}

	/**
	 * @return bool
	 */
	function getAdvance() {
		if ( isset( $this->advance ) ) {
			return $this->advance;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setAdvance( $bool ) {
		$this->advance = $bool;

		return true;
	}

	//Because this class doesn't extend the original Factory class, we have to duplicate the getGenericObject() code here.

	/**
	 * @return bool|null
	 */
	function getUserObject() {
		if ( isset( $this->user_obj ) && is_object( $this->user_obj ) && $this->getUser() == $this->user_obj->getID() ) {
			return $this->user_obj;
		} else {
			$lf = TTnew( 'UserListFactory' ); /** @var UserListFactory $lf */
			$lf->getById( $this->getUser() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->user_obj = $lf->getCurrent();

				return $this->user_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodObject() {
		if ( isset( $this->pay_period_obj ) && is_object( $this->pay_period_obj ) && $this->getPayPeriod() == $this->pay_period_obj->getID() ) {
			return $this->pay_period_obj;
		} else {
			$lf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $lf */
			$lf->getById( $this->getPayPeriod() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_period_obj = $lf->getCurrent();

				return $this->pay_period_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodScheduleObject() {
		$pay_period_schedule_id = TTUUID::getZeroID();
		if ( is_object( $this->getPayPeriodObject() ) ) {
			$pay_period_schedule_id = $this->getPayPeriodObject()->getPayPeriodSchedule();
		}
		if ( isset( $this->pay_period_schedule_obj ) && is_object( $this->pay_period_schedule_obj ) && $pay_period_schedule_id == $this->pay_period_schedule_obj->getID() ) {
			return $this->pay_period_schedule_obj;
		} else {
			$lf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $lf */
			$lf->getById( $pay_period_schedule_id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_period_schedule_obj = $lf->getCurrent();

				return $this->pay_period_schedule_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		$company_id = TTUUID::getZeroID();
		if ( is_object( $this->getUserObject() ) ) {
			$company_id = $this->getUserObject()->getCompany();
		}

		if ( isset( $this->pay_stub_entry_account_link_obj ) && is_object( $this->pay_stub_entry_account_link_obj ) && $company_id == $this->pay_stub_entry_account_link_obj->getCompany() ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$lf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $lf */
			$lf->getByCompanyId( $company_id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_stub_entry_account_link_obj = $lf->getCurrent();

				return $this->pay_stub_entry_account_link_obj;
			}

			return false;
		}
	}

	/**
	 * @param $seconds
	 * @param $rate
	 * @return int|string
	 */
	function getWage( $seconds, $rate ) {
		if ( $seconds == '' || empty( $seconds ) ) {
			return 0;
		}

		if ( $rate == '' || empty( $rate ) ) {
			return 0;
		}

		return bcmul( TTDate::getHours( $seconds ), $rate );
	}

	/**
	 * @param object $user_wage_obj
	 * @return string
	 */
	function getMaximumPayPeriodWage( $user_wage_obj ) {
		if ( is_object( $user_wage_obj ) && is_object( $this->getPayPeriodScheduleObject() ) && $this->getPayPeriodScheduleObject()->getAnnualPayPeriods() > 0 ) {
			$maximum_pay_period_wage = bcdiv( $user_wage_obj->getAnnualWage(), $this->getPayPeriodScheduleObject()->getAnnualPayPeriods() );
			Debug::text( 'Absolute Maximum Pay Period (NO Advance): Wage: ' . $maximum_pay_period_wage . ' User Wage ID: ' . $user_wage_obj->getId() . ' Annual Wage: ' . $user_wage_obj->getAnnualWage() . ' Annual Pay Periods: ' . $this->getPayPeriodScheduleObject()->getAnnualPayPeriods(), __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::text( 'WARNING: Pay Period Schedule does not exist, or annual pay periods is 0...', __FILE__, __LINE__, __METHOD__, 10 );
			$maximum_pay_period_wage = 0;
		}

		return $maximum_pay_period_wage;
	}

	/**
	 * @return string
	 */
	function getPayStubAmendmentEarnings() {
		//Get pay stub amendments here.
		$psalf = TTnew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psalf */

		if ( $this->getAdvance() == true ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, true, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text( 'Pay Stub Amendment Advance Earnings: ' . $advance_pos_sum, __FILE__, __LINE__, __METHOD__, 10 );

			$full_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, true, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text( 'Pay Stub Amendment Full Earnings: ' . $full_pos_sum, __FILE__, __LINE__, __METHOD__, 10 );
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$pos_sum = $advance_pos_sum + ($full_pos_sum / 2);
			$pos_sum = bcadd( $advance_pos_sum, bcdiv( $full_pos_sum, 2 ) );
		} else {
			$pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, true, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;

		Debug::text( 'Pay Stub Amendment Total Earnings: ' . $pos_sum, __FILE__, __LINE__, __METHOD__, 10 );

		return $pos_sum;
	}

	/**
	 * @return string
	 */
	function getPayStubAmendmentDeductions() {
		//Get pay stub amendments here.
		$psalf = TTnew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psalf */

		if ( $this->getAdvance() == true ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, true, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text( 'Pay Stub Amendment Advance Deductions: ' . $advance_neg_sum, __FILE__, __LINE__, __METHOD__, 10 );

			$full_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, true, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text( 'Pay Stub Amendment Full Deductions: ' . $full_neg_sum, __FILE__, __LINE__, __METHOD__, 10 );
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$neg_sum = $advance_neg_sum + ($full_neg_sum / 2);
			$neg_sum = bcadd( $advance_neg_sum, bcdiv( $full_neg_sum, 2 ) );
		} else {
			//$pay_period_end_date =
			$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, true, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;


		Debug::text( 'Pay Stub Amendment Total Deductions: ' . $neg_sum, __FILE__, __LINE__, __METHOD__, 10 );

		return bcmul( $neg_sum, -1 );
	}

	/**
	 * @return int
	 */
	function getRawGrossWage() {
		$wage = 0;

		$udt_arr = $this->getUserDateTotalArray();
		if ( isset( $udt_arr['entries'] ) && count( $udt_arr['entries'] ) > 0 ) {
			foreach ( $udt_arr['entries'] as $udt ) {
				if ( isset( $udt['amount'] ) ) {
					$wage += $udt['amount'];
				}
			}
		}

		Debug::text( 'Raw Gross Wage: ' . $wage, __FILE__, __LINE__, __METHOD__, 10 );

		return $wage;
	}

	/**
	 * @return int
	 */
	function getGrossWage() {

		$wage = $this->getRawGrossWage();

		Debug::text( 'Gross Wage (NOT incl amendments) $' . $wage, __FILE__, __LINE__, __METHOD__, 10 );

		return $wage;
	}

	/**
	 * @return array|bool|null
	 */
	function getUserDateTotalArray() {
		if ( isset( $this->user_date_total_arr ) ) {
			return $this->user_date_total_arr;
		}

		//If the user date total array isn't set, set it now, and return its value.
		return $this->setUserDateTotalArray();
		//return FALSE;
	}

	/**
	 * @return array|bool
	 */
	function setUserDateTotalArray() {
		//Loop through unique UserDateTotal rows... Adding entries to pay stubs.
		$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$udtlf->getByUserIdAndPayPeriodIdAndEndDate( $this->getUser(), $this->getPayPeriod(), $this->getPayPeriodObject()->getEndDate() );

		$calculate_salary = false;

		$dock_absence_time = 0;
		$paid_absence_time = 0;
		$dock_absence_amount = 0;
		$paid_absence_amount = 0;
		$prev_wage_effective_date = 0;
		$paid_absence_amount_arr = [];
		$reduce_salary_absence_amount_arr = [];
		$salary_regular_time = [];
		$dock_absence_amount_arr = [];
		$ret_arr = [];
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $udt_obj ) {
				Debug::text( 'User Total Row... Object Type: ' . $udt_obj->getObjectType() . ' PayCode ID: ' . $udt_obj->getPayCode() . ' Amount: ' . $udt_obj->getTotalTimeAmount() . ' Hourly Rate: ' . $udt_obj->getColumn( 'hourly_rate' ) . ' Pay Code Type: ' . $udt_obj->getColumn( 'pay_code_type_id' ) . ' User Wage ID: ' . $udt_obj->getColumn( 'user_wage_id' ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $udt_obj->getColumn( 'pay_code_type_id' ) == 10 ) { //Paid
					if ( $udt_obj->getObjectType() == 25 ) { //Absence
						Debug::text( 'User Total Row... Absence Time: ' . $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10 );

						if ( is_object( $udt_obj->getPayCodeObject() )
								&& ( $udt_obj->getPayCodeObject()->getType() == 10 || $udt_obj->getPayCodeObject()->getType() == 12 )
								&& $udt_obj->getPayCodeObject()->getPayStubEntryAccountID() != '' ) { //Paid
							Debug::text( 'Paid Absence Time: ' . $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10 );

							$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountID();
							$total_time = $udt_obj->getTotalTime();
							$rate = $udt_obj->getColumn( 'hourly_rate' );
							$amount = $udt_obj->getTotalTimeAmount();

							//Debug::text('Paid Absence Info: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
							Debug::text( 'cPay Stub Entry Account ID: ' . $pay_stub_entry . ' Amount: ' . $amount . ' Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );

							$paid_absence_time = bcadd( $paid_absence_time, $udt_obj->getTotalTime() );
							$paid_absence_amount = bcadd( $paid_absence_amount, $amount );

							//Make sure we add the amount below. Incase there are two or more
							//entries for a paid absence in the same user_wage_id on one pay stub.
							if ( !isset( $paid_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] ) ) {
								$paid_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] = 0;
							}
							$paid_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] = bcadd( $paid_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )], $amount );

							//Some paid absences are over and above employees salary, so we need to track them separately.
							//So we only reduce the salary of the amount of regular paid absences, not "Paid (Above Salary)" absences.
							if ( !isset( $reduce_salary_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] ) ) {
								$reduce_salary_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] = 0;
							}
							if ( $udt_obj->getColumn( 'pay_code_type_id' ) == 10 ) {
								$reduce_salary_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] = bcadd( $reduce_salary_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )], $amount );
							}
						}
					} else {
						//Check if they are a salary user...
						//Use WORKED time to calculate regular time. Not just regular time.
						//user_wage_id is only needed for default wages, so it doesn't take into account pay formulas at all.
						if ( $udt_obj->getColumn( 'user_wage_type_id' ) > 10 ) { //Salaried
							//Salary
							Debug::text( 'Strict Salary Wage: Reduce Regular Pay By: Dock Time: ' . $dock_absence_time . ' and Paid Absence: ' . $paid_absence_time, __FILE__, __LINE__, __METHOD__, 10 );

							$calculate_salary = true;

							if ( !isset( $salary_regular_time[$udt_obj->getColumn( 'user_wage_id' )] ) ) {
								$salary_regular_time[$udt_obj->getColumn( 'user_wage_id' )] = 0;
							}

							//Only include regular time units in salary calculation.
							if ( $udt_obj->getObjectType() == 20 ) { //Regular Time
								$salary_regular_time[$udt_obj->getColumn( 'user_wage_id' )] += $udt_obj->getTotalTime();
							}
						} else {
							//Hourly
							Debug::text( 'Hourly Wage', __FILE__, __LINE__, __METHOD__, 10 );
							$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountId();
							$total_time = $udt_obj->getTotalTime();
							$rate = $udt_obj->getColumn( 'hourly_rate' );
							$amount = $udt_obj->getTotalTimeAmount();
							Debug::text( 'aPay Stub Entry Account ID: ' . $pay_stub_entry . ' Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				} else if ( $udt_obj->getColumn( 'pay_code_type_id' ) == 12 ) { //Paid Above
					//This is typically but not always overtime/premium time.
					if ( is_object( $udt_obj->getPayCodeObject() ) && $udt_obj->getColumn( 'hourly_rate' ) != 0 ) {
						Debug::text( 'Paid (Above Salary) Time... Rate: ' . $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__, 10 );
						$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountId();
						$total_time = $udt_obj->getTotalTime();
						$rate = $udt_obj->getColumn( 'hourly_rate' );
						$amount = $udt_obj->getTotalTimeAmount();
						Debug::text( 'bPay Stub Entry Account ID: ' . $pay_stub_entry . ' Amount: ' . $amount . ' Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::text( '  NOT Paid Time Policy...', __FILE__, __LINE__, __METHOD__, 10 );
					}
//				//We shouldn't do anything with UNPAID pay codes. If there needs to be Tax/Deduction calculations based on unpaid timesheet information, they should be marked as "PAID" but have a $0 hourly rate instead.
//				//PayStubFactory will still accept $0 amount entries, allow calculations to be made on them, then remove them at the last second though.
//				} elseif ( $udt_obj->getColumn('pay_code_type_id') == 20 ) { //UnPaid
//					//Pass through even unpaid time to pay stubs, so Tax/Deduction records can base calculations on just units/hours if needed to create other earnings amounts.
//					if ( is_object( $udt_obj->getPayCodeObject() ) ) {
//						Debug::text('UnPaid Time... Total Time: '. $udt_obj->getTotalTime() .' Rate: '. $udt_obj->getColumn('hourly_rate'), __FILE__, __LINE__, __METHOD__, 10);
//						$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountId();
//						$total_time = $udt_obj->getTotalTime();
//						$rate = $udt_obj->getColumn('hourly_rate');
//						$amount = $udt_obj->getTotalTimeAmount();
//						Debug::text('cPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
//					} else {
//						Debug::text('  NOT UDT Object...', __FILE__, __LINE__, __METHOD__, 10);
//					}
				} else if ( $udt_obj->getColumn( 'pay_code_type_id' ) == 30 ) { //Dock
					$dock_absence_time = bcadd( $dock_absence_time, $udt_obj->getTotalTime() );
					$rate = $udt_obj->getColumn( 'hourly_rate' );
					$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
					$dock_absence_amount = bcadd( $dock_absence_amount, $amount );

					//Make sure we account for multiple dock absence policies, for the same wage entry in the same pay period.
					if ( isset( $dock_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] ) ) {
						$dock_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] += $amount;
					} else {
						$dock_absence_amount_arr[$udt_obj->getColumn( 'user_wage_id' )] = $amount;
					}

					Debug::text( 'DOCK Absence Time.. Adding: ' . $udt_obj->getTotalTime() . ' Total: ' . $dock_absence_time . ' Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );
					unset( $rate );
				}

				if ( isset( $pay_stub_entry ) && $pay_stub_entry != '' ) {
					Debug::text( 'zPay Stub Entry Account ID: ' . $pay_stub_entry . ' Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
					$ret_arr['entries'][] = [
							'user_wage_id'   => $udt_obj->getColumn( 'user_wage_id' ),
							'pay_stub_entry' => $pay_stub_entry,
							'total_time'     => $total_time,
							'amount'         => $amount,
							'rate'           => $rate,
							'description'    => null,
					];
				}
				unset( $pay_stub_entry, $amount, $total_time, $rate );
			}

			if ( $calculate_salary == true ) {
				//When the employee is salary and their wage changes in the middle of the pay period and they don't have any regular time worked
				//in any one of the periods that either wage is effective, the period without any regular time was not being paid before.
				//Therefore we moved the salary calcuations to the very end and if there is any regular time in the entire pay period
				//we simply loop through all salaried wages and calculate the pro-rated amounts. Even if no regular time exists in one of the wage periods.
				Debug::text( 'Calculating Salary...', __FILE__, __LINE__, __METHOD__, 10 );

				//Get all wages that apply in this period so we can determine pro-rating for salaries.
				$uwlf = TTNew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
				$uwlf->getDefaultWageGroupByUserIdAndStartDateAndEndDate( $this->getUser(), $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() ); //ORDER BY effective_date desc
				if ( $uwlf->getRecordCount() > 0 ) {
					foreach ( $uwlf as $uw_obj ) {
						$description = null;
						if ( $uw_obj->getType() != 10 ) {
							if ( isset( $dock_absence_amount_arr[$uw_obj->getID()] ) ) {
								$dock_absence_wage = abs( $dock_absence_amount_arr[$uw_obj->getID()] ); //Make sure the dock absence wage is always a positive, since we subtract is below.
							} else {
								$dock_absence_wage = 0;
							}
							if ( isset( $reduce_salary_absence_amount_arr[$uw_obj->getID()] ) ) {
								$paid_absence_wage = abs( $reduce_salary_absence_amount_arr[$uw_obj->getID()] ); //Make sure the dock absence wage is always a positive, since we subtract is below.
							} else {
								$paid_absence_wage = 0;
							}
							Debug::text( 'Wage ID: ' . $uw_obj->getID() . ' Dock Absence Wage: ' . $dock_absence_wage . ' Paid Absence Wage: ' . $paid_absence_wage, __FILE__, __LINE__, __METHOD__, 10 );

							$maximum_wage_salary = UserWageFactory::proRateSalary( $this->getMaximumPayPeriodWage( $uw_obj ), $uw_obj->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getHireDate(), $this->getUserObject()->getTerminationDate() );

							$amount = bcsub( $maximum_wage_salary, bcadd( $dock_absence_wage, $paid_absence_wage ) );
							//Include time if we have it, otherwise use 0.
							$total_time = ( isset( $salary_regular_time[$uw_obj->getID()] ) ) ? $salary_regular_time[$uw_obj->getID()] : 0; //Dont minus dock/paid absence time. Because its already not included.
							$rate = null;
							$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();
							unset( $dock_absence_wage, $paid_absence_wage );

							$salary_dates = UserWageFactory::proRateSalaryDates( $uw_obj->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getHireDate(), $this->getUserObject()->getTerminationDate() );
							if ( is_array( $salary_dates ) && isset( $salary_dates['percent'] ) && $salary_dates['percent'] < 100 ) {
								$description = TTi18n::getText( 'Prorate Salary' ) . ': ' . TTDate::getDate( 'DATE', $salary_dates['start_date'] ) . ' - ' . TTDate::getDate( 'DATE', $salary_dates['end_date'] ) . ' (' . $salary_dates['percent'] . '%)';
							}

							if ( isset( $pay_stub_entry ) && $pay_stub_entry != '' ) {
								Debug::text( '  Pay Stub Entry Account ID: ' . $pay_stub_entry . ' Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
								$ret_arr['entries'][] = [
										'user_wage_id'   => $udt_obj->getColumn( 'user_wage_id' ),
										'pay_stub_entry' => $pay_stub_entry,
										'total_time'     => $total_time,
										'amount'         => $amount,
										'rate'           => $rate,
										'description'    => $description,
								];
							}


							unset( $pay_stub_entry, $amount, $total_time, $rate );
						}

						//Must go outside the $uw_obj->getType() != 10 check, so we can properly switch from Salary to Hourly in the middle of a PP.
						$prev_wage_effective_date = $uw_obj->getEffectiveDate();
					}
				}
				unset( $uwlf, $uw_obj );
			}
		} else {
			Debug::text( 'NO UserDate Total entries found.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$ret_arr['other']['paid_absence_time'] = $paid_absence_time;
		$ret_arr['other']['dock_absence_time'] = $dock_absence_time;

		$ret_arr['other']['paid_absence_amount'] = $paid_absence_amount;
		$ret_arr['other']['dock_absence_amount'] = $dock_absence_amount;

		if ( empty( $ret_arr ) == false ) {
			Debug::Arr( $ret_arr, 'UserDateTotal Array', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->user_date_total_arr = $ret_arr;
		}

		return false;
	}
}

?>