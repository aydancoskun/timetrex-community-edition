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
class ImportDepartment extends Import {

	public $class_name = 'APIDepartment';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				global $current_company;

				$bf = TTNew( 'DepartmentFactory' ); /** @var DepartmentFactory $bf */
				$retval = $bf->getOptions( 'columns' );

				if ( is_object( $current_company ) ) {
					//Get custom fields for import data.
					$oflf = TTnew( 'OtherFieldListFactory' ); /** @var OtherFieldListFactory $oflf */
					$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), [ 5 ], [ 5 => '' ] );
					if ( is_array( $other_field_names ) ) {
						$retval = array_merge( (array)$retval, (array)$other_field_names );
					}
				}

				$retval = Misc::trimSortPrefix( $retval );
				Debug::Arr( $retval, 'ImportDepartmentColumns: ', __FILE__, __LINE__, __METHOD__, 10 );

				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match' => TTi18n::getText( 'Enable smart matching.' ),
				];
				break;
			case 'parse_hint':
				$retval = [];
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
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getDepartmentDefaultData() );
		$retval['manual_id'] += $row_number; //Auto increment manual_id automatically.

		return $retval;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setDepartment( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return int
	 */
	function parse_status( $input, $default_value = null, $parse_hint = null, $raw_row = null ) {
		if ( strtolower( $input ) == 'e'
				|| strtolower( $input ) == 'enabled' ) {
			$retval = 10;
		} else if ( strtolower( $input ) == 'd'
				|| strtolower( $input ) == 'disabled' ) {
			$retval = 20;
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}

}

?>
