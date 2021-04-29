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
 * @package Modules\Message
 */
class MessageControlFactory extends Factory {
	protected $table = 'message_control';
	protected $pk_sequence_name = 'message_control_id_seq'; //PK Sequence name

	protected $obj_handler = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'UNREAD' ),
						20 => TTi18n::gettext( 'READ' ),
				];
				break;
			case 'type':
				$retval = [
						5   => 'email',
						//10 => 'default_schedule',
						//20 => 'schedule_amendment',
						//30 => 'shift_amendment',
						40  => 'authorization',
						50  => 'request',
						60  => 'job',
						70  => 'job_item',
						80  => 'client',
						90  => 'timesheet',
						100 => 'user' //For notes assigned to users?
				];
				break;
			case 'type_to_api_map': //Maps the object_type_id to an API class that we can use to determine if the user has access to view the specific records or not.
				$retval = [
					//5 => 'email', //Email is never linked to another class
					//10 => 'default_schedule',
					//20 => 'schedule_amendment',
					//30 => 'shift_amendment',
					40  => 'APIAuthorization',
					50  => 'APIRequest',
					60  => 'APIJob',
					70  => 'APIJobItem',
					80  => 'APIClient',
					90  => 'APITimeSheet',
					100 => 'APIUser' //For notes assigned to users?
				];
				break;
			case 'object_type':
			case 'object_name':
				$retval = [
						5   => TTi18n::gettext( 'Email' ), //Email from user to another
						10  => TTi18n::gettext( 'Recurring Schedule' ),
						20  => TTi18n::gettext( 'Schedule Amendment' ),
						30  => TTi18n::gettext( 'Shift Amendment' ),
						40  => TTi18n::gettext( 'Authorization' ),
						50  => TTi18n::gettext( 'Request' ),
						60  => TTi18n::gettext( 'Job' ),
						70  => TTi18n::gettext( 'Task' ),
						80  => TTi18n::gettext( 'Client' ),
						90  => TTi18n::gettext( 'TimeSheet' ),
						100 => TTi18n::gettext( 'Employee' ) //For notes assigned to users?
				];
				break;
			case 'folder':
				$retval = [
						10 => TTi18n::gettext( 'Inbox' ),
						20 => TTi18n::gettext( 'Sent' ),
				];
				break;
			case 'priority':
				$retval = [
						10  => TTi18n::gettext( 'LOW' ),
						50  => TTi18n::gettext( 'NORMAL' ),
						100 => TTi18n::gettext( 'HIGH' ),
						110 => TTi18n::gettext( 'URGENT' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-from_first_name'  => TTi18n::gettext( 'From: First Name' ),
						'-1020-from_middle_name' => TTi18n::gettext( 'From: Middle Name' ),
						'-1030-from_last_name'   => TTi18n::gettext( 'From: Last Name' ),

						'-1110-to_first_name'  => TTi18n::gettext( 'To: First Name' ),
						'-1120-to_middle_name' => TTi18n::gettext( 'To: Middle Name' ),
						'-1130-to_last_name'   => TTi18n::gettext( 'To: Last Name' ),

						'-1200-subject'     => TTi18n::gettext( 'Subject' ),
						'-1210-object_type' => TTi18n::gettext( 'Type' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						//'-2020-updated_by' => TTi18n::gettext('Updated By'),
						//'-2030-updated_date' => TTi18n::gettext('Updated Date'),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'from_first_name',
						'from_last_name',
						'to_first_name',
						'to_last_name',
						'subject',
						'object_type',
						'created_date',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id' => 'ID',

				'from_user_id'     => 'FromUserID',
				'from_first_name'  => false,
				'from_middle_name' => false,
				'from_last_name'   => false,

				'to_user_id'     => 'ToUserID',
				'to_first_name'  => false,
				'to_middle_name' => false,
				'to_last_name'   => false,

				'status_id'      => false,
				'object_type_id' => 'ObjectType',
				'object_type'    => false,
				'object_id'      => 'Object',
				'parent_id'      => 'Parent',
				'priority_id'    => 'Priority',
				'subject'        => 'Subject',
				'body'           => 'Body',
				'require_ack'    => 'RequireAck',
				'deleted'        => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getFromUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getFromUserID(), 'from_user_obj' );
	}

	/**
	 * @return bool
	 */
	function getFromUserId() {
		return $this->getGenericTempDataValue( 'from_user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setFromUserId( $value ) {
		if ( $value != '' ) {
			return $this->setGenericTempDataValue( 'from_user_id', $value );
		}

		return false;
	}

	/**
	 * @return bool|array|string
	 */
	function getToUserId() {
		return $this->getGenericTempDataValue( 'to_user_id' );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setToUserId( $ids ) {
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		$ids = array_unique( $ids );
		if ( count( $ids ) > 0 ) {
			$tmp_ids = []; //Reset the TO array, so if this is called multiple times, we don't keep adding more and more users to it.
			foreach ( $ids as $id ) {
				if ( TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() ) {
					$tmp_ids[] = $id;
				}
			}
			$this->setGenericTempDataValue( 'to_user_id', $tmp_ids );

			return true;
		}

		return false;
	}

	/**
	 * Expose message_sender_id for migration purposes.
	 * @return bool
	 */
	function getMessageSenderId() {
		return $this->getGenericTempDataValue( 'message_sender_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setMessageSenderId( $value ) {
		if ( $value != '' ) {
			return $this->setGenericTempDataValue( 'message_sender_id', $value );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isAck() {
		if ( $this->getRequireAck() == true && $this->getColumn( 'ack_date' ) == '' ) {
			return false;
		}

		return true;
	}

	/**
	 * Parent ID is the parent message_sender_id.
	 * @return bool
	 */
	function getParent() {
		return $this->getGenericTempDataValue( 'parent_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericTempDataValue( 'parent_id', $value );
	}

	/**
	 * These functions are out of the ordinary, as the getStatus gets the status of a message based on a SQL join to the recipient table.
	 * @return bool|int
	 */
	function getStatus() {
		return (int)$this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @return null|object
	 */
	function getObjectHandler() {
		if ( is_object( $this->obj_handler ) ) {
			return $this->obj_handler;
		} else {
			switch ( $this->getObjectType() ) {
				case 5:
				case 100:
					$this->obj_handler = TTnew( 'UserListFactory' );
					break;
				case 40:
					$this->obj_handler = TTnew( 'AuthorizationListFactory' );
					break;
				case 50:
					$this->obj_handler = TTnew( 'RequestListFactory' );
					break;
				case 90:
					$this->obj_handler = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
					break;
			}

			return $this->obj_handler;
		}
	}

	/**
	 * @return bool|int
	 */
	function getObjectType() {
		return (int)$this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'object_type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getObject() {
		return $this->getGenericDataValue( 'object_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setObject( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'object_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getPriority() {
		return $this->getGenericDataValue( 'priority_id' );
	}

	/**
	 * @param null $value
	 * @return bool
	 */
	function setPriority( $value = null ) {
		$value = (int)trim( $value );

		if ( empty( $value ) ) {
			$value = 50;
		}

		return $this->setGenericDataValue( 'priority_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSubject() {
		return $this->getGenericDataValue( 'subject' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSubject( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'subject', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getBody() {
		return $this->getGenericDataValue( 'body' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBody( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'body', $value );
	}

	/**
	 * @return bool
	 */
	function getRequireAck() {
		return $this->fromBool( $this->getGenericDataValue( 'require_ack' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRequireAck( $value ) {
		return $this->setGenericDataValue( 'require_ack', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailMessage() {
		if ( isset( $this->email_message ) ) {
			return $this->email_message;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableEmailMessage( $bool ) {
		$this->email_message = $bool;

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
		// Parent
		if ( $this->getParent() !== false && $this->getParent() != TTUUID::getZeroID() ) {
			$this->Validator->isUUID( 'parent',
									  $this->getParent(),
									  TTi18n::gettext( 'Parent is invalid' )
			);
		}
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  $this->getOptions( 'type' )
		);
		// Object
		$this->Validator->isResultSetWithRows( 'object',
											   ( is_object( $this->getObjectHandler() ) ) ? $this->getObjectHandler()->getByID( $this->getObject() ) : false,
											   TTi18n::gettext( 'Object is invalid' )
		);
		// Priority
		if ( $this->getPriority() !== false ) {
			$this->Validator->inArrayKey( 'priority',
										  $this->getPriority(),
										  TTi18n::gettext( 'Invalid Priority' ),
										  $this->getOptions( 'priority' )
			);
		}
		// Subject
		if ( $this->getSubject() !== false ) {
			$this->Validator->isLength( 'subject',
										$this->getSubject(),
										TTi18n::gettext( 'Subject is too short' ),
										2,
										99999
			);
			$this->Validator->isLength( 'subject',
										$this->getSubject(),
										TTi18n::gettext( 'Subject is too long' ),
										0,
										100
			);
		}

		// Message body
		//Flex interface validates the message too soon, make it skip a 0 length message when only validating.
		if ( $this->Validator->getValidateOnly() == true && $this->getBody() == '' ) {
			$minimum_length = 0;
		} else {
			$minimum_length = 2;
		}
		$this->Validator->isLength( 'body',
									$this->getBody(),
									TTi18n::gettext( 'Message body is too short.' ),
									$minimum_length,
				( 1024 * 9999999 )
		);
		if ( $this->Validator->isError( 'body' ) == false ) {
			$this->Validator->isLength( 'body',
										$this->getBody(),
										TTi18n::gettext( 'Message body is too long.' ),
										0,
					( 1024 * 10 )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//


		//Only validate from/to user if there is a subject and body set, otherwise validation will fail on a new object with no data all the time.
		if ( $this->getSubject() != '' && $this->getBody() != '' ) {
			if ( $this->Validator->hasError( 'from' ) == false && $this->getFromUserId() == '' ) {
				$this->Validator->isTrue( 'from',
										  false,
										  TTi18n::gettext( 'Message sender is invalid' ) );
			}

			//Messages attached to objects do not require a recipient.
			if ( $this->Validator->hasError( 'to' ) == false && $this->getObjectType() == 5 && ( $this->getToUserId() == '' || ( is_array( $this->getToUserId() ) && count( $this->getToUserId() ) == 0 ) ) ) {
				$this->Validator->isTrue( 'to_user_id',
										  false,
										  TTi18n::gettext( 'Please specify at least one employee' ) );
			}
		}

		if ( $this->Validator->getValidateOnly() == false ) {
			if ( $this->getObjectType() == '' ) {
				$this->Validator->isTrue( 'object_type_id',
										  false,
										  TTi18n::gettext( 'Object type is invalid' ) );
			}

			if ( $this->Validator->hasError( 'object' ) == false && $this->getObject() == '' ) {
				$this->Validator->isTrue( 'object',
										  false,
										  TTi18n::gettext( 'Object must be specified' ) );
			}
		}

		//If deleted is TRUE, we need to make sure all sender/recipient records are also deleted.
		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string|array $ids  UUID
	 * @return bool
	 */
	static function markRecipientMessageAsRead( $company_id, $user_id, $ids ) {
		if ( $company_id == '' || $user_id == '' || $ids == '' || ( is_array( $ids ) && count( $ids ) == 0 ) ) {
			return false;
		}

		Debug::Arr( $ids, 'Message Recipeint Ids: ', __FILE__, __LINE__, __METHOD__, 10 );

		$mrlf = TTnew( 'MessageRecipientListFactory' ); /** @var MessageRecipientListFactory $mrlf */
		$mrlf->getByCompanyIdAndUserIdAndMessageSenderIdAndStatus( $company_id, $user_id, $ids, 10 );
		if ( $mrlf->getRecordCount() > 0 ) {
			foreach ( $mrlf as $mr_obj ) {
				$mr_obj->setStatus( 20 ); //Read
				if ( $mr_obj->isValid() ) {
					$mr_obj->Save();
				}
			}
		}

		return true;
	}

	/**
	 * @return array|bool
	 */
	function getEmailMessageAddresses() {
		//Remove the From User from any recipicient list so we don't send emails back to ourselves.
		$user_ids = array_diff( $this->getToUserId(), [ $this->getFromUserId() ] );
		if ( isset( $user_ids ) && is_array( $user_ids ) && count( $user_ids ) > 0 ) {
			//Get user preferences and determine if they accept email notifications.
			Debug::Arr( $user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__, 10 );

			$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
			$uplf->getByUserIdAndEnableLogin( $user_ids, true ); //Only email employees/supervisors who can still login.
			if ( $uplf->getRecordCount() > 0 ) {
				$retarr = [];
				foreach ( $uplf as $up_obj ) {
					if ( $up_obj->getEnableEmailNotificationMessage() == true && is_object( $up_obj->getUserObject() ) && $up_obj->getUserObject()->getEnableLogin() == 10 ) {
						if ( $up_obj->getUserObject()->getWorkEmail() != '' && $up_obj->getUserObject()->getWorkEmailIsValid() == true ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getWorkEmail(), $up_obj->getUserObject() );
						}

						if ( $up_obj->getEnableEmailNotificationHome() && is_object( $up_obj->getUserObject() ) && $up_obj->getUserObject()->getHomeEmail() != '' && $up_obj->getUserObject()->getHomeEmailIsValid() == true ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getHomeEmail(), $up_obj->getUserObject() );
						}
					}
				}

				if ( isset( $retarr ) ) {
					Debug::Arr( $retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__, 10 );

					return array_unique( $retarr );
				}
			} else {
				Debug::Text( 'No user preferences available, or user is not active...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function emailMessage() {
		Debug::Text( 'emailMessage: ', __FILE__, __LINE__, __METHOD__, 10 );

		$email_to_arr = $this->getEmailMessageAddresses();
		if ( $email_to_arr == false ) {
			return false;
		}

		//Get from User Object so we can include more information in the message.
		if ( is_object( $this->getFromUserObject() ) ) {
			$u_obj = $this->getFromUserObject();
		} else {
			Debug::Text( 'From object does not exist: ' . $this->getFromUserID(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$from = $reply_to = '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Message' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';

//		Always make sure ReplyTo is the generic email address that will cause a bounce.
//		global $current_user;
//		if ( is_object($current_user) AND $current_user->getWorkEmail() != '' ) {
//			$reply_to = Misc::formatEmailAddress( $current_user->getWorkEmail(), $current_user );
//		}

		Debug::Text( 'To: ' . implode( ',', $email_to_arr ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'From: ' . $from . ' Reply-To: ' . $reply_to, __FILE__, __LINE__, __METHOD__, 10 );

		//Define subject/body variables here.
		$search_arr = [
				'#from_employee_first_name#',
				'#from_employee_last_name#',
				'#from_employee_default_branch#',
				'#from_employee_default_department#',
				'#from_employee_group#',
				'#from_employee_title#',
				'#company_name#',
				'#link#',
		];

		$replace_arr = [
				$u_obj->getFirstName(),
				$u_obj->getLastName(),
				( is_object( $u_obj->getDefaultBranchObject() ) ) ? $u_obj->getDefaultBranchObject()->getName() : null,
				( is_object( $u_obj->getDefaultDepartmentObject() ) ) ? $u_obj->getDefaultDepartmentObject()->getName() : null,
				( is_object( $u_obj->getGroupObject() ) ) ? $u_obj->getGroupObject()->getName() : null,
				( is_object( $u_obj->getTitleObject() ) ) ? $u_obj->getTitleObject()->getName() : null,
				( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null,
				null,
		];

		$email_subject = TTi18n::gettext( 'New message waiting in' ) . ' ' . APPLICATION_NAME;

		$email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$email_body .= TTi18n::gettext( 'You have a new message waiting for you in' ) . ' ' . APPLICATION_NAME . "\n";
		$email_body .= ( $this->getSubject() != '' ) ? TTi18n::gettext( 'Subject' ) . ': ' . $this->getSubject() . "\n" : null;
		$email_body .= TTi18n::gettext( 'From' ) . ': #from_employee_first_name# #from_employee_last_name#' . "\n";
		$email_body .= ( $replace_arr[2] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #from_employee_default_branch#' . "\n" : null;
		$email_body .= ( $replace_arr[3] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #from_employee_default_department#' . "\n" : null;
		$email_body .= ( $replace_arr[4] != '' ) ? TTi18n::gettext( 'Group' ) . ': #from_employee_group#' . "\n" : null;
		$email_body .= ( $replace_arr[5] != '' ) ? TTi18n::gettext( 'Title' ) . ': #from_employee_title#' . "\n" : null;

		$email_body .= TTi18n::gettext( 'Link' ) . ': <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . '">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Login' ) . '</a>';

		$email_body .= ( $replace_arr[6] != '' ) ? "\n\n\n" . TTi18n::gettext( 'Company' ) . ': #company_name#' . "\n" : null; //Always put at the end

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text( 'Subject: ' . $subject, __FILE__, __LINE__, __METHOD__, 10 );

		$headers = [
				'From'    => $from,
				'Subject' => $subject,
				//Reply-To/Return-Path are handled in TTMail.
		];

		$body = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $email_body ) . '</pre></body></html>';
		Debug::Text( 'Body: ' . $body, __FILE__, __LINE__, __METHOD__, 10 );

		$mail = new TTMail();
		$mail->setTo( $email_to_arr );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody( $body );

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == true ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Email Message to' ) . ': ' . implode( ', ', $email_to_arr ), null, $this->getTable() );

			return true;
		}

		return true; //Always return true
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Check to make sure the 'From' user_id doesn't appear in the 'To' user list as well.
		$from_user_id_key = array_search( $this->getFromUserId(), (array)$this->getToUserId() );
		if ( $from_user_id_key !== false ) {
			$to_user_ids = $this->getToUserId();
			unset( $to_user_ids[$from_user_id_key] );
			$this->setToUserId( $to_user_ids );

			Debug::text( 'From user is assigned as a To user as well, removing...' . $from_user_id_key, __FILE__, __LINE__, __METHOD__, 9 );
		}

		Debug::Arr( $this->getFromUserId(), 'From: ', __FILE__, __LINE__, __METHOD__, 9 );
		Debug::Arr( $this->getToUserId(), 'Sending To: ', __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Save Sender/Recipient records for this message.
		if ( $this->getDeleted() == false ) {
			$to_user_ids = $this->getToUserId();
			if ( $to_user_ids != false ) {
				foreach ( $to_user_ids as $to_user_id ) {
					//We need one message_sender record for every recipient record, otherwise when a message is sent to
					//multiple recipients, and one of them replies, the parent_id will point to original sender record which
					//then maps to every single recipient, making it hard to show messages just between the specific users.
					//
					//On the other hand, having multiple sender records, one for each recipient makes it hard to show
					//just the necessary messages on the embedded message list, as it wants to show duplicates messages for
					//each recipient.
					$msf = TTnew( 'MessageSenderFactory' ); /** @var MessageSenderFactory $msf */
					$msf->setUser( $this->getFromUserId() );
					Debug::Text( 'Parent ID: ' . $this->getParent(), __FILE__, __LINE__, __METHOD__, 10 );

					//Only specify parent if the object type is message.
					if ( $this->getObjectType() == 5 ) {
						$msf->setParent( $this->getParent() );
					} else {
						$msf->setParent( TTUUID::getZeroID() );
					}
					$msf->setMessageControl( $this->getId() );
					$msf->setCreatedBy( $this->getCreatedBy() );
					$msf->setCreatedDate( $this->getCreatedDate() );
					$msf->setUpdatedBy( $this->getUpdatedBy() );
					$msf->setUpdatedDate( $this->getUpdatedDate() );
					if ( $msf->isValid() ) {
						$message_sender_id = $msf->Save();
						$this->setMessageSenderId( $message_sender_id ); //Used mainly for migration purposes, so we can obtain this from outside the class.
						Debug::Text( 'Message Sender ID: ' . $message_sender_id, __FILE__, __LINE__, __METHOD__, 10 );

						if ( $message_sender_id != false ) {
							$mrf = TTnew( 'MessageRecipientFactory' ); /** @var MessageRecipientFactory $mrf */
							$mrf->setUser( $to_user_id );
							$mrf->setMessageSender( $message_sender_id );
							if ( isset( $this->migration_status ) ) {
								$mrf->setStatus( $this->migration_status );
							}
							$mrf->setCreatedBy( $this->getCreatedBy() );
							$mrf->setCreatedDate( $this->getCreatedDate() );
							$mrf->setUpdatedBy( $this->getUpdatedBy() );
							$mrf->setUpdatedDate( $this->getUpdatedDate() );
							if ( $mrf->isValid() ) {
								$mrf->Save();
							}
						}
					}
				}

				//Send email to all recipients.
				if ( $this->getEnableEmailMessage() == true ) {
					$this->emailMessage();
				}
			} //else {
			//If no recipients are specified (user replying to their own request before a superior does, or a user sending a request without a hierarchy)
			//Make sure we have at least one sender record.
			//Either that or make sure we always reply to ALL senders and recipients in the thread.
			//}
		}

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
						case 'to_user_id':
						case 'to_first_name':
						case 'to_middle_name':
						case 'to_last_name':
						case 'from_user_id':
						case 'from_first_name':
						case 'from_middle_name':
						case 'from_last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status_id':
							$data[$variable] = $this->getStatus(); //Make sure this is returned as an INT.
							break;
						case 'object_type':
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
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

}

?>
