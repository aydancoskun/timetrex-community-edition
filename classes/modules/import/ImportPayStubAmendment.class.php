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
 * @package Modules\Import
 */
class ImportPayStubAmendment extends Import {

	public $class_name = 'APIPayStubAmendment';

	public $pay_stub_account_options = false;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$psaf = TTNew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( [ 'status', 'type', 'pay_stub_entry_name', 'effective_date', 'amount', 'rate', 'units', 'description', 'ytd_adjustment' ], Misc::trimSortPrefix( $psaf->getOptions( 'columns' ) ) ) );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'status'              => 'status_id',
						'type'                => 'type_id',
						'pay_stub_entry_name' => 'pay_stub_entry_name_id',
				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match' => TTi18n::getText( 'Enable smart matching.' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

				$retval = [
						'effective_date' => $upf->getOptions( 'date_format' ),
						//'amount' => $upf->getOptions('time_unit_format'),
				];
				break;
		}

		return $retval;
	}


	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function _preParseRow( $row_number, $raw_row ) {
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getPayStubAmendmentDefaultData() );

		return $retval;
	}

	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function _postParseRow( $row_number, $raw_row ) {
		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == false ) {
			unset( $raw_row['user_id'] );
		}

		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setPayStubAmendment( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @return bool
	 */
	function getPayStubAccountOptions() {
		//Get accrual policies
		$psealf = TTNew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyIdAndTypeId( $this->company_id, [ 10, 20, 30, 50, 80 ] );

		//Get names with types in front, ie: "Earning - Commission"
		$this->pay_stub_account_options = (array)$psealf->getArrayByListFactory( $psealf, false, true, true );

		//Get names without types in front, ie: "Commission"
		$this->pay_stub_account_short_options = (array)$psealf->getArrayByListFactory( $psealf, false, true, false, false );
		unset( $psealf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_pay_stub_entry_name( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //Default Wage Group
		}

		if ( !is_array( $this->pay_stub_account_options ) ) {
			$this->getPayStubAccountOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->pay_stub_account_options );
		//Debug::Arr( $this->pay_stub_account_options, 'aAttempting to find PS Account with long name: '. $input, __FILE__, __LINE__, __METHOD__, 10);
		if ( $retval === false ) {
			$retval = $this->findClosestMatch( $input, $this->pay_stub_account_short_options );
			//Debug::Arr( $this->pay_stub_account_short_options, 'bAttempting to find PS Account with short name: '. $input, __FILE__, __LINE__, __METHOD__, 10);
			if ( $retval === false ) {
				$retval = -1; //Make sure this fails.
			}
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_effective_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_status( $input, $default_value = null, $parse_hint = null ) {
		$psaf = TTnew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
		$options = Misc::trimSortPrefix( $psaf->getOptions( 'status' ) );

		if ( isset( $options[$input] ) ) {
			return $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_type( $input, $default_value = null, $parse_hint = null ) {
		$psaf = TTnew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
		$options = Misc::trimSortPrefix( $psaf->getOptions( 'type' ) );

		if ( isset( $options[$input] ) ) {
			return $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_amount( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		$val = new Validator();
		$retval = $val->stripNonFloat( $input );

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_rate( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		$val = new Validator();
		$retval = $val->stripNonFloat( $input );

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_units( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		$val = new Validator();
		$retval = $val->stripNonFloat( $input );

		return $retval;
	}
}

?>
