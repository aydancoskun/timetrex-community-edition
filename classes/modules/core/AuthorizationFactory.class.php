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
 * @package Core
 */
class AuthorizationFactory extends Factory {
	protected $table = 'authorizations';
	protected $pk_sequence_name = 'authorizations_id_seq'; //PK Sequence name

	protected $obj_handler = NULL;
	protected $obj_handler_obj = NULL;
	protected $hierarchy_arr = NULL;

	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
										//10 => 'default_schedule',
										//20 => 'schedule_amendment',
										//30 => 'shift_amendment',
										//40 => 'pay_stub_amendment',

										//52 => 'request_vacation',
										//54 => 'request_missed_punch',
										//56 => 'request_edit_punch',
										//58 => 'request_absence',
										//59 => 'request_schedule',
										90 => 'timesheet',

										200 => 'expense',

										//50 => 'request', //request_other
										1010 => 'request_punch',
										1020 => 'request_punch_adjust',
										1030 => 'request_absence',
										1040 => 'request_schedule',
										1100 => 'request_other',
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-created_by' => TTi18n::gettext('Name'),
										'-1020-created_date' => TTi18n::gettext('Date'),
										'-1030-authorized' => TTi18n::gettext('Authorized'),
										//'-1100-object_type' => TTi18n::gettext('Object Type'),

