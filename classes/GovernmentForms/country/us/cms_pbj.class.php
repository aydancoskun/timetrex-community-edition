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


include_once( 'US.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_CMS_PBJ extends GovernmentForms_US {
	public $xml_schema = 'CMS/PBJ/nhpbj_4_00_0.xsd';

	public function getTemplateSchema( $name = null ) {
		$template_schema = [];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	public static function getFederalYearQuarterMonth( $epoch = null ) {
		$year_quarter_months = [
				1  => 2,
				2  => 2,
				3  => 2,
				4  => 3,
				5  => 3,
				6  => 3,
				7  => 4,
				8  => 4,
				9  => 4,
				10 => 1,
				11 => 1,
				12 => 1,
		];

		$month = TTDate::getMonth( $epoch );

		if ( isset( $year_quarter_months[$month] ) ) {
			return $year_quarter_months[$month];
		}

		return false;
	}


	function _outputXML( $type = null ) {
		$xml = new SimpleXMLElement( '<nursingHomeData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="nhpbj_4_00_0.xsd"></nursingHomeData>' );
		$this->setXMLObject( $xml );

		$xml->addChild( 'header' );
		$xml->header->addAttribute( 'fileSpecVersion', '4.00.0' );

		$xml->header->addChild( 'facilityId', $this->facility_code );
		$xml->header->addChild( 'stateCode', $this->state_code );
		$xml->header->addChild( 'reportQuarter', $this->getFederalYearQuarterMonth( $this->date ) );
		$xml->header->addChild( 'federalFiscalYear', TTDate::getFiscalYearFromEpoch( $this->date, 'US' ) );

		$xml->header->addChild( 'softwareVendorName', APPLICATION_NAME );
		$xml->header->addChild( 'softwareVendorEmail', 'support@timetrex.com' );
		$xml->header->addChild( 'softwareProductName', APPLICATION_NAME );
		$xml->header->addChild( 'softwareProductVersion', APPLICATION_VERSION );

		$xml->addChild( 'employees' );
		$xml->addChild( 'staffingHours' );
		$xml->staffingHours->addAttribute( 'processType', 'replace' );

		$records = $this->getRecords();
		if ( is_array( $records ) && count( $records ) > 0 ) {
			//Process records into Employee -> Date -> Hour Entries
			$tmp_rows = [];
			foreach ( $records as $record ) {
				$tmp_rows[$record['employee_number']][$record['date_stamp']][$record['pbj_job_title_code']][] = $record;
			}
			unset( $records );

			if ( isset( $tmp_rows ) ) {
				$e = 0;
				foreach ( $tmp_rows as $key => $date_data ) {
					$xml->employees->addChild( 'employee' );
					$xml->staffingHours->addChild( 'staffHours' );

					$d = 0;
					foreach ( $date_data as $date_stamp => $hour_data ) {
						$h = 0;
						foreach ( $hour_data as $title_code => $title_data ) {
							foreach ( $title_data as $data ) {
								$this->arrayToObject( $data ); //Convert record array to object

								if ( $d == 0 && $h == 0 ) {
									$xml->employees->employee[$e]->addChild( 'employeeId', $this->employee_number );
									$xml->employees->employee[$e]->addChild( 'hireDate', date( 'Y-m-d', $this->{'hire-date_stamp'} ) );
									if ( $this->{'termination-date_stamp'} != '' ) {
										$xml->employees->employee[$e]->addChild( 'terminationDate', date( 'Y-m-d', $this->{'termination-date_stamp'} ) );
									}

									$xml->staffingHours->staffHours[$e]->addChild( 'employeeId', $this->employee_number );
									$xml->staffingHours->staffHours[$e]->addChild( 'workDays' );
								}

								if ( $h == 0 ) {
									$xml->staffingHours->staffHours[$e]->workDays->addChild( 'workDay' );
									$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->addChild( 'date', date( 'Y-m-d', $date_stamp ) );
									$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->addChild( 'hourEntries' );
								}

								$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->hourEntries->addChild( 'hourEntry' );
								$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->hourEntries->hourEntry[$h]->addChild( 'hours', round( TTDate::getHours( $this->pbj_hours ), 2 ) );
								/*
								1=Administrator
								2=Medical Director
								3=Other Physician
								4=Physician Assistant
								5=Registered Nurse Director of Nursing
								6=Registered Nurse with Administrative Duties
								7=Registered Nurse
								8=Licensed Practical/Vocational Nurse with Administrative Duties
								9=Licensed Practical/Vocational Nurse
								10=Certified Nurse Aide
								11=Nurse Aide in Training
								12=Medication Aide/Technician
								13=Nurse Practitioner
								14=Clinical Nurse Specialist
								15=Pharmacist
								16=Dietitian
								17=Feeding Assistant
								18=Occupational Therapist
								19=Occupational Therapy Assistant
								20=Occupational Therapy Aide
								21=Physical Therapist
								22=Physical Therapy Assistant
								23=Physical Therapy Aide
								24=Respiratory Therapist
								25=Respiratory Therapy Technician
								26=Speech/Language Pathologist
								27=Therapeutic Recreation Specialist
								28=Qualified Activities Professional
								29=Other Activities Staff
								30=Qualified Social Worker
								31=Other Social Worker
								32=Dentist
								33=Podiatrist
								34=Mental Health Service Worker
								35=Vocational Service Worker
								36=Clinical Laboratory Service Worker
								37=Diagnostic X-ray Service Worker
								38=Blood Service Worker (optional)
								39=Housekeeping Service Worker (opt
								40=Other Service Worker (optional)
								*/
								$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->hourEntries->hourEntry[$h]->addChild( 'jobTitleCode', (int)$this->pbj_job_title_code );

								/*
								1=Exempt
								2=Non-Exempt
								3=Contract
								*/
								//Default to Non-Exempt as that is most common.
								$xml->staffingHours->staffHours[$e]->workDays->workDay[$d]->hourEntries->hourEntry[$h]->addChild( 'payTypeCode', ( (int)$this->pbj_pay_type_code == 0 ) ? 2 : (int)$this->pbj_pay_type_code );

								$this->revertToOriginalDataState();

								$h++;
							}
						}
						$d++;
					}
					$e++;
				}
			}
		}

		return true;
	}

	function _outputPDF( $type ) {
		return false;
	}
}

?>