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
 * @package Modules\Company
 */
class IdempotentRequestFactory extends Factory {
	protected $table = 'idempotent_request';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'PENDING' ),
						20 => TTi18n::gettext( 'COMPLETE' ),
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
				'id'             => 'ID',
				'status_id'      => 'Status',
				'status'         => false,
				'request_date'   => 'RequestDate',
				'request_method' => 'RequestMethod',
				'request_body'   => 'RequestBody',
				'request_uri'    => 'RequestURI',
				'response_code'  => 'ResponseCode',
				'response_body'  => 'ResponseBody',
				'response_date'  => 'ResponseDate',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|mixed
	 */
	function getIdempotentKey() {
		return $this->getGenericDataValue( 'idempotent_key' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIdempotentKey( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'idempotent_key', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
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
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}


	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getRequestDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'request_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRequestDate( $value ) {
		return $this->setGenericDataValue( 'request_date', TTDate::getISOTimeStampWithMilliseconds( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getRequestMethod() {
		return $this->getGenericDataValue( 'request_method' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRequestMethod( $value ) {
		return $this->setGenericDataValue( 'request_method', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getRequestBody() {
		return $this->getGenericDataValue( 'request_body' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRequestBody( $value ) {
		return $this->setGenericDataValue( 'request_body', json_encode( $value ) );
	}


	/**
	 * @return bool|mixed
	 */
	function getRequestURI() {
		return $this->getGenericDataValue( 'request_uri' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRequestURI( $value ) {
		return $this->setGenericDataValue( 'request_uri', trim( $value ) );
	}



	/**
	 * @return bool|mixed
	 */
	function getResponseCode() {
		return $this->getGenericDataValue( 'response_code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setResponseCode( $value ) {
		return $this->setGenericDataValue( 'response_code', trim( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getResponseBody() {
		return $this->getGenericDataValue( 'response_body' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setResponseBody( $value ) {
		return $this->setGenericDataValue( 'response_body', json_encode( $value ) );
	}

	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getResponseDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'response_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setResponseDate( $value ) {
		return $this->setGenericDataValue( 'response_date', TTDate::getISOTimeStampWithMilliseconds( $value ) );
	}


	/**
	 * @param $value
	 * @return bool
	 */
	function setIsExists( $value ) {
		return $this->setGenericTempDataValue( 'is_exists', (bool)$value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getIsExists() {
		return (bool)$this->getGenericTempDataValue( 'is_exists' );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}



		$this->Validator->isLength( 'request_body',
									$this->getGenericDataValue( 'request_body'), //JSON encoded body.
									TTi18n::gettext( 'Request body is too long' ),
									0,
									5000000
		);

		$this->Validator->isLength( 'response_body',
									$this->getGenericDataValue( 'request_body'), //JSON encoded body.
									TTi18n::gettext( 'Response body is too long' ),
									0,
									5000000
		);

		return true;
	}


	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == false ) {
			$this->setStatus( 10 );
		}

		return true;
	}

	function modifyInsertQuery( $query ) {
		$query .= ' ON CONFLICT (idempotent_key) DO UPDATE SET request_method = EXCLUDED.request_method RETURNING id, status_id';
		return $query;
	}

	function handleSaveSQLReturning( $rs ) {
		if ( $rs->RecordCount() > 0 ) {
			foreach ( $rs as $row ) {
				if ( isset( $row['status_id'] ) ) {
					$this->setStatus( (int)$row['status_id'] );

					//If the ID of the current record match the ID from the INSERT RETURNING clause, then we know we just inserted that record and its not in conflict.
					if ( (string)$row['id'] != $this->getId() ) { //10=Running
						$this->setIsExists( true );
					}
				}
			}
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
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			//$this->setCreatedAndUpdatedColumns( $data );

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
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}
}

?>