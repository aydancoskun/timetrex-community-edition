<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
 * @package PayrollDeduction\CR
 */
class PayrollDeduction_CR_Data extends PayrollDeduction_Base {
	var $db = NULL;
	var $income_tax_rates = array();
	var $country_primary_currency = 'CRC';

	var $federal_income_tax_rate_options = array(
												20070930 => array(
															10 => array(
																	array( 'income' => 6096000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 9144000,	'rate' => 10,	'constant' => 0 ),
																	array( 'income' => 9144000,	'rate' => 15,	'constant' => 0 ),
																	),
															),
												20060930 => array(
															10 => array(
																	array( 'income' => 5616000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 8424000,	'rate' => 10,	'constant' => 0 ),
																	array( 'income' => 8424000,	'rate' => 15,	'constant' => 0 ),
																),
															),
												);

	var $federal_allowance = array(
									20060930 => 10560.00, //01-Oct-07
									20070930 => 11520.00  //01-Oct-07
								);

	var $federal_filing = array(
									20060930 => 15720.00, //01-Oct-07
									20070930 => 17040.00  //01-Oct-07
								);

	function __construct() {
		global $db;

		$this->db = $db;

		return TRUE;
	}

	function getData() {
		global $cache;

		$country = $this->getCountry();

		$epoch = $this->getDate();
		$federal_status = $this->getFederalFilingStatus();
		if ( $federal_status == '' ) {
			$federal_status = 10;
		}

		if ($epoch == NULL OR $epoch == ''){
			$epoch = $this->getISODate( TTDate::getTime() );
		}

		$this->income_tax_rates = FALSE;
		if ( isset($this->federal_income_tax_rate_options) AND count($this->federal_income_tax_rate_options) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$federal_income_tax_rate_options = $this->getDataFromRateArray($epoch, $this->federal_income_tax_rate_options );
			if ( isset($federal_income_tax_rate_options[$federal_status]) ) {
				foreach( $federal_income_tax_rate_options[$federal_status] as $data ) {
					$this->income_tax_rates['federal'][] = array(
															'prev_income' => $prev_income,
															'income' => $data['income'],
															'prev_rate' => ( $prev_rate / 100 ),
															'rate' => ( $data['rate'] / 100 ),
															'prev_constant' => $prev_constant,
															'constant' => $data['constant']
															);

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset($prev_income, $prev_rate, $prev_constant, $data, $federal_income_tax_rate_options);
		}
				
		return $this;
	}

	function getFederalTaxTable($income) {
		$arr = $this->income_tax_rates['federal'];

		//Debug::Arr($arr, 'Federal tax table: ', __FILE__, __LINE__, __METHOD__, 10);
		return $arr;
	}

	function getFederalAllowanceAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_allowance);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}

	function getFederalFilingAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_filing);

		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}

}
?>
