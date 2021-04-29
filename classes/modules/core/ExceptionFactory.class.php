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
 * @package Core
 */
class ExceptionFactory extends Factory {
	protected $table = 'exception';
	protected $pk_sequence_name = 'exception_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $exception_policy_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				//Exception life-cycle
				//
				// - Exception occurs, such as missed out punch, in late.
				//	 - If the exception is pre-mature, we wait 16-24hrs for it to become a full-blown exception
				// - If the exception requires authorization, it sits in a pending state waiting for supervsior intervention.
				// - Supervisor authorizes the exception, or makes a correction, leaves a note or something.
				//	 - Exception no longer appears on timesheet/exception list.
				$retval = [
						5  => TTi18n::gettext( 'Pre-Mature' ),
						30 => TTi18n::gettext( 'PENDING AUTHORIZATION' ),
						40 => TTi18n::gettext( 'AUTHORIZATION OPEN' ),
						50 => TTi18n::gettext( 'ACTIVE' ),
						55 => TTi18n::gettext( 'AUTHORIZATION DECLINED' ),
						60 => TTi18n::gettext( 'DISABLED' ),
						70 => TTi18n::gettext( 'Corrected' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						//'-1005-user_status' => TTi18n::gettext('Employee Status'),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1039-group'              => TTi18n::gettext( 'Group' ),
						'-1050-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1060-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1070-branch'             => TTi18n::gettext( 'Branch' ),
						'-1080-department'         => TTi18n::gettext( 'Department' ),
						'-1090-country'            => TTi18n::gettext( 'Country' ),
						'-1100-province'           => TTi18n::gettext( 'Province' ),

						'-1120-date_stamp'               => TTi18n::gettext( 'Date' ),
						'-1130-severity'                 => TTi18n::gettext( 'Severity' ),
						'-1140-exception_policy_type'    => TTi18n::gettext( 'Exception' ),
						'-1150-exception_policy_type_id' => TTi18n::gettext( 'Code' ),
						'-1160-policy_group'             => TTi18n::gettext( 'Policy Group' ),
						'-1170-permission_group'         => TTi18n::gettext( 'Permission Group' ),
						'-1200-pay_period_schedule'      => TTi18n::gettext( 'Pay Period Schedule' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( [ 'date_stamp', 'severity', 'exception_policy_type', 'exception_policy_type_id' ], Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'date_stamp',
						'severity',
						'exception_policy_type',
						'exception_policy_type_id',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                          => 'ID',
				'date_stamp'                  => false,
				'pay_period_start_date'       => false,
				'pay_period_end_date'         => false,
				'pay_period_transaction_date' => false,
				'pay_period'                  => false,
				'exception_policy_id'         => 'ExceptionPolicyID',
				'punch_control_id'            => 'PunchControlID',
				'punch_id'                    => 'PunchID',
				'type_id'                     => 'Type',
				'type'                        => false,
				'severity_id'                 => false,
				'severity'                    => false,
				'exception_color'             => 'Color',
				'exception_background_color'  => 'BackgroundColor',
				'exception_policy_type_id'    => false,
				'exception_policy_type'       => false,
				'policy_group'                => false,
				'permission_group'            => false,
				'pay_period_schedule'         => false,
				//'enable_demerit' => 'EnableDemerits',

				'pay_period_id'          => false,
				'pay_period_schedule_id' => false,

				'user_id'               => false,
				'first_name'            => false,
				'last_name'             => false,
				'country'               => false,
				'province'              => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'group'                 => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,

				'branch_id'     => false,
				'branch'        => false,
				'department_id' => false,
				'department'    => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool
	 */
	function getExceptionPolicyObject() {
		return $this->getGenericObject( 'ExceptionPolicyListFactory', $this->getExceptionPolicyID(), 'exception_policy_obj' );
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriod( $value = null ) {
		if ( $value == null ) {
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}

		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.
		if ( $value > 0 ) {
			return $this->setGenericDataValue( 'date_stamp', $value );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getExceptionPolicyID() {
		return $this->getGenericDataValue( 'exception_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setExceptionPolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'exception_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchControlID() {
		return $this->getGenericDataValue( 'punch_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPunchControlID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'punch_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchID() {
		return $this->getGenericDataValue( 'punch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPunchID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'punch_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getEnableDemerits() {
		return $this->getGenericDataValue( 'enable_demerit' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableDemerits( $value ) {
		$this->setGenericDataValue( 'enable_demerit', $value );

		return true;
	}

	/**
	 * @return bool|string
	 */
	function getBackgroundColor() {
		//Use HTML color codes so they work in Flex too.
		$retval = false;
		if ( $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn( 'severity_id' ) != '' ) {
				switch ( $this->getColumn( 'severity_id' ) ) {
					case 10:
						$retval = false;
						break;
					case 20:
						$retval = '#FFFF00'; #'yellow';
						break;
					case 25:
						$retval = '#FF9900'; #'orange';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getColor() {
		$retval = false;

		//Use HTML color codes so they work in Flex too.
		if ( $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn( 'severity_id' ) != '' ) {
				switch ( $this->getColumn( 'severity_id' ) ) {
					case 10:
						$retval = '#000000'; #'black';
						break;
					case 20:
						$retval = '#0000FF'; #'blue';
						break;
					case 25:
						$retval = '#FF9900'; #'blue';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}

	/**
	 * @param object $u_obj
	 * @param object $ep_obj
	 * @return array|bool
	 */
	function getEmailExceptionAddresses( $u_obj = null, $ep_obj = null ) {
		Debug::text( ' Attempting to Email Notification...', __FILE__, __LINE__, __METHOD__, 10 );

		//Make sure type is not pre-mature.
		if ( $this->getType() > 5 ) {
			if ( !is_object( $ep_obj ) ) {
				$ep_obj = $this->getExceptionPolicyObject();
			}

			//Make sure exception policy email notifications are enabled.
			if ( $ep_obj->getEmailNotification() > 0 ) {
				$retarr = [];
				if ( !is_object( $u_obj ) ) {
					$u_obj = $this->getUserObject();
				}

				//Make sure user email notifications are enabled and user is *not* terminated.
				if ( ( $ep_obj->getEmailNotification() == 10 || $ep_obj->getEmailNotification() == 100 )
						&& is_object( $u_obj->getUserPreferenceObject() )
						&& $u_obj->getUserPreferenceObject()->getEnableEmailNotificationException() == true
						&& $u_obj->getStatus() == 10 && $u_obj->getEnableLogin() == true ) {
					Debug::Text( ' Emailing exception to user!', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $u_obj->getWorkEmail() != '' && $u_obj->getWorkEmailIsValid() == true ) {
						$retarr[] = Misc::formatEmailAddress( $u_obj->getWorkEmail(), $u_obj );
					}
					if ( $u_obj->getUserPreferenceObject()->getEnableEmailNotificationHome() == true && $u_obj->getHomeEmail() != '' && $u_obj->getHomeEmailIsValid() == true ) {
						$retarr[] = Misc::formatEmailAddress( $u_obj->getHomeEmail(), $u_obj );
					}
				} else {
					Debug::Text( ' Skipping email to user.', __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Make sure supervisor email notifcations are enabled
				if ( $ep_obj->getEmailNotification() == 20 || $ep_obj->getEmailNotification() == 100 ) {
					//Find supervisor(s)
					$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
					$parent_user_id = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $u_obj->getCompany(), $u_obj->getId(), 80 );
					if ( $parent_user_id != false ) {
						//Parent could be multiple supervisors, make sure we email them all.
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getByIdAndCompanyId( $parent_user_id, $u_obj->getCompany() );
						if ( $ulf->getRecordCount() > 0 ) {
							foreach ( $ulf as $parent_user_obj ) {
								//Make sure supervisor has exception notifications enabled and is *not* terminated
								if ( is_object( $parent_user_obj->getUserPreferenceObject() )
										&& $parent_user_obj->getUserPreferenceObject()->getEnableEmailNotificationException() == true
										&& $parent_user_obj->getStatus() == 10 && $parent_user_obj->getEnableLogin() == true ) {
									Debug::Text( ' Emailing exception to supervisor!', __FILE__, __LINE__, __METHOD__, 10 );
									if ( $parent_user_obj->getWorkEmail() != '' && $parent_user_obj->getWorkEmailIsValid() == true ) {
										$retarr[] = Misc::formatEmailAddress( $parent_user_obj->getWorkEmail(), $parent_user_obj );
									}

									if ( $parent_user_obj->getUserPreferenceObject()->getEnableEmailNotificationHome() == true && $parent_user_obj->getHomeEmail() != '' && $parent_user_obj->getHomeEmailIsValid() == true ) {
										$retarr[] = Misc::formatEmailAddress( $parent_user_obj->getHomeEmail(), $parent_user_obj );
									}
								} else {
									Debug::Text( ' Skipping email to supervisor.', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						}
					} else {
						Debug::Text( ' No Hierarchy Parent Found, skipping email to supervisor.', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}

				if ( empty( $retarr ) == false ) {
					return array_unique( $retarr );
				} else {
					Debug::text( ' No user objects to email too...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::text( ' Exception Policy Email Exceptions are disabled, skipping email...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::text( ' Pre-Mature exception, or not in production mode, skipping email...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}


	/*

		What do we pass the emailException function?
			To address, CC address (home email) and Bcc (supervisor) address?

	*/
	/**
	 * @param object $u_obj
	 * @param int $date_stamp EPOCH
	 * @param object $punch_obj
	 * @param object $schedule_obj
	 * @param object $ep_obj
	 * @return bool
	 */
	function emailException( $u_obj, $date_stamp, $punch_obj = null, $schedule_obj = null, $ep_obj = null ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		if ( $date_stamp == '' ) {
			return false;
		}

		if ( !is_object( $ep_obj ) ) {
			$ep_obj = $this->getExceptionPolicyObject();
		}

		//Only email on active exceptions.
		if ( $this->getType() != 50 ) {
			return false;
		}

		$email_to_arr = $this->getEmailExceptionAddresses( $u_obj, $ep_obj );
		if ( $email_to_arr == false ) {
			return false;
		}

		$from = $reply_to = '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Exception' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';
		Debug::Text( 'To: ' . implode( ',', $email_to_arr ), __FILE__, __LINE__, __METHOD__, 10 );

		//Define subject/body variables here.
		$search_arr = [
				'#employee_first_name#',
				'#employee_last_name#',
				'#employee_default_branch#',
				'#employee_default_department#',
				'#employee_group#',
				'#employee_title#',
				'#exception_code#',
				'#exception_name#',
				'#exception_severity#',
				'#date#',
				'#company_name#',
				'#link#',
				'#schedule_start_time#',
				'#schedule_end_time#',
				'#schedule_branch#',
				'#schedule_department#',
				'#punch_time#',
		];

		$replace_arr = [
				$u_obj->getFirstName(),
				$u_obj->getLastName(),
				( is_object( $u_obj->getDefaultBranchObject() ) ) ? $u_obj->getDefaultBranchObject()->getName() : null,
				( is_object( $u_obj->getDefaultDepartmentObject() ) ) ? $u_obj->getDefaultDepartmentObject()->getName() : null,
				( is_object( $u_obj->getGroupObject() ) ) ? $u_obj->getGroupObject()->getName() : null,
				( is_object( $u_obj->getTitleObject() ) ) ? $u_obj->getTitleObject()->getName() : null,
				$ep_obj->getType(),
				Option::getByKey( $ep_obj->getType(), $ep_obj->getOptions( 'type' ) ),
				Option::getByKey( $ep_obj->getSeverity(), $ep_obj->getOptions( 'severity' ) ),
				TTDate::getDate( 'DATE', $date_stamp ),
				( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null,
				null,
				( is_object( $schedule_obj ) ) ? TTDate::getDate( 'TIME', $schedule_obj->getStartTime() ) : null,
				( is_object( $schedule_obj ) ) ? TTDate::getDate( 'TIME', $schedule_obj->getEndTime() ) : null,
				( is_object( $schedule_obj ) && is_object( $schedule_obj->getBranchObject() ) ) ? $schedule_obj->getBranchObject()->getName() : null,
				( is_object( $schedule_obj ) && is_object( $schedule_obj->getDepartmentObject() ) ) ? $schedule_obj->getDepartmentObject()->getName() : null,
				( is_object( $punch_obj ) ) ? TTDate::getDate( 'TIME', $punch_obj->getTimeStamp() ) : null,
		];

		$exception_email_subject = '#exception_name# (#exception_code#) ' . TTi18n::gettext( 'exception for' ) . ' #employee_first_name# #employee_last_name# ' . TTi18n::gettext( 'on' ) . ' #date#';
		$exception_email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$exception_email_body .= TTi18n::gettext( 'Employee' ) . ': #employee_first_name# #employee_last_name#' . "\n";
		$exception_email_body .= TTi18n::gettext( 'Date' ) . ': #date#' . "\n";
		$exception_email_body .= TTi18n::gettext( 'Exception' ) . ': #exception_name# (#exception_code#)' . "\n";
		$exception_email_body .= TTi18n::gettext( 'Severity' ) . ': #exception_severity#' . "\n";

		$exception_email_body .= ( $replace_arr[12] != '' || $replace_arr[13] != '' || $replace_arr[14] != '' || $replace_arr[15] != '' || $replace_arr[16] != '' ) ? "\n" : null;
		$exception_email_body .= ( $replace_arr[12] != '' && $replace_arr[13] != '' ) ? TTi18n::gettext( 'Schedule' ) . ': #schedule_start_time# - #schedule_end_time#' . "\n" : null;
		$exception_email_body .= ( $replace_arr[14] != '' ) ? TTi18n::gettext( 'Schedule Branch' ) . ': #schedule_branch#' . "\n" : null;
		$exception_email_body .= ( $replace_arr[15] != '' ) ? TTi18n::gettext( 'Schedule Department' ) . ': #schedule_department#' . "\n" : null;
		if ( $replace_arr[16] != '' ) {
			$exception_email_body .= TTi18n::gettext( 'Punch' ) . ': #punch_time#' . "\n";
		} else if ( $replace_arr[12] != '' && $replace_arr[13] != '' ) {
			$exception_email_body .= TTi18n::gettext( 'Punch' ) . ': ' . TTi18n::gettext( 'None' ) . "\n";
		}

		$exception_email_body .= ( $replace_arr[2] != '' || $replace_arr[3] != '' || $replace_arr[4] != '' || $replace_arr[5] != '' ) ? "\n" : null;
		$exception_email_body .= ( $replace_arr[2] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #employee_default_branch#' . "\n" : null;
		$exception_email_body .= ( $replace_arr[3] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #employee_default_department#' . "\n" : null;
		$exception_email_body .= ( $replace_arr[4] != '' ) ? TTi18n::gettext( 'Group' ) . ': #employee_group#' . "\n" : null;
		$exception_email_body .= ( $replace_arr[5] != '' ) ? TTi18n::gettext( 'Title' ) . ': #employee_title#' . "\n" : null;

		$exception_email_body .= "\n";
		$exception_email_body .= TTi18n::gettext( 'Link' ) . ': <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . '">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Login' ) . '</a>';

		$exception_email_body .= ( $replace_arr[10] != '' ) ? "\n\n\n" . TTi18n::gettext( 'Company' ) . ': #company_name#' . "\n" : null; //Always put at the end

		$exception_email_body .= "\n\n" . TTi18n::gettext( 'Email sent' ) . ': ' . TTDate::getDate( 'DATE+TIME', time() ) . "\n";

		$subject = str_replace( $search_arr, $replace_arr, $exception_email_subject );
		//Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__, 10);

		$headers = [
				'From'    => $from,
				'Subject' => $subject,
				//Reply-To/Return-Path are handled in TTMail.
		];

		$body = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $exception_email_body ) . '</pre></body></html>';
		Debug::Text( 'Body: ' . $body, __FILE__, __LINE__, __METHOD__, 10 );

		$mail = new TTMail();
		$mail->setTo( $email_to_arr );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody( $body );

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == true ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Email Exception' ) . ': ' . Option::getByKey( $ep_obj->getType(), $ep_obj->getOptions( 'type' ) ) . ' To: ' . implode( ', ', $email_to_arr ), $u_obj->getID(), $this->getTable() ); //Make sure this log entry is assigned to the user triggering the exception so it can be viewed in the audit log.
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

		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
		// Pay Period
		if ( $this->getPayPeriod() != false && $this->getPayPeriod() != TTUUID::getZeroID() ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$this->Validator->isResultSetWithRows( 'pay_period',
												   $pplf->getByID( $this->getPayPeriod() ),
												   TTi18n::gettext( 'Invalid Pay Period' )
			);
		}
		// Date
		if ( $this->getDateStamp() !== false ) {
			$this->Validator->isDate( 'date_stamp',
									  $this->getDateStamp(),
									  TTi18n::gettext( 'Incorrect date' ) );
			if ( $this->Validator->isError( 'date_stamp' ) == false ) {
				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
			}
		} else {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Incorrect date' )
			);
		}
		// Exception Policy ID
		if ( $this->getExceptionPolicyID() !== false && $this->getExceptionPolicyID() != TTUUID::getZeroID() ) {
			$eplf = TTnew( 'ExceptionPolicyListFactory' ); /** @var ExceptionPolicyListFactory $eplf */
			$this->Validator->isResultSetWithRows( 'exception_policy',
												   $eplf->getByID( $this->getExceptionPolicyID() ),
												   TTi18n::gettext( 'Invalid Exception Policy ID' )
			);
		}
		// Punch Control ID
		if ( $this->getPunchControlID() !== false && $this->getPunchControlID() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'punch_control',
												   $pclf->getByID( $this->getPunchControlID() ),
												   TTi18n::gettext( 'Invalid Punch Control ID' )
			);
		}
		// Punch ID
		if ( $this->getPunchID() !== false && $this->getPunchID() != TTUUID::getZeroID() ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$this->Validator->isResultSetWithRows( 'punch',
												   $plf->getByID( $this->getPunchID() ),
												   TTi18n::gettext( 'Invalid Punch ID' )
			);
		}
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == false && $this->getDateStamp() == false ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getPayPeriod() == false ) {
			$this->setPayPeriod();
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$variable_function_map = $this->getVariableToFunctionMap();

		$epf = TTnew( 'ExceptionPolicyFactory' ); /** @var ExceptionPolicyFactory $epf */
		$exception_policy_type_options = $epf->getOptions( 'type' );
		$exception_policy_severity_options = $epf->getOptions( 'severity' );

		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {
					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'pay_period_id':
						case 'pay_period_schedule_id':
							//case 'pay_period_start_date':
							//case 'pay_period_end_date':
							//case 'pay_period_transaction_date':
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'country':
						case 'province':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'branch_id':
						case 'branch':
						case 'department_id':
						case 'department':
						case 'severity_id':
						case 'exception_policy_type_id':
						case 'policy_group':
						case 'permission_group':
						case 'pay_period_schedule':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'severity':
							$data[$variable] = Option::getByKey( $this->getColumn( 'severity_id' ), $exception_policy_severity_options );
							break;
						case 'exception_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'exception_policy_type_id' ), $exception_policy_type_options );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'pay_period_start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_start_date' ) ) );
							break;
						case 'pay_period_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_end_date' ) ) );
							break;
						case 'pay_period':
						case 'pay_period_transaction_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_transaction_date' ) ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

}

?>
