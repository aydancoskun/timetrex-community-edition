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
 * Class OSTicket
 */
class OSTicket {
	private $osticket_url = null; //Base URL. ie: api/tickets.json will be appended to it.
	private $osticket_api_key = null;

	/**
	 * OSTicket constructor.
	 * @param null $url
	 * @param null $api_key
	 */
	function __construct( $url = null, $api_key = null ) {
		if ( $url != '' ) {
			$this->osticket_url = $url;
		}

		if ( $api_key != '' ) {
			$this->osticket_api_key = $api_key;
		}
	}

	/**
	 * @param $data
	 * @return bool|int
	 */
	function addTicket( $data ) {
		//$data = array(
		//		'subject' => '', //Ticket subject
		//		'message' => '', //Ticket body, customer sees.
		//		'internal_note' => '', //Ticket internal note.
		//		'note' => '', //From user note.
		//		'name' => '', //From Name
		//		'email' => '', //From Email
		//		'alert' => false, //Alert to staff
		//		'autorespond' => false, //Auto-respond to creator of ticket
		//		'staffId' => null, //Assign to specific staff
		//		'deptId' => null, //Assign to specific department
		//		'attachments' => [], //Attachments array.
		//);

		/*
				  * Add in attachments here

				 $data['attachments'][] =
				 array('filename.pdf' =>
						 'data:image/png;base64,' .
							 base64_encode(file_get_contents('/path/to/filename.pdf')));
				  */
		//$data['attachments'][] = array( basename( $attachment_file ) => 'data:'. mime_content_type( $attachment_file ).';base64,'. base64_encode( file_get_contents( $attachment_file ) ) );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->osticket_url . '/api/tickets.json' );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'osTicket API Client v1.7' );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Expect:', 'X-API-Key: ' . $this->osticket_api_key ] );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$result = curl_exec( $ch );
		$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $code != 201 ) {
			Debug::Text( '  ERROR: osTicket addTicket failed! Code: ' . $code, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$ticket_id = (int)$result;

		Debug::Text( '  osTicket addTicket success! Ticket ID: ' . $ticket_id, __FILE__, __LINE__, __METHOD__, 10 );

		return $ticket_id;
	}
}

?>
