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
 * @package Modules\Import
 */
class ImportUserWage extends Import {

	public $class_name = 'APIUserWage';

	public $wage_group_options = false;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$uwf = TTNew( 'UserWageFactory' ); /** @var UserWageFactory $uwf */
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( [ 'wage_group', 'type', 'wage', 'effective_date', 'hourly_rate', 'labor_burden_percent', 'weekly_time', 'note' ], Misc::trimSortPrefix( $uwf->getOptions( 'columns' ) ) ) );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'type'       => 'type_id',
						'wage_group' => 'wage_group_id',
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
						'weekly_time'    => $upf->getOptions( 'time_unit_format' ),
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
		$user_id = $this->getUserIdByRowData( $raw_row ); //Try to get user_id of row so we set default effective_date to the employees hire_date.
		$user_wage_default_data = $this->getObject()->stripReturnHandler( $this->getObject()->getUserWageDefaultData( $user_id ) );
		$user_wage_default_data['effective_date'] = TTDate::parseDateTime( $user_wage_default_data['effective_date'] ); //Effective Date is formatted for user to see, so convert it to epoch for importing.

		$retval = $user_wage_default_data;

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

		//If its a salary type, make sure average weekly time is always specified and hourly rate.
		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setUserWage( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @return bool
	 */
	function getWageGroupOptions() {
		//Get job titles
		$wglf = TTNew( 'WageGroupListFactory' ); /** @var WageGroupListFactory $wglf */
		$wglf->getByCompanyId( $this->company_id );
		$this->wage_group_options = (array)$wglf->getArrayByListFactory( $wglf, false );
		unset( $wglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_wage_group( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' || trim( strtolower( $input ) ) == 'default' ) {
			return TTUUID::getZeroID(); //Default Wage Group
		}

		if ( !is_array( $this->wage_group_options ) ) {
			$this->getWageGroupOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->wage_group_options );
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}


	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return bool|false|int
	 */
	function parse_effective_date( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		if ( isset( $parse_hint ) && $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );

			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return array|bool|int|mixed
	 */
	function parse_type( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		$uwf = TTnew( 'UserWageFactory' ); /** @var UserWageFactory $uwf */
		$options = Misc::trimSortPrefix( $uwf->getOptions( 'type' ) );

		if ( isset( $options[$input] ) ) {
			$retval = $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				$retval = $this->findClosestMatch( $input, $options, 50 );
			} else {
				$retval = array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}

		if ( $retval === false ) {
			if ( strtolower( $input ) == 'salary' || strtolower( $input ) == 'salaried' || strtolower( $input ) == 's' || strtolower( $input ) == 'annual' ) {
				$retval = 20;
			} else if ( strtolower( $input ) == 'month' || strtolower( $input ) == 'monthly' ) {
				$retval = 15;
			} else if ( strtolower( $input ) == 'biweekly' || strtolower( $input ) == 'bi-weekly' ) {
				$retval = 13;
			} else if ( strtolower( $input ) == 'week' || strtolower( $input ) == 'weekly' ) {
				$retval = 12;
			} else {
				$retval = 10;
			}
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return bool|float|int|number|string
	 */
	function parse_weekly_time( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		if ( isset( $parse_hint ) && $parse_hint != '' ) {
			TTDate::setTimeUnitFormat( $parse_hint );
		}

		$retval = TTDate::parseTimeUnit( $input );

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_wage( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		$val = new Validator();
		$retval = $val->stripNonFloat( $input );

		return $retval;
	}
}

?>
