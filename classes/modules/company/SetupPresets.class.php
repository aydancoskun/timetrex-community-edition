<?php /** @noinspection PhpStatementHasEmptyBodyInspection */
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
 * @package Modules\Users
 */
class SetupPresets extends Factory {

	public $data = null;

	protected $company_obj = null;
	protected $user_obj = null;

	protected $already_processed = null; //Track functions that have already been processed to avoid doing them multiple times.

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return mixed
	 */
	function getCompany() {
		return $this->data['company_id'];
	}

	/**
	 * @param string $id UUID
	 */
	function setCompany( $id ) {
		$this->data['company_id'] = $id;
	}

	/**
	 * @return bool
	 */
	function getUser() {
		if ( isset( $this->data['user_id'] ) ) {
			return $this->data['user_id'];
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 */
	function setUser( $id ) {
		$this->data['user_id'] = $id;

		//Force timezone settings to the user who will be assigned to everything like remittance agency events. This helps prevent Annual event frequencies from appearing like: 01-Jan-2019 to 01-Jan-2020 because they might be created in the wrong timezone.
		$this->setTimeZone();
	}

	/**
	 * @return bool
	 */
	function setTimeZone() {
		Debug::text( 'Switching to users timezone preferences... User ID: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );

		$user_obj = $this->getUserObject();
		if ( is_object( $user_obj ) ) {
			$user_preference_obj = $user_obj->getUserPreferenceObject();
			if ( is_object( $user_preference_obj ) ) {
				return $user_preference_obj->setDateTimePreferences();
			}
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPayStubAccount( $data ) {
		if ( is_array( $data ) ) {

			$pseaf = TTnew( 'PayStubEntryAccountFactory' ); /** @var PayStubEntryAccountFactory $pseaf */
			$pseaf->setObjectFromArray( $data );
			if ( $pseaf->isValid() ) {
				return $pseaf->Save();
			}
		}

		return false;
	}

	/**
	 * Need to be able to add just global accounts, or country specific accounts, or just province specific accounts,
	 * So this function should be called with once with no arguments, once for the country, and once for each province.
	 * ie: PayStubAccounts(), PayStubAccounts( 'ca' ), PayStubAccounts( 'ca', 'bc' )
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function PayStubAccounts( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['pay_stub_accounts'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['pay_stub_accounts'][$country . $province . $district . $industry] = true;
		}

		//See if accounts are already linked
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
		$pseallf->getByCompanyId( $this->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$psealf = $pseallf->getCurrent();
		} else {
			$psealf = TTnew( 'PayStubEntryAccountLinkFactory' ); /** @var PayStubEntryAccountLinkFactory $psealf */
			$psealf->setCompany( $this->getCompany() );
		}

		$gl_account_expense_suffix = ' [Expense]';
		$gl_account_payable_suffix = ' [Payable]';

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				case 'ca':
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'CA - Federal Income Tax',
									'ps_order'       => 200,
									'debit_account'  => '',
									'credit_account' => 'Federal Tax' . $gl_account_payable_suffix, //2190
							]
					);
					/* //Don't separate this into its own pay stub account, as for US at least we can't rejoin it when multiple states are involved.
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'CA - Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$cpp_employee_psea_id = $this->createPayStubAccount( //Need to update PayStubAccountLink for: $psealf->setEmployeeCPP( $psea_id );
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'CPP',
									'ps_order'       => 203,
									'debit_account'  => '',
									'credit_account' => 'CPP' . $gl_account_payable_suffix, //2185
							]
					);
					if ( TTUUID::isUUID( $cpp_employee_psea_id ) && $cpp_employee_psea_id != TTUUID::getZeroID() && $cpp_employee_psea_id != TTUUID::getNotExistID() ) {
						$psealf->setEmployeeCPP( $cpp_employee_psea_id );
					}
					$ei_employee_psea_id = $this->createPayStubAccount( //Need to update PayStubAccountLink for:$psealf->setEmployeeEI( $psea_id );
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'EI',
									'ps_order'       => 204,
									'debit_account'  => '',
									'credit_account' => 'EI' . $gl_account_payable_suffix, //2180
							]
					);
					if ( TTUUID::isUUID( $ei_employee_psea_id ) && $ei_employee_psea_id != TTUUID::getZeroID() && $ei_employee_psea_id != TTUUID::getNotExistID() ) {
						$psealf->setEmployeeEI( $ei_employee_psea_id );
					}

					//Employer Contributions
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'CPP - Employer',
									'ps_order'       => 303,
									'debit_account'  => 'CPP' . $gl_account_expense_suffix, //5430
									'credit_account' => 'CPP' . $gl_account_payable_suffix, //2185
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'EI - Employer',
									'ps_order'       => 304,
									'debit_account'  => 'EI' . $gl_account_expense_suffix, //5420
									'credit_account' => 'EI' . $gl_account_payable_suffix, //2180
							]
					);

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 10,
									'name'           => 'Vacation - No Accrual',
									'ps_order'       => 180,
									'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
									'credit_account' => '',
							]
					);
					$vacation_accrual_psea_id = $this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 50,
									'name'           => 'Vacation Accrual',
									'ps_order'       => 400,
									'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410 - Expense the vacation payable when its accrued.
									'credit_account' => 'Vacation Accrual' . $gl_account_payable_suffix, //2170
							]
					);
					if ( TTUUID::isUUID( $vacation_accrual_psea_id ) && $vacation_accrual_psea_id != TTUUID::getZeroID() && $vacation_accrual_psea_id != TTUUID::getNotExistID() ) {
						$this->createPayStubAccount(
								[
										'company_id'                        => $this->getCompany(),
										'status_id'                         => 10,
										'type_id'                           => 10,
										'name'                              => 'Vacation - Accrual Release',
										'ps_order'                          => 181,
										'accrual_pay_stub_entry_account_id' => $vacation_accrual_psea_id,
										'debit_account'                     => 'Wages' . $gl_account_expense_suffix, //Needs to just debit the Wages account, as the Vacation (Accrual) account handles debit/credit of the payable based on +/- amounts.
										'credit_account'                    => '',
								]
						);
					}

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'RRSP',
									'ps_order'       => 206,
									'debit_account'  => '',
									'credit_account' => 'RRSP' . $gl_account_payable_suffix, //2360
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'RRSP - Employer',
									'ps_order'       => 306,
									'debit_account'  => 'RRSP' . $gl_account_expense_suffix, //5462
									'credit_account' => 'RRSP' . $gl_account_payable_suffix, //2362
							]
					);
					break;
				case 'us':
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'US - Federal Income Tax',
									'ps_order'       => 200,
									'debit_account'  => '',
									'credit_account' => 'Federal Withholding' . $gl_account_payable_suffix, //2190
							]
					);
					/*
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'US - Federal Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'Social Security (FICA)',
									'ps_order'       => 202,
									'debit_account'  => '',
									'credit_account' => 'Social Security (FICA)' . $gl_account_payable_suffix, //2185
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'Social Security (FICA)',
									'ps_order'       => 302,
									'debit_account'  => 'Social Security (FICA)' . $gl_account_expense_suffix, //5430
									'credit_account' => 'Social Security (FICA)' . $gl_account_payable_suffix, //2185
							]
					);

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'US - Federal Unemployment Insurance',
									'ps_order'       => 303,
									'debit_account'  => 'Federal UI' . $gl_account_expense_suffix, //5420
									'credit_account' => 'Federal UI' . $gl_account_payable_suffix, //2180
							]
					);

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => 'Medicare',
									'ps_order'       => 203,
									'debit_account'  => '',
									'credit_account' => 'Medicare' . $gl_account_payable_suffix, //2187
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => 'Medicare',
									'ps_order'       => 303,
									'debit_account'  => 'Medicare' . $gl_account_expense_suffix, //5440
									'credit_account' => 'Medicare' . $gl_account_payable_suffix, //2187
							]
					);

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => '401(k)',
									'ps_order'       => 230,
									'debit_account'  => '',
									'credit_account' => '401(k)' . $gl_account_payable_suffix, //2360
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => '401(k)',
									'ps_order'       => 330,
									'debit_account'  => '401(k)' . $gl_account_expense_suffix, //5462
									'credit_account' => '401(k)' . $gl_account_payable_suffix, //2362, 2360
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 10,
									'name'           => 'Vacation',
									'ps_order'       => 181,
									'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
									'credit_account' => '',
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 10,
									'name'           => 'Paid Time Off (PTO)',
									'ps_order'       => 181,
									'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
									'credit_account' => '',
							]
					);
					break;
				default:
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $country ) . ' - Federal Income Tax',
									'ps_order'       => 200,
									'debit_account'  => '',
									'credit_account' => 'Federal Tax' . $gl_account_payable_suffix, //2190
							]
					);
					/*
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($country) .' - Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 10,
									'name'           => 'Vacation',
									'ps_order'       => 181,
									'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
									'credit_account' => '',
							]
					);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' && $province != '' ) {
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => strtoupper( $province ) . ' - Provincial Income Tax',
							'ps_order'       => 202,
							'debit_account'  => '',
							'credit_account' => 'Provincial Tax' . $gl_account_payable_suffix, //2190
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => strtoupper( $province ) . ' - Workers Compensation',
							'ps_order'       => 305,
							'debit_account'  => 'Workers Comp.' . $gl_account_expense_suffix, //5440
							'credit_account' => 'Workers Comp.' . $gl_account_payable_suffix, //2230
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Termination Pay (In Lieu Of Notice)', //Different from severance pay. Usually used in lieu of notice of termination.
							'ps_order'       => 160,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Severance Pay (Retiring Allowance)', //Was: Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
							'ps_order'       => 161,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
		}

		//United States
		if ( $country == 'us' && $province != '' ) {
			if ( in_array( $province, [
					'al', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'dc', 'ga', 'hi', 'id', 'il',
					'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi', 'mn', 'ms', 'mo',
					'mt', 'ne', 'nj', 'nm', 'ny', 'nc', 'nd', 'oh', 'ok', 'or', 'pa', 'ri',
					'sc', 'ut', 'vt', 'va', 'wi', 'wv',
			] ) ) {
				$this->createPayStubAccount(
						[
								'company_id'     => $this->getCompany(),
								'status_id'      => 10,
								'type_id'        => 20,
								'name'           => strtoupper( $province ) . ' - State Income Tax',
								'ps_order'       => 204,
								'debit_account'  => '',
								'credit_account' => 'State Withholding' . $gl_account_payable_suffix, //2190
						]
				);
				/*
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => strtoupper($province) .' - State Addl. Income Tax',
													'ps_order' => 205,
												)
											);
				*/
			}

			//District/Local, income tax.
			if ( in_array( $province, [
					'al', 'ar', 'co', 'dc', 'de',
					'ia', 'in', 'ky', 'md', 'mi',
					'mo', 'ny', 'oh', 'or', 'pa',
			] ) ) {
				$this->createPayStubAccount(
						[
								'company_id'     => $this->getCompany(),
								'status_id'      => 10,
								'type_id'        => 20,
								'name'           => strtoupper( $province ) . ' - District Income Tax',
								'ps_order'       => 206,
								'debit_account'  => '',
								'credit_account' => 'District Withholding' . $gl_account_payable_suffix, //2192
						]
				);
			}

			//State Unemployement Insurace, deducted from employee
			if ( in_array( $province, [ 'ak', 'nj', 'pa' ] ) ) {
				$this->createPayStubAccount(
						[
								'company_id'     => $this->getCompany(),
								'status_id'      => 10,
								'type_id'        => 20,
								'name'           => strtoupper( $province ) . ' - Unemployment Insurance',
								'ps_order'       => 207,
								'debit_account'  => '',
								'credit_account' => 'State UI' . $gl_account_payable_suffix, //2182
						]
				);
			}
			//State Unemployement Insurance, deducted from employer
			if ( in_array( $province, [
					'ak', 'al', 'ar', 'az', 'ca', 'co', 'ct', 'dc', 'de', 'fl', 'ga', 'hi',
					'ia', 'id', 'il', 'in', 'ks', 'ky', 'la', 'ma', 'md', 'me', 'mi', 'mn',
					'mo', 'ms', 'mt', 'nc', 'nd', 'ne', 'nh', 'nj', 'nm', 'nv', 'ny', 'oh',
					'ok', 'or', 'pa', 'sc', 'sd', 'tn', 'tx', 'ut', 'va', 'vt', 'wa', 'wi',
					'wv', 'wy',
			] ) ) {
				$this->createPayStubAccount(
						[
								'company_id'     => $this->getCompany(),
								'status_id'      => 10,
								'type_id'        => 30,
								'name'           => strtoupper( $province ) . ' - Unemployment Insurance',
								'ps_order'       => 306,
								'debit_account'  => 'State UI' . $gl_account_expense_suffix, //5422
								'credit_account' => 'State UI' . $gl_account_payable_suffix, //2182
						]
				);
			}

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => strtoupper( $province ) . ' - Workers Compensation - Employer',
							'ps_order'       => 305,
							'debit_account'  => 'Workers Comp.' . $gl_account_expense_suffix, //5440
							'credit_account' => 'Workers Comp.' . $gl_account_payable_suffix, //2230
					]
			);


			//Split into Termination Pay and Retiring Allowance in Canada.
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Severence Pay',
							'ps_order'       => 161,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);

			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $province ) {
				//US
				case 'al': //alabama
					//Unemployment Insurance - Employee
					//Employment Security Asmt
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment Security Assessment',
									'ps_order'       => 310,
									'debit_account'  => 'Employment Security' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment Security' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ak': //alaska
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					break;
				case 'az': //arizona
					//Unemployment Insurance - Employee
					//Surcharge
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Job Training Surcharge',
									'ps_order'       => 310,
									'debit_account'  => 'Job Training' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Job Training' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ar': //arkansas
					//Unemployment Insurance - Employee
					break;
				case 'ca': //california
					//Unemployment Insurance - Employee
					//Disability Insurance
					//Employment Training Tax
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment Training Tax',
									'ps_order'       => 310,
									'debit_account'  => 'Employment Training' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment Training' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'co': //colorado
					//Unemployment Insurance - Employee
					break;
				case 'ct': //connecticut
					//Unemployment Insurance - Employee
					break;
				case 'de': //delaware
					//Unemployment Insurance - Employee
					break;
				case 'dc': //d.c.
					//Unemployment Insurance - Employee
					//Administrative Assessment
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Administrative Assessment',
									'ps_order'       => 310,
									'debit_account'  => 'Administrative Assessment' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Administrative Assessment' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'fl': //florida
					//Unemployment Insurance - Employee
					break;
				case 'ga': //georgia
					//Unemployment Insurance - Employee
					//Administrative Assessment
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Administrative Assessment',
									'ps_order'       => 310,
									'debit_account'  => 'Administrative Assessment' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Administrative Assessment' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'hi': //hawaii
					//Unemployment Insurance - Employee
					//E&T Assessment
					//Health Insurance
					//Disability Insurance
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - E&T Assessment',
									'ps_order'       => 310,
									'debit_account'  => 'E&T Assessment' . $gl_account_expense_suffix, //5424
									'credit_account' => 'E&T Assessment' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Health Insurance',
									'ps_order'       => 310,
									'debit_account'  => 'Health Insurance' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Health Insurance' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					break;
				case 'id': //idaho
					//Unemployment Insurance - Employee
					//Administrative Reserve
					//Workforce Development
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Administrative Reserve',
									'ps_order'       => 310,
									'debit_account'  => 'Administrative Reserve' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Administrative Reserve' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Workforce Development',
									'ps_order'       => 310,
									'debit_account'  => 'Workforce Development' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Workforce Development' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'il': //illinois
					//Unemployment Insurance - Employee
					break;
				case 'in': //indiana
					//Unemployment Insurance - Employee
					//County Tax
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - County Income Tax',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'County Tax' . $gl_account_payable_suffix, //2194
							]
					);
					break;
				case 'ia': //iowa
					//Unemployment Insurance - Employee
					//Reserve Fund
					//Surcharge
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Reserve Fund',
									'ps_order'       => 310,
									'debit_account'  => 'Reserve Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Reserve Fund' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Surcharge',
									'ps_order'       => 311,
									'debit_account'  => 'Surcharge' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Surcharge' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ks': //kansas
					//Unemployment Insurance - Employee
					break;
				case 'ky': //kentucky
					//Unemployment Insurance - Employee
					break;
				case 'la': //louisiana
					//Unemployment Insurance - Employee
					break;
				case 'me': //maine
					//Unemployment Insurance - Employee
					//Competitive Skills
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Competitive Skills',
									'ps_order'       => 310,
									'debit_account'  => 'Competitive Skills' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Competitive Skills' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'md': //maryland
					//Unemployment Insurance - Employee
					break;
				case 'ma': //massachusetts
					//Unemployment Insurance - Employee
					//Health Insurance
					//Workforce Training Fund
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Health Insurance',
									'ps_order'       => 310,
									'debit_account'  => 'Health Insurance' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Health Insurance' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Workforce Training Fund',
									'ps_order'       => 311,
									'debit_account'  => 'Workforce Training Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Workforce Training Fund' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'mi': //michigan
					//Unemployment Insurance - Employee
					break;
				case 'mn': //minnesota
					//Unemployment Insurance - Employee
					//Workforce Enhancement Fee
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Workforce Enhancement Fee',
									'ps_order'       => 310,
									'debit_account'  => 'Workforce Enhancement Fee' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Workforce Enhancement Fee' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ms': //mississippi
					//Unemployment Insurance - Employee
					//Training Contribution
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Training Contribution',
									'ps_order'       => 310,
									'debit_account'  => 'Training Contribution' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Training Contribution' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'mo': //missouri
					//Unemployment Insurance - Employee
					break;
				case 'mt': //montana
					//Unemployment Insurance - Employee
					//Administrative Fund
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Administrative Fund',
									'ps_order'       => 310,
									'debit_account'  => 'Administrative Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Administrative Fund' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ne': //nebraska
					//Unemployment Insurance - Employee
					//SUIT
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - SUIT',
									'ps_order'       => 310,
									'debit_account'  => 'SUIT' . $gl_account_expense_suffix, //5424
									'credit_account' => 'SUIT' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'nv': //nevada
					//Unemployment Insurance - Employee
					//Career Enhancement
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Career Enhancement',
									'ps_order'       => 310,
									'debit_account'  => 'Career Enhancement' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Career Enhancement' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'nh': //new hampshire
					//Unemployment Insurance - Employee
					//Administrative Contribution
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Administrative Contribution',
									'ps_order'       => 310,
									'debit_account'  => 'Administrative Contribution' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Administrative Contribution' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'nm': //new mexico
					//Unemployment Insurance - Employee
					//State Trust Fund
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - State Trust Fund',
									'ps_order'       => 310,
									'debit_account'  => 'State Trust Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'State Trust Fund' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'nj': //new jersey
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					//Disability Insurance - Employee
					//Disability Insurance - Employer
					//Workforce Development - Employee
					//Workforce Development - Employer
					//Healthcare Subsidy - Employee
					//Healthcare Subsidy - Employer
					//Family Leave Insurace
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Disability Insurance',
									'ps_order'       => 310,
									'debit_account'  => 'Disability Insurance' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Workforce Development',
									'ps_order'       => 211,
									'debit_account'  => '',
									'credit_account' => 'Workforce Development' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Workforce Development',
									'ps_order'       => 311,
									'debit_account'  => 'Workforce Development' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Workforce Development' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Healthcare Subsidy',
									'ps_order'       => 212,
									'debit_account'  => '',
									'credit_account' => 'Healthcare Subsidy' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Healthcare Subsidy',
									'ps_order'       => 312,
									'debit_account'  => 'Healthcare Subsidy' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Healthcare Subsidy' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Family Leave Insurance',
									'ps_order'       => 213,
									'debit_account'  => '',
									'credit_account' => 'Family Leave Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					break;
				case 'ny': //new york
					//Unemployment Insurance - Employee
					//Reemployment Service Fund
					//Disability Insurance - Employee
					//Disability Insurance - Male
					//Disability Insurance - Female
					//Metropolitan Commuter Tax
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Reemployment Service Fund',
									'ps_order'       => 310,
									'debit_account'  => 'Reemployment Service Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Reemployment Service Fund' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance - Male',
									'ps_order'       => 211,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Disability Insurance - Female',
									'ps_order'       => 212,
									'debit_account'  => '',
									'credit_account' => 'Disability Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Metropolitan Commuter Tax',
									'ps_order'       => 213,
									'debit_account'  => '',
									'credit_account' => 'Metropolitan Commuter Tax' . $gl_account_payable_suffix, //2186
							]
					);
					break;
				case 'nc': //north carolina
					//Unemployment Insurance - Employee
					break;
				case 'nd': //north dakota
					//Unemployment Insurance - Employee
					break;
				case 'oh': //ohio
					//Unemployment Insurance - Employee
					break;
				case 'ok': //oklahoma
					//Unemployment Insurance - Employee
					break;
				case 'or': //oregon
					//Unemployment Insurance - Employee
					//Workers Benefit - Employee
					//Workers Benefit - Employer
					//Statewide Transit Tax
					//Tri-Met Transit District
					//Lane Transit District
					//Special Payroll Tax offset
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20, //Employee Deduction
									'name'           => strtoupper( $province ) . ' - Statewide Transit Tax',
									'ps_order'       => 211,
									'debit_account'  => '',
									'credit_account' => 'Statewide Transit Tax' . $gl_account_payable_suffix,
							]
					);

					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Workers Benefit',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Workers Benefit' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Workers Benefit',
									'ps_order'       => 310,
									'debit_account'  => 'Workers Benefit' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Workers Benefit' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Tri-Met Transit District',
									'ps_order'       => 311,
									'debit_account'  => 'Tri-Met Transit District' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Tri-Met Transit District' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Lane Transit District',
									'ps_order'       => 312,
									'debit_account'  => 'Lane Transit District' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Lane Transit District' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Special Payroll Tax Offset',
									'ps_order'       => 313,
									'debit_account'  => 'Special Payroll Tax Offset' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Special Payroll Tax Offset' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'pa': //pennsylvania
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					break;
				case 'ri': //rhode island
					//Employment Security
					//Job Development Fund
					//Temporary Disability Insurance
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment Security',
									'ps_order'       => 310,
									'debit_account'  => 'Employment Security' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment Security' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Job Development Fund',
									'ps_order'       => 311,
									'debit_account'  => 'Job Development Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Job Development Fund' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Temporary Disability Ins.',
									'ps_order'       => 212,
									'debit_account'  => '',
									'credit_account' => 'Temporary Disability Ins.' . $gl_account_payable_suffix, //2186
							]
					);
					break;
				case 'sc': //south carolina
					//Unemployment Insurance - Employee
					//Contingency Assessment
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Contingency Assessment',
									'ps_order'       => 310,
									'debit_account'  => 'Contingency Assessment' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Contingency Assessment' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'sd': //south dakota
					//Unemployment Insurance - Employee
					//Investment Fee
					//UI Surcharge
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Investment Fee',
									'ps_order'       => 310,
									'debit_account'  => 'Investment Fee' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Investment Fee' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - UI Surcharge',
									'ps_order'       => 310,
									'debit_account'  => 'UI Surcharge' . $gl_account_expense_suffix, //5424
									'credit_account' => 'UI Surcharge' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'tn': //tennessee
					//Unemployment Insurance - Employee
					//Job Skills Fee
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Job Skills Fee',
									'ps_order'       => 310,
									'debit_account'  => 'Job Skills Fee' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Job Skills Fee' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'tx': //texas
					//Unemployment Insurance - Employee
					//Employment & Training
					//UI Obligation Assessment
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment & Training',
									'ps_order'       => 310,
									'debit_account'  => 'Employment & Training' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment & Training' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - UI Obligation Assessment',
									'ps_order'       => 311,
									'debit_account'  => 'UI Obligation Assessment' . $gl_account_expense_suffix, //5424
									'credit_account' => 'UI Obligation Assessment' . $gl_account_payable_suffix, //2184
							]
					);
					break;
				case 'ut': //utah
					//Unemployment Insurance - Employee
					break;
				case 'vt': //vermont
					//Unemployment Insurance - Employee
					break;
				case 'va': //virginia
					//Unemployment Insurance - Employee
					break;
				case 'wa': //washington
					//Unemployment Insurance - Employee
					//Industrial Insurance - Employee
					//Industrial Insurance - Employer
					//Employment Admin Fund
					//Paid Family and Medical Leave - Employee
					//Paid Family and Medical Leave - Employer
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Industrial Insurance',
									'ps_order'       => 210,
									'debit_account'  => '',
									'credit_account' => 'Industrial Insurance' . $gl_account_payable_suffix, //2186
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Industrial Insurance',
									'ps_order'       => 310,
									'debit_account'  => 'Industrial Insurance' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Industrial Insurance' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment Admin Fund',
									'ps_order'       => 311,
									'debit_account'  => 'Employment Admin Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment Admin Fund' . $gl_account_payable_suffix, //2184
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 20,
									'name'           => strtoupper( $province ) . ' - Paid Family and Medical Leave - Employee',
									'ps_order'       => 216,
									'debit_account'  => '',
									'credit_account' => 'Paid Family and Medical Leave' . $gl_account_payable_suffix,
							]
					);
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Paid Family and Medical Leave - Employer',
									'ps_order'       => 316,
									'debit_account'  => 'Paid Family and Medical Leave' . $gl_account_expense_suffix,
									'credit_account' => 'Paid Family and Medical Leave' . $gl_account_payable_suffix,
							]
					);
					break;
				case 'wv': //west virginia
					//Unemployment Insurance - Employee
					break;
				case 'wi': //wisconsin
					//Unemployment Insurance - Employee
					break;
				case 'wy': //wyomin
					//Unemployment Insurance - Employee
					//Employment Support Fund
					$this->createPayStubAccount(
							[
									'company_id'     => $this->getCompany(),
									'status_id'      => 10,
									'type_id'        => 30,
									'name'           => strtoupper( $province ) . ' - Employment Support Fund',
									'ps_order'       => 310,
									'debit_account'  => 'Employment Support Fund' . $gl_account_expense_suffix, //5424
									'credit_account' => 'Employment Support Fund' . $gl_account_payable_suffix, //2184
							]
					);
					break;
			}
		}

		//Default accounts, only created if country and province are not defined.
		if ( $country == '' && $province == '' && $district == '' ) {
			$regular_time_psea_id = $this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Regular Time',
							'ps_order'       => 100,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			if ( TTUUID::isUUID( $regular_time_psea_id ) && $regular_time_psea_id != TTUUID::getZeroID() && $regular_time_psea_id != TTUUID::getNotExistID() ) {
				$psealf->setRegularTime( $regular_time_psea_id );
			}

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Time Bank (Withdrawal)',
							'ps_order'       => 119,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Over Time 1',
							'ps_order'       => 120,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Over Time 2',
							'ps_order'       => 121,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);


			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Premium 1',
							'ps_order'       => 130,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Premium 2',
							'ps_order'       => 131,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Statutory Holiday',
							'ps_order'       => 140,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Sick',
							'ps_order'       => 142,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Bereavement',
							'ps_order'       => 145,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Jury Duty',
							'ps_order'       => 146,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Tips',
							'ps_order'       => 150,
							'debit_account'  => 'Wage Tips' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Commission',
							'ps_order'       => 152,
							'debit_account'  => 'Wage Commission' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Retro Pay',
							'ps_order'       => 153,
							'debit_account'  => 'Wages' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Expense Reimbursement',
							'ps_order'       => 154,
							'debit_account'  => 'Expense Reimbursement', //5410
							'credit_account' => '',
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Bonus',
							'ps_order'       => 156,
							'debit_account'  => 'Wage Bonuses' . $gl_account_expense_suffix, //5410
							'credit_account' => '',
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 10,
							'name'           => 'Advance',
							'ps_order'       => 170,
							'debit_account'  => 'Advance', //5510
							'credit_account' => '',
					]
			);


			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Health Benefits Plan',
							'ps_order'       => 250,
							'debit_account'  => '',
							'credit_account' => 'Health Benefits' . $gl_account_payable_suffix, //2160
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Dental Benefits Plan',
							'ps_order'       => 255,
							'debit_account'  => '',
							'credit_account' => 'Dental Benefits' . $gl_account_payable_suffix, //2162
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Life Insurance',
							'ps_order'       => 256,
							'debit_account'  => '',
							'credit_account' => 'Life Insurance' . $gl_account_payable_suffix, //2164
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Long Term Disability',
							'ps_order'       => 257,
							'debit_account'  => '',
							'credit_account' => 'Long Term Disability' . $gl_account_payable_suffix, //2166
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Accidental Death & Dismemberment',
							'ps_order'       => 258,
							'debit_account'  => '',
							'credit_account' => 'Accidental D&D' . $gl_account_payable_suffix, //2168
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Advance Paid',
							'ps_order'       => 280,
							'debit_account'  => '',
							'credit_account' => 'Advance', //5510
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Union Dues',
							'ps_order'       => 282,
							'debit_account'  => '',
							'credit_account' => 'Union Dues' . $gl_account_payable_suffix, //2170
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Child Support',
							'ps_order'       => 288,
							'debit_account'  => '',
							'credit_account' => 'Child Support' . $gl_account_payable_suffix, //2172
					]
			);

			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 20,
							'name'           => 'Garnishment',
							'ps_order'       => 289,
							'debit_account'  => '',
							'credit_account' => 'Garnishment' . $gl_account_payable_suffix, //2172
					]
			);


			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => 'Health Benefits Plan',
							'ps_order'       => 340,
							'debit_account'  => 'Health Benefits' . $gl_account_expense_suffix, //5410
							'credit_account' => 'Health Benefits' . $gl_account_payable_suffix, //2160
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => 'Dental Benefits Plan',
							'ps_order'       => 341,
							'debit_account'  => 'Dental Benefits' . $gl_account_expense_suffix, //5410
							'credit_account' => 'Dental Benefits' . $gl_account_payable_suffix, //2162
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => 'Life Insurance',
							'ps_order'       => 346,
							'debit_account'  => 'Life Insurance' . $gl_account_expense_suffix, //5410
							'credit_account' => 'Life Insurance' . $gl_account_payable_suffix, //2164
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => 'Long Term Disability',
							'ps_order'       => 347,
							'debit_account'  => 'Long Term Disability' . $gl_account_expense_suffix, //5410
							'credit_account' => 'Long Term Disability' . $gl_account_payable_suffix, //2166
					]
			);
			$this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 30,
							'name'           => 'Accidental Death & Dismemberment',
							'ps_order'       => 348,
							'debit_account'  => 'Accidental D&D' . $gl_account_expense_suffix, //5410
							'credit_account' => 'Accidental D&D' . $gl_account_payable_suffix, //2168
					]
			);


			//Loan
			$loan_accrual_psea_id = $this->createPayStubAccount(
					[
							'company_id' => $this->getCompany(),
							'status_id'  => 10,
							'type_id'    => 50,
							'name'       => 'Loan Balance',
							'ps_order'   => 497,
					]
			);
			if ( TTUUID::isUUID( $loan_accrual_psea_id ) && $loan_accrual_psea_id != TTUUID::getZeroID() && $loan_accrual_psea_id != TTUUID::getNotExistID() ) {
				$this->createPayStubAccount(
						[
								'company_id'                        => $this->getCompany(),
								'status_id'                         => 10,
								'type_id'                           => 10,
								'name'                              => 'Loan',
								'ps_order'                          => 197,
								'accrual_pay_stub_entry_account_id' => $loan_accrual_psea_id,
								'debit_account'                     => 'Loan', //1200
								'credit_account'                    => '',
						]
				);
				$this->createPayStubAccount(
						[
								'company_id'                        => $this->getCompany(),
								'status_id'                         => 10,
								'type_id'                           => 20,
								'name'                              => 'Loan Repayment',
								'ps_order'                          => 297,
								'accrual_pay_stub_entry_account_id' => $loan_accrual_psea_id,
								'debit_account'                     => '',
								'credit_account'                    => 'Loan', //1200
						]
				);
			}

			//Totals
			$total_gross_psea_id = $this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 40,
							'name'           => 'Total Gross',
							'ps_order'       => 199,
							'debit_account'  => '', //5400
							'credit_account' => '',
					]
			);
			if ( TTUUID::isUUID( $total_gross_psea_id ) && $total_gross_psea_id != TTUUID::getZeroID() && $total_gross_psea_id != TTUUID::getNotExistID() ) {
				$psealf->setTotalGross( $total_gross_psea_id );
			}

			$total_deductions_psea_id = $this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 40,
							'name'           => 'Total Deductions',
							'ps_order'       => 298,
							'debit_account'  => '',
							'credit_account' => '',
					]
			);
			if ( TTUUID::isUUID( $total_deductions_psea_id ) && $total_deductions_psea_id != TTUUID::getZeroID() && $total_deductions_psea_id != TTUUID::getNotExistID() ) {
				$psealf->setTotalEmployeeDeduction( $total_deductions_psea_id );
			}


			$net_pay_psea_id = $this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 40,
							'name'           => 'Net Pay',
							'ps_order'       => 299,
							'debit_account'  => '',
							'credit_account' => 'Net Pay (Bank Account)', //1060
					]
			);
			if ( TTUUID::isUUID( $net_pay_psea_id ) && $net_pay_psea_id != TTUUID::getZeroID() && $net_pay_psea_id != TTUUID::getNotExistID() ) {
				$psealf->setTotalNetPay( $net_pay_psea_id );
			}

			$employer_deductions_psea_id = $this->createPayStubAccount(
					[
							'company_id'     => $this->getCompany(),
							'status_id'      => 10,
							'type_id'        => 40,
							'name'           => 'Employer Total Contributions',
							'ps_order'       => 399,
							'debit_account'  => '',
							'credit_account' => '',
					]
			);
			if ( TTUUID::isUUID( $employer_deductions_psea_id ) && $employer_deductions_psea_id != TTUUID::getZeroID() && $employer_deductions_psea_id != TTUUID::getNotExistID() ) {
				$psealf->setTotalEmployerDeduction( $employer_deductions_psea_id );
			}
		}

		if ( $psealf->isValid() == true ) {
			Debug::text( 'Saving.... PSA Linking', __FILE__, __LINE__, __METHOD__, 10 );
			$psealf->Save();
		} else {
			Debug::text( 'Saving.... PSA Linking FAILED!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * NOTE: This was originally duplicated in Report class. If you change it here, check to see if changes are needed there too.
	 * @param string $company_id UUID
	 * @param object $report_obj
	 * @param $data
	 * @return bool
	 */
	function createUserReportData( $company_id, $report_obj, $data ) {
		$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
		$urdlf->getByCompanyIdAndScriptAndDefault( $company_id, get_class( $report_obj ) );

		//Make sure we don't overwrite existing Form Setup if it has already been setup.
		//  Otherwise when adding a new state for example could completely overwrite their tax form setup unexpectedly.
		if ( $urdlf->getRecordCount() == 0 ) {
			Debug::text( 'Form Setup does not exist, creating for the first time...', __FILE__, __LINE__, __METHOD__, 10 );
			$urdf = TTnew( 'UserReportDataFactory' ); /** @var UserReportDataFactory $urdf */
			$urdf->setCompany( $company_id );
			$urdf->setScript( get_class( $report_obj ) );
			$urdf->setName( $report_obj->title );
			$urdf->setData( $data );
			$urdf->setDefault( true );
			if ( $urdf->isValid() ) {
				$urdf->Save();

				return true;
			} else {
				Debug::text( 'Unable to save UserReportData!', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::text( 'Form Setup already exists, not overwriting!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function TaxForms( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['tax_forms'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['tax_forms'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province != '' ) {
			switch ( $country ) {
				case 'ca':
					//
					//Form T4
					//
					$form_t4_config = [
							'status_id'    => 'O', //Original
							'income'       => [ //Employment Income (Box 14) - Should match Gross Pay on Remittance Summary Report (PD7A)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
												],
												'exclude_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay. Included in box 67 instead.
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Health Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Dental Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
												],
							],
							'tax'          => [ //Income Tax Withheld (Box 22)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '%Provincial Income Tax' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'employee_cpp' => [ //Employee CPP (Box 16)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'employer_cpp' => [ //Employer CPP
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'CPP - Employer' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'cpp_earnings' => [ //CPP Earnings (Box 26)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ), //Employer paid RRSP contributions are pensionable and insurable.
												],
												'exclude_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
												],
							],
							'employee_ei'  => [ //Employee EI (Box 18)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'employer_ei'  => [ //Employer EI
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'EI - Employer' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'ei_earnings'  => [ //EI Earnings (Box 24)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ), //Employer paid RRSP contributions are pensionable and insurable.
												],
												'exclude_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
												],
							],
							'union_dues'   => [ //Union Dues (Box 44)
												'include_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Union Dues' ),
												],
												'exclude_pay_stub_entry_account' => [],
							],
							'other_box'    => [ //Other Box
												[
														'box'                            => 40, //(Code 40) - Other taxable allowances and benefits (ie: RRSP)
														'include_pay_stub_entry_account' => [
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ),
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
														],
														'exclude_pay_stub_entry_account' => [],
												],
												[
														'box'                            => 67, //(Code 67) - Non-eligible retiring allowances
														'include_pay_stub_entry_account' => [
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
														],
														'exclude_pay_stub_entry_account' => [],
												],
												[
														'box'                            => 85, //(Code 85) - Employee-paid premiums for private health services plans (Optional - But required for the employee to take advantage of the tax savings when they file tax return at year end), but may avoid requiring employee requiring supporting documents.
														'include_pay_stub_entry_account' => [
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Health Benefits Plan' ),
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Dental Benefits Plan' ),
														],
														'exclude_pay_stub_entry_account' => [],
												],

							],
					];

					Debug::Arr( $form_t4_config, 'T4 TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'T4SummaryReport' ), $form_t4_config );
					unset( $form_t4_config );

					//
					//Form T4A
					//
					$form_t4a_config = [
							'status_id'  => 'O', //Original
							'income_tax' => [ //Income Tax Withheld (Box 22)
											  'include_pay_stub_entry_account' => [
													  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
													  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '%Provincial Income Tax' ),
											  ],
											  'exclude_pay_stub_entry_account' => [],
							],
					];

					Debug::Arr( $form_t4a_config, 'T4A TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'T4ASummaryReport' ), $form_t4a_config );
					unset( $form_t4a_config );

					//
					//Remittance Summary
					//
					$form_remittance_summary_config = [
							'gross_payroll' => [ //Gross Payroll, should match Employment Income (Box 14) on T4
												 'include_pay_stub_entry_account' => [
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
												 ],
												 'exclude_pay_stub_entry_account' => [
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay. Included in box 67 instead.
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Health Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Dental Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
												 ],
							],
							'cpp'           => [ //CPP
												 'include_pay_stub_entry_account' => [
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'CPP - Employer' ),
												 ],
												 'exclude_pay_stub_entry_account' => [],
							],
							'ei'            => [ //EI)
												 'include_pay_stub_entry_account' => [
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'EI - Employer' ),
												 ],
												 'exclude_pay_stub_entry_account' => [],
							],
							'tax'           => [ //Income Tax
												 'include_pay_stub_entry_account' => [
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
														 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '%Provincial Income Tax' ),
												 ],
												 'exclude_pay_stub_entry_account' => [],
							],
					];

					Debug::Arr( $form_remittance_summary_config, 'Remittance Summary TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'RemittanceSummaryReport' ), $form_remittance_summary_config );
					unset( $form_remittance_summary_config );

					//
					//ROE setup.
					//
					$form_roe_config = [
							'insurable_earnings_psea_ids' => [ //Insurable Earnings
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 1' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 2' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 1' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 2' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Statutory Holiday' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Sick' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Commission' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Bonus' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Retro Pay' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Termination Pay (In Lieu Of Notice)' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Bereavement' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Jury Duty' ),
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ), //Employer paid RRSP contributions are pensionable and insurable.
							],
							'vacation_psea_ids'           => [ //Vacation Pay
															   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
							],
							'absence_policy_ids'          => [
									$this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Sick (PAID)' ),
									$this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Vacation (PAID)' ),
									$this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
							],
					];
					Debug::Arr( $form_roe_config, 'ROE TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );

					$ugdlf = TTnew( 'UserGenericDataListFactory' ); /** @var UserGenericDataListFactory $ugdlf */
					$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getCompanyObject()->getId(), 'roe', true );
					if ( $ugdlf->getRecordCount() == 1 ) {
						$ugdf = $ugdlf->getCurrent();
						//Debug::Arr($ugdf->data, 'Found Existing UserGenericData recods: '. $ugdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					} else {
						$ugdf = TTnew( 'UserGenericDataFactory' ); /** @var UserGenericDataFactory $ugdf */
						$ugdf->setCompany( $this->getCompanyObject()->getId() );
						$ugdf->setUser( TTUUID::getZeroID() );
						$ugdf->setDefault( true );
						$ugdf->setScript( 'roe' );
						$ugdf->setName( 'form' ); //Must come last.
					}
					$ugdf->setData( $form_roe_config );
					if ( $ugdf->isValid() ) {
						$ugdf->Save();
					}

					break;
				case 'us':
					//
					//Form W2
					//
					$form_w2_config = [
							'l1' => [ //Wages (Box 1)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
									  ],
									  'exclude_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
									  ],
							],
							'l2' => [ //Federal Income Tax Withheld (Box 2)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l3' => [ //Social Security Wages (Box 3)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
									  ],
									  'exclude_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ), //Tips are excluded from Social Security wages as they are handled in Box 7.
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
											  //$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
									  ],
							],
							'l4' => [ //Social Security Tax Withheld (Box 4)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Social Security (FICA)' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l5' => [ //Medicare Wages (Box 5)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
									  ],
									  'exclude_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
											  //$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
									  ],
							],
							'l6' => [ //Medicare Tax Withheld (Box 6)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Medicare' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l7' => [ //Social Security Tips (Box 7)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l8' => [ //Allocated Tips (Box 7)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],

					];

					Debug::Arr( $form_w2_config, 'W2 TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'FormW2Report' ), $form_w2_config );
					unset( $form_w2_config );

					//
					//Form 941
					//
					$form_941_config = [
							'deposit_schedule'             => 10, //Monthly
							'wages'                        => [ //Wages (Line 2)
																'include_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
																],
																'exclude_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																],
							],
							'income_tax'                   => [ //Income Tax Withheld (Line 3)
																'include_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
																],
																'exclude_pay_stub_entry_account' => [],
							],
							'social_security_wages'        => [ //Social Security Wages (Box 3)
																'include_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
																],
																'exclude_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ), //Tips are excluded from Social Security wages as they are handled in Box 7.
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
																		//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																],
							],
							'social_security_tax'          => [ //Social Security Tax Withheld (Box 4)
																'include_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Social Security (FICA)' ),
																],
																'exclude_pay_stub_entry_account' => [],
							],
							'social_security_tax_employer' => [ //Social Security - Employer
																'include_pay_stub_entry_account' => [
																		$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Social Security (FICA)' ),
																],
																'exclude_pay_stub_entry_account' => [],
							],

							'social_security_tips' => [ //Social Security Tips (Box 7)
														'include_pay_stub_entry_account' => [
																$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
														],
														'exclude_pay_stub_entry_account' => [],
							],

							'medicare_wages'        => [ //Medicare Wages (Box 5)
														 'include_pay_stub_entry_account' => [
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														 ],
														 'exclude_pay_stub_entry_account' => [
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
																 //$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
														 ],
							],
							'medicare_tax'          => [ //Medicare Tax Withheld (Box 6)
														 'include_pay_stub_entry_account' => [
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Medicare' ),
														 ],
														 'exclude_pay_stub_entry_account' => [],
							],
							'medicare_tax_employer' => [ //Medicare - Employer
														 'include_pay_stub_entry_account' => [
																 $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Medicare' ),
														 ],
														 'exclude_pay_stub_entry_account' => [],
							],
					];

					Debug::Arr( $form_941_config, '941 TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'Form941Report' ), $form_941_config );
					unset( $form_941_config );

					//
					//Form 940
					//
					$form_940_config = [
							'state_id'        => strtoupper( $province ), //State
							'total_payments'  => [ //Wages (Line 3)
												   'include_pay_stub_entry_account' => [
														   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
														   //$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, '401(k)' ),
												   ],
												   'exclude_pay_stub_entry_account' => [
														   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														   $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
												   ],
							],
							'exempt_payments' => [ //Exempt Payments (Line 4)
												   'include_pay_stub_entry_account' => [],
												   'exclude_pay_stub_entry_account' => [],
							],
					];

					Debug::Arr( $form_940_config, '940 TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'Form940Report' ), $form_940_config );
					unset( $form_940_config );

					//
					//Form 1099-Misc
					//
					$form_1099m_config = [
							'l4' => [ //Federal Income Tax Withheld (Box 4)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
									  ],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l6' => [ //Medicare and Health Care Payments (Box 6)
									  'include_pay_stub_entry_account' => [],
									  'exclude_pay_stub_entry_account' => [],
							],
							'l7' => [ //Wages (Box 7)
									  'include_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 40, 'Total Gross' ),
									  ],
									  'exclude_pay_stub_entry_account' => [
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
											  $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
									  ],
							],
					];

					Debug::Arr( $form_1099m_config, '1099M TaxForm Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
					$this->createUserReportData( $this->getCompany(), TTNew( 'Form1099MiscReport' ), $form_1099m_config );
					unset( $form_1099m_config );

					if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
						//
						//Affordable Care
						//
						$form_affordable_care_config = [
								'eligible_time_contributing_pay_code' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						];

						Debug::Arr( $form_affordable_care_config, 'Affordable Care Config: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
						$this->createUserReportData( $this->getCompany(), TTNew( 'AffordableCareReport' ), $form_affordable_care_config );
						unset( $form_affordable_care_config );
					}

					break;
			}
		}

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createCompanyDeduction( $data ) {
		if ( is_array( $data ) ) {

			$cdf = TTnew( 'CompanyDeductionFactory' ); /** @var CompanyDeductionFactory $cdf */

			$data['id'] = $cdf->getNextInsertId();

			$cdf->setObjectFromArray( $data );
			if ( $cdf->isValid() ) {
				return $cdf->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param int $type_id
	 * @return bool
	 */
	function getPayStubEntryAccountByCompanyIDAndType( $type_id ) {
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyIdAndType( $this->getCompany(), $type_id );
		if ( $psealf->getRecordCount() > 0 ) {
			return $psealf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param int $type_id
	 * @param $name
	 * @return bool
	 */
	function getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $type_id, $name ) {
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyIdAndTypeAndFuzzyName( $this->getCompany(), $type_id, $name );
		if ( $psealf->getRecordCount() > 0 ) {
			return $psealf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPayrollRemittanceAgency( $data ) {
		if ( is_array( $data ) ) {

			$praf = TTnew( 'PayrollRemittanceAgencyFactory' ); /** @var PayrollRemittanceAgencyFactory $praf */

			$data['id'] = $praf->getNextInsertId();

			$praf->setObjectFromArray( $data );
			if ( $praf->isValid() ) {
				return $praf->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param string $legal_entity_id UUID
	 * @param string $agency_id       UUID
	 * @return bool
	 */
	function getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $legal_entity_id, $agency_id ) {
		$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
		$pralf->getByLegalEntityIdAndAgencyIDAndCompanyId( $legal_entity_id, $agency_id, $this->getCompany() );
		Debug::text( 'Searching Agency based on Agency ID: ' . $agency_id . ' Legal Entity ID: ' . $legal_entity_id . ' Found: ' . (int)$pralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pralf->getRecordCount() > 0 ) {
			return $pralf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @param string $legal_entity_id UUID
	 * @return bool
	 */
	function PayrollRemittanceAgencys( $country = null, $province = null, $district = null, $industry = null, $legal_entity_id = null ) {
		global $config_vars;

		$country = strtolower( $country );
		$province = strtolower( $province );

		$praf = TTnew( 'PayrollRemittanceAgencyFactory' ); /** @var PayrollRemittanceAgencyFactory $praf */
		$praf->StartTransaction();

		Debug::text( 'Country: ' . $country . ' Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );

		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		if ( is_array( $legal_entity_id ) ||
				( TTUUID::isUUID( $legal_entity_id ) && $legal_entity_id != TTUUID::getZeroID() && $legal_entity_id != TTUUID::getNotExistID() ) ) {
			Debug::text( '  Single Legal Entity ID: ' . implode( ',', (array)$legal_entity_id ), __FILE__, __LINE__, __METHOD__, 10 );
			$lelf->getByIDAndCompanyID( $legal_entity_id, $this->getCompany() );
		} else {
			Debug::text( '  All Legal Entity IDs...', __FILE__, __LINE__, __METHOD__, 10 );
			$lelf->getByCompanyID( $this->getCompany() );
		}

		if ( $lelf->getRecordCount() > 0 ) {
			Debug::text( '    Total Legal Entities: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $lelf as $le_obj ) {
				//Determine Source Account to use, if EFT exists, use it.
				$remittance_source_account_id = TTUUID::getZeroID();
				$rsalf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rsalf */
				$rsalf->getByLegalEntityId( $le_obj->getId(), null, [ 'type_id' => 'DESC' ] );  //Order EFT first.
				if ( $rsalf->getRecordCount() > 0 ) {
					$remittance_source_account_id = $rsalf->getCurrent()->getId();
					Debug::text( '    Found Remittance Source Account: ' . $remittance_source_account_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $rsalf );

				if ( $country != '' && $province == '' ) {
					$agency_arr = $praf->getOptions( 'agency', [ 'type_id' => 10, 'country' => strtoupper( $country ), 'province' => '00', 'district' => '00' ] );

					if ( isset( $agency_arr ) && is_array( $agency_arr ) ) {
						foreach ( $agency_arr as $agency_id => $agency_type_name ) {
							$status_id = 10; //Enabled
							$primary_identification = null;

							$always_week_day_id = 2; //Next business day. Both US and Canada allow due dates to be delayed if they land on a weekend or legal holiday.

							$recurring_holiday_ids = [];
							//Use just first 5 chars: '10:CA:00:00:0010'
							switch ( substr( $agency_id, 0, 5 ) ) {
								case '10:CA': //Canada
									//Because CRA allows for provincial holidays, make sure they are created first.
									//  Since this is executed at the country level and provincial holidays may not be created yet.
									$this->RecurringHolidays( $country, $le_obj->getProvince() );

									$recurring_holiday_ids = array_merge(
											$this->RecurringHolidaysByRegion( $country, $province, 100 ), //Include federally recognized holidays by the CRA - https://www.canada.ca/en/revenue-agency/services/tax/public-holidays.html
											$this->RecurringHolidaysByRegion( $country, $le_obj->getProvince(), 100 ) //Include provincially recognized holidays by the CRA - Since this is only called when no province is specified, we need to use the province from the legal entity.
									);

									if ( ( isset( $config_vars['other']['demo_mode'] ) && $config_vars['other']['demo_mode'] == true ) ) {
										$primary_identification = rand( 100000000, 999999999 ) . 'RP0001';
									}
									break;
								case '10:US': //US
									if ( $agency_id == '10:US:00:00:0100' ) { //Centers for Medicare & Medical Services (CMS.gov)
										$status_id = 20;                      //Disabled by default.
									}

									$recurring_holiday_ids = array_merge(
											$this->RecurringHolidaysByRegion( $country, $province, 100 ) //Include Gov't Holidays
									);

									if ( ( isset( $config_vars['other']['demo_mode'] ) && $config_vars['other']['demo_mode'] == true ) ) {
										$primary_identification = rand( 10, 99 ) . '-' . rand( 1000000, 9999999 );
									}
									break;
								default: //This is COUNTRY specific only, below is Province/State.
									break;
							}

							$recurring_holiday_ids = array_unique( $recurring_holiday_ids );
							$insert_id = $this->createPayrollRemittanceAgency(
									[
											'company_id'                   => $this->getCompany(),
											'legal_entity_id'              => $le_obj->getId(),
											'status_id'                    => $status_id,
											'type_id'                      => 10, //Federal
											'agency_id'                    => $agency_id,
											//'name' => strtoupper($country) .' - '. $agency_type_name .' ['. $le_obj->getLegalName() .']',
											'name'                         => strtoupper( $country ) . ' - ' . $agency_type_name, //Don't add legal entity name to agency name, as it may be too long.
											'country'                      => strtoupper( $country ),
											'remittance_source_account_id' => $remittance_source_account_id,
											'always_week_day_id'           => $always_week_day_id,
											'recurring_holiday_policy_id'  => $recurring_holiday_ids,
											'contact_user_id'              => $this->getUser(),
											'primary_identification'       => $primary_identification,
									]
							);

							$this->createRemittanceAgencyEvents( $insert_id );
							unset( $insert_id );
						}
					}
				} else if ( $country != '' && $province != '' ) {
					Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
					$agency_arr = $praf->getOptions( 'agency', [ 'type_id' => 20, 'country' => strtoupper( $country ), 'province' => strtoupper( $province ), 'district' => '00' ] );

					if ( isset( $agency_arr ) && is_array( $agency_arr ) ) {
						foreach ( $agency_arr as $agency_id => $agency_type_name ) {
							$status_id = 10;         //Enabled
							$always_week_day_id = 2; //Next business day.

							switch ( substr( $agency_id, 0, 5 ) ) {
								case '20:CA': //Canada
									if ( substr( $agency_id, -4 ) == '0040' ) { //Child Support, disable by default.
										$status_id = 20;                        //Disabled by default.
									}

									if ( preg_match( '/20:CA:.*:0010/i', $agency_id ) == 1 ) {
										Debug::text( '    Matched regex... for CA provinces WCB...', __FILE__, __LINE__, __METHOD__, 10 );
										$always_week_day_id = 1;                                                          //Previous business day. Most WCB agencies require this.
										$recurring_holiday_ids = $this->RecurringHolidaysByRegion( $country, $province ); //Do not include Gov't Holidays
									} else {
										$recurring_holiday_ids = $this->RecurringHolidaysByRegion( $country, $province, 10 ); //Include Gov't Holidays
									}
									break;
								case '20:US': //Federal Holidays observed by all states.
									if ( substr( $agency_id, -4 ) == '0040' ) { //Child Support, disable by default.
										$status_id = 20;                        //Disabled by default.
									}

									$recurring_holiday_ids = $this->RecurringHolidaysByRegion( $country, $province, 10 ); //Include Gov't Holidays
									break;
								default:
									$recurring_holiday_ids = $this->RecurringHolidaysByRegion( $country, $province, 10 ); //Include Gov't Holidays
									break;
							}

							$recurring_holiday_ids = array_unique( $recurring_holiday_ids );
							$insert_id = $this->createPayrollRemittanceAgency(
									[
											'company_id'                   => $this->getCompany(),
											'legal_entity_id'              => $le_obj->getId(),
											'status_id'                    => $status_id,
											'type_id'                      => 20, //State
											'agency_id'                    => $agency_id,
											//'name' => strtoupper($province) .' - '. $agency_type_name .' ['. $le_obj->getLegalName() .']',
											'name'                         => strtoupper( $province ) . ' - ' . $agency_type_name, //Don't add legal entity name to agency name, as it may be too long.
											'country'                      => strtoupper( $country ),
											'province'                     => strtoupper( $province ),
											//'district' => strtoupper($district),
											'remittance_source_account_id' => $remittance_source_account_id,
											'always_week_day_id'           => $always_week_day_id,
											'recurring_holiday_policy_id'  => $recurring_holiday_ids,
											'contact_user_id'              => $this->getUser(),
									]
							);

							$this->createRemittanceAgencyEvents( $insert_id );
							unset( $insert_id );
						}
					}
				}
			}
		}

		$praf->CommitTransaction();

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPayrollRemittanceAgencyEvent( $data ) {
		if ( is_array( $data ) ) {
			$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */

			$data['id'] = $praef->getNextInsertId();

			$praef->setObjectFromArray( $data );
			if ( $praef->isValid() ) {
				return $praef->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function createRemittanceAgencyEvents( $id ) {
		global $config_vars;

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->StartTransaction();

		$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
		$pralf->getByIdAndCompanyId( $id, $this->getCompany() );
		if ( $pralf->getRecordCount() > 0 ) {
			$agency_events_arr = [];

			$reminder_user_id = $this->getUser();

			//Check to see if installer is enabled (performing an upgrade), or if they pay stubs.
			if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 && is_object( $this->getUserObject() ) ) {
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByCompanyId( $this->getUserObject()->getCompany(), 1 ); //Limit=1
				if ( $pslf->getRecordCount() == 0 ) {
					Debug::Text( '  WARNING: No pay stubs created, not setting event reminder user...', __FILE__, __LINE__, __METHOD__, 10 );
					$reminder_user_id = false;
				}
			}

			$remittance_agency_event_data = include( 'PayrollRemittanceAgencyEventFactory.data.php' ); //Contains large array of necessary data.

			foreach ( $pralf as $pra_obj ) {
				$agency_id = $pra_obj->getAgency();
				if ( isset( $remittance_agency_event_data[$agency_id] ) && count( $remittance_agency_event_data[$agency_id] ) > 0 ) {
					foreach ( $remittance_agency_event_data[$agency_id] as $tmp_agency_type_id => $tmp_agency_event_data ) {
						if ( isset( $tmp_agency_event_data['frequency'] ) && count( $tmp_agency_event_data['frequency'] ) > 0 ) {
							foreach ( $tmp_agency_event_data['frequency'] as $tmp_agency_event_frequency_data ) {
								$tmp_data = $tmp_agency_event_frequency_data;
								$tmp_data['type_id'] = $tmp_agency_type_id;
								$agency_events_arr[] = $tmp_data;
							}
						}
					}
				}
				unset( $tmp_agency_type_id, $tmp_agency_event_data, $tmp_agency_event_frequency_data, $tmp_data );

				if ( isset( $agency_events_arr ) && is_array( $agency_events_arr ) && count( $agency_events_arr ) > 0 ) {
					Debug::Arr( $agency_events_arr, '  Agency ID: ' . $pra_obj->getID() . ' Name: ' . $pra_obj->getName() . ' Events: ', __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $agency_events_arr as $agency_event_arr ) {
						$agency_event_arr['payroll_remittance_agency_id'] = $pra_obj->getId();
						$agency_event_arr['reminder_user_id'] = $reminder_user_id;
						$this->createPayrollRemittanceAgencyEvent( $agency_event_arr );
					}
				} else {
					Debug::Text( 'No Agency Events...', __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $agency_events_arr, $agency_event_arr );
			}
		}

		$praef->CommitTransaction();

		return true;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @param string $legal_entity_id UUID
	 * @return bool
	 */
	function CompanyDeductions( $country = null, $province = null, $district = null, $industry = null, $legal_entity_id = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//
		// Intuit Payroll has lots of information:
		//  http://payroll.intuit.com/support/compliance/agencydetail.jsp?agencyCode=WY
		//
		//Additional Information: http://www.payroll-taxes.com/state-tax.htm
		//
		//Get PayStub Link accounts
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
		$pseallf->getByCompanyId( $this->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$psea_obj = $pseallf->getCurrent();
		} else {
			Debug::text( 'ERROR: No PayStubEntryAccountLinkList for Company ID: ' . $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );
		$pd_obj = new PayrollDeduction( $country, $province );
		$pd_obj->setDate( time() );

		$cdf = TTnew( 'CompanyDeductionFactory' ); /** @var CompanyDeductionFactory $cdf */
		$cdf->StartTransaction();

		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		if ( is_array( $legal_entity_id ) ||
				( TTUUID::isUUID( $legal_entity_id ) && $legal_entity_id != TTUUID::getZeroID() && $legal_entity_id != TTUUID::getNotExistID() ) ) {
			Debug::text( '  Single Legal Entity ID: ' . implode( ',', (array)$legal_entity_id ), __FILE__, __LINE__, __METHOD__, 10 );
			$lelf->getByIDAndCompanyID( $legal_entity_id, $this->getCompany() );
		} else {
			Debug::text( '  All Legal Entity IDs...', __FILE__, __LINE__, __METHOD__, 10 );
			$lelf->getByCompanyID( $this->getCompany() );
		}

		if ( $lelf->getRecordCount() > 0 ) {
			foreach ( $lelf as $le_obj ) {
				//$legal_entity_name = ' [' . $le_obj->getLegalName() . ']';

				Debug::text( 'Country: ' . $country . ' Legal Entity: ' . $le_obj->getLegalName() . ' ID: ' . $le_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $country != '' && $province == '' ) {
					switch ( $country ) {
						case 'ca':
							$pd_obj = new PayrollDeduction( $country, 'BC' ); //Pick default province for now.
							$pd_obj->setDate( time() );

							//Federal Income Tax
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                           => 'CA - Federal Income Tax',
											'calculation_id'                 => 100,
											'calculation_order'              => 100,
											'country'                        => strtoupper( $country ),
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
											'user_value1'                    => $pd_obj->getBasicFederalClaimCodeAmount(),
											'include_pay_stub_entry_account' => [
													$psea_obj->getTotalGross(),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
											],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ), //Advances shouldn't be taxed, as they are similar to a loan.
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'RRSP' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Union Dues' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Health Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Dental Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                   => $this->getCompany(),
											'legal_entity_id'              => $le_obj->getID(),
											'status_id'                    => 10, //Enabled
											'type_id'                      => 10, //Tax
											'payroll_remittance_agency_id' => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                         => 'CA - Addl. Income Tax',
											'calculation_id'               => 20,
											'calculation_order'            => 105,
											'pay_stub_entry_account_id'    => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
											'user_value1'                  => 0,
									]
							);

							//CPP
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                           => 'CPP - Employee',
											'calculation_id'                 => 90, //CPP Formula
											'calculation_order'              => 80,
											'minimum_user_age'               => 18,
											'maximum_user_age'               => 70,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ),
											'include_pay_stub_entry_account' => [
													$psea_obj->getTotalGross(),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ), //Employer paid RRSP contributions are pensionable and insurable.
											],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                           => 'CPP - Employer',
											'calculation_id'                 => 10,
											'calculation_order'              => 85,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'CPP - Employer' ),
											'include_pay_stub_entry_account' => [ $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ), ],
											'user_value1'                    => 100,
									]
							);

							//EI
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                           => 'EI - Employee',
											'calculation_id'                 => 91, //EI Formula
											'calculation_order'              => 90,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ),
											'include_pay_stub_entry_account' => [
													$psea_obj->getTotalGross(),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'RRSP - Employer' ), //Employer paid RRSP contributions are pensionable and insurable.
											],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
											'name'                           => 'EI - Employer',
											'calculation_id'                 => 10,
											'calculation_order'              => 95,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'EI - Employer' ),
											'include_pay_stub_entry_account' => [ $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ), ],
											'user_value1'                    => 140,
									]
							);
							break;
						case 'us':
							//Federal Income Tax
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'US - Federal Income Tax',
											'calculation_id'                 => 100,
											'calculation_order'              => 100,
											'country'                        => strtoupper( $country ),
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
											'user_value1'                    => 10, //Marital Status: Single
											'user_value2'                    => 0, //Allowances
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                   => $this->getCompany(),
											'legal_entity_id'              => $le_obj->getID(),
											'status_id'                    => 10, //Enabled
											'type_id'                      => 10, //Tax
											'payroll_remittance_agency_id' => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                         => 'US - Addl. Income Tax',
											'calculation_id'               => 20,
											'calculation_order'            => 105,
											'pay_stub_entry_account_id'    => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
											'user_value1'                  => 0,
									]
							);

							//Federal Unemployment Insurance.
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'US - Federal Unemployment Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 80,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'US - Federal Unemployment Insurance' ),
											'user_value1'                    => $pd_obj->getFederalUIMinimumRate(),
											'user_value2'                    => $pd_obj->getFederalUIMaximumEarnings(),
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Social Security
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'Social Security - Employee',
											'calculation_id'                 => 84,
											'calculation_order'              => 80,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Social Security (FICA)' ),
											//'user_value1' => $pd_obj->getSocialSecurityRate(), //2013
											//'user_value2' => $pd_obj->getSocialSecurityMaximumEarnings(),
											//'user_value3' => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'Social Security - Employer',
											'calculation_id'                 => 85,
											'calculation_order'              => 81,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Social Security (FICA)' ),
											//'user_value1' => $pd_obj->getSocialSecurityRate(),
											//'user_value2' => $pd_obj->getSocialSecurityMaximumEarnings(),
											//'user_value3' => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Medicare
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'Medicare - Employee',
											'calculation_id'                 => 82,
											'calculation_order'              => 90,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Medicare' ),
											//'user_value1' => $pd_obj->getMedicareRate(),
											'user_value1'                    => 10, //Single
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:US:00:00:0010' ), //Must go before name
											'name'                           => 'Medicare - Employer',
											'calculation_id'                 => 83,
											'calculation_order'              => 91,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Medicare' ),
											//'user_value1' => $pd_obj->getMedicareRate(),
											//'user_value1' => 10, //Single
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
					}
				}

				//Canada
				if ( $country == 'ca' && $province != '' ) {
					Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );

					$vacation_data = [
							'primary_percent'             => 0,
							'secondary_percent'           => 0,
							'secondary_length_of_service' => 0,
					];

					$this->createCompanyDeduction(
							[
									'company_id'                     => $this->getCompany(),
									'legal_entity_id'                => $le_obj->getID(),
									'status_id'                      => 10, //Enabled
									'type_id'                        => 10, //Tax
									'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:CA:' . strtoupper( $province ) . ':00:0100' ), //Must go before name
									'name'                           => strtoupper( $province ) . ' - Workers Compensation',
									'calculation_id'                 => 15,
									'calculation_order'              => 96,
									'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workers Compensation' ),
									'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
									'exclude_pay_stub_entry_account' => [
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
									],
									'user_value1'                    => 0.00,
									'user_value2'                    => 0, //Annual Wage Base
									'user_value3'                    => 0,
							]
					);

					//Labor Law wording for BC (https://www.labour.gov.bc.ca/esb/igm/esa-part-7/igm-esa-s-58.htm)
					//   Its somewhat vague, and its different if the employee leaves during their 5th year or not.
					//   The section about when vacation pay is paid out on every check is telling.
					//
					//   But overall they should start accruing 6% *during* their 5th year of employment to be used once they start their 6th year.
					//     That way any "adjustment" would be to reduce their final vacation accrual payout upon termination.
					/*
						An employee becomes entitled to 6% vacation pay at the completion of their fifth year of employment.
						If an employee has not completed their fifth year of consecutive employment, and their employment ends,
						the vacation pay is based on 4% of gross earnings in the fifth year of employment.
						Vacation pay for an employee who completes five years of employment is calculated as 6% of the gross
						wages earned by the employee during the fifth year of employment.

						When an employee is paid on each scheduled payday
							An employee and employer may agree, in writing, that the employee will receive their vacation pay on every scheduled payday.
							However, when vacation pay is paid on each pay period, the employee will be entitled to an adjustment upon reaching their fifth anniversary.

						Example
							Rashid is paid 4% vacation pay on every cheque. Upon completion of his fifth year of employment, Rashid receives 6%
							vacation pay on each cheque. He also receives a one-time 2% vacation pay adjustment based on total gross earnings
							during the fifth year of employment. The 2% vacation pay adjustment, which he becomes entitled to upon completion
							of his fifth year of employment (employment anniversary) should be paid on the first pay day following the completion
							of the fifth year of employment. Payment of the 2% adjustment ensures that Rashid has received 6% vacation pay when
							he takes his next annual vacation.
					 */
					switch ( $province ) {
						//CA
						case 'bc':
						case 'ab':
						case 'mb':
						case 'on':
						case 'qc':
						case 'nu':
						case 'nt':
							$vacation_data = [
									'primary_percent'             => 4,
									'secondary_percent'           => 6,
									'secondary_length_of_service' => 5, //After 5th year
							];
							break;
						case 'nb':
						case 'ns':
						case 'pe':
							$vacation_data = [
									'primary_percent'             => 4,
									'secondary_percent'           => 6,
									'secondary_length_of_service' => 8, //After 8th year
							];
							break;
						case 'yt':
							$vacation_data = [
									'primary_percent'             => 4,
									'secondary_percent'           => 0,
									'secondary_length_of_service' => 0,
							];
							break;
						case 'sk':
							$vacation_data = [
									'primary_percent'             => 4,
									'secondary_percent'           => 8,
									'secondary_length_of_service' => 10, //After 10th year
							];
							break;
						case 'nl':
							$vacation_data = [
									'primary_percent'             => 4,
									'secondary_percent'           => 6,
									'secondary_length_of_service' => 15, //After 15th year
							];
							break;
					}

					if ( $province == 'ab' ) {
						//OT and Stat Holiday is excluded from vacation pay when its accrued: https://www.alberta.ca/vacation-pay.aspx
						$vacation_accrual_exclude_pay_stub_accounts = [
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 1' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 2' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Statutory Holiday' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
						];

						//In No Accrual cases DO NOT exclude Stat Holiday as per: https://www.alberta.ca/general-holidays-pay.aspx#toc-3
						$vacation_no_accrual_exclude_pay_stub_accounts = [
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 1' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 2' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
						];
					} else {
						$vacation_accrual_exclude_pay_stub_accounts = [
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance Pay (Retiring Allowance)' ), //Severance (aka Retiring Allowance) is an earned benefit. Different from termination pay.
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
						];

						$vacation_no_accrual_exclude_pay_stub_accounts = $vacation_accrual_exclude_pay_stub_accounts;
					}

					//if ( !in_array( $province, array('yt') ) ) {
					if ( isset( $vacation_data['secondary_length_of_service'] ) && $vacation_data['secondary_length_of_service'] > 0 ) {
						$this->createCompanyDeduction(
								[
										'company_id'                        => $this->getCompany(),
										'legal_entity_id'                   => $le_obj->getID(),
										'status_id'                         => 10, //Enabled
										'type_id'                           => 30, //Other
										'name'                              => strtoupper( $province ) . ' - Vacation Accrual - 0-' . ( $vacation_data['secondary_length_of_service'] - 1 ) . ' Years',
										'calculation_id'                    => 10,
										'calculation_order'                 => 50,
										'minimum_length_of_service_unit_id' => 40, //Years
										'minimum_length_of_service'         => 0,
										'maximum_length_of_service_unit_id' => 40, //Years
										'maximum_length_of_service'         => ( $vacation_data['secondary_length_of_service'] - 0.001 ),
										'pay_stub_entry_account_id'         => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Vacation Accrual' ),
										'include_pay_stub_entry_account'    => [ $psea_obj->getTotalGross() ],
										'exclude_pay_stub_entry_account'    => $vacation_accrual_exclude_pay_stub_accounts,
										'user_value1'                       => $vacation_data['primary_percent'],
								]
						);
					}
					$this->createCompanyDeduction(
							[
									'company_id'                        => $this->getCompany(),
									'legal_entity_id'                   => $le_obj->getID(),
									'status_id'                         => 10, //Enabled
									'type_id'                           => 30, //Other
									'name'                              => strtoupper( $province ) . ' - Vacation Accrual - ' . ( $vacation_data['secondary_length_of_service'] - 0 ) . '+ Years',
									'calculation_id'                    => 10,
									'calculation_order'                 => 51,
									'minimum_length_of_service_unit_id' => 40, //Years
									'minimum_length_of_service'         => $vacation_data['secondary_length_of_service'],
									'maximum_length_of_service_unit_id' => 40, //Years
									'maximum_length_of_service'         => 0,
									'pay_stub_entry_account_id'         => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Vacation Accrual' ),
									'include_pay_stub_entry_account'    => [ $psea_obj->getTotalGross() ],
									'exclude_pay_stub_entry_account'    => $vacation_accrual_exclude_pay_stub_accounts,
									'user_value1'                       => $vacation_data['secondary_percent'],
							]
					);
					//if ( !in_array( $province, array('yt') ) ) {
					if ( isset( $vacation_data['secondary_length_of_service'] ) && $vacation_data['secondary_length_of_service'] > 0 ) {
						$this->createCompanyDeduction(
								[
										'company_id'                        => $this->getCompany(),
										'legal_entity_id'                   => $le_obj->getID(),
										'status_id'                         => 10, //Enabled
										'type_id'                           => 30, //Other
										'name'                              => strtoupper( $province ) . ' - Vacation No Accrual - 0-' . ( $vacation_data['secondary_length_of_service'] - 1 ) . ' Years',
										'calculation_id'                    => 10,
										'calculation_order'                 => 50,
										'minimum_length_of_service_unit_id' => 40, //Years
										'minimum_length_of_service'         => 0,
										'maximum_length_of_service_unit_id' => 40, //Years
										'maximum_length_of_service'         => ( $vacation_data['secondary_length_of_service'] - 0.001 ),
										'pay_stub_entry_account_id'         => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
										'include_pay_stub_entry_account'    => [ $psea_obj->getTotalGross() ],
										'exclude_pay_stub_entry_account'    => $vacation_no_accrual_exclude_pay_stub_accounts,
										'user_value1'                       => $vacation_data['primary_percent'],
								]
						);
					}
					$this->createCompanyDeduction(
							[
									'company_id'                        => $this->getCompany(),
									'legal_entity_id'                   => $le_obj->getID(),
									'status_id'                         => 10, //Enabled
									'type_id'                           => 30, //Other
									'name'                              => strtoupper( $province ) . ' - Vacation No Accrual - ' . ( $vacation_data['secondary_length_of_service'] - 0 ) . '+ Years',
									'calculation_id'                    => 10,
									'calculation_order'                 => 51,
									'minimum_length_of_service_unit_id' => 40, //Years
									'minimum_length_of_service'         => $vacation_data['secondary_length_of_service'],
									'maximum_length_of_service_unit_id' => 40, //Years
									'maximum_length_of_service'         => 0,
									'pay_stub_entry_account_id'         => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
									'include_pay_stub_entry_account'    => [ $psea_obj->getTotalGross() ],
									'exclude_pay_stub_entry_account'    => $vacation_no_accrual_exclude_pay_stub_accounts,
									'user_value1'                       => $vacation_data['secondary_percent'],
							]
					);


					$this->createCompanyDeduction(
							[
									'company_id'                     => $this->getCompany(),
									'legal_entity_id'                => $le_obj->getID(),
									'status_id'                      => 10, //Enabled
									'type_id'                        => 10, //Tax
									'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '10:CA:00:00:0010' ), //Must go before name
									'name'                           => strtoupper( $province ) . ' - Provincial Income Tax',
									'calculation_id'                 => 200,
									'calculation_order'              => 101,
									'country'                        => strtoupper( $country ),
									'province'                       => strtoupper( $province ),
									'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Provincial Income Tax' ),
									'user_value1'                    => $pd_obj->getBasicProvinceClaimCodeAmount(),
									'include_pay_stub_entry_account' => [
											$psea_obj->getTotalGross(),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Life Insurance' ), //Employer contributions are taxable.
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Accidental Death & Dismemberment' ), //Employer contributions are taxable.
									],
									'exclude_pay_stub_entry_account' => [
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'RRSP' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Union Dues' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Health Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Dental Benefits Plan' ), //Employee contributions eligible for tax deductions. Employer Contributions are not.
									],
							]
					);
				}

				if ( $country == 'us' && $province != '' ) {
					if ( in_array( $province, [
							'al', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'dc', 'ga', 'hi', 'id', 'il',
							'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi', 'mn', 'ms', 'mo',
							'mt', 'ne', 'nj', 'nm', 'ny', 'nc', 'nd', 'oh', 'ok', 'or', 'pa', 'ri',
							'sc', 'ut', 'vt', 'va', 'wi', 'wv',
					] ) ) {

						//States that don't use marital statuses as user_value1.
						if ( in_array( $province, [ 'az', 'il', 'in', 'oh', 'va' ] ) ) {
							$user_value1 = 0;
						} else {
							$user_value1 = 10; //Single
						}

						//State Income Tax
						$this->createCompanyDeduction(
								[
										'company_id'                     => $this->getCompany(),
										'legal_entity_id'                => $le_obj->getID(),
										'status_id'                      => 10, //Enabled
										'type_id'                        => 10, //Tax
										'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
										'name'                           => strtoupper( $province ) . ' - State Income Tax',
										'calculation_id'                 => 200,
										'calculation_order'              => 200,
										'country'                        => strtoupper( $country ),
										'province'                       => strtoupper( $province ),
										'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - State Income Tax' ),
										'user_value1'                    => $user_value1,
										'user_value2'                    => 0, //0 Allowances
										'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
										'exclude_pay_stub_entry_account' => [
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
										],
								]
						);
						$this->createCompanyDeduction(
								[
										'company_id'                   => $this->getCompany(),
										'legal_entity_id'              => $le_obj->getID(),
										'status_id'                    => 10, //Enabled
										'type_id'                      => 10, //Tax
										'payroll_remittance_agency_id' => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
										'name'                         => strtoupper( $province ) . ' - State Addl. Income Tax',
										'calculation_id'               => 20,
										'calculation_order'            => 205,
										'pay_stub_entry_account_id'    => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - State Income Tax' ),
										'user_value1'                  => 0,
								]
						);
					}

					if ( $province == 'wy' ) { //WY, Workers Comp. goes to UI agency.
						$workers_compensation_payroll_remittance_agency_id = $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' );
					} else {
						$workers_compensation_payroll_remittance_agency_id = $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0100' );
					}

					$this->createCompanyDeduction(
							[
									'company_id'                     => $this->getCompany(),
									'legal_entity_id'                => $le_obj->getID(),
									'status_id'                      => 10, //Enabled
									'type_id'                        => 10, //Tax
									'payroll_remittance_agency_id'   => $workers_compensation_payroll_remittance_agency_id, //Must go before name
									'name'                           => strtoupper( $province ) . ' - Workers Compensation - Employer',
									'calculation_id'                 => 15,
									'calculation_order'              => 96,
									'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workers Compensation - Employer' ),
									'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
									'exclude_pay_stub_entry_account' => [
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
											$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
									],
									'user_value1'                    => 0.00,
									'user_value2'                    => 0, //Annual Wage Base
									'user_value3'                    => 0,
							]
					);
					unset( $workers_compensation_payroll_remittance_agency_id );


					//State UI wage base list: http://www.americanpayroll.org/members/stateui/state-ui-2/
					//Default to unemployment rates to 0.
					$company_state_unemployment_rate = 0;
					$company_state_unemployment_wage_base = 0;
					$state_unemployment_rate = 0;
					$state_unemployment_wage_base = 0;

					Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
					switch ( $province ) {
						//US
						case 'al': //alabama
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 8000;

							//Employment Security Asmt
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'name'                           => strtoupper( $province ) . ' - Employment Security Assessment', //Unemployment
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment Security Assessment' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ak': //alaska
							//Unemployment Insurance - Employee
							//Unemployment Insurance - Employer
							$company_state_unemployment_wage_base = $state_unemployment_wage_base = 39900; //Rate for: 20190101
							break;
						case 'az': //arizona
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7000;

							//Surcharge
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Job Training Surcharge', //Part of UI.
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Job Training Surcharge' ),
											'user_value1'                    => 0.10, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ar': //arkansas
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7000; //Rate for: 20190101

							break;
						case 'ca': //california
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7000;

							//Disability Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance' ),
											'user_value1'                    => 1.0, //Percent
											'user_value2'                    => 118371, //WageBase - Rate for: 20190101
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Employment Training Tax
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Employment Training Tax',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment Training Tax' ),
											'user_value1'                    => 0.10, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'co': //colorado
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 13100; //Rate for: 20190101

							break;
						case 'ct': //connecticut
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 15000;
							break;
						case 'de': //delaware
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 16500; //Rate for: 20180101
							break;
						case 'dc': //d.c.
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9000;

							//Administrative Assessment
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Administrative Assessment',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Administrative Assessment' ),
											'user_value1'                    => 0.20, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'fl': //florida
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7000;

							break;
						case 'ga': //georgia
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9500;

							//Administrative Assessment
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Administrative Assessment',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Administrative Assessment' ),
											'user_value1'                    => 0.08, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'hi': //hawaii
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 46800; //Rate for: 20190101

							//E&T Assessment
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - E&T Assessment',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - E&T Assessment' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Health Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'name'                           => strtoupper( $province ) . ' - Health Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											//FIXME: 'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Health Insurance' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Disability Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance' ),
											'user_value1'                    => 0.50, //Percent
											'user_value2'                    => 51082.72, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							break;
						case 'id': //idaho
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 40000; //Rate for: 20190101

							//Administrative Reserve
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Administrative Reserve',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Administrative Reserve' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Workforce Development
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workforce Development',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workforce Development' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'il': //illinois
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 12960;
							break;
						case 'in': //indiana
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9500;

							//County Tax
							/*
							$this->createPayStubAccount(
															array(
																'company_id' => $this->getCompany(),
																'status_id' => 10,
																'type_id' => 20,
																'name' => strtoupper($province) .' - County Income Tax',
																'ps_order' => 210,
															)
														);
							*/
							break;
						case 'ia': //iowa
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 31600; //Rate for: 20200101

							//Reserve Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Reserve Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Reserve Fund' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Surcharge
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Surcharge',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Surcharge' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ks': //kansas
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 14000;
							break;
						case 'ky': //kentucky
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 10800; //Rate for: 20200101
							break;
						case 'la': //louisiana
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7700;
							break;
						case 'me': //maine
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 12000;

							//Competitive Skills
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Competitive Skills',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Competitive Skills' ),
											'user_value1'                    => 0.06, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'md': //maryland
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 8500;
							break;
						case 'ma': //massachusetts
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 15000;

							//Health Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Health Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Health Insurance' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Workforce Training Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workforce Training Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workforce Training Fund' ),
											'user_value1'                    => 0.06, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'mi': //michigan
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9000;
							break;
						case 'mn': //minnesota
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 35000; //Rate for: 20200101

							//Workforce Enhancement Fee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workforce Enhancement Fee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workforce Enhancement Fee' ),
											'user_value1'                    => 0.10, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ms': //mississippi
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 14000;

							//Training Contribution
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Training Contribution',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Training Contribution' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'mo': //missouri
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 11500; //Rate for: 20200101
							break;
						case 'mt': //montana
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 34100; //Rate for: 20200101

							//Administrative Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Administrative Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Administrative Fund' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ne': //nebraska
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9000;

							//SUIT
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - SUIT',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - SUIT' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'nv': //nevada
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 32500; //Rate for: 20200101

							//Career Enhancement
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Career Enhancement',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Career Enhancement' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'nh': //new hampshire
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 14000;

							//Administrative Contribution
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Administrative Contribution',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Administrative Contribution' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'nj': //new jersey
							//Unemployment Insurance - Employee
							//Unemployment Insurance - Employer
							$state_unemployment_wage_base = 35300; //Rate for: 20200101

							//Disability Insurance - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance - Employee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance' ),
											'user_value1'                    => 0.20, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Disability Insurance - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance - Employer',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Disability Insurance' ),
											'user_value1'                    => 0.50, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Workforce Development - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workforce Development - Employee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Workforce Development' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Workforce Development - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workforce Development - Employer',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workforce Development' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Healthcare Subsidy - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Healthcare Subsidy - Employee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Healthcare Subsidy' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Healthcare Subsidy - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Healthcare Subsidy - Employer',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Healthcare Subsidy' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Family Leave Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Family Leave Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Family Leave Insurance' ),
											'user_value1'                    => 0.08, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							break;
						case 'nm': //new mexico
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 25800; //Rate for: 20200101

							//State Trust Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - State Trust Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - State Trust Fund' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ny': //new york
							//Unemployment Insurance - Employee
							$company_state_unemployment_wage_base = $state_unemployment_wage_base = 11600; //Rate for: 20200101

							//Reemployment Service Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Reemployment Service Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Reemployment Service Fund' ),
											'user_value1'                    => 0.075, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Disability Insurance - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance' ),
											'user_value1'                    => 0.50, //Percent
											'user_value2'                    => 6240, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Disability Insurance - Male
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance - Male',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance - Male' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 6000, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Disability Insurance - Female
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Disability Insurance - Female',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Disability Insurance - Female' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 6000, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Metropolitan Commuter Tax
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Metropolitan Commuter Tax',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Metropolitan Commuter Tax' ),
											'user_value1'                    => 0.34, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							if ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) { //When in unit test mode don't create NY city income taxes, as we will have to update all of our unit tests.
								//NY City Income Tax
								$this->createCompanyDeduction(
										[
												'company_id'                     => $this->getCompany(),
												'legal_entity_id'                => $le_obj->getID(),
												'status_id'                      => 10, //Enabled
												'type_id'                        => 10, //Tax
												'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
												'name'                           => strtoupper( $province ) . ' - New York City Income Tax',
												'calculation_id'                 => 300,
												'calculation_order'              => 300,
												'country'                        => strtoupper( $country ),
												'province'                       => strtoupper( $province ),
												'district'                       => 'NYC',
												'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - District Income Tax' ),
												'user_value1'                    => 10, //Single
												'user_value2'                    => 0, //0 Allowances
												'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
												'exclude_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
												],
										]
								);

								//Yonkers Income Tax
								$this->createCompanyDeduction(
										[
												'company_id'                     => $this->getCompany(),
												'legal_entity_id'                => $le_obj->getID(),
												'status_id'                      => 10, //Enabled
												'type_id'                        => 10, //Tax
												'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
												'name'                           => strtoupper( $province ) . ' - Yonkers Income Tax',
												'calculation_id'                 => 300,
												'calculation_order'              => 300,
												'country'                        => strtoupper( $country ),
												'province'                       => strtoupper( $province ),
												'district'                       => 'Yonkers',
												'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - District Income Tax' ),
												'user_value1'                    => 10, //Single
												'user_value2'                    => 0, //0 Allowances
												'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
												'exclude_pay_stub_entry_account' => [
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
														$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
												],
										]
								);
							}

							break;
						case 'nc': //north carolina
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 24300; //Rate for: 20190101
							break;
						case 'nd': //north dakota
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 36400; //Rate for: 20190101
							break;
						case 'oh': //ohio
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9000; //Rate for: 20200101
							break;
						case 'ok': //oklahoma
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 18700; //Rate for: 20200101
							break;
						case 'or': //oregon
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 42100; //Rate for: 20200101

							//Workers Benefit - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workers Benefit - Employee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Workers Benefit' ),
											'user_value1'                    => 0.016, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Workers Benefit - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Workers Benefit - Employer',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Workers Benefit' ),
											'user_value1'                    => 0.017, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Tri-Met Transit District
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Tri-Met Transit District',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Tri-Met Transit District' ),
											'user_value1'                    => 0.7537, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Lane Transit District
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Lane Transit District',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Lane Transit District' ),
											'user_value1'                    => 0.73, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Special Payroll Tax offset
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Special Payroll Tax Offset',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Special Payroll Tax Offset' ),
											'user_value1'                    => 0.09, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Statewide Transit Tax
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Statewide Transit Tax',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Statewide Transit Tax' ),
											'user_value1'                    => 0.10, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'pa': //pennsylvania
							//Unemployment Insurance - Employee
							//Unemployment Insurance - Employer
							$state_unemployment_wage_base = 0;
							$company_state_unemployment_wage_base = 10000; //Rate for: 20180101
							break;
						case 'ri': //rhode island
							//**Unemployement is called "Job Development Fund, enter wage base for it.**

							//Employment Security
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Employment Security',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment Security' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 23000, //WageBase Rate for: 20180101
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Job Development Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Job Development Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Job Development Fund' ),
											'user_value1'                    => 0.21, //Percent
											'user_value2'                    => 23600, //WageBase Rate for: 20190101
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Temporary Disability Insurance
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Temporary Disability Insurance',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Temporary Disability Ins.' ),
											'user_value1'                    => 1.20, //Percent
											'user_value2'                    => 68100, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'sc': //south carolina
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 14000;

							//Contingency Assessment
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Contingency Assessment',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Contingency Assessment' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'sd': //south dakota
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 15000;

							//Investment Fee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Investment Fee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Investment Fee' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//UI Surcharge
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - UI Surcharge',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - UI Surcharge' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'tn': //tennessee
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 7000; //Rate for: 20190101

							//Job Skills Fee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Job Skills Fee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Job Skills Fee' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'tx': //texas
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 9000;

							//Employment & Training
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Employment & Training',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment & Training' ),
											'user_value1'                    => 0.10, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//UI Obligation Assessment
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - UI Obligation Assessment',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - UI Obligation Assessment' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
						case 'ut': //utah
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 36600; //Rate for: 20200101
							break;
						case 'vt': //vermont
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 16100; //Rate for: 20200101
							break;
						case 'va': //virginia
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 8000;
							break;
						case 'wa': //washington
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 52700; //Rate for: 20200101

							//Industrial Insurance - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Industrial Insurance - Employee',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Industrial Insurance' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Industrial Insurance - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Industrial Insurance - Employer',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Industrial Insurance' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => 0, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							//Employment Admin Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Employment Admin Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment Admin Fund' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $pd_obj->getSocialSecurityMaximumEarnings(), //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
											],
									]
							);

							//Paid Family and Medical Leave - Employee
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Paid Family and Medical Leave - Employee',
											'description'                    => '*NOTE: 0.2533% is the result of 63.33% of 0.4%',
											'calculation_id'                 => 15,
											'calculation_order'              => 187,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Paid Family and Medical Leave - Employee' ),
											'user_value1'                    => 0.2533, //Percent - Default to 63.33% of 0.4% = 0.2533332%
											'user_value2'                    => $pd_obj->getSocialSecurityMaximumEarnings(), //WageBase - Matches Social Security
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
											],
									]
							);
							//Paid Family and Medical Leave - Employer
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Paid Family and Medical Leave - Employer',
											'description'                    => '*NOTE: This should be 0.4% if the employer is paying 100% of premiums, or 0.1467% (36.67% of 0.4%) if the employer is paying 36.67% of premiums.',
											'calculation_id'                 => 15,
											'calculation_order'              => 188,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Paid Family and Medical Leave - Employer' ),
											'user_value1'                    => 0.00, //Percent - Default to 0% as employers less than 50EE don't need to pay anything. However this would either be 0.4% or ( 0.4%×36.67% ) = 0.1467%
											'user_value2'                    => $pd_obj->getSocialSecurityMaximumEarnings(), //WageBase - Matches Social Security
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);

							break;
						case 'wv': //west virginia
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 12000;
							break;
						case 'wi': //wisconsin
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 14000;
							break;
						case 'wy': //wyoming
							//Unemployment Insurance - Employee
							$state_unemployment_wage_base = 26400; //Rate for: 20200101

							//Employment Support Fund
							$this->createCompanyDeduction(
									[
											'company_id'                     => $this->getCompany(),
											'legal_entity_id'                => $le_obj->getID(),
											'status_id'                      => 10, //Enabled
											'type_id'                        => 10, //Tax
											'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
											'name'                           => strtoupper( $province ) . ' - Employment Support Fund',
											'calculation_id'                 => 15,
											'calculation_order'              => 186,
											'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Employment Support Fund' ),
											'user_value1'                    => 0.00, //Percent
											'user_value2'                    => $state_unemployment_wage_base, //WageBase
											'user_value3'                    => 0,
											'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
											'exclude_pay_stub_entry_account' => [
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
													$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
													//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
											],
									]
							);
							break;
					}

					//Unemployment insurance must go below the above state settings so it has the proper rate/wage_base for
					//State Unemployement Insurace, deducted from employer
					if ( in_array( $province, [
							'ak', 'al', 'ar', 'az', 'ca', 'co', 'ct', 'dc', 'de', 'fl', 'ga', 'hi',
							'ia', 'id', 'il', 'in', 'ks', 'ky', 'la', 'ma', 'md', 'me', 'mi', 'mn',
							'mo', 'ms', 'mt', 'nc', 'nd', 'nh', 'nj', 'nm', 'nv', 'ny', 'oh',
							'ok', 'or', 'pa', 'sc', 'sd', 'tn', 'tx', 'ut', 'va', 'vt', 'wa', 'wi',
							'wv', 'wy',
					] ) ) {

						if ( in_array( $province, [ 'ca', 'nm', 'ny', 'or' ] ) ) {                                                                                                          //These states remit UI with state income tax.
							$payroll_remittance_agency_id = $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0010' ); //State Government [Income Tax].
						} else {
							$payroll_remittance_agency_id = $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ); //State Government [UI].
						}

						$this->createCompanyDeduction(
								[
										'company_id'                     => $this->getCompany(),
										'legal_entity_id'                => $le_obj->getID(),
										'status_id'                      => 10, //Enabled
										'type_id'                        => 10, //Tax
										'payroll_remittance_agency_id'   => $payroll_remittance_agency_id, //Must go before name
										'name'                           => strtoupper( $province ) . ' - Unemployment Insurance - Employer',
										'calculation_id'                 => 15,
										'calculation_order'              => 185,
										'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper( $province ) . ' - Unemployment Insurance' ),
										'user_value1'                    => $state_unemployment_rate, //Percent
										'user_value2'                    => $state_unemployment_wage_base, //WageBase
										'user_value3'                    => 0,
										'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
										'exclude_pay_stub_entry_account' => [
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
												//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
										],
								]
						);
						unset( $payroll_remittance_agency_id );
					}
					//State Unemployement Insurace, deducted from employee
					if ( in_array( $province, [ 'ak', 'nj', 'pa' ] ) ) {
						$this->createCompanyDeduction(
								[
										'company_id'                     => $this->getCompany(),
										'legal_entity_id'                => $le_obj->getID(),
										'status_id'                      => 10, //Enabled
										'type_id'                        => 10, //Tax
										'payroll_remittance_agency_id'   => $this->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $le_obj->getId(), '20:US:' . strtoupper( $province ) . ':00:0020' ), //Must go before name
										'name'                           => strtoupper( $province ) . ' - Unemployment Insurance - Employee',
										'calculation_id'                 => 15,
										'calculation_order'              => 186,
										'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper( $province ) . ' - Unemployment Insurance' ),
										'user_value1'                    => $company_state_unemployment_rate, //Percent
										'user_value2'                    => $company_state_unemployment_wage_base, //WageBase
										'user_value3'                    => 0,
										'include_pay_stub_entry_account' => [ $psea_obj->getTotalGross() ],
										'exclude_pay_stub_entry_account' => [
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
												$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Advance' ),
												//$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
										],
								]
						);
					}
				}

				//Default accounts, only created if country and province are not defined.
				if ( $country == '' && $province == '' && $district == '' ) {
					$this->createCompanyDeduction(
							[
									'company_id'                     => $this->getCompany(),
									'legal_entity_id'                => $le_obj->getID(),
									'status_id'                      => 10, //Enabled
									'type_id'                        => 20, //Deduction
									'name'                           => 'Loan Repayment',
									'calculation_id'                 => 52, //Fixed Amount w/Target
									'calculation_order'              => 200,
									'pay_stub_entry_account_id'      => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Loan Repayment' ),
									'user_value1'                    => 25, //Fixed amount to repay each pay period.
									'user_value2'                    => 0,
									'include_account_amount_type_id' => 30, //YTD Amount
									'include_pay_stub_entry_account' => [ $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Loan Balance' ) ],
							]
					);
				}
			}
		}

		$cdf->CommitTransaction();

		return true;
	}


	/**
	 * @param $name
	 * @return array|bool
	 */
	function getRecurringHolidayByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$rhlf = TTnew( 'RecurringHolidayListFactory' ); /** @var RecurringHolidayListFactory $rhlf */
		$rhlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $rhlf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $rhlf as $rh_obj ) {
				$retarr[] = $rh_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $name
	 * @param null $prefix
	 * @return array|bool
	 */
	function getRecurringHolidayByCompanyIDAndNameAndPrefix( $name, $prefix = null ) {
		$name_with_prefix = strtoupper( $prefix ) . ' - ' . $name;

		$retval = $this->getRecurringHolidayByCompanyIDAndName( $name_with_prefix );
		if ( $retval == false ) {
			$retval = $this->getRecurringHolidayByCompanyIDAndName( $name );
		}

		if ( $retval == false ) {
			Debug::text( '  WARNING: Unable to find recurring holiday: ' . $name . ' Prefix: ' . $prefix, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $type_id 10=Province/State recognized Government only Holidays, 100=Federally recognized only holidays.
	 * @return array
	 */
	function RecurringHolidaysByRegion( $country = null, $province = null, $type_id = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		Debug::text( 'Country: ' . $country . ' Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );

		$retval = [];

		//Type_id=10 //Include Government Only Holidays
		if ( $country != '' && $province == '' ) {
			Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $country ) {
				case 'ca':
					$retval = array_merge(
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
					);

					if ( $type_id == 10 ) { //Government Only holidays.
						$retval = array_merge( $retval,
											   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country )
						);
					}
					break;
				case 'us':
					$retval = array_merge(
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Martin Luther%', $country ),
							//(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Inauguration Day%', $country ), //Observed every 4years.
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Presidents Day%', $country ), //Washingtons Birthday.
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Emancipation Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Memorial Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Independence Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Columbus%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Veterans Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country )
					);
					break;
			}
		} else if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Country: ca Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $province ) {
				case 'bc':
					if ( $type_id == 100 ) {
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'British Columbia Day%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'British Columbia Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Family Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'ab':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Family Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'mb':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								//(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ), //Partial holiday, paid 1.5x but not paid time off.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Louis Riel Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}

					break;
				case 'qc':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Patriot\'s Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'St. Jean Baptiste Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country )

						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'nl':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'nu':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Nunavut Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'nt':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Aboriginal Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'nb':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Brunswick Day%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Family Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Brunswick Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'ns':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								//(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ), //Partial holiday, paid 1.5x but not paid time off.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Heritage Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'on':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Family Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country )
							);
						}
					}
					break;
				case 'pe':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Civic Holiday%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Islander Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'yt':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Discovery Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Aboriginal Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
				case 'sk':
					if ( $type_id == 100 ) { //CRA provincially recognized holidays
						$retval = (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Saskatchewan Day%', $province );
					} else {
						$retval = array_merge(
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victoria Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Canada Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Remembrance Day%', $country ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country ),

								//Province Specific.
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Family Day%', $province ),
								(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Saskatchewan Day%', $province )
						);

						if ( $type_id == 10 ) { //Government Only holidays.
							$retval = array_merge( $retval,
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Easter Monday%', $country ),
												   (array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Boxing Day%', $country )
							);
						}
					}
					break;
			}
		} else if ( $country == 'us' && $province != '' ) {
			//US - States
			Debug::text( 'Country: us Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );

			$retval = array_merge(
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Year%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Martin Luther%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Presidents Day%', $country ), //Washingtons Birthday, Lincolns Birthday
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Memorial Day%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Independence Day%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Labo%r Day%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Columbus%', $country ), //Federal Reserve holiday. Enable in all states.
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Veterans Day%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Thanksgiving Day%', $country ),
					(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Day%', $country )
			);

			if ( in_array( $province, [ 'ca', 'ct', 'de', 'hi', 'in', 'la', 'nj', 'nc', 'nd', 'tn' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Good Friday%', $province )
				);
			}

			//Federal Reserve holiday, so enable it for all states?
//			if ( in_array( $province, array('al', 'az', 'co', 'ct', 'ca', 'ga', 'id', 'il', 'in', 'ia', 'la', 'md', 'mn', 'mo', 'mt', 'ne', 'nh', 'nj', 'nm', 'ny', 'oh', 'pa', 'ri', 'sc', 'sd', 'tn', 'va', 'wa', 'wv', 'wi') ) ) {
//				$retval = array_merge(
//						$retval,
//						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Columbus%', $country ) //Country wide holiday.
//				);
//			}

			if ( in_array( $province, [ 'mi' ] ) ) { //WV=Half day.
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'New Years Eve%', $province )
				);
			}

			if ( in_array( $province, [ 'ca', 'ct', 'il', 'ia', 'mo', 'ny', 'vt' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Lincolns Birthday%', $province )
				);
			}
//			if ( in_array( $province, array('ca', 'oh') ) ) {
//				$retval = array_merge(
//						$retval,
//						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Rosa Parks Day%', $province )
//				);
//			}


//			if ( in_array( $province, array('wi') ) ) {
//				$retval = array_merge(
//						$retval,
//						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Susan B. Anthony Day%', $province )
//				);
//			}

			if ( in_array( $province, [ 'ca' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'César Chávez Day%', $province )
				);
			}

			if ( in_array( $province, [ 'ca', 'dc', 'mn' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Emancipation Day%', $province )
				);
			}

			if ( in_array( $province, [ 'me', 'ma' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Patriots Day%', $province )
				);
			}


			if ( in_array( $province, [ 'ar', 'mi', 'ri', 'sc', 'tx' ] ) ) { //WV=Half day.
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Christmas Eve%', $country ) //Country wide holiday.
				);
			}

			if ( in_array( $province, [ 'ok', 'sc', 'tn', 'tx' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Day After Christmas%', $province )
				);
			}

			if ( in_array( $province, [ 'wv' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'West Virginia Day%', $province )
				);
			}

			if ( in_array( $province, [ 'ne' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Arbor Day%', $province )
				);
			}

			if ( in_array( $province, [ 'vt' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Town Meeting Day%', $province ),
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Bennington Battle Day%', $province )
				);
			}

			if ( in_array( $province, [ 'ri' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Independence Day%', $province ),
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Victory Day%', $province )
				);
			}

			if ( in_array( $province, [ 'al', 'ga', 'fl', 'ms', 'sc', ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndName( $province . ' - Confederate Memorial Day%' ) //Always specific to each state, so don't use the prefix search.
				);
			}

			if ( in_array( $province, [ 'va' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Lee-Jackson Day%', $province )
				);
			}

			if ( in_array( $province, [ 'al', 'ky' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Jefferson Davis Birthday%', $province )
				);
			}

			if ( in_array( $province, [ 'ut' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Pioneer Day%', $province )
				);
			}

			if ( in_array( $province, [ 'wi' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Juneteenth Day%', $province )
				);
			}

			if ( in_array( $province, [ 'mo' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Truman Day%', $province )
				);
			}

			if ( in_array( $province, [ 'nv' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Nevada Day%', $province )
				);
			}

			if ( in_array( $province, [ 'ak' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Alaska Day%', $province ),
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Sewards Day%', $province )
				);
			}

			if ( in_array( $province, [ 'hi' ] ) ) {
				$retval = array_merge(
						$retval,
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Prince Kuhio Day%', $province ),
						(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Kamehameha Day%', $province ),
						(array)$this->getRecurringHolidayByCompanyIDAndName( $province . ' - Admission Day%' ) //Always specific to each state, so don't use the prefix search.
				);
			}

			//Gov't Only Holidays.
			if ( $type_id == 10 ) {
				if ( in_array( $province, [ 'ca', 'co', 'de', 'fl', 'ga', 'il', 'in', 'ky', 'la', 'me', 'md', 'mi', 'mn', 'ne', 'nv', 'ok', 'or', 'pa', 'sc', 'tn', 'tx', 'va', 'wa', 'wv' ] ) ) { //Update the states where the recurring holiday is created too.
					$retval = array_merge(
							$retval,
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Day After Thanksgiving Day%', $province )
					);
				}

				if ( in_array( $province, [ 'ca' ] ) ) {
					$retval = array_merge(
							$retval,
							(array)$this->getRecurringHolidayByCompanyIDAndNameAndPrefix( 'Native American Day%', $province )
					);
				}

				if ( in_array( $province, [ 'ca' ] ) ) {
					$retval = array_merge(
							$retval,
							(array)$this->getRecurringHolidayByCompanyIDAndName( $province . ' - Admission Day%' ) //Always specific to each state, so don't use the prefix search.
					);
				}
			}
		}

		Debug::text( '  Returning Holidays: ' . count( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createRecurringHoliday( $data ) {
		if ( is_array( $data ) ) {

			$rhf = TTnew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf */
			$rhf->setObjectFromArray( $data );
			if ( $rhf->isValid() ) {
				return $rhf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function RecurringHolidays( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Since this is called from PayrollRemittanceAgencys() as well, prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['recurring_holidays'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['recurring_holidays'][$country . $province . $district . $industry] = true;
		}

		//
		// See also: RecurringHolidaysByRegion() -- As it defined specifically what holidays apply where.
		//

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			//
			//http://www.statutoryholidays.com/
			//
			switch ( $country ) {
				case 'ca':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - New Years Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Good Friday',
									'type_id'            => 20,
									'special_day'        => 1, //Easter
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									//'day_of_month' => 1,
									//'month_int' => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Canada Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 7,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Labour Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 9,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Christmas Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 25,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);

					//Optional holidays or ones observed by many provinces.
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Christmas Eve',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 24,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Boxing Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 26,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Easter Monday',
									'type_id'            => 20,
									'special_day'        => 6, //Easter Monday
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									//'day_of_month' => 1,
									//'month_int' => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Thanksgiving Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									'week_interval'      => 2,
									'day_of_week'        => 1,
									//'day_of_month' => 24,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'             => $this->getCompany(),
									'name'                   => strtoupper( $country ) . ' - Victoria Day',
									'type_id'                => 30,
									'special_day'            => 0,
									'pivot_day_direction_id' => 30,
									//'week_interval' => 0,
									'day_of_week'            => 1,
									'day_of_month'           => 24,
									'month_int'              => 5,
									'always_week_day_id'     => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Remembrance Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 11,
									'month_int'          => 11,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'us':
					//Offical federal holidays
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - New Years Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'             => $this->getCompany(),
									'name'                   => strtoupper( $country ) . ' - Memorial Day',
									'type_id'                => 30,
									'special_day'            => 0,
									'pivot_day_direction_id' => 20,
									//'week_interval' => 3,
									'day_of_week'            => 1,
									'day_of_month'           => 24,
									'month_int'              => 5,
									'always_week_day_id'     => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Independence Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 4,
									'month_int'          => 7,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Labour Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 9,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Veterans Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 11,
									'month_int'          => 11,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Thanksgiving Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									'week_interval'      => 4,
									'day_of_week'        => 4,
									//'day_of_month' => 24,
									'month_int'          => 11,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Christmas Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 25,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);

					//Rhode Island doesn't observe this, but all other states do.
					//Optional days
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Martin Luther King Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 11,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Presidents Day', //Washingtons Birthday
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 11,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

//					//Inauguration Day is every 4 years.
//					$this->createRecurringHoliday(
//							array(
//									'company_id'         => $this->getCompany(),
//									'name'               => strtoupper( $country ) . ' - Inauguration Day',
//									'type_id'            => 10,
//									'special_day'        => 0,
//									//'pivot_day_direction_id' => 0,
//									//'week_interval' => 3,
//									//'day_of_week' => 1,
//									'day_of_month'       => 20,
//									'month_int'          => 1,
//									'always_week_day_id' => 3, //Closest
//							)
//					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Emancipation Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 3,
									//'day_of_week' => 1,
									'day_of_month'       => 16,
									'month_int'          => 4,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Christmas Eve',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 24,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - Columbus Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 20,
									'week_interval'      => 2,
									'day_of_week'        => 1,
									//'day_of_month' => 24,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'cr':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'New Years Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Good Friday' ),
									'type_id'            => 20,
									'special_day'        => 1, //Easter
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									//'day_of_month' => 1,
									//'month_int' => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Christmas Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 25,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Juan Santamaria Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 11,
									'month_int'          => 4,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Labour Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 1,
									'month_int'          => 5,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Anexion de Guanacaste Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 25,
									'month_int'          => 7,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Virgen de los Angeles Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 2,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Mothers Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 15,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Independence Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 15,
									'month_int'          => 9,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Culture Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 12,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'gt':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'New Years Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Good Friday' ),
									'type_id'            => 20,
									'special_day'        => 1, //Easter
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									//'day_of_month' => 1,
									//'month_int' => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Labour Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 1,
									'month_int'          => 5,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Army Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 30,
									'month_int'          => 6,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Virgin Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 15,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Independence Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 15,
									'month_int'          => 9,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( '1944 Revolution Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 20,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'All Saint Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 1,
									'month_int'          => 11,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Christmas Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 25,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Christmas Eve' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 24,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'hn':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'New Years Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 1,
									'month_int'          => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Good Friday' ),
									'type_id'            => 20,
									'special_day'        => 1, //Easter
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									//'day_of_month' => 1,
									//'month_int' => 1,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Labour Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 1,
									'month_int'          => 5,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Independence Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 15,
									'month_int'          => 9,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Christmas Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 25,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Morazan Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 3,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Culture Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 12,
									'month_int'          => 10,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $country ) . ' - ' . TTi18n::gettext( 'Armed Forces Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 30,
									//'week_interval' => 4,
									//'day_of_week' => 4,
									'day_of_month'       => 21,
									'month_int'          => 12,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $province ) {
				case 'bc':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - British Columbia Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Family Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
				case 'ab':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Family Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
				case 'mb':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Louis Riel Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'qc':
					//Easter Monday moved this to federal, as CRA does observe it too.
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - St. Jean Baptiste Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 24,
									'month_int'          => 6,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'             => $this->getCompany(),
									'name'                   => strtoupper( $province ) . ' - Patriot\'s Day',
									'type_id'                => 30,
									'special_day'            => 0,
									'pivot_day_direction_id' => 30,
									//'week_interval' => 0,
									'day_of_week'            => 1,
									'day_of_month'           => 24,
									'month_int'              => 5,
									'always_week_day_id'     => 3, //Closest
							]
					);
					break;
				case 'nb':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - New Brunswick Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Family Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
				case 'nl':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - St. Patrick\'s Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 17,
									'month_int'          => 3,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - St. George\'s Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 23,
									'month_int'          => 4,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Discovery Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 24,
									'month_int'          => 6,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'nu':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Civic Holiday',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Nunavut Day',
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval'      => 1,
									//'day_of_week'        => 1,
									'day_of_month'       => 9,
									'month_int'          => 7,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'nt':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Civic Holiday',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - ' . TTi18n::gettext( 'Aboriginal Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 21,
									'month_int'          => 6,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'ns':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Heritage Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'on':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Family Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Civic Holiday',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
				case 'pe':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Islander Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Civic Holiday',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
				case 'yt':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Discovery Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - ' . TTi18n::gettext( 'Aboriginal Day' ),
									'type_id'            => 10,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									//'week_interval' => 0,
									//'day_of_week' => 0,
									'day_of_month'       => 21,
									'month_int'          => 6,
									'always_week_day_id' => 3, //Closest
							]
					);
					break;
				case 'sk':
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Family Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 3,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 2,
									'always_week_day_id' => 3, //Closest
							]
					);
					$this->createRecurringHoliday(
							[
									'company_id'         => $this->getCompany(),
									'name'               => strtoupper( $province ) . ' - Saskatchewan Day',
									'type_id'            => 20,
									'special_day'        => 0,
									//'pivot_day_direction_id' => 0,
									'week_interval'      => 1,
									'day_of_week'        => 1,
									//'day_of_month' => 1,
									'month_int'          => 8,
									'always_week_day_id' => 3, //Closest
							]
					);

					break;
			}
		}

		//US
		if ( $country == 'us' && $province != '' ) {
			if ( in_array( $province, [ 'ca', 'ct', 'de', 'fl', 'hi', 'in', 'ky', 'la', 'nj', 'nc', 'nd', 'tn', 'tx' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Good Friday',
								'type_id'            => 20,
								'special_day'        => 1, //Easter
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								//'day_of_month' => 1,
								//'month_int' => 1,
								'always_week_day_id' => 3, //Closest
						]
				);
			}
			if ( in_array( $province, [ 'ca', 'co', 'de', 'fl', 'ga', 'il', 'in', 'ia', 'ky', 'la', 'me', 'md', 'mi', 'mn', 'ne', 'nv', 'nm', 'ok', 'or', 'pa', 'sc', 'tn', 'tx', 'va', 'wa', 'wv' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Day After Thanksgiving Day',
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 40,
								//'week_interval' => 4,
								'day_of_week'            => 5,
								'day_of_month'           => 23,
								'month_int'              => 11,
								'always_week_day_id'     => 3, //Closest
						]
				);
			}
			if ( in_array( $province, [ 'ar', 'nc', 'ok', 'sc', 'tn', 'tx', 'wi' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Day After Christmas',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 26,
								'month_int'          => 12,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'al', 'ky', 'mi', 'wv', 'wi' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - New Years Eve',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 31,
								'month_int'          => 12,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ca', 'ct', 'il', 'ia', 'mo', 'mt', 'nj', 'ny' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Lincolns Birthday',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 12,
								'month_int'          => 2,
								'always_week_day_id' => 3, //Closest
						]
				);
			}
//			if ( in_array( $province, array('ca', 'oh') ) ) {
//				$this->createRecurringHoliday(
//						array(
//								'company_id'         => $this->getCompany(),
//								'name'               => strtoupper( $province ) . ' - Rosa Parks Day',
//								'type_id'            => 10,
//								'special_day'        => 0,
//								//'pivot_day_direction_id' => 0,
//								//'week_interval' => 0,
//								//'day_of_week' => 0,
//								'day_of_month'       => 4,
//								'month_int'          => 2,
//								'always_week_day_id' => 3, //Closest
//						)
//				);
//			}

			if ( in_array( $province, [ 'va' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Lee-Jackson Day',
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 2,
								'day_of_week'        => 5,
								//'day_of_month' => 11,
								'month_int'          => 1,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'al' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Jefferson Davis Birthday',
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 1,
								'day_of_week'        => 1,
								//'day_of_month' => 11,
								'month_int'          => 6,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'fl' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Susan B. Anthony Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 15,
								'month_int'          => 2,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ca' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - César Chávez Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 31,
								'month_int'          => 3,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ut' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Pioneer Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 24,
								'month_int'          => 7,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ms' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Confederate Memorial Day', //Last monday in April.
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 30,
								//'week_interval' => 3,
								'day_of_week'            => 1,
								'day_of_month'           => 30,
								'month_int'              => 4,
								'always_week_day_id'     => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'al' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Confederate Memorial Day', //4th monday in April.
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 4,
								'day_of_week'        => 1,
								//'day_of_month' => 1,
								'month_int'          => 4,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ga' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Confederate Memorial Day', //Monday prior to April 26th
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 30,
								//'week_interval' => 0,
								'day_of_week'            => 1,
								'day_of_month'           => 26,
								'month_int'              => 4,
								'always_week_day_id'     => 3, //Closest

						]
				);
			}

			if ( in_array( $province, [ 'fl' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Confederate Memorial Day', //April 26th
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 26,
								'month_int'          => 4,
								'always_week_day_id' => 3, //Closest

						]
				);
			}

			if ( in_array( $province, [ 'sc' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Confederate Memorial Day', //May 10th.
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 10,
								'month_int'          => 5,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'vt' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Town Meeting Day', //Mar 3rd
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 3,
								'month_int'          => 3,
								'always_week_day_id' => 3, //Closest

						]
				);
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Bennington Battle Day', //Aug 16th
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 16,
								'month_int'          => 8,
								'always_week_day_id' => 3, //Closest

						]
				);
			}

			if ( in_array( $province, [ 'ne' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Arbor Day', //Last friday in April.
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 30,
								//'week_interval' => 3,
								'day_of_week'            => 5,
								'day_of_month'           => 30,
								'month_int'              => 4,
								'always_week_day_id'     => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ca' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Native American Day', //4th  Fri in Sept.
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 4,
								'day_of_week'        => 5,
								//'day_of_month' => 1,
								'month_int'          => 9,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'sd' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Native American Day', //2nd Mon in Oct
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 2,
								'day_of_week'        => 1,
								//'day_of_month' => 1,
								'month_int'          => 10,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ca' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Admission Day', //California Admission Day
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 9,
								'month_int'          => 9,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'wv' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - West Virginia Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 20,
								'month_int'          => 6,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ca', 'dc', 'mn' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Emancipation Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 16,
								'month_int'          => 4,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'me', 'ma' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Patriots Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 16,
								'month_int'          => 4,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'wi' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Juneteenth Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 19,
								'month_int'          => 6,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ak' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Alaska Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 18,
								'month_int'          => 10,
								'always_week_day_id' => 3, //Closest
						]
				);
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Sewards Day', //Last monday in March.
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 30,
								//'week_interval' => 3,
								'day_of_week'            => 1,
								'day_of_month'           => 30,
								'month_int'              => 3,
								'always_week_day_id'     => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ky' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Robert E. Lee Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 19,
								'month_int'          => 1,
								'always_week_day_id' => 3, //Closest
						]
				);
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Franklin D. Roosevelt Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 30,
								'month_int'          => 1,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'mo' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Truman Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 8,
								'month_int'          => 5,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'ri' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Independence Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 4,
								'month_int'          => 5,
								'always_week_day_id' => 3, //Closest
						]
				);

				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Victory Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 2,
								'day_of_week'        => 1, //Monday
								//'day_of_month' => 1,
								'month_int'          => 8,
								'always_week_day_id' => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'nv' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'             => $this->getCompany(),
								'name'                   => strtoupper( $province ) . ' - Nevada Day',
								'type_id'                => 30,
								'special_day'            => 0,
								'pivot_day_direction_id' => 30,
								//'week_interval' => 3,
								'day_of_week'            => 5,
								'day_of_month'           => 31,
								'month_int'              => 10,
								'always_week_day_id'     => 3, //Closest
						]
				);
			}

			if ( in_array( $province, [ 'hi' ] ) ) {
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Prince Kuhio Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 26,
								'month_int'          => 3,
								'always_week_day_id' => 3, //Closest
						]
				);
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Kamehameha Day',
								'type_id'            => 10,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								//'week_interval' => 0,
								//'day_of_week' => 0,
								'day_of_month'       => 11,
								'month_int'          => 6,
								'always_week_day_id' => 3, //Closest
						]
				);
				$this->createRecurringHoliday(
						[
								'company_id'         => $this->getCompany(),
								'name'               => strtoupper( $province ) . ' - Admission Day',
								'type_id'            => 20,
								'special_day'        => 0,
								//'pivot_day_direction_id' => 0,
								'week_interval'      => 3,
								'day_of_week'        => 5,
								//'day_of_month' => 1,
								'month_int'          => 8,
								'always_week_day_id' => 3, //Closest
						]
				);
			}
		}

		return true;
	}

	/**
	 * @param $name
	 * @return array|bool
	 */
	function getRegularTimePolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$rtplf = TTnew( 'RegularTimePolicyListFactory' ); /** @var RegularTimePolicyListFactory $rtplf */
		$rtplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $rtplf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $rtplf as $rtp_obj ) {
				$retarr[] = $rtp_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createRegularTimePolicy( $data ) {
		if ( is_array( $data ) ) {
			$rtpf = TTnew( 'RegularTimePolicyFactory' ); /** @var RegularTimePolicyFactory $rtpf */
			$rtpf->setObjectFromArray( $data );
			if ( $rtpf->isValid() ) {
				return $rtpf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function RegularTimePolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			$this->createRegularTimePolicy(
					[
							'company_id'                   => $this->getCompany(),
							'name'                         => 'Regular Time',
							'calculation_order'            => 9999,
							'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ), //Include Meal/Breaks by default so if they switch to using Auto-Deduct/Auto-Add meal policies regular time takes them into account.
							'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'Regular Time' ) ),
					]
			);
		}

		return true;
	}

	/**
	 * @param $name
	 * @return array|bool
	 */
	function getOverTimePolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$otplf = TTnew( 'OverTimePolicyListFactory' ); /** @var OverTimePolicyListFactory $otplf */
		$otplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $otplf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $otplf as $otp_obj ) {
				$retarr[] = $otp_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createOverTimePolicy( $data ) {
		if ( is_array( $data ) ) {

			$otpf = TTnew( 'OverTimePolicyFactory' ); /** @var OverTimePolicyFactory $otpf */
			$otpf->setObjectFromArray( $data );
			if ( $otpf->isValid() ) {
				return $otpf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function OverTimePolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['over_time_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['over_time_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				case 'ca':
				case 'us':
					//Need to prefix the country/province on the overtime policies so they get a break-down of BC OT vs AB OT for example.
					//This also makes it easier to create policy groups.
					$this->createOverTimePolicy(
							[
									'company_id'                   => $this->getCompany(),
									'name'                         => strtoupper( $country ) . ' - Holiday',
									'type_id'                      => 180, //Holiday
									'trigger_time'                 => 0, //0hrs
									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ), //Don't include Meak/Break time as its already included in Regular Time by default.
									'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
							]
					);
					break;
				default:
					//Default policies for other countries.
					$this->createOverTimePolicy(
							[
									'company_id'                   => $this->getCompany(),
									'name'                         => strtoupper( $country ) . ' - Daily >8hrs',
									'type_id'                      => 10, //Daily
									'trigger_time'                 => ( 8 * 3600 ), //8hrs
									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
									'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
							]
					);
					$this->createOverTimePolicy(
							[
									'company_id'                   => $this->getCompany(),
									'name'                         => strtoupper( $country ) . ' - Weekly >40hrs',
									'type_id'                      => 20, //Weekly
									'trigger_time'                 => ( 40 * 3600 ), //40hrs
									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
									'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
							]
					);
					$this->createOverTimePolicy(
							[
									'company_id'                   => $this->getCompany(),
									'name'                         => strtoupper( $country ) . ' - Holiday',
									'type_id'                      => 180, //Holiday
									'trigger_time'                 => 0, //0hrs
									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
									'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
							]
					);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			if ( in_array( $province, [ 'bc', 'ab', 'sk', 'mb', 'yt', 'nt', 'nu' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Daily >8hrs',
								'type_id'                      => 10, //Daily
								'trigger_time'                 => ( 8 * 3600 ), //8hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}
			if ( in_array( $province, [ 'bc' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Daily >12hrs',
								'type_id'                      => 10, //Daily
								'trigger_time'                 => ( 12 * 3600 ), //8hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
						]
				);
			}

			if ( in_array( $province, [ 'bc', 'sk', 'mb', 'qc', 'nl', 'yt', 'nt', 'nu' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >40hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 40 * 3600 ), //40hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}
			if ( in_array( $province, [ 'ab', 'on', 'nb' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >44hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 44 * 3600 ), //44hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}
			if ( in_array( $province, [ 'ns' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >48hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 48 * 3600 ), //48hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}
		}

		//US
		if ( $country == 'us' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			if ( in_array( $province, [ 'ca', 'ak', 'nv' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Daily >8hrs',
								'type_id'                      => 10, //Daily
								'trigger_time'                 => ( 8 * 3600 ), //8hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}

			if ( in_array( $province, [ 'ca', 'co' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Daily >12hrs',
								'type_id'                      => 10, //Daily
								'trigger_time'                 => ( 12 * 3600 ), //12hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
						]
				);
			}

			if ( in_array( $province, [ 'ca', 'ky' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - 7th Consecutive Day',
								'type_id'                      => 155, //Daily
								'trigger_time'                 => ( 0 * 3600 ), //0hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}

			if ( in_array( $province, [ 'ca' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - 7th Consecutive Day >8hrs',
								'type_id'                      => 155, //Daily
								'trigger_time'                 => ( 8 * 3600 ), //0hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
						]
				);
			}


			if ( in_array( $province, [ 'mn' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >48hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 48 * 3600 ), //40hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			} else if ( in_array( $province, [ 'ks' ] ) ) {
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >46hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 46 * 3600 ), //40hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			} else {
				//Always have a weekly OT policy for reference at least, many companies probably implement it on their own anyways?
				$this->createOverTimePolicy(
						[
								'company_id'                   => $this->getCompany(),
								'name'                         => strtoupper( $province ) . ' - Weekly >40hrs',
								'type_id'                      => 20, //Weekly
								'trigger_time'                 => ( 40 * 3600 ), //40hrs
								'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'pay_code_id'                  => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
						]
				);
			}
		}

		return true;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function getExceptionPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$eplf = TTnew( 'ExceptionPolicyControlListFactory' ); /** @var ExceptionPolicyControlListFactory $eplf */
		$eplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $eplf->getRecordCount() > 0 ) {
			return $eplf->getCurrent()->getID();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createExceptionPolicy( $data ) {
		if ( is_array( $data ) ) {
			$epcf = TTnew( 'ExceptionPolicyControlFactory' ); /** @var ExceptionPolicyControlFactory $epcf */
			$epcf->setObjectFromArray( $data );
			if ( $epcf->isValid() ) {
				$control_id = $epcf->Save();
				if ( TTUUID::isUUID( $control_id ) && $control_id != TTUUID::getZeroID() && $control_id != TTUUID::getNotExistID() ) {
					$epf = TTnew( 'ExceptionPolicyFactory' ); /** @var ExceptionPolicyFactory $epf */

					$data = $epf->getExceptionTypeDefaultValues( null, $this->getCompanyObject()->getProductEdition() );
					if ( is_array( $data ) ) {
						foreach ( $data as $exception_policy_data ) {
							$exception_policy_data['exception_policy_control_id'] = $control_id;
							unset( $exception_policy_data['id'] );

							$epf->setObjectFromArray( $exception_policy_data );
							if ( $epf->isValid() ) {
								$epf->Save();
							}
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function ExceptionPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				default:
					//Default policies for other countries.
					$this->createExceptionPolicy(
							[
									'company_id' => $this->getCompany(),
									'name'       => 'Default',
							]
					);
					break;
			}
		}

		return true;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function getMealPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$mplf = TTnew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $mplf */
		$mplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $mplf->getRecordCount() > 0 ) {
			return $mplf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createMealPolicy( $data ) {
		if ( is_array( $data ) ) {
			$mpf = TTnew( 'MealPolicyFactory' ); /** @var MealPolicyFactory $mpf */
			$mpf->setObjectFromArray( $data );
			if ( $mpf->isValid() ) {
				return $mpf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function MealPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['meal_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['meal_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				default:
					//Default policies for other countries.
					$this->createMealPolicy(
							[
									'company_id'               => $this->getCompany(),
									'name'                     => '30min Lunch',
									'type_id'                  => 20, //Normal
									'trigger_time'             => ( 5 * 3600 ), //5hrs
									'amount'                   => 1800, //30min
									'auto_detect_type_id'      => 20, //Punch Type
									'minimum_punch_time'       => ( 20 * 60 ), //20min
									'maximum_punch_time'       => ( 40 * 60 ), //40min
									'include_lunch_punch_time' => false,
									'pay_code_id'              => current( $this->getPayCodeByCompanyIDAndName( 'Lunch Time' ) ),
							]
					);
					$this->createMealPolicy(
							[
									'company_id'               => $this->getCompany(),
									'name'                     => '60min Lunch',
									'type_id'                  => 20, //Normal
									'trigger_time'             => ( 7 * 3600 ), //7hrs
									'amount'                   => 3600, //60min
									'auto_detect_type_id'      => 20, //Punch Type
									'minimum_punch_time'       => ( 45 * 60 ), //20min
									'maximum_punch_time'       => ( 75 * 60 ), //40min
									'include_lunch_punch_time' => false,
									'pay_code_id'              => current( $this->getPayCodeByCompanyIDAndName( 'Lunch Time' ) ),
							]
					);

					break;
			}
		}

		return true;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function getBreakPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$bplf = TTnew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
		$bplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $bplf->getRecordCount() > 0 ) {
			return $bplf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createBreakPolicy( $data ) {
		if ( is_array( $data ) ) {
			$bpf = TTnew( 'BreakPolicyFactory' ); /** @var BreakPolicyFactory $bpf */
			$bpf->setObjectFromArray( $data );
			if ( $bpf->isValid() ) {
				return $bpf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function BreakPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				default:
					//Default policies for other countries.
					$this->createBreakPolicy(
							[
									'company_id'               => $this->getCompany(),
									'name'                     => 'Break1',
									'type_id'                  => 20, //Normal
									'trigger_time'             => ( 2 * 3600 ), //2hrs
									'amount'                   => ( 15 * 60 ), //15min
									'auto_detect_type_id'      => 20, //Punch Type
									'minimum_punch_time'       => ( 5 * 60 ), //5min
									'maximum_punch_time'       => ( 19 * 60 ), //19min
									'include_break_punch_time' => false,
									'pay_code_id'              => current( $this->getPayCodeByCompanyIDAndName( 'Break Time' ) ),
							]
					);
					$this->createBreakPolicy(
							[
									'company_id'               => $this->getCompany(),
									'name'                     => 'Break2',
									'type_id'                  => 20, //Normal
									'trigger_time'             => ( 5 * 3600 ), //5hrs
									'amount'                   => ( 15 * 60 ), //15min
									'auto_detect_type_id'      => 20, //Punch Type
									'minimum_punch_time'       => ( 5 * 60 ), //5min
									'maximum_punch_time'       => ( 19 * 60 ), //19min
									'include_break_punch_time' => false,
									'pay_code_id'              => current( $this->getPayCodeByCompanyIDAndName( 'Break Time' ) ),
							]
					);

					break;
			}
		}

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createSchedulePolicy( $data ) {
		if ( is_array( $data ) ) {
			$spf = TTnew( 'SchedulePolicyFactory' ); /** @var SchedulePolicyFactory $spf */
			$data['id'] = $spf->getNextInsertId();

			$spf->setObjectFromArray( $data );
			if ( $spf->isValid() ) {
				return $spf->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function SchedulePolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['schedule_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['schedule_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				default:
					//Default policies for other countries.
					$this->createSchedulePolicy(
							[
									'company_id'        => $this->getCompany(),
									'name'              => 'No Lunch',
									'meal_policy'       => [ -1 ],
									'break_policy'      => [],
									'start_stop_window' => ( 3600 * 2 ), //1 hr
							]
					);
					$this->createSchedulePolicy(
							[
									'company_id'        => $this->getCompany(),
									'name'              => 'No Lunch / No Break',
									'meal_policy'       => [ -1 ],
									'break_policy'      => [ -1 ],
									'start_stop_window' => ( 3600 * 2 ), //1 hr
							]
					);
					$this->createSchedulePolicy(
							[
									'company_id'        => $this->getCompany(),
									'name'              => '30min Lunch',
									'meal_policy'       => (array)$this->getMealPolicyByCompanyIDAndName( '30min Lunch' ),
									'break_policy'      => [ 0 ],
									'start_stop_window' => ( 3600 * 2 ), //1 hr
							]
					);
					$this->createSchedulePolicy(
							[
									'company_id'        => $this->getCompany(),
									'name'              => '60min Lunch',
									'meal_policy'       => (array)$this->getMealPolicyByCompanyIDAndName( '60min Lunch' ),
									'break_policy'      => [ 0 ],
									'start_stop_window' => ( 3600 * 2 ), //1 hr
							]
					);
					break;
			}
		}

		return true;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	function getAccrualPolicyAccountByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$apalf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apalf */
		$apalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $apalf->getRecordCount() > 0 ) {
			return $apalf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createAccrualPolicyAccount( $data ) {
		if ( is_array( $data ) ) {
			$apaf = TTnew( 'AccrualPolicyAccountFactory' ); /** @var AccrualPolicyAccountFactory $apaf */
			$apaf->setObjectFromArray( $data );
			if ( $apaf->isValid() ) {
				return $apaf->Save();
			}
		}

		return false;
	}

	/**
	 * @param int $type_id
	 * @param $name
	 * @return bool
	 */
	function getAccrualPolicyByCompanyIDAndTypeAndName( $type_id, $name ) {
		$filter_data = [
				'type_id' => [ $type_id ],
				'name'    => $name,
		];
		$acplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $acplf */
		$acplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $acplf->getRecordCount() > 0 ) {
			return $acplf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createAccrualPolicy( $data ) {
		if ( is_array( $data ) ) {
			$apf = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $apf */
			$apf->setObjectFromArray( $data );
			if ( $apf->isValid() ) {
				return $apf->Save();
			}
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createAccrualPolicyMilestone( $data ) {
		if ( is_array( $data ) ) {
			$apmf = TTnew( 'AccrualPolicyMilestoneFactory' ); /** @var AccrualPolicyMilestoneFactory $apmf */
			$apmf->setObjectFromArray( $data );
			if ( $apmf->isValid() ) {
				return $apmf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function AccrualPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['accrual_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['accrual_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			//Time Bank
			$this->createAccrualPolicyAccount(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Time Bank',
							'enable_pay_stub_balance_display' => true,
					]
			);

			switch ( $country ) {
				case 'ca':
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Vacation',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Sick',
									'enable_pay_stub_balance_display' => true,
							]
					);

					break;
				case 'us':
					//Vacation
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Paid Time Off (PTO)',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$accrual_policy_id = $this->createAccrualPolicy(
							[
									'company_id'                   => $this->getCompany(),
									'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Paid Time Off (PTO)' ),
									'name'                         => 'Paid Time Off (PTO)',
									'type_id'                      => 20, //Calendar
									'apply_frequency_id'           => 10, //Each pay period.
									'milestone_rollover_hire_date' => true,
									'minimum_employed_days'        => 0,
							]
					);

					if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 0,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 5,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
					}

					//Vacation
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Vacation',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$accrual_policy_id = $this->createAccrualPolicy(
							[
									'company_id'                   => $this->getCompany(),
									'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' ),
									'name'                         => 'Vacation',
									'type_id'                      => 20, //Calendar
									'apply_frequency_id'           => 10, //Each pay period.
									'milestone_rollover_hire_date' => true,
									'minimum_employed_days'        => 0,
							]
					);

					if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 0,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 5,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
					}
					unset( $accrual_policy_id );

					//Sick
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Sick',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$accrual_policy_id = $this->createAccrualPolicy(
							[
									'company_id'                   => $this->getCompany(),
									'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' ),
									'name'                         => 'Sick',
									'type_id'                      => 20, //Calendar
									'apply_frequency_id'           => 10, //Each pay period.
									'milestone_rollover_hire_date' => true,
									'minimum_employed_days'        => 0,
							]
					);
					if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 0,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 5,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
					}
					unset( $accrual_policy_id );

					break;
				default:
					//Vacation
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Vacation',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$accrual_policy_id = $this->createAccrualPolicy(
							[
									'company_id'                   => $this->getCompany(),
									'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' ),
									'name'                         => 'Vacation',
									'type_id'                      => 20, //Calendar
									'apply_frequency_id'           => 10, //Each pay period.
									'milestone_rollover_hire_date' => true,
									'minimum_employed_days'        => 0,
							]
					);

					if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 0,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 5,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
					}
					unset( $accrual_policy_id );

					//Sick
					$this->createAccrualPolicyAccount(
							[
									'company_id'                      => $this->getCompany(),
									'name'                            => 'Sick',
									'enable_pay_stub_balance_display' => true,
							]
					);

					$accrual_policy_id = $this->createAccrualPolicy(
							[
									'company_id'                   => $this->getCompany(),
									'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' ),
									'name'                         => 'Sick',
									'type_id'                      => 20, //Calendar
									'apply_frequency_id'           => 10, //Each pay period.
									'milestone_rollover_hire_date' => true,
									'minimum_employed_days'        => 0,
							]
					);
					if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 0,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
						$this->createAccrualPolicyMilestone(
								[
										'accrual_policy_id'         => $accrual_policy_id,
										'length_of_service'         => 5,
										'length_of_service_unit_id' => 40, //Years
										'accrual_rate'              => ( 0 * 3600 ),
										'maximum_time'              => ( 0 * 3600 ),
										'rollover_time'             => ( 9999 * 3600 ),
								]
						);
					}
					unset( $accrual_policy_id );

					break;
			}
		}

		//Canada
		if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );

			$accrual_policy_id = $this->createAccrualPolicy(
					[
							'company_id'                   => $this->getCompany(),
							'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' ),
							'name'                         => strtoupper( $province ) . ' - Vacation',
							'type_id'                      => 20, //Calendar
							'apply_frequency_id'           => 10, //Each pay period.
							'milestone_rollover_hire_date' => true,
							'minimum_employed_days'        => 0,
					]
			);

			if ( in_array( $province, [ 'bc', 'ab', 'mb', 'qc', 'nt', 'nu' ] ) ) {
				if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 0,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 80 * 3600 ),
									'maximum_time'              => ( 80 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 5,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 120 * 3600 ),
									'maximum_time'              => ( 120 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
				}
				unset( $accrual_policy_id );
			}

			if ( in_array( $province, [ 'sk' ] ) ) {
				if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 0,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 120 * 3600 ),
									'maximum_time'              => ( 120 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 10,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 160 * 3600 ),
									'maximum_time'              => ( 160 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
				}
				unset( $accrual_policy_id );
			}

			if ( in_array( $province, [ 'on', 'yt' ] ) ) {
				if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 0,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 80 * 3600 ),
									'maximum_time'              => ( 80 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
				}
				unset( $accrual_policy_id );
			}

			if ( in_array( $province, [ 'nb', 'ns', 'pe' ] ) ) {
				if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 0,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 80 * 3600 ),
									'maximum_time'              => ( 80 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 8,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 120 * 3600 ),
									'maximum_time'              => ( 120 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
				}
				unset( $accrual_policy_id );
			}

			if ( in_array( $province, [ 'nl' ] ) ) {
				if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 0,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 80 * 3600 ),
									'maximum_time'              => ( 80 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
					$this->createAccrualPolicyMilestone(
							[
									'accrual_policy_id'         => $accrual_policy_id,
									'length_of_service'         => 15,
									'length_of_service_unit_id' => 40, //Years
									'accrual_rate'              => ( 120 * 3600 ),
									'maximum_time'              => ( 120 * 3600 ),
									'rollover_time'             => ( 9999 * 3600 ),
							]
					);
				}
				unset( $accrual_policy_id );
			}

			$accrual_policy_id = $this->createAccrualPolicy(
					[
							'company_id'                   => $this->getCompany(),
							'accrual_policy_account_id'    => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' ),
							'name'                         => 'Sick',
							'type_id'                      => 20, //Calendar
							'apply_frequency_id'           => 10, //Each pay period.
							'milestone_rollover_hire_date' => true,
							'minimum_employed_days'        => 0,
					]
			);
			if ( TTUUID::isUUID( $accrual_policy_id ) && $accrual_policy_id != TTUUID::getZeroID() && $accrual_policy_id != TTUUID::getNotExistID() ) {
				$this->createAccrualPolicyMilestone(
						[
								'accrual_policy_id'         => $accrual_policy_id,
								'length_of_service'         => 0,
								'length_of_service_unit_id' => 40, //Years
								'accrual_rate'              => ( 0 * 3600 ),
								'maximum_time'              => ( 0 * 3600 ),
								'rollover_time'             => ( 9999 * 3600 ),
						]
				);
				$this->createAccrualPolicyMilestone(
						[
								'accrual_policy_id'         => $accrual_policy_id,
								'length_of_service'         => 5,
								'length_of_service_unit_id' => 40, //Years
								'accrual_rate'              => ( 0 * 3600 ),
								'maximum_time'              => ( 0 * 3600 ),
								'rollover_time'             => ( 9999 * 3600 ),
						]
				);
			}
			unset( $accrual_policy_id );
		}

		return true;
	}


	/**
	 * @param $name
	 * @return array|bool
	 */
	function getAbsencePolicyByCompanyIDAndTypeAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
		$aplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $aplf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $aplf as $ap_obj ) {
				$retarr[] = $ap_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createAbsencePolicy( $data ) {
		if ( is_array( $data ) ) {
			$apf = TTnew( 'AbsencePolicyFactory' ); /** @var AbsencePolicyFactory $apf */
			$apf->setObjectFromArray( $data );
			if ( $apf->isValid() ) {
				return $apf->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function AbsencePolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['absence_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['absence_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {

			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Jury Duty',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Jury Duty' ) ),
					]
			);
			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Bereavement',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Bereavement' ) ),
					]
			);

			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Statutory Holiday',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Statutory Holiday' ) ),
					]
			);
			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Time Bank (Withdrawal)',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Time Bank (Withdrawal)' ) ),
					]
			);

			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Vacation (PAID)',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Vacation' ) ),
					]
			);

			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Vacation (UNPAID)',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Vacation (UNPAID)' ) ),
					]
			);

			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Sick (PAID)',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Sick' ) ),
					]
			);
			$this->createAbsencePolicy(
					[
							'company_id'  => $this->getCompany(),
							'name'        => 'Sick (UNPAID)',
							'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Sick (UNPAID)' ) ),
					]
			);

			if ( $country == 'us' ) {
				$this->createAbsencePolicy(
						[
								'company_id'  => $this->getCompany(),
								'name'        => 'Paid Time Off (PTO)',
								'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Paid Time Off (PTO)' ) ),
						]
				);
			}
		}

		return true;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	function getPayFormulaPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$lf = TTnew( 'PayFormulaPolicyListFactory' ); /** @var PayFormulaPolicyListFactory $lf */
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			foreach ( $lf as $obj ) {
				return $obj->getCurrent()->getId();
			}
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPayFormulaPolicy( $data ) {
		if ( is_array( $data ) ) {
			$f = TTnew( 'PayFormulaPolicyFactory' ); /** @var PayFormulaPolicyFactory $f */
			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function PayFormulaPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['pay_formula_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['pay_formula_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $country != '' && $province == '' ) {
			//Common for all countries.
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'None ($0)',
							'pay_type_id'       => 10, //Pay Multiplied By Factor
							'rate'              => 0.00,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'Regular',
							'pay_type_id'       => 10, //Pay Multiplied By Factor
							'rate'              => 1.00,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'OverTime (1.5x)',
							'pay_type_id'       => 10, //Pay Multiplied By Factor
							'rate'              => 1.50,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'OverTime (2.0x)',
							'pay_type_id'       => 10, //Pay Multiplied By Factor
							'rate'              => 2.00,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'Premium 1',
							'pay_type_id'       => 50, //Premium Only
							'rate'              => 0.50,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'        => $this->getCompany(),
							'name'              => 'Premium 2',
							'pay_type_id'       => 50, //Premium Only
							'rate'              => 0.75,
							'wage_group_id'     => 0,
							'accrual_rate'      => 0.00,
							'accrual_policy_id' => 0,
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Time Bank (Deposit)',
							'pay_type_id'               => 10, //Pay Multiplied By Factor
							'rate'                      => 0.00,
							'wage_group_id'             => 0,
							'accrual_rate'              => 1.00, //Increase accrual when this is used.
							'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Time Bank' ),
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Time Bank (Withdrawal)',
							'pay_type_id'               => 10, //Pay Multiplied By Factor
							'rate'                      => 1.00,
							'wage_group_id'             => 0,
							'accrual_rate'              => -1.00, //Reduce accrual when this is used
							'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Time Bank' ),
					]
			);

			$this->createPayFormulaPolicy(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Sick',
							'pay_type_id'               => 10, //Pay Multiplied By Factor
							'rate'                      => 1.00,
							'wage_group_id'             => 0,
							'accrual_rate'              => -1.00, //Reduce accrual when this is used
							'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' ),
					]
			);
			$this->createPayFormulaPolicy(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Vacation',
							'pay_type_id'               => 10, //Pay Multiplied By Factor
							'rate'                      => 1.00,
							'wage_group_id'             => 0,
							'accrual_rate'              => -1.00, //Reduce accrual when this is used
							'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' ),
					]
			);

			if ( $country == 'us' ) {
				$this->createPayFormulaPolicy(
						[
								'company_id'                => $this->getCompany(),
								'name'                      => 'Paid Time Off (PTO)',
								'pay_type_id'               => 10, //Pay Multiplied By Factor
								'rate'                      => 1.00,
								'wage_group_id'             => 0,
								'accrual_rate'              => -1.00, //Reduce accrual when this is used
								'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Paid Time Off (PTO)' ),
						]
				);
			}
		}

		return true;
	}


	/**
	 * @param $name
	 * @return array|bool
	 */
	function getPayCodeByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$lf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $lf */
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $lf as $obj ) {
				$retarr[] = $obj->getCurrent()->getId();
				//return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPayCode( $data ) {
		if ( is_array( $data ) ) {
			$f = TTnew( 'PayCodeFactory' ); /** @var PayCodeFactory $f */
			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save();
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function PayCode( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['pay_code_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['pay_code_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $country != '' && $province == '' ) {
			//Common for all countries.
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'UnPaid',
							'code'                      => 'UNPAID',
							'type_id'                   => 20, //UNPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Regular Time',
							'code'                      => 'REG',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Lunch Time',
							'code'                      => 'LNH',
							'type_id'                   => 20, //UNPAID: Because it contributes to regular time by default, so if its marked as paid it gets double deducted/added to Paid Time column in reports.
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Break Time',
							'code'                      => 'BRK',
							'type_id'                   => 20, //UNPAID: Because it contributes to regular time by default, so if its marked as paid it gets double deducted/added to Paid Time column in reports.
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'OverTime (1.5x)',
							'code'                      => 'OT1',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 1' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'OverTime (1.5x)' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'OverTime (2.0x)',
							'code'                      => 'OT1',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 2' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'OverTime (2.0x)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Premium 1',
							'code'                      => 'PRE1',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 1' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Premium 1' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Premium 2',
							'code'                      => 'PRE2',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 2' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Premium 2' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Time Bank (Deposit)',
							'code'                      => 'BANK',
							'type_id'                   => 20, //UNPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Time Bank (Deposit)' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Time Bank (Withdrawal)',
							'code'                      => 'BANK',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Time Bank (Withdrawal)' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Time Bank (Withdrawal)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Statutory Holiday',
							'code'                      => 'STAT',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Statutory Holiday' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Jury Duty',
							'code'                      => 'JURY',
							'type_id'                   => 20, //UnPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Bereavement',
							'code'                      => 'BEREAV',
							'type_id'                   => 20, //UnPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Sick',
							'code'                      => 'SICK',
							'type_id'                   => 10, //PAID
							'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Sick' ),
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Sick' ),
					]
			);
			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Sick (UNPAID)',
							'code'                      => 'USICK',
							'type_id'                   => 20, //UnPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
					]
			);

			$this->createPayCode(
					[
							'company_id'                => $this->getCompany(),
							'name'                      => 'Vacation (UNPAID)',
							'code'                      => 'UVAC',
							'type_id'                   => 20, //UnPAID
							'pay_stub_entry_account_id' => 0,
							'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
					]
			);

			if ( $country == 'us' ) {
				$this->createPayCode(
						[
								'company_id'                => $this->getCompany(),
								'name'                      => 'Vacation',
								'code'                      => 'VAC',
								'type_id'                   => 10, //PAID
								'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation' ),
								'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Vacation' ),
						]
				);

				$this->createPayCode(
						[
								'company_id'                => $this->getCompany(),
								'name'                      => 'Paid Time Off (PTO)',
								'code'                      => 'PTO',
								'type_id'                   => 10, //PAID
								'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Paid Time Off (PTO)' ),
								'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Paid Time Off (PTO)' ),
						]
				);
			} else if ( $country == 'ca' ) {
				$this->createPayCode(
						[
								'company_id'                => $this->getCompany(),
								'name'                      => 'Vacation',
								'code'                      => 'VAC',
								'type_id'                   => 10, //PAID
								'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
								'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Vacation' ),
						]
				);
			} else {
				$this->createPayCode(
						[
								'company_id'                => $this->getCompany(),
								'name'                      => 'Vacation',
								'code'                      => 'VAC',
								'type_id'                   => 10, //PAID
								'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation' ),
								'pay_formula_policy_id'     => $this->getPayFormulaPolicyByCompanyIDAndName( 'Vacation' ),
						]
				);
			}
		}

		return true;
	}

	/**
	 * @param $name
	 * @return array|bool
	 */
	function getContributingPayCodePolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$lf = TTnew( 'ContributingPayCodePolicyListFactory' ); /** @var ContributingPayCodePolicyListFactory $lf */
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $lf as $obj ) {
				//$retarr[] = $obj->getCurrent()->getId();
				return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createContributingPayCodePolicy( $data ) {
		if ( is_array( $data ) ) {
			$f = TTnew( 'ContributingPayCodePolicyFactory' ); /** @var ContributingPayCodePolicyFactory $f */
			$data['id'] = $f->getNextInsertId();

			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function ContributingPayCodePolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['contributing_pay_code_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['contributing_pay_code_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $country != '' && $province == '' ) {
			//Common for all countries.
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + Meal',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + Meal + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' )
							),
					]
			);

			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + Paid Absence',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
									(array)$this->getPayCodeByCompanyIDAndName( '%Vacation' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Paid Time Off (PTO)' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + Paid Absence + Meal + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
									(array)$this->getPayCodeByCompanyIDAndName( '%Vacation' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Paid Time Off (PTO)' )
							),
					]
			);


			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT + Meal',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT + Meal + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT + Paid Absence',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
									(array)$this->getPayCodeByCompanyIDAndName( '%Vacation' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Paid Time Off (PTO)' )
							),
					]
			);
			$this->createContributingPayCodePolicy(
					[
							'company_id' => $this->getCompany(),
							'name'       => 'Regular Time + OT + Paid Absence + Meal + Break',
							'pay_code'   => array_merge(
									(array)$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Break Time' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
									(array)$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
									(array)$this->getPayCodeByCompanyIDAndName( '%Vacation' ),
									(array)$this->getPayCodeByCompanyIDAndName( 'Paid Time Off (PTO)' )
							),
					]
			);
		}

		return true;
	}


	/**
	 * @param $name
	 * @return array|bool
	 */
	function getContributingShiftPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$lf = TTnew( 'ContributingShiftPolicyListFactory' ); /** @var ContributingShiftPolicyListFactory $lf */
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $lf as $obj ) {
				//$retarr[] = $obj->getCurrent()->getId();
				return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createContributingShiftPolicy( $data ) {
		if ( is_array( $data ) ) {
			$f = TTnew( 'ContributingShiftPolicyFactory' ); /** @var ContributingShiftPolicyFactory $f */
			$data['id'] = $f->getNextInsertId();

			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function ContributingShiftPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['contributing_shift_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['contributing_shift_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $country != '' && $province == '' ) {
			//Common for all countries.
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + Break',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Break' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + Meal',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Meal' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + Meal + Break',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + Paid Absence',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Paid Absence' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + Paid Absence + Meal + Break',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Paid Absence + Meal + Break' ),
					]
			);

			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + OT',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + OT + Meal',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Meal' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + OT + Meal + Break',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Meal + Break' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + OT + Paid Absence',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
					]
			);
			$this->createContributingShiftPolicy(
					[
							'company_id'                      => $this->getCompany(),
							'name'                            => 'Regular Time + OT + Paid Absence + Meal + Break',
							'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
					]
			);
		}

		return true;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	function getHolidayPolicyByCompanyIDAndName( $name ) {
		$filter_data = [
				'name' => $name,
		];
		$hplf = TTnew( 'HolidayPolicyListFactory' ); /** @var HolidayPolicyListFactory $hplf */
		$hplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $hplf->getRecordCount() > 0 ) {
			return $hplf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createHolidayPolicy( $data ) {
		if ( is_array( $data ) ) {
			$hpf = TTnew( 'HolidayPolicyFactory' ); /** @var HolidayPolicyFactory $hpf */
			$data['id'] = $hpf->getNextInsertId();

			if ( isset( $data['absence_policy_id'] ) && is_array( $data['absence_policy_id'] ) ) {
				$data['absence_policy_id'] = $data['absence_policy_id'][0];
			}

			$hpf->setObjectFromArray( $data );
			if ( $hpf->isValid() ) {
				return $hpf->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function HolidayPolicy( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['holiday_policy'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['holiday_policy'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				case 'ca':
					break;
				case 'us':
				default:
					//Default policies for other countries.
					$this->createHolidayPolicy(
							[
									'company_id'                 => $this->getCompany(),
									'name'                       => strtoupper( $country ) . ' - Statutory Holiday',
									'type_id'                    => 10, //Standard
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days'      => 30,

									'minimum_time'                          => ( 8 * 3600 ), //8hrs

									//'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
									//'recurring_holiday_id' => (array)$this->getRecurringHolidayByCompanyIDAndName( $country.'%' ),
									'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
									'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
							]
					);

					break;
			}
		}

		if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			if ( in_array( $province, [ 'bc' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 30,

								//Prior to the holiday
								'minimum_worked_days'              => 15, //Employee must work at least
								'minimum_worked_period_days'       => 30, //Of the last X days
								'worked_scheduled_days'            => 0, //Calendar days

								//After the holiday
								'minimum_worked_after_period_days' => 0,
								'minimum_worked_after_days'        => 0,
								'worked_after_scheduled_days'      => 0,

								//Averaging
								'average_time_days'                => 30, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}
			if ( in_array( $province, [ 'ab' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                 => $this->getCompany(),
								'name'                       => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                    => 30, //Advanced: Average
								'default_schedule_status_id' => 20, //Absent
								'minimum_employed_days'      => 30, //Employed at least 30 days prior to the holiday.

								//Prior to the holiday
								'minimum_worked_days'        => 5, //Employee must work at least
								'minimum_worked_period_days' => 9, //Of the last X days
								'worked_scheduled_days'      => 2, //Holiday Week Days

								'shift_on_holiday_type_id'         => 30, //Must work on the holiday if scheduled.

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 28, //Days to average time over (4 weeks)
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 20, //Divsor for average formula. X * 5% is the same as X / 20.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => false,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Paid Absence' ), //Was: 'Regular Time + OT + Paid Absence'
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}
			if ( in_array( $province, [ 'sk' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 0,

								//Prior to the holiday
								'minimum_worked_days'              => 0, //Employee must work at least
								'minimum_worked_period_days'       => 0, //Of the last X days
								'worked_scheduled_days'            => 0, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 0,
								'minimum_worked_after_days'        => 0,
								'worked_after_scheduled_days'      => 0,

								//Averaging
								'average_time_days'                => 28, //Days to average time over
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 20, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'mb' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 0,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 28, //Days to average time over
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 20, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'on' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 0,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 4, //Weeks to average time over
								'average_time_frequency_type_id'   => 15, //Weeks
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 20, //Divisor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
						]
				);
			}

			if ( in_array( $province, [ 'qc' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 0,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 28, //Days to average time over
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 20, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'nb' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 90,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 30, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}


			if ( in_array( $province, [ 'ns' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 0,

								//Prior to the holiday
								'minimum_worked_days'              => 15, //Employee must work at least
								'minimum_worked_period_days'       => 30, //Of the last X days
								'worked_scheduled_days'            => 0, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 30, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'pe' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 30,

								//Prior to the holiday
								'minimum_worked_days'              => 15, //Employee must work at least
								'minimum_worked_period_days'       => 30, //Of the last X days
								'worked_scheduled_days'            => 0, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 30, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'nl' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 30,

								//Prior to the holiday
								'minimum_worked_days'              => 15, //Employee must work at least
								'minimum_worked_period_days'       => 30, //Of the last X days
								'worked_scheduled_days'            => 0, //Holiday week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 21, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => true,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
						]
				);
			}

			if ( in_array( $province, [ 'yt' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 30,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Scheduled week days

								//After the holiday
								'minimum_worked_after_period_days' => 1,
								'minimum_worked_after_days'        => 1,
								'worked_after_scheduled_days'      => 1,

								//Averaging
								'average_time_days'                => 14, //Days to average time over
								'average_time_worked_days'         => false, //Only days worked
								'average_days'                     => 10, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => false,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
						]
				);
			}

			if ( in_array( $province, [ 'nt', 'nu' ] ) ) {
				$this->createHolidayPolicy(
						[
								'company_id'                       => $this->getCompany(),
								'name'                             => strtoupper( $province ) . ' - Statutory Holiday',
								'type_id'                          => 30, //Advanced: Average
								'default_schedule_status_id'       => 20, //Absent
								'minimum_employed_days'            => 30,

								//Prior to the holiday
								'minimum_worked_days'              => 1, //Employee must work at least
								'minimum_worked_period_days'       => 1, //Of the last X days
								'worked_scheduled_days'            => 1, //Scheduled week days

								//After the holiday
								'minimum_worked_after_period_days' => 0,
								'minimum_worked_after_days'        => 0,
								'worked_after_scheduled_days'      => 0,

								//Averaging
								'average_time_days'                => 28, //Days to average time over
								'average_time_worked_days'         => true, //Only days worked
								'average_days'                     => 0, //Divsor for average formula.

								'minimum_time' => 0,
								'maximum_time' => 0,

								'include_paid_absence_time'             => false,
								'absence_policy_id'                     => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 'Statutory Holiday' ),
								'recurring_holiday_id'                  => (array)$this->RecurringHolidaysByRegion( $country, $province ),
								'contributing_shift_policy_id'          => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
						]
				);
			}
		}

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function createPolicyGroup( $data ) {
		if ( is_array( $data ) ) {
			$pgf = TTnew( 'PolicyGroupFactory' ); /** @var PolicyGroupFactory $pgf */
			$data['id'] = $pgf->getNextInsertId();
			$pgf->setObjectFromArray( $data );
			if ( $pgf->isValid() ) {
				return $pgf->Save( true, true );
			}
		}

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @return bool
	 */
	function PolicyGroup( $country = null, $province = null, $district = null, $industry = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Prevent it from running multiple times for the same country/province pair.
		if ( isset( $this->already_processed['policy_group'][$country . $province . $district . $industry] ) ) {
			Debug::text( 'Already ran for Country: ' . $country . ' Province: ' . $province . ' skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->already_processed['policy_group'][$country . $province . $district . $industry] = true;
		}

		Debug::text( 'Country: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $country != '' && $province == '' ) {
			switch ( $country ) {
				case 'ca':
					break;
				case 'us':
					break;
				default:
					//Default policies for other countries.
					$this->createPolicyGroup(
							[
									'company_id'                  => $this->getCompany(),
									'name'                        => strtoupper( $province ) . ' - Hourly Employees',
									'regular_time_policy'         => (array)$this->getRegularTimePolicyByCompanyIDAndName( 'Regular Time' ),
									'over_time_policy'            => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $province ) . '%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $country ) . ' - Holiday' ) ),
									'meal_policy'                 => [ $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ],
									'accrual_policy'              => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper( $province ) . '%' ),
									'holiday_policy'              => $this->getHolidayPolicyByCompanyIDAndName( strtoupper( $province ) . '%' ),
									'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
									'absence_policy'              => $this->getAbsencePolicyByCompanyIDAndTypeAndName( '%' ),
							]
					);
					break;
			}
		}

		if ( $country == 'us' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			$this->createPolicyGroup(
					[
							'company_id'                  => $this->getCompany(),
							'name'                        => strtoupper( $province ) . ' - Hourly (OT Non-Exempt)',
							'regular_time_policy'         => (array)$this->getRegularTimePolicyByCompanyIDAndName( 'Regular Time' ),
							'over_time_policy'            => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $province ) . '%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $country ) . ' - Holiday' ) ),
							'meal_policy'                 => [ $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ],
							'accrual_policy'              => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper( $province ) . '%' ),
							'holiday_policy'              => $this->getHolidayPolicyByCompanyIDAndName( strtoupper( $province ) . '%' ),
							'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
							'absence_policy'              => $this->getAbsencePolicyByCompanyIDAndTypeAndName( '%' ),
					]
			);

			$this->createPolicyGroup(
					[
							'company_id'                  => $this->getCompany(),
							'name'                        => strtoupper( $province ) . ' - Salary (OT Exempt)',
							'regular_time_policy'         => (array)$this->getRegularTimePolicyByCompanyIDAndName( 'Regular Time' ),
							//'over_time_policy' => (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ),
							'meal_policy'                 => [ $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ],
							'accrual_policy'              => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper( $province ) . '%' ),
							'holiday_policy'              => $this->getHolidayPolicyByCompanyIDAndName( strtoupper( $province ) . '%' ),
							'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
							'absence_policy'              => $this->getAbsencePolicyByCompanyIDAndTypeAndName( '%' ),
					]
			);
		}
		if ( $country == 'ca' && $province != '' ) {
			Debug::text( 'Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			$this->createPolicyGroup(
					[
							'company_id'                  => $this->getCompany(),
							'name'                        => strtoupper( $province ) . ' - Hourly Employees',
							'regular_time_policy'         => (array)$this->getRegularTimePolicyByCompanyIDAndName( 'Regular Time' ),
							'over_time_policy'            => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $province ) . '%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper( $country ) . ' - Holiday' ) ),
							'meal_policy'                 => [ $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ],
							'accrual_policy'              => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper( $province ) . '%' ),
							'holiday_policy'              => $this->getHolidayPolicyByCompanyIDAndName( strtoupper( $province ) . '%' ),
							'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
							'absence_policy'              => $this->getAbsencePolicyByCompanyIDAndTypeAndName( '%' ),
					]
			);

			$this->createPolicyGroup(
					[
							'company_id'                  => $this->getCompany(),
							'name'                        => strtoupper( $province ) . ' - Salary Employees',
							'regular_time_policy'         => (array)$this->getRegularTimePolicyByCompanyIDAndName( 'Regular Time' ),
							//'over_time_policy' => (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ),
							'meal_policy'                 => [ $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ],
							'accrual_policy'              => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper( $province ) . '%' ),
							'holiday_policy'              => $this->getHolidayPolicyByCompanyIDAndName( strtoupper( $province ) . '%' ),
							'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
							'absence_policy'              => $this->getAbsencePolicyByCompanyIDAndTypeAndName( '%' ),
					]
			);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function Permissions() {
		//Always assume that Administrator permission group already exists.
		//This must be called before UserDefaults.
		Debug::text( 'Adding Preset Permission Groups', __FILE__, __LINE__, __METHOD__, 9 );

		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
		$pf->StartTransaction();

		$preset_flags = array_keys( $pf->getOptions( 'preset_flags' ) );
		$preset_options = $pf->getOptions( 'preset' );
		unset( $preset_options[40] ); //Remove Administration presets, as they should already exist.
		$preset_level_options = $pf->getOptions( 'preset_level' );
		foreach ( $preset_options as $preset_id => $preset_name ) {
			$pcf = TTnew( 'PermissionControlFactory' ); /** @var PermissionControlFactory $pcf */
			$pcf->setCompany( $this->getCompanyObject()->getID() );
			$pcf->setName( $preset_name );
			$pcf->setDescription( '' );
			$pcf->setLevel( $preset_level_options[$preset_id] );
			if ( $pcf->isValid() ) {
				$pcf_id = $pcf->Save( false );
				$pf->applyPreset( $pcf_id, $preset_id, $preset_flags );
			}
		}
		$pf->CommitTransaction();

		return true;
	}

	/**
	 * @param string $legal_entity_id UUID
	 * @return bool
	 */
	function UserDefaults( $legal_entity_id ) {
		if ( is_object( $this->getCompanyObject() ) ) {
			//User Default settings, always do this last.
			if ( is_object( $this->getCompanyObject()->getUserDefaultObject() ) ) {
				$udf = $this->getCompanyObject()->getUserDefaultObject();
			} else {
				$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */
			}
			$udf->setCompany( $this->getCompanyObject()->getID() );
			$udf->setLegalEntity( $legal_entity_id );
			$udf->setCity( $this->getCompanyObject()->getCity() );
			$udf->setCountry( $this->getCompanyObject()->getCountry() );
			$udf->setProvince( $this->getCompanyObject()->getProvince() );
			$udf->setWorkPhone( $this->getCompanyObject()->getWorkPhone() );

			$udf->setLanguage( 'en' );
			$udf->setItemsPerPage( 50 );

			//Get currently logged in user preferences and create defaults from those.
			if ( is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getUserPreferenceObject() ) ) {
				$udf->setDateFormat( $this->getUserObject()->getUserPreferenceObject()->getDateFormat() );
				$udf->setTimeFormat( $this->getUserObject()->getUserPreferenceObject()->getTimeFormat() );
				$udf->setTimeUnitFormat( $this->getUserObject()->getUserPreferenceObject()->getTimeUnitFormat() );
				$udf->setStartWeekDay( $this->getUserObject()->getUserPreferenceObject()->getStartWeekDay() );
				$udf->setDistanceFormat( $this->getUserObject()->getUserPreferenceObject()->getDistanceFormat() );
			} else {
				$udf->setDateFormat( 'd-M-y' );
				$udf->setTimeFormat( 'g:i A' );
				$udf->setTimeUnitFormat( 10 );
				$udf->setStartWeekDay( 0 );
				$udf->setDistanceFormat( 10 );
			}

			if ( strtoupper( $udf->getCountry() ) == 'US' ) {
				$udf->setDistanceFormat( 20 ); //20=Miles.
			}

			//Get Pay Period Schedule
			$ppslf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByCompanyId( $this->getCompanyObject()->getID() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$udf->setPayPeriodSchedule( $ppslf->getCurrent()->getID() );
			}

			//Get Policy Group
			$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getByCompanyId( $this->getCompanyObject()->getID() );
			if ( $pglf->getRecordCount() > 0 ) {
				$udf->setPolicyGroup( $pglf->getCurrent()->getID() );
			}

			//Permissions
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getByCompanyIdAndLevel( $this->getCompanyObject()->getID(), 10, 1, null, null, [ 'level' => 'desc' ] );
			if ( $pclf->getRecordCount() > 0 ) {
				$udf->setPermissionControl( $pclf->getCurrent()->getID() );
			}

			//Terminated Permissions
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getByCompanyIdAndLevel( $this->getCompanyObject()->getID(), 5, 1, null, null, [ 'level' => 'desc' ] );
			if ( $pclf->getRecordCount() > 0 ) {
				$udf->setTerminatedPermissionControl( $pclf->getCurrent()->getID() );
			}

			//Currency
			$clf = TTNew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $clf */
			$clf->getByCompanyIdAndDefault( $this->getCompany(), true );
			if ( $clf->getRecordCount() > 0 ) {
				$udf->setCurrency( $clf->getCurrent()->getID() );
			}

			$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
			$udf->setTimeZone( $upf->getLocationTimeZone( $this->getCompanyObject()->getCountry(), $this->getCompanyObject()->getProvince(), $this->getCompanyObject()->getWorkPhone() ) );
			Debug::text( 'Time Zone: ' . $udf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9 );

			$udf->setEnableEmailNotificationException( true );
			$udf->setEnableEmailNotificationMessage( true );
			$udf->setEnableEmailNotificationPayStub( true );
			$udf->setEnableEmailNotificationHome( true );

			if ( $udf->isValid() ) {
				Debug::text( 'Adding User Default settings...', __FILE__, __LINE__, __METHOD__, 9 );

				return $udf->Save();
			}
		}

		Debug::text( 'ERROR: Failed adding User Default settings...', __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @param null $country
	 * @param null $province
	 * @param null $district
	 * @param null $industry
	 * @param null $flags
	 * @param null $legal_entity_id
	 * @return bool
	 */
	function createPresets( $country = null, $province = null, $district = null, $industry = null, $flags = null, $legal_entity_id = null ) {
		$country = strtolower( $country );
		$province = strtolower( $province );

		//Policies: ( In Order )
		// Accrual
		// PayFormula
		// PayCode
		// Absence
		// Overtime
		// Meal
		// Break
		// Holiday
		// Schedule
		// Premium
		// Exception
		// Policy Groups
		// UserDefaults (this should be called manually after everything is done, outside of this function)
		if ( $country == '' ) {
			$this->RecurringHolidays();

			$this->PayrollRemittanceAgencys( null, null, null, null, $legal_entity_id );
			$this->PayStubAccounts();
			$this->CompanyDeductions( null, null, null, null, $legal_entity_id );

			$this->AccrualPolicy();
			$this->PayFormulaPolicy();
			$this->PayCode();
			$this->ContributingPayCodePolicy();
			$this->ContributingShiftPolicy();

			$this->AbsencePolicy();
			$this->HolidayPolicy();
			$this->RegularTimePolicy();
			$this->OverTimePolicy();
			$this->MealPolicy();
			$this->BreakPolicy();
			$this->SchedulePolicy();
			$this->ExceptionPolicy();

			$this->PolicyGroup();

			$this->TaxForms(); //Must go after Pay Stub Accounts and Absence Policies
		} else if ( $country != '' && $province == '' ) {
			$this->RecurringHolidays( $country ); //Must come before Agencies.

			$this->PayrollRemittanceAgencys( $country, null, null, null, $legal_entity_id );
			$this->PayStubAccounts( $country );
			$this->CompanyDeductions( $country, null, null, null, $legal_entity_id );

			$this->AccrualPolicy( $country );
			$this->PayFormulaPolicy( $country );
			$this->PayCode( $country );
			$this->ContributingPayCodePolicy( $country );
			$this->ContributingShiftPolicy( $country );

			$this->AbsencePolicy( $country );
			$this->HolidayPolicy( $country );
			$this->RegularTimePolicy( $country );
			$this->OverTimePolicy( $country );
			$this->MealPolicy( $country );
			$this->BreakPolicy( $country );
			$this->SchedulePolicy( $country );
			$this->ExceptionPolicy( $country );

			$this->PolicyGroup( $country );

			$this->TaxForms( $country ); //Must go after Pay Stub Accounts and Absence Policies
		} else if ( $country != '' && $province != '' ) {
			$this->RecurringHolidays( $country, $province );

			$this->PayrollRemittanceAgencys( $country, $province, null, null, $legal_entity_id );
			$this->PayStubAccounts( $country, $province );
			$this->CompanyDeductions( $country, $province, null, null, $legal_entity_id );

			$this->AccrualPolicy( $country, $province );
			$this->PayFormulaPolicy( $country, $province );
			$this->PayCode( $country, $province );
			$this->ContributingPayCodePolicy( $country, $province );
			$this->ContributingShiftPolicy( $country, $province );

			$this->AbsencePolicy( $country, $province );
			$this->HolidayPolicy( $country, $province );
			$this->RegularTimePolicy( $country, $province );
			$this->OverTimePolicy( $country, $province );
			$this->MealPolicy( $country, $province );
			$this->BreakPolicy( $country, $province );
			$this->SchedulePolicy( $country, $province );
			$this->ExceptionPolicy( $country, $province );

			$this->PolicyGroup( $country, $province );

			$this->TaxForms( $country, $province ); //Must go after Pay Stub Accounts and Absence Policies
		}

		return true;
	}
}

?>