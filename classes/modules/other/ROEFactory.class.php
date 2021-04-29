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
 * @package Modules\Other
 */
class ROEFactory extends Factory {
	protected $table = 'roe';
	protected $pk_sequence_name = 'roe_id_seq'; //PK Sequence name

	var $user_obj = null;
	var $pay_stub_entry_account_link_obj = null;
	var $initial_pay_period_earnings = null;
	var $pay_period_earnings = null;

	protected $system_status_ids = [ 200, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359 ]; //These all special types reserved for system use only.

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				//If the user edits an existing ROE, it will default the status back to PENDING, then they have to click "eFile" again to switch it to Submitted (ROE WEB) or Submitted (TimeTrex)
				$retval = [
						10  => TTi18n::gettext( 'Pending' ), //Pending submission to Service Canada
						100 => TTi18n::gettext( 'Submitted (Paper)' ),
						200 => TTi18n::gettext( 'Submitted (ROE WEB)' ), //This gets set automatically when they click eFile icon on any ROE.
						//350 => TTi18n::gettext('Submitted (TimeTrex - Pending)'), //TimeTrex Full Service Payroll - This gets set automatically when they click eFile icon on any ROE.
						//351 => TTi18n::gettext('Submitted (TimeTrex - Error)'),
						//352 => TTi18n::gettext('Submitted (TimeTrex - Rejected)'),
						//353 => TTi18n::gettext('Submitted (TimeTrex - ...)'),
						359 => TTi18n::gettext( 'Submitted (TimeTrex)' ), //Change to TimeTrex - Confirmed eventually.
				];
				break;
			case 'user_status':
				$retval = array_diff_key( $this->getOptions( 'status' ), array_flip( $this->system_status_ids ) );
				break;
			case 'code': //See: https://www.unemploymentcanada.ca/record-of-employment-reason-for-issuing-roe/
				$retval = [
						'A00' => TTi18n::gettext( '(A) Shortage of work / End of Contract or Season' ),
						'A01' => TTi18n::gettext( '(A) Employer bankruptcy or receivership' ),
						'B00' => TTi18n::gettext( '(B) Strike Or Lockout' ),
						//'C00'		=> TTi18n::gettext('(C) Return to School'), //Replaced by E03
						'D00' => TTi18n::gettext( '(D) Illness or Injury' ),
						'E00' => TTi18n::gettext( '(E) Quit' ),
						'E02' => TTi18n::gettext( '(E) Quit / Follow spouse' ),
						'E03' => TTi18n::gettext( '(E) Quit / Return to school' ),
						'E04' => TTi18n::gettext( '(E) Quit / Health reasons' ),
						'E05' => TTi18n::gettext( '(E) Quit / Voluntary retirement' ),
						'E06' => TTi18n::gettext( '(E) Quit / Take another job' ),
						'E09' => TTi18n::gettext( '(E) Quit / Employer relocation' ),
						'E10' => TTi18n::gettext( '(E) Quit / Care for a dependant' ),
						'E11' => TTi18n::gettext( '(E) Quit / To become self-employed' ),
						'F00' => TTi18n::gettext( '(F) Maternity' ),
						'G00' => TTi18n::gettext( '(G) Retirement' ),
						'G07' => TTi18n::gettext( '(G) Retirement / Approved workforce reduction' ),
						'H00' => TTi18n::gettext( '(H) Work Sharing' ),
						'J00' => TTi18n::gettext( '(J) Apprentice Training' ),
						'K00' => TTi18n::gettext( '(K) Other' ),
						'K12' => TTi18n::gettext( '(K) Other / Change of payroll frequency' ),
						'K13' => TTi18n::gettext( '(K) Other / Change of ownership' ),
						'K14' => TTi18n::gettext( '(K) Other / Requested by Employment Insurance' ),
						'K15' => TTi18n::gettext( '(K) Other / Canadian Forces – Queen’s Regulations/Orders' ),
						'K16' => TTi18n::gettext( '(K) Other / At the employee’s request' ),
						'K17' => TTi18n::gettext( '(K) Other / Change of Service Provider' ),
						'M00' => TTi18n::gettext( '(M) Dismissal' ),
						'M08' => TTi18n::gettext( '(M) Dismissal / Terminated within probationary period' ),
						'N00' => TTi18n::gettext( '(N) Leave of Absence' ),
						'P00' => TTi18n::gettext( '(P) Parental' ),
						'Z00' => TTi18n::gettext( '(Z) Compassionate Care/Parents of Critically Ill Children' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-status'                     => TTi18n::gettext( 'Status' ),
						'-1010-first_name'                 => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'                  => TTi18n::gettext( 'Last Name' ),
						'-1025-insurable_absence_policies' => TTi18n::gettext( 'Insurable Absence Policies' ),
						'-1030-insurable_earnings'         => TTi18n::gettext( 'Insurable Earnings (Box 15B)' ),
						'-1040-vacation_pay'               => TTi18n::gettext( 'Vacation Pay (Box 17A)' ),
						'-1050-code'                       => TTi18n::gettext( 'Reason' ),
						'-1060-pay_period_type'            => TTi18n::gettext( 'Pay Period Type' ),
						'-1070-first_date'                 => TTi18n::gettext( 'First Day Worked' ),
						'-1080-last_date'                  => TTi18n::gettext( 'Last Day For Which Paid' ),
						'-1100-pay_period_end_date'        => TTi18n::gettext( 'Final Pay Period Ending Date' ),
						'-1120-recall_date'                => TTi18n::gettext( 'Expected Date of Recall' ),
						'-1150-serial'                     => TTi18n::gettext( 'Serial No' ),
						'-1170-comments'                   => TTi18n::gettext( 'Comments' ),
						'-1200-release_accruals'           => TTi18n::gettext( 'Release All Accruals' ),
						'-1220-generate_pay_stub'          => TTi18n::gettext( 'Generate Final Pay Stub' ),
						'-1230-insurable_hours'            => TTi18n::gettext( 'Insurable Hours' ),

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
						'status',
						'first_name',
						'last_name',
						'first_date',
						'last_date',
						'code',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                              => 'ID',
				'user_id'                         => 'User',
				'status_id'                       => 'Status',
				'status'                          => false,
				'first_name'                      => false,
				'last_name'                       => false,
				'pay_period_type_id'              => 'PayPeriodType',
				'pay_period_type'                 => false,
				'code_id'                         => 'Code',
				'code'                            => false,
				'first_date'                      => 'FirstDate',
				'last_date'                       => 'LastDate',
				'pay_period_end_date'             => 'PayPeriodEndDate',
				'final_pay_stub_end_date'         => 'FinalPayStubEndDate',
				'final_pay_stub_transaction_date' => 'FinalPayStubTransactionDate',
				'recall_date'                     => 'RecallDate',
				'insurable_hours'                 => 'InsurableHours',
				'insurable_earnings'              => 'InsurableEarnings',
				'vacation_pay'                    => false,
				'serial'                          => 'Serial',
				'comments'                        => 'Comments',

				'release_accruals'  => false,
				'generate_pay_stub' => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return null
	 */
	function getUserObject() {
		if ( is_object( $this->user_obj ) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;
			}
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		if ( is_object( $this->pay_stub_entry_account_link_obj ) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();

				return $this->pay_stub_entry_account_link_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		return $this->setGenericDataValue( 'status_id', (int)trim( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getPayPeriodType() {
		return $this->getGenericDataValue( 'pay_period_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayPeriodType( $value ) {
		$value = (int)trim( $value );
		Debug::Text( 'Type ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'pay_period_type_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getCode() {
		return (string)$this->getGenericDataValue( 'code_id' );//Should not be cast to INT.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCode( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'code_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFirstDate() {
		return $this->getGenericDataValue( 'first_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setFirstDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'first_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastDate() {
		return $this->getGenericDataValue( 'last_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		//Include the entire day.
		//$epoch = TTDate::getBeginDayEpoch( $epoch ) + (86400-120);
		$value = TTDate::getEndDayEpoch( $value );

		return $this->setGenericDataValue( 'last_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriodEndDate() {
		return $this->getGenericDataValue( 'pay_period_end_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setPayPeriodEndDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'pay_period_end_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRecallDate() {
		return $this->getGenericDataValue( 'recall_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setRecallDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'recall_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFinalPayStubEndDate() {
		return $this->getGenericDataValue( 'final_pay_stub_end_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setFinalPayStubEndDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'final_pay_stub_end_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFinalPayStubTransactionDate() {
		return $this->getGenericDataValue( 'final_pay_stub_transaction_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setFinalPayStubTransactionDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'final_pay_stub_transaction_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getInsurableHours() {
		return $this->getGenericDataValue( 'insurable_hours' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInsurableHours( $value ) {
		$value = trim( $value );
		if ( $value == '' || $value == null ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'insurable_hours', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getInsurableEarnings() {
		return $this->getGenericDataValue( 'insurable_earnings' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInsurableEarnings( $value ) {
		$value = trim( $value );
		if ( $value == '' || $value == null ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'insurable_earnings', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getVacationPay() {
		return $this->getGenericDataValue( 'vacation_pay' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setVacationPay( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'vacation_pay', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSerial() {
		return $this->getGenericDataValue( 'serial' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSerial( $value ) {
		$value = trim( $value );

		//Don't force serial numbers anymore, as online ROEs don't require them.
		return $this->setGenericDataValue( 'serial', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getComments() {
		return $this->getGenericDataValue( 'comments' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setComments( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'comments', $value );
	}

	/**
	 * @return mixed
	 */
	function getInsurableHoursReportPayPeriods() {
		return $this->getInsurableEarningsReportPayPeriods( '15c' );
	}

	/**
	 * @param string $line
	 * @return mixed
	 */
	function getInsurableEarningsReportPayPeriods( $line = '15b' ) {
		if ( $line == '15b' ) {
			//Line 15b is a total of insurable earnings over a shorter period than displayed in 15b often.
			$report_period_arr = [
					10  => 27, //'Weekly',
					20  => 14, //'Bi-Weekly',
					30  => 13, //'Semi-Monthly',
					40  => 7, //'Monthly + Advance',
					50  => 7, //'Monthly'
					100 => 27, //'Weekly',
					200 => 14, //'Bi-Weekly',
			];
		} else {
			//15a & 15c requires more pay periods data than is used to total up 15b.
			$report_period_arr = [
					10  => 53, //'Weekly',
					20  => 27, //'Bi-Weekly',
					30  => 25, //'Semi-Monthly',
					40  => 13, //'Monthly + Advance',
					50  => 13, //'Monthly'
					100 => 53, //'Weekly',
					200 => 27, //'Bi-Weekly',
			];
		}

		if ( isset( $report_period_arr[$this->getPayPeriodType()] ) ) {
			$retval = $report_period_arr[$this->getPayPeriodType()];
		} else {
			//Likely a manual pay period, try to determine based off annual pay periods.
			Debug::Text( '  WARNING: Unable to determine pay period schedule type...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}

		Debug::Text( 'Pay Periods: ' . $retval . ' Line: ' . $line . ' Type: ' . $this->getPayPeriodType(), __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param $pay_periods
	 * @return bool
	 */
	function getInsurablePayPeriodStartDate( $pay_periods ) {
		Debug::Text( 'Pay Periods to Consider: ' . $pay_periods, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'First Day Worked: ' . TTDate::getDate( 'DATE+TIME', $this->getFirstDate() ) . ' Last Worked Day: ' . TTDate::getDate( 'DATE+TIME', $this->getLastDate() ) . ' Final Pay Stub End Date: ' . TTDate::getDate( 'DATE+TIME', $this->getFinalPayStubEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		$start_date = false;

		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pay_period_obj = $pplf->getByUserIdAndEndDate( $this->getUser(), $this->getLastDate() )->getCurrent();
		Debug::Text( 'Pay Period ID: ' . $pay_period_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$pplf->getByPayPeriodScheduleId( $pay_period_obj->getPayPeriodSchedule(), null, null, null, [ 'start_date' => 'desc' ] );
		$i = 1;
		foreach ( $pplf as $pay_period ) {
			//Make sure if there are more pay periods inserted AFTER the last day, we DO NOT include those in the count.
			Debug::Text( 'Pay Period: Start Date: ' . TTDate::getDate( 'DATE+TIME', $pay_period->getStartDate() ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $pay_period->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getFirstDate() <= $pay_period->getEndDate() && $this->getFinalPayStubEndDate() >= $pay_period->getStartDate() ) {
				Debug::Text( $i . '.	Including Pay Period ID: ' . $pay_period->getID(), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $this->getLastDate() < $pay_period->getStartDate() ) {
					$tmp_after_last_date = true;
				} else {
					$tmp_after_last_date = false;
				}

				//Need to find pay periods with no earnings, so add pay periods with no earnings first, then overwrite them with earnings later.
				$this->initial_pay_period_earnings[$pay_period->getID()] = [ 'amount' => false, 'units' => false, 'start_date' => $pay_period->getStartDate(), 'end_date' => $pay_period->getEndDate(), 'after_last_date' => $tmp_after_last_date ];

				//If there aren't enough pay periods yet, use what we have...
				$start_date = $pay_period->getStartDate();

				if ( $i == $pay_periods ) {
					break;
				}

				//Only count pay periods that are between the First Day worked and Last Day For Which Paid, thereby ignoring pay periods after the Last Day For Which Paid.
				//This is important when displaying the maximum number of pay periods (ie: 27/53) and there is a gap between the Final Pay Period Ending Date and the Final Pay Stub End Date.
				if ( $tmp_after_last_date == false ) {
					$i++;
				}
			}
		}

		Debug::Text( 'Pay Period Report Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );

		//Debug::Arr($this->initial_pay_period_earnings, 'Initilized Pay Period Earnings Array...', __FILE__, __LINE__, __METHOD__, 10);

		return $start_date;
	}

	/**
	 * @return bool
	 */
	function getEnableReCalculate() {
		if ( isset( $this->recalc ) ) {
			return $this->recalc;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableReCalculate( $bool ) {
		$this->recalc = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableReleaseAccruals() {
		if ( isset( $this->release_accruals ) ) {
			return $this->release_accruals;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableReleaseAccruals( $bool ) {
		$this->release_accruals = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableGeneratePayStub() {
		if ( isset( $this->generate_pay_stub ) ) {
			return $this->generate_pay_stub;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableGeneratePayStub( $bool ) {
		$this->generate_pay_stub = $bool;

		return true;
	}

	/**
	 * @return mixed
	 */
	function calculateFirstDate() {
		$user_id = $this->getUser();

		//get User data for hire date
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		//Is there a previous ROE? If so, find first shift back since ROE was issued.
		$rlf = TTnew( 'ROEListFactory' ); /** @var ROEListFactory $rlf */
		$rlf->getLastROEByUserId( $user_id );
		if ( $rlf->getRecordCount() > 0 ) {
			$roe_obj = $rlf->getCurrent();

			//getNextByUserIdAndObjectTypeAndEpoch() below uses >=, so make sure the first date is one day past the last date.
			//  Otherwise if the employee worked on the last day worked of the previous ROE, that will be the same day used for the First Date, which is not correct as its being included twice.
			$first_date = ( $roe_obj->getLastDate() + 86400 );
			Debug::Text( 'Previous ROE Last Date: ' . TTDate::getDate( 'DATE+TIME', $roe_obj->getLastDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( !isset( $first_date ) || $first_date == '' ) {
			$first_date = $user_obj->getHireDate();
		}

		//Find first pay stub after this date, to ensure they actually did get paid and have insurable earnings. Essentially the first pay period can't be $0.00.
		$pslf = TTnew( 'PayStubListFactory' );
		$pslf->getFirstPayStubByUserIdAndStartDate( $user_id, $first_date, 1 );
		if ( $pslf->getRecordCount() > 0 ) {
			if ( $pslf->getCurrent()->getStartDate() > $first_date ) {
				$first_date = $pslf->getCurrent()->getStartDate();
			}
		}

		$udlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udlf */
		$udlf->getNextByUserIdAndObjectTypeAndEpoch( $user_id, [ 10 ], $first_date ); //10=Worked time only. This needs to include punches and manual timesheet entries. FIXME: Should probably include insurable absence policies too like calculateLastDate()
		if ( $udlf->getRecordCount() > 0 ) {
			if ( $udlf->getCurrent()->getDateStamp() > $first_date ) {
				$first_date = $udlf->getCurrent()->getDateStamp();
			}
		}

		Debug::Text( 'First Date: ' . TTDate::getDate( 'DATE+TIME', $first_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $first_date;
	}

	/**
	 * @return int
	 */
	function calculateLastDate() {
		$user_id = $this->getUser();

		$setup_data = $this->getSetupData();

		$udlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udlf */

		//10=Worked time. This needs to include punches and manual timesheet entries.
		// It also needs to include insurable absence policies from the Form Setup since its last day worked or received insurable earnings.
		$udlf->getLastByUserIdAndObjectTypeOrAbsencePolicy( $user_id, [ 10 ], $setup_data['absence_policy_ids'], 1 );

		if ( $udlf->getRecordCount() > 0 ) {
			$ud_obj = $udlf->getCurrent();
			$last_date = $ud_obj->getDateStamp();
		} else {
			$last_date = TTDate::getTime();
		}

		Debug::Text( 'Last Worked Date: ' . TTDate::getDate( 'DATE+TIME', $last_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $last_date;
	}

	/**
	 * @return bool
	 */
	function calculateFinalPayStubEndDate() {
		$user_id = $this->getUser();

		if ( is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getStatus() != 10 && $this->getUserObject()->getTerminationDate() != '' ) {
				$end_date = $this->getUserObject()->getTerminationDate();
			} else {
				$end_date = time();
			}

			$plf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $plf */
			$plf->getByUserIdAndEndDate( $user_id, $end_date );
			if ( $plf->getRecordCount() > 0 ) {
				$pp_obj = $plf->getCurrent();
				Debug::Text( 'PayPeriod ID: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $this->getUserObject()->getId(), $this->getUserObject()->getCompany(), [ $pp_obj->getId() ] );
				if ( $pslf->getRecordCount() > 0 ) {
					Debug::Text( 'PS ID: ' . $pslf->getCurrent()->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

					return $pslf->getCurrent()->getEndDate();
				} else {
					Debug::Text( 'PayPeriod ID: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

					return $plf->getCurrent()->getEndDate();
				}
			}
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function calculateFinalPayStubTransactionDate() {
		$user_id = $this->getUser();

		if ( is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getStatus() != 10 && $this->getUserObject()->getTerminationDate() != '' ) {
				$end_date = $this->getUserObject()->getTerminationDate();
			} else {
				$end_date = time();
			}

			$plf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $plf */
			$plf->getByUserIdAndEndDate( $user_id, $end_date );
			if ( $plf->getRecordCount() > 0 ) {
				$pp_obj = $plf->getCurrent();
				Debug::Text( 'PayPeriod ID: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

				//This is actually needs to be the end date of pay period, not the final pay stub transaction date.
				return $pp_obj->getEndDate();

//				$pslf = TTnew('PayStubListFactory'); /** @var PayStubListFactory $pslf */
//				$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $this->getUserObject()->getId(), $this->getUserObject()->getCompany(), array( $pp_obj->getId() ) );
//				if ( $pslf->getRecordCount() > 0 ) {
//					return $pslf->getCurrent()->getTransactionDate();
//				} else {
//					return time();
//				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isFinalPayStubExists() {
		$user_id = $this->getUser();

		if ( is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getStatus() != 10 && $this->getUserObject()->getTerminationDate() != '' ) {
				$end_date = $this->getUserObject()->getTerminationDate();
			} else {
				$end_date = time();
			}

			$plf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $plf */
			$plf->getByUserIdAndEndDate( $user_id, $end_date );
			if ( $plf->getRecordCount() > 0 ) {
				$pp_obj = $plf->getCurrent();
				Debug::Text( 'PayPeriod ID: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $this->getUserObject()->getId(), $this->getUserObject()->getCompany(), [ $pp_obj->getId() ] );
				if ( $pslf->getRecordCount() > 0 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param int $date EPOCH
	 * @return array
	 */
	function calculatePayPeriodType( $date ) {
		$user_id = $this->getUser();

		$plf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $plf */
		$pay_period_obj = $plf->getByUserIdAndEndDate( $user_id, $date )->getCurrent();

		$annual_pay_periods_days = null;
		$pay_period_type_id = false;
		if ( is_object( $pay_period_obj->getPayPeriodScheduleObject() ) ) {
			$pay_period_type_id = $pay_period_obj->getPayPeriodScheduleObject()->getType();

			$annual_pay_periods_per_type = $pay_period_obj->getPayPeriodScheduleObject()->getOptions( 'annual_pay_periods_per_type' );
			$annual_pay_periods_per_type_reversed = array_flip( $annual_pay_periods_per_type );

			$annual_pay_periods_days_options = $pay_period_obj->getPayPeriodScheduleObject()->getOptions( 'annual_pay_periods_maximum_days' );
			if ( isset( $annual_pay_periods_per_type_reversed[$pay_period_type_id] ) && isset( $annual_pay_periods_days_options[$annual_pay_periods_per_type_reversed[$pay_period_type_id]] ) ) {
				$annual_pay_periods_days = $annual_pay_periods_days_options[$annual_pay_periods_per_type_reversed[$pay_period_type_id]];
			}

			if ( $pay_period_type_id == 5 ) { //5=Manual
				$annual_pay_periods = (int)$pay_period_obj->getPayPeriodScheduleObject()->getAnnualPayPeriods();

				if ( isset( $annual_pay_periods_per_type[$annual_pay_periods] ) ) {
					$pay_period_type_id = $annual_pay_periods_per_type[$annual_pay_periods];
				} else {
					$pay_period_type_id = false;
				}
				Debug::Text( '  Found Manual Pay Period Schedule, detecting pay period schedule of type_id: ' . $pay_period_type_id . ' based on annual PPs of: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		if ( $pay_period_type_id == false ) {
			$pay_period_type_id = 10; //Default to Weekly at the very least, so we don't trigger a validation error on FALSE that doesn't make sense to the user.
			Debug::Text( '  Defaulting to Weekly PP schedule, as nothing else was found...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return [ 'pay_period_type_id' => $pay_period_type_id, 'pay_period_start_date' => $pay_period_obj->getStartDate(), 'pay_period_end_date' => $pay_period_obj->getEndDate(), 'pay_period_maximum_days' => $annual_pay_periods_days ];
	}

	/**
	 * @return bool
	 */
	function getSetupData() {
		//FIXME: Alert the user if they don't have enough information in TimeTrex to get accurate values.
		//Get insurable hours, earnings, and vacation pay now that the final pay stub is generated
		$ugdlf = TTnew( 'UserGenericDataListFactory' ); /** @var UserGenericDataListFactory $ugdlf */
		$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), $this->getTable() );
		if ( $ugdlf->getRecordCount() > 0 ) {
			Debug::Text( 'Found Company Form Setup!', __FILE__, __LINE__, __METHOD__, 10 );
			$ugd_obj = $ugdlf->getCurrent();
			$setup_data = $ugd_obj->getData();
		}
		unset( $ugd_obj );

		if ( isset( $setup_data ) ) {
			//var_dump($setup_data);
			if ( !isset( $setup_data['insurable_earnings_psea_ids'] ) ) {
				$setup_data['insurable_earnings_psea_ids'] = $this->getPayStubEntryAccountLinkObject()->getTotalGross();
			}

			if ( !isset( $setup_data['absence_policy_ids'] ) ) {
				$setup_data['absence_policy_ids'] = [];
			}

			return $setup_data;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function combinePostTerminationPayPeriods( $data ) {

		$retarr = [];
		if ( is_array( $data ) && count( $data ) > 0 ) {
			$tmp_data = array_reverse( $data, true );
			$prev_pay_period_id = false;

			foreach ( $tmp_data as $pay_period_id => $pp_data ) {
				if ( $pp_data['after_last_date'] == true && isset( $retarr[$prev_pay_period_id] ) ) {
					$retarr[$prev_pay_period_id]['amount'] += $pp_data['amount'];
					$retarr[$prev_pay_period_id]['units'] += $pp_data['units'];
					$retarr[$prev_pay_period_id]['pay_period_ids'][] = $pay_period_id;
					Debug::Text( 'Combining post Termination Pay Period: ' . $pay_period_id . ' with: ' . $prev_pay_period_id, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					$retarr[$pay_period_id] = $pp_data;
					$prev_pay_period_id = $pay_period_id;
				}
			}
			$retarr = array_reverse( $retarr, true );
			//Debug::Arr($retarr, 'Combined Final Pay Period', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$retarr = $data;
		}

		return $retarr;
	}

	/**
	 * @param string $line
	 * @return bool|null
	 */
	function getInsurableEarningsByPayPeriod( $line = '15c' ) {
		if ( $this->pay_period_earnings !== null ) {
			return $this->pay_period_earnings;
		}

		$setup_data = $this->getSetupData();
		$insurable_earnings_start_date = $this->getInsurablePayPeriodStartDate( $this->getInsurableEarningsReportPayPeriods( $line ) );
		Debug::Text( 'Getting earnings for line: ' . $line . ' Start Date: ' . TTDate::getDate( 'DATE', $insurable_earnings_start_date ) . ' Last Date: ' . TTDate::getDate( 'DATE', $this->getLastDate() ) . ' Final Pay Stub End Date: ' . TTDate::getDate( 'DATE', $this->getFinalPayStubEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		$pay_periods_with_earnings = 0;

		//Don't include YTD adjustments in ROE totals,
		//As the proper way is to generate ROEs from their old system and ROEs from TimeTrex, and issue both to the employee.
		//Since its unlikely that they will be importing hours and earnings worked in each pay period in order to properly account for them.
		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getPayPeriodReportByUserIdAndEntryNameIdAndStartDateAndEndDate( $this->getUser(), $setup_data['insurable_earnings_psea_ids'], $insurable_earnings_start_date, $this->getFinalPayStubEndDate(), 0, true, null, [ 'x.start_date' => 'desc' ] );
		if ( $pself->getRecordCount() > 0 ) {
			$this->pay_period_earnings = $this->initial_pay_period_earnings;
			foreach ( $pself as $pse_obj ) {
				$pay_period_id = TTUUID::castUUID( $pse_obj->getColumn( 'pay_period_id' ) );

				if ( $this->getLastDate() < strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ) ) {
					$tmp_after_last_date = true;
				} else {
					$tmp_after_last_date = false;
				}

				$this->pay_period_earnings[$pay_period_id] = [
						'amount'          => $pse_obj->getColumn( 'amount' ),
						'units'           => $pse_obj->getColumn( 'units' ),
						'start_date'      => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
						'end_date'        => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
						'after_last_date' => $tmp_after_last_date,
				];
				//Debug::Arr($this->pay_period_earnings[$pay_period_id], 'Pay Period Start Date: '. TTDate::getDate('DATE', strtotime($pse_obj->getColumn('pay_period_start_date')) ) .' End Date: '. TTDate::getDate('DATE', strtotime($pse_obj->getColumn('pay_period_start_date')) ), __FILE__, __LINE__, __METHOD__, 10);

				$pay_periods_with_earnings++;
			}
		}

		//There shouldn't be pay periods at the end of the list (beginning of the employment) that are $0.
		//  We can't remove them though as the first/last date of the ROE is likely incorrect and needs to be fixed anyways.

		if ( $pay_periods_with_earnings > 0 ) {
			Debug::Arr( $this->pay_period_earnings, 'Pay Period Earnings: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->pay_period_earnings;
		}

		Debug::Text( ' No Pay Period Earnings...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function isPayPeriodWithNoEarnings() {
		//Show earnings per pay period always, as some provinces require it for certain purposes like EI to determine highest weekly earnings.
		return true;
	}

	/**
	 * @return int
	 */
	function getTotalInsurableEarnings() {
		$total_earnings = 0;

		$pp_earnings = $this->getInsurableEarningsByPayPeriod( '15b' );
		if ( is_array( $pp_earnings ) ) {
			foreach ( $pp_earnings as $pp_earning ) {
				$total_earnings += $pp_earning['amount'];
			}
		}
		Debug::Text( 'Total Insurable Earnings (15b): ' . $total_earnings, __FILE__, __LINE__, __METHOD__, 10 );

		return $total_earnings;
	}

	/**
	 * @return bool
	 */
	function getLastPayPeriodVacationEarnings() {
		$setup_data = $this->getSetupData();

		//Get last pay period id
		$pay_period_earnings = $this->getInsurableEarningsByPayPeriod( '15b' );
		if ( is_array( $pay_period_earnings ) ) {
			$last_pay_period_ids = [];

			//Get last pay period and all pay periods after the last date.
			foreach ( $pay_period_earnings as $pay_period_id => $pp_data ) {
				if ( $pp_data['after_last_date'] == true ) {
					$last_pay_period_ids[] = $pay_period_id;
				} else {
					$last_pay_period_ids[] = $pay_period_id;
					break;
				}
			}
			//$pay_period_earning_keys = array_keys( $pay_period_earnings );
			//$last_pay_period_id = array_shift( $pay_period_earning_keys );

			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$retval = $pself->getAmountSumByUserIdAndEntryNameIdAndPayPeriodId( $this->getUser(), $setup_data['vacation_psea_ids'], $last_pay_period_ids );

			Debug::Arr( $last_pay_period_ids, 'Last Pay Period Vacation Pay: ' . $retval['amount'] . ' Last Pay Period ID: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval['amount'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function reCalculate() {
		//Re-generate final pay stub
		//get current pay period based off their last day of work
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getFinalPayStubEndDate() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pay_period_id = $pplf->getCurrent()->getId();
		} else {
			$pay_period_id = false;
		}
		Debug::Text( 'Pay Period ID: ' . $pay_period_id . ' End Date: ' . $this->getFinalPayStubEndDate(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( TTUUID::isUUID( $pay_period_id ) == false ) {
			UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Pay Stub' ), 10, TTi18n::gettext( 'Pay Period is invalid!' ), null );

			return false;
		}

		if ( $this->getEnableGeneratePayStub() == true ) {
			//Find out if a pay stub is already generated for the pay period we are currently in.
			//If it is, delete it so we can start from fresh
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$pslf->getByUserIdAndPayPeriodId( $this->getUser(), $pay_period_id );

			foreach ( $pslf as $pay_stub ) {
				Debug::Text( 'Found Pay Stub ID: ' . $pay_stub->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				//Do not delete PAID pay stubs!
				if ( $pay_stub->getStatus() == 10 ) {
					Debug::Text( 'Last Pay Stub Exists: ' . $pay_stub->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$pay_stub->setDeleted( true );
					$pay_stub->Save();
				}
			}

			//FIXME: Make sure user isn't already in-active! Otherwise pay stub won't generate.
			//Check if pay stub is already generated as well, if it is, and marked paid, then
			//we can't re-generate it, we need to skip this step.
			Debug::Text( 'Calculating Pay Stub...', __FILE__, __LINE__, __METHOD__, 10 );
			$cps = new CalculatePayStub();
			$cps->setUser( $this->getUser() );
			$cps->setPayPeriod( $pay_period_id );
			$cps->setEnablePostTerminationCalculation( true ); //Allow calculating pay stubs after termination date.
			$cps->setTransactionDate( $this->getFinalPayStubTransactionDate() );
			$cps->setRun( PayStubListFactory::getCurrentPayRun( $this->getUserObject()->getCompany(), $pay_period_id ) );
			$cps->calculate();
			Debug::Text( 'Done Calculating Pay Stub', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ), 20, TTi18n::gettext( 'Not generating final pay stub!' ), null );
		}

		//FIXME: Alert the user if they don't have enough information in TimeTrex to get accurate values.
		//Get insurable hours, earnings, and vacation pay now that the final pay stub is generated
		$setup_data = $this->getSetupData();
		$absence_policy_ids = $setup_data['absence_policy_ids'];

		//Find out the date of how far back we have to go to get insurable values.
		//Insurable Hours
		$insurable_hours_start_date = $this->getInsurablePayPeriodStartDate( $this->getInsurableHoursReportPayPeriods() );

		//All worked time and overtime is considered insurable.
		//Only between the start and final date worked, as if they worked or have insurable earnings past the last date then the last date should be moved back.
		$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$worked_total_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $insurable_hours_start_date, $this->getLastDate() );
		Debug::text( 'Worked Total Time: ' . $worked_total_time, __FILE__, __LINE__, __METHOD__, 10 );

		//User definable absence policies for insurable hours.
		//Only between the start and final date worked, as if they worked or have insurable earnings past the last date then the last date should be moved back.
		$absence_total_time = $udtlf->getAbsenceTimeSumByUserIDAndAbsenceIDAndStartDateAndEndDate( $this->getUser(), $absence_policy_ids, $insurable_hours_start_date, $this->getLastDate() );
		Debug::text( 'Absence Total Time: ' . $absence_total_time, __FILE__, __LINE__, __METHOD__, 10 );

		$total_hours = round( TTDate::getHours( $worked_total_time + $absence_total_time ) );
		Debug::Text( 'Total Insurable Hours: ' . $total_hours, __FILE__, __LINE__, __METHOD__, 10 );

		$total_earnings = $this->getTotalInsurableEarnings();

		//Note, this includes the current pay stub we just generated
		Debug::Text( 'Total Insurable Earnings: ' . $total_earnings, __FILE__, __LINE__, __METHOD__, 10 );

		$user_generic_status_id = 30;
		if ( $total_hours == 0 && $total_earnings != 0 ) {
			$user_generic_status_id = 20;
		}
		UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Record of Employment' ), $user_generic_status_id, TTi18n::gettext( 'Insurable Hours' ) . ': ' . $total_hours, null );

		$user_generic_status_id = 30;
		if ( $total_earnings == 0 && $total_hours != 0 ) {
			$user_generic_status_id = 20;
		}
		UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Record of Employment' ), $user_generic_status_id, TTi18n::gettext( 'Insurable Earnings' ) . ': $' . $total_earnings, null );


		//ReSave these
		if ( $this->getId() != '' ) {
			$rlf = TTnew( 'ROEListFactory' ); /** @var ROEListFactory $rlf */
			$rlf->getById( $this->getId() );
			if ( $rlf->getRecordCount() > 0 ) {
				$roe_obj = $rlf->getCurrent();

				$roe_obj->setInsurableHours( $total_hours );
				$roe_obj->setInsurableEarnings( $total_earnings );
				if ( $roe_obj->isValid() ) {
					$roe_obj->Save();
				}
			}
		}

		return true;
	}

	/**
	 * @return mixed
	 */
	function getFormObject() {
		if ( !isset( $this->form_obj['gf'] ) || !is_object( $this->form_obj['gf'] ) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() . '/classes/fpdi/fpdi.php' );
			require_once( Environment::getBasePath() . '/classes/tcpdf/tcpdf.php' );
			require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

			$gf = new GovernmentForms();

			$this->form_obj['gf'] = $gf;

			return $this->form_obj['gf'];
		}

		return $this->form_obj['gf'];
	}

	/**
	 * @return mixed
	 */
	function getROEObject() {
		if ( !isset( $this->form_obj['roe'] ) || !is_object( $this->form_obj['roe'] ) ) {
			$this->form_obj['roe'] = $this->getFormObject()->getFormObject( 'ROE', 'CA' );

			return $this->form_obj['roe'];
		}

		return $this->form_obj['roe'];
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}

		//ROEs need a SIN to be submitted. Though there seem to be some rare cases where out of country employees won't have one, so allow 0's or 9's.
		if ( is_object( $this->getUserObject() ) ) {
			$this->Validator->isSIN( 'user_id',
									 $this->getUserObject()->getSIN(),
									 TTi18n::gettext( 'Employee record must have a valid SIN specified' ),
									 $this->getUserObject()->getCountry()
			);

			$this->Validator->isTRUE( 'user_id',
					( $this->getUserObject()->getAddress1() != '' ? true : false ),
									  TTi18n::gettext( 'Employee record does not have a Address specified' )
			);

			$this->Validator->isTRUE( 'user_id',
					( $this->getUserObject()->getCity() != '' ? true : false ),
									  TTi18n::gettext( 'Employee record does not have a City specified' )
			);

			$this->Validator->isTRUE( 'user_id',
					( $this->getUserObject()->getPostalCode() != '' ? true : false ),
									  TTi18n::gettext( 'Employee record does not have a Postal Code specified' )
			);
		}

		// Pay period type
		if ( $this->getPayPeriodType() !== false ) {
			$ppsf = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $ppsf */
			$this->Validator->inArrayKey( 'pay_period_type_id',
										  $this->getPayPeriodType(),
										  TTi18n::gettext( 'Incorrect pay period type' ),
										  $ppsf->getOptions( 'type' )
			);
		}
		// Code
		if ( $this->getCode() != '' ) {
			$this->Validator->inArrayKey( 'code_id',
										  $this->getCode(),
										  TTi18n::gettext( 'Incorrect code' ),
										  $this->getOptions( 'code' )
			);
		}
		// First date
		if ( $this->getFirstDate() !== false ) {
			$this->Validator->isDate( 'first_date',
									  $this->getFirstDate(),
									  TTi18n::gettext( 'Invalid first date' )
			);
		}
		// Last date
		if ( $this->getLastDate() !== false ) {
			$this->Validator->isDate( 'last_date',
									  $this->getLastDate(),
									  TTi18n::gettext( 'Invalid last date' )
			);
		}
		// Final pay period end date
		if ( $this->getPayPeriodEndDate() !== false ) {
			$this->Validator->isDate( 'pay_period_end_date',
									  $this->getPayPeriodEndDate(),
									  TTi18n::gettext( 'Invalid final pay period end date' )
			);
		}

		// Make sure last day for which paid and final pay period ending date does not exceed the maximum number of days in a pay period minus 1.
		// Max days that can elapse in the same pay period upon termination. ie: if they were terminated on the first day of a pay period, then only 13 days could lapse after that in a bi-weekly pay period.
		//   Alternatively we could just check to make sure that Last Day for Which Paid is in the same pay period as Final Pay Period Ending Date?
		if ( $this->getLastDate() !== false && $this->getPayPeriodEndDate() !== false ) {
			$pay_period_data = $this->calculatePayPeriodType( $this->getLastDate() );
			$pay_period_data['pay_period_maximum_days'] = ( $pay_period_data['pay_period_maximum_days'] - 1 );
			$days_between_pay_period_and_end_date = (int)( TTDate::getDayDifference( TTDate::getMiddleDayEpoch( $this->getLastDate() ), TTDate::getMiddleDayEpoch( $this->getPayPeriodEndDate() ) ) );

			$this->Validator->isGreaterThan( 'pay_period_end_date',
											 $pay_period_data['pay_period_maximum_days'],
											 TTi18n::gettext( 'Final Pay Period End Date cannot be more than %1 days after the Last Day For Which Paid, based on this pay period type', [ $pay_period_data['pay_period_maximum_days'] ] ),
											 $days_between_pay_period_and_end_date
			);
			unset( $pay_period_data, $days_between_pay_period_and_end_date );
		}


		// Recall date
		if ( $this->getRecallDate() != '' ) {
			$this->Validator->isDate( 'recall_date',
									  $this->getRecallDate(),
									  TTi18n::gettext( 'Invalid recall date' )
			);
		}

		if ( $this->getFirstDate() !== false && $this->getLastDate() !== false && TTDate::getMiddleDayEpoch( $this->getFirstDate() ) > TTDate::getMiddleDayEpoch( $this->getLastDate() ) ) {
			$this->Validator->isTrue( 'last_date',
									  false,
									  TTi18n::gettext( 'Last Day Paid must be after First Day Worked' )
			);
		}

		if ( $this->getLastDate() !== false && $this->getPayPeriodEndDate() !== false && TTDate::getMiddleDayEpoch( $this->getLastDate() ) > TTDate::getMiddleDayEpoch( $this->getPayPeriodEndDate() ) ) {
			$this->Validator->isTrue( 'pay_period_end_date',
									  false,
									  TTi18n::gettext( 'Final Pay Period Ending Date must be on or after Last Day Paid' )
			);
		}

		if ( $this->getEnableReleaseAccruals() == true || $this->getEnableGeneratePayStub() == true ) {
			// Pay stub end date
			if ( $this->getFinalPayStubEndDate() == '' ) {
				$this->Validator->isTrue( 'final_pay_stub_end_date',
										  false,
										  TTi18n::gettext( 'Final pay stub end date must be specified' )
				);
			}
			// Pay stub transaction date
			if ( $this->getFinalPayStubTransactionDate() == '' ) {
				$this->Validator->isTRUE( 'final_pay_stub_transaction_date',
										  false,
										  TTi18n::gettext( 'Final pay stub transaction date must be specified' )
				);
			}
		}

		// Pay stub end date
		if ( $this->getFinalPayStubEndDate() != '' ) {
			$this->Validator->isDate( 'final_pay_stub_end_date',
									  $this->getFinalPayStubEndDate(),
									  TTi18n::gettext( 'Invalid final pay stub end date' )
			);
		}
		// Pay stub transaction date
		if ( $this->getFinalPayStubTransactionDate() != '' ) {
			$this->Validator->isDate( 'final_pay_stub_transaction_date',
									  $this->getFinalPayStubTransactionDate(),
									  TTi18n::gettext( 'Invalid final pay stub transaction date' )
			);
		}

		// Insurable hours
		if ( $this->getInsurableHours() !== false && $this->getInsurableHours() != 0 ) {
			$this->Validator->isFloat( 'insurable_hours',
									   $this->getInsurableHours(),
									   TTi18n::gettext( 'Invalid insurable hours' )
			);
		}
		// Insurable earnings
		if ( $this->getInsurableEarnings() !== false && $this->getInsurableEarnings() != 0 ) {
			$this->Validator->isFloat( 'insurable_earnings',
									   $this->getInsurableEarnings(),
									   TTi18n::gettext( 'Invalid insurable earnings' )
			);
		}
		// Vacation pay
		if ( $this->getVacationPay() !== false ) {
			$this->Validator->isFloat( 'vacation_pay',
									   $this->getVacationPay(),
									   TTi18n::gettext( 'Invalid vacation pay' )
			);
		}
		// Serial number
		if ( $this->getSerial() !== false && $this->getSerial() != '' ) {
			$this->Validator->isLength( 'serial',
										$this->getSerial(),
										TTi18n::gettext( 'Serial number should be between 9 and 15 digits' ),
										9,
										15 );
		}

		if ( ( $this->getCode() == 'K00' || $this->getCode() == 'K15' ) && $this->getComments() == '' ) {
			$this->Validator->isTRUE( 'comments',
									  false,
									  TTi18n::gettext( 'Comments must be specified when using the selected reason' )
			);
		}

		// Comments
		$this->Validator->isLength( 'comments',
									$this->getComments(),
									TTi18n::gettext( 'Comments are too long' ),
									0,
									1024 );
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == false ) {
			//Make sure Final Pay Stub Transaction Date is equal or after Final End Date
			if ( $this->getFinalPayStubTransactionDate() < $this->getFinalPayStubEndDate() ) {
				$this->Validator->isTrue( 'final_pay_stub_transaction_date',
										  false,
										  TTi18n::gettext( 'Final pay stub transaction date must be on or after final pay stub end date' ) );
			}

			//This can't be determined here as we have to generate the final pay stub first, and we don't do that until postSave().
			//  Add a warning to the ROE PDF instead.
//			//Pay Period #1 (last pay period) must be more than the Vacation Pay, since vacation pay after termination needs to be included with their final earnings from when their last day worked is.
//			$last_pay_period_vacation_earnings = $this->getLastPayPeriodVacationEarnings();
//			if ( $last_pay_period_vacation_earnings > 0 ) {
//				$insurable_earnings_by_pay_period = $this->getInsurableEarningsByPayPeriod( '15c' );
//				if ( is_array( $insurable_earnings_by_pay_period ) ) {
//					reset( $insurable_earnings_by_pay_period );
//					$insurable_earnings_first_pay_period_key = key( $insurable_earnings_by_pay_period );
//
//					if ( $last_pay_period_vacation_earnings <= $insurable_earnings_by_pay_period[ $insurable_earnings_first_pay_period_key ]['amount'] ) {
//						$this->Validator->isTrue( 'last_date',
//												  FALSE,
//												  TTi18n::gettext( 'Insurable Earnings in Pay Period 1 must be greater than any Vacation Pay in box 17a. In this case, Last Day For Which Paid should likely be the last day the employee worked and not include a pay period with just vacation pay' ) );
//
//					}
//				}
//			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //Pending
		}

		if ( $this->getEnableReleaseAccruals() == true ) {
			if ( $this->getFinalPayStubEndDate() != '' ) {
				//Create PS amendment releasing all accruals
				UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Pay Stub Amendment' ), 30, TTi18n::gettext( 'Releasing all employee accruals' ), null );

				//If the final pay stub end date is the same pay period as the Last Date, then use the last date as the effective date.
				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getFinalPayStubEndDate() );
				if ( $pplf->getRecordCount() == 1 ) {
					$pay_period_obj = $pplf->getCurrent();
					Debug::Text( 'Final Pay Stub End Date - Pay Period ID: ' . $pay_period_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $this->getLastDate() >= $pay_period_obj->getStartDate() && $this->getLastDate() <= $pay_period_obj->getEndDate() ) {
						$psa_effective_date = $this->getLastDate();
					} else {
						$psa_effective_date = $this->getFinalPayStubEndDate();
					}
					PayStubAmendmentFactory::releaseAllAccruals( $this->getUser(), $psa_effective_date );
				}
			}
		}

		//Start these off as zero, until we can save this row, and re-calc them after
		//the final pay stub has been generated.
		if ( $this->getInsurableHours() == '' ) {
			$this->setInsurableHours( 0 );
		}
		if ( $this->getInsurableEarnings() == '' ) {
			$this->setInsurableEarnings( 0 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Handle dirty work here.
		Debug::Text( 'ID we just saved: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getEnableReCalculate() == true ) {
			//Set User Termination date to Last Day.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() > 0 ) {
				Debug::Text( 'Setting User Termination Date', __FILE__, __LINE__, __METHOD__, 10 );
				$user_obj = $ulf->getCurrent();
				$user_obj->setStatus( 20 ); //Set status to terminated, now that pay stubs will always generate anyways.
				$user_obj->setTerminationDate( $this->getLastDate() );
				if ( $user_obj->isValid() ) {
					$user_obj->Save();
					UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Employee Record' ), 30, TTi18n::gettext( 'Setting employee termination date to' ) . ': ' . TTDate::getDate( 'DATE', $this->getLastDate() ), null );
				}
			}

			//Warn user if employee has pay stubs or pay stub amendments after the Final Pay Stub End Date.
			if ( $this->getFinalPayStubTransactionDate() != '' ) {
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getNextPayStubByUserIdAndTransactionDateAndRun( $this->getUser(), $this->getFinalPayStubTransactionDate(), 1 );
				Debug::Text( 'Pay Stubs after final: ' . $pslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $pslf->getRecordCount() > 0 ) {
					UserGenericStatusFactory::queueGenericStatus( $this->getUserObject()->getFullName( true ) . ' - ' . TTi18n::gettext( 'Record of Employment' ), 20, TTi18n::gettext( 'Pay stub exists after final pay stub transaction date, therefore this ROE may not be accurate' ), null );
				}
				unset( $pslf );
			}

			$this->ReCalculate();
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
						case 'first_date':
							$this->setFirstDate( TTDate::parseDateTime( $data['first_date'] ) );
							break;
						case 'last_date':
							$this->setLastDate( TTDate::parseDateTime( $data['last_date'] ) );
							break;
						case 'pay_period_end_date':
							$this->setPayPeriodEndDate( TTDate::parseDateTime( $data['pay_period_end_date'] ) );
							break;
						case 'recall_date':
							$this->setRecallDate( TTDate::parseDateTime( $data['recall_date'] ) );
							break;
						case 'final_pay_stub_end_date':
							$this->setFinalPayStubEndDate( TTDate::parseDateTime( $data['final_pay_stub_end_date'] ) );
							break;
						case 'final_pay_stub_transaction_date':
							$this->setFinalPayStubTransactionDate( TTDate::parseDateTime( $data['final_pay_stub_transaction_date'] ) );
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
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
						case 'code':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'pay_period_type':
							$ppsf = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $ppsf */
							$data[$variable] = Option::getByKey( $this->getPayPeriodType(), $ppsf->getOptions( 'type' ) );
							break;
						case 'first_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getFirstDate() );
							break;
						case 'last_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getLastDate() );
							break;
						case 'pay_period_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getPayPeriodEndDate() );
							break;
						case 'recall_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getRecallDate() );
							break;
						case 'final_pay_stub_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getFinalPayStubEndDate() );
							break;
						case 'final_pay_stub_transaction_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getFinalPayStubTransactionDate() );
							break;
						case 'insurable_earnings':
							$data[$variable] = $this->getInsurableEarnings();
							break;
						case 'vacation_pay':
							$data[$variable] = $this->getVacationPay();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'ROE' ) . ' - ' . TTi18n::getText( 'Employee' ) . ': ' . $this->getUserObject()->getFullName() . ' ' . TTi18n::getText( 'Final End Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getFinalPayStubEndDate() ) . ' ' . TTi18n::getText( 'Final Transaction Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getFinalPayStubTransactionDate() ) . ' ' . TTi18n::getText( 'Insurable Earnings' ) . ': ' . Misc::MoneyFormat( $this->getInsurableEarnings() ), null, $this->getTable(), $this );
	}
}

?>
