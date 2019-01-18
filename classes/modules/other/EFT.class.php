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


/*

How this needs to work
----------------------

Abstraction layer. Store all data in a "TimeTrex" format, then use
different classes to export that data to each EFT foramt.

Add:

setFormat()

We probably need to support CPA 005, a CVS standard, and 105/80 byte standards too

Add internal checks that totals the debits/credits before the format is compiled, then matches
with the compiled format as well?

*/

/*
Example Usage:

$eft = new EFT();
$eft->setOriginatorID(1234567890);
$eft->setFileCreationNumber(1777);
$eft->setDataCenter(00400);
	$record = new EFT_Record();
	$record->setType('C');
	$record->setCPACode(001);
	$record->setAmount(100.11);
	$record->setDueDate( time() + (86400 * 7) );
	$record->setInstitution( 123 );
	$record->setTransit( 12345 );
	$record->setAccount( 123456789012 );
	$record->setName( 'Mike Benoit' );

	$record->setOriginatorShortName( 'TimeTrex' );
	$record->setOriginatorLongName( 'TimeTrex Payroll Services' );
	$record->setOriginatorReferenceNumber( 987789 );

	$record->setReturnInstitution( 321 );
	$record->setReturnTransit( 54321 );
	$record->setReturnAccount( 210987654321 );
$eft->setRecord( $record );

$eft->compile();
$eft->save('/tmp/eft01.txt');
*/

/**
 * @package Modules\Other
 */
class EFT {

	var $file_format_options = array( '1464', '105', 'HSBC', 'BEANSTREAM', 'ACH' );
	var $file_format = NULL; //File format
	var $line_ending = "\r\n";

	var $file_prefix_data = NULL; //Leading data in the file, primarily for RBC routing lines.
	var $file_postfix_data = NULL;
	var $header_data = NULL;
	var $data = NULL;

	var $compiled_data = NULL;

