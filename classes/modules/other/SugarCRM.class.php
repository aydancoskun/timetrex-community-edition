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
 * Class SugarCRM
 */
class SugarCRM {
	private $soap_client_obj = null;
	private $session_id = null;

	private $sugarcrm_url = null;
	private $sugarcrm_user_name = null;
	private $sugarcrm_password = null;

	/**
	 * SugarCRM constructor.
	 * @param null $url
	 */
	function __construct( $url = null ) {
		if ( $url != '' ) {
			$this->sugarcrm_url = $url;
		}
	}

	/**
	 * @return null|SoapClient
	 */
	function getSoapObject() {
		if ( $this->soap_client_obj == null ) {
			ini_set( 'default_socket_timeout', 300 ); //This helps prevent "Error fetching HTTP headers" SOAP error.
			$this->soap_client_obj = new SoapClient( null, [
																 'location'           => $this->sugarcrm_url,
																 'uri'                => 'urn:http://www.sugarcrm.com/sugarcrm',
																 'style'              => SOAP_RPC,
																 'use'                => SOAP_ENCODED,
																 'encoding'           => 'UTF-8',
																 'connection_timeout' => 30,
																 'keep_alive'         => false, //This prevents "Error fetching HTTP headers" SOAP error.
																 'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
																 'trace'              => 1,
																 'exceptions'         => 0,
														 ]
			);
		}

		return $this->soap_client_obj;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function convertToNameValueList( $data ) {
		if ( is_array( $data ) ) {

			$retarr = [];
			foreach ( $data as $key => $value ) {
				$row = new stdClass();
				$row->name = $key;
				$row->value = $value;
				$retarr[] = $row;
				unset( $row );
			}

			return $retarr;
		}

		return false;
	}

	/**
	 * @param null $user_name
	 * @param null $password
	 * @return bool
	 */
	function login( $user_name = null, $password = null ) {
		if ( $user_name == '' ) {
			$user_name = $this->sugarcrm_user_name;
		}

		if ( $password == '' ) {
			$password = $this->sugarcrm_password;
		}
		$user_auth = [
				'user_name' => $user_name,
				'password'  => md5( $password ),
				'version'   => '0.1',
		];
		$result = $this->getSoapObject()->login( $user_auth, 'timetrex' );

		//echo "Request :\n".htmlspecialchars($this->getSoapObject()->__getLastRequest()) ."\n";
		//echo "Response :\n".htmlspecialchars($this->getSoapObject()->__getLastResponse()) ."\n";
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//( isset($result->error) AND isset($result->error->number) AND $result->error->number == 0 ) )
		if ( is_object( $result ) && ( isset( $result->id ) && strlen( $result->id ) > 0 ) ) {
			$this->session_id = $result->id;
			Debug::Text( 'aSugarCRM Login Success! Session ID: ' . $this->session_id, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			//Retry login
			sleep( 5 );
			$result = $this->getSoapObject()->login( $user_auth, 'timetrex' );
			if ( is_object( $result ) && ( isset( $result->id ) && strlen( $result->id ) > 0 ) ) {
				$this->session_id = $result->id;
				Debug::Text( 'bSugarCRM Login Success! Session ID: ' . $this->session_id, __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			} else {
				Debug::Text( 'bSugarCRM Login failed!', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		Debug::Arr( $result, 'SOAP Login Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return mixed
	 */
	function getUserGUID() {
		$user_guid = $this->getSoapObject()->get_user_id( $this->session_id );
		Debug::Text( 'User GUID: ' . $user_guid, __FILE__, __LINE__, __METHOD__, 10 );

		return $user_guid;
	}

	/**
	 * @return bool
	 */
	function getAvailableModules() {
		$result = $this->getSoapObject()->get_available_modules( $this->session_id );
		Debug::Arr( $result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Search by account name as well, if the email doesn't match but company name does.
	 * @param $search_field
	 * @param $search_value
	 * @param string $select_fields
	 * @param string $limit
	 * @return SugarCRMReturnHandler
	 */
	function getLeads( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		switch ( $search_field ) {
			case 'id':
				$query = "( leads.id = '" . $search_value . "' )";
				break;
			case 'email':
				//This query can take around 1 second to run.
				//Don't restrict the query to just specific lead_sources, as that can cause emails to be missed.
				//$query = "leads.lead_source = 'Web Site' AND leads.assigned_user_id != 1 AND leads.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'Leads' AND ea.email_address = '".$search_value."' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				//$query = "( leads.lead_source = 'Web Site' OR leads.lead_source = 'Cold Call' ) AND leads.assigned_user_id != 1 AND leads.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'Leads' AND ea.email_address = '".$search_value."' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				$query = "leads.assigned_user_id != 1 AND leads.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'Leads' AND ea.email_address = '" . $search_value . "' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				break;
			case 'any_phone':
				$query = "( replace(replace(replace(replace(replace(leads.phone_work, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(leads.phone_mobile, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(leads.phone_home, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(leads.phone_other, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(leads.phone_fax, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' )";
				break;
			case 'status':
				$query = "( leads.status LIKE '" . $search_value . "' )";
				break;
		}

		// get_entry_list($session, $module_name, $query, $order_by, $offset, $select_fields, $max_results, $deleted ) {
		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Leads', $query, '', 0, $select_fields, $limit, false );
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//return $result;
		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	/**
	 * Search by account name as well, if the email doesn't match but company name does.
	 * @param $search_field
	 * @param $search_value
	 * @param string $select_fields
	 * @param string $limit
	 * @return SugarCRMReturnHandler
	 */
	function getContacts( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		switch ( $search_field ) {
			case 'email':
				//This query can take around 1 second to run.
				$query = "contacts.assigned_user_id != 1 AND contacts.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'contacts' AND ea.email_address = '" . $search_value . "' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				break;
			case 'any_phone':
				$query = "( replace(replace(replace(replace(replace(contacts.phone_work, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(contacts.phone_mobile, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(contacts.phone_home, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(contacts.phone_other, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' OR replace(replace(replace(replace(replace(contacts.phone_fax, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '" . $search_value . "' )";
				break;
			case 'status':
				$query = "( contacts.status LIKE '" . $search_value . "' )";
				break;
		}

		// get_entry_list($session, $module_name, $query, $order_by, $offset, $select_fields, $max_results, $deleted ) {
		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Contacts', $query, '', 0, $select_fields, $limit );
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//return $result;
		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	/**
	 * @param $search_field
	 * @param $search_value
	 * @param string $select_fields
	 * @param string $limit
	 * @return SugarCRMReturnHandler
	 */
	function getEmails( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		Debug::Text( 'Get Emails for Field: ' . $search_field . ' Value: ' . $search_value, __FILE__, __LINE__, __METHOD__, 10 );

		switch ( $search_field ) {
			case 'lead_id':
				$query = "emails.id in ( SELECT email_id FROM emails_beans WHERE bean_module = 'Leads' AND bean_id = '" . $search_value . "' )";
				break;
		}

		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Emails', $query, '', 0, $select_fields, $limit );

		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	/**
	 * @param $search_field
	 * @param $search_value
	 * @param string $select_fields
	 * @param string $limit
	 * @return SugarCRMReturnHandler
	 */
	function getCalls( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		Debug::Text( 'Get Calls for Field: ' . $search_field . ' Value: ' . $search_value, __FILE__, __LINE__, __METHOD__, 10 );

		switch ( $search_field ) {
			case 'id':
				//Get a call by call_id.
				$query = "calls.id = '" . $search_value . "'";
				break;
			case 'lead_id':
				$query = "calls.id in ( SELECT call_id FROM calls_leads WHERE lead_id = '" . $search_value . "' )";
				break;
		}

		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Calls', $query, '', 0, $select_fields, $limit );

		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	/**
	 * @param string $id UUID
	 * @param $status
	 * @return bool
	 */
	function setLeadStatus( $id, $status ) {

		$data = [
				[ 'name' => 'status', 'value' => $status ],
				[ 'name' => 'id', 'value' => $id ],
		];

		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Leads', $data );

		if ( $result->error->number == 0 ) {
			Debug::Text( 'Changed lead status success! ID: ' . $id . ' Status: ' . $status, __FILE__, __LINE__, __METHOD__, 10 );

			//Send out an email to sales@timetrex.com stating that the lead was converted?

			return true;
		} else {
			Debug::Text( 'Changed lead status FAILED!: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $data
	 * @return SugarCRMReturnHandler
	 */
	function setContact( $data ) {
		/*
		Fields:
			array( 'name' => 'first_name', 'value' => $this->getFirstName() ),
			array( 'name' => 'last_name', 'value' => $this->getLastName() ),
			array( 'name' => 'phone_work', 'value' => $this->getPhone() ),
			array( 'name' => 'phone_mobile', 'value' => $this->getPhone() ),
			array( 'name' => 'account_name', 'value' => $this->getCompanyName() ),
			array( 'name' => 'email1', 'value' => $this->getEmail() ),
			array( 'name' => 'primary_address_city', 'value' => $this->getCity() ),
			array( 'name' => 'primary_address_country', 'value' => $this->getCountry() ),
			array( 'name' => 'lead_source', 'value' => 'Web Site' ),
			array( 'name' => 'description', 'value' => $request_text_arr['body'] ),
			array( 'name' => 'assigned_user_id', 'value' => '8db3bb58-8203-7ef3-b33f-4db9e7891adc' ), //Murray
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Contacts', $this->convertToNameValueList( $data ) );
		Debug::Arr( $result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return new SugarCRMReturnHandler( $result );
	}

	/**
	 * @param $data
	 * @return SugarCRMReturnHandler
	 */
	function setLead( $data ) {
		/*
		Fields:
			array( 'name' => 'first_name', 'value' => $this->getFirstName() ),
			array( 'name' => 'last_name', 'value' => $this->getLastName() ),
			array( 'name' => 'phone_work', 'value' => $this->getPhone() ),
			array( 'name' => 'account_name', 'value' => $this->getCompanyName() ),
			array( 'name' => 'email1', 'value' => $this->getEmail() ),
			array( 'name' => 'primary_address_city', 'value' => $this->getCity() ),
			array( 'name' => 'primary_address_country', 'value' => $this->getCountry() ),
			array( 'name' => 'Employees_c', 'value' => $this->getEmployee() ),
			array( 'name' => 'time_zone_c', 'value' => $this->getTimeZone() .' ('. $time_zone_offset .')' ),
			array( 'name' => 'status', 'value' => 'New' ),
			array( 'name' => 'lead_source', 'value' => 'Web Site' ),
			array( 'name' => 'opportunity_amount', 'value' => $this->getBudgetAmount() ),
			array( 'name' => 'description', 'value' => $request_text_arr['body'] ),
			//array( 'name' => 'assigned_user_id', 'value' => $user_guid ),
			array( 'name' => 'assigned_user_id', 'value' => '8db3bb58-8203-7ef3-b33f-4db9e7891adc' ), //Murray
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Leads', $this->convertToNameValueList( $data ) );
		Debug::Arr( $result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return new SugarCRMReturnHandler( $result );
	}

	/**
	 * @param $data
	 * @return SugarCRMReturnHandler
	 */
	function setCall( $data ) {
		/*
		Fields:
			Name (subject)
			Description
			duration_hours
			duration_minutes //15 minute increments only.
			date_start
			date_entered
			status (Planned, Held, Not Held)
			direction (Inbound/Outbound)
			assigned_user_id
			parent_type (Module related too: Leads, Accounts, Contacts)
			parent_id (module object id)
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Calls', $this->convertToNameValueList( $data ) );
		Debug::Arr( $result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return new SugarCRMReturnHandler( $result );
	}

	/**
	 * @param $data
	 * @return SugarCRMReturnHandler
	 */
	function setEmail( $data ) {
		// - http://panther.sugarcrm.com/forums/showthread.php?t=68490&highlight=set_entry+email
		/*
		Fields:
			from_addr
			to_addrs
			status (Sent/Read/UnRead/Replied)
			name (subject)
			description (body?)
			date_start (Date/Time email was sent.)
			assigned_user_id
			parent_type (Module related too: Leads, Accounts, Contacts)
			parent_id (module object id)
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Emails', $this->convertToNameValueList( $data ) );
		Debug::Arr( $result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return new SugarCRMReturnHandler( $result );
	}

	/**
	 * @param $module1
	 * @param string $module1_id UUID
	 * @param $module2
	 * @param string $module2_id UUID
	 * @return SugarCRMReturnHandler
	 */
	function setRelationship( $module1, $module1_id, $module2, $module2_id ) {
		//Examples on relating contacts/leads to emails.
		//$result = $sugarcrm->setRelationship( 'Contacts', '5b3826da-78d8-568a-73f3-43d92903b54d', 'Emails', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		//$result = $sugarcrm->setRelationship( 'Leads', '5f1f58a8-45c4-15da-ea0e-4d3f43f0f71f', 'Emails', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		//$result = $sugarcrm->setRelationship( 'Contacts', '5b3826da-78d8-568a-73f3-43d92903b54d', 'Accounts', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		$data = [
				'module1'    => $module1,
				'module1_id' => $module1_id,
				'module2'    => $module2,
				'module2_id' => $module2_id,
		];

		//Debug::Arr($data, 'Relationship Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$result = $this->getSoapObject()->set_relationship( $this->session_id, $data );

		//Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}
}

/**
 * Class SugarCRMReturnHandler
 */
class SugarCRMReturnHandler {
	protected $result_data = null;
	protected $select_fields = [];
	protected $limit = null;

	/**
	 * SugarCRMReturnHandler constructor.
	 * @param $result_data
	 * @param array $select_fields
	 * @param string $limit
	 */
	function __construct( $result_data, $select_fields = [], $limit = '' ) {
		$this->result_data = $result_data;
		$this->select_fields = $select_fields;
		$this->limit = $limit;

		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function convertFromNameValueList( $data ) {
		//Debug::Arr($data, 'Raw data to convert: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( isset( $data->name_value_list ) ) {

			$retarr = [];
			foreach ( $data->name_value_list as $field ) {
				$retarr[$field->name] = $field->value;
			}
			if ( isset( $retarr ) ) {
				return $retarr;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isFault() {
		if ( get_class( $this->result_data ) == 'SoapFault' ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isValid() {
		if ( $this->isFault() == true && is_soap_fault( $this->result_data ) ) {
			trigger_error( 'SOAP Fault: (Code: ' . $this->result_data->faultcode . ', String: ' . $this->result_data->faultstring . ')', E_USER_NOTICE );
		} else {
			if ( isset( $this->result_data->error ) && $this->result_data->error->number == 0 ) {
				return true;
			} else if ( isset( $this->result_data->number ) && $this->result_data->number == 0 ) { //For set_relationship()
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getRecordCount() {
		if ( isset( $this->result_data->result_count ) ) {
			return (int)$this->result_data->result_count;
		}

		return false;
	}

	/**
	 * Returns the array of just one row.
	 * @param array $select_fields
	 * @return array|bool
	 */
	function getRow( $select_fields = [] ) {
		if ( $this->result_data->error->number == 0 && isset( $this->result_data->result_count ) && $this->result_data->result_count == 1 && count( $this->result_data->entry_list ) == 1 ) {
			//One row
			Debug::Text( 'Single row...', __FILE__, __LINE__, __METHOD__, 10 );
			$tmp_data = $this->convertFromNameValueList( $this->result_data->entry_list[0] );
			if ( is_array( $tmp_data ) ) {
				Debug::Arr( $tmp_data, 'Tmp Data', __FILE__, __LINE__, __METHOD__, 10 );
				$retarr = $tmp_data;
			}

			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		}

		return false;
	}

	/**
	 * Returns one column from one row returned.
	 * @return bool|mixed
	 */
	function getOne() {
		//Debug::Arr($this->result_data, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->result_data->error->number == 0 && isset( $this->result_data->result_count ) && $this->result_data->result_count == 1 && count( $this->result_data->entry_list ) == 1 ) {
			//One row
			Debug::Text( 'Single row...', __FILE__, __LINE__, __METHOD__, 10 );
			$tmp_data = $this->convertFromNameValueList( $this->result_data->entry_list[0] );
			if ( is_array( $tmp_data ) ) {
				//Debug::Arr($tmp_data, 'Tmp Data', __FILE__, __LINE__, __METHOD__, 10);

				//Check for one field too
				if ( count( array_keys( $tmp_data ) ) == 1 ) {
					Debug::Text( 'Single field...', __FILE__, __LINE__, __METHOD__, 10 );
					$key = key( $tmp_data );
					$retarr = $tmp_data[$key];
				}
			}

			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		} else if ( $this->result_data->error->number == 0 && isset( $this->result_data->id ) ) {
			//Saved record, just return the ID.
			return $this->result_data->id;
		}

		return false;
	}

	/**
	 * Used by getResult()
	 * @param $result
	 * @param array $select_fields
	 * @param string $limit
	 * @return array|bool
	 */
	function handleResult( $result, $select_fields = [], $limit = '' ) {
		if ( !is_array( $select_fields ) ) {
			$select_fields = [ $select_fields ];
		}

		if ( $result->error->number == 0 && isset( $result->result_count ) && $result->result_count > 0 ) {

			$retarr = [];

			if ( is_array( $result->entry_list ) ) {
				//Use getOne or getRow() if only one result is returned.
				foreach ( $result->entry_list as $row ) {
					//Debug::Arr($row, 'zSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);
					$retarr[] = $this->convertFromNameValueList( $row );
				}
			}
			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		} else if ( $result->error->number == 0 && !isset( $result->result_count ) ) {
			//Saving a record, Sugar returns just the ID and any error message?
			if ( isset( $result->id ) ) {
				return $result->id;
			}
		}

		return false;
	}

	/**
	 * @param array $select_fields
	 * @param string $limit
	 * @return array|bool
	 */
	function getResult( $select_fields = [], $limit = '' ) {
		if ( count( $select_fields ) == 0 ) {
			$select_fields = $this->select_fields;
		}

		if ( $limit == '' ) {
			$limit = $this->limit;
		}

		return $this->handleResult( $this->result_data, $select_fields, $limit );
	}

}

?>
