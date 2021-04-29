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


/**
 * @package Modules\Users
 */
class UserDeductionFactory extends Factory {
	protected $table = 'user_deduction';
	protected $pk_sequence_name = 'user_deduction_id_seq'; //PK Sequence name

	var $user_obj = null;
	var $company_deduction_obj = null;
	var $pay_stub_entry_account_link_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1010-status'      => TTi18n::gettext( 'Status' ),
						'-1020-type'        => TTi18n::gettext( 'Type' ),
						'-1030-name'        => TTi18n::gettext( 'Tax / Deduction' ),
						'-1040-calculation' => TTi18n::gettext( 'Calculation' ),

						'-1110-first_name' => TTi18n::gettext( 'First Name' ),
						'-1120-last_name'  => TTi18n::gettext( 'Last Name' ),

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
						'type',
						'name',
						'calculation',
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
				'id'                   => 'ID',
				'user_id'              => 'User',
				'company_deduction_id' => 'CompanyDeduction',

				//CompanyDeduction
				'name'                 => false, //CompanyDeduction Name
				'status_id'            => false,
				'status'               => false,
				'type_id'              => false,
				'type'                 => false,
				'calculation_id'       => false,
				'calculation'          => false,

				'first_name'     => false,
				'last_name'      => false,
				'middle_name'    => false,
				'user_status_id' => false,
				'user_status'    => false,
				'full_name'      => false,

				'length_of_service_date' => 'LengthOfServiceDate',
				'start_date'             => 'StartDate',
				'end_date'               => 'EndDate',

				'user_value1'  => 'UserValue1',
				'user_value2'  => 'UserValue2',
				'user_value3'  => 'UserValue3',
				'user_value4'  => 'UserValue4',
				'user_value5'  => 'UserValue5',
				'user_value6'  => 'UserValue6',
				'user_value7'  => 'UserValue7',
				'user_value8'  => 'UserValue8',
				'user_value9'  => 'UserValue9',
				'user_value10' => 'UserValue10',

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool
	 */
	function getCompanyDeductionObject() {
		return $this->getGenericObject( 'CompanyDeductionListFactory', $this->getCompanyDeduction(), 'company_deduction_obj' );
	}

