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
 * @package Modules\PayStub
 */
class PayStubEntryFactory extends Factory {
	protected $table = 'pay_stub_entry';
	protected $pk_sequence_name = 'pay_stub_entry_id_seq'; //PK Sequence name

	protected $pay_stub_entry_account_obj = null;
	protected $pay_stub_obj = null;
	protected $ps_amendment_obj = null;

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                        => 'ID',
				'type_id'                   => false,
				'pay_stub_entry_account_id' => false,
				'name'                      => false,
				'pay_stub_id'               => 'PayStub',
				'rate'                      => 'Rate',
				'units'                     => 'Units',
				'ytd_units'                 => 'YTDUnits',
				'amount'                    => 'Amount',
				'ytd_amount'                => 'YTDAmount',
				'description'               => 'Description',
				'pay_stub_entry_name_id'    => 'PayStubEntryNameId',
				'pay_stub_amendment_id'     => 'PayStubAmendment',
				'user_expense_id'           => 'UserExpense',

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getPayStubEntryAccountObject() {
		return $this->getGenericObject( 'PayStubEntryAccountListFactory', $this->getPayStubEntryNameID(), 'pay_stub_entry_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayStubObject() {
		return $this->getGenericObject( 'PayStubListFactory', $this->getPayStub(), 'pay_stub_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayStubAmendmentObject() {
		return $this->getGenericObject( 'PayStubAmendmentListFactory', $this->getPayStubAmendment(), 'ps_amendment_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStub() {
		return $this->getGenericDataValue( 'pay_stub_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayStub( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_stub_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStubEntryNameId() {
		return $this->getGenericDataValue( 'pay_stub_entry_name_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayStubEntryNameId( $value ) {
		$value = TTUUID::castUUID( $value );

		Debug::text( 'Entry Account ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'pay_stub_entry_name_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStubAmendment() {
		return $this->getGenericDataValue( 'pay_stub_amendment_id' );
	}

	/**
	 * @param string $id UUID
	 * @param bool $start_date
	 * @param bool $end_date
	 * @return bool
	 */
	function setPayStubAmendment( $id, $start_date = false, $end_date = false ) {
		$id = TTUUID::castUUID( $id );

		if ( $id != TTUUID::getZeroID() ) {
			if ( $start_date == '' && $end_date == '' ) {
				$pay_stub_obj = $this->getPayStubObject();
				if ( is_object( $pay_stub_obj ) ) {
					$start_date = $pay_stub_obj->getStartDate();
					$end_date = $pay_stub_obj->getEndDate();
				} else {
					return false;
				}
				unset( $pay_stub_obj );
			}
		}

		Debug::text( 'PS Amendment ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		$psalf = TTnew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psalf */
		if ( $id == TTUUID::getZeroID()
				|| $this->Validator->isResultSetWithRows( 'pay_stub_amendment_id',
														  $psalf->getByIdAndStartDateAndEndDate( $id, $start_date, $end_date ),
														  TTi18n::gettext( 'Pay Stub Amendment effective date is after employees pay stub end date or termination date' )
				) ) {
			$this->setGenericDataValue( 'pay_stub_amendment_id', $id );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserExpense() {
		return $this->getGenericDataValue( 'user_expense_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUserExpense( $value ) {
		$value = TTUUID::castUUID( $value );

		Debug::text( 'User Expense ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( getTTProductEdition() <= TT_PRODUCT_CORPORATE ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'user_expense_id', $value );
	}

	/**
	 * @return null
	 */
	function getRate() {
		return $this->getGenericDataValue( 'rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRate( $value ) {
		$value = trim( $value );
		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'rate', Misc::MoneyRound( $value, 4 ) ); //DB schema limits to 4 decimal places, so make sure we round to that here, rather than let the DB truncate the value. This ensures the value in the DB always matches the value in memory, which is critical for calculating pay stub differences and avoiding being off by $0.01
	}

	/**
	 * @return null
	 */
	function getUnits() {
		return $this->getGenericDataValue( 'units' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUnits( $value ) {
		$value = trim( $value );

		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'units', Misc::MoneyRound( $value, 4 ) ); //DB schema limits to 4 decimal places, so make sure we round to that here, rather than let the DB truncate the value. This ensures the value in the DB always matches the value in memory, which is critical for calculating pay stub differences and avoiding being off by $0.01
	}

	/**
	 * @return null
	 */
	function getYTDUnits() {
		return $this->getGenericDataValue( 'ytd_units' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setYTDUnits( $value ) {
		$value = trim( $value );

		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'ytd_units', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableCalculateYTD() {
		if ( isset( $this->enable_calc_ytd ) ) {
			return $this->enable_calc_ytd;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalculateYTD( $bool ) {
		$this->enable_calc_ytd = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalculateAccrualBalance() {
		if ( isset( $this->enable_calc_accrual_balance ) ) {
			return $this->enable_calc_accrual_balance;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalculateAccrualBalance( $bool ) {
		$this->enable_calc_accrual_balance = $bool;

		return true;
	}

	/**
	 * @return null
	 */
	function getAmount() {
		return $this->getGenericDataValue( 'amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim( $value );

		//if ($value == NULL OR $value == '' OR $value < 0) {
		//Allow negative values for things like minusing vacation accural?
		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'amount', ( is_object( $this->getPayStubObject() ) && is_object( $this->getPayStubObject()->getCurrencyObject() ) ) ? $this->getPayStubObject()->getCurrencyObject()->round( $value ) : Misc::MoneyFormat( $value, false ) );
	}

	/**
	 * @return null
	 */
	function getYTDAmount() {
		return $this->getGenericDataValue( 'ytd_amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setYTDAmount( $value ) {
		$value = trim( $value );
		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'ytd_amount', ( is_object( $this->getPayStubObject() ) && is_object( $this->getPayStubObject()->getCurrencyObject() ) ) ? $this->getPayStubObject()->getCurrencyObject()->round( $value ) : Misc::MoneyFormat( $value, false ) );
	}

	/**
	 * @return mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', htmlspecialchars( $value ) );
	}

	/**
	 * @return bool
	 */
	function preSave() {
		Debug::text( 'Pay Stub ID: ' . $this->getPayStub() . ' Amount: '. $this->getAmount() .' Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' PS Account: '. $this->getPayStubEntryNameId() .' PSA ID: '. $this->getPayStubAmendment() .' Expense: '. $this->getUserExpense() .' Calc YTD: ' . (int)$this->getEnableCalculateYTD(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getYTDAmount() == false ) {
			$this->setYTDAmount( 0 );
		}

		if ( $this->getYTDUnits() == false ) {
			$this->setYTDUnits( 0 );
		}

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Pay Stub
		if ( $this->getPayStub() !== false ) {
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$this->Validator->isResultSetWithRows( 'pay_stub',
												   $pslf->getByID( $this->getPayStub() ),
												   TTi18n::gettext( 'Invalid Pay Stub' )
			);
		}
		// Entry Account Id
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$this->Validator->isResultSetWithRows( 'pay_stub_entry_name_id',
											   $psealf->getById( $this->getPayStubEntryNameId() ),
											   TTi18n::gettext( 'Invalid Pay Stub Account' )
		);
		// Expense
		if ( $this->getUserExpense() !== false && $this->getUserExpense() != TTUUID::getZeroID() ) {
			$uelf = TTnew( 'UserExpenseListFactory' ); /** @var UserExpenseListFactory $uelf */
			$result = $uelf->getById( $this->getUserExpense() );
			$this->Validator->isResultSetWithRows( 'user_expense_id',
												   $result,
												   TTi18n::gettext( 'Invalid Expense' )
			);
		}
		// Rate
		if ( $this->getRate() != '' ) {
			$this->Validator->isFloat( 'rate',
									   $this->getRate(),
									   TTi18n::gettext( 'Invalid Rate' )
			);
			if ( $this->Validator->isError( 'rate' ) == false ) {
				$this->Validator->isLength( 'rate',
											$this->getRate(),
											TTi18n::gettext( 'Rate has too many digits' ),
											0,
											21 ); //Need to include decimal.
			}
			if ( $this->Validator->isError( 'rate' ) == false ) {
				$this->Validator->isLengthBeforeDecimal( 'rate',
														 $this->getRate(),
														 TTi18n::gettext( 'Rate has too many digits before the decimal' ),
														 0,
														 16
				);
			}
		}
		// Units
		if ( $this->getUnits() != '' ) {
			$this->Validator->isFloat( 'units',
									   $this->getUnits(),
									   TTi18n::gettext( 'Invalid Units' )
			);
			if ( $this->Validator->isError( 'units' ) == false ) {
				$this->Validator->isLength( 'units',
											$this->getUnits(),
											TTi18n::gettext( 'Units has too many digits' ),
											0,
											21
				); //Need to include decimal
			}
			if ( $this->Validator->isError( 'units' ) == false ) {
				$this->Validator->isLengthBeforeDecimal( 'units',
														 $this->getUnits(),
														 TTi18n::gettext( 'Units has too many digits before the decimal' ),
														 0,
														 16
				);
			}
		}
		// YTD Units
		if ( $this->getYTDUnits() !== false ) {
			$this->Validator->isFloat( 'ytd_units',
									   $this->getYTDUnits(),
									   TTi18n::gettext( 'Invalid YTD Units' )
			);
		}
		// Amount
		if ( $this->getAmount() !== false ) {
			$this->Validator->isFloat( 'amount',
									   $this->getAmount(),
									   TTi18n::gettext( 'Invalid Amount' )
			);
			if ( $this->Validator->isError( 'amount' ) == false ) {
				$this->Validator->isLength( 'amount',
											$this->getAmount(),
											TTi18n::gettext( 'Amount has too many digits' ),
											0,
											21
				); //Need to include decimal
			}
			if ( $this->Validator->isError( 'amount' ) == false ) {
				$this->Validator->isLengthBeforeDecimal( 'amount',
														 $this->getAmount(),
														 TTi18n::gettext( 'Amount has too many digits before the decimal' ),
														 0,
														 16
				);
			}
		}
		// YTD Amount
		if ( $this->getYTDAmount() !== false ) {
			$this->Validator->isFloat( 'ytd_amount',
									   $this->getYTDAmount(),
									   TTi18n::gettext( 'Invalid YTD Amount' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Invalid Description Length' ),
										2,
										100
			);
		}


		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Calc YTD values if they aren't already done.
		if ( $this->getYTDAmount() == null || $this->getYTDUnits() == null ) {
			$this->preSave();
		}

		//Make sure rate * units = amount

		if ( $this->getAmount() === null ) {
			//var_dump( $this->getAmount() );
			$this->Validator->isTrue( 'amount',
									  false,
									  TTi18n::gettext( 'Invalid Amount' ) );
		}

		if ( $this->getPayStubEntryNameId() == '' ) {
			Debug::text( 'PayStubEntryNameID is NULL: ', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->isTrue( 'pay_stub_entry_name_id',
									  false,
									  TTi18n::gettext( 'Invalid Pay Stub Account' ) );
		}

		/*
		//Allow just units to be set. For cases like Gross Pay Units.
		//Make sure Units isn't set if Rate is
		if ( $this->getRate() != NULL AND $this->getUnits() == NULL ) {
			$this->Validator->isTrue(		'units',
											FALSE,
											TTi18n::gettext('Invalid Units'));
		}

		if ( $this->getUnits() != NULL AND $this->getRate() == NULL ) {
			$this->Validator->isTrue(		'rate',
											FALSE,
											TTi18n::gettext('Invalid Rate'));
		}
		*/

		/*
		//FIXME: For some reason the calculation done here has one less decimal digit then
		//the calculation done in Wage::getOverTime2Wage().
		if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL
				AND ( $this->getRate() * $this->getUnits() ) != $this->getAmount() ) {
			Debug::text('Validate: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Amount: '. $this->getAmount() .' Calc: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Total: '. ( $this->getRate() * $this->getUnits() ), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'amount',
											FALSE,
											TTi18n::gettext('Invalid Amount, calculation is incorrect.'));
		}
		*/
		//Make sure YTD values are set
		//YTD could be 0 though if we "cancel" out a entry like vacation accrual.
		if ( $this->getYTDAmount() === null ) {
			Debug::text( 'getYTDAmount is NULL: ', __FILE__, __LINE__, __METHOD__, 10 );
			//var_dump( $this );

			$this->Validator->isTrue( 'ytd_amount',
									  false,
									  TTi18n::gettext( 'Invalid YTD Amount' ) );
		}

		if ( $this->getYTDUnits() === null ) {
			$this->Validator->isTrue( 'ytd_units',
									  false,
									  TTi18n::gettext( 'Invalid YTD Units' ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//If this entry is based off pay stub amendment, mark
		//PS amendment as "ACTIVE" status.
		//Once PS is paid, mark them as PAID.

		//If Pay Stub Account is attached to an accrual, handle that now.
		//Only calculate accrual if this is a new pay stub entry, not if we're
		//editing one, so we don't duplicate the accrual entry.
		//
		// **Handle this in PayStubFactory instead.
		//
		/*
		//This all handled in PayStubFactory::addEntry() now.
		if ( $this->getEnableCalculateAccrualBalance() == TRUE
				AND $this->getPayStubEntryAccountObject() != FALSE
				AND $this->getPayStubEntryAccountObject()->getAccrual() != FALSE
				AND $this->getPayStubEntryAccountObject()->getAccrual() != 0
				) {
			Debug::text('Pay Stub Account is linked to an accrual...', __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPayStubEntryAccountObject()->getType() == 10 ) {
				$amount = $this->getAmount()*-1; //This is an earning... Reduce accrual
			} elseif ( $this->getPayStubEntryAccountObject()->getType() == 20 ) {
				$amount = $this->getAmount(); //This is a employee deduction, add to accrual.
			}
			Debug::text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

			if ( $amount != 0 ) {
				//Add entry to do the opposite to the accrual.
				$psef = TTnew( 'PayStubEntryFactory' );
				$psef->setPayStub( $this->getPayStub() );
				$psef->setPayStubEntryNameId( $this->getPayStubEntryAccountObject()->getAccrual() );
				$psef->setAmount( $amount );

				return $psef->Save();
			}
		} else {
			Debug::text('Pay Stub Account is NOT linked to an accrual...', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

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

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'pay_stub_entry_account_id':
						case 'type_id':
						case 'name':
							$data[$variable] = $this->getColumn( $variable );
							break;
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
		return TTLog::addEntry( $this->getPayStub(), $log_action, TTi18n::getText( 'Pay Stub Entry' ) . ': ' . $this->getPayStubEntryAccountObject()->getName() . ': ' . TTi18n::getText( 'Amount' ) . ': ' . $this->getAmount(), null, $this->getTable(), $this );
	}
}

?>
