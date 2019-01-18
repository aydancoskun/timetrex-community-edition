<?php
/**
 * $License$
 */

/**
 * @package Modules\Import
 */
class ImportRemittanceDestinationAccount extends Import {

	public $class_name = 'APIRemittanceDestinationAccount';

	public $wage_group_options = FALSE;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$baf = TTNew('RemittanceDestinationAccountFactory');
				$retval = Misc::trimSortPrefix( $baf->getOptions('columns') );

				unset($retval['display_amount']); //For display purposes only.

				$retval = Misc::addSortPrefix( Misc::prependArray( $this->getUserIdentificationColumns(), Misc::trimSortPrefix($retval) ) );
				ksort($retval);

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = array(

								//'wage_group' => 'wage_group_id',
								'amount_type' => 'amount_type_id',
								'type' => 'type_id',

								);
				break;
			case 'import_options':
				$retval = array(
								'-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
								);
				break;
			case 'parse_hint':
				//$upf = TTnew('UserPreferenceFactory');

				$retval = array(
								//'effective_date' => $upf->getOptions('date_format'),
								//'weekly_time' => $upf->getOptions('time_unit_format'),
								);
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
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getRemittanceDestinationAccountDefaultData() );
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
		if ( $raw_row['user_id'] == FALSE ) {
			$raw_row['user_id'] = TTUUID::getNotExistID();
			//unset($raw_row['user_id']);
		}

		if ( !isset($raw_row['type']) OR $raw_row['type'] == '' ) {
			$raw_row['type'] = 3000; //EFT
		}

		//remittance source by user
		if ( isset($raw_row['user_id']) AND !isset($raw_row['remittance_source_account_id']) ) {
			/** @var UserListFactory $ulf */
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByIdAndCompanyId( $raw_row['user_id'], $this->getCompanyObject()->getId() );

			if ( $ulf->getRecordCount() > 0 ) {

				$u_obj = $ulf->getCurrent();

				/** @var RemittanceSourceAccountListFactory $rsalf */
				$rsalf = TTnew( 'RemittanceSourceAccountListFactory' );
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



	function parse_status( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( strtolower( $input ) == 'e'
				OR strtolower( $input ) == 'enabled' ) {
			$retval = 10;
		} elseif ( strtolower( $input ) == 'd'
				OR strtolower( $input ) == 'disabled' ) {
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
	function parse_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		$rsaf = TTnew('RemittanceSourceAccountFactory');
		$options = $rsaf->getOptions( 'type' );

		if ( isset($options[$input]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', $options) );
			}
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_amount_type( $input, $default_value = 'Percent', $parse_hint = NULL ) {
		$rsaf = TTnew('RemittanceDestinationAccountFactory');
		$options = $rsaf->getOptions( 'amount_type' );

		if ( isset($options[$input]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', $options) );
			}
		}
	}

	function parse_remittance_source_account( $input, $default_value = NULL, $parse_hint = NULL ) {
		/** @var RemittanceSourceAccountListFactory $rdalf */
		$rdalf = TTnew('RemittanceSourceAccountListFactory');
		$rdalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompanyObject()->getId(), array());
		$result  = (array)$rdalf->getArrayByListFactory( $rdalf, FALSE, TRUE );
		$retval = $this->findClosestMatch( $input, $result );
		if ( $retval === FALSE ) {
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
	function parse_ach_transaction_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		$rdaf = TTnew('RemittanceDestinationAccountFactory');
		$options = $rdaf->getOptions( 'ach_transaction_type' );

		if ( isset($options[$input]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', (array)$options) );
			}
		}
	}
}
?>
