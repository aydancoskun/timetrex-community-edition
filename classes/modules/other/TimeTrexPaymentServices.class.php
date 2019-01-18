<?php
class TimeTrexPaymentServices {
	protected $url = 'https://paymentservices.timetrex.com/api/soap/api.php';

	protected $user_name = NULL;
	protected $password = NULL;

	/**
	 * Constructor.
	 * @param string $user_name
	 * @param string $password
	 */
	function __construct( $user_name = NULL, $password = NULL ) {
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'other'. DIRECTORY_SEPARATOR .'PaymentServicesClientAPI.class.php' );

		global $PAYMENTSERVICES_USER, $PAYMENTSERVICES_PASSWORD, $PAYMENTSERVICES_URL;
		$PAYMENTSERVICES_USER = $user_name;
		$PAYMENTSERVICES_PASSWORD = $password;
		$PAYMENTSERVICES_URL = $this->url;

		return TRUE;
	}

	/**
	 * Converts a remittance source account object to a remittance bank account array for uploading.
	 * @param $rs_obj
	 * @return array
	 */
	function convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj ) {
		$remittances_bank_account_data = array(
				'_kind' => 'BankAccount',
				'remote_id' => $rs_obj->getID(),

				'type_id'   => 'S', //Settlement

				'name' => $rs_obj->getName(),

				'domiciled_country' => $rs_obj->getCountry(),
				'currency_iso_code' => $rs_obj->getCurrencyObject()->getISOCode(),

				'bank_account_type'   => 'C', //Checking
				'bank_routing_number' => ( $rs_obj->getCountry() == 'CA' ) ? str_pad( $rs_obj->getValue1(), 4, 0, STR_PAD_LEFT ) . str_pad( $rs_obj->getValue2(), 5, 0, STR_PAD_LEFT ) : $rs_obj->getValue2(),
				'bank_account_number' => $rs_obj->getValue3(),

				'deleted' => $rs_obj->getDeleted(),
		);

		Debug::Arr( $remittances_bank_account_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $remittances_bank_account_data;
	}

	/**
	 * Converts a remittance agency object to a agency authorization array for uploading.
	 * @param $rae_obj
	 * @return array
	 */
	function convertRemittanceAgencyEventObjectToAgencyAuthorizationArray( $rae_obj ) {
		if ( !is_object( $rae_obj ) ) {
			return FALSE;
		}

		$remittance_agency_data = array(
				'_kind' => 'AgencyAuthorization',
				'remote_id' => $rae_obj->getID(),

				//'status_id'   => 'P', //Pending Authorization -- This is handled automatically on the remote end.
				'form_type_id' => $rae_obj->getType(),
				'frequency_id' => $rae_obj->getFrequency(),
				'agency_id'   => $rae_obj->getPayrollRemittanceAgencyObject()->getAgency(),

				'primary_identification' => $rae_obj->getPayrollRemittanceAgencyObject()->getPrimaryIdentification(),
				'secondary_identification' => $rae_obj->getPayrollRemittanceAgencyObject()->getSecondaryIdentification(),
				'tertiary_identification' => $rae_obj->getPayrollRemittanceAgencyObject()->getTertiaryIdentification(),

				'deleted' => $rae_obj->getDeleted(),
		);

		Debug::Arr( $remittance_agency_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $remittance_agency_data;
	}

	/**
	 * Converts a remittance destination account to a remittance bank account array for uploading.
	 * @param $rd_obj
	 * @param $rs_obj
	 * @param $u_obj
	 * @return array
	 */
	function convertRemittanceDestinationAccountObjectToBankAccountArray( $rd_obj, $rs_obj, $u_obj ) {
		$country = $rs_obj->getCountry();

		$remittances_bank_account_data = array(
				'_kind' => 'BankAccount',
				'remote_id' => $rd_obj->getID(),

				'type_id'   => 'N', //Normal

				'name' => $u_obj->getFullName( TRUE ),

				'domiciled_country' => $country,
				'currency_iso_code' => $rd_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->getISOCode(),

				'bank_account_type'   => ( $rd_obj->getValue1() == '32' ) ? 'S' : 'C', //S=Savings, C=Checking
				'bank_routing_number' => ( $country == 'CA' ) ? str_pad( $rd_obj->getValue1(), 4, 0, STR_PAD_LEFT ) . str_pad( $rd_obj->getValue2(), 5, 0, STR_PAD_LEFT ) : $rd_obj->getValue2(),
				'bank_account_number' => $rd_obj->getValue3(),

				'deleted' => $rd_obj->getDeleted(),
		);

		Debug::Arr( $remittances_bank_account_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $remittances_bank_account_data;
	}


	function generateBatchID( $end_date, $run_id = NULL ) {
		//APR 02 R01 -- Only the first 7 characters are shown on settlement transactions.
		//was: PP APR02 R01
		//$batch_id = 'PP '. date( 'Md', $end_date ) .' R'. str_pad( $run_id, 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		$batch_id = date( 'M d', $end_date );

		if ( $run_id != '' ) {
			$batch_id .= ' R'. str_pad( $run_id, 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		}

		Debug::Text( 'Batch ID: '. $batch_id, __FILE__, __LINE__, __METHOD__, 10 );

		return $batch_id;
	}

	/**
	 * Converts Pay Stub Transaction objects to a remittance transaction array for uploading.
	 * @param $pst_obj
	 * @param $ps_obj
	 * @param $rs_obj
	 * @param $uf_obj
	 * @param $confirmation_number
	 * @param $batch_id
	 * @return array
	 */
	function convertPayStubTransactionObjectToTransactionArray( $pst_obj, $ps_obj, $rs_obj, $uf_obj, $confirmation_number, $batch_id ) {
		$settlement_bank_account_data = $this->convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj );
		$bank_account_data = $this->convertRemittanceDestinationAccountObjectToBankAccountArray( $pst_obj->getRemittanceDestinationAccountObject(), $rs_obj, $uf_obj );

		if ( is_object( $ps_obj->getPayPeriodObject() ) ) {
			//PP APR 02R01
			$batch_id = $this->generateBatchID( $ps_obj->getPayPeriodObject()->getEndDate(), $ps_obj->getRun() );
			//$batch_id = 'PP '. date( 'M d', $ps_obj->getPayPeriodObject()->getEndDate() ) .'R'. str_pad( $ps_obj->getRun(), 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		}

		$remittances_transaction_data = array(
				'_kind' => 'Transaction',
				'remote_id' => $pst_obj->getID(),
				'remote_batch_id' => $batch_id,

				'settlement_bank_account_id' => $settlement_bank_account_data,
				'bank_account_id' => $bank_account_data,

				'category_id'   => 'DD', //Direct Deposit
				'type_id' => 'C', //Credit

				'name' => $uf_obj->getFullName( TRUE ),
				'reference_number' => $confirmation_number,
				'due_date' => TTDate::getISOTimeStamp( $ps_obj->getTransactionDate() ), //Don't pass epoch as the remote system won't know the timezone.

				'amount' => $pst_obj->getAmount(),
		);

		Debug::Arr( $remittances_transaction_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $remittances_transaction_data;
	}

	/**
	 * Converts a legal entity object to a remittance organization array for uploading.
	 * @param $le_obj
	 * @return array
	 */
	function convertLegalEntityObjectToOrganizationArray( $le_obj ) {
		$validator = new Validator();

		$license = new TTLicense();

		$primary_identification = NULL;

		//Get federal remittance agency in an attempt to get EIN/Business number.
		$filter_data['legal_entity_id'] = $le_obj->getId();

		if ( strtoupper( $le_obj->getCountry() ) == 'CA' ) {
			$filter_data['agency_id'] = array('10:CA:00:00:0010'); //CA federal
		} elseif ( strtoupper( $le_obj->getCountry() ) == 'US' ) {
			$filter_data['agency_id'] = array('10:US:00:00:0010'); //US federal
		}

		/** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $le_obj->getCompany(), $filter_data );
		if ( $ralf->getRecordCount() > 0 ) {
			$ra_obj = $ralf->getCurrent();

			$primary_identification = $ra_obj->getPrimaryIdentification();
		}

		$legal_entity_data = array(
				'_kind' => 'Organization',

				'remote_id' => $le_obj->getID(),

				'legal_name' => $le_obj->getLegalName(),
				'trade_name' => $le_obj->getTradeName(),
				'short_name' => ( $le_obj->getShortName() != '' ) ? $le_obj->getShortName() : substr( $validator->stripNonAlphaNumeric( trim( $le_obj->getTradeName() ) ), 0, 15 ), //Short was recently added to legal entities, so if its not defined, use the first 15 chars of trade name instead.
				'primary_identification' => $primary_identification, //Obtain from federal remittance agency

				'address1' => $le_obj->getAddress1(),
				'address2' => $le_obj->getAddress2(),
				'city' => $le_obj->getCity(),
				'province' => $le_obj->getProvince(),
				'country' => $le_obj->getCountry(),
				'postal_code' => $le_obj->getPostalCode(),
				'work_phone' => $le_obj->getWorkPhone(),

				'extra_data' => array( 'company_name' => $le_obj->getCompanyObject()->getName(),
									   'registration_key' => SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' ),
									   'hardware_id' => $license->getHardwareID(),
									   'company_id' => $le_obj->getCompany(),
									   'legal_entity_id' => $le_obj->getId() ),

				'deleted' => $le_obj->getDeleted(),
		);

		Debug::Arr( $legal_entity_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $legal_entity_data;
	}

	/**
	 * Converts a user object to a remittance user array for uploading.
	 * @param $u_obj
	 * @return array
	 */
	function convertUserObjectToUserArray( $u_obj, $remote_organization_id = NULL ) {
		$user_data = array(
				'_kind' => 'User',
				'remote_id' => $u_obj->getID(),

				'user_name' => $u_obj->getUserName(),

				'first_name' => $u_obj->getFirstName(),
				'middle_name' => $u_obj->getMiddleName(),
				'last_name' => $u_obj->getLastName(),

				'address1' => $u_obj->getAddress1(),
				'address2' => $u_obj->getAddress2(),
				'city' => $u_obj->getCity(),
				'province' => $u_obj->getProvince(),
				'country' => $u_obj->getCountry(),
				'postal_code' => $u_obj->getPostalCode(),
				'work_phone' => $u_obj->getWorkPhone(),
				'work_phone_ext' => $u_obj->getWorkPhoneExt(),
				'home_phone' => $u_obj->getHomePhone(),
				'mobile_phone' => $u_obj->getMobilePhone(),

				'birth_date' => $u_obj->getBirthDate(),
				'sin' => $u_obj->getSIN(),

				'work_email' => $u_obj->getWorkEmail(),
				'home_email' => $u_obj->getHomeEmail(),

				'deleted' => $u_obj->getDeleted(),
		);

		if ( $remote_organization_id != '' ) {
			$user_data['organization_id'] = $remote_organization_id;
		}

		Debug::Arr( $user_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $user_data;
	}

	/**
	 * Converts Pay Stub Transaction objects to a remittance transaction array for uploading.
	 * @param $pst_obj
	 * @param $ps_obj
	 * @param $rs_obj
	 * @param $uf_obj
	 * @param $confirmation_number
	 * @param $batch_id
	 * @return array
	 */
	function convertReportPaymentServicesDataToAgencyReportArray( $report_data, $rae_obj, $ra_obj, $rs_obj, $user_obj, $pay_period_start_date, $pay_period_end_date, $pay_period_transaction_date, $pay_period_run, $batch_id, $remote_id, $type_id = 'D' ) {
		$settlement_bank_account_data = $this->convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj );

		if ( isset($report_data['object']) ) {
			Debug::Text( 'Report Object: ' . $report_data['object'], __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $report_data['object'] ) {
				case 'RemittanceSummaryReport':
					$standard_report_data = array(
							'total_employees' => $report_data['employees'],
							'subject_wages' => $report_data['gross_payroll'],
							'taxable_wages' => $report_data['gross_payroll'],
							'amount_withheld' => $report_data['total'],
							'amount_due' => $report_data['total'],
							'due_date' => $report_data['due_date'],
					);
					break;
				case 'TaxSummaryReport':
				default:
					$standard_report_data = $report_data;
					break;
			}
		}

		$remittances_transaction_data = array(
				'_kind' => 'AgencyReport',

				'agency_id'   => $rae_obj->getPayrollRemittanceAgencyObject()->getAgency(),
				//'agency_organization_id' => $ra_obj->getId(),

				'settlement_bank_account_id' => $settlement_bank_account_data,

				'status_id'   => 'P', //Pending
				'type_id'     => $type_id, //Deposit/Estimate
				'form_type_id' => $rae_obj->getType(),

				'pay_period_start_date' => $pay_period_start_date,
				'pay_period_end_date' => $pay_period_end_date,
				'pay_period_transaction_date' => $pay_period_transaction_date,
				'pay_period_run' => $pay_period_run,

				'period_start_date' => $rae_obj->getStartDate(), //$pay_period_start_date,
				'period_end_date' => $rae_obj->getEndDate(),
				'due_date' => $rae_obj->getDueDate(),

				'total_employees' => (int)$standard_report_data['total_employees'],
				'subject_wages' => $standard_report_data['subject_wages'],
				'taxable_wages' => $standard_report_data['taxable_wages'],
				'amount_withheld' => $standard_report_data['amount_withheld'],
				'amount_due' => $standard_report_data['amount_due'],

				'extra_data' => $report_data,

				'frequency_id' => $rae_obj->getFrequency(),

				'remote_id' => $remote_id, //TTUUID::convertStringToUUID( md5( $rae_obj->getId() . $batch_id ) ), //Try to get consistent IDs based on the event and batch_id, so mulitple submissions get uploaded.
				'remote_batch_id' => $batch_id,
		);

		Debug::Arr( $remittances_transaction_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $remittances_transaction_data;
	}


	/**
	 * Uploads organization data to TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setOrganization( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		foreach( $rows as $row ) {
			$api = new PaymentServicesClientAPI( 'APIOrganization' );
			$api_result = $api->setOrganization( $row );
			if ( $api_result !== FALSE ) {
				if ( $api_result->isValid() === TRUE ) {
					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

					return FALSE; //This will trigger a general error to the user.
				}
			} else {
				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return FALSE; //This will trigger a general error to the user.
			}
		}

		return TRUE;
	}

	/**
	 * Uploads organization data to TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setUser( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		foreach( $rows as $row ) {
			$api = new PaymentServicesClientAPI( 'APIUser' );
			$api_result = $api->setUser( $row );
			if ( $api_result !== FALSE ) {
				if ( $api_result->isValid() === TRUE ) {
					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

					return FALSE; //This will trigger a general error to the user.
				}
			} else {
				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return FALSE; //This will trigger a general error to the user.
			}
		}

		return TRUE;
	}

	//Create organization from Legal Entity
	function createNewOrganization( $row ) {
		if ( isset( $row['_kind'] ) ) {
			$row = array( $row );
		}

		$api = new PaymentServicesClientAPI('APIAuthentication' ); //Need to do this before logging in.
		$api_result = $api->setNewOrganization( $row );
		if ( $api_result !== FALSE ) {
			if ( $api_result->isValid() === TRUE ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return $api_result->getResult(); //Return the ID so we can link a user to it.
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return FALSE; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return FALSE; //This will trigger a general error to the user.
		}

		return TRUE;
	}

	function createNewUser( $row ) {
		if ( isset( $row['_kind'] ) ) {
			$row = array( $row );
		}

		$api = new PaymentServicesClientAPI('APIAuthentication' ); //Need to do this before logging in.
		$api_result = $api->setNewUser( $row );
		if ( $api_result !== FALSE ) {
			if ( $api_result->isValid() === TRUE ) {
				Debug::Arr( $api_result->getResult(), 'PaymentServices API: Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
				return $api_result->getResult(); //Return the ID so we can link a user to it.
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return FALSE; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return FALSE; //This will trigger a general error to the user.
		}

		return TRUE;
	}


	/**
	 * Deep validation of bank account.
	 * @param $row
	 * @return bool
	 */
	function validateBankAccount( $row ) {
		$api = new PaymentServicesClientAPI( 'APIBankAccount' );
		$api_result = $api->validateBankAccount( $row );
		if ( $api_result !== FALSE ) {
			if ( $api_result->isValid() === TRUE ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return TRUE;
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return FALSE; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return FALSE; //This will trigger a general error to the user.
		}

		return FALSE;
	}


	/**
	 * Updates the remote remittance bank account information.
	 * @param $rows
	 * @return bool
	 */
	function setRemittanceSourceAccount( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		foreach( $rows as $row ) {
			if ( isset( $row['deleted'] ) AND $row['deleted'] == TRUE ) {
				$api = new PaymentServicesClientAPI( 'APIBankAccount' );
				$api_result = $api->getBankAccount( array( 'filter_data' => array( 'remote_id' => $row['remote_id'] ) ) );
				if ( $api_result->isValid() === TRUE ) {
					$bank_account_id = $api_result->getResult()[0]['id'];
					Debug::Text( 'PaymentServices API: Bank Account ID: ' . $bank_account_id, __FILE__, __LINE__, __METHOD__, 10 );

					$api_result = $api->deleteBankAccount( $bank_account_id );
					if ( $api_result->isValid() !== TRUE ) {
						Debug::Arr( $api_result->getResult(), 'PaymentServices API: Failed deleting Bank Account ID: '. $bank_account_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				$api = new PaymentServicesClientAPI( 'APIBankAccount' );
				$api_result = $api->setBankAccount( $row );
				if ( $api_result !== FALSE ) {
					if ( $api_result->isValid() === TRUE ) {
						Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

						return FALSE; //This will trigger a general error to the user.
					}
				} else {
					//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					return FALSE; //This will trigger a general error to the user.
				}
			}
		}

		return TRUE;
	}

	/**
	 * Updates the remote remittance agency information.
	 * @param $rows
	 * @return bool
	 */
	function setAgencyAuthorization( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		foreach( $rows as $row ) {
			if ( isset( $row['deleted'] ) AND $row['deleted'] == TRUE ) {
				$api = new PaymentServicesClientAPI( 'APIAgencyAuthorization' );
				$api_result = $api->getAgencyAuthorization( array( 'filter_data' => array( 'remote_id' => $row['remote_id'] ) ) );
				if ( $api_result->isValid() === TRUE ) {
					$agency_authorization_id = $api_result->getResult()[0]['id'];
					Debug::Text( 'PaymentServices API: Agency Authorization ID: ' . $agency_authorization_id, __FILE__, __LINE__, __METHOD__, 10 );

					$api_result = $api->deleteAgencyAuthorization( $agency_authorization_id );
					if ( $api_result->isValid() !== TRUE ) {
						Debug::Arr( $api_result->getResult(), 'PaymentServices API: Failed deleting Agency Authorization ID: '. $agency_authorization_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				$api = new PaymentServicesClientAPI( 'APIAgencyAuthorization' );
				$api_result = $api->setAgencyAuthorization( $row );
				if ( $api_result !== FALSE ) {
					if ( $api_result->isValid() === TRUE ) {
						Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

						return FALSE; //This will trigger a general error to the user.
					}
				} else {
					//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					return FALSE; //This will trigger a general error to the user.
				}
			}
		}

		return TRUE;
	}

	/**
	 * Uploads Agency Report for processing through TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setAgencyReport( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		$api = new PaymentServicesClientAPI( 'APIAgencyReport' );
		$api_result = $api->setAgencyReport( $rows );
		return $api_result;

//		foreach( $rows as $row ) {
//			$api = new PaymentServicesClientAPI( 'APIAgencyReport' );
//			$api_result = $api->setAgencyReport( $row );
//			if ( $api_result !== FALSE ) {
//				if ( $api_result->isValid() === TRUE ) {
//					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//				} else {
//					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//
//					return FALSE; //This will trigger a general error to the user.
//				}
//			} else {
//				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//				return FALSE; //This will trigger a general error to the user.
//			}
//		}

		return TRUE;
	}


	/**
	 * Uploads Pay Stub transactions for processing through TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setPayStubTransaction( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = array( $rows );
		}

		$api = new PaymentServicesClientAPI( 'APITransaction' );
		$api_result = $api->setTransaction( $rows );
		return $api_result;

//		foreach( $rows as $row ) {
//			$api = new PaymentServicesClientAPI( 'APITransaction' );
//			$api_result = $api->setTransaction( $row );
//			if ( $api_result !== FALSE ) {
//				if ( $api_result->isValid() === TRUE ) {
//					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//				} else {
//					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//
//					return FALSE; //This will trigger a general error to the user.
//				}
//			} else {
//				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
//				return FALSE; //This will trigger a general error to the user.
//			}
//		}

		return TRUE;
	}

	function ping() {
		$api = new PaymentServicesClientAPI( 'APIAuthentication' );
		return $api->ping();
	}
}
?>