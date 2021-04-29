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
class Permission {
	private $cached_permissions = [];
	private $cached_permission_children_ids = [];
	private $cached_permission_levels = [];

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return array
	 */
	function getPermissions( $user_id, $company_id ) {
		//When Permission->Check() is used in a tight loop, even getCache() can be slow as it has to load a large array.
		//So cache the permissions in even faster access memory when possible.
		if ( isset( $this->cached_permissions[$user_id][$company_id] ) && $this->cached_permissions[$user_id][$company_id] != false ) {
			return $this->cached_permissions[$user_id][$company_id];
		}

		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */

		$cache_id = 'permission_all_' . $user_id . '_' . $company_id; //This is also in UserFactory->postSave()
		$perm_arr = $plf->getCache( $cache_id );
		//Debug::Arr($perm_arr, 'Cached Perm Arr:', __FILE__, __LINE__, __METHOD__, 9);
		if ( $perm_arr === false ) {
			$plf->getAllPermissionsByCompanyIdAndUserId( $company_id, $user_id );
			if ( $plf->getRecordCount() > 0 ) {
				//Debug::Text('Found Permissions in DB!', __FILE__, __LINE__, __METHOD__, 9);
				$perm_arr['_system']['last_updated_date'] = null;
				foreach ( $plf as $p_obj ) {
					//Debug::Text('Perm -  Section: '. $p_obj->getSection() .' Name: '. $p_obj->getName() .' Value: '. (int)$p_obj->getValue(), __FILE__, __LINE__, __METHOD__, 9);
					if ( $p_obj->getUpdatedDate() > $perm_arr['_system']['last_updated_date'] ) {
						$perm_arr['_system']['last_updated_date'] = $p_obj->getUpdatedDate();
					}
					$perm_arr[$p_obj->getSection()][$p_obj->getName()] = $p_obj->getValue();
				}
				//Last iteration, grab the permission level.
				$perm_arr['_system']['level'] = $p_obj->getColumn( 'level' );

				$plf->saveCache( $perm_arr, $cache_id );
				//Debug::Text('  Caching permissions for: User ID: '. $user_id .' Company ID: '. $company_id .' Total Permissions: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
			}
		}

		$this->cached_permissions[$user_id][$company_id] = $perm_arr; //Populate local cache.

		return $perm_arr;
	}

	/**
	 * Check to make sure the authentication type is at least this level.
	 * @param int $type_id
	 * @return bool
	 */
	function checkAuthenticationType( $type_id ) {
		global $authentication;

		if ( is_object( $authentication ) ) {
			if ( $authentication->getType() >= $type_id ) {
				return true;
			}
		} else {
			return true; //No authentication object to check against, return TRUE.
		}

		return false;
	}

