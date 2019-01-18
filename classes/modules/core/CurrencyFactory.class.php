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
class CurrencyFactory extends Factory {
	protected $table = 'currency';
	protected $pk_sequence_name = 'currency_id_seq'; //PK Sequence name

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('ENABLED'),
										20 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'country_currency':
				//Country to primary currency mappings.
				$retval = array(
										'AF' => 'AFA',
										'AL' => 'ALL',
										'DZ' => 'DZD',
										'AS' => 'USD',
										'AD' => 'EUR',
										'AO' => 'AON',
										'AI' => 'XCD',
										'AQ' => 'NOK',
										'AG' => 'XCD',
										'AR' => 'ARA',
										'AM' => 'AMD',
										'AW' => 'AWG',
										'AU' => 'AUD',
										'AT' => 'EUR',
										'AZ' => 'AZM',
										'BS' => 'BSD',
										'BH' => 'BHD',
										'BD' => 'BDT',
										'BB' => 'BBD',
										'BY' => 'BYR',
										'BE' => 'EUR',
										'BZ' => 'BZD',
										'BJ' => 'XAF',
										'BM' => 'BMD',
										'BT' => 'BTN',
										'BO' => 'BOB',
										'BA' => 'BAM',
										'BW' => 'BWP',
										'BV' => 'NOK',
										'BR' => 'BRR',
										'IO' => 'GBP',
										'BN' => 'BND',
										'BG' => 'BGL',
										'BF' => 'XAF',
										'BI' => 'BIF',
										'KH' => 'KHR',
										'CM' => 'XAF',
										'CA' => 'CAD',
										'CV' => 'CVE',
										'KY' => 'KYD',
										'CF' => 'XAF',
										'TD' => 'XAF',
										'CL' => 'CLF',
										'CN' => 'CNY',
										'CX' => 'AUD',
										'CC' => 'AUD',
										'CO' => 'COP',
										'KM' => 'KMF',
										//'CD' => 'CDZ', //Not available in PEAR.
										'CG' => 'XAF',
										'CK' => 'NZD',
										'CR' => 'CRC',
										'HR' => 'HRK',
										'CU' => 'CUP',
										'CY' => 'CYP',
										'CZ' => 'CZK',
										'DK' => 'DKK',
										'DJ' => 'DJF',
										'DM' => 'XCD',
										'DO' => 'DOP',
										'TP' => 'TPE',
										'EC' => 'USD',
										'EG' => 'EGP',
										'SV' => 'SVC',
										'GQ' => 'XAF',
										'ER' => 'ERN',
										'EE' => 'EEK',
										'ET' => 'ETB',
										'FK' => 'FKP',
										'FO' => 'DKK',
										'FJ' => 'FJD',
										'FI' => 'EUR',
										'FR' => 'EUR',
										'FX' => 'EUR',
										'GF' => 'EUR',
										'PF' => 'XPF',
										'TF' => 'EUR',
										'GA' => 'XAF',
										'GM' => 'GMD',
										'GE' => 'GEL',
										'DE' => 'EUR',
										'GH' => 'GHC',
										'GI' => 'GIP',
										'GR' => 'EUR',
										'GL' => 'DKK',
										'GD' => 'XCD',
										'GP' => 'EUR',
										'GU' => 'USD',
										'GT' => 'GTQ',
										'GN' => 'GNS',
										'GW' => 'GWP',
										'GY' => 'GYD',
										'HT' => 'HTG',
										'HM' => 'AUD',
										'VA' => 'EUR',
										'HN' => 'HNL',
										'HK' => 'HKD',
										'HU' => 'HUF',
										'IS' => 'ISK',
										'IN' => 'INR',
										'ID' => 'IDR',
										'IR' => 'IRR',
										'IQ' => 'IQD',
										'IE' => 'EUR',
										'IL' => 'ILS',
										'IT' => 'EUR',
										'CI' => 'XAF',
										'JM' => 'JMD',
										'JP' => 'JPY',
										'JO' => 'JOD',
										'KZ' => 'KZT',
										'KE' => 'KES',
										'KI' => 'AUD',
										'KP' => 'KPW',
										'KR' => 'KRW',
										'KW' => 'KWD',
										'KG' => 'KGS',
										'LA' => 'LAK',
										'LV' => 'LVL',
										'LB' => 'LBP',
										'LS' => 'LSL',
										'LR' => 'LRD',
										'LY' => 'LYD',
										'LI' => 'CHF',
										'LT' => 'LTL',
										'LU' => 'EUR',
										'MO' => 'MOP',
										'MK' => 'MKD',
										'MG' => 'MGF',
										'MW' => 'MWK',
										'MY' => 'MYR',
										'MV' => 'MVR',
										'ML' => 'XAF',
										'MT' => 'MTL',
										'MH' => 'USD',
										'MQ' => 'EUR',
										'MR' => 'MRO',
										'MU' => 'MUR',
										'YT' => 'EUR',
										'MX' => 'MXN',
										'FM' => 'USD',
										'MD' => 'MDL',
										'MC' => 'EUR',
										'MN' => 'MNT',
										'MS' => 'XCD',
										'MA' => 'MAD',
										'MZ' => 'MZM',
										'MM' => 'MMK',
										'NA' => 'NAD',
										'NR' => 'AUD',
										'NP' => 'NPR',
										'NL' => 'EUR',
										'AN' => 'ANG',
										'NC' => 'XPF',
										'NZ' => 'NZD',
										'NI' => 'NIC',
										'NE' => 'XOF',
										'NG' => 'NGN',
										'NU' => 'NZD',
										'NF' => 'AUD',
										'MP' => 'USD',
										'NO' => 'NOK',
										'OM' => 'OMR',
										'PK' => 'PKR',
										'PW' => 'USD',
										'PA' => 'PAB',
										'PG' => 'PGK',
										'PY' => 'PYG',
										'PE' => 'PEI',
										'PH' => 'PHP',
										'PN' => 'NZD',
										'PL' => 'PLN',
										'PT' => 'EUR',
										'PR' => 'USD',
										'QA' => 'QAR',
										'RE' => 'EUR',
										'RO' => 'ROL',
										'RU' => 'RUB',
										'RW' => 'RWF',
										'KN' => 'XCD',
										'LC' => 'XCD',
										'VC' => 'XCD',
										'WS' => 'WST',
										'SM' => 'EUR',
										'ST' => 'STD',
										'SA' => 'SAR',
										'SN' => 'XOF',
										'CS' => 'CSD',
										'SC' => 'SCR',
										'SL' => 'SLL',
										'SG' => 'SGD',
										'SK' => 'SKK',
										'SI' => 'SIT',
										'SB' => 'SBD',
										'SO' => 'SOS',
										'ZA' => 'ZAR',
										'GS' => 'GBP',
										'ES' => 'EUR',
										'LK' => 'LKR',
										'SH' => 'SHP',
										'PM' => 'EUR',
										'SD' => 'SDP',
										'SR' => 'SRG',
										'SJ' => 'NOK',
										'SZ' => 'SZL',
										'SE' => 'SEK',
										'CH' => 'CHF',
										'SY' => 'SYP',
										'TW' => 'TWD',
										'TJ' => 'TJR',
										'TZ' => 'TZS',
										'TH' => 'THB',
										'TG' => 'XAF',
										'TK' => 'NZD',
										'TO' => 'TOP',
										'TT' => 'TTD',
										'TN' => 'TND',
										'TR' => 'TRL',
										'TM' => 'TMM',
										'TC' => 'USD',
										'TV' => 'AUD',
										'UG' => 'UGS',
										'UA' => 'UAH',
										'SU' => 'SUR',
										'AE' => 'AED',
										'GB' => 'GBP',
										'US' => 'USD',
										'UM' => 'USD',
										'UY' => 'UYU',
										'UZ' => 'UZS',
										'VU' => 'VUV',
										'VE' => 'VEB',
										'VN' => 'VND',
										'VG' => 'USD',
										'VI' => 'USD',
										'WF' => 'XPF',
										'XO' => 'XOF',
										'EH' => 'MAD',
										'YE' => 'YER',
										//'ZM' => 'ZMK', //Switched to ZMW in Aug 2012.
										'ZM' => 'ZMW',
										'ZW' => 'ZWD',
									);
				break;
			case 'round_decimal_places':
				$retval = array(
										0 => 0,
										1 => 1,
										2 => 2,
										3 => 3,
										4 => 4,
							);
				break;
			case 'columns':
				$retval = array(
										'-1000-status' => TTi18n::gettext('Status'),
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-symbol' => TTi18n::gettext('Symbol'),
										'-1020-iso_code' => TTi18n::gettext('ISO Code'),
										'-1030-conversion_rate' => TTi18n::gettext('Conversion Rate'),
										'-1040-auto_update' => TTi18n::gettext('Auto Update'),
										'-1050-actual_rate' => TTi18n::gettext('Actual Rate'),
										'-1060-actual_rate_updated_date' => TTi18n::gettext('Last Downloaded Date'),
										'-1070-rate_modify_percent' => TTi18n::gettext('Rate Modify Percent'),
										'-1080-is_default' => TTi18n::gettext('Default Currency'),
										'-1090-is_base' => TTi18n::gettext('Base Currency'),
										'-1100-round_decimal_places' => TTi18n::gettext('Round Decimal Places'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('name', 'iso_code', 'symbol', 'status'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'name',
								'iso_code',
								'conversion_rate',
								'is_default',
								'is_base',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'is_default',
								'is_base',
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
										'company_id' => 'Company',
										'status_id' => 'Status',
										'status' => FALSE,
										'name' => 'Name',
										'symbol' => FALSE,
										'iso_code' => 'ISOCode',
										'conversion_rate' => 'ConversionRate',
										'auto_update' => 'AutoUpdate',
										'actual_rate' => 'ActualRate',
										'actual_rate_updated_date' => 'ActualRateUpdatedDate',
										'rate_modify_percent' => 'RateModifyPercent',
										'is_default' => 'Default',
										'is_base' => 'Base',
										'round_decimal_places' => 'RoundDecimalPlaces',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return mixed
	 */
	static function getISOCodesArray() {
		return TTi18n::getCurrencyArray();
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND lower(name) = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
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
	function setName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getISOCode() {
		return $this->getGenericDataValue( 'iso_code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setISOCode( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'iso_code', $value );
	}

	/**
	 * @return string
	 */
	function getReverseConversionRate() {
		return bcdiv( 1, $this->getConversionRate() );
	}

	/**
	 * @return bool
	 */
	function getConversionRate() {
		return $this->getGenericDataValue( 'conversion_rate' ); //Don't cast to (float) as it may strip some precision.
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
	 * @return bool
	 */
	function getAutoUpdate() {
		return $this->fromBool( $this->getGenericDataValue( 'auto_update' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAutoUpdate( $value) {
		return $this->setGenericDataValue( 'auto_update', $this->toBool($value) );
	}

	/**
	 * @return bool|mixed
	 */
	function getActualRate() {
		return $this->getGenericDataValue( 'actual_rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setActualRate( $value ) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		//Ignore any boolean values passed in to this function.
		if ( is_numeric( $value ) ) {
			return $this->setGenericDataValue( 'actual_rate', $value );
		}
		return FALSE;
	}

	/**
	 * @return bool|int
	 */
	function getActualRateUpdatedDate() {
		return $this->getGenericDataValue( 'actual_rate_updated_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setActualRateUpdatedDate( $value = NULL) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		if ($value == NULL) {
			$value = TTDate::getTime();
		}
		return $this->setGenericDataValue( 'actual_rate_updated_date', $value );
	}

	/**
	 * @param $rate
	 * @return string
	 */
	function getPercentModifiedRate( $rate ) {
		if ( $this->getRateModifyPercent() == 0 ) {
			$percent = 1;
		} else {
			$percent = $this->getRateModifyPercent();
		}
		return bcmul( $rate, $percent );
	}

	/**
	 * @return bool|mixed
	 */
	function getRateModifyPercent() {
		return $this->getGenericDataValue( 'rate_modify_percent' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRateModifyPercent( $value ) {
		$value = (float)trim($value);
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		return $this->setGenericDataValue( 'rate_modify_percent', $value );
	}

	/**
	 * @return bool
	 */
	function isUniqueDefault() {
		$ph = array(
					'company_id' => $this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND is_default = 1 AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Currency Default: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
	 * @return bool
	 */
	function getDefault() {
		return $this->fromBool( $this->getGenericDataValue( 'is_default' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefault( $value) {
		return $this->setGenericDataValue( 'is_default', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function isUniqueBase() {
		$ph = array(
					'company_id' => $this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND is_base = 1 AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Currency Base: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
	 * @return bool
	 */
	function getBase() {
		return $this->fromBool( $this->getGenericDataValue( 'is_base' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBase( $value) {
		return $this->setGenericDataValue( 'is_base', $this->toBool($value)  );
	}

	/**
	 * @return mixed|string
	 */
	function getSymbol() {
		return TTi18n::getCurrencySymbol( $this->getISOCode() );
	}

	/**
	 * @return int
	 */
	function getRoundDecimalPlaces() {
		$value = $this->getGenericDataValue( 'round_decimal_places' );
		if ( $value !== FALSE ) {
			return $value;
		}

		return 2;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRoundDecimalPlaces( $value ) {
		if ( version_compare( APPLICATION_VERSION, 5.5, '>' ) ) {
			$value = trim($value);
			return $this->setGenericDataValue( 'round_decimal_places', $value );
		}
		return FALSE;
	}

	/**
	 * @param $value
	 * @return string
	 */
	function round( $value ) {
		//This needs to be number_format, as round strips trailing 0's.
		return number_format( (float)$value, (int)$this->getRoundDecimalPlaces(), '.', '');
		//return round( (float)$value, (int)$this->getRoundDecimalPlaces() );
	}

	/**
	 * @param $src_rate
	 * @param $dst_rate
	 * @return int|string
	 */
	static function getCrossConversionRate( $src_rate, $dst_rate ) {
		if ( $src_rate == $dst_rate ) {
			$retval = 1;
		} else {
			$retval = bcmul( bcdiv(1, $src_rate ), $dst_rate );
		}

		return $retval;
	}

	/**
	 * @param $src_rate
	 * @param $dst_rate
	 * @param $amount
	 * @param int $round_decimal_places
	 * @return float
	 */
	static function convert( $src_rate, $dst_rate, $amount, $round_decimal_places = 2 ) {
		$base_amount = bcmul( bcdiv(1, $src_rate), $amount );
		$retval = round( bcmul( $dst_rate, $base_amount ), (int)$round_decimal_places );

		return $retval;
	}

	/**
	 * @param string $src_currency_id UUID
	 * @param string $dst_currency_id UUID
	 * @param int $amount
	 * @return bool|float|int
	 */
	static function convertCurrency( $src_currency_id, $dst_currency_id, $amount = 1 ) {
		//Debug::Text('Source Currency: '. $src_currency_id .' Destination Currency: '. $dst_currency_id .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

		if ( $src_currency_id == '' ) {
			return FALSE;
		}

		if ( $dst_currency_id == '' ) {
			return FALSE;
		}

		if ( $amount == '' ) {
			return FALSE;
		}

		if ( $src_currency_id == $dst_currency_id ) {
			return $amount;
		}

		$clf = TTnew( 'CurrencyListFactory' );
		$clf->getById( $src_currency_id );
		if ( $clf->getRecordCount() > 0 ) {
			$src_currency_obj = $clf->getCurrent();
		} else {
			Debug::Text('Source currency does not exist.', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$clf->getById( $dst_currency_id );
		if ( $clf->getRecordCount() > 0 ) {
			$dst_currency_obj = $clf->getCurrent();
		} else {
			Debug::Text('Destination currency does not exist.', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( is_object( $src_currency_obj ) AND is_object( $dst_currency_obj ) ) {
			return self::Convert( $src_currency_obj->getConversionRate(), $dst_currency_obj->getConversionRate(), $amount, $dst_currency_obj->getRoundDecimalPlaces() );
		}

		return FALSE;
	}

	/**
	 * @param $amount
	 * @param $rate
	 * @param bool $convert
	 * @return string
	 */
	function getBaseCurrencyAmount( $amount, $rate, $convert = TRUE ) {
		if ( $convert == TRUE AND $rate !== 1 ) { //Don't bother converting if rate is 1.
			$amount = bcmul( $rate, $amount );
		}

		return $this->round( $amount );
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	static function updateCurrencyRates( $company_id ) {
		/*

			Contact info@timetrex.com to request adding custom currency data feeds.

		*/
		$base_currency = FALSE;

		Debug::Text('Begin updating Currencies...', __FILE__, __LINE__, __METHOD__, 10);

		$clf = TTnew( 'CurrencyListFactory' );
		$clf->getByCompanyId( $company_id );
		$manual_currencies = array();
		$active_currencies = array();
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj) {
				if ( $c_obj->getBase() == TRUE ) {
					$base_currency = $c_obj->getISOCode();

					$manual_currencies[$c_obj->getID()] = $c_obj->getISOCode(); //Make base currency manually updated too.
				} else {
					if ( $c_obj->getStatus() == 10 AND $c_obj->getAutoUpdate() == TRUE ) {
						$active_currencies[$c_obj->getID()] = $c_obj->getISOCode();
					} elseif ( $c_obj->getStatus() == 10 AND $c_obj->getAutoUpdate() == FALSE ) {
						$manual_currencies[$c_obj->getID()] = $c_obj->getISOCode();
					}
				}
			}
		}
		unset($clf, $c_obj);

		$ttsc = new TimeTrexSoapClient();

		//Fill in any gaps or missing rates prior to today.
		//Get the earliest pay period date as the absolute earliest to get rates from.
		//Loop through each currency and get the latest date.
		//Download rates to fill in the gaps, if rates returned by server don't fill all gaps, manually fill them ourselves.
		if ( empty($active_currencies) == FALSE OR empty($manual_currencies) == FALSE ) {
			$earliest_pay_period_start_date = time();
			$pplf = TTNew('PayPeriodListFactory');
			$pplf->getByCompanyId($company_id, 1, NULL, NULL, array( 'start_date' => 'asc' ) );
			if ( $pplf->getRecordCount() > 0 ) {
				$earliest_pay_period_start_date = $pplf->getCurrent()->getStartDate();
			}
			unset($pplf);
			Debug::Text('  Earliest Pay Period Date: '. TTDate::getDATE('DATE', $earliest_pay_period_start_date) .'('. $earliest_pay_period_start_date .')', __FILE__, __LINE__, __METHOD__, 10);


			$crlf = TTNew('CurrencyRateListFactory');
			if ( empty($active_currencies) == FALSE ) {
				Debug::Text('  Processing Auto-Update Currencies... Total: '. count($active_currencies), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $active_currencies as $active_currency_id => $active_currency_iso_code ) {
					$crlf->getByCurrencyId( $active_currency_id, 1, NULL, NULL, array('date_stamp' => 'desc' ) );
					if ( $crlf->getRecordCount() > 0 ) {
						$latest_currency_rate_date = $crlf->getCurrent()->getDateStamp();
					} else {
						$latest_currency_rate_date = $earliest_pay_period_start_date;
					}
					Debug::Text('  Latest Currency Rate Date: '. TTDate::getDATE('DATE', $latest_currency_rate_date ) .'('.$latest_currency_rate_date.') Currency ISO: '. $active_currency_iso_code, __FILE__, __LINE__, __METHOD__, 10);

					if ( ( TTDate::getMiddleDayEpoch( time() ) - TTDate::getMiddleDayEpoch( $latest_currency_rate_date ) ) > 86400 ) {
						$currency_rates = $ttsc->getCurrencyExchangeRatesByDate( $company_id, array( $active_currency_iso_code ), $base_currency, $latest_currency_rate_date, ( TTDate::getMiddleDayEpoch( time() ) - 86400 ) );
						Debug::Text('Currency Rates for: '. $active_currency_iso_code .' Total: '. count($currency_rates), __FILE__, __LINE__, __METHOD__, 10);
						//Debug::Arr($currency_rates, 'Currency Rates for: '. $active_currency_iso_code, __FILE__, __LINE__, __METHOD__, 10);

						if ( is_array($currency_rates[$active_currency_iso_code]) ) {
							foreach( $currency_rates[$active_currency_iso_code] as $date_stamp => $conversion_rate ) {
								$crf = TTnew('CurrencyRateFactory');
								$crf->setCurrency( $active_currency_id );
								$crf->setDateStamp( strtotime( $date_stamp ) );
								$crf->setConversionRate(  $conversion_rate );
								Debug::Text('Currency: '. $active_currency_iso_code .' Date: '. $date_stamp .' Rate: Raw: '. $conversion_rate .' Modified: '. $crf->getCurrencyObject()->getPercentModifiedRate( $conversion_rate ), __FILE__, __LINE__, __METHOD__, 10);
								if ( $crf->isValid() ) {
									$crf->Save();
								}
							}
						}
					} else {
						Debug::Text('  Rates not older than 24hrs, no need to backfill...', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			} else {
				Debug::Text('  No Auto-Update Currencies to process...', __FILE__, __LINE__, __METHOD__, 10);
			}
			unset($crlf);

			$crlf = TTNew('CurrencyRateListFactory');
			if ( empty($manual_currencies) == FALSE ) {
				Debug::Text('  Processing Manual Currencies... Total: '. count($manual_currencies), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $manual_currencies as $active_currency_id => $active_currency_iso_code ) {
					$crlf->getByCurrencyId( $active_currency_id, 1, NULL, NULL, array('date_stamp' => 'desc' ) );
					if ( $crlf->getRecordCount() > 0 ) {
						$latest_currency_rate_date = $crlf->getCurrent()->getDateStamp();
					} else {
						$latest_currency_rate_date = $earliest_pay_period_start_date;
					}
					Debug::Text('  Latest Currency Rate Date: '. TTDate::getDATE('DATE', $latest_currency_rate_date ) .'('.$latest_currency_rate_date.') Currency ISO: '. $active_currency_iso_code, __FILE__, __LINE__, __METHOD__, 10);
					$latest_currency_rate_date += 86400; //Start on next day.

					if ( ( TTDate::getMiddleDayEpoch( time() ) - TTDate::getMiddleDayEpoch( $latest_currency_rate_date ) ) >= 86400 ) {
						$clf = TTnew( 'CurrencyListFactory' );
						$clf->getByIdAndCompanyId( $active_currency_id, $company_id );
						if ( $clf->getRecordCount() > 0 ) {
							$last_conversion_rate = $clf->getCurrent()->getConversionRate();
							//This updates right to the current date, as we won't be downloading any rates later, and no need to update the currency record itself.

							//As an optimization, do quick inserts if we're more than 30 days old.
							if ( TTDate::getDays( ( time() - $latest_currency_rate_date ) ) > 30 ) {
								$crf = TTnew('CurrencyRateFactory');
								for( $x = TTDate::getMiddleDayEpoch( $latest_currency_rate_date ); $x <= TTDate::getMiddleDayEpoch( time() ); $x += 86400 ) {
									$crf->db->Execute('INSERT INTO '. $crf->getTable() .' (currency_id,date_stamp,conversion_rate,created_date) VALUES('. $active_currency_id .',\''. $crf->db->BindDate($x) .'\', '. $last_conversion_rate .','. time() .')');
								}
							} else {
								for( $x = TTDate::getMiddleDayEpoch( $latest_currency_rate_date ); $x <= TTDate::getMiddleDayEpoch( time() ); $x += 86400 ) {
									$crf = TTnew('CurrencyRateFactory');
									$crf->setCurrency( $active_currency_id );
									$crf->setDateStamp( $x );
									$crf->setConversionRate( $last_conversion_rate );
									Debug::Text('  Currency: '. $active_currency_iso_code .' Date: '. $x .' Rate: Raw: '. $last_conversion_rate, __FILE__, __LINE__, __METHOD__, 10);
									if ( $crf->isValid() ) {
										$crf->Save();
									}
								}
							}
						}
					}
					unset($last_conversion_rate);
				}
			} else {
				Debug::Text('  No Manual Currencies to process...', __FILE__, __LINE__, __METHOD__, 10);
			}
			unset($crlf);
		}
		unset( $active_currency_id, $active_currency_iso_code, $latest_currency_rate_date, $currency_rates );

		if ( $base_currency != FALSE
				AND empty($active_currencies) == FALSE
				AND is_array($active_currencies)
				AND count($active_currencies) > 0 ) {
			$currency_rates = $ttsc->getCurrencyExchangeRates( $company_id, $active_currencies, $base_currency );
		} else {
			Debug::Text('  No auto-update currencies exist, no need to process further...', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($currency_rates) AND is_array($currency_rates) AND count($currency_rates) > 0 ) {
			foreach( $currency_rates as $currency => $rate ) {
				if ( is_numeric($rate) ) {
					$clf = TTnew( 'CurrencyListFactory' );
					$clf->getByCompanyIdAndISOCode( $company_id, $currency);
					if ( $clf->getRecordCount() == 1 ) {
						$c_obj = $clf->getCurrent();

						if ( $c_obj->getAutoUpdate() == TRUE ) {
							$c_obj->setActualRate( $rate );
							$c_obj->setConversionRate( $c_obj->getPercentModifiedRate( $rate ) );
							$c_obj->setActualRateUpdatedDate( time() );
							if ( $c_obj->isValid() ) {
								$c_obj->Save();
							}
						}
					}
				} else {
					Debug::Text('  Invalid rate from data feed! Currency: '. $currency .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			unset($ttsc, $currency_rates, $currency, $rate, $clf, $c_obj);

			Debug::Text('Done updating Currencies...', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::Text('Updating Currency Data Complete...', __FILE__, __LINE__, __METHOD__, 10);
		unset($ttsc);

		return FALSE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		if ( $this->getCompany() != FALSE AND $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' );
			$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		}
		// Status
		if ( $this->getStatus() != '' ) {
			$this->Validator->inArrayKey(	'status',
													$this->getStatus(),
													TTi18n::gettext('Incorrect Status'),
													$this->getOptions('status')
												);
		}
		// Name
		if ( $this->getName() !== FALSE ) {
			$this->Validator->isLength(		'name',
													$this->getName(),
													TTi18n::gettext('Name is too short or too long'),
													2,
													100
												);
			if ( $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue(		'name',
														$this->isUniqueName($this->getName()),
														TTi18n::gettext('Currency already exists')
													);
			}
		}
		// ISO code
		if ( $this->getISOCode() !== FALSE ) {
			$this->Validator->inArrayKey(	'iso_code',
													$this->getISOCode(),
													TTi18n::gettext('ISO code is invalid'),
													$this->getISOCodesArray()
												);
		}
		// Conversion rate
		if ( $this->getConversionRate() !== FALSE ) {
			$this->Validator->isTrue( 'conversion_rate',
											$this->getConversionRate(),
											TTi18n::gettext( 'Conversion rate not specified' )
										);
			if ( $this->Validator->isError('conversion_rate') == FALSE ) {
				$this->Validator->isFloat( 'conversion_rate',
													$this->getConversionRate(),
													TTi18n::gettext( 'Incorrect Conversion Rate' )
												);
			}
			if ( $this->Validator->isError('conversion_rate') == FALSE ) {
				$this->Validator->isLessThan( 'conversion_rate',
													$this->getConversionRate(),
													TTi18n::gettext( 'Conversion Rate is too high' ),
													99999999
												);
			}
			if ( $this->Validator->isError('conversion_rate') == FALSE ) {
				$this->Validator->isGreaterThan( 'conversion_rate',
														$this->getConversionRate(),
														TTi18n::gettext( 'Conversion Rate is too low' ),
														-99999999
													);
			}
		}
		// Actual Rate
		if ( $this->getActualRate() !== FALSE AND is_numeric( $this->getActualRate() ) ) {
			$this->Validator->isFloat(	'actual_rate',
												$this->getActualRate(),
												TTi18n::gettext('Incorrect Actual Rate')
											);
		}

		// Updated Date
		if ( $this->getActualRateUpdatedDate() !== FALSE ) {
			$this->Validator->isDate( 'actual_rate_updated_date',
									  $this->getActualRateUpdatedDate(),
									  TTi18n::gettext( 'Incorrect Actual Rate Updated Date' )
			);
		}
		// Modify Percent
		if ( $this->getRateModifyPercent() !== FALSE ) {
			$this->Validator->isFloat( 'rate_modify_percent',
									   $this->getRateModifyPercent(),
									   TTi18n::gettext( 'Incorrect Modify Percent' )
			);
		}
		// Is Default
		if ( $this->getDefault() !== FALSE ) {
			$this->Validator->isTrue(		'is_default',
													$this->isUniqueDefault(),
													TTi18n::gettext('There is already a default currency set')
												);
		}
		// Is Base
		if ( $this->getBase() !== FALSE ) {
			$this->Validator->isTrue(		'is_base',
													$this->isUniqueBase(),
													TTi18n::gettext('There is already a base currency set')
												);
		}
		// Rounding decimal places
		if ( $this->getRoundDecimalPlaces() != 2 ) {
			$this->Validator->inArrayKey(	'round_decimal_places',
													$this->getRoundDecimalPlaces(),
													TTi18n::gettext('Incorrect rounding decimal places'),
													$this->getOptions('round_decimal_places')
												);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == TRUE ) {
			//CHeck to make sure currency isnt in-use by paystubs/employees/wages, if so, don't delete.

			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'RemittanceSourceAccountListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by remittance sources' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'ProductListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by invoice products' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'UserListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by employees' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var InvoiceListFactory $splf */
				$splf = TTnew( 'InvoiceListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by invoices' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ClientContactListFactory $splf */
				$splf = TTnew( 'ClientContactListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by client contacts' ) );
				}
			}

			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'RemittanceDestinationAccountListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by employee payment methods' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'TransactionListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by invoice transactions' ) );
				}
			}
			if ( $this->Validator->isError( 'in_use' ) == FALSE ) {
				/** @var ProductListFactory $splf */
				$splf = TTnew( 'PayStubListFactory' );
				$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'pay_stub_currency_id' => $this->getId() ), 1 );
				if ( $splf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  FALSE,
											  TTi18n::gettext( 'This currency is currently in use' ) . ' ' . TTi18n::gettext( 'by pay stubs' ) );
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getBase() == TRUE ) {
			$this->setConversionRate( '1.00' );
			$this->setRateModifyPercent( '1.00' );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getCompany().$this->getBase() );

		//CompanyFactory->getEncoding() is used to determine report encodings based on data saved here.
		$this->removeCache( 'encoding_'.$this->getCompany(), 'company' );

		if ( $this->getDeleted() == FALSE ) {
			Debug::Text('Currency modified, update historical rate for today: '. $this->getISOCode() .' Date: '. time() .' Rate: '. $this->getConversionRate(), __FILE__, __LINE__, __METHOD__, 10);
			$crlf = TTnew('CurrencyRateListFactory');
			$crlf->getByCurrencyIdAndDateStamp( $this->getID(), time() );
			if ( $crlf->getRecordCount() > 0 ) {
				$crf = $crlf->getCurrent();
			} else {
				$crf = TTnew('CurrencyRateFactory');
			}
			$crf->setCurrency( $this->getID() );
			$crf->setDateStamp( time() );
			$crf->setConversionRate( $this->getConversionRate() );
			if ( $crf->isValid() ) {
				$crf->Save();
			}
		}

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	/**
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
						case 'status':
						//case 'country_currency':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'symbol':
							$data[$variable] = $this->getSymbol();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Currency').': '. $this->getISOCode() .' '.  TTi18n::getText('Rate').': '. $this->getConversionRate(), NULL, $this->getTable(), $this );
	}

}
?>
