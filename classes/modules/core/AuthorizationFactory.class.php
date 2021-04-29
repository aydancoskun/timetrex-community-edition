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
 * @package Core
 */
class AuthorizationFactory extends Factory {
	protected $table = 'authorizations';
	protected $pk_sequence_name = 'authorizations_id_seq'; //PK Sequence name

	protected $obj_handler = null;
	protected $obj_handler_obj = null;
	protected $hierarchy_arr = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'object_type':
				$retval = [
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

					200  => 'expense',

					//50 => 'request', //request_other
					1010 => 'request_punch',
					1020 => 'request_punch_adjust',
					1030 => 'request_absence',
					1040 => 'request_schedule',
					1100 => 'request_other',
				];
				break;
			case 'columns':
				$retval = [

						'-1010-created_by'   => TTi18n::gettext( 'Name' ),
						'-1020-created_date' => TTi18n::gettext( 'Date' ),
						'-1030-authorized'   => TTi18n::gettext( 'Authorized' ),
						//'-1100-object_type' => TTi18n::gettext('Object Type'),

						//'-2020-updated_by' => TTi18n::gettext('Updated By'),
						//'-2030-updated_date' => TTi18n::gettext('Updated Date'),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'created_by',
						'created_date',
						'authorized',
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
				'id'             => 'ID',
				'object_type_id' => 'ObjectType',
				'object_type'    => false,
				'object_id'      => 'Object',
				'authorized'     => 'Authorized',
				'deleted'        => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCurrentUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getCurrentUser(), 'user_obj' );
	}

	/**
	 * Stores the current user in memory, so we can determine if its the employee verifying, or a superior.
	 * @return mixed
	 */
	function getCurrentUser() {
		return $this->getGenericTempDataValue( 'current_user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrentUser( $value ) {
		$value = trim( $value );

		return $this->setGenericTempDataValue( 'current_user_id', $value );
	}


	/**
	 * @return array|bool|null
	 */
	function getHierarchyArray() {
		if ( is_array( $this->hierarchy_arr ) ) {
			return $this->hierarchy_arr;
		} else {
			$user_id = $this->getCurrentUser();

			if ( is_object( $this->getObjectHandler() ) ) {
				$this->getObjectHandler()->getByID( $this->getObject() );
				$current_obj = $this->getObjectHandler()->getCurrent();
				$object_user_id = $current_obj->getUser();

				if ( TTUUID::isUUID( $object_user_id ) && $object_user_id != TTUUID::getZeroID() && $object_user_id != TTUUID::getNotExistID() ) {
					Debug::Text( ' Authorizing User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Text( ' Object User ID: ' . $object_user_id, __FILE__, __LINE__, __METHOD__, 10 );

					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$company_id = $ulf->getById( $object_user_id )->getCurrent()->getCompany();
					Debug::Text( ' Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

					$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
					$this->hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $object_user_id, $this->getObjectType(), false );

					Debug::Arr( $this->hierarchy_arr, ' Hierarchy Arr: ', __FILE__, __LINE__, __METHOD__, 10 );

					return $this->hierarchy_arr;
				} else {
					Debug::Text( ' Could not find Object User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( ' ERROR: No ObjectHandler defined...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}


	/**
	 * @return array|bool
	 */
	function getHierarchyChildLevelArray() {
		$retval = [];

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					continue;
				}

				if ( $next_level == true ) {
					//Debug::Arr( $level_parent_arr, ' Child: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = array_merge( $retval, $level_parent_arr ); //Append from all levels.
				}
			}
		}

		if ( count( $retval ) > 0 ) {
			return $retval;
		}

		return false;
	}

	/**
	 * @param bool $force
	 * @return bool|mixed
	 */
	function getHierarchyCurrentLevelArray( $force = false ) {
		$retval = false;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					if ( $force == false ) {
						continue;
					}
				}

				if ( $next_level == true ) { //Current level is alway one level lower, as this often gets called after the level has been changed.
					$retval = $level_parent_arr;
					//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}

			if ( $next_level == true && $retval == false ) {
				//Current level was the top and only level.
				$retval = $level_parent_arr;
				//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $retval;
	}

	/**
	 * @return array|bool|mixed
	 */
	function getHierarchyParentLevelArray() {
		$retval = false;

		$user_id = TTUUID::castUUID( $this->getCurrentUser() );
		$parent_arr = array_reverse( (array)$this->getHierarchyArray() );
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level => $level_parent_arr ) {
				if ( is_array( $level_parent_arr ) && in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					continue;
				}

				//Since this loops in reverse, always assume the first element is the parent for cases where a subordinate may be submitting the object (ie: request) and it needs to go to the direct superiors.
				if ( $next_level == true ) {
					//Debug::Arr( $level_parent_arr, ' Parents: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $level_parent_arr;
					break;
				}
			}

			//If we get here without finding a parent, use the lowest lower parents by default.
			if ( $next_level == false ) {
				reset( $parent_arr );
				$retval = $parent_arr[key( $parent_arr )];
			}
		}

		return $retval;
	}

	/**
	 * This will return false if it can't find a hierarchy, or if its at the top level (1) and can't find a higher level.
	 * @return bool|int|string
	 */
	function getNextHierarchyLevel() {
		$retval = false;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			foreach ( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					break;
				}
				$retval = $level;
			}
		}

		if ( $retval < 1 ) {
			Debug::Text( ' ERROR, hierarchy level goes past 1... This shouldnt happen...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}

		return $retval;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $hierarchy_type_id
	 * @return int|mixed
	 */
	static function getInitialHierarchyLevel( $company_id, $user_id, $hierarchy_type_id ) {
		$hierarchy_highest_level = 99;
		if ( $company_id != '' && $user_id != '' && $hierarchy_type_id > 0 ) {
			$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
			$hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, $hierarchy_type_id, false );
			if ( isset( $hierarchy_arr ) && is_array( $hierarchy_arr ) ) {
				Debug::Arr( $hierarchy_arr, ' aUser ID ' . $user_id . ' Type ID: ' . $hierarchy_type_id . ' Array: ', __FILE__, __LINE__, __METHOD__, 10 );

				//See if current user is in superior list, if so, start at one level up in the hierarchy, unless its level 1.
				foreach ( $hierarchy_arr as $level => $superior_user_ids ) {
					if ( in_array( $user_id, $superior_user_ids, true ) == true ) {
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

		Debug::Text( ' Returning initial hierarchy level to: ' . $hierarchy_highest_level, __FILE__, __LINE__, __METHOD__, 10 );

		return $hierarchy_highest_level;
	}

	/**
	 * @return bool
	 */
	function isValidParent() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			krsort( $parent_arr );
			foreach ( $parent_arr as $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					return true;
				}
			}
		}

		Debug::Text( ' Authorizing User is not a parent of the object owner: ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function isFinalAuthorization() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			//Check that level 1 parent exists
			if ( isset( $parent_arr[1] ) && in_array( $user_id, $parent_arr[1] ) ) {
				Debug::Text( ' Final Authorization!', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		Debug::Text( ' NOT Final Authorization!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return null|object
	 */
	function getObjectHandler() {
		if ( is_object( $this->obj_handler ) ) {
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
	 * @return bool
	 */
	function getAuthorized() {
		return $this->fromBool( $this->getGenericDataValue( 'authorized' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorized( $value ) {
		return $this->setGenericDataValue( 'authorized', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function clearHistory() {
		Debug::text( 'Clearing Authorization History For Type: ' . $this->getObjectType() . ' ID: ' . $this->getObject(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getObjectType() === false || $this->getObject() === false ) {
			Debug::text( 'Clearing Authorization History FAILED!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$alf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $alf */
		$alf->getByObjectTypeAndObjectId( $this->getObjectType(), $this->getObject() );
		foreach ( $alf as $authorization_obj ) {
			$authorization_obj->setDeleted( true );
			$authorization_obj->Save();
		}

		return true;
	}

	/**
	 * @return object
	 */
	function getObjectHandlerObject() {
		if ( is_object( $this->obj_handler_obj ) ) {
			return $this->obj_handler_obj;
		} else {
			//Get user_id of object.
			$this->getObjectHandler()->getByID( $this->getObject() );
			$this->obj_handler_obj = $this->getObjectHandler()->getCurrent();
//			if ( method_exists( $this->obj_handler_obj, 'setCurrentUser' ) AND $this->obj_handler_obj->getCurrentUser() != $this->getCurrentUser() ) { //Required for authorizing TimeSheets from MyAccount -> TimeSheet Authorization.
//				$this->obj_handler_obj->setCurrentUser( $this->getCurrentUser() );
//			}

			return $this->obj_handler_obj;
		}
	}

	/**
	 * @return boolean
	 */
	function setObjectHandlerStatus() {
		$is_final_authorization = $this->isFinalAuthorization();

		$this->obj_handler_obj = $this->getObjectHandlerObject();
		if ( $this->getAuthorized() === true ) {
			if ( $is_final_authorization === true ) {
				if ( $this->getCurrentUser() != $this->obj_handler_obj->getUser() ) {
					Debug::Text( '  Approving Authorization... Final Authorizing Object: ' . $this->getObject() . ' - Type: ' . $this->getObjectType(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->obj_handler_obj->setAuthorizationLevel( 1 );
					$this->obj_handler_obj->setStatus( 50 ); //Active/Authorized
					$this->obj_handler_obj->setAuthorized( true );
				} else {
					Debug::Text( '  Currently logged in user is authorizing (or submitting as new) their own request, not authorizing...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::text( '  Approving Authorization, moving to next level up...', __FILE__, __LINE__, __METHOD__, 10 );
				$current_level = $this->obj_handler_obj->getAuthorizationLevel();
				if ( $current_level > 1 ) { //Highest level is 1, so no point in making it less than that.

					//Get the next level above the current user doing the authorization, in case they have dropped down a level or two.
					$next_level = $this->getNextHierarchyLevel();
					if ( $next_level !== false && $next_level < $current_level ) {
						Debug::text( '  Current Level: ' . $current_level . ' Moving Up To Level: ' . $next_level, __FILE__, __LINE__, __METHOD__, 10 );
						$this->obj_handler_obj->setAuthorizationLevel( $next_level );
					}
				}
				unset( $current_level, $next_level );
			}
		} else {
			Debug::text( '  Declining Authorization...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->obj_handler_obj->setStatus( 55 ); //'AUTHORIZATION DECLINED'
			$this->obj_handler_obj->setAuthorized( false );
		}

		return true;
	}

	/**
	 * @return array|bool
	 */
	function getEmailAuthorizationAddresses() {
		$object_handler_user_id = $this->getObjectHandlerObject()->getUser(); //Object handler (request) user_id.

		$is_final_authorization = $this->isFinalAuthorization();
		$authorization_level = $this->getObjectHandlerObject()->getAuthorizationLevel(); //This is the *new* level, not the old level.

		$hierarchy_current_level_arr = $this->getHierarchyCurrentLevelArray();
		Debug::Arr( $hierarchy_current_level_arr, '  Authorization Level: ' . $authorization_level . ' Authorized: ' . (int)$this->getAuthorized() . ' Is Final Auth: ' . (int)$is_final_authorization . ' Object Handler User ID: ' . $object_handler_user_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getAuthorized() == true && $authorization_level == 0 ) {
			//Final authorization has taken place
			//Email original submittor and all lower level superiors?
			$user_ids = $this->getHierarchyChildLevelArray();

			if ( is_a( $this->getObjectHandlerObject(), 'PayPeriodTimeSheetVerify' ) ) { //is_a() will match on plugin class names too because it also checks the parent class name.
				//Check to see what type of timesheet verification is required, if its superior only, don't email the employee to avoid confusion.
				if ( $this->getObjectHandlerObject()->getVerificationType() != 30 ) {
					$user_ids[] = $object_handler_user_id;
				} else {
					Debug::text( '  TimeSheetVerification for superior only, dont email employee...', __FILE__, __LINE__, __METHOD__, 10 );
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
			} else if ( $is_final_authorization == true && $this->getCurrentUser() == $object_handler_user_id && $this->getAuthorized() == true && $authorization_level == 1 ) {
				//Subordinate who is also a superior at the top and only level of the hierarchy is submitting a request.
				$user_ids = $this->getHierarchyCurrentLevelArray( true ); //Force to real current level.
				//Debug::Arr($user_ids , '  b2Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				//Authorized at a middle level, email current level superiors only so they know its waiting on them.
				$user_ids = $this->getHierarchyParentLevelArray();
				//Debug::Arr($user_ids , '  b3Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Parent: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		//Remove the current authorizing user from the array, as they don't need to be notified as they are performing the action.
		$user_ids = array_diff( (array)$user_ids, [ $this->getCurrentUser() ] );         //CurrentUser is currently logged in user.
		if ( isset( $user_ids ) && is_array( $user_ids ) && count( $user_ids ) > 0 ) {
			//Get user preferences and determine if they accept email notifications.
			Debug::Arr( $user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__, 10 );

			$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
			$uplf->getByUserIdAndStatus( $user_ids, 10 ); //Only email ACTIVE employees/supervisors when Login is Enabled (Checked below)
			if ( $uplf->getRecordCount() > 0 ) {
				$retarr = [];
				foreach ( $uplf as $up_obj ) {
					if ( $up_obj->getEnableEmailNotificationMessage() == true && is_object( $up_obj->getUserObject() ) && $up_obj->getUserObject()->getStatus() == 10 && $up_obj->getUserObject()->getEnableLogin() == true ) {
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
	function emailAuthorization() {
		Debug::Text( 'emailAuthorization: ', __FILE__, __LINE__, __METHOD__, 10 );

		$email_to_arr = $this->getEmailAuthorizationAddresses();
		if ( $email_to_arr == false ) {
			return false;
		}

		//Get from User Object so we can include more information in the message.
		if ( is_object( $this->getCurrentUserObject() ) ) {
			$u_obj = $this->getCurrentUserObject();
		} else {
			Debug::Text( 'From object does not exist: ' . $this->getCurrentUser(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$object_handler_user_obj = $this->getObjectHandlerObject()->getUserObject();                                                                                       //Object handler (request) user_id.
		$status_label = Option::getByKey( $this->getObjectHandlerObject()->getStatus(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions( 'status' ) ) ); //PENDING, AUTHORIZED, DECLINED
		switch ( $this->getObjectType() ) {
			case 90: //TimeSheet
				$object_type_label = TTi18n::getText( 'TimeSheet' );
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText( 'Pay Period' ) . ': ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getEndDate() );
				break;
			case 200: //Expense
				$object_type_label = TTi18n::getText( 'Expense' );
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText( 'Incurred' ) . ': ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getIncurredDate() ) . ' ' . TTi18n::getText( 'for' ) . ' ' . $this->getObjectHandlerObject()->getGrossAmount();
				break;
			default:
				$object_type_label = TTi18n::getText( 'Request' );
				$object_type_short_description = '';
				$object_type_long_description = TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getObjectHandlerObject()->getType(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions( 'type' ) ) ) . ' ' . TTi18n::getText( 'on' ) . ' ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getDateStamp() );
				break;
		}

		$from = $reply_to = '"' . APPLICATION_NAME . ' - ' . $object_type_label . ' ' . TTi18n::gettext( 'Authorization' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';

		Debug::Text( 'To: ' . implode( ',', $email_to_arr ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'From: ' . $from . ' Reply-To: ' . $reply_to, __FILE__, __LINE__, __METHOD__, 10 );

		//Define subject/body variables here.
		$search_arr = [
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
		];

		$replace_arr = [
				$object_type_label,
				$object_type_short_description,
				$object_type_long_description,
				$status_label,

				$u_obj->getFirstName(),
				$u_obj->getLastName(),

				$object_handler_user_obj->getFirstName(),
				$object_handler_user_obj->getLastName(),
				( is_object( $object_handler_user_obj->getDefaultBranchObject() ) ) ? $object_handler_user_obj->getDefaultBranchObject()->getName() : null,
				( is_object( $object_handler_user_obj->getDefaultDepartmentObject() ) ) ? $object_handler_user_obj->getDefaultDepartmentObject()->getName() : null,
				( is_object( $object_handler_user_obj->getGroupObject() ) ) ? $object_handler_user_obj->getGroupObject()->getName() : null,
				( is_object( $object_handler_user_obj->getTitleObject() ) ) ? $object_handler_user_obj->getTitleObject()->getName() : null,

				( is_object( $object_handler_user_obj->getCompanyObject() ) ) ? $object_handler_user_obj->getCompanyObject()->getName() : null,
				null,
		];

		$email_subject = '#object_type# by #object_employee_first_name# #object_employee_last_name# #status#' . ' ' . TTi18n::gettext( 'in' ) . ' ' . APPLICATION_NAME;

		$email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$email_body .= '#object_type# by #object_employee_first_name# #object_employee_last_name# #status#' . ' ' . TTi18n::gettext( 'in' ) . ' ' . APPLICATION_NAME . "\n";
		$email_body .= ( $replace_arr[2] != '' ) ? '#object_type_long_description#' . "\n" : null;
		$email_body .= "\n";
		$email_body .= ( $replace_arr[8] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #object_employee_default_branch#' . "\n" : null;
		$email_body .= ( $replace_arr[9] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #object_employee_default_department#' . "\n" : null;
		$email_body .= ( $replace_arr[10] != '' ) ? TTi18n::gettext( 'Group' ) . ': #object_employee_group#' . "\n" : null;
		$email_body .= ( $replace_arr[11] != '' ) ? TTi18n::gettext( 'Title' ) . ': #object_employee_title#' . "\n" : null;

		$email_body .= TTi18n::gettext( 'Link' ) . ': <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . '">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Login' ) . '</a>';

		$email_body .= ( $replace_arr[12] != '' ) ? "\n\n\n" . TTi18n::gettext( 'Company' ) . ': #company_name#' . "\n" : null; //Always put at the end

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
	 * Used by Request/TimeSheetVerification/Expense when initially saving a record to notify the immediate superiors, rather than using the message notification.
	 * @param string $current_user_id UUID
	 * @param int $object_type_id
	 * @param string $object_id       UUID
	 * @return bool
	 */
	static function emailAuthorizationOnInitialObjectSave( $current_user_id, $object_type_id, $object_id ) {
		$authorization_obj = TTNew( 'AuthorizationFactory' ); /** @var AuthorizationFactory $authorization_obj */
		$authorization_obj->setObjectType( $object_type_id );
		$authorization_obj->setObject( $object_id );
		$authorization_obj->setCurrentUser( $current_user_id );
		$authorization_obj->setAuthorized( true );
		$authorization_obj->emailAuthorization(); //Don't save this...

		return true;
	}

	/**
	 * @return bool
	 */
	function isUnique() {
		$ph = [
				'object_type' => (int)$this->getObjectType(),
				'object_id'   => TTUUID::castUUID( $this->getObject() ),
				'authorized'  => (int)$this->getAuthorized(),
				'created_by'  => TTUUID::castUUID( $this->getCreatedBy() ),
		];

		$query = 'select id from ' . $this->getTable() . ' where object_type_id = ? AND object_id = ? AND authorized = ? AND created_by = ?';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Authorization: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  $this->getOptions( 'object_type' )
		);
		// Object ID
		$this->Validator->isResultSetWithRows( 'object',
											   ( is_object( $this->getObjectHandler() ) ) ? $this->getObjectHandler()->getByID( $this->getObject() ) : false,
											   TTi18n::gettext( 'Object ID is invalid' )
		);

		//Prevent duplicate authorizations by the same person.
		// This may cause problems if the hierarchy is changed and the same superior needs to authorize the request again though?
		//   By definition this should never happen at the final authorization level, so someone higher up in the hierarchy could always drop down and authorize it during the transition.
		if ( $this->getDeleted() == false ) {
			if ( $this->Validator->getValidateOnly() == false && $this->isUnique() == false ) {
				$this->Validator->isTrue( 'object',
										  false,
										  TTi18n::gettext( 'Record has already been authorized/declined by you' ) );
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() === false
				&& $this->isFinalAuthorization() === false
				&& $this->isValidParent() === false ) {
			$this->Validator->isTrue( 'parent',
									  false,
									  TTi18n::gettext( 'Employee authorizing this object is not a parent of it' ) );

			return false;
		}

		$this->setObjectHandlerStatus();

		if ( $this->getDeleted() == false && is_object( $this->getObjectHandlerObject() ) && $this->getObjectHandlerObject()->isValid() == false ) {
			Debug::text( '  ObjectHandler Validation Failed, pass validation errors up the chain...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Debug::Text(' Calling preSave!: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->StartTransaction();

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getDeleted() == false ) {
			if ( is_object( $this->getObjectHandlerObject() ) && $this->getObjectHandlerObject()->isValid() == true ) {
				Debug::text( '  Object Valid...', __FILE__, __LINE__, __METHOD__, 10 );
				//Return true if object saved correctly.
				$retval = $this->getObjectHandlerObject()->Save( false );
				if ( $this->getObjectHandlerObject()->isValid() == false ) {
					Debug::text( '  Object postSave validation FAILED!', __FILE__, __LINE__, __METHOD__, 10 );
					$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
				} else {
					Debug::text( '  Object postSave validation SUCCESS!', __FILE__, __LINE__, __METHOD__, 10 );
					$this->emailAuthorization();
				}

				if ( $retval === true ) {
					$this->CommitTransaction();

					return true;
				} else {
					$this->FailTransaction();
				}
			} else {
				//Always fail the transaction if we get this far.
				//This stops authorization entries from being inserted.
				$this->FailTransaction();
			}

			$this->CommitTransaction(); //preSave() starts the transaction

			return false;
		}

		$this->CommitTransaction(); //preSave() starts the transaction

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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'object_type':
							Debug::text( '  Object Type...', __FILE__, __LINE__, __METHOD__, 10 );
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
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( $this->getAuthorized() === true ) {
			$authorized = TTi18n::getText( 'True' );
		} else {
			$authorized = TTi18n::getText( 'False' );
		}

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Authorization Object Type' ) . ': ' . ucwords( str_replace( '_', ' ', Option::getByKey( $this->getObjectType(), $this->getOptions( 'object_type' ) ) ) ) . ' ' . TTi18n::getText( 'Authorized' ) . ': ' . $authorized, null, $this->getTable() );
	}
}

?>
