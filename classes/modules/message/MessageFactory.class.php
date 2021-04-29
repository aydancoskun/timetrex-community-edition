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
 * @package Modules\Message
 */
class MessageFactory extends Factory {
	protected $table = 'message';
	protected $pk_sequence_name = 'message_id_seq'; //PK Sequence name
	protected $obj_handler = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'UNREAD' ),
						20 => TTi18n::gettext( 'READ' ),
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
		}

		return $retval;
	}


	/**
	 * @return bool|mixed
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'parent_id', $value );
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
		return $this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = (int)trim( $value );

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
		$value = TTUUID::castUUID( $value );

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
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );
		$this->setStatusDate();

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStatusDate() {
		return $this->getGenericDataValue( 'status_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStatusDate( $value = null ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'status_date', $value );
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
	function isAck() {
		if ( $this->getRequireAck() == true && $this->getAckDate() == '' ) {
			return false;
		}

		return true;
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
	function getAck() {
		return $this->fromBool( $this->getGenericDataValue( 'ack' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAck( $value ) {
		$this->setGenericDataValue( 'ack', $this->toBool( $value ) );
		if ( $this->getAck() == true ) {
			$this->setAckDate();
			$this->setAckBy();
		}

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getAckDate() {
		return $this->getGenericDataValue( 'ack_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setAckDate( $value = null ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'ack_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAckBy() {
		return $this->getGenericDataValue( 'ack_by' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAckBy( $value = null ) {
		$value = trim( $value );

		if ( empty( $value ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$value = $current_user->getID();
			} else {
				return false;
			}
		}

		return $this->setGenericDataValue( 'ack_by', $value );
	}

	/**
	 * @return array|bool
	 */
	function getEmailMessageAddresses() {
		$olf = $this->getObjectHandler();
		if ( is_object( $olf ) ) {

			$user_ids = [];
			$olf->getById( $this->getObject() );
			if ( $olf->getRecordCount() > 0 ) {
				$obj = $olf->getCurrent();

				switch ( $this->getObjectType() ) {
					case 5:
					case 100:
						Debug::Text( 'Email Object Type... Parent ID: ' . $this->getParent(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $this->getParent() == TTUUID::getZeroID() ) {
							$user_ids[] = $obj->getId();
						} else {
							$mlf = TTnew( 'MessageListFactory' ); /** @var MessageListFactory $mlf */
							$mlf->getById( $this->getParent() );
							if ( $mlf->getRecordCount() > 0 ) {
								$m_obj = $mlf->getCurrent();

								$user_ids[] = $m_obj->getCreatedBy();
							}
							Debug::Text( 'cEmail Object Type... Parent ID: ' . $this->getParent(), __FILE__, __LINE__, __METHOD__, 10 );
						}
						break;
					case 40:
						$user_ids[] = $obj->getId();
						break;
					case 50: //Request
						//Get all users who have contributed to the thread.
						$mlf = TTnew( 'MessageListFactory' ); /** @var MessageListFactory $mlf */
						$mlf->getMessagesInThreadById( $this->getId() );
						Debug::Text( ' Messages In Thread: ' . $mlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $mlf->getRecordCount() > 0 ) {
							foreach ( $mlf as $m_obj ) {
								$user_ids[] = $m_obj->getCreatedBy();
							}
						}
						unset( $mlf, $m_obj );
						//Debug::Arr($user_ids, 'User IDs in Thread: ', __FILE__, __LINE__, __METHOD__, 10);

						//Only alert direct supervisor to request at this point. Because we need to take into account
						//if the request was authorized or not to determine if we should email the next higher level in the hierarchy.
						if ( $this->getParent() == TTUUID::getZeroID() ) {
							//Get direct parent in hierarchy.
							$u_obj = $obj->getUserObject();

							$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
							$user_ids[] = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $u_obj->getCompany(), $u_obj->getId(), $this->getObjectType(), true, false );
							unset( $hlf );
						}

						global $current_user;
						if ( isset( $current_user ) && is_object( $current_user ) && isset( $user_ids ) && is_array( $user_ids ) ) {
							$user_ids = array_unique( $user_ids );
							$current_user_key = array_search( $current_user->getId(), $user_ids );
							Debug::Text( ' Current User Key: ' . $current_user_key, __FILE__, __LINE__, __METHOD__, 10 );
							if ( $current_user_key !== false ) {
								Debug::Text( ' Removing Current User From Recipient List...' . $current_user->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								unset( $user_ids[$current_user_key] );
							}
						} else {
							Debug::Text( ' Current User Object not available...', __FILE__, __LINE__, __METHOD__, 10 );
						}
						unset( $current_user, $current_user_key );

						break;
					case 90:
						$user_ids[] = $obj->getUser();
						break;
				}
			}

			if ( empty( $user_ids ) == false ) {
				//Get user preferences and determine if they accept email notifications.
				Debug::Arr( $user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__, 10 );

				$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
				$uplf->getByUserId( $user_ids );
				if ( $uplf->getRecordCount() > 0 ) {
					$retarr = [];
					foreach ( $uplf as $up_obj ) {
						if ( $up_obj->getEnableEmailNotificationMessage() == true && $up_obj->getUserObject()->getStatus() == 10 ) {
							if ( $up_obj->getUserObject()->getWorkEmail() != '' ) {
								$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getWorkEmail(), $up_obj->getUserObject() );
							}

							if ( $up_obj->getEnableEmailNotificationHome() && $up_obj->getUserObject()->getHomeEmail() != '' ) {
								$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getHomeEmail(), $up_obj->getUserObject() );
							}
						}
					}

					if ( empty( $retarr ) == false ) {
						Debug::Arr( $retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__, 10 );

						return $retarr;
					}
				}
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

		$from = $reply_to = '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Message' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';

		global $current_user;
		if ( is_object( $current_user ) && $current_user->getWorkEmail() != '' ) {
			$reply_to = Misc::formatEmailAddress( $current_user->getWorkEmail(), $current_user );
		}
		Debug::Text( 'From: ' . $from . ' Reply-To: ' . $reply_to, __FILE__, __LINE__, __METHOD__, 10 );

		$to = array_shift( $email_to_arr );
		Debug::Text( 'To: ' . $to, __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $email_to_arr ) && count( $email_to_arr ) > 0 ) {
			$bcc = implode( ',', $email_to_arr );
		} else {
			$bcc = null;
		}

		$email_subject = TTi18n::gettext( 'New message waiting in' ) . ' ' . APPLICATION_NAME;
		$email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$email_body .= TTi18n::gettext( 'You have a new message waiting for you in' ) . ' ' . APPLICATION_NAME . "\n";
		if ( $this->getSubject() != '' ) {
			$email_body .= TTi18n::gettext( 'Subject' ) . ': ' . $this->getSubject() . "\n";
		}

		$email_body .= TTi18n::gettext( 'Link' ) . ': <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . '">' . APPLICATION_NAME . ' ' . TTi18n::getText( 'Login' ) . '</a>';

		//Define subject/body variables here.
		$search_arr = [
				'#employee_first_name#',
				'#employee_last_name#',
		];

		$replace_arr = [
				null,
				null,
		];

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text( 'Subject: ' . $subject, __FILE__, __LINE__, __METHOD__, 10 );

		$headers = [
				'From'    => $from,
				'Subject' => $subject,
				'Bcc'     => $bcc,
				//Reply-To/Return-Path are handled in TTMail.
		];

		$body = '<pre>' . str_replace( $search_arr, $replace_arr, $email_body ) . '</pre>';
		Debug::Text( 'Body: ' . $body, __FILE__, __LINE__, __METHOD__, 10 );

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody( $body );

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == true ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Email Message to' ) . ': ' . $to . ' Bcc: ' . $headers['Bcc'], null, $this->getTable() );

			return true;
		}

		return true; //Always return true
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Parent
		if ( $this->getParent() != TTUUID::getZeroID() ) {
			$mlf = TTnew( 'MessageListFactory' ); /** @var MessageListFactory $mlf */
			$this->Validator->isResultSetWithRows( 'parent',
												   $mlf->getByID( $this->getParent() ),
												   TTi18n::gettext( 'Parent is invalid' )
			);
		}
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  $this->getOptions( 'type' )
		);
		// Object ID
		$this->Validator->isResultSetWithRows( 'object',
											   $this->getObjectHandler()->getByID( $this->getObject() ),
											   TTi18n::gettext( 'Object ID is invalid' )
		);
		// Priority
		$this->Validator->inArrayKey( 'priority',
									  $this->getPriority(),
									  TTi18n::gettext( 'Invalid Priority' ),
									  $this->getOptions( 'priority' )
		);
		// Status
		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status' ),
									  $this->getOptions( 'status' )
		);
		// Date
		$this->Validator->isDate( 'status_date',
								  $this->getStatusDate(),
								  TTi18n::gettext( 'Incorrect Date' )
		);
		// Subject
		if ( $this->getSubject() != '' ) {
			$this->Validator->isLength( 'subject',
										$this->getSubject(),
										TTi18n::gettext( 'Invalid Subject length' ),
										2,
										100
			);
		}
		// Body
		$this->Validator->isLength( 'body',
									$this->getBody(),
									TTi18n::gettext( 'Invalid Body length' ),
									2, //Allow the word: "ok", or "done" to at least be a response.
									1024
		);
		// Acknowledge Date
		$this->Validator->isDate( 'ack_date',
								  $this->getAckDate(),
								  TTi18n::gettext( 'Invalid Acknowledge Date' )
		);
		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'ack_by',
											   $ulf->getByID( $this->getAckBy() ),
											   TTi18n::gettext( 'Incorrect User' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Only email message notifications when they are not deleted and UNREAD still. Other it may email when a message is marked as read as well.
		//Don't email messages when they are being deleted.
		if ( $this->getDeleted() == false && $this->getStatus() == 10 ) {
			$this->emailMessage();
		}

		if ( $this->getStatus() == 20 ) {
			global $current_user;

			$this->removeCache( $current_user->getId() );
		}

		return true;
	}

}

?>