	/**
	 * Do not replace this with getGenericObject() as it uses the CompanyID not the ID itself.
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
	 * @param string $deduction_id UUID
	 * @return bool
	 */
	function isUniqueCompanyDeduction( $deduction_id ) {
		$ph = [
				'user_id'      => TTUUID::castUUID( $this->getUser() ),
				'deduction_id' => TTUUID::castUUID( $deduction_id ),
		];

		$query = 'select id from ' . $this->getTable() . ' where user_id = ? AND company_deduction_id = ? AND deleted = 0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Company Deduction: ' . $deduction_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyDeduction() {
		return $this->getGenericDataValue( 'company_deduction_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompanyDeduction( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_deduction_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getLengthOfServiceDate( $raw = false ) {
		$retval = false;
		$value = $this->getGenericDataValue( 'length_of_service_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				$retval = $value;
			} else {
				$retval = TTDate::strtotime( $value );
			}
		}

		if ( $retval == '' && $this->getColumn( 'hire_date' ) != '' ) {
			if ( $raw === true ) {
				return $this->getColumn( 'hire_date' );
			} else {
				return TTDate::strtotime( $this->getColumn( 'hire_date' ) );
			}
		} else {
			return $retval;
		}
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLengthOfServiceDate( $value ) {
		if ( $value != '' ) {
			$value = TTDate::getBeginDayEpoch( trim( $value ) );
		}
		Debug::Arr( $value, 'Length of Service Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'length_of_service_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getStartDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_date' );
		$retval = false;
		if ( $value !== false ) {
			if ( $raw === true ) {
				$retval = $value;
			} else {
				$retval = TTDate::strtotime( $value );
			}
		}

		if ( $retval == '' && $this->getColumn( 'company_deduction_start_date' ) != '' ) {
			if ( $raw === true ) {
				return $this->getColumn( 'company_deduction_start_date' );
			} else {
				return TTDate::strtotime( $this->getColumn( 'company_deduction_start_date' ) );
			}
		} else {
			return $retval;
		}
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		if ( $value != '' ) {
			$value = TTDate::getBeginDayEpoch( trim( $value ) );
		}
		Debug::Arr( $value, 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getEndDate( $raw = false ) {
		$retval = false;
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				$retval = $value;
			} else {
				$retval = TTDate::strtotime( $value );
			}
		}

		if ( $retval == '' && $this->getColumn( 'company_deduction_end_date' ) != '' ) {
			if ( $raw === true ) {
				return $this->getColumn( 'company_deduction_end_date' );
			} else {
				return TTDate::strtotime( $this->getColumn( 'company_deduction_end_date' ) );
			}
		} else {
			return $retval;
		}
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		if ( $value != '' ) {
			$value = TTDate::getBeginDayEpoch( trim( $value ) );
		}
		Debug::Arr( $value, 'End Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue1() {
		return $this->getGenericDataValue( 'user_value1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value1', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue2() {
		return $this->getGenericDataValue( 'user_value2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value2', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue3() {
		return $this->getGenericDataValue( 'user_value3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue3( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value3', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue4() {
		return $this->getGenericDataValue( 'user_value4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value4', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue5() {
		return $this->getGenericDataValue( 'user_value5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value5', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue6() {
		return $this->getGenericDataValue( 'user_value6' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue6( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value6', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue7() {
		return $this->getGenericDataValue( 'user_value7' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue7( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value7', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue8() {
		return $this->getGenericDataValue( 'user_value8' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue8( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value8', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue9() {
		return $this->getGenericDataValue( 'user_value9' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue9( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value9', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue10() {
		return $this->getGenericDataValue( 'user_value10' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue10( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value10', $value );
	}

	/**
	 * Primarily used to display marital status/allowances/claim amounts on pay stubs.
	 * @param bool $transaction_date
	 * @return bool|string
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	function getDescription( $transaction_date = false ) {
		$retval = false;

		//Calculates the deduction.
		$cd_obj = $this->getCompanyDeductionObject();

		$user_value1 = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
		$user_value2 = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
		$user_value3 = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
		$user_value4 = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
		$user_value5 = ( ( $this->getUserValue5() == '' ) ? $cd_obj->getUserValue5() : $this->getUserValue5() );
		$user_value6 = ( ( $this->getUserValue6() == '' ) ? $cd_obj->getUserValue6() : $this->getUserValue6() );
		$user_value7 = ( ( $this->getUserValue7() == '' ) ? $cd_obj->getUserValue7() : $this->getUserValue7() );
		$user_value9 = ( ( $this->getUserValue9() == '' ) ? $cd_obj->getUserValue9() : $this->getUserValue9() );

		if ( $transaction_date == '' ) {
			$transaction_date = time();
		}

		if ( $cd_obj->getCountry() == 'CA' ) {
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );
			$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), $cd_obj->getProvince() );
			$pd_obj->setDate( $transaction_date );
		}

		//Debug::Text('UserDeduction ID: '. $this->getID() .' Calculation ID: '. $cd_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $cd_obj->getCalculation() ) {
			case 100: //Federal
				$country_label = strtoupper( $cd_obj->getCountry() );

				if ( $cd_obj->getCountry() == 'CA' ) {
					//Filter Claim Amount through PayrollDeduction class so it can be automatically adjusted if necessary.
					$pd_obj->setFederalTotalClaimAmount( $user_value1 );
					$retval = $country_label . ' - ' . TTI18n::getText( 'Claim Amount' ) . ': $' . Misc::MoneyFormat( $pd_obj->getFederalTotalClaimAmount() );
				} else if ( $cd_obj->getCountry() == 'US' ) {
					$retval = $country_label . ' - ' . TTI18n::getText( 'Filing Status' ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'federal_filing_status' ) );
					if ( $user_value9 == 2020 ) {
						$retval .= ' ' . TTI18n::getText( 'Claim Dependents' ) . ': ' . $user_value4;
					} else {
						$retval .= ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
					}

					if ( (int)$this->getUserValue10() >= 1 ) {
						$retval .= ' ' . TTI18n::getText( 'Exempt' );
					}
				}
				break;
			case 200:
				$province_label = strtoupper( $cd_obj->getProvince() );

				if ( $cd_obj->getCountry() == 'CA' ) {
					//Filter Claim Amount through PayrollDeduction class so it can be automatically adjusted if necessary.
					$pd_obj->setProvincialTotalClaimAmount( $user_value1 );
					$retval = $province_label . ' - ' . TTI18n::getText( 'Claim Amount' ) . ': $' . Misc::MoneyFormat( $pd_obj->getProvincialTotalClaimAmount() );
				} else if ( $cd_obj->getCountry() == 'US' ) {
					switch ( strtolower( $cd_obj->getProvince() ) ) {
						case 'al':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_al_filing_status' ) ) . ' ' . TTI18n::getText( 'Dependents' ) . ': ' . (int)$user_value2;
							break;
						case 'az': //Percent
							$retval = $province_label . ' - ' . TTI18n::getText( 'Percent', $province_label ) . ': ' . (float)$user_value1 . '%';
							break;
						case 'ct':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_ct_filing_status' ) );
							break;
						case 'dc':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_dc_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'de':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_de_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'ga':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_ga_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2 . ' ' . TTI18n::getText( 'Dependent Allowances' ) . ': ' . (int)$user_value3;
							break;
						case 'il':
							$retval = $province_label . ' - ' . TTI18n::getText( 'IL-W-4 Line 1' ) . ': ' . (int)$user_value1 . ' ' . TTI18n::getText( 'IL-W-4 Line 2' ) . ': ' . (int)$user_value2;
							break;
						case 'in':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Allowances', $province_label ) . ': ' . (int)$user_value1 . ' ' . TTI18n::getText( 'Dependents' ) . ': ' . (int)$user_value2;
							break;
						case 'la':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value3, $cd_obj->getOptions( 'state_la_filing_status' ) ) . ' ' . TTI18n::getText( 'Dependents' ) . ': ' . (int)$user_value2 . ' ' . TTI18n::getText( 'Exemptions' ) . ': ' . (int)$user_value1;
							break;
						case 'ma':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_ma_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'md': //County Rate
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_filing_status' ) ) . ' ' . TTI18n::getText( 'County Rate' ) . ': ' . (float)$user_value2 . '%';
							break;
						case 'me':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_me_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'nc':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_nc_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'nj':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_nj_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						case 'ar':
						case 'ia':
						case 'ky':
						case 'mi':
						case 'mt':
						case 'oh':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Allowances', $province_label ) . ': ' . (int)$user_value2;
							break;
						case 'or':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							//As of 01-Jan-2017, Oregon law ( ORS 652.610 ) requires 'the name and business registry number or business identification number of the employer'; displayed on pay stubs.
							if ( is_object( $cd_obj->getPayrollRemittanceAgencyObject() ) && $cd_obj->getPayrollRemittanceAgencyObject()->getPrimaryIdentification() != '' ) {
								$retval .= ' [#' . $cd_obj->getPayrollRemittanceAgencyObject()->getPrimaryIdentification() . ']';
							}
							break;
						case 'va':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value1 . ' ' . TTI18n::getText( 'Age 65/Blind' ) . ': ' . (int)$user_value2;
							break;
						case 'wv':
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_wv_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
						default:
							$retval = $province_label . ' - ' . TTI18n::getText( 'Filing Status', $province_label ) . ': ' . Option::getByKey( $user_value1, $cd_obj->getOptions( 'state_filing_status' ) ) . ' ' . TTI18n::getText( 'Allowances' ) . ': ' . (int)$user_value2;
							break;
					}

					if ( (int)$this->getUserValue10() >= 1 ) {
						$retval .= ' ' . TTI18n::getText( 'Exempt' );
					}
				}
				break;
		}

		return $retval;
	}

	/**
	 * Since some Tax/Deduction calculation types (ie: Canada Federal/Provincial) take into account specific amounts calculated by other Tax/Deduction records (ie: Canada CPP/EI),
	 * this provides a way to force additional required PSA's that affect just the dependancy tree.
	 * @return array
	 * @throws DBError
	 */
	function getAdditionalRequiredPayStubAccounts() {
		$cd_obj = $this->getCompanyDeductionObject();

		$retarr = [];

		//For Canada Federal/Provincial Income Tax calculations, they require that Employee CPP/EI is calculated first, as they are taken into account when calculating the taxes,
		// but we can't include the CPP/EI PSA's in the Tax/Deduction record as that would include the dollar values.
		// So we need way to inject additional required PSA's into just the dependancy tree, to ensure consistent calculation order.
		if ( in_array( $cd_obj->getCalculation(), [ 100, 200 ] ) && $this->getCompanyDeductionObject()->getCountry() == 'CA' ) {
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $cd_obj->getCompany(), $cd_obj->getLegalEntity(), [ 90, 91 ], 10 ); //90=CPP,91=EI, 10=Enabled
			if ( $cdlf->getRecordCount() > 0 ) {
				foreach ( $cdlf as $tmp_cd_obj ) {
					$retarr[] = $tmp_cd_obj->getPayStubEntryAccount();
					Debug::Text( '    Found Additional Required Include Pay Stub Account: ' . $tmp_cd_obj->getPayStubEntryAccount() . ' Company Deduction: ' . $cd_obj->getName() . ' (' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return $retarr;
	}

	/**
	 * @param $user_obj
	 * @param PayStubFactory $pay_stub_obj
	 * @param PayPeriodFactory $pay_period_obj
	 * @param int $formula_type_id
	 * @param int $payroll_run_id
	 * @return int|string
	 * @throws DBError
	 */
	function getDeductionAmount( $user_obj, $pay_stub_obj, $pay_period_obj, $formula_type_id = 10, $payroll_run_id = 1 ) {
		if ( !is_object( $user_obj ) ) {
			Debug::Text( 'Missing User Object: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( !is_object( $pay_stub_obj ) ) {
			Debug::Text( 'Missing Pay Stub Object: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( !is_object( $pay_period_obj ) ) {
			Debug::Text( 'Missing Pay Period Object: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$this->user_obj = $user_obj;
		/** @var UserFactory $user_obj */
		$user_id = $user_obj->getId();

		//Calculates the deduction.
		$cd_obj = $this->getCompanyDeductionObject();
		/** @var CompanyDeductionFactory $cd_obj */

		$annual_pay_periods = $pay_period_obj->getPayPeriodScheduleObject()->getAnnualPayPeriods();
		if ( $annual_pay_periods <= 0 ) {
			$annual_pay_periods = 1;
		}

		//Need to use pay stub dates rather than pay period dates for this, because if you are in the first pay period of 2016 (Transaction: 01-Jan) and
		//you need to run a out-of-cycle bonus to be paid by 24-Dec, it will think its the 1st pay stub of the year when its really the last. This causes taxes to be incorrect.
		$current_pay_period = $pay_period_obj->getPayPeriodScheduleObject()->getCurrentPayPeriodNumber( $pay_stub_obj->getTransactionDate(), $pay_stub_obj->getEndDate() );
		if ( $current_pay_period <= 0 ) {
			$current_pay_period = 1;
		}

		$hire_adjusted_annual_pay_periods = $pay_period_obj->getPayPeriodScheduleObject()->getHireAdjustedAnnualPayPeriods( $pay_stub_obj->getTransactionDate(), $this->getUserObject()->getHireDate() );
		if ( $hire_adjusted_annual_pay_periods <= 0 ) {
			$hire_adjusted_annual_pay_periods = 1;
		}
		$hire_adjusted_current_pay_period = $pay_period_obj->getPayPeriodScheduleObject()->getHireAdjustedCurrentPayPeriodNumber( $pay_stub_obj->getTransactionDate(), $pay_stub_obj->getEndDate(), $this->getUserObject()->getHireDate() );
		if ( $hire_adjusted_current_pay_period <= 0 ) {
			$hire_adjusted_current_pay_period = 1;
		}

		if ( !is_object( $cd_obj ) ) {
			return false;
		}

		if ( in_array( $cd_obj->getCalculation(), [ 100, 200, 300 ] ) && (int)$cd_obj->getCompanyValue1() > 0 ) {
			Debug::Text( 'Overriding Formula Type to: ' . (int)$cd_obj->getCompanyValue1() . ' From: ' . $formula_type_id, __FILE__, __LINE__, __METHOD__, 10 );
			$formula_type_id = (int)$cd_obj->getCompanyValue1();
		}

		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );

		$retval = 0;

		Debug::Text( 'Company Deduction: ID: ' . $cd_obj->getID() . ' Name: ' . $cd_obj->getName() . ' Calculation ID: ' . $cd_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10 );
		switch ( $cd_obj->getCalculation() ) {
			case 10: //Basic Percent
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				$retval = bcmul( $amount, bcdiv( $percent, 100 ) );

				break;
			case 15: //Advanced Percent
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$wage_base = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$wage_base = $this->Validator->stripNonFloat( $wage_base );

				$exempt_amount = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$exempt_amount = $this->Validator->stripNonFloat( $exempt_amount );

				//Annual Wage Base is the maximum earnings that an employee can earn before they are no longer eligible for this deduction
				//Annual Deduction Amount

				Debug::Text( 'Percent: ' . $percent . ' Wage Base: ' . $wage_base . ' Exempt Amount: ' . $exempt_amount, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $percent != 0 ) {

					if ( $exempt_amount > 0 ) {
						$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), bcdiv( $exempt_amount, $annual_pay_periods ) );
						Debug::Text( 'Amount After Exemption: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );
						Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( $wage_base > 0 ) {
						//*NOTE: If the first pay stub in TimeTrex is near the end of the year, and the employee has already exceeded the wage base amount
						//the payroll admin needs to make sure they add a YTD Adjustment for each Include PS Accounts that this calculation is based on,
						//NOT the total amount they have paid for the resulting calculation, as that has no effect whatsoever.
						// The above is no longer the case since we switched to use opening balance pay stubs instead.

						//getCalculationYTDAmount is the previous pay stub YTD amount, but it includes any YTD Adjustments in the current pay stub too.
						$ytd_amount = $cd_obj->getCalculationYTDAmount( $pay_stub_obj );
						Debug::Text( 'Wage Base is set: ' . $wage_base . ' Amount: ' . $amount . ' Current YTD: ' . $ytd_amount, __FILE__, __LINE__, __METHOD__, 10 );

						//Possible calcations:
						//
						//Wage Base: 3000
						//Amount: 500 YTD: 0		= 500
						//Amount: 500 YTD: 2900		= 100
						//Amount: 500 YTD: 3100		= 0
						//Amount: 3500 YTD: 0		= 3000
						//AMount: 3500 YTD: 2900	= 100
						//Amount: 3500 YTD: 3100	= 0

						//Check to see if YTD is less than wage base.
						$remaining_wage_base = bcsub( $wage_base, $ytd_amount );
						Debug::Text( 'Remaining Wage Base to be calculated: ' . $remaining_wage_base, __FILE__, __LINE__, __METHOD__, 10 );
						if ( $remaining_wage_base > 0 ) {
							if ( $amount > $remaining_wage_base ) {
								$amount = $remaining_wage_base;
							}
						} else {
							$amount = 0; //Exceeded wage base, nothing to calculate.
						}
						unset( $remaining_wage_base );
					} else {
						Debug::Text( 'Wage Base is NOT set: ' . $wage_base, __FILE__, __LINE__, __METHOD__, 10 );
					}

					$retval = bcmul( $amount, bcdiv( $percent, 100 ) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 && $retval < 0 ) {
					$retval = 0;
				}

				unset( $amount, $ytd_amount, $percent, $wage_base );

				break;
			case 16: //Advanced Percent (w/Target)
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$target_amount = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$target_amount = $this->Validator->stripNonFloat( $target_amount );

				$target_ytd_amount = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$target_ytd_amount = $this->Validator->stripNonFloat( $target_ytd_amount );

				Debug::Text( 'Percent: ' . $percent . ' Target Amount: ' . $target_amount . ' YTD Amount: ' . $target_ytd_amount, __FILE__, __LINE__, __METHOD__, 10 );
				$retval = 0;
				if ( $percent != 0 ) {
					$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

					//Make sure YTD amount includes any other amounts on the current pay stub, incase they have two calculations or PS amendments that affect the same account.
					$ytd_amount = bcadd( $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ $cd_obj->getPayStubEntryAccount() ], 'current', 'amount' ), $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ $cd_obj->getPayStubEntryAccount() ], 'previous+ytd_adjustment', 'ytd_amount' ) );
					Debug::Text( 'Amount: ' . $amount . ' YTD Amount: ' . $ytd_amount, __FILE__, __LINE__, __METHOD__, 10 );

					$percent_amount = bcmul( $amount, bcdiv( $percent, 100 ) );

					if ( $percent_amount != 0 ) {
						$filtered_amount = Misc::getAmountToLimit( $percent_amount, $target_amount );
						Debug::Text( '  Filtered Amount: ' . $filtered_amount . ' YTD Amount: ' . $ytd_amount, __FILE__, __LINE__, __METHOD__, 10 );

						$filtered_ytd_amount = Misc::getAmountDifferenceToLimit( $ytd_amount, $target_ytd_amount );
						Debug::Text( '  Filtered YTD Amount: ' . $filtered_ytd_amount . ' YTD Amount: ' . $ytd_amount, __FILE__, __LINE__, __METHOD__, 10 );

						//Choose whichever filtered amount is lower.
						if ( $filtered_ytd_amount < $filtered_amount ) {
							$retval = $filtered_ytd_amount;
						} else {
							$retval = $filtered_amount;
						}
					}
				}

				if ( $percent >= 0 && $retval < 0 ) {
					$retval = 0;
				}

				unset( $amount, $ytd_amount, $percent, $percent_amount, $filtered_amount, $filtered_ytd_amount, $target_amount, $target_ytd_amount );
				break;
			case 17: //Advanced Percent (Range Bracket)
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$min_wage = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				$max_wage = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				$annual_deduction_amount = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				$annual_fixed_amount = ( ( $this->getUserValue5() == '' ) ? $cd_obj->getUserValue5() : $this->getUserValue5() );
				$annual_fixed_amount = $this->Validator->stripNonFloat( $annual_fixed_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods );
				$max_wage = bcdiv( $max_wage, $annual_pay_periods );
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text( 'Percent: ' . $percent . ' Min Wage: ' . $min_wage . ' Max Wage: ' . $max_wage . ' Annual Deduction: ' . $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $amount >= $min_wage && $amount <= $max_wage ) {
						$retval = bcadd( bcmul( $amount, bcdiv( $percent, 100 ) ), $annual_fixed_amount );
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 && $retval < 0 ) {
					$retval = 0;
				}

				unset( $amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount );

				break;
			case 18: //Advanced Percent (Tax Bracket)
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$wage_base = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$wage_base = $this->Validator->stripNonFloat( $wage_base );

				$exempt_amount = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$exempt_amount = $this->Validator->stripNonFloat( $exempt_amount );

				$annual_deduction_amount = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				Debug::Text( 'Percent: ' . $percent . ' Wage Base: ' . $wage_base . ' Exempt Amount: ' . $exempt_amount, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $percent != 0 ) {
					if ( $exempt_amount > 0 ) {
						$pp_exempt_amount = bcdiv( $exempt_amount, $annual_pay_periods );
					} else {
						$pp_exempt_amount = 0;
					}
					//Debug::Text('PP Exempt Amount: '. $pp_exempt_amount, __FILE__, __LINE__, __METHOD__, 10);

					if ( $wage_base > 0 ) {
						$pp_wage_base_amount = bcdiv( $wage_base, $annual_pay_periods );
					} else {
						$pp_wage_base_amount = 0;
					}

					if ( $annual_deduction_amount > 0 ) {
						$pp_annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
					} else {
						$pp_annual_deduction_amount = 0;
					}

					//Debug::Text('PP Wage Base Base Amount: '. $pp_wage_base_amount, __FILE__, __LINE__, __METHOD__, 10);
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $pp_annual_deduction_amount );

					//Debug::Text('Calculation Pay Stub Amount: '. $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), __FILE__, __LINE__, __METHOD__, 10);
					if ( $pp_wage_base_amount > 0
							&& $amount > $pp_wage_base_amount ) {
						//Debug::Text('Exceeds Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__, 10);
						$amount = bcsub( $pp_wage_base_amount, $pp_exempt_amount );
					} else {
						//Debug::Text('Under Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__, 10);
						$amount = bcsub( $amount, $pp_exempt_amount );
					}
					Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

					$retval = bcmul( $amount, bcdiv( $percent, 100 ) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 && $retval < 0 ) {
					$retval = 0;
				}

				unset( $amount, $percent, $wage_base, $pp_wage_base_amount, $pp_exempt_amount, $annual_deduction_amount, $pp_annual_deduction_amount );

				break;
			case 19: //Advanced Percent (Tax Bracket Alternate)
				/*
					This is designed to be used for single line item tax calculations, in that the formula looks like this,
					where only ONE bracket would be applied to the employee, NOT all:
					Wage between 0 - 10, 000 calculate 10%
					Wage between 10, 001 - 20, 000 calculate 15% + $1000 (10% of 10, 000 as per above)
					Wage between 20, 001 - 30, 000 calculate 20% + $2500 (10% of 10, 000 as first bracket, and 15% of 10, 000 as per 2nd bracket)
				*/
				$percent = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$percent = $this->Validator->stripNonFloat( $percent );

				$min_wage = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				$max_wage = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				$annual_deduction_amount = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				$annual_fixed_amount = ( ( $this->getUserValue5() == '' ) ? $cd_obj->getUserValue5() : $this->getUserValue5() );
				$annual_fixed_amount = $this->Validator->stripNonFloat( $annual_fixed_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods );
				$max_wage = bcdiv( $max_wage, $annual_pay_periods );
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text( 'Percent: ' . $percent . ' Min Wage: ' . $min_wage . ' Max Wage: ' . $max_wage . ' Annual Deduction: ' . $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $amount >= $min_wage && $amount <= $max_wage ) {
						$retval = bcadd( bcmul( bcsub( $amount, $min_wage ), bcdiv( $percent, 100 ) ), $annual_fixed_amount );
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 && $retval < 0 ) {
					$retval = 0;
				}

				unset( $amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount );

				break;
			case 20: //Fixed amount
				$amount = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$amount = $this->Validator->stripNonFloat( $amount );

				$retval = $amount;
				unset( $amount );

				break;
			case 30: //Fixed Amount (Range Bracket)
				$fixed_amount = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$fixed_amount = $this->Validator->stripNonFloat( $fixed_amount );

				$min_wage = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				$max_wage = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				$annual_deduction_amount = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods );
				$max_wage = bcdiv( $max_wage, $annual_pay_periods );
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );

				Debug::Text( 'Amount: ' . $fixed_amount . ' Min Wage: ' . $min_wage . ' Max Wage: ' . $max_wage . ' Annual Deduction: ' . $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $fixed_amount != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $amount >= $min_wage && $amount <= $max_wage ) {
						$retval = $fixed_amount;
					}
				} else {
					$retval = 0;
				}

				unset( $fixed_amount, $amount, $percent, $min_wage, $max_wage, $annual_deduction_amount );

				break;
			case 52: //Fixed Amount (w/Limit)
				$fixed_amount = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$fixed_amount = $this->Validator->stripNonFloat( $fixed_amount );

				$target_amount = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$target_amount = $this->Validator->stripNonFloat( $target_amount );

				Debug::Text( 'Fixed Amount: ' . $fixed_amount . ' Target Amount: ' . $target_amount, __FILE__, __LINE__, __METHOD__, 10 );

				$retval = 0;
				if ( $fixed_amount != 0 ) {
					$ytd_amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

					$ytd_amount_remaining = Misc::getAmountDifferenceToLimit( $ytd_amount, $target_amount );

					$retval = Misc::getAmountToLimit( $ytd_amount_remaining, $fixed_amount );
					Debug::Text( '  Retval: ' . $retval . ' YTD Amount: ' . $ytd_amount . ' YTD Remaining Amount: ' . $ytd_amount_remaining, __FILE__, __LINE__, __METHOD__, 10 );
				}

				unset( $fixed_amount, $target_amount, $ytd_amount, $ytd_amount_remaining );

				break;
			case 69: // Custom Formulas
				$user_value1 = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$user_value2 = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$user_value3 = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$user_value4 = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$user_value5 = ( ( $this->getUserValue5() == '' ) ? $cd_obj->getUserValue5() : $this->getUserValue5() );
				$user_value6 = ( ( $this->getUserValue6() == '' ) ? $cd_obj->getUserValue6() : $this->getUserValue6() );
				$user_value7 = ( ( $this->getUserValue7() == '' ) ? $cd_obj->getUserValue7() : $this->getUserValue7() );
				$user_value8 = ( ( $this->getUserValue8() == '' ) ? $cd_obj->getUserValue8() : $this->getUserValue8() );
				$user_value9 = ( ( $this->getUserValue9() == '' ) ? $cd_obj->getUserValue9() : $this->getUserValue9() );
				$user_value10 = ( ( $this->getUserValue10() == '' ) ? $cd_obj->getUserValue10() : $this->getUserValue10() );

				// evaluate math expressions as the company_value1 and user_value1-10 defined by user.
				$company_value1 = $cd_obj->getCompanyValue1(); // Custom Formula

				$variables = [];
				$formula_variables = array_keys( (array)TTMath::parseColumnsFromFormula( $company_value1 ) );
				Debug::Arr( $formula_variables, 'Formula Variables: ', __FILE__, __LINE__, __METHOD__, 10 );

				if ( is_array( $formula_variables ) ) {
					$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */

					if ( in_array( 'currency_conversion_rate', $formula_variables ) && is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getCurrencyObject() ) ) {
						$currency_iso_code = $this->getUserObject()->getCurrencyObject()->getISOCode();
						$currency_conversion_rate = $this->getUserObject()->getCurrencyObject()->getConversionRate();
						Debug::Text( 'Currency Variables: Rate: ' . $currency_conversion_rate . ' ISO: ' . $currency_iso_code, __FILE__, __LINE__, __METHOD__, 10 );
					}

					//First pass to gather any necessary data based on variables
					if ( in_array( 'employee_hourly_rate', $formula_variables ) || in_array( 'employee_annual_wage', $formula_variables ) || in_array( 'employee_wage_average_weekly_hours', $formula_variables ) ) {
						$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
						$uwlf->getWageByUserIdAndPayPeriodEndDate( $this->getUser(), $pay_period_obj->getEndDate() );
						if ( $uwlf->getRecordCount() > 0 ) {
							$uwf = $uwlf->getCurrent();
							$employee_hourly_rate = $uwf->getHourlyRate();
							$employee_annual_wage = $uwf->getAnnualWage();
							$employee_wage_average_weekly_hours = TTDate::getHours( $uwf->getWeeklyTime() );
						} else {
							$employee_hourly_rate = 0;
							$employee_annual_wage = 0;
							$employee_wage_average_weekly_hours = 0;
						}
						Debug::Text( 'Employee Hourly Rate: ' . $employee_hourly_rate, __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( in_array( 'pay_period_worked_days', $formula_variables ) || in_array( 'pay_period_paid_days', $formula_variables ) ) {
						$pay_period_days_worked = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
						$pay_period_days_absence = (array)$udtlf->getDaysPaidAbsenceByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
					}
					if ( in_array( 'pay_period_worked_time', $formula_variables ) || in_array( 'pay_period_paid_time', $formula_variables ) ) {
						$pay_period_worked_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
						$pay_period_absence_time = $udtlf->getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
					}

					if ( $cd_obj->getCompanyValue2() != '' && $cd_obj->getCompanyValue2() > 0 && $cd_obj->getCompanyValue3() != '' && $cd_obj->getCompanyValue3() > 0 ) {
						Debug::Text( 'Formula Lookback enable: ' . $cd_obj->getCompanyValue2(), __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $formula_variables as $formula_variable ) {
							if ( strpos( $formula_variable, 'lookback_' ) !== false ) {
								Debug::Text( 'Lookback variables exist...', __FILE__, __LINE__, __METHOD__, 10 );
								$lookback_dates = $cd_obj->getLookbackStartAndEndDates( $pay_period_obj );
								$lookback_pay_stub_dates = $cd_obj->getLookbackPayStubs( $this->getUser(), $pay_period_obj );
								//Debug::Arr( $lookback_dates, 'Lookback Dates...', __FILE__, __LINE__, __METHOD__, 10 );
								//Debug::Arr( $lookback_pay_stub_dates, 'Lookback PayStub Dates...', __FILE__, __LINE__, __METHOD__, 10 );
								break;
							}
						}
					}

					if ( isset( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) && isset( $lookback_pay_stub_dates['last_pay_stub_end_date'] )
							&& in_array( 'lookback_pay_stub_worked_days', $formula_variables ) || in_array( 'lookback_pay_stub_paid_days', $formula_variables ) ) {
						Debug::Text( 'Lookback Pay Stub Dates... Start: ' . TTDate::getDate( 'DATE', $lookback_pay_stub_dates['first_pay_stub_start_date'] ) . ' End: ' . TTDate::getDate( 'DATE', $lookback_pay_stub_dates['last_pay_stub_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						$lookback_pay_stub_days_worked = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
						$lookback_pay_stub_days_absence = (array)$udtlf->getDaysPaidAbsenceByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
					}
					if ( isset( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) && isset( $lookback_pay_stub_dates['last_pay_stub_end_date'] )
							&& in_array( 'lookback_pay_stub_worked_time', $formula_variables ) || in_array( 'lookback_pay_stub_paid_time', $formula_variables ) ) {
						Debug::Text( 'Lookback Pay Stub Dates... Start: ' . TTDate::getDate( 'DATE', $lookback_pay_stub_dates['first_pay_stub_start_date'] ) . ' End: ' . TTDate::getDate( 'DATE', $lookback_pay_stub_dates['last_pay_stub_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						$lookback_pay_stub_worked_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
						$lookback_pay_stub_absence_time = $udtlf->getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
					}

					//Second pass to define variables.
					foreach ( $formula_variables as $formula_variable ) {
						//Handle individual PS account amounts/units.
						switch ( substr( $formula_variable, 0, 3 ) ) {
							case 'PU:': //Units
								$variables[$formula_variable] = $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ str_replace( 'PU:', '', $formula_variable ) ], 'current', 'units' );
								break;
							case 'PR:': //Rate
								$variables[$formula_variable] = $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ str_replace( 'PR:', '', $formula_variable ) ], 'current', 'rate' );
								break;
							case 'PA:': //Amount
								$variables[$formula_variable] = $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ str_replace( 'PA:', '', $formula_variable ) ], 'current', 'amount' );
								break;
							case 'PY:': //YTD Amount/Balance
								$variables[$formula_variable] = $cd_obj->getPayStubEntryAmountSum( $pay_stub_obj, [ str_replace( 'PY:', '', $formula_variable ) ], 'previous', 'ytd_amount' ); //YTD Amount is only populated on previous pay stub when this runs.
								break;
						}

						if ( !isset( $variables[$formula_variable] ) ) {
							switch ( $formula_variable ) {
								case 'custom_value1':
									$variables[$formula_variable] = $user_value1;
									break;
								case 'custom_value2':
									$variables[$formula_variable] = $user_value2;
									break;
								case 'custom_value3':
									$variables[$formula_variable] = $user_value3;
									break;
								case 'custom_value4':
									$variables[$formula_variable] = $user_value4;
									break;
								case 'custom_value5':
									$variables[$formula_variable] = $user_value5;
									break;
								case 'custom_value6':
									$variables[$formula_variable] = $user_value6;
									break;
								case 'custom_value7':
									$variables[$formula_variable] = $user_value7;
									break;
								case 'custom_value8':
									$variables[$formula_variable] = $user_value8;
									break;
								case 'custom_value9':
									$variables[$formula_variable] = $user_value9;
									break;
								case 'custom_value10':
									$variables[$formula_variable] = $user_value10;
									break;

								case 'employee_hourly_rate':
									$variables[$formula_variable] = $employee_hourly_rate;
									break;
								case 'employee_annual_wage':
									$variables[$formula_variable] = $employee_annual_wage;
									break;
								case 'employee_wage_average_weekly_hours':
									$variables[$formula_variable] = $employee_wage_average_weekly_hours;
									break;

								case 'annual_pay_periods':
									$variables[$formula_variable] = $annual_pay_periods;
									break;

								case 'pay_period_start_date':
									$variables[$formula_variable] = $pay_period_obj->getStartDate();
									break;
								case 'pay_period_end_date':
									$variables[$formula_variable] = $pay_period_obj->getEndDate();
									break;
								case 'pay_period_transaction_date':
									$variables[$formula_variable] = $pay_period_obj->getTransactionDate();
									break;
								case 'pay_period_total_days':
									$variables[$formula_variable] = round( TTDate::getDays( ( TTDate::getEndDayEpoch( $pay_period_obj->getEndDate() ) - TTDate::getBeginDayEpoch( $pay_period_obj->getStartDate() ) ) ) );
									break;
								case 'pay_period_worked_days':
									$variables[$formula_variable] = count( array_unique( $pay_period_days_worked ) );
									break;
								case 'pay_period_paid_days':
									$variables[$formula_variable] = count( array_unique( array_merge( $pay_period_days_worked, $pay_period_days_absence ) ) );
									break;
								case 'pay_period_worked_time':
									$variables[$formula_variable] = $pay_period_worked_time;
									break;
								case 'pay_period_paid_time':
									$variables[$formula_variable] = ( $pay_period_worked_time + $pay_period_absence_time );
									break;

								case 'employee_hire_date':
									$variables[$formula_variable] = $this->getUserObject()->getHireDate();
									break;
								case 'employee_termination_date':
									$variables[$formula_variable] = $this->getUserObject()->getTerminationDate();
									break;
								case 'employee_birth_date':
									$variables[$formula_variable] = $this->getUserObject()->getBirthDate();
									break;

								case 'currency_iso_code':
									$variables[$formula_variable] = $currency_iso_code;
									break;
								case 'currency_conversion_rate':
									$variables[$formula_variable] = $currency_conversion_rate;
									break;

								case 'include_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 10 );
									break;
								case 'include_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 30 );
									break;
								case 'include_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 20 );
									break;
								case 'include_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 40 );
									break;
								case 'exclude_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, null, 10 );
									break;
								case 'exclude_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, null, 30 );
									break;
								case 'exclude_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, null, 20 );
									break;
								case 'exclude_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, null, 40 );
									break;
								case 'pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 10, 10 );
									break;
								case 'pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 30, 30 );
									break;
								case 'pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 20, 20 );
									break;
								case 'pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 40, 40 );
									break;

								//Lookback variables.
								case 'lookback_total_pay_stubs':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['total_pay_stubs'] ) ) ? $lookback_pay_stub_dates['total_pay_stubs'] : 0;
									break;
								case 'lookback_start_date':
									$variables[$formula_variable] = ( isset( $lookback_dates['start_date'] ) ) ? $lookback_dates['start_date'] : 0;
									break;
								case 'lookback_end_date':
									$variables[$formula_variable] = ( isset( $lookback_dates['end_date'] ) ) ? $lookback_dates['end_date'] : 0;
									break;
								case 'lookback_total_days':
									if ( isset( $lookback_dates['start_date'] ) && isset( $lookback_dates['end_date'] ) ) {
										$variables[$formula_variable] = round( TTDate::getDays( ( TTDate::getEndDayEpoch( $lookback_dates['end_date'] ) - TTDate::getBeginDayEpoch( $lookback_dates['start_date'] ) ) ) );
									} else {
										$variables[$formula_variable] = 0;
									}
									break;
								case 'lookback_first_pay_stub_start_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) ) ? $lookback_pay_stub_dates['first_pay_stub_start_date'] : 0;
									break;
								case 'lookback_first_pay_stub_end_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['first_pay_stub_end_date'] ) ) ? $lookback_pay_stub_dates['first_pay_stub_end_date'] : 0;
									break;
								case 'lookback_first_pay_stub_transaction_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['first_pay_stub_transaction_date'] ) ) ? $lookback_pay_stub_dates['first_pay_stub_transaction_date'] : 0;
									break;
								case 'lookback_last_pay_stub_start_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['last_pay_stub_start_date'] ) ) ? $lookback_pay_stub_dates['last_pay_stub_start_date'] : 0;
									break;
								case 'lookback_last_pay_stub_end_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['last_pay_stub_end_date'] ) ) ? $lookback_pay_stub_dates['last_pay_stub_end_date'] : 0;
									break;
								case 'lookback_last_pay_stub_transaction_date':
									$variables[$formula_variable] = ( isset( $lookback_pay_stub_dates['last_pay_stub_transaction_date'] ) ) ? $lookback_pay_stub_dates['last_pay_stub_end_date'] : 0;
									break;

								case 'lookback_pay_stub_total_days':
									if ( isset( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) && isset( $lookback_pay_stub_dates['last_pay_stub_end_date'] ) ) {
										$variables[$formula_variable] = round( TTDate::getDays( ( ( TTDate::getEndDayEpoch( $lookback_pay_stub_dates['last_pay_stub_end_date'] ) - TTDate::getBeginDayEpoch( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) ) ) ) );
									} else {
										$variables[$formula_variable] = 0;
									}
									break;
								case 'lookback_pay_stub_worked_days':
									$variables[$formula_variable] = count( array_unique( $lookback_pay_stub_days_worked ) );
									break;
								case 'lookback_pay_stub_paid_days':
									$variables[$formula_variable] = count( array_unique( array_merge( $lookback_pay_stub_days_worked, $lookback_pay_stub_days_absence ) ) );
									break;
								case 'lookback_pay_stub_worked_time':
									$variables[$formula_variable] = $lookback_pay_stub_worked_time;
									break;
								case 'lookback_pay_stub_paid_time':
									$variables[$formula_variable] = ( $lookback_pay_stub_worked_time + $lookback_pay_stub_absence_time );
									break;

								case 'lookback_include_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 10 );
									break;
								case 'lookback_include_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 30 );
									break;
								case 'lookback_include_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 20 );
									break;
								case 'lookback_include_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 40 );
									break;
								case 'lookback_exclude_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( null, 10 );
									break;
								case 'lookback_exclude_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( null, 30 );
									break;
								case 'lookback_exclude_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( null, 20 );
									break;
								case 'lookback_exclude_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( null, 40 );
									break;
								case 'lookback_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 10, 10 );
									break;
								case 'lookback_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 30, 30 );
									break;
								case 'lookback_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 20, 20 );
									break;
								case 'lookback_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 40, 40 );
									break;
							}
						}
					}

					unset( $uwlf, $uwf, $employee_hourly_rate, $employee_annual_wage, $employee_wage_average_weekly_hours, $annual_pay_periods, $lookback_dates, $lookback_pay_stub_dates, $currency_iso_code, $currency_conversion_rate, $pay_period_worked_time, $pay_period_absence_time, $lookback_pay_stub_worked_time, $lookback_pay_stub_absence_time, $pay_period_days_worked, $pay_period_days_absence, $lookback_pay_stub_days_worked, $lookback_pay_stub_days_absence );
				}

				//Debug::Arr( $variables, 'Formula Variable values: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( [ str_replace( "\r", '; ', $company_value1 ), str_replace( "\r", '; ', TTMath::translateVariables( $company_value1, $variables ) ) ], 'Original/Translated Formula: ', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = TTMath::evaluate( TTMath::translateVariables( $company_value1, $variables ) );

				Debug::Text( 'Formula Retval: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
				break;
			case 82: //US - Medicare - Employee
			case 83: //US - Medicare - Employer
			case 84: //US - Social Security - Employee
			case 85: //US - Social Security - Employer
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( 'US', null );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text( 'User Currency ID: ' . $currency_id, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				switch ( $cd_obj->getCalculation() ) {
					case 82: //US - Medicare - Employee
						$pd_obj->setYearToDateGrossIncome( $cd_obj->getCalculationYTDAmount( $pay_stub_obj ) ); //Make sure YTD amount is specified for all calculation types.
						$retval = $pd_obj->getEmployeeMedicare();
						break;
					case 83: //US - Medicare - Employer
						$retval = $pd_obj->getEmployerMedicare();
						break;
					case 84: //US - Social Security - Employee
						$pd_obj->setYearToDateSocialSecurityContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
						$retval = $pd_obj->getEmployeeSocialSecurity();
						break;
					case 85: //US - Social Security - Employer
						$pd_obj->setYearToDateSocialSecurityContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
						$retval = $pd_obj->getEmployerSocialSecurity();
						break;
				}

				break;
			case 90: //Canada - CPP
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( 'CA', null );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				$pd_obj->setEnableCPPAndEIDeduction( true );

				//Used to check $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() here, but that function has been deprecated to better support multiple legal entities.
				$pd_obj->setYearToDateCPPContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeCPP();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 91: //Canada - EI
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( 'CA', null );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				$pd_obj->setEnableCPPAndEIDeduction( true );

				//Used to check $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() here, but that function has been deprecated to better support multiple legal entities.
				$pd_obj->setYearToDateEIContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeEI();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 100: //Federal Income Tax
				$user_value1 = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$user_value2 = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$user_value3 = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				$user_value4 = ( ( $this->getUserValue4() == '' ) ? $cd_obj->getUserValue4() : $this->getUserValue4() );
				$user_value5 = ( ( $this->getUserValue5() == '' ) ? $cd_obj->getUserValue5() : $this->getUserValue5() );
				$user_value6 = ( ( $this->getUserValue6() == '' ) ? $cd_obj->getUserValue6() : $this->getUserValue6() );
				$user_value7 = ( ( $this->getUserValue7() == '' ) ? $cd_obj->getUserValue7() : $this->getUserValue7() );
				$user_value9 = ( ( $this->getUserValue9() == '' ) ? $cd_obj->getUserValue9() : $this->getUserValue9() );
				Debug::Text( 'UserValue: 1: ' . $user_value1 . ' 2: ' . $user_value2 . ' 3: ' . $user_value3 . ' 4: ' . $user_value4 . ' 5: ' . $user_value5 . ' 6: ' . $user_value6 . ' 7: ' . $user_value7 . ' 9: ' . $user_value9, __FILE__, __LINE__, __METHOD__, 10 );

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), null );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text( 'User Currency ID: ' . $currency_id, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$pd_obj->setYearToDateGrossIncome( $cd_obj->getCalculationYTDAmount( $pay_stub_obj ) );       //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setYearToDateDeduction( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) ); //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setGrossPayPeriodIncome( $amount );

				if ( $cd_obj->getCountry() == 'CA' ) {
					$user_value1 = $this->Validator->stripNonFloat( $user_value1 );

					//CA
					$pd_obj->setFederalTotalClaimAmount( $user_value1 );
					$pd_obj->setEnableCPPAndEIDeduction( true );

					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $cd_obj->getCompany(), $cd_obj->getLegalEntity(), 90, 10 ); //90=CPP, 10=Enabled
					if ( $cdlf->getRecordCount() == 1 ) {
						$tmp_cd_obj = $cdlf->getCurrent();
						Debug::Text( 'Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__, 10 );

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Since this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						//$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $tmp_cd_obj->getPayStubEntryAccount() );
						if ( isset( $current_cpp['amount'] ) && $current_cpp['amount'] == 0 ) {
							Debug::Text( 'Current CPP: ' . $current_cpp['amount'] . ' Setting CPP exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10 );
							$pd_obj->setCPPExempt( true );
						} else if ( isset( $current_cpp['amount'] ) && $current_cpp['amount'] != 0 ) {
							$pd_obj->setEmployeeCPPForPayPeriod( $current_cpp['amount'] ); //Make sure we pass in the amount that was calculated, as it may have different include/exclude accounts than this.
						}

						//$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, $tmp_cd_obj->getPayStubEntryAccount() );

						Debug::text( 'YTD CPP Contribution: ' . $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset( $ytd_cpp_arr, $current_cpp, $tmp_cd_obj );
					}

					$cdlf->getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $cd_obj->getCompany(), $cd_obj->getLegalEntity(), 91, 10 ); //91=EI, 10=Enabled
					if ( $cdlf->getRecordCount() == 1 ) {
						$tmp_cd_obj = $cdlf->getCurrent();
						Debug::Text( 'Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__, 10 );

						//See comment above regarding CPP exempt.
						//$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $tmp_cd_obj->getPayStubEntryAccount() );
						if ( isset( $current_ei['amount'] ) && $current_ei['amount'] == 0 ) {
							Debug::Text( 'Current EI: ' . $current_ei['amount'] . ' Setting EI exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10 );
							$pd_obj->setEIExempt( true );
						} else if ( isset( $current_ei['amount'] ) && $current_ei['amount'] != 0 ) {
							$pd_obj->setEmployeeEIForPayPeriod( $current_ei['amount'] ); //Make sure we pass in the amount that was calculated, as it may have different include/exclude accounts than this.
						}

						//$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, $tmp_cd_obj->getPayStubEntryAccount() );

						Debug::text( 'YTD EI Contribution: ' . $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset( $ytd_ei_arr, $current_ei, $tmp_cd_obj );
					}
					unset( $cdlf );
				} else if ( $cd_obj->getCountry() == 'US' ) {
					$user_value2 = $this->Validator->stripNonFloat( $user_value2 );
					//UserValue3 is boolean.
					$user_value4 = $this->Validator->stripNonFloat( $user_value4 );
					$user_value5 = $this->Validator->stripNonFloat( $user_value5 );
					$user_value6 = $this->Validator->stripNonFloat( $user_value6 );
					$user_value7 = $this->Validator->stripNonFloat( $user_value7 );

					//US
					$pd_obj->setFederalFormW4Version( $user_value9 );
					$pd_obj->setFederalFilingStatus( $user_value1 );
					$pd_obj->setFederalAllowance( $user_value2 );
					$pd_obj->setFederalMultipleJobs( ( ( (int)$user_value3 >= 1 ) ? true : false ) );
					$pd_obj->setFederalClaimDependents( $user_value4 );
					$pd_obj->setFederalOtherIncome( $user_value5 );
					$pd_obj->setFederalDeductions( $user_value6 );
					$pd_obj->setFederalAdditionalDeduction( $user_value7 );
					$pd_obj->setFederalTaxExempt( ( ( (int)$this->getUserValue10() >= 1 ) ? true : false ) );
				} else if ( $cd_obj->getCountry() == 'CR' ) {
					//CR
					$pd_obj->setFederalFilingStatus( $user_value1 );  //Single/Married
					$pd_obj->setFederalAllowance( $user_value2 );     //Allownces/Children
				}

				$retval = $pd_obj->getFederalPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 200: //Province Income Tax
				$user_value1 = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$user_value2 = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$user_value3 = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				Debug::Text( 'UserValue: 1: ' . $user_value1 . ' 2: ' . $user_value2 . ' 3: ' . $user_value3, __FILE__, __LINE__, __METHOD__, 10 );

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), $cd_obj->getProvince() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text( 'User Currency ID: ' . $currency_id, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$pd_obj->setYearToDateGrossIncome( $cd_obj->getCalculationYTDAmount( $pay_stub_obj ) );       //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setYearToDateDeduction( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) ); //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setGrossPayPeriodIncome( $amount );

				if ( $cd_obj->getCountry() == 'CA' ) {
					$user_value1 = $this->Validator->stripNonFloat( $user_value1 );

					Debug::Text( 'Canada Pay Period Deductions...', __FILE__, __LINE__, __METHOD__, 10 );
					$pd_obj->setProvincialTotalClaimAmount( $user_value1 );

					$pd_obj->setEnableCPPAndEIDeduction( true );

					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $cd_obj->getCompany(), $cd_obj->getLegalEntity(), 90, 10 ); //90=CPP, 10=Enabled
					if ( $cdlf->getRecordCount() == 1 ) {
						$tmp_cd_obj = $cdlf->getCurrent();
						Debug::Text( 'Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__, 10 );

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Since this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						//$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $tmp_cd_obj->getPayStubEntryAccount() );
						if ( isset( $current_cpp['amount'] ) && $current_cpp['amount'] == 0 ) {
							Debug::Text( 'Current CPP: ' . $current_cpp['amount'] . ' Setting CPP exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10 );
							$pd_obj->setCPPExempt( true );
						} else if ( isset( $current_cpp['amount'] ) && $current_cpp['amount'] != 0 ) {
							$pd_obj->setEmployeeCPPForPayPeriod( $current_cpp['amount'] ); //Make sure we pass in the amount that was calculated, as it may have different include/exclude accounts than this.
						}

						//$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, $tmp_cd_obj->getPayStubEntryAccount() );

						Debug::text( 'YTD CPP Contribution: ' . $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset( $ytd_cpp_arr, $current_cpp, $tmp_cd_obj );
					}

					$cdlf->getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $cd_obj->getCompany(), $cd_obj->getLegalEntity(), 91, 10 ); //91=EI, 10=Enabled
					if ( $cdlf->getRecordCount() == 1 ) {
						$tmp_cd_obj = $cdlf->getCurrent();
						Debug::Text( 'Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__, 10 );

						//See comment above regarding CPP exempt.
						//$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $tmp_cd_obj->getPayStubEntryAccount() );
						if ( isset( $current_ei['amount'] ) && $current_ei['amount'] == 0 ) {
							Debug::Text( 'Current EI: ' . $current_ei['amount'] . ' Setting EI exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10 );
							$pd_obj->setEIExempt( true );
						} else if ( isset( $current_ei['amount'] ) && $current_ei['amount'] != 0 ) {
							$pd_obj->setEmployeeEIForPayPeriod( $current_ei['amount'] ); //Make sure we pass in the amount that was calculated, as it may have different include/exclude accounts than this.
						}

						//$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, $tmp_cd_obj->getPayStubEntryAccount() );

						Debug::text( 'YTD EI Contribution: ' . $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset( $ytd_ei_arr, $current_ei, $tmp_cd_obj );
					}
					unset( $cdlf );

					$retval = $pd_obj->getProvincialPayPeriodDeductions();
				} else if ( $cd_obj->getCountry() == 'US' ) {
					Debug::Text( 'US Pay Period Deductions...', __FILE__, __LINE__, __METHOD__, 10 );

					//Need to set Federal settings here.
					$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
					$udlf->getByUserIdAndCalculationIdAndCountryID( $user_id, 100, $cd_obj->getCountry() );
					if ( $udlf->getRecordCount() > 0 ) {
						$tmp_ud_obj = $udlf->getCurrent();

						if ( $tmp_ud_obj->getUserValue1() == '' ) {
							$tmp_user_value1 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue1();
						} else {
							$tmp_user_value1 = $tmp_ud_obj->getUserValue1();
						}

						if ( $tmp_ud_obj->getUserValue2() == '' ) {
							$tmp_user_value2 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue2();
						} else {
							$tmp_user_value2 = $tmp_ud_obj->getUserValue2();
						}

						Debug::Text( 'Found Federal User Deduction... Total Records: ' . $udlf->getRecordCount() . ' TmpUserValue1: ' . $tmp_user_value1 . ' TmpUserValue2: ' . $tmp_user_value2, __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setFederalFilingStatus( $tmp_user_value1 );
						$pd_obj->setFederalAllowance( $tmp_user_value2 );
						if ( (int)$tmp_ud_obj->getUserValue10() >= 1 ) {
							$pd_obj->setFederalTaxExempt( true );
						}

						unset( $tmp_ud_obj, $tmp_user_value1, $tmp_user_value1 );
					}
					unset( $udlf );

					$pd_obj->setStateFilingStatus( $user_value1 );
					$pd_obj->setStateAllowance( $user_value2 );

					$pd_obj->setUserValue1( $user_value1 );
					$pd_obj->setUserValue2( $user_value2 );
					$pd_obj->setUserValue3( $user_value3 );

					if ( (int)$this->getUserValue10() >= 1 ) {
						$pd_obj->setProvincialTaxExempt( true );
					}

					$retval = $pd_obj->getStatePayPeriodDeductions();
				}

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 300: //District Income Tax
				$user_value1 = ( ( $this->getUserValue1() == '' ) ? $cd_obj->getUserValue1() : $this->getUserValue1() );
				$user_value2 = ( ( $this->getUserValue2() == '' ) ? $cd_obj->getUserValue2() : $this->getUserValue2() );
				$user_value3 = ( ( $this->getUserValue3() == '' ) ? $cd_obj->getUserValue3() : $this->getUserValue3() );
				Debug::Text( 'UserValue: 1: ' . $user_value1 . ' 2: ' . $user_value2 . ' 3: ' . $user_value3, __FILE__, __LINE__, __METHOD__, 10 );

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( 'District: ' . $cd_obj->getDistrict(), __FILE__, __LINE__, __METHOD__, 10 );

				$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), $cd_obj->getProvince(), $cd_obj->getDistrict() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_stub_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );
				$pd_obj->setCurrentPayPeriod( $current_pay_period );
				$pd_obj->setHireAdjustedAnnualPayPeriods( $hire_adjusted_annual_pay_periods );
				$pd_obj->setHireAdjustedCurrentPayPeriod( $hire_adjusted_current_pay_period );
				$pd_obj->setCurrentPayrollRunID( $payroll_run_id );
				$pd_obj->setFormulaType( $formula_type_id );

				$pd_obj->setDistrictFilingStatus( $user_value1 );
				$pd_obj->setDistrictAllowance( $user_value2 );

				$pd_obj->setUserValue1( $user_value1 );
				$pd_obj->setUserValue2( $user_value2 );
				$pd_obj->setUserValue3( $user_value3 );

				$pd_obj->setYearToDateGrossIncome( $cd_obj->getCalculationYTDAmount( $pay_stub_obj ) );       //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setYearToDateDeduction( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) ); //Make sure YTD amount is specified for all calculation types.
				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getDistrictPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
		}

		Debug::Text( 'Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		//Allow negative values, as some advanced tax bracket setups require this.
		if ( $retval < 0 ) {
			//Debug::Text('Deduction was negative, setting to 0...', __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text( 'Deduction was negative...', __FILE__, __LINE__, __METHOD__, 10 );
			//$retval = 0;
		}

		return $retval;
	}

	/**
	 * Returns the maximum taxable wages for any given calculation formula.
	 * Returns FALSE for no maximum.
	 * Primary used in TaxSummary (Generic) report.
	 * @return bool|mixed
	 */
	function getMaximumPayStubEntryAccountAmount( $end_date = null ) {
		$retval = false;

		$cd_obj = $this->getCompanyDeductionObject();
		if ( is_object( $cd_obj ) ) {
			switch ( $cd_obj->getCalculation() ) {
				case 15: //Advanced Percent
					if ( $this->getUserValue2() == '' ) {
						$wage_base = $cd_obj->getUserValue2();
					} else {
						$wage_base = $this->getUserValue2();
					}
					$retval = $this->Validator->stripNonFloat( $wage_base );
					break;
				case 16: //Advanced Percent (w/Target) -- No maximum
					break;
				case 17: //Advanced Percent (Range Bracket)
					if ( $this->getUserValue3() == '' ) {
						$max_wage = $cd_obj->getUserValue3();
					} else {
						$max_wage = $this->getUserValue3();
					}
					$retval = $this->Validator->stripNonFloat( $max_wage );
					break;
				case 18: //Advanced Percent (Tax Bracket)
					if ( $this->getUserValue2() == '' ) {
						$wage_base = $cd_obj->getUserValue2();
					} else {
						$wage_base = $this->getUserValue2();
					}
					$retval = $this->Validator->stripNonFloat( $wage_base );
					break;
				case 84: //US - Social Security Formula (Employee)
				case 85: //US - Social Security Formula (Employer)
				case 90: //Canada - CPP Formula
				case 91: //Canada - EI Formula
					require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );

					switch ( $cd_obj->getCalculation() ) {
						case 84: //US - Social Security Formula (Employee)
						case 85: //US - Social Security Formula (Employer)
							$pd_obj = new PayrollDeduction( 'US', null );
							$pd_obj->setCompany( $cd_obj->getCompany() );
							$pd_obj->setDate( $end_date );

							$retval = $pd_obj->getSocialSecurityMaximumEarnings();
							break;
						case 90: //Canada - CPP Formula
							$pd_obj = new PayrollDeduction( 'CA', null );
							$pd_obj->setCompany( $cd_obj->getCompany() );
							$pd_obj->setDate( $end_date );

							$retval = $pd_obj->getCPPMaximumEarnings();
							break;
						case 91: //Canada - EI Formula
							$pd_obj = new PayrollDeduction( 'CA', null );
							$pd_obj->setCompany( $cd_obj->getCompany() );
							$pd_obj->setDate( $end_date );

							$retval = $pd_obj->getEIMaximumEarnings();
							break;
					}
					break;
			}
		}

		return $retval;
	}

	/**
	 * Returns the percent rate when specified.
	 * @return bool|mixed
	 */
	function getRate() {
		$retval = false;

		$cd_obj = $this->getCompanyDeductionObject();
		if ( is_object( $cd_obj ) ) {
			switch ( $cd_obj->getCalculation() ) {
				case 15: //Advanced Percent
				case 16: //Advanced Percent (w/Target)
				case 17: //Advanced Percent (Range Bracket)
				case 18: //Advanced Percent (Tax Bracket)
					if ( $this->getUserValue1() == '' ) {
						$percent = $cd_obj->getUserValue1();
					} else {
						$percent = $this->getUserValue1();
					}
					$retval = $this->Validator->stripNonFloat( $percent );
					break;
			}
		}

		return $retval;
	}

	/**
	 * Migrates UserDeductions as best as it possibly can for an employee when switching legal entities.
	 * @param $user_obj  object
	 * @param $data_diff array
	 * @return bool
	 */
	static function MigrateLegalEntity( $user_obj, $data_diff ) {
		//Get all CompanyDeduction records assigned to the new legal entity so we can quickly loop over them multiple times if needed.

		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */

		$cdlf->StartTransaction();

		$cdlf->getByCompanyIdAndLegalEntityId( $user_obj->getCompany(), $user_obj->getLegalEntity() );

		$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getId() );
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::text( 'Legal Entity changed. Trying to match all tax/deduction data to new entity for user: ' . $user_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $udlf as $ud_obj ) {
				$matched_company_deduction_ids = [];

				$cd_obj = $ud_obj->getCompanyDeductionObject();

				if ( is_object( $cd_obj ) && $cd_obj->getLegalEntity() == TTUUID::getZeroId() ) {
					Debug::text( '  Skipping due to no legal entity assigned: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( is_object( $cd_obj ) && $cd_obj->getLegalEntity() == $user_obj->getGenericOldDataValue( 'legal_entity_id' ) ) { //Only convert records assigned to the old legal entity. Skip records not assigned to any legal entity.
					//Search for matching CompanyDeduction record to try to re-assign them to.
					//  Must Match: Calculate Type, Legal Entity -> User Legal Entity, Pay Stub Account
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach ( $cdlf as $tmp_cd_obj ) {
							if ( $cd_obj->getCalculation() == $tmp_cd_obj->getCalculation()
									&& $tmp_cd_obj->getLegalEntity() == $user_obj->getLegalEntity()
									&& $cd_obj->getPayStubEntryAccount() == $tmp_cd_obj->getPayStubEntryAccount()
							) {
								Debug::text( '  Legal Entity/Calculation/Pay Stub Account Match Found! Company Deduction: Old: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ') New: ' . $tmp_cd_obj->getName() . '(' . $tmp_cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
								$matched_company_deduction_ids[$tmp_cd_obj->getId()] = $tmp_cd_obj->getName(); //Use an array, if more than exactly one match, we can't migrate date to it.
							} else {
								Debug::text( '  NOT a Match... Company Deduction: Old: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ') New: ' . $tmp_cd_obj->getName() . '(' . $tmp_cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
						unset( $tmp_cd_obj );
					}
				}

				Debug::text( '  Matches Found (' . count( $matched_company_deduction_ids ) . ')!', __FILE__, __LINE__, __METHOD__, 10 );
				if ( count( $matched_company_deduction_ids ) > 1 ) {
					$matched_company_deduction_id = Misc::findClosestMatch( $cd_obj->getName(), $matched_company_deduction_ids );
					Debug::text( '  Closest Match: ' . $matched_company_deduction_id . ' Searched For: ' . $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
				} else if ( count( $matched_company_deduction_ids ) == 1 ) {
					reset( $matched_company_deduction_ids );
					$matched_company_deduction_id = key( $matched_company_deduction_ids );
					Debug::text( '  Only one Match: ' . $matched_company_deduction_id, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					$matched_company_deduction_id = null;
					Debug::text( '  No Match Found (' . count( $matched_company_deduction_ids ) . ')!', __FILE__, __LINE__, __METHOD__, 10 );
				}

				if ( $matched_company_deduction_id != '' ) {
					//Create new UserDeduction record so the audit log shows the employee being removed from one CompanyDeduction record and assigned to another.
					$tmp_ud_obj = clone $ud_obj;
					$tmp_ud_obj->setId( false );
					$tmp_ud_obj->setCompanyDeduction( $matched_company_deduction_id );
					if ( $tmp_ud_obj->isValid() ) {
						$tmp_ud_obj->Save();
					}
					unset( $tmp_ud_obj );
				}

				if ( $cd_obj->getLegalEntity() == TTUUID::getZeroID() && $matched_company_deduction_id == '' ) {
					Debug::text( '  No legal entity assigned to Tax/Deduction record, and no match found, so *not* unassigning user from: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::text( '  Unassigning user from: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
					$ud_obj->setDeleted( true );
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					} else {
						Debug::text( '  ERROR! Validation failed when reassigning CompanyDeduction records... Company Deduction: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}
		}

		$cdlf->CommitTransaction();

		unset( $udlf, $ud_obj, $cd_obj, $cdlf, $matched_company_deduction_ids );

		return true;
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
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
		// Tax/Deduction
		if ( $this->getCompanyDeduction() == TTUUID::getZeroID() ) {
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$this->Validator->isResultSetWithRows( 'company_deduction',
												   $cdlf->getByID( $this->getCompanyDeduction() ),
												   TTi18n::gettext( 'Tax/Deduction is invalid' )
			);
		}
		// Length Of Service Date
		if ( $this->getLengthOfServiceDate() != '' ) {
			$this->Validator->isDate( 'length_of_service_date',
									  $this->getLengthOfServiceDate(),
									  TTi18n::gettext( 'Incorrect Length Of Service Date' )
			);
		}
		// Start Date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect Start Date' )
			);
		}
		// End Date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect End Date' )
			);
		}
		// User Value 1
		if ( $this->getUserValue1() != '' ) {
			$this->Validator->isLength( 'user_value1',
										$this->getUserValue1(),
										TTi18n::gettext( 'User Value 1 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 2
		if ( $this->getUserValue2() != '' ) {
			$this->Validator->isLength( 'user_value2',
										$this->getUserValue2(),
										TTi18n::gettext( 'User Value 2 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 3
		if ( $this->getUserValue3() != '' ) {
			$this->Validator->isLength( 'user_value3',
										$this->getUserValue3(),
										TTi18n::gettext( 'User Value 3 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 4
		if ( $this->getUserValue4() != '' ) {
			$this->Validator->isLength( 'user_value4',
										$this->getUserValue4(),
										TTi18n::gettext( 'User Value 4 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 5
		if ( $this->getUserValue5() != '' ) {
			$this->Validator->isLength( 'user_value5',
										$this->getUserValue5(),
										TTi18n::gettext( 'User Value 5 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 6
		if ( $this->getUserValue6() != '' ) {
			$this->Validator->isLength( 'user_value6',
										$this->getUserValue6(),
										TTi18n::gettext( 'User Value 6 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 7
		if ( $this->getUserValue7() != '' ) {
			$this->Validator->isLength( 'user_value7',
										$this->getUserValue7(),
										TTi18n::gettext( 'User Value 7 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 8
		if ( $this->getUserValue8() != '' ) {
			$this->Validator->isLength( 'user_value8',
										$this->getUserValue8(),
										TTi18n::gettext( 'User Value 8 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 9
		if ( $this->getUserValue9() != '' ) {
			$this->Validator->isLength( 'user_value9',
										$this->getUserValue9(),
										TTi18n::gettext( 'User Value 9 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 10
		if ( $this->getUserValue10() != '' ) {
			$this->Validator->isLength( 'user_value10',
										$this->getUserValue10(),
										TTi18n::gettext( 'User Value 10 is too short or too long' ),
										1,
										20
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getUser() == false ) {
			$this->Validator->isTrue( 'user',
									  false,
									  TTi18n::gettext( 'Employee not specified' ) );
		}

		if ( TTUUID::isUUID( $this->getUser() )
				&& $this->getDeleted() == false
				&& TTUUID::isUUID( $this->getCompanyDeduction() )
				&& is_object( $this->getCompanyDeductionObject() ) ) {
			$this->Validator->isTrue( 'company_deduction',
									  $this->isUniqueCompanyDeduction( $this->getCompanyDeduction() ),
									  TTi18n::gettext( 'Tax/Deduction is already assigned to employee' ) . ': ' . $this->getCompanyDeductionObject()->getName()
			);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//If the length of service date matches the current hire date, make it blank so we always default to the hire date in case it changes later.
		if ( is_object( $this->getUserObject() ) && TTDate::getMiddleDayEpoch( $this->getLengthOfServiceDate() ) == TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) ) {
			Debug::Text( 'Forcing blank LengthOfServiceDate as it matches hire_date...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setLengthOfServiceDate( '' );
		}
		if ( is_object( $this->getCompanyDeductionObject() ) && TTDate::getMiddleDayEpoch( $this->getStartDate() ) == TTDate::getMiddleDayEpoch( $this->getCompanyDeductionObject()->getStartDate() ) ) {
			Debug::Text( 'Forcing blank StartDate as it matches Tax/Deduction record...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setStartDate( '' );
		}
		if ( is_object( $this->getCompanyDeductionObject() ) && TTDate::getMiddleDayEpoch( $this->getEndDate() ) == TTDate::getMiddleDayEpoch( $this->getCompanyDeductionObject()->getEndDate() ) ) {
			Debug::Text( 'Forcing blank EndDate as it matches Tax/Deduction record...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setEndDate( '' );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

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
						case 'company_deduction_id':
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );

								//As soon as we set the Company Deduction record, parse the UserValues before they are set later on.
								if ( is_object( $this->getCompanyDeductionObject() ) ) {
									$data = $this->getCompanyDeductionObject()->parseUserValues( $this->getCompanyDeductionObject()->getCalculation(), $data );
								}
							}
							break;
						case 'length_of_service_date':
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			$cdf = new CompanyDeductionFactory();

			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						//CompanyDeduction columns.
						case 'name':
						case 'status_id':
						case 'type_id':
						case 'calculation_id':
							//User columns.
						case 'first_name':
						case 'last_name':
						case 'middle_name':
						case 'user_status_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
							$data[$variable] = Option::getByKey( $this->getColumn( $variable . '_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'full_name':
							$data[$variable] = Misc::getFullName( $this->getColumn( 'first_name' ), $this->getColumn( 'middle_name' ), $this->getColumn( 'last_name' ), true, true );
							break;
						//CompanyDeduction columns.
						case 'type':
						case 'status':
						case 'calculation':
							$data[$variable] = Option::getByKey( $this->getColumn( $variable . '_id' ), $cdf->getOptions( $variable ) );
							break;
						case 'length_of_service_date':
						case 'start_date':
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
		$obj = $this->getUserObject();
		if ( is_object( $obj ) ) {
			return TTLog::addEntry( $this->getCompanyDeduction(), $log_action, TTi18n::getText( 'Employee Deduction' ) . ': ' . $obj->getFullName(), null, $this->getTable(), $this );
		}

		return false;
	}
}

?>
