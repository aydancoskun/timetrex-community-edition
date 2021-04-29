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
 * @package Modules\Import
 */
class ImportRemittanceDestinationAccount extends Import {

	public $class_name = 'APIRemittanceDestinationAccount';

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
				$baf = TTNew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $baf */
				$retval = Misc::trimSortPrefix( $baf->getOptions( 'columns' ) );

				unset( $retval['display_amount'] ); //For display purposes only.

				$retval = Misc::addSortPrefix( Misc::prependArray( $this->getUserIdentificationColumns(), Misc::trimSortPrefix( $retval ) ) );
				ksort( $retval );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [

					//'wage_group' => 'wage_group_id',
					'amount_type' => 'amount_type_id',
					'type'        => 'type_id',

				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match' => TTi18n::getText( 'Enable smart matching.' ),
				];
				break;
			case 'parse_hint':
				//$upf = TTnew('UserPreferenceFactory');

				$retval = [
					//'effective_date' => $upf->getOptions('date_format'),
					//'weekly_time' => $upf->getOptions('time_unit_format'),
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
		//Try to determine if its a checking or savings account, so we at least have a chance at specifying a default name for the account.
		$ach_transaction_type = 22;
		if ( isset( $raw_row['ach_transaction_type'] ) ) {
			$ach_transaction_type = $this->parse_ach_transaction_type( $raw_row['ach_transaction_type'] );
		}

		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getRemittanceDestinationAccountDefaultData( $ach_transaction_type ) );
		foreach ( $raw_row as $key => $value ) {
			$retval[$key] = $value;
		}

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
			$raw_row['user_id'] = TTUUID::getNotExistID();
			//unset($raw_row['user_id']);
		}

		if ( !isset( $raw_row['type'] ) || $raw_row['type'] == '' ) {
			$raw_row['type'] = 3000; //EFT
		}

		//remittance source by user
		if ( isset( $raw_row['user_id'] ) && !isset( $raw_row['remittance_source_account_id'] ) ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByIdAndCompanyId( $raw_row['user_id'], $this->getCompanyObject()->getId() );

			if ( $ulf->getRecordCount() > 0 ) {

				$u_obj = $ulf->getCurrent();

				$rsalf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rsalf */
				$rsalf->getByLegalEntityIdAndTypeIdAndCompanyId( $u_obj->getLegalEntity(), $raw_row['type'], $this->getCompanyObject()->getId() );
				if ( $rsalf->getRecordCount() > 0 ) {
					$raw_row['remittance_source_account_id'] = $rsalf->getCurrent()->getId();
					unset( $rsalf );
				}
			}
		}

		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setRemittanceDestinationAccount( $this->getParsedData(), $validate_only );
	}


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


	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_type( $input, $default_value = null, $parse_hint = null ) {
		$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */
		$options = $rsaf->getOptions( 'type' );

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
	 * @param string $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_amount_type( $input, $default_value = 'Percent', $parse_hint = null ) {
		$rsaf = TTnew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $rsaf */
		$options = $rsaf->getOptions( 'amount_type' );

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
	 * @return array|bool|int|mixed
	 */
	function parse_remittance_source_account( $input, $default_value = null, $parse_hint = null ) {
		$rdalf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rdalf */
		$rdalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompanyObject()->getId(), [] );
		$result = (array)$rdalf->getArrayByListFactory( $rdalf, false );
		$retval = $this->findClosestMatch( $input, $result );
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_ach_transaction_type( $input, $default_value = null, $parse_hint = null ) {
		$rdaf = TTnew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $rdaf */
		$options = $rdaf->getOptions( 'ach_transaction_type' );

		if ( isset( $options[$input] ) ) {
			return $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower( $input ), array_map( 'strtolower', (array)$options ) );
			}
		}
	}
}

?>