	/**
	 * @param $section
	 * @param $name
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	function Check( $section, $name, $user_id = null, $company_id = null ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		if ( $company_id == null || $company_id == '' ) {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset( $permission_arr[$section][$name] ) ) {
			//Debug::Text('Permission is Set!', __FILE__, __LINE__, __METHOD__, 9);
			$result = $permission_arr[$section][$name];
		} else {
			//Debug::Text('Permission is NOT Set!', __FILE__, __LINE__, __METHOD__, 9);
			$result = false;
		}

		return $result;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool|int
	 */
	function getLevel( $user_id = null, $company_id = null ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		if ( $company_id == null || $company_id == '' ) {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$cache_id = 'permission_level_' . $user_id . '_' . $company_id; //This is cleared in PermissionFactory->clearCache() which is also called from PermissionControlFactory->postSave()

		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
		$retval = $plf->getCache( $cache_id );
		if ( $retval === false ) {
			//Check if permissions are already cached, if so just grab the level directly from that.
			if ( isset( $this->cached_permissions[$user_id][$company_id] ) && $this->cached_permissions[$user_id][$company_id] != false ) {
				$permission_arr = $this->getPermissions( $user_id, $company_id );
				if ( isset( $permission_arr['_system']['level'] ) ) {
					$retval = (int)$permission_arr['_system']['level'];
				}
			} else {
				//If permissions are not cached, rather than getting all permissions when we just need to the level, just grab the level itself and cache that separately.

				//Because caching is disabled when inside a RetryTransaction() add local memory caching to getting permission levels
				if ( isset( $this->cached_permission_levels[$user_id][$company_id] ) && $this->cached_permission_levels[$user_id][$company_id] != false ) {
					$retval = $this->cached_permission_levels[$user_id][$company_id];
				} else {
					$pcf = new PermissionControlListFactory();
					$pcf->getByCompanyIdAndUserId( $company_id, $user_id );
					if ( $pcf->getRecordCount() == 1 ) {
						$retval = (int)$pcf->getCurrent()->getLevel();
						$this->cached_permission_levels[$user_id][$company_id] = $retval;
					}
				}
			}

			if ( $retval !== false ) {
				$plf->saveCache( $retval, $cache_id ); //Only save cache if there is a permission level to return, so we aren't caching negative values in cases where a new user may be created.
			} else {
				$retval = 1; //Lowest level.
			}
		}

		return $retval;
	}

	/**
	 * @param $result
	 * @return bool
	 */
	function Redirect( $result ) {
		if ( $result !== true ) {
			Redirect::Page( URLBuilder::getURL( null, Environment::getBaseURL() . '/permission/PermissionDenied.php' ) );
		}

		return true;
	}

	/**
	 * @param bool $result
	 * @param string $description
	 * @return bool
	 */
	function AuthenticationTypeDenied( $result = false, $description = null ) {
		return $this->PermissionDenied( false, TTi18n::getText( 'Authentication Method is not secure enough to perform this action.' ) );
	}

	/**
	 * @param bool $result
	 * @param string $description
	 * @return bool
	 */
	function PermissionDenied( $result = false, $description = null ) {
		if ( $description === null ) {
			$description = TTi18n::getText( 'Permission Denied' );
		}

		if ( $result !== true ) {
			Debug::Text( 'Permission Denied! Description: ' . $description, __FILE__, __LINE__, __METHOD__, 10 );
			$af = TTnew( 'APIPermission' ); /** @var APIPermission $af */
			return $af->returnHandler( false, 'PERMISSION', $description );
		}

		return true;
	}

	/**
	 * @param $section
	 * @param $name
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	function Query( $section, $name, $user_id = null, $company_id = null ) {
		Debug::Text( 'Permission Query!', __FILE__, __LINE__, __METHOD__, 9 );
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		if ( $company_id == null || $company_id == '' ) {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */

		return $plf->getBySectionAndNameAndUserIdAndCompanyId( $section, $name, $user_id, $company_id )->getCurrent();
	}

