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
 * @package Core
 */
class CurrencyRateFactory extends Factory {
	protected $table = 'currency_rate';
	protected $pk_sequence_name = 'currency_rate_id_seq'; //PK Sequence name

	protected $currency_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										//'-1010-iso_code' => TTi18n::gettext('ISO Code'),
										'-1020-date_stamp' => TTi18n::gettext('Date'),
										'-1030-conversion_rate' => TTi18n::gettext('Conversion Rate'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp', 'conversion_rate'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'date_stamp',
								'conversion_rate',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'date_stamp',
								);
				break;

		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'currency_id' => 'Currency',
										//'status_id' => FALSE,
										//'status' => FALSE,
										//'name' => FALSE,
										//'symbol' => FALSE,
										//'iso_code' => FALSE,
										'date_stamp' => 'DateStamp',
										'conversion_rate' => 'ConversionRate',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrency( $value) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Currency ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	/**
	 * @return bool
	 */
	function isUnique() {
		$ph = array(
					'currency_id' => TTUUID::castUUID( $this->getCurrency() ),
					'date_stamp' => $this->db->BindDate( $this->getDateStamp() ),
					);

		$query = 'select id from '. $this->getTable() .' where currency_id = ? AND date_stamp = ?';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Currency Rate: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	function getReverseConversionRate() {
		$rate = $this->getConversionRate();
		if ( $rate != 0 ) { //Prevent division by 0.
			return bcdiv( 1, $rate );
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getConversionRate() {
		return $this->getGenericDataValue( 'conversion_rate' );//Don't cast to (float) as it may strip some precision.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConversionRate( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		return $this->setGenericDataValue( 'conversion_rate', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Currency
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't do the follow validation checks during Mass Edit.
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}

		// Date
		if ( $this->Validator->getValidateOnly() == FALSE AND $this->getDateStamp() != FALSE ) {
			$this->Validator->isDate(		'date_stamp',
													$this->getDateStamp(),
													TTi18n::gettext('Incorrect date')
												);
		}

		// Conversion rate
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't do the follow validation checks during Mass Edit.
			$this->Validator->isTrue( 'conversion_rate',
									  $this->getConversionRate(),
									  TTi18n::gettext( 'Conversion rate not specified' )
			);
		}

		if ( $this->getConversionRate() !== FALSE ) {
			if ( $this->Validator->isError( 'conversion_rate' ) == FALSE ) {
				$this->Validator->isFloat( 'conversion_rate',
										   $this->getConversionRate(),
										   TTi18n::gettext( 'Incorrect Conversion Rate' )
				);
			}
			if ( $this->Validator->isError( 'conversion_rate' ) == FALSE ) {
				$this->Validator->isLessThan( 'conversion_rate',
											  $this->getConversionRate(),
											  TTi18n::gettext( 'Conversion Rate is too high' ),
											  99999999
				);
			}
			if ( $this->Validator->isError( 'conversion_rate' ) == FALSE ) {
				$this->Validator->isGreaterThan( 'conversion_rate',
												 $this->getConversionRate(),
												 TTi18n::gettext( 'Conversion Rate is too low' ),
												 -99999999
				);
			}
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == FALSE ) {
			if ( $this->Validator->isError('date_stamp') == FALSE ) {
				if ( $this->Validator->getValidateOnly() == FALSE AND $this->getDateStamp() == FALSE ) {
					$this->Validator->isTrue( 'date_stamp',
											  FALSE,
											  TTi18n::gettext( 'Date not specified' ) );
				} else {
					if ( $this->Validator->getValidateOnly() == FALSE AND $this->isUnique() == FALSE ) {
						$this->Validator->isTrue( 'date_stamp',
												  FALSE,
												  TTi18n::gettext( 'Currency rate already exists for this date' ) );
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();

			foreach( $variable_function_map as $key => $function ) {

				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
//						case 'conversion_rate':
//							$this->$function( TTi18n::parseFloat( $data[$key] ) );
//							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		/*
		$include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'date_stamp':
							$data[$variable] = $this->$function( TRUE );
							break;
//						case 'conversion_rate':
//							$data[$variable] = TTi18n::formatNumber( $this->$function(), TRUE, 10, 10 );
//							break;
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Currency Rate').': '. $this->getCurrencyObject()->getISOCode() .' '.  TTi18n::getText('Rate').': '. $this->getConversionRate(), NULL, $this->getTable(), $this );
	}

}
?>