										//'-2020-updated_by' => TTi18n::gettext('Updated By'),
										//'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'created_by',
								'created_date',
								'authorized',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'object_type_id' => 'ObjectType',
										'object_type' => FALSE,
										'object_id' => 'Object',
										'authorized' => 'Authorized',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCurrentUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getCurrentUser(), 'user_obj' );
	}

	//Stores the current user in memory, so we can determine if its the employee verifying, or a superior.
	function getCurrentUser() {
		if ( isset($this->tmp_data['current_user_id']) ) {
			return $this->tmp_data['current_user_id'];
		}
	}
	function setCurrentUser($id) {
		$id = trim($id);

		$this->tmp_data['current_user_id'] = $id;

		return TRUE;
	}


	function getHierarchyArray() {
		if ( is_array($this->hierarchy_arr) ) {
			return $this->hierarchy_arr;
		} else {
			$user_id = $this->getCurrentUser();

			if ( is_object( $this->getObjectHandler() ) ) {
				$this->getObjectHandler()->getByID( $this->getObject() );
				$current_obj = $this->getObjectHandler()->getCurrent();
				$object_user_id = $current_obj->getUser();

				if ( $object_user_id > 0 ) {
					Debug::Text(' Authorizing User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					Debug::Text(' Object User ID: '. $object_user_id, __FILE__, __LINE__, __METHOD__, 10);

					$ulf = TTnew( 'UserListFactory' );
					$company_id = $ulf->getById( $object_user_id )->getCurrent()->getCompany();
					Debug::Text(' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10);

					$hlf = TTnew( 'HierarchyListFactory' );
					$this->hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $object_user_id, $this->getObjectType(), FALSE);

					Debug::Arr($this->hierarchy_arr, ' Hierarchy Arr: ', __FILE__, __LINE__, __METHOD__, 10);
					return $this->hierarchy_arr;
				} else {
					Debug::Text(' Could not find Object User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::Text(' ERROR: No ObjectHandler defined...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return FALSE;
	}


	function getHierarchyChildLevelArray() {
		$retval = array();

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) AND count( $parent_arr ) > 0 ) {
			$next_level = FALSE;
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = TRUE;
					continue;
				}

				if ( $next_level == TRUE ) {
					//Debug::Arr( $level_parent_arr, ' Child: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = array_merge( $retval, $level_parent_arr); //Append from all levels.
				}
			}
		}

		if ( count($retval) > 0 ) {
			return $retval;
		}

		return FALSE;
	}

	function getHierarchyCurrentLevelArray( $force = FALSE ) {
		$retval = FALSE;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) AND count( $parent_arr ) > 0 ) {
			$next_level = FALSE;
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = TRUE;
					if ( $force == FALSE ) {
						continue;
					}
				}

				if ( $next_level == TRUE  ) { //Current level is alway one level lower, as this often gets called after the level has been changed.
					$retval = $level_parent_arr;
					//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}

			if ( $next_level == TRUE AND $retval == FALSE ) {
				//Current level was the top and only level.
				$retval = $level_parent_arr;
				//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $retval;
	}

	function getHierarchyParentLevelArray() {
		$retval = FALSE;

		$user_id = (int)$this->getCurrentUser();
		$parent_arr = array_reverse( (array)$this->getHierarchyArray() );
		if ( is_array( $parent_arr ) AND count( $parent_arr ) > 0 ) {
			$next_level = FALSE;
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( is_array( $level_parent_arr ) AND in_array( $user_id, $level_parent_arr ) ) {
					$next_level = TRUE;
					continue;
				}

				//Since this loops in reverse, always assume the first element is the parent for cases where a suborindate may be submitting the object (ie: request) and it needs to go to the direct superiors.
				if ( $next_level == TRUE ) {
					//Debug::Arr( $level_parent_arr, ' Parents: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $level_parent_arr;
					break;
				}
			}

			//If we get here without finding a parent, use the lowest lower parents by default.
			if ( $next_level == FALSE ) {
				reset($parent_arr);
				$retval = $parent_arr[key($parent_arr)];
			}
		}

		return $retval;
	}

	//This will return false if it can't find a hierarchy, or if its at the top level (1) and can't find a higher level.
	function getNextHierarchyLevel() {
		$retval = FALSE;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array($user_id, $level_parent_arr) ) {
					break;
				}
				$retval = $level;
			}
		}

		if ( $retval < 1 ) {
			Debug::Text(' ERROR, hierarchy level goes past 1... This shouldnt happen...', __FILE__, __LINE__, __METHOD__, 10);
			$retval = FALSE;
		}

		return $retval;
	}

	static function getInitialHierarchyLevel( $company_id, $user_id, $hierarchy_type_id ) {
		$hierarchy_highest_level = 99;
		if ( $company_id > 0 AND $user_id > 0 AND $hierarchy_type_id > 0 ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, $hierarchy_type_id, FALSE );
			if ( isset( $hierarchy_arr ) AND is_array( $hierarchy_arr ) ) {
				Debug::Arr( $hierarchy_arr, ' aUser ID ' . $user_id . ' Type ID: ' . $hierarchy_type_id . ' Array: ', __FILE__, __LINE__, __METHOD__, 10 );

				//See if current user is in superior list, if so, start at one level up in the hierarchy, unless its level 1.
				foreach ( $hierarchy_arr as $level => $superior_user_ids ) {
					if ( in_array( $user_id, $superior_user_ids, TRUE ) == TRUE ) {
						Debug::Text( '   Found user in superior list at level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );

						$i = $level;
						while ( isset( $hierarchy_arr[$i] ) ) {
							if ( $i != 1 ) {
								Debug::Text( '    Removing lower level: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
								unset( $hierarchy_arr[$i] );
							}
							$i++;
						}
					}
				}

				Debug::Arr( $hierarchy_arr, ' bUser ID ' . $user_id . ' Type ID: ' . $hierarchy_type_id . ' Array: ', __FILE__, __LINE__, __METHOD__, 10 );
				$hierarchy_arr = array_keys( $hierarchy_arr );
				$hierarchy_highest_level = end( $hierarchy_arr );
			}
		}

		Debug::Text(' Returning initial hierarchy level to: '. $hierarchy_highest_level, __FILE__, __LINE__, __METHOD__, 10);
		return $hierarchy_highest_level;
	}

	function isValidParent() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			krsort($parent_arr);
			foreach( $parent_arr as $level_parent_arr ) {
				if ( in_array($user_id, $level_parent_arr) ) {
					return TRUE;
				}
			}
		}

		Debug::Text(' Authorizing User is not a parent of the object owner: ', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function isFinalAuthorization() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			//Check that level 1 parent exists
			if ( isset($parent_arr[1]) AND in_array( $user_id, $parent_arr[1] ) ) {
				Debug::Text(' Final Authorization!', __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		}

		Debug::Text(' NOT Final Authorization!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {
			switch ( $this->getObjectType() ) {
				case 90: //TimeSheet
					$this->obj_handler = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
					break;
				case 200:
					$this->obj_handler = TTnew( 'UserExpenseListFactory' );
					break;
				case 50: //Requests
				case 1010:
				case 1020:
				case 1030:
				case 1040:
				case 1100:
					$this->obj_handler = TTnew( 'RequestListFactory' );
					break;
			}

			return $this->obj_handler;
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return (int)$this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getObject() {
		if ( isset($this->data['object_id']) ) {
			return (int)$this->data['object_id'];
		}

		return FALSE;
	}
	function setObject($id) {
		$id = trim($id);

		if (	$this->Validator->isResultSetWithRows(	'object',
														( is_object( $this->getObjectHandler() ) ) ? $this->getObjectHandler()->getByID($id) : FALSE,
														TTi18n::gettext('Object ID is invalid')
														) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		return $this->fromBool( $this->data['authorized'] );
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return TRUE;
	}

	function clearHistory() {
		Debug::text('Clearing Authorization History For Type: '. $this->getObjectType() .' ID: '. $this->getObject(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getObjectType() === FALSE OR $this->getObject() === FALSE ) {
			Debug::text('Clearing Authorization History FAILED!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$alf = TTnew( 'AuthorizationListFactory' );
		$alf->getByObjectTypeAndObjectId( $this->getObjectType(), $this->getObject() );
		foreach( $alf as $authorization_obj ) {
			$authorization_obj->setDeleted(TRUE);
			$authorization_obj->Save();
		}

		return TRUE;
	}

	function getObjectHandlerObject() {
		if ( is_object($this->obj_handler_obj) ) {
			return $this->obj_handler_obj;
		} else {
			$is_final_authorization = $this->isFinalAuthorization();

			//Get user_id of object.
			$this->getObjectHandler()->getByID( $this->getObject() );
			$this->obj_handler_obj = $this->getObjectHandler()->getCurrent();
			if ( $this->getAuthorized() === TRUE ) {
				if ( $is_final_authorization === TRUE ) {
					if ( $this->getCurrentUser() != $this->obj_handler_obj->getUser() ) {
						Debug::Text('  Approving Authorization... Final Authorizing Object: '. $this->getObject() .' - Type: '. $this->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);
						$this->obj_handler_obj->setAuthorizationLevel( 1 );
						$this->obj_handler_obj->setStatus(50); //Active/Authorized
						$this->obj_handler_obj->setAuthorized(TRUE);
					} else {
						Debug::Text('  Currently logged in user is authorizing (or submitting as new) their own request, not authorizing...', __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('  Approving Authorization, moving to next level up...', __FILE__, __LINE__, __METHOD__, 10);
					$current_level = $this->obj_handler_obj->getAuthorizationLevel();
					if ( $current_level > 1 ) { //Highest level is 1, so no point in making it less than that.

						//Get the next level above the current user doing the authorization, in case they have dropped down a level or two.
						$next_level = $this->getNextHierarchyLevel();
						if ( $next_level !== FALSE AND $next_level < $current_level ) {
							Debug::text('  Current Level: '. $current_level .' Moving Up To Level: '. $next_level, __FILE__, __LINE__, __METHOD__, 10);
							$this->obj_handler_obj->setAuthorizationLevel( $next_level );
						}
					}
					unset( $current_level, $next_level );
				}
			} else {
				Debug::text('  Declining Authorization...', __FILE__, __LINE__, __METHOD__, 10);
				$this->obj_handler_obj->setStatus(55); //'AUTHORIZATION DECLINED'
				$this->obj_handler_obj->setAuthorized(FALSE);
			}

			return $this->obj_handler_obj;
		}
	}

	function getEmailAuthorizationAddresses() {
		$object_handler_user_id = $this->getObjectHandlerObject()->getUser(); //Object handler (request) user_id.

		$is_final_authorization = $this->isFinalAuthorization();
		$authorization_level = $this->getObjectHandlerObject()->getAuthorizationLevel(); //This is the *new* level, not the old level.

		$hierarchy_current_level_arr = $this->getHierarchyCurrentLevelArray();
		Debug::Arr( $hierarchy_current_level_arr, '  Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Is Final Auth: '. (int)$is_final_authorization .' Object Handler User ID: '. $object_handler_user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getAuthorized() == TRUE AND $authorization_level == 0 ) {
			//Final authorization has taken place
			//Email original submittor and all lower level superiors?
			$user_ids = $this->getHierarchyChildLevelArray();

			if ( strpos( get_class( $this->getObjectHandlerObject() ), 'PayPeriodTimeSheetVerify' ) === 0 ) { //Allow for PayStubListFactoryPlugin to match as well.
				//Check to see what type of timesheet verification is required, if its superior only, don't email the employee to avoid confusion.
				if ( $this->getObjectHandlerObject()->getVerificationType() != 30 ) {
					$user_ids[] = $object_handler_user_id;
				} else {
					Debug::text('  TimeSheetVerification for superior only, dont email employee...', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				$user_ids[] = $object_handler_user_id;
			}
			//Debug::Arr($user_ids , '  aAuthorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ' , __FILE__, __LINE__, __METHOD__, 10);
		} else {
			//Debug::Text('  bAuthorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized(), __FILE__, __LINE__, __METHOD__, 10);
			//Final authorization has *not* yet taken place
			if ( $this->getObjectHandlerObject()->getStatus() == 55 ) { //Declined
				//Authorization declined. Email original submittor and all lower level superiors?
				$user_ids = $this->getHierarchyChildLevelArray();
				$user_ids[] = $object_handler_user_id;
				//Debug::Arr($user_ids , '  b1Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ', __FILE__, __LINE__, __METHOD__, 10);
			} elseif ( $is_final_authorization == TRUE AND $this->getCurrentUser() == $object_handler_user_id AND $this->getAuthorized() == TRUE AND $authorization_level == 1 ) {
				//Subordinate who is also a superior at the top and only level of the hierarchy is submitting a request.
				$user_ids = $this->getHierarchyCurrentLevelArray( TRUE ); //Force to real current level.
				//Debug::Arr($user_ids , '  b2Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				//Authorized at a middle level, email current level superiors only so they know its waiting on them.
				$user_ids = $this->getHierarchyParentLevelArray();
				//Debug::Arr($user_ids , '  b3Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Parent: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		//Remove the current authorizing user from the array, as they don't need to be notified as they are performing the action.
		$user_ids = array_diff( (array)$user_ids, array( $this->getCurrentUser() ) ); //CurrentUser is currently logged in user.
		if ( isset($user_ids) AND is_array($user_ids) AND count($user_ids) > 0 ) {
			//Get user preferences and determine if they accept email notifications.
			Debug::Arr($user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__, 10);

			$uplf = TTnew( 'UserPreferenceListFactory' );
			//$uplf->getByUserId( $user_ids );
			$uplf->getByUserIdAndStatus( $user_ids, 10 ); //Only email ACTIVE employees/supervisors.
			if ( $uplf->getRecordCount() > 0 ) {
				$retarr = array();
				foreach( $uplf as $up_obj ) {
					if ( $up_obj->getEnableEmailNotificationMessage() == TRUE AND is_object( $up_obj->getUserObject() ) AND $up_obj->getUserObject()->getStatus() == 10 ) {
						if ( $up_obj->getUserObject()->getWorkEmail() != '' AND $up_obj->getUserObject()->getWorkEmailIsValid() == TRUE ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getWorkEmail(), $up_obj->getUserObject() );
						}

						if ( $up_obj->getEnableEmailNotificationHome() AND is_object( $up_obj->getUserObject() ) AND $up_obj->getUserObject()->getHomeEmail() != '' AND $up_obj->getUserObject()->getHomeEmailIsValid() == TRUE ) {
							$retarr[] = Misc::formatEmailAddress( $up_obj->getUserObject()->getHomeEmail(), $up_obj->getUserObject() );
						}
					}
				}

				if ( isset($retarr) ) {
					Debug::Arr($retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__, 10);
					return array_unique($retarr);
				}
			} else {
				Debug::Text('No user preferences available, or user is not active...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return FALSE;
	}

	function emailAuthorization() {
		Debug::Text('emailAuthorization: ', __FILE__, __LINE__, __METHOD__, 10);

		$email_to_arr = $this->getEmailAuthorizationAddresses();
		if ( $email_to_arr == FALSE ) {
			return FALSE;
		}

		//Get from User Object so we can include more information in the message.
		if ( is_object( $this->getCurrentUserObject() ) ) {
			$u_obj = $this->getCurrentUserObject();
		} else {
			Debug::Text('From object does not exist: '. $this->getCurrentUser(), __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$object_handler_user_obj = $this->getObjectHandlerObject()->getUserObject(); //Object handler (request) user_id.
		$status_label = Option::getByKey( $this->getObjectHandlerObject()->getStatus(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions('status') ) ); //PENDING, AUTHORIZED, DECLINED
		switch( $this->getObjectType() ) {
			case 90: //TimeSheet
				$object_type_label = TTi18n::getText('TimeSheet');
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText('Pay Period') .': '. TTDate::getDate('DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getStartDate() ) .' -> '. TTDate::getDate('DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getEndDate() );
				break;
			case 200: //Expense
				$object_type_label = TTi18n::getText('Expense');
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText('Incurred').': '. TTDate::getDate('DATE', $this->getObjectHandlerObject()->getIncurredDate() ) .' '. TTi18n::getText('for') .' '. $this->getObjectHandlerObject()->getGrossAmount();
				break;
			default:
				$object_type_label = TTi18n::getText('Request');
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText('Type').': '. Option::getByKey( $this->getObjectHandlerObject()->getType(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions('type') ) ) .' '. TTi18n::getText('on').' '. TTDate::getDate('DATE', $this->getObjectHandlerObject()->getDateStamp() );
				break;
		}

		$from = $reply_to = '"'. APPLICATION_NAME .' - '. $object_type_label .' '. TTi18n::gettext('Authorization') .'" <'. Misc::getEmailLocalPart() .'@'. Misc::getEmailDomain() .'>';

		Debug::Text('To: '. implode(',', $email_to_arr), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('From: '. $from .' Reply-To: '. $reply_to, __FILE__, __LINE__, __METHOD__, 10);

		//Define subject/body variables here.
		$search_arr = array(
				'#object_type#',
				'#object_type_short_description#',
				'#object_type_long_description#',
				'#status#',

				'#current_employee_first_name#',
				'#current_employee_last_name#',

				'#object_employee_first_name#',
				'#object_employee_last_name#',
				'#object_employee_default_branch#',
				'#object_employee_default_department#',
				'#object_employee_group#',
				'#object_employee_title#',

				'#company_name#',
				'#link#',
		);

		$replace_arr = array(
				$object_type_label,
				$object_type_short_description,
				$object_type_long_description,
				$status_label,

				$u_obj->getFirstName(),
				$u_obj->getLastName(),

				$object_handler_user_obj->getFirstName(),
				$object_handler_user_obj->getLastName(),
				( is_object( $object_handler_user_obj->getDefaultBranchObject() ) ) ? $object_handler_user_obj->getDefaultBranchObject()->getName() : NULL,
				( is_object( $object_handler_user_obj->getDefaultDepartmentObject() ) ) ? $object_handler_user_obj->getDefaultDepartmentObject()->getName() : NULL,
				( is_object( $object_handler_user_obj->getGroupObject() ) ) ? $object_handler_user_obj->getGroupObject()->getName() : NULL,
				( is_object( $object_handler_user_obj->getTitleObject() ) ) ? $object_handler_user_obj->getTitleObject()->getName() : NULL,

				( is_object( $object_handler_user_obj->getCompanyObject() ) ) ? $object_handler_user_obj->getCompanyObject()->getName() : NULL,
				NULL,
		);

		$email_subject = '#object_type# by #object_employee_first_name# #object_employee_last_name# #status#' .' '. TTi18n::gettext('in') .' '. APPLICATION_NAME;

		$email_body = TTi18n::gettext('*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*')."\n\n";
		$email_body .= '#object_type# by #object_employee_first_name# #object_employee_last_name# #status#' .' '. TTi18n::gettext('in') .' '. APPLICATION_NAME."\n";
		$email_body .= ( $replace_arr[2] != '' ) ? '#object_type_long_description#'."\n" : NULL;
		$email_body .= "\n";
		$email_body .= ( $replace_arr[8] != '' ) ? TTi18n::gettext('Default Branch').': #object_employee_default_branch#'."\n" : NULL;
		$email_body .= ( $replace_arr[9] != '' ) ? TTi18n::gettext('Default Department').': #object_employee_default_department#'."\n" : NULL;
		$email_body .= ( $replace_arr[10] != '' ) ? TTi18n::gettext('Group').': #object_employee_group#'."\n" : NULL;
		$email_body .= ( $replace_arr[11] != '' ) ? TTi18n::gettext('Title').': #object_employee_title#'."\n" : NULL;

		$email_body .= TTi18n::gettext('Link').': <a href="'. Misc::getURLProtocol() .'://'. Misc::getHostName().Environment::getDefaultInterfaceBaseURL().'">'.APPLICATION_NAME.' '. TTi18n::gettext('Login') .'</a>';

		$email_body .= ( $replace_arr[12] != '' ) ? "\n\n\n".TTi18n::gettext('Company').': #company_name#'."\n" : NULL; //Always put at the end

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__, 10);

		$headers = array(
				'From'		=> $from,
				'Subject'	=> $subject,
				//Reply-To/Return-Path are handled in TTMail.
		);

		$body = '<html><body><pre>'.str_replace( $search_arr, $replace_arr, $email_body ).'</pre></body></html>';
		Debug::Text('Body: '. $body, __FILE__, __LINE__, __METHOD__, 10);

		$mail = new TTMail();
		$mail->setTo( $email_to_arr );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody($body);

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == TRUE ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Email Message to').': '. implode(', ', $email_to_arr), NULL, $this->getTable() );
			return TRUE;
		}

		return TRUE; //Always return true
	}

	//Used by Request/TimeSheetVerification/Expense when initially saving a record to notify the immediate superiors, rather than using the message notification.
	static function emailAuthorizationOnInitialObjectSave($current_user_id, $object_type_id, $object_id) {
		$authorization_obj = TTNew('AuthorizationFactory');
		$authorization_obj->setObjectType( $object_type_id );
		$authorization_obj->setObject( $object_id );
		$authorization_obj->setCurrentUser( $current_user_id );
		$authorization_obj->setAuthorized( TRUE );
		$authorization_obj->emailAuthorization(); //Don't save this...

		return TRUE;
	}


	function Validate( $ignore_warning = TRUE ) {
		if ( $this->getDeleted() === FALSE
				AND $this->isFinalAuthorization() === FALSE
				AND $this->isValidParent() === FALSE ) {
			$this->Validator->isTrue(		'parent',
											FALSE,
											TTi18n::gettext('User authorizing this object is not a parent of it'));

			return FALSE;
		}

		if ( $this->getDeleted() == FALSE AND is_object( $this->getObjectHandlerObject() ) AND $this->getObjectHandlerObject()->isValid() == FALSE ) {
			Debug::text('  ObjectHandler Validation Failed, pass validation errors up the chain...', __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
		}

		return TRUE;
	}

	function preSave() {
		//Debug::Text(' Calling preSave!: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->StartTransaction();

		return TRUE;
	}

	function postSave() {
		if ( $this->getDeleted() == FALSE ) {
			if ( is_object( $this->getObjectHandlerObject() ) AND $this->getObjectHandlerObject()->isValid() == TRUE ) {
				Debug::text('  Object Valid...', __FILE__, __LINE__, __METHOD__, 10);
				//Return true if object saved correctly.
				$retval = $this->getObjectHandlerObject()->Save( FALSE );
				if ( $this->getObjectHandlerObject()->isValid() == FALSE ) {
					Debug::text('  Object postSave validation FAILED!', __FILE__, __LINE__, __METHOD__, 10);
					$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
				} else {
					Debug::text('  Object postSave validation SUCCESS!', __FILE__, __LINE__, __METHOD__, 10);
					$this->emailAuthorization();
				}

				if ( $retval === TRUE ) {
					$this->CommitTransaction();
					return TRUE;
				} else {
					$this->FailTransaction();
				}
			} else {
				//Always fail the transaction if we get this far.
				//This stops authorization entries from being inserted.
				$this->FailTransaction();
			}

			$this->CommitTransaction(); //preSave() starts the transaction
			return FALSE;
		}

		$this->CommitTransaction(); //preSave() starts the transaction

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'object_type':
							Debug::text('  Object Type...', __FILE__, __LINE__, __METHOD__, 10);
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
			$this->getPermissionColumns( $data, $this->getColumn('user_id'), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ($this->getAuthorized() === TRUE ) {
			$authorized = TTi18n::getText('True');
		} else {
			$authorized = TTi18n::getText('False');
		}
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Authorization Object Type').': '. ucwords( str_replace('_', ' ', Option::getByKey( $this->getObjectType(), $this->getOptions('object_type') ) ) ) .' '. TTi18n::getText('Authorized').': '. $authorized, NULL, $this->getTable() );
	}
}
?>
