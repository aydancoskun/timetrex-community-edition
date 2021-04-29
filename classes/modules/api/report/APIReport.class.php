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
 * @package API\Report
 */
class APIReport extends APIFactory {
	public $report_obj = null;

	/**
	 * APIReport constructor.
	 * @noinspection PhpPossiblePolymorphicInvocationInspection
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		$report_obj = TTNew( $this->main_class ); //Allow plugins to work with reports.
		$report_obj->setUserObject( $this->getCurrentUserObject() );
		$report_obj->setPermissionObject( $this->getPermissionObject() );

		$this->setMainClassObject( $report_obj );

		return true;
	}

	/**
	 * @return null
	 */
	function getReportObject() {
		return $this->getMainClassObject();
	}

	/**
	 * @param bool $name
	 * @return array|bool
	 */
	function getTemplate( $name = false ) {
		return $this->returnHandler( $this->getReportObject()->getTemplate( $name ) );
	}

	/**
	 * @return array|bool
	 */
	function getConfig() {
		return $this->returnHandler( $this->getReportObject()->getConfig() );
	}

	/**
	 * @param bool $data
	 * @return array|bool
	 */
	function setConfig( $data = false ) {
		return $this->returnHandler( $this->getReportObject()->setConfig( $data ) );
	}

	/**
	 * @return array|bool
	 */
	function getOtherConfig() {
		return $this->returnHandler( $this->getReportObject()->getOtherConfig() );
	}

	/**
	 * @return array|bool
	 */
	function getChartConfig() {
		return $this->returnHandler( $this->getReportObject()->getChartConfig() );
	}

	/**
	 * @param bool $data
	 * @return array|bool
	 */
	function setCompanyFormConfig( $data = false ) {
		if ( $this->getReportObject()->checkPermissions() == true ) {
			return $this->returnHandler( $this->getReportObject()->setCompanyFormConfig( $data ) );
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'PERMISSION DENIED' ) );
	}

	/**
	 * @return array|bool
	 */
	function getCompanyFormConfig() {
		if ( $this->getReportObject()->checkPermissions() == true ) {
			return $this->returnHandler( $this->getReportObject()->getCompanyFormConfig() );
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'PERMISSION DENIED' ) );
	}

	/**
	 * @param bool $config
	 * @param string $format
	 * @return array|bool
	 */
	function validateReport( $config = false, $format = 'pdf' ) {
		$this->getReportObject()->setConfig( $config ); //Set config first, so checkPermissions can check/modify data in the config for Printing timesheets for regular employees.
		if ( $this->getReportObject()->checkPermissions() == true ) {
			$validation_obj = $this->getReportObject()->validateConfig( $format );
			if ( $validation_obj->isValid() == false ) {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), [ 0 => $validation_obj->getErrorsArray() ], [ 'total_records' => 1, 'valid_records' => 0 ] );
			}
		} else {
			//Display permission denied error message to user.
			$validator = new Validator();
			$validator->isTrue( 'PERMISSION', false, TTi18n::getText( 'Permission Denied' ) );
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), [ 0 => $validator->getErrorsArray() ], [ 'total_records' => 1, 'valid_records' => 0 ] );

			//return $this->getPermissionObject()->PermissionDenied(); //This won't display anything to the end-user, which should probably be fixed and used instead when we have more time.
		}

		return $this->returnHandler( true );
	}

	/**
	 * Use JSON API to download PDF files.
	 * @param bool $config
	 * @param string $format
	 * @return array|bool
	 */
	function getReport( $config = false, $format = 'pdf' ) {
		if ( is_string( $format ) == false || $format == '' ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Format is invalid' ) );
		}

		if ( Misc::isSystemLoadValid() == false ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Please try again later...' ) );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( is_object( $this->getReportObject()->getUserObject() ) && $this->getReportObject()->getUserObject()->getStatus() != 10 ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to view reports' ) );
		}

		$format = Misc::trimSortPrefix( $format );
		Debug::Text( 'Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		$this->getReportObject()->setConfig( $config ); //Set config first, so checkPermissions can check/modify data in the config for Printing timesheets for regular employees.
		if ( $this->getReportObject()->checkPermissions() == true ) {
			$this->getReportObject()->setAPIMessageID( $this->getAPIMessageID() ); //This must be set *after* the all constructor functions are called, as its primarily called from JSON.

			$validation_obj = $this->getReportObject()->validateConfig( $format );
			if ( $validation_obj->isValid() == true ) {
				//return Misc::APIFileDownload( 'report.pdf', 'application/pdf', $this->getReportObject()->getOutput( $format ) );
				$output_arr = $this->getReportObject()->getOutput( $format );

				if ( isset( $output_arr['file_name'] ) && isset( $output_arr['mime_type'] ) && isset( $output_arr['data'] ) ) {
					//If using the SOAP API, return data base64 encoded so it can be decoded on the client side.
					if ( defined( 'TIMETREX_SOAP_API' ) && TIMETREX_SOAP_API == true ) {
						$output_arr['data'] = base64_encode( $output_arr['data'] );

						return $this->returnHandler( $output_arr );
					} else {
						if ( $output_arr['mime_type'] === 'text/html' ) {
							return $this->returnHandler( $output_arr['data'] );
						} else {
							Misc::APIFileDownload( $output_arr['file_name'], $output_arr['mime_type'], $output_arr['data'] );

							return null; //Don't send any additional data, so JSON encoding doesn't corrupt the download.
						}
					}
				} else if ( isset( $output_arr['api_retval'] ) ) { //Pass through validation errors.
					Debug::Text( 'Report returned VALIDATION error, passing through...', __FILE__, __LINE__, __METHOD__, 10 );

					return $this->returnHandler( $output_arr['api_retval'], $output_arr['api_details']['code'], $output_arr['api_details']['description'] );
					//return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('Please try again later...') );
				} else if ( $output_arr !== false ) {
					//Likely RAW data, return untouched.
					return $this->returnHandler( $output_arr );
				} else {
					//getOutput() returned FALSE, some error occurred. Likely load too high though.
					//return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('Error generating report...') );
					return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: Report is too large, please try again later or narrow your search criteria to decrease the size of your report' ) . '...' );
				}
			} else {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), [ 0 => $validation_obj->getErrorsArray() ], [ 'total_records' => 1, 'valid_records' => 0 ] );
			}
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'PERMISSION DENIED' ) );
	}
}

?>