	/**
	 * Checks if the row_object_id is created by the current user
	 * @param $object_created_by
	 * @param null $object_assigned_to
	 * @param string $current_user_id UUID
	 * @return bool
	 */
	function isOwner( $object_created_by, $object_assigned_to = null, $current_user_id = null ) {
		if ( $current_user_id == null || $current_user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		//Allow object_assigned_to to be an array, then make sure *all* records in the array match.
		if ( ( $object_created_by != '' && $object_created_by == $current_user_id )
				|| ( $object_assigned_to != '' && $object_assigned_to == $current_user_id )
				|| ( is_array( $object_assigned_to ) && count( array_unique( $object_assigned_to ) ) == 1 && $object_assigned_to[0] == $current_user_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the row_object_id is in the src_object_list array,
	 * @param string $row_object_id   UUID
	 * @param $src_object_list
	 * @param string $current_user_id UUID
	 * @return bool
	 */
	function isChild( $row_object_id, $src_object_list, $current_user_id = null ) {
		if ( !TTUUID::isUUID( $row_object_id ) && !is_array( $row_object_id ) ) {
			return false;
		}

		if ( $current_user_id == null || $current_user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return false;
			}
		}
		//Can never be a child of themselves, so remove the current user from the child list.
		if ( $row_object_id == $current_user_id ) {
			return false;
		}

		if ( !is_array( $src_object_list ) && $src_object_list != '' ) {
			$src_object_list = [ $src_object_list ];
		}

		//If row_object_id is an array (ie: a subordinate list), then they must *all* match the src_object_list for this to be valid.
		//This is used by recurring_schedule for supervisor (subordinates only)
		if ( is_array( $row_object_id ) && is_array( $src_object_list ) ) {
			foreach ( $row_object_id as $tmp_row_object_id ) {
				if ( !in_array( $tmp_row_object_id, $src_object_list ) ) {
					return false;
				}
			}

			//All items match, return TRUE.
			return true;
		} else if ( is_array( $src_object_list ) && in_array( $row_object_id, $src_object_list ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @param $inner_column
	 * @param bool $append_comma
	 * @param null $special_child_id
	 * @return string
	 */
	static function getPermissionIsChildIsOwnerSQL( $id, $inner_column, $append_comma = true, $special_child_id = null ) {
		$query = "\n";

		//Attendance -> Schedule needs to pass this special_child_id so all records assigned to the OPEN employee are considered "children".
		if ( $special_child_id != '' ) {
			$query .= ' CASE WHEN ( phc.is_child is NOT NULL OR ' . $inner_column . ' = \'' . TTUUID::getZeroID() . '\' ) THEN 1 ELSE 0 END as is_child,';
		} else {
			$query .= ' CASE WHEN phc.is_child is NOT NULL THEN 1 ELSE 0 END as is_child,';
		}

		$query .= ' CASE WHEN ' . $inner_column . ' = \'' . TTUUID::castUUID( $id ) . '\' THEN 1 ELSE 0 END as is_owner';

		if ( $append_comma == true ) {
			$query .= ', ';
		}

		return $query;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param $outer_column
	 * @return string
	 */
	static function getPermissionHierarchySQL( $company_id, $user_id, $outer_column ) {
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$query = '
						LEFT JOIN (
							select phc_huf.user_id as user_id, 1 as is_child
							from ' . $huf->getTable() . ' as phc_huf
							LEFT JOIN ' . $hlf->getTable() . ' as phc_hlf ON phc_huf.hierarchy_control_id = phc_hlf.hierarchy_control_id
							LEFT JOIN ' . $hotf->getTable() . ' as phc_hotf ON phc_huf.hierarchy_control_id = phc_hotf.hierarchy_control_id
							LEFT JOIN ' . $hcf->getTable() . ' as phc_hcf ON phc_huf.hierarchy_control_id = phc_hcf.id
							WHERE
								phc_hlf.user_id = \'' . TTUUID::castUUID( $user_id ) . '\'
								AND phc_hcf.company_id = \'' . TTUUID::castUUID( $company_id ) . '\'
								AND phc_hotf.object_type_id = 100
								AND phc_huf.user_id != phc_hlf.user_id
								AND ( phc_hlf.deleted = 0 AND phc_hcf.deleted = 0 )
						) as phc ON ' . $outer_column . ' = phc.user_id
					';

		return $query;
	}

	/**
	 * @param $filter_data
	 * @param $outer_column_name
	 * @return array|bool|string
	 */
	static function getPermissionIsChildIsOwnerFilterSQL( $filter_data, $outer_column_name ) {
		if ( isset( $filter_data['permission_invalid'] ) ) {
			Debug::Text( '  Potential security bypass, permission invalid...', __FILE__, __LINE__, __METHOD__, 10 );
			$query = ' AND ' . $outer_column_name . ' = \'' . TTUUID::getNotExistID() . '\''; //Bogus permissions, don't return any data.

			return $query;
		} else {
			$query = [];
			if ( isset( $filter_data['permission_is_own'] ) && $filter_data['permission_is_own'] == true && isset( $filter_data['permission_current_user_id'] ) ) {
				$query[] = $outer_column_name . ' = \'' . TTUUID::castUUID( $filter_data['permission_current_user_id'] ) . '\'';
			}
			if ( isset( $filter_data['permission_is_child'] ) && $filter_data['permission_is_child'] == true ) {
				$query[] = 'phc.is_child = 1';
			}

			//Don't add this filter unless we have already added a is_own or is_child filter above because it will restrict to just the permission_is_id rather than it along with other children IDs.
			if ( count( $query ) > 0 && isset( $filter_data['permission_is_id'] ) && TTUUID::isUUID( $filter_data['permission_is_id'] ) ) {
				$query[] = $outer_column_name . ' = \'' . TTUUID::castUUID( $filter_data['permission_is_id'] ) . '\'';
			}

			if ( empty( $query ) == false ) {
				return ' AND ( ' . implode( ' OR ', $query ) . ') ';
			}
		}

		return false;
	}

	/**
	 * @param $section
	 * @param $name
	 * @param string $user_id UUID
	 * @return array|bool
	 */
	function getPermissionFilterData( $section, $name, $user_id = null ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

//		if ( $company_id == NULL OR $company_id == '') {
//			global $current_company;
//			$company_id = $current_company->getId();
//		}

		/*
			permission_children_ids
			permission_current_user_id
			permission_is_child = 1
			permission_is_own = 1
		*/
		$retarr = [];
		$retarr['permission_current_user_id'] = $user_id;
		if ( $this->Check( $section, $name ) == false ) {
			if ( $this->Check( $section, $name . '_child' ) ) {
				$retarr['permission_is_child'] = true;
			}
			if ( $this->Check( $section, $name . '_own' ) ) {
				$retarr['permission_is_own'] = true; //Return user_id so we can match that specifically
			}

			//#2242 - If a invalid/bogus $section is passed in from the user, be sure to default to no permissions.
			// With permission checks we shouldnt ever get here unless at least one of the three
			// permissions ( view, view_own, view_child ) are TRUE otherwise treat it as bogus/invalid and don't return anything.
			if ( !isset( $retarr['permission_is_child'] ) && !isset( $retarr['permission_is_own'] ) ) {
				Debug::Text( 'ERROR: Potential security bypass, permission section/name is invalid: Section: ' . $section . ' Name: ' . $name . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				$retarr = [ 'permission_invalid' => true ];
			}
		}

		if ( empty( $retarr ) == false ) {
			return $retarr;
		}

		return [];
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return mixed
	 */
	function getPermissionHierarchyChildren( $company_id, $user_id ) {
		if ( isset( $this->cached_permission_children_ids[$company_id][$user_id] ) ) {
			return $this->cached_permission_children_ids[$company_id][$user_id];
		} else {
			Debug::Text( '  Getting hierarchy children for User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
			$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
			$this->cached_permission_children_ids[$company_id][$user_id] = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, 100 );

			//Debug::Arr($this->cached_permission_children_ids[$company_id][$user_id], 'Permission Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->cached_permission_children_ids[$company_id][$user_id];
		}
	}

	/**
	 * @param $section
	 * @param $name
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return array|bool|mixed|null
	 */
	function getPermissionChildren( $section, $name, $user_id = null, $company_id = null ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		if ( $company_id == null || $company_id == '' ) {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//If 'view' is FALSE, we are not returning children to check for edit/delete permissions, so those are denied.
		//This can be tested with 'users', 'view', 'users', 'edit_subordinate' allowed. They won't be able to edit subordinates.
		if ( $this->Check( $section, $name, $user_id, $company_id ) == false ) {
			if ( $this->Check( $section, $name . '_child', $user_id, $company_id ) == true ) {
				$retarr = $this->getPermissionHierarchyChildren( $company_id, $user_id );
			}
			//Why are we including the current user in the "child" list, if they can view their own records.
			//This essentially makes edit_child permissions include edit_own as well. Which for the editing punches
			//there may be cases where they can edit subordinates but not themselves.
			//Because in the SQL query, we restrict to just the child_ids.
			//Its different view view_own/view_child as compared to edit_own/edit_child.
			//	So we need to include the current user if they can only view their own, but exclude the current user when doing is_child checks above.
			//Another way we could handle this is to return an array of children and owner separately, then in SQL queries combine them together.
			if ( $this->Check( $section, $name . '_own', $user_id, $company_id ) == true ) {
				$retarr[] = TTUUID::castUUID( $user_id );
			}

			//If they don't have permissions to view anything, make sure we return a blank array, so all in_array() or isPermissionChild() returns FALSE.
			if ( !isset( $retarr ) ) {
				$retarr = [];
			}
		} else {
			//This must return TRUE, otherwise the SQL query will restrict returned records just to the children.
			//Used to be NULL, but isset() doesn't work on NULL. Using TRUE though caused is_array() to always return TRUE too, which may not be a bad thing.
			//   However using TRUE causing other PHP warnings when trying to add values to it as if it were an array.
			$retarr = null;
		}

		return $retarr;
	}

	/**
	 * @param string $user_id                 UUID
	 * @param string|string[] $permission_children_ids UUID
	 * @return bool
	 */
	function isPermissionChild( $user_id, $permission_children_ids ) {
		if ( $permission_children_ids === null || in_array( TTUUID::castUUID( $user_id ), (array)$permission_children_ids, true ) ) { //Make sure we do a STRICT in_array() match, so $user_id=TRUE isn't matched.
			return true;
		}

		return false;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	function getLastUpdatedDate( $user_id = null, $company_id = null ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			if ( isset( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return false;
			}
		}

		if ( $company_id == null || $company_id == '' ) {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset( $permission_arr['_system']['last_updated_date'] ) ) {
			return $permission_arr['_system']['last_updated_date'];
		}

		return false;
	}
}

?>