	/**
	 * EFT constructor.
	 * @param null $options
	 */
	function __construct( $options = NULL ) {
		Debug::Text(' Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->setFileCreationDate( time() );

		return TRUE;
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	function removeDecimal( $value ) {
		$retval = str_replace('.', '', number_format( $value, 2, '.', '') );

		return $retval;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return string
	 */
	function toJulian( $epoch ) {

		$year = str_pad( date('y', $epoch), 3, 0, STR_PAD_LEFT);

		//PHP's day of year is 0 based, so we need to add one for the banks.
		$day = str_pad( (date('z', $epoch) + 1), 3, 0, STR_PAD_LEFT);

		$retval = $year.$day;

		Debug::Text('Converting: '. TTDate::getDate('DATE+TIME', $epoch) .' To Julian: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function isAlphaNumeric( $value ) {
		/*
		//if ( preg_match('/^[-0-9A-Z\ ]+$/', $value) ) {
		if ( preg_match('/^[-0-9A-Z_\ =\$\.\&\*\,]+$/i', $value) ) { //Case insensitive
			return TRUE;
		}

		return FALSE;
		*/

		return TRUE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function isNumeric( $value ) {
		if ( preg_match('/^[-0-9]+$/', $value) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function isFloat( $value ) {
		if ( preg_match('/^[-0-9\.]+$/', $value) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool|null
	 */
	function getFilePrefixData() {
		if ( isset($this->file_prefix_data) ) {
			return $this->file_prefix_data;
		}

		return FALSE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setFilePrefixData( $data ) {
		$this->file_prefix_data = $data;

		return TRUE;
	}

	/**
	 * @return bool|null
	 */
	function getFilePostfixData() {
		if ( isset($this->file_postfix_data) ) {
			return $this->file_postfix_data;
		}

		return FALSE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setFilePostfixData( $data ) {
		$this->file_postfix_data = $data;

		return TRUE;
	}

	/**
	 * @return bool|null
	 */
	function getFileFormat() {
		if ( isset($this->file_format) ) {
			return $this->file_format;
		}

		return FALSE;
	}

	/**
	 * @param $format
	 * @return bool
	 */
	function setFileFormat( $format) {
		$this->file_format = $format;

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getBusinessNumber() {
		if ( isset($this->header_data['business_number']) ) {
			return $this->header_data['business_number'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBusinessNumber( $value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['business_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getOriginatorID() {
		if ( isset($this->header_data['originator_id']) ) {
			return $this->header_data['originator_id'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginatorID( $value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['originator_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getOriginatorShortName() {
		if ( isset($this->header_data['originator_short_name']) ) {
			return $this->header_data['originator_short_name'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginatorShortName( $value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 26 ) {
			$this->header_data['originator_short_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getFileCreationNumber() {
		if ( isset($this->header_data['file_creation_number']) ) {
			return $this->header_data['file_creation_number'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFileCreationNumber( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 4 ) {
			$this->header_data['file_creation_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getInitialEntryNumber() {
		if ( isset($this->header_data['initial_entry_number']) ) {
			return $this->header_data['initial_entry_number'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInitialEntryNumber( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 15 ) {
			$this->header_data['initial_entry_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getFileCreationDate() {
		if ( isset($this->header_data['file_creation_date']) ) {
			return $this->header_data['file_creation_date'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFileCreationDate( $value) {
		if ( $value != '' ) {
			$this->header_data['file_creation_date'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getDataCenter() {
		if ( isset($this->header_data['data_center']) ) {
			return $this->header_data['data_center'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDataCenter( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['data_center'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getDataCenterName() {
		if ( isset($this->header_data['data_center_name']) ) {
			return $this->header_data['data_center_name'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDataCenterName( $value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 23 ) {
			$this->header_data['data_center_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getCurrencyISOCode() {
		if ( isset($this->header_data['currency_iso_code']) ) {
			return $this->header_data['currency_iso_code'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCurrencyISOCode( $value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->header_data['currency_iso_code'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//
	//See similar function in EFT_Record class.
	//
	/**
	 * @return bool
	 */
	function getBatchBusinessNumber() {
		if ( isset($this->header_data['batch_business_number']) ) {
			return $this->header_data['batch_business_number'];
		} else {
			//If batch business number is not set, fall back to file business number instead.
			return $this->getBusinessNumber();
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBatchBusinessNumber( $value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['batch_business_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//See similar function in EFT_Record class.

	/**
	 * @return string
	 */
	function getBatchServiceCode() {
		if ( isset($this->header_data['batch_service_code']) ) {
			return $this->header_data['batch_service_code'];
		} else {
			return 'PPD'; //Prearranged Payment and Deposit Entry type transactions
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBatchServiceCode( $value) {
		$value = strtoupper(trim($value));
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->header_data['batch_service_code'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	function getBatchEntryDescription() {
		if ( isset($this->header_data['batch_entry_description']) ) {
			return $this->header_data['batch_entry_description'];
		} else {
			return 'PAYROLL'; //Prearranged Payment and Deposit Entry type transactions
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBatchEntryDescription( $value) {
		$value = strtoupper(trim($value));
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['batch_entry_description'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	//
	//See similar function in EFT_Record class.
	//

	/**
	 * @param $key
	 * @return bool
	 */
	function getOtherData( $key ) {
		if ( isset($this->header_data[$key]) ) {
			return $this->header_data[$key];
		}

		return FALSE;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	function setOtherData( $key, $value) {
		$this->header_data[$key] = $value;

		return TRUE;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	private function usortByBusinessNumberAndServiceCodeAndEntryDescriptionAndDueDateAndType( $a, $b) {
		if ( !isset($a->record_data['business_number']) ) {
			$a->record_data['business_number'] = FALSE;
		}
		if ( !isset($b->record_data['business_number']) ) {
			$b->record_data['business_number'] = FALSE;
		}

		if ( $a->record_data['business_number'] == $b->record_data['business_number'] ) {
			if ( !isset($a->record_data['service_code']) ) {
				$a->record_data['service_code'] = FALSE;
			}
			if ( !isset($b->record_data['service_code']) ) {
				$b->record_data['service_code'] = FALSE;
			}

			if ( $a->record_data['service_code'] == $b->record_data['service_code'] ) {
				if ( !isset($a->record_data['entry_description']) ) {
					$a->record_data['entry_description'] = FALSE;
				}
				if ( !isset($b->record_data['entry_description']) ) {
					$b->record_data['entry_description'] = FALSE;
				}

				if ( $a->record_data['entry_description'] == $b->record_data['entry_description'] ) {
					if ( $a->record_data['due_date'] == $b->record_data['due_date'] ) {

						if ( $a->record_data['type'] == $b->record_data['type'] ) {
							return strcmp( $a->record_data['name'], $b->record_data['name'] );
						} else {
							return strcmp( $a->record_data['type'], $b->record_data['type'] );
						}
					} else {
						return ( $a->record_data['due_date'] < $b->record_data['due_date'] ) ? (-1) : 1; //Sort ASC.
					}
				} else {
					return strcmp( $a->record_data['entry_description'], $b->record_data['entry_description'] );
				}
			} else {
				return strcmp( $a->record_data['service_code'], $b->record_data['service_code'] );
			}
		} else {
			return ( $a->record_data['business_number'] < $b->record_data['business_number'] ) ? (-1) : 1; //Sort ASC.
		}
	}

	/**
	 * @return bool
	 */
	private function sortRecords() {
		if ( is_array($this->data) ) {
			return usort( $this->data, array( $this, 'usortByBusinessNumberAndServiceCodeAndEntryDescriptionAndDueDateAndType' ) );
		}

		return FALSE;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setRecord( $obj ) {
		if ( is_object( $obj ) ) {

			//Need this to handle switching transactions between batches with ACH record types.
			if ( strtoupper( $this->getFileFormat() ) == 'ACH' ) {

				$obj->setBusinessNumber( $this->getBatchBusinessNumber() );
				$obj->setServiceCode( $this->getBatchServiceCode() );
				$obj->setEntryDescription( $this->getBatchEntryDescription() );
			}

			$this->data[] = $obj;

			return TRUE;
		}

		return FALSE;
	}

	/*
	Functions to help process the data.
	*/

	/**
	 * @param $value
	 * @param $length
	 * @param $type
	 * @return string
	 */
	function padRecord( $value, $length, $type ) {
		$type = strtolower($type);

		//Trim record incase its too long.
		$value = substr( $value, 0, $length); //Starts at 0, adn $length is total length. So we don't need to minus one from the length.

		switch ($type) {
			case 'n':
				$retval = str_pad( $value, $length, 0, STR_PAD_LEFT);
				break;
			case 'an':
				$retval = str_pad( $value, $length, ' ', STR_PAD_RIGHT);
				break;
			case 'x': //Same as AN only padded to the left instead of right.
				$retval = str_pad( $value, $length, ' ', STR_PAD_LEFT);
				break;
		}

		return $retval;
	}

	/**
	 * @param $line
	 * @param $length
	 * @return string
	 */
	function padLine( $line, $length, $include_line_ending = TRUE ) {
		$retval = str_pad( $line, $length, ' ', STR_PAD_RIGHT);

		if ( $include_line_ending == TRUE ) {
			$retval .= $this->line_ending;
		}

		return $retval;
	}


	/**
	 * @return bool|string
	 */
	function getCompiledData() {
		if ( $this->compiled_data !== NULL AND $this->compiled_data !== FALSE ) {
			$retval = '';

			if ( trim( $this->getFilePrefixData() ) != '' ) {
				$retval .= $this->getFilePrefixData() ."\r\n";
			}

			$retval .= $this->compiled_data;

			if ( trim( $this->getFilePostfixData() ) != '' ) {
				$retval .= "\r\n" . $this->getFilePostfixData(); //Compiled data doesn't have line endings on the last line, so need to insert them before the postfix line.
			}

			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function compile() {
		/*
			$file_format_class_name = 'EFT_File_Format_'.$this->getFileFormat().'()';
			//$file_format_obj = new $file_format_class_name;
			$file_format_obj = new EFT_File_Format_{$this->getFileFormat()}( $this->header_data, $this->data );
		*/

		//Always sort records based on type, so debits come first, then credits (when offset records exist)
		$this->sortRecords();

		switch ( strtoupper( $this->getFileFormat() ) )	 {
			case 1464:
				$file_format_obj = new EFT_File_Format_1464($this->header_data, $this->data);
				break;
			case 105:
				$file_format_obj = new EFT_File_Format_105($this->header_data, $this->data);
				break;
//			case 'HSBC':
//				$file_format_obj = new EFT_File_Format_HSBC($this->data);
//				break;
			case 'BEANSTREAM':
				$file_format_obj = new EFT_File_Format_BEANSTREAM($this->data);
				break;
			case 'ACH':
				$file_format_obj = new EFT_File_Format_ACH($this->header_data, $this->data);
				break;
			default:
				Debug::Text('Format does not exist: '. $this->getFileFormat(), __FILE__, __LINE__, __METHOD__, 10);
				break;
		}

		Debug::Text('aData Lines: '. count($this->data), __FILE__, __LINE__, __METHOD__, 10);

		if ( is_object( $file_format_obj ) ) {
			$compiled_data = $file_format_obj->_compile();
			if ( $compiled_data !== FALSE ) {
				$this->compiled_data = $compiled_data;
				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * @param $file_name
	 * @return bool
	 */
	function save( $file_name) {
		//saves processed data to a file.

		$compiled_data = $this->getCompiledData();
		if ( $compiled_data !== FALSE ) {

			if ( is_writable( dirname($file_name) ) AND !file_exists($file_name) ) {
				if ( file_put_contents($file_name, $compiled_data ) > 0 ) {
					Debug::Text('Write successfull:', __FILE__, __LINE__, __METHOD__, 10);

					return TRUE;
				} else {
					Debug::Text('Write failed:', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::Text('File is not writable, or already exists:', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		Debug::Text('Save Failed!:', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_record extends EFT {

	var $record_data = NULL;

	/**
	 * EFT_record constructor.
	 * @param null $options
	 */
	function __construct( $options = NULL ) {
		Debug::Text(' EFT_Record Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	/**
	 * @return bool|string
	 */
	function getType() {
		if ( isset($this->record_data['type']) ) {
			return strtoupper($this->record_data['type']);
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value) {
		$value = strtolower($value);

		if ( $value == 'd' OR $value == 'c' ) {
			$this->record_data['type'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getCPACode() {
		if ( isset($this->record_data['cpa_code']) ) {
			return $this->record_data['cpa_code'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCPACode( $value) {
		//200 - Payroll deposit
		//460 - Accounts Payable
		//470 - Fees/Dues
		//452 - Expense Payment
		//700 - Business PAD
		//430 - Bill Payment
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['cpa_code'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getAmount() {
		if ( isset($this->record_data['amount']) ) {
			return $this->record_data['amount'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value) {
		if ( $this->isFloat( $value ) AND strlen( $value ) <= 10 ) {
			$this->record_data['amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDueDate() {
		if ( isset($this->record_data['due_date']) ) {
			return $this->record_data['due_date'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDueDate( $value) {
		if ( $value != '' ) {
			$this->record_data['due_date'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//
	//ACH Only, helps set the batches based on different criteria.
	//
	/**
	 * @return bool
	 */
	function getBusinessNumber() {
		if ( isset($this->record_data['business_number']) ) {
			return $this->record_data['business_number'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBusinessNumber( $value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->record_data['business_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	function getServiceCode() {
		if ( isset($this->record_data['service_code']) ) {
			return $this->record_data['service_code'];
		} else {
			return 'PPD'; //Prearranged Payment and Deposit Entry type transactions
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setServiceCode( $value) {
		$value = strtoupper(trim($value));
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['service_code'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	function getEntryDescription() {
		if ( isset($this->record_data['entry_description']) ) {
			return $this->record_data['entry_description'];
		} else {
			return 'PAYROLL'; //Prearranged Payment and Deposit Entry type transactions
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEntryDescription( $value) {
		$value = strtoupper(trim($value));
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->record_data['entry_description'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	function getBatchKey() {
		return trim( $this->getBusinessNumber().$this->getServiceCode().$this->getEntryDescription().$this->getDueDate() );
	}
	//
	//ACH Only, helps set the batches based on different criteria.
	//


	/**
	 * @return bool
	 */
	function getInstitution() {
		if ( isset($this->record_data['institution']) ) {
			return $this->record_data['institution'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInstitution( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getTransit() {
		if ( isset($this->record_data['transit']) ) {
			return $this->record_data['transit'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTransit( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 9 ) { //EFT Transit <= 5, ACH Transit/Routing <= 9:
			$this->record_data['transit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getAccount() {
		if ( isset($this->record_data['account']) ) {
			return $this->record_data['account'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccount( $value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 17 ) { //Needs to be 17 digits for US, 13 for CAD?
			$this->record_data['account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getOriginatorShortName() {
		if ( isset($this->record_data['originator_short_name']) ) {
			return $this->record_data['originator_short_name'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginatorShortName( $value) {
		if ( $this->isAlphaNumeric( $value ) ) { //Max of 15 chars for EFT, but 23 for ACH, so it will be trimmed automatically by the record handler.
			$this->record_data['originator_short_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getName() {
		if ( isset($this->record_data['name']) ) {
			return $this->record_data['name'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value) {
		//Payor or Payee name
		if ( $this->isAlphaNumeric( $value ) ) { //Trimmed automatically to correct size in padRecord()
			$this->record_data['name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getOriginatorLongName() {
		if ( isset($this->record_data['originator_long_name']) ) { //Trimmed automatically to correct size in padRecord()
			return $this->record_data['originator_long_name'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginatorLongName( $value) {
		if ( $this->isAlphaNumeric( $value ) ) { //Trimmed automatically to correct size in padRecord()
			$this->record_data['originator_long_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getOriginatorReferenceNumber() {
		if ( isset($this->record_data['originator_reference_number']) ) {
			return $this->record_data['originator_reference_number'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginatorReferenceNumber( $value) { //Trimmed automatically to correct size in padRecord()
		if ( $this->isAlphaNumeric( $value ) ) {
			$this->record_data['originator_reference_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getReturnInstitution() {
		if ( isset($this->record_data['return_institution']) ) {
			return $this->record_data['return_institution'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReturnInstitution( $value) {
		//Must be 0004 for TD?
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['return_institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getReturnTransit() {
		if ( isset($this->record_data['return_transit']) ) {
			return $this->record_data['return_transit'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReturnTransit( $value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 5 ) {
			$this->record_data['return_transit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getReturnAccount() {
		if ( isset($this->record_data['return_account']) ) {
			return $this->record_data['return_account'];
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReturnAccount( $value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 12 ) {
			$this->record_data['return_account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function getOtherData( $key ) {
		if ( isset($this->record_data[$key]) ) {
			return $this->record_data[$key];
		}

		return FALSE;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	function setOtherData( $key, $value) {
		$this->record_data[$key] = $value;

		return TRUE;
	}
}



/**
 * CPA005 Specification: https://www.payments.ca/sites/default/files/standard-005.pdf
 * @package Modules\Other
 */
class EFT_File_Format_1464 Extends EFT {
	var $header_data = NULL;
	var $data = NULL;

	/**
	 * EFT_File_Format_1464 constructor.
	 * @param null $header_data
	 * @param $data
	 */
	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_1464 Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	/**
	 * @return bool|string
	 */
	private function compileHeader() {
		$line[] = 'A'; //A Record
		$line[] = '000000001'; //A Record number
		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -4 ), 4, 'N');
		$line[] = $this->padRecord( $this->toJulian( $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( $this->getDataCenter(), 5, 'N');

		$sanity_check_1 = strlen( implode('', $line) );
		Debug::Text('Digits to Data Center: '. $sanity_check_1 .' - Should be: 35', __FILE__, __LINE__, __METHOD__, 10);
		if ( $sanity_check_1 !== 35 ) {
			Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		unset($sanity_check_1);

		$line[] = str_repeat(' ', 20); //Blank

		if ( $this->getCurrencyISOCode() != '' ) {
			$line[] = $this->getCurrencyISOCode(); //56-58 - Currency ISO Code, ie: CAD/USD
		}

		if ( $this->getOtherData('sub_file_format') == 30 ) { //CIBC
			//FIXME: The settlement account series (01) needs to be in each record between 252 and 253 (N) as well
			//Some banks, specifically CIBC require a mandatory Settlement Account Series that is the return bank account.
			//This seems to be only for their pre-funded settlement option.
			$line[] = str_repeat(' ', 1190); //Blank
			$line[] = '0001'; //Version Number (always 0001) - Starts at 1249
			$line[] = '01'; //Number of Settlement Account Series (always 01)
			$line[] = '0'.$this->padRecord( $this->getOtherData('settlement_institution'), 3, 'N').$this->padRecord( $this->getOtherData('settlement_transit'), 5, 'N');
			$line[] = $this->padRecord( $this->getOtherData('settlement_account'), 12, 'AN');

			$sanity_check_2 = strlen( implode('', $line) );
			Debug::Text('Digits to end of Settlement Account: '. $sanity_check_2 .' - Should be: 1275', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_2 !== 1275 ) {
				Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_2);
		}

		$retval = $this->padLine( implode('', $line), 1464 );

		Debug::Text('A Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i = 2;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $record->getType(); //Position: 1
			$line[] = $this->padRecord($i, 9, 'N'); //Position: 2-10

			$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN'); //Position: 11-24
			$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -4 ), 4, 'N'); //Includes above

			$line[] = $this->padRecord( $record->getCPACode(), 3, 'N'); //Position: 25-27
			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N'); //Position: 28-37

			$line[] = $this->padRecord( $this->toJulian( $record->getDueDate() ), 6, 'N'); //Position: 38-43

			$line[] = '0'.$this->padRecord( $record->getInstitution(), 3, 'N').$this->padRecord( $record->getTransit(), 5, 'N'); //Position: 44-52
			$line[] = $this->padRecord( $record->getAccount(), 12, 'AN'); //Position: 53-64

			$line[] = str_repeat('0', 22); //Reserved Position: 65-86
			$line[] = str_repeat('0', 3); //Reserved Position: 87-89

			$sanity_check_1 = strlen( implode('', $line) );
			Debug::Text('Digits to Originator Short Name: '. $sanity_check_1 .' - Should be: 89', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_1 !== 89 ) {
				Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_1);

			$line[] = $this->padRecord( $record->getOriginatorShortName(), 15, 'AN'); //Position: 90-104
			$line[] = $this->padRecord( $record->getName(), 30, 'AN'); //Position: 105-134

			$line[] = $this->padRecord( $record->getOriginatorLongName(), 30, 'AN'); //Position: 135-164
			$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN'); //Position: 165-174
			$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 19, 'AN'); //Position: 175-193

			$line[] = '0'.$this->padRecord( $record->getReturnInstitution(), 3, 'N').$this->padRecord( $record->getReturnTransit(), 5, 'N'); //Position: 194-202
			$line[] = $this->padRecord( $record->getReturnAccount(), 12, 'AN'); //Position: 203-214

			$sanity_check_2 = strlen( implode('', $line) );
			Debug::Text('Digits to END of return account: '. $sanity_check_2 .' - Should be: 214', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_2 !== 214 ) {
				Debug::Text('Failed Sanity Check 2', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_2);

			$line[] = $this->padRecord( NULL, 15, 'AN'); //215-229 - Originators Sundry Info. -- Blank
			$line[] = $this->padRecord( NULL, 22, 'AN'); //230-251 - Filler -- Blank
			if ( $this->getOtherData('sub_file_format') == 30 ) { //CIBC
				$line[] = $this->padRecord( '01', 2, 'AN'); //252-253 - Settlement Code, always '01'
			} else {
				$line[] = $this->padRecord( NULL, 2, 'AN'); //252-253 - Settlement Code -- Blank
			}
			$line[] = $this->padRecord( NULL, 11, 'N'); //254-264 - Invalid Data Element, must be 0's for HSBC to accept it -- Blank

			$sanity_check_3 = strlen( implode('', $line) );
			Debug::Text('Digits to END of invalid data element: '. $sanity_check_3 .' - Should be: 264', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_3 !== 264 ) {
				Debug::Text('Failed Sanity Check 3', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_3);

			$d_record = $this->padLine( implode('', $line), 1464 );
			//strlen($d_record) might show 1466 (2digits more), due to "/n" being at the end.
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);

			$i++;
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	private function compileFooter() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		$line[] = 'Z'; //Z Record

		//$line[] = '000000001'; //Z Record number
		$line[] = $this->padRecord( (count($this->data) + 2), 9, 'N'); //add 2, 1 for the A record, and 1 for the Z record.

		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -4 ), 4, 'N');

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$c_record_count++;
			}
		}

		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 14, 'N');
		$line[] = $this->padRecord( $d_record_count, 8, 'N');

		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 14, 'N');
		$line[] = $this->padRecord( $c_record_count, 8, 'N');

		$line[] = $this->padRecord( NULL, 14, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 8, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 14, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 8, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank

		$retval = $this->padLine( implode('', $line), 1464, FALSE ); //Last line in file, don't include line ending so we don't get blank lines.

		Debug::Text('Z Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;

	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = $this->compileHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFooter();

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 1464 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}



/**
 * @package Modules\Other
 */
class EFT_File_Format_105 Extends EFT {
	var $header_data = NULL;
	var $data = NULL;

	/**
	 * EFT_File_Format_105 constructor.
	 * @param null $header_data
	 * @param $data
	 */
	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_105 Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	/**
	 * @return string
	 */
	private function compileHeader() {
		$line[] = 'A'; //A Record
		$line[] = '000000001'; //A Record number

		//This should be the scotia bank "Customer Number"
		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -4 ), 4, 'N');
		$line[] = $this->padRecord( $this->toJulian( $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( $this->getDataCenter(), 5, 'N');
		$line[] = 'D';

		$retval = $this->padLine( implode('', $line), 105 );

		Debug::Text('A Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	private function compileCustomerHeader() {
		$record = $this->data[0]; //Just use info from first record;

		if ( is_object($record) ) {
			$line[] = 'Y';
			$line[] = $this->padRecord( $record->getOriginatorShortName(), 15, 'AN');
			$line[] = $this->padRecord( $record->getOriginatorLongName(), 30, 'AN');
			$line[] = $this->padRecord( $record->getReturnInstitution(), 3, 'N');

			$line[] = $this->padRecord( $record->getReturnTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getReturnAccount(), 12, 'AN');

			$retval = $this->padLine( implode('', $line), 105 );

			Debug::Text('Y Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i = 2;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $record->getType();
			$line[] = $this->padRecord($record->getCPACode(), 3, 'N');
			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N');
			$line[] = $this->padRecord( $this->toJulian( $record->getDueDate() ), 6, 'N');

			$sanity_check_1 = strlen( implode('', $line) );
			Debug::Text('Digits to Originator Short Name: '. $sanity_check_1 .' - Should be: 20', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_1 !== 20 ) {
				Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_1);

			if ( $record->getType() == 'D' ) {
				$line[] = ' ';
			}

			Debug::Text('Institution: '. $record->getInstitution() .' Transit: '.$record->getTransit().' Bank Account Number: '. $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $this->padRecord( $record->getInstitution(), 3, 'N');
			$line[] = $this->padRecord( $record->getTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getAccount(), 12, 'AN');
			$line[] = $this->padRecord( $record->getName(), 30, 'AN');
			$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 19, 'AN');

			$d_record = $this->padLine( implode('', $line), 105 );
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);

			$i++;
		}

		if ( isset($retval) ) {
			//var_dump($retval);
			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	private function compileFooter() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		$line[] = 'Z'; //Z Record
		$line[] = $this->padRecord( NULL, 9, 'AN');

		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -4 ), 4, 'N');

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$c_record_count++;
			}
		}

		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 14, 'N');
		$line[] = $this->padRecord( $d_record_count, 8, 'N');

		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 14, 'N');
		$line[] = $this->padRecord( $c_record_count, 8, 'N');

		$retval = $this->padLine( implode('', $line), 105 );

		Debug::Text('Z Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;

	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = $this->compileHeader();
		$compiled_data .= $this->compileCustomerHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFooter();

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= (105 * 3) ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}

/**
 * @package Modules\Other
 */
class EFT_File_Format_ACH Extends EFT {
	/*
	Validator Program: http://www.download.com/3001-2066_4-10344585.html
	http://www.google.ca/url?sa=t&rct=j&q=ach%20file%20format%20immediate%20origin&source=web&cd=4&ved=0CEMQFjAD&url=http%3A%2F%2Fwww.commercebank.com%2FPDFs%2Fconnections%2FACH-fileformats.pdf&ei=mb0VT5tdo6OIAo3lqNwN&usg=AFQjCNEI0pnOfN65qRpVtCLYWTIM4inKtQ&cad=rja
	Google: nacha 94 byte file format OR International ACH (IAT) NACHA File Formats

		File Header Record
			Batch Header Record
				First entry detail record
				Second entry detail record
				...
				Last entry detail record
			Batch Control Record
			Batch Header Record
				First entry detail record
				Second entry detail record
				...
				Last entry detail record
			Batch Control Record
		File Control Record

	Additional Data to pass:
	- Immediate Destination
	- Immediate Origin
	*/

	var $header_data = NULL;
	var $data = NULL;

	protected $batch_number = 1;

	/**
	 * EFT_File_Format_ACH constructor.
	 * @param null $header_data
	 * @param $data
	 */
	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_ACH Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	/**
	 * @return string
	 */
	private function compileFileHeader() {
		$line[] = '1'; //1 Record
		$line[] = '01'; //Priority code

		//NOTE: Some banks require this to have a leading blank space, or all 0's with a leading blank. If that is the case it will need to be defined specifically.
		//Banks seem to want DataCenter/Immediate Origin preceeded by a space, but 0 padded to 9 digits.
		//Some banks use 10 digits though, so check to see if its less than 10 and handle it differently.
		$line[] = $this->padRecord( str_pad( $this->getDataCenter(), 9, '0', STR_PAD_LEFT), 10, 'X'); //Immidiate destination - 10 digits, left padding with space. '072000805' - Standard Federal Bank
		$line[] = $this->padRecord( str_pad( $this->getOriginatorID(), 9, '0', STR_PAD_LEFT), 10, 'X'); //Immediate Origin - 10 digits, left padding with space. Recommend IRS Federal Tax ID Number

		$line[] = $this->padRecord( date('ymd', $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( date('Hi', $this->getFileCreationDate() ), 4, 'N');

		$line[] = $this->padRecord( 0, 1, 'N'); //0-9, input file ID modifier

		$line[] = $this->padRecord( 94, 3, 'N'); //94 byte records
		$line[] = $this->padRecord( 10, 2, 'N'); //Blocking factor
		$line[] = $this->padRecord( 1, 1, 'N'); //Format code

		$line[] = $this->padRecord( strtoupper( $this->getDataCenterName() ), 23, 'AN'); //Immidiate destination name. Optional
		$line[] = $this->padRecord( strtoupper( $this->getOriginatorShortName() ), 23, 'AN'); //Company Short Name. Optional

		$line[] = $this->padRecord( substr( $this->getFileCreationNumber(), -8 ), 8, 'AN'); //File Reference Code

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('File Header Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $type
	 * @param $business_number
	 * @param $service_code
	 * @param $entry_description
	 * @param int $due_date EPOCH
	 * @return string
	 */
	private function compileBatchHeader( $type, $business_number, $service_code, $entry_description, $due_date ) {
		$line[] = '5'; //5 Record

		if ( $type == 'CD' ) {
			$line[] = '200'; //Debits and Credits
		} elseif ( $type == 'D' ) {
			$line[] = '225'; //Debits Only
		} else {
			$line[] = '220'; //Credits Only
		}

		$line[] = $this->padRecord( strtoupper( $this->getOriginatorShortName() ), 16, 'AN'); //Company Short Name
		$line[] = $this->padRecord( '', 20, 'AN'); //Discretionary Data
		$line[] = $this->padRecord( $business_number, 10, 'N'); //Company Identification - Recommend IRS Federal Tax ID Number
		$line[] = $this->padRecord( $service_code, 3, 'AN'); //Standard Entry Class. (PPD, CCD, CTX, TEL, WEB)
		$line[] = $this->padRecord( $entry_description, 10, 'AN'); //Entry Description
		$line[] = $this->padRecord( date('ymd', $this->getFileCreationDate() ), 6, 'N'); //Date
		$line[] = $this->padRecord( date('ymd', $due_date ), 6, 'N'); //Date to post funds.
		$line[] = $this->padRecord( '', 3, 'AN'); //Blank
		$line[] = '1'; //Originator Status Code
		$line[] = $this->padRecord( $this->getInitialEntryNumber(), 8, 'N' ); //First 8 digits of InitialEntryNumber, which needs to match the beginning part of InitialEntry column of '6' records below. Used to be Originating ID or Originating Bank Transit
		$line[] = $this->padRecord( $this->batch_number, 7, 'N'); //Batch Number

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('Batch Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $type
	 * @param $record_count
	 * @param $batch_debit_amount
	 * @param $batch_credit_amount
	 * @param $hash
	 * @return string
	 */
	private function compileBatchControl( $type, $record_count, $batch_debit_amount, $batch_credit_amount, $hash ) {
		$line[] = '8'; //8 Record

		if ( $type == 'CD' ) {
			$line[] = '200'; //Debits and Credits
		} elseif ( $type == 'D' ) {
			$line[] = '225'; //Debits Only
		} else {
			$line[] = '220'; //Credits Only
		}

		$line[] = $this->padRecord( $record_count, 6, 'N'); //Entry and Addenda count.
		$line[] = $this->padRecord( substr( str_pad( $hash, 10, 0, STR_PAD_LEFT), -10), 10, 'N'); //Entry hash. If it exceeds 10 digits, use just the last 10.
		$line[] = $this->padRecord( $this->removeDecimal( $batch_debit_amount ), 12, 'N'); //Debit Total
		$line[] = $this->padRecord( $this->removeDecimal( $batch_credit_amount ), 12, 'N'); //Credit Total
		$line[] = $this->padRecord( $this->getBusinessNumber(), 10, 'N'); //Company Identification - Recommend IRS Federal Tax ID Number
		$line[] = $this->padRecord( '', 19, 'AN'); //Blank
		$line[] = $this->padRecord( '', 6, 'AN'); //Blank
		$line[] = $this->padRecord( $this->getInitialEntryNumber(), 8, 'N' ); //First 8 digits of InitialEntryNumber, which needs to match the beginning part of InitialEntry column of '6' records below. Used to be Originating ID or Originating Bank Transit
		$line[] = $this->padRecord( $this->batch_number, 7, 'N'); //Batch Number

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('Batch Control Record: '. $retval .' Count: '. $record_count .' Amount: Debit: '. $batch_debit_amount .' Credit: '. $batch_credit_amount .' BatchNum: '. $this->batch_number, __FILE__, __LINE__, __METHOD__, 10);

		$this->batch_number++;

		return $retval;
	}

	/**
	 * @param $records
	 * @return bool|string
	 */
	private function getRecordTypes( $records ) {
		$retval = FALSE;
		foreach( $records as $key => $record ) {
			if ( $retval == FALSE ) {
				$retval = $record->getType();
			} elseif ( $record->getType() != $retval ) {
				$retval = 'CD'; //Credits and Debits.
			}
		}

		return $retval;
	}

	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Batch records by business number, service code, entry description and due date. All fields in the batch header.
		$prev_batch_key = FALSE;
		$batch_id = 0;
		foreach( $this->data as $key => $record ) {
			$prev_batch_key = $record->getBatchKey();

			$batched_records[$batch_id][] = $record;

			if ( isset($this->data[($key + 1)]) AND ( $prev_batch_key == FALSE OR $prev_batch_key != $this->data[($key + 1)]->getBatchKey() ) ) {
				$batch_id++;
				Debug::Text('  Starting new batch: '. $batch_id .' Key: Prev: '. $prev_batch_key .' New: '. $this->data[($key + 1)]->getBatchKey(), __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::Text('  Continuing batch: '. $batch_id .' Key: Prev: '. $prev_batch_key, __FILE__, __LINE__, __METHOD__, 10);
			}
		}
		unset($prev_batch_key, $batch_id, $key, $record);

		$i = 1;
		$max = count($this->data);
		foreach( $batched_records as $batch_id => $batch_records ) {
			$batch_debit_amount = 0;
			$batch_credit_amount = 0;
			$batch_record_count = 0;
			$batch_hash = 0;

			$batch_record_types = $this->getRecordTypes( $batch_records );

			$retval[] = $this->compileBatchHeader( $batch_record_types, $batch_records[0]->getBusinessNumber(), $batch_records[0]->getServiceCode(), $batch_records[0]->getEntryDescription(), $batch_records[0]->getDueDate() );

			foreach( $batch_records as $key => $record ) {
				//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Institution: '. $record->getInstitution() .' Transit: '.$record->getTransit().' Bank Account Number: '. $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10);

				$line[] = '6'; //6 Record (PPD)

				//Transaction code used to default to 22 (checkings account) always.
				$transaction_type = substr( $record->getInstitution(), 0, 2);
				if ( (int)$transaction_type == 0 ) { //Institution defaults to '000' if its not set, so assume its a checkings account in that case.
					$transaction_type = 22;
				}

				if ( $record->getType() == 'D' ) {
					$transaction_type = 27; //Checking Account Debit.
				}

				$line[] = $this->padRecord( $transaction_type, 2, 'N'); //Transaction code - 22=Deposit destined for checking account, 32=Deposit destined for savings account

				$line[] = $this->padRecord( substr($record->getTransit(), 0, 8), 8, 'N'); //Transit
				$line[] = $this->padRecord( substr($record->getTransit(), 8, 1), 1, 'N'); //Check Digit
				$line[] = $this->padRecord( $record->getAccount(), 17, 'AN'); //Account number
				$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N'); //Amount
				$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 15, 'AN'); //transaction identification number
				$line[] = $this->padRecord( $record->getName(), 22, 'AN'); //Name of receiver
				$line[] = $this->padRecord( '', 2, 'AN'); //discretionary data
				$line[] = $this->padRecord( 0, 1, 'N'); //Addenda record indicator
				$line[] = $this->padRecord( $this->getInitialEntryNumber() . str_pad( $i, ( 15 - strlen($this->getInitialEntryNumber() ) ), 0, STR_PAD_LEFT), 15, 'N'); //Trace number. Bank assigns?

				$d_record = $this->padLine( implode('', $line), 94 );

				$retval[] = $d_record;

				if ( $record->getType() == 'D' ) {
					$batch_debit_amount += $record->getAmount();
				} else {
					$batch_credit_amount += $record->getAmount();
				}
				$batch_hash += substr($record->getTransit(), 0, 8);
				Debug::Text('PPD Record:'. $d_record .' - DueDate: '. $record->getDueDate() .' Batch Amount Debit: '. $batch_debit_amount.' Credit: '. $batch_credit_amount .' Length: '. strlen($d_record) .' Hash1: '. substr($record->getTransit(), 0, 8) .' Hash2: '. $batch_hash, __FILE__, __LINE__, __METHOD__, 10);

				unset($line);
				unset($d_record);
				$i++;
				$batch_record_count++;
			}

			//Add BatchControl Record Here
			//Because each batch only has a due date, only start a new batch if the DueDate changes.
			//Add batch record here
			//Close the previous batch before starting a new one.
			$retval[] = $this->compileBatchControl( $batch_record_types, $batch_record_count, $batch_debit_amount, $batch_credit_amount, $batch_hash );
		}

		if ( isset($retval) ) {
			//var_dump($retval);
			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	private function compileFileControl() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		$hash_total = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$c_record_count++;
			}

			if ( $record->getTransit() != '' ) {
				$hash_total += substr( $record->getTransit(), 0, 8 );
			}
		}
		$hash_total = substr( str_pad( $hash_total, 10, 0, STR_PAD_LEFT), -10); //Last 10 chars.

		$line[] = '9'; //9 Record
		$line[] = $this->padRecord( ($this->batch_number - 1), 6, 'N'); //Total number of batches

		/*
		Total count of output lines, including the first and last lines, divided by 10,
		rounded up to the nearest integer e.g. 99.9 becomes 100); 6 columns, zero-padded on
		the left.
		Total up: All C records, All D records, Total Batches (x2 lines each), plus FileHeader and FileControl.
		*/
		$block_count = ( ( ( $c_record_count + $d_record_count + ( ( $this->batch_number - 1 ) * 2 ) ) + 2 ) / 10 );
		Debug::Text('File Hash:'. $hash_total .' Batch Number: '. $this->batch_number .' Block Count: '. $block_count, __FILE__, __LINE__, __METHOD__, 10);

		$line[] = $this->padRecord( ceil( $block_count ), 6, 'N'); //Block count?!?!

		$line[] = $this->padRecord( ($c_record_count + $d_record_count), 8, 'N'); //Total entry count

		$line[] = $this->padRecord( $hash_total, 10, 'N'); //Entry hash
		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 12, 'N'); //Total Debit Amount
		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 12, 'N'); //Total Credit Amount

		$line[] = $this->padRecord( '', 39, 'AN'); //Blank

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('File Control Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $compiled_data
	 * @return null|string
	 */
	function compileFileControlPadding( $compiled_data ) {
		$total_records = substr_count( $compiled_data, "\n" );

		//Need to create batches of 94x10. 94 chars wide, 10lines long. So every file must be 10 lines, 20 lines, etc...
		$pad_lines = ( $total_records % 10 );
		if ( $pad_lines > 0 ) {
			$pad_lines = ( 10 - $pad_lines );
		}
		Debug::Text('File Control Record Padding: Total Records: '. $total_records .' Pad Lines: '. $pad_lines, __FILE__, __LINE__, __METHOD__, 10);

		for( $i = 0; $i < $pad_lines; $i++ ) {
			$line[] = $this->padLine( str_repeat(9, 94), 94 );
		}

		if ( isset($line) ) {
			return trim( implode('', $line) ); //Make sure there are no blank lines at the end, so if any file postfix line is appended there isn't a blank line inbetween.
		}

		return NULL;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = $this->compileFileHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFileControl();
		$compiled_data .= $this->compileFileControlPadding( $compiled_data );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= (94 * 3) ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_File_Format_BEANSTREAM Extends EFT {
	var $data = NULL;

	/**
	 * EFT_File_Format_BEANSTREAM constructor.
	 * @param null $data
	 */
	function __construct( $data ) {
		Debug::Text(' EFT_Format_BEANSTREAM Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->data = $data;

		return TRUE;
	}

	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			//Transaction method, EFT = E, ACH = A
			if ( $record->getInstitution() == '' ) {
				//ACH
				$line[] = 'A';
			} else {
				//EFT
				$line[] = 'E';
			}

			//Transaction type
			$line[] = $record->getType(); //C = Credit, D= Debit

			if ( $record->getInstitution() != '' ) {
				$line[] = $record->getInstitution();
			}
			$line[] = $record->getTransit();
			$line[] = $record->getAccount();

			if ( $record->getInstitution() == '' ) {
				$line[] = 'CC'; //Corporate Checking Account, for ACH only.
			}

			$line[] = $this->removeDecimal( $record->getAmount() );

			$line[] = $record->getOriginatorReferenceNumber();

			$line[] = $record->getName();

			$d_record = implode(',', $line);
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it.
		$compiled_data = @implode("\r\n", $this->compileRecords() );

		if ( strlen( $compiled_data ) >= 25 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}

?>
