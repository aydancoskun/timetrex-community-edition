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
 * @package Modules\Payroll Agency
 */
class PayrollRemittanceAgencyFactory extends Factory {
	protected $table = 'payroll_remittance_agency';
	protected $pk_sequence_name = 'payroll_remittance_agency_id_seq'; //PK Sequence name

	protected $legal_entity_obj = null;
	protected $contact_user_obj = null;
	protected $remittance_source_account_obj = null;

	/**
	 * @param bool $name
	 * @param null $params
	 * @return array|mixed|null
	 */
	function _getFactoryOptions( $name = false, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Enabled' ),
						20 => TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Federal' ),
						20 => TTi18n::gettext( 'Provincial/State' ),
						30 => TTi18n::gettext( 'Local (City/District/County)' ),
						40 => TTi18n::gettext( '3rd Party' ),
				];
				break;
			case 'always_week_day':
				$retval = [
					//Adjust holiday to next weekday
					0 => TTi18n::gettext( 'No' ),
					1 => TTi18n::gettext( 'Yes - Previous Business Day' ),
					2 => TTi18n::gettext( 'Yes - Next Business Day' ),
					3 => TTi18n::gettext( 'Yes - Closest Business Day' ),
				];
				break;
			case 'agency_id_field_labels':
				if ( !isset( $params['agency_id'] ) ) {
					return false;
				}

				$retval = [
						'primary_identification'   => TTi18n::gettext( 'Primary Identification' ),
						'secondary_identification' => TTi18n::gettext( 'Secondary Identification' ),
						'tertiary_identification'  => TTi18n::gettext( 'Tertiary Identification' ),
				];

				$val = $this->parseAgencyID( $params['agency_id'] );
				$type_id = $val['type_id'];
				$country = $val['country'];
				$province = $val['province'];
				$id = $val['id'];

				switch ( $type_id ) {
					case 10: //Federal
						$agency_labels = [
								'10:CA:00:00:0010' => [
										'primary_identification' => TTi18n::gettext( 'Business Number' ),
										//'secondary_identification' => TTi18n::gettext( 'Transmitter #' ),
										//'tertiary_identification' => TTi18n::gettext( 'Web Access Code' )
								],
								'10:CA:00:00:0020' => [], //Get the business number from CRA agency above. This needs to login to CRA
								'10:US:00:00:0010' => [ 'primary_identification' => TTi18n::gettext( 'EIN' ), 'tertiary_identification' => TTi18n::gettext( 'eFile User ID' ) ],
								'10:US:00:00:0020' => [ 'primary_identification' => TTi18n::gettext( 'EIN' ) ],
						];

						break;
					case 20: //Province/State
						if ( $country == 'US' && (int)$id == 10 ) {
							switch ( $province ) { // See: http://kb.drakesoftware.com/Site/Browse/W2-State-eFiling-in-CWU
								//case 'CO': //Does not seem to require State Control Number - https://www.colorado.gov/pacific/sites/default/files/Withholding6.pdf
								//case 'KS':
								//case 'VT': //Does not require State Control Number - https://tax.vermont.gov/sites/tax/files/documents/GB-1118.pdf
								//	$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'State ID #' ), 'secondary_identification' => TTi18n::gettext( 'State Control Code' ) ];
								//	break;
								case 'IA':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Withholding Permit #' ), 'secondary_identification' => TTi18n::gettext( 'Business eFile Number (BEN)' ), ];
									break;
								case 'IN':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Taxpayer ID (TID) (13 Digits)' ) ];
									break;
								case 'ME':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Withholding Account Number' ) ];
									break;
								case 'MN':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Tax ID Number' ) ];
									break;
								case 'NJ':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Taxpayer Identification Number' ) ];
									break;
								case 'WI':
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'Tax Account Number' ) ];
									break;
								//case 'DC':  -- Not 100% certain about these, so allow ID field to be entered still.
								//case 'GA':
								//case 'KY':
								//case 'NE':
								//case 'ND':
								//case 'UT':
								//	$agency_labels[$params['agency_id']] = []; //No State Employer Tax ID, uses FEIN instead most likely?
								//	break;
								default:
									$agency_labels[$params['agency_id']] = [ 'primary_identification' => TTi18n::gettext( 'State ID #' ) ]; //, 'tertiary_identification' => TTi18n::gettext( 'eFile User ID' ) ];
									break;
							}
						}

						break;
				}

				if ( isset( $agency_labels[$params['agency_id']] ) ) {
					$retval = $agency_labels[$params['agency_id']];
				}

				break;
			case 'agency':
				//All 4 params must be specified
				if ( !isset( $params['type_id'] ) || !isset( $params['country'] ) || !isset( $params['province'] ) || !isset( $params['district'] ) ) {
					return false;
				}

				if ( $params['country'] == false ) {
					$params['country'] = '00';
				}
				if ( $params['province'] == false ) {
					$params['province'] = '00';
				}
				if ( $params['district'] == false ) {
					$params['district'] = '00';
				}

				$params['country'] = strtoupper( $params['country'] );
				$params['province'] = strtoupper( $params['province'] );
				$params['district'] = strtoupper( $params['district'] );

				//List of agencies and important information, such as holidays and dates. https://community.intuit.com/browse/payroll-compliance-us-en
				//Federal New Hire reporting law: More info: https://www.law.cornell.edu/uscode/text/42/653a, https://newhire-reporting.com/TN-Newhire/PrintForm.aspx

				$options = [
					// Canada IDs start with 1, US with 2.
					'10' => [ //Federal
							  'CA' => [
									  '10' => TTi18n::gettext( 'Canada Revenue Agency (CRA)' ), //[Federal/Provincial Tax/EI/CPP]
									  '20' => TTi18n::gettext( 'Service Canada [ROE]' ),
							  ],

							  'US' => [
									  '10'  => TTi18n::gettext( 'Internal Revenue Service (IRS)' ), //Tax, Social Security, Form 941, 940, 1099 //[Federal Tax/Social Security/Medicare]
									  '20'  => TTi18n::gettext( 'Social Security Administration (SSA)' ), //Form W2, no payments. //[FUTA/Unemployment]
									  '100' => TTi18n::gettext( 'Centers for Medicare & Medical Services (CMS.gov)' ), //CMS Payroll Based Jounal (PBJ)
							  ],
					],
					'20' => [ //Province/State
							  'CA' => [
									  'AB' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Program (MEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ),
									  ],
									  'BC' => [
											  '40'  => TTi18n::gettext( 'Family Maintenance Enforcement Program (FMEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Worksafe BC' ),
									  ],
									  'SK' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Office' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ),
									  ],
									  'MB' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Program (MEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ),
									  ],
									  'QC' => [
											  '40'  => TTi18n::gettext( 'Support-Payment Collection Progam' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ),
									  ],
									  'ON' => [
											  '40'  => TTi18n::gettext( 'Family Responsibility Office' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workplace Safety and Insurance Board' ),
									  ],
									  'NL' => [
											  '40'  => TTi18n::gettext( 'Support Enforcement Program' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workplace Health, Safety & Compensation Commission' ),
									  ],
									  'NB' => [
											  '40'  => TTi18n::gettext( 'Office of Support Enforcement (OSE)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Worksafe NB' ),
									  ],
									  'NS' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Program (MEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ) //Paid through the CRA.
									  ],
									  'PE' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Program (MEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation Board' ),
									  ],
									  'NT' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Program (MEP)' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Safety and Compensation Commission' ),
									  ],
									  'YT' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Office' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Compensation, Health and Safety Board' ),
									  ],
									  'NU' => [
											  '40'  => TTi18n::gettext( 'Maintenance Enforcement Office' ), //ie: Child Support
											  '100' => TTi18n::gettext( 'Workers Safety and Compensation Commission' ),
									  ],
							  ],
							  'US' => [
									  'AL' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'AK' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'AZ' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'AR' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'CA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  //'20' => TTi18n::gettext('State Government [Unemployment Insurance]'), //Combined with State
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'CO' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'CT' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'DE' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'DC' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'FL' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'GA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'HI' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'ID' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'IL' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'IN' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'IA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'KS' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'KY' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'LA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'ME' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MD' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MI' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MN' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MS' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MO' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'MT' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NE' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NV' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NH' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NM' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  //'20' => TTi18n::gettext('State Government [Unemployment Insurance]'), //Combined with State Income Tax
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NJ' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NY' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  //'20' => TTi18n::gettext('State Government [Unemployment Insurance]'), //Combined with State Income Tax
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'NC' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'ND' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'State Government [Workers Compensation]' ), //Mandatory
									  ],
									  'OH' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'State Government [Workers Compensation]' ), //Mandatory
									  ],
									  'OK' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'OR' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  //'20' => TTi18n::gettext('State Government [Unemployment Insurance]'), //Combined with income tax.
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'PA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'RI' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'SC' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'SD' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'TN' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'TX' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'UT' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'VT' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'VA' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'WA' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
										  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
										  '100' => TTi18n::gettext( 'State Government [Workers Compensation]' ), //Mandatory
									  ],
									  'WV' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'WI' => [
											  '10'  => TTi18n::gettext( 'State Government [State Income Tax]' ),
											  '20'  => TTi18n::gettext( 'State Government [Unemployment Insurance]' ),
											  '30'  => TTi18n::gettext( 'State Government [New Hires]' ),
											  '40'  => TTi18n::gettext( 'State Government [Child Support]' ),
											  '100' => TTi18n::gettext( 'Workers Compensation' ),
									  ],
									  'WY' => [
										  //'10' => TTi18n::gettext('State Government [State Income Tax]'), //No state income tax.
										  '20' => TTi18n::gettext( 'State Government [Unemployment Insurance/WC]' ),
										  '30' => TTi18n::gettext( 'State Government [New Hires]' ),
										  '40' => TTi18n::gettext( 'State Government [Child Support]' ),
										  //'100' => TTi18n::gettext('State Government [Workers Compensation]'), //Mandatory (Combined with Unemployment Insurance)
									  ],
							  ],
					],
					'30' => [ //District/Local
							  'CA' => [
									  'AB' => [],
									  'BC' => [],
									  'SK' => [],
									  'MB' => [],
									  'QC' => [],
									  'ON' => [],
									  'NL' => [],
									  'NB' => [],
									  'NS' => [],
									  'PE' => [],
									  'NT' => [],
									  'YT' => [],
									  'NU' => [],
							  ],
							  'US' => [
									  'AL' => [],
									  'AK' => [],
									  'AZ' => [],
									  'AR' => [],
									  'CA' => [],
									  'CO' => [],
									  'CT' => [],
									  'DE' => [],
									  'DC' => [],
									  'FL' => [],
									  'GA' => [],
									  'HI' => [],
									  'ID' => [],
									  'IL' => [],
									  'IN' => [],
									  'IA' => [],
									  'KS' => [],
									  'KY' => [],
									  'LA' => [],
									  'ME' => [],
									  'MD' => [],
									  'MA' => [],
									  'MI' => [],
									  'MN' => [],
									  'MS' => [],
									  'MO' => [],
									  'MT' => [],
									  'NE' => [],
									  'NV' => [],
									  'NH' => [],
									  'NM' => [],
									  'NJ' => [],
									  'NY' => [
											  'NYC'     => [ '10' => TTi18n::gettext( 'New York City [City Income Tax]' ) ],
											  'YONKERS' => [ '20' => TTi18n::gettext( 'Yonkers [City Income Tax]' ) ],
									  ],
									  'NC' => [],
									  'ND' => [],
									  'OH' => [
											  '00' => [ '10' => TTi18n::gettext( 'Regional Income Tax Agency (RITA) [Local Income Tax]' ) ],
									  ],
									  'OK' => [],
									  'OR' => [],
									  'PA' => [],
									  'RI' => [],
									  'SC' => [],
									  'SD' => [],
									  'TN' => [],
									  'TX' => [],
									  'UT' => [],
									  'VT' => [],
									  'VA' => [],
									  'WA' => [],
									  'WV' => [],
									  'WI' => [],
									  'WY' => [],
							  ],
					],
					'40' => [ //3rd Party
							  'CA' => [
									  '00' => [

											  '1010' => TTi18n::gettext( 'Blue Cross' ),
											  '1020' => TTi18n::gettext( 'Standard Life' ),
											  '1030' => TTi18n::gettext( 'Great West Life' ),
											  '1040' => TTi18n::gettext( 'Sun Life' ),
											  '1050' => TTi18n::gettext( 'Manulife' ),
											  '1060' => TTi18n::gettext( 'BCAA' ),
											  '8000' => TTi18n::gettext( 'Union' ),
											  '9500' => TTi18n::gettext( 'Child Support' ),
											  '9510' => TTi18n::gettext( 'Garnishment' ),
									  ],
									  'AB' => [//9999 => TTi18n::gettext('BCAA'),
									  ],
									  'BC' => [],
									  'SK' => [],
									  'MB' => [],
									  'QC' => [],
									  'ON' => [],
									  'NL' => [],
									  'NB' => [],
									  'NS' => [],
									  'PE' => [],
									  'NT' => [],
									  'YT' => [],
									  'NU' => [],
							  ],
							  'US' => [
									  '00' => [
											  '1010' => 'Aetna',
											  '1020' => 'AIG',
											  '1030' => 'All Savers',
											  '1040' => 'Allied National',
											  '1050' => 'Always Care',
											  '1060' => 'Ameritas',
											  '1070' => 'Assurant Health',
											  '1080' => 'Assurant Employee Benefits',
											  '1090' => 'Avesis',
											  '1110' => 'BCBS',
											  '1120' => 'BEST',
											  '1130' => 'Christian Church Health Care Benefit Trust',
											  '1140' => 'Cigna',
											  '1150' => 'Coventry',
											  '1160' => 'Dearborn National',
											  '1170' => 'Delta Dental',
											  '1180' => 'Dental Select',
											  '1190' => 'Eye Med',
											  '1200' => 'First Continental',
											  '1210' => 'Guardian',
											  '1220' => 'Guidestone',
											  '1230' => 'Humana',
											  '1240' => 'Lincoln Financial',
											  '1250' => 'Memorial Hermann',
											  '1260' => 'Meritain Health',
											  '1270' => 'MetLife',
											  '1280' => 'Mutual of Omaha',
											  '1290' => 'National Guardian Vision',
											  '1300' => 'National Vision Administrators',
											  '1310' => 'Nippon Life',
											  '1320' => 'OptiMed',
											  '1330' => 'PHCS MultiPlan',
											  '1340' => 'Principal Financial',
											  '1350' => 'Reliance Standard',
											  '1360' => 'Scott & White',
											  '1370' => 'Starmark',
											  '1380' => 'Sun Life',
											  '1390' => 'Sure Bridge',
											  '1400' => 'The Standard',
											  '1410' => 'Transamerica',
											  '1420' => 'UHC River Valley',
											  '1430' => 'UMR',
											  '1440' => 'United',
											  '1450' => 'United Concordia',
											  '1460' => 'Unum',
											  '1470' => 'VSP',
											  '8000' => 'Union',
											  '9500' => TTi18n::gettext( 'Child Support' ),
											  '9510' => TTi18n::gettext( 'Garnishment' ),
									  ],
									  'AL' => [],
									  'AK' => [],
									  'AZ' => [],
									  'AR' => [],
									  'CA' => [],
									  'CO' => [],
									  'CT' => [],
									  'DE' => [],
									  'DC' => [],
									  'FL' => [],
									  'GA' => [],
									  'HI' => [],
									  'ID' => [],
									  'IL' => [],
									  'IN' => [],
									  'IA' => [],
									  'KS' => [],
									  'KY' => [],
									  'LA' => [],
									  'ME' => [],
									  'MD' => [],
									  'MA' => [],
									  'MI' => [],
									  'MN' => [],
									  'MS' => [],
									  'MO' => [],
									  'MT' => [],
									  'NE' => [],
									  'NV' => [],
									  'NH' => [],
									  'NM' => [],
									  'NJ' => [],
									  'NY' => [],
									  'NC' => [],
									  'ND' => [],
									  'OH' => [],
									  'OK' => [],
									  'OR' => [],
									  'PA' => [],
									  'RI' => [],
									  'SC' => [],
									  'SD' => [],
									  'TN' => [],
									  'TX' => [],
									  'UT' => [],
									  'VT' => [],
									  'VA' => [],
									  'WA' => [],
									  'WV' => [],
									  'WI' => [],
									  'WY' => [],
							  ],
					],
				];

				//Return all values in the following format:
				//IDs: <Type>:<Country Code>:<Province Code>:<District Code>:<ID>

				$prefix = $params['type_id'] . ':' . $params['country'] . ':' . ( isset( $params['province'] ) ? $params['province'] : '00' ) . ':' . ( isset( $params['district'] ) ? $params['district'] : '00' ) . ':';
				if ( isset( $options[$params['type_id']] ) ) {
					switch ( $params['type_id'] ) {
						case 10:
							if ( isset( $options[$params['type_id']][$params['country']] ) ) {
								//$prefix = $params['type_id'].':'.$params['country'].':00:00:';
								$tmp_retval = $options[$params['type_id']][$params['country']];
							}
							break;
						case 20:
						case 40:
							if ( isset( $options[$params['type_id']][$params['country']][$params['province']] ) ) {
								//$prefix = $params['type_id'].':'.$params['country'].':'.$params['province'].':00:';
								$tmp_retval = $options[$params['type_id']][$params['country']][$params['province']];
								if ( isset( $options[$params['type_id']][$params['country']]['00'] ) ) { //Append non-province specifc items.
									//Do not use array_merge here. It does not preserve the numeric key the way that the addition operator does.
									$tmp_retval = ( $tmp_retval + $options[$params['type_id']][$params['country']]['00'] );
								}
							}
							break;
						case 30:
							if ( isset( $options[$params['type_id']][$params['country']][$params['province']][$params['district']] ) ) {
								//$prefix = $params['type_id'].':'.$params['country'].':'.$params['province'].':'.$params['district'].':';
								$tmp_retval = $options[$params['type_id']][$params['country']][$params['province']][$params['district']];
							}
							break;
					}
				}

				//Don't add the "Other" option for Federal and provincial agencies in US or CA as we define them all explicitly.
				if ( !( ( $params['type_id'] == 10 || $params['type_id'] == 20 ) && ( $params['country'] == 'CA' || $params['country'] == 'US' ) ) ) {
					$tmp_retval['0000'] = '-- ' . TTi18n::gettext( 'Other' ) . ' --';
				}

				//Add prefix to each returned item.
				if ( isset( $tmp_retval ) && is_array( $tmp_retval ) ) {
					$retval = [];
					foreach ( $tmp_retval as $key => $value ) {
						$key = str_pad( $key, 4, '0', STR_PAD_LEFT );
						$retval[$prefix . $key] = $value;
					}
				} else {
					$retval = [ 0 => TTi18n::gettext( '-- None --' ) ];
				}
				unset( $prefix, $tmp_retval );

				//Debug::Arr($retval, 'Type ID: '. $params['type_id'] .' Country: '. $params['country'] .' Province: '. $params['province'] .' District: '. $params['district'] .' Agencies: ', __FILE__, __LINE__, __METHOD__, 10);
				break;
			case 'columns':
				$retval = [
						'-1010-status'                  => TTi18n::gettext( 'Status' ),
						'-1020-type'                    => TTi18n::gettext( 'Type' ),
						'-1030-name'                    => TTi18n::gettext( 'Name' ),
						'-1035-legal_entity_legal_name' => TTi18n::gettext( 'Legal Entity' ),
						'-1140-description'             => TTi18n::gettext( 'Description' ),
						'-1170-province'                => TTi18n::gettext( 'Province/State' ),
						'-1180-country'                 => TTi18n::gettext( 'Country' ),

						'-1190-district'               => TTi18n::gettext( 'District' ),
						'-1200-agency'                 => TTi18n::gettext( 'Agency' ),
						'-1210-primary_identification' => TTi18n::gettext( 'Primary Identification' ),

						'-1300-secondary_identification'  => TTi18n::gettext( 'Secondary Identification' ),
						'-1320-tertiary_identification'   => TTi18n::gettext( 'Tertiary Identification' ),
						'-1330-contact_user'              => TTi18n::gettext( 'Contact' ),
						'-1340-remittance_source_account' => TTi18n::gettext( 'Remittance Source Account' ),
						'-1420-start_date'                => TTi18n::gettext( 'Start Date' ),
						'-1450-end_date'                  => TTi18n::gettext( 'End Date' ),

						//'-1900-in_use' => TTi18n::gettext('In Use'), //doesn't make sense here, as several agencies are for reporting only (ie: New Hires, Social Security Administration) and will not have Tax/Deduction records assigned to them.

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
						'legal_entity_legal_name',
						'name',
						'type',
						'province',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
						'type_id',
						'country',
						'province',
						'district',
						'agency_id',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [
						'country',
						'province',
				];
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
				'id'                           => 'ID',
				'legal_entity_id'              => 'LegalEntity',
				'legal_entity_legal_name'      => false,
				'status_id'                    => 'Status',
				'status'                       => false,
				'type_id'                      => 'Type',
				'type'                         => false,
				'name'                         => 'Name',
				'description'                  => 'Description',
				'country'                      => 'Country',
				'province'                     => 'Province',
				'district'                     => 'District',
				'agency_id'                    => 'Agency',
				'agency'                       => false,
				'primary_identification'       => 'PrimaryIdentification',
				'secondary_identification'     => 'SecondaryIdentification',
				'tertiary_identification'      => 'TertiaryIdentification',
				'user_name'                    => 'UserName',
				'password'                     => 'Password',
				'contact_user_id'              => 'ContactUser',
				'contact_user'                 => false,
				'remittance_source_account_id' => 'RemittanceSourceAccount',
				'remittance_source_account'    => false,
				'always_week_day_id'           => 'AlwaysOnWeekDay',
				'recurring_holiday_policy_id'  => 'RecurringHoliday',

				'start_date' => 'StartDate',
				'end_date'   => 'EndDate',

				'in_use' => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		$le_obj = $this->getLegalEntityObject();
		if ( is_object( $le_obj ) ) {
			return $le_obj->getCompanyObject();
		}

		return false;
	}

	/**
	 * @return object|bool
	 */
	function getContactUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getContactUser(), 'contact_user_obj' );
	}

	/**
	 * @return object|bool
	 */
	function getLegalEntityObject() {
		return $this->getGenericObject( 'LegalEntityListFactory', $this->getLegalEntity(), 'legal_entity_obj' );
	}

	/**
	 * @return object|bool
	 */
	function getRemittanceSourceAccountObject() {
		return $this->getGenericObject( 'RemittanceSourceAccountListFactory', $this->getRemittanceSourceAccount(), 'remittance_source_account_obj' );
	}

	/**
	 * @return object|bool
	 */
	function getCompanyDeductionListFactory() {
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndPayrollRemittanceAgencyId( $this->getCompanyObject()->getId(), $this->getId() );

		return $cdlf;
	}


	/**
	 * @return bool|mixed
	 */
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegalEntity( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceSourceAccount() {
		return $this->getGenericDataValue( 'remittance_source_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRemittanceSourceAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'remittance_source_account_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAgency() {
		return $this->getGenericDataValue( 'agency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAgency( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'agency_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getRecurringHoliday() {
		$company_obj = $this->getCompanyObject();
		if ( is_object( $company_obj ) ) {
			return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_obj->getId(), 5000, $this->getID() );
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setRecurringHoliday( $ids ) {
		Debug::text( 'Setting Recurring Holiday IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		$company_obj = $this->getCompanyObject();
		if ( is_object( $company_obj ) ) {
			return CompanyGenericMapFactory::setMapIDs( $company_obj->getId(), 5000, $this->getID(), (array)$ids );
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getAlwaysOnWeekDay() {
		return $this->getGenericDataValue( 'always_week_day_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAlwaysOnWeekDay( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'always_week_day_id', $value );
	}

	/**
	 * @param string $agency_id UUID
	 * @param string $label     type_id, country, province, district, id
	 * @return array|mixed
	 */
	function parseAgencyID( $agency_id = null, $label = null ) {
		if ( $agency_id == null ) {
			$agency_id = $this->getAgency();
		}

		$split_agency_id = explode( ':', $agency_id );
		//Debug::Arr( $split_agency_id, 'Split Agency: ' . $agency_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( is_array( $split_agency_id ) && count( $split_agency_id ) > 1 ) {
			$retarr = [];
			$retarr['type_id'] = (int)$split_agency_id[0];
			$retarr['country'] = $split_agency_id[1];
			$retarr['province'] = $split_agency_id[2];
			$retarr['district'] = $split_agency_id[3];
			$retarr['id'] = (int)$split_agency_id[4];

			if ( $label != null ) {
				return $retarr[$label];
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( is_object( $this->getLegalEntityObject() ) ) {
			$company_id = $this->getLegalEntityObject()->getCompany();
			$legal_entity_id = $this->getLegalEntityObject()->getID();
		} else {
			$company_id = TTUUID::getZeroID();
			$legal_entity_id = TTUUID::getZeroID();
		}

		if ( $name == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		//Only force names to be unique within the same legal entity.
		$ph = [
				'name'            => $name,
				'legal_entity_id' => TTUUID::castUUID( $legal_entity_id ),
				'company_id'      => TTUUID::castUUID( $company_id ),
		];

		$lef = TTnew( 'LegalEntityFactory' ); /** @var LegalEntityFactory $lef */

		$query = 'SELECT a.id
					FROM ' . $this->getTable() . ' as a
					LEFT JOIN ' . $lef->getTable() . ' as lef ON ( a.legal_entity_id = lef.id AND lef.deleted = 0 )
					WHERE a.name = ?
						AND a.legal_entity_id = ?
						AND lef.company_id = ?
						AND a.deleted = 0';

		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value ) {
		Debug::Text( 'Country: ' . $this->getCountry() . ' Province: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getCountry() {
		return $this->getGenericDataValue( 'country' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCountry( $value ) {
		return $this->setGenericDataValue( 'country', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPrimaryIdentification() {
		return $this->getGenericDataValue( 'primary_identification' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimaryIdentification( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'primary_identification', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondaryIdentification() {
		return $this->getGenericDataValue( 'secondary_identification' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondaryIdentification( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'secondary_identification', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTertiaryIdentification() {
		return $this->getGenericDataValue( 'tertiary_identification' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTertiaryIdentification( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'tertiary_identification', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserName() {
		return $this->getGenericDataValue( 'user_anme' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPassword() {
		return $this->getGenericDataValue( 'password' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPassword( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'password', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getContactUser() {
		return $this->getGenericDataValue( 'contact_user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setContactUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'contact_user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDistrict() {
		return $this->getGenericDataValue( 'district' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDistrict( $value ) {
		return $this->setGenericDataValue( 'district', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getStartDate() {
		return $this->getGenericDataValue( 'start_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = trim( $value );
		if ( $value != '' ) {
			$value = TTDate::getBeginDayEpoch( trim( $value ) );
		}
		Debug::Text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getEndDate() {
		return $this->getGenericDataValue( 'end_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = trim( $value );
		if ( $value != '' ) {
			$value = TTDate::getEndDayEpoch( trim( $value ) );
		}
		Debug::Text( 'End Date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Legal entity
		if ( $this->getLegalEntity() !== false ) {
			$llf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $llf */
			$this->Validator->isResultSetWithRows( 'legal_entity_id',
												   $llf->getByID( $this->getLegalEntity() ),
												   TTi18n::gettext( 'Legal entity is invalid' )
			);
		}
		// Remittance source account
		if ( $this->getRemittanceSourceAccount() != false && $this->getRemittanceSourceAccount() != TTUUID::getZeroID() ) {
			$llf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $llf */
			$this->Validator->isResultSetWithRows( 'remittance_source_account_id',
												   $llf->getByID( $this->getRemittanceSourceAccount() ),
												   TTi18n::gettext( 'Remittance source account is invalid' )
			);
		}
		// Status
		if ( $this->getStatus() != '' ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		if ( $this->Validator->getValidateOnly() == false ) {
			// Type
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
			// Agency
			$this->Validator->inArrayKey( 'agency_id',
										  $this->getAgency(),
										  TTi18n::gettext( 'Incorrect Agency' ),
										  $this->getOptions( 'agency', [ 'type_id' => $this->getType(), 'country' => $this->getCountry(), 'province' => $this->getProvince(), 'district' => $this->getDistrict() ] )
			);
		}
		// Name
		if ( $this->getName() !== false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2,
										200
			);
			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name already exists' )
				);
			}
		}

		// Province/State
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

		if ( $this->getCountry() !== false && $this->getProvince() != '' && $this->getProvince() != '00' && $this->Validator->getValidateOnly() == false ) {
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = [];
			}
			//skip validation for type 3rd Party and no provinces exist for country.
			if ( !( isset( $options ) && count( $options ) == 1 && isset( $options['00'] ) ) ) {
				$this->Validator->inArrayKey( 'province',
											  $this->getProvince(),
											  TTi18n::gettext( 'Invalid Province/State' ),
											  $options
				);
			}
			unset( $options_arr, $options );
		}

		// Country
		if ( $this->getCountry() !== false && $this->Validator->getValidateOnly() == false ) {
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Invalid Country' ),
										  $cf->getOptions( 'country' )
			);
		}

		// District
		if ( $this->getDistrict() != '' && $this->getDistrict() != '00' && $this->Validator->getValidateOnly() == false ) {
			$options_arr = $cf->getOptions( 'district' );
			if ( isset( $options_arr[$this->getCountry()][$this->getProvince()] ) ) {
				$options = $options_arr[$this->getCountry()][$this->getProvince()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'district',
										  $this->getDistrict(),
										  TTi18n::gettext( 'Invalid District' ),
										  $options
			);
			unset( $options, $options_arr );
		}

		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is invalid' ),
									0, 255
		);
		// Primary identification
		$this->Validator->isLength( 'primary_identification',
									$this->getPrimaryIdentification(),
									TTi18n::gettext( 'Primary identification is invalid' ),
									0, 255
		);
		// Secondary identification
		$this->Validator->isLength( 'secondary_identification',
									$this->getSecondaryIdentification(),
									TTi18n::gettext( 'Secondary identification is invalid' ),
									0, 255
		);
		// Tertiary identification
		$this->Validator->isLength( 'tertiary_identification',
									$this->getTertiaryIdentification(),
									TTi18n::gettext( 'Tertiary identification is invalid' ),
									0, 255
		);
		// Contact - Allow this to be NONE in cases where creating it during a fresh install when a user may not even exist yet.
		if ( $this->getContactUser() != '' && $this->getContactUser() != TTUUID::getZeroId() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'contact_user_id',
												   $ulf->getByID( $this->getContactUser() ),
												   TTi18n::gettext( 'Invalid Contact' )
			);
		}
		// start date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}
		// End Date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.
		if ( $this->getDeleted() == true ) {
			if ( is_object( $this->getLegalEntityObject() ) ) {
				$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
				$cdlf->getByCompanyIdAndPayrollRemittanceAgencyId( $this->getLegalEntityObject()->getCompany(), $this->getId(), 1 );
				if ( $cdlf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  false,
											  TTi18n::gettext( 'This payroll remittance agency is currently in use by one or more Tax/Deductions' ) );
				}
			}
		}

		//Check to see if there are any full service events.
		$praelf = TTnew( 'PayrollRemittanceAgencyEventListFactory' );
		$praelf->getByLegalEntityIdAndRemittanceAgencyIdAndStatus( $this->getLegalEntity(), $this->getId(), 15 );
		if ( $praelf->getRecordCount() > 0 ) {
			$has_full_service_events = true;
		} else {
			$has_full_service_events = false;
		}
		Debug::Text( '  Has Full Service Events: ' . (int)$has_full_service_events . ' Total Full Service Events: ' . $praelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		unset( $praelf );

		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.
			if ( $this->getName() == false && $this->Validator->hasError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}


			if ( $this->getAgency() == false && $this->Validator->hasError( 'agency_id' ) == false ) {
				$this->Validator->isTrue( 'agency_id',
										  false,
										  TTi18n::gettext( 'Please specify agency' ) );
			}

			if ( $this->getStatus() == false ) {
				$this->Validator->isTrue( 'status_id',
										  false,
										  TTi18n::gettext( 'Please specify status' ) );
			}

			if ( $this->getType() == false ) {
				$this->Validator->isTrue( 'type_id',
										  false,
										  TTi18n::gettext( 'Please specify type' ) );
			}

			if ( $this->getLegalEntity() == false && $this->Validator->hasError( 'legal_entity_id' ) == false ) {
				$this->Validator->isTrue( 'legal_entity_id',
										  false,
										  TTi18n::gettext( 'Please specify legal entity' ) );
			}
			if ( $this->getContactUser() == false && $this->Validator->hasError( 'contact_user_id' ) == false ) {
				$this->Validator->isTrue( 'contact_user_id',
										  false,
										  TTi18n::gettext( 'Please specify a contact' ) );
			}

			//Only if full service events exist must there be a contact person specified. Otherwise impounding won't work.
			if ( $has_full_service_events == true && ( $this->getContactUser() == false || $this->getContactUser() == TTUUID::getZeroID() ) ) {
				$this->Validator->isTrue( 'contact_user_id',
										  false,
										  TTi18n::gettext( 'Contact person must be specified' ) );
			}
		}

		//RemittanceSourceAccount must be optional, as we won't know it during SetupPresets.
		//Change this to a warning instead perhaps?
		if ( $ignore_warning == false ) {
			if ( $this->getRemittanceSourceAccount() == false || $this->getRemittanceSourceAccount() == TTUUID::getZeroID() ) {
				$this->Validator->Warning( 'remittance_source_account_id', TTi18n::gettext( 'It is recommended that a remittance source account be specified' ) );
			}

			if ( $has_full_service_events == false && ( $this->getContactUser() == false || $this->getContactUser() == TTUUID::getZeroID() ) ) {
				$this->Validator->Warning( 'contact_user_id', TTi18n::gettext( 'It is recommended that a contact person is always specified' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableAddEventPreset() {
		if ( isset( $this->add_event_preset ) ) {
			return $this->add_event_preset;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableAddEventPreset( $bool ) {
		$this->add_event_preset = (bool)$bool;

		return false;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->isNew() == true ) {
			$this->is_new = true;
		}

		if ( $this->getProvince() == '' ) {
			$this->setProvince( '00' );
		}
		if ( $this->getDistrict() == '' ) {
			$this->setDistrict( '00' );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( isset( $this->is_new ) && $this->is_new == true && $this->getEnableAddEventPreset() == true ) {
			Debug::Text( '  New Agency, adding Event presets...', __FILE__, __LINE__, __METHOD__, 10 );
			$sp = new SetupPresets();
			$sp->setCompany( $this->getLegalEntityObject()->getCompany() );
			$sp->setUser( $this->getCreatedBy() );
			$sp->createRemittanceAgencyEvents( $this->getId() );
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
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'contact':
							$data[$key] = Misc::getFullName( $this->getColumn( 'first_name' ), null, $this->getColumn( 'last_name' ), false, false );
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
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'type':
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'agency':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable, [ 'type_id' => $this->getType(), 'country' => $this->getCountry(), 'province' => $this->getProvince(), 'district' => $this->getDistrict() ] ) );
							}
							break;
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'contact_user':
							$data[$variable] = Misc::getFullName( $this->getColumn( 'first_name' ), null, $this->getColumn( 'last_name' ), false, false );
							break;
						case 'legal_entity_legal_name':
						case 'remittance_source_account':
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Payroll Remittance Agency' ) . ': ' . $this->getName(), null, $this->getTable(), $this );
	}

}

?>
