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
 * @package Core
 */
class PermissionControlFactory extends Factory {
	protected $table = 'permission_control';
	protected $pk_sequence_name = 'permission_control_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $tmp_previous_user_ids = array();

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'preset':
				$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
				$retval = $pf->getOptions('preset');
				break;
			case 'level':
				for( $i = 1; $i <= 100; $i++ ) { //100 Levels.
					$retval[$i] = $i;
				}
				break;
			case 'columns':
				$retval = array(
										'-1000-name' => TTi18n::gettext('Name'),
										'-1010-description' => TTi18n::gettext('Description'),
										'-1020-level' => TTi18n::gettext('Level'),
										'-1030-total_users' => TTi18n::gettext('Employees'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('name', 'description', 'level'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'level',
								'total_users',
								'updated_by',
								'updated_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'name' => 'Name',
										'description' => 'Description',
										'level' => 'Level',
										'total_users' => FALSE,
										'user' => 'User',
										'permission' => 'Permission',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool|int|string
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$permission_control_id = $this->db->GetOne($query, $ph);
		Debug::Arr($permission_control_id, 'Unique Permission Control ID: '. $permission_control_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $permission_control_id === FALSE ) {
			return TRUE;
		} else {
			if ($permission_control_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'name', $value );
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
	function setDescription( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}


	/**
	 * @return bool|int
	 */
	function getLevel() {
		return $this->getGenericDataValue( 'level' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLevel( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'level', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUser() {
		$pulf = TTnew( 'PermissionUserListFactory' ); /** @var PermissionUserListFactory $pulf */
		$pulf->getByPermissionControlId( $this->getId() );

		$list = array();
		foreach ($pulf as $obj) {
			$list[] = $obj->getUser();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUser( $ids) {
		Debug::text('Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		if ( is_array($ids) AND count($ids) > 0 ) {
			global $current_user;

			//Remove any of the selected employees from other permission control objects first.
			//So there we can switch employees from one group to another in a single action.
			$pulf = TTnew( 'PermissionUserListFactory' ); /** @var PermissionUserListFactory $pulf */
			$pulf->getByCompanyIdAndUserIdAndNotPermissionControlId( $this->getCompany(), $ids, TTUUID::castUUID($this->getId()) );
			if ( $pulf->getRecordCount() > 0 ) {
				Debug::text('Found User IDs assigned to another Permission Group, unassigning them!', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $pulf as $pu_obj ) {
					if ( !is_object($current_user) OR ( is_object($current_user) AND $current_user->getID() != $pu_obj->getUser() ) ) { //Not Acting on currently logged in user.
						$pu_obj->Delete();
					}
				}
			}
			unset($pulf, $pu_obj);

			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pulf = TTnew( 'PermissionUserListFactory' ); /** @var PermissionUserListFactory $pulf */
				$pulf->getByPermissionControlId( $this->getId() );
				foreach ($pulf as $obj) {
					$id = $obj->getUser();
					Debug::text('Permission Control ID: '. $obj->getPermissionControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						if ( is_object($current_user) AND $current_user->getID() == $id ) { //Not Acting on currently logged in user.
							$this->Validator->isTrue(		'user',
															FALSE,
															TTi18n::gettext('Unable to remove your own record from a permission group') );

						} else {
							Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
							$this->tmp_previous_user_ids[] = $id;
							$obj->Delete();
						}
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					//Remove users from any other permission control object
					//first, otherwise there is a gap where an employee has
					//no permissions, this is especially bad for administrators
					//who are currently logged in.
					$puf = TTnew( 'PermissionUserFactory' ); /** @var PermissionUserFactory $puf */
					$puf->setPermissionControl( $this->getId() );
					$puf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();
						if ( $this->Validator->isTrue(		'user',
															 $puf->isValid(),
															 TTi18n::gettext('Selected employee is already assigned to another permission group').' ('. $obj->getFullName() .')' )) {
							$puf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @return array
	 */
	function getPermissionOptions() {
		$product_edition = $this->getCompanyObject()->getProductEdition();

		$retval = array();

		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
		$sections = $pf->getOptions('section');
		$names = $pf->getOptions('name');
		if ( is_array($names) ) {
			foreach ($names as $section => $permission_arr) {
				if ( ( $pf->isIgnore( $section, NULL, $product_edition ) == FALSE ) ) {
					foreach($permission_arr as $name => $display_name) {
						if ( $pf->isIgnore( $section, $name, $product_edition ) == FALSE ) {
							if ( isset($sections[$section]) ) {
								$retval[$section][$name] = 0;
							}
						}
					}
					unset($display_name); //code standards
				}
			}
		}

		return $retval;
	}

	/**
	 * @return array|bool
	 */
	function getPermission() {
		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
		$plf->getByCompanyIdAndPermissionControlId( $this->getCompany(), $this->getId() );
		if ( $plf->getRecordCount() > 0 ) {
			$current_permissions = array();
			Debug::Text('Found Permissions: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach($plf as $p_obj) {
				$current_permissions[$p_obj->getSection()][$p_obj->getName()] = $p_obj->getValue();
			}

			return $current_permissions;
		}

		return FALSE;
	}

	/**
	 * @param $permission_arr
	 * @param array $old_permission_arr
	 * @return bool
	 */
	function setPermission( $permission_arr, $old_permission_arr = array() ) {
		if ( $this->getId() == FALSE ) {
			return FALSE;
		}

		if ( $this->Validator->getValidateOnly() == TRUE ) {
			return TRUE;
		}

		global $profiler, $config_vars;
		$profiler->startTimer( 'setPermission' );

		//Since implementing the HTML5 Install Wizard, which uses the API, we have to check to see if the installer is enabled, and if so skip this next block of code.
		if ( defined('TIMETREX_API') AND TIMETREX_API == TRUE
				AND ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 0 ) ) {
			//When creating a new permission group this causes it to be really slow as it creates a record for every permission that is set to DENY.

			//If we do the permission diff it messes up the HTML interface.
			if ( !is_array($old_permission_arr) OR ( is_array($old_permission_arr) AND count($old_permission_arr) == 0 ) ) {
				$old_permission_arr = $this->getPermission();
				//Debug::Text(' Old Permissions: '. count($old_permission_arr), __FILE__, __LINE__, __METHOD__, 10);
			}

			$permission_options = $this->getPermissionOptions();
			//Debug::Arr($permission_options, ' Permission Options: '. count($permission_options), __FILE__, __LINE__, __METHOD__, 10);

			$permission_arr = Misc::arrayMergeRecursiveDistinct( (array)$permission_options, (array)$permission_arr );
			//Debug::Text(' New Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($permission_arr, ' Final Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__, 10);
		}
		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */

		//Don't Delete all previous permissions, do that in the Permission class.
		if ( isset($permission_arr) AND is_array($permission_arr) AND count($permission_arr) > 0 ) {
			foreach ($permission_arr as $section => $permissions) {
				//Debug::Text('	 Section: '. $section, __FILE__, __LINE__, __METHOD__, 10);

				foreach ($permissions as $name => $value) {
					//Debug::Text('		Name: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
					if (	(
							!isset($old_permission_arr[$section][$name])
								OR (isset($old_permission_arr[$section][$name]) AND $value != $old_permission_arr[$section][$name] )
							)
							AND $pf->isIgnore( $section, $name, $this->getCompanyObject()->getProductEdition() ) == FALSE
							) {

						if ( $value == 0 OR $value == 1 ) {
							Debug::Text('	 Modifying/Adding Section: '. $section .' Permission: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
							$tmp_pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $tmp_pf */
							$tmp_pf->setEnableSectionAndNameValidation( FALSE ); //Disable error checking for performance optimization, as we know they are correct.
							$tmp_pf->setCompany( $this->getCompanyObject()->getId() );
							$tmp_pf->setPermissionControl( $this->getId() );
							$tmp_pf->setSection( $section );
							$tmp_pf->setName( $name );
							$tmp_pf->setValue( (int)$value );
							if ( $tmp_pf->isValid() ) {
								$tmp_pf->save();
							}
						}
					} //else { //Debug::Text('	   Permission didnt change... Skipping', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		$profiler->stopTimer( 'setPermission' );

		return TRUE;
	}

	/**
	 * Quick way to touch the updated_date, updated_by when adding/removing employees from the UserFactory.
	 * @param string $permission_control_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function touchUpdatedByAndDate( $permission_control_id = NULL ) {
		global $current_user;

		if ( is_object($current_user) ) {
			$user_id = $current_user->getID();
		} else {
			return FALSE;
		}

		$ph = array(
					'updated_date' => TTDate::getTime(),
					'updated_by' => $user_id,
					'id' => ( $permission_control_id == '' ) ? TTUUID::castUUID($this->getID()) : TTUUID::castUUID($permission_control_id),
					);

		$query = 'update '. $this->getTable() .' set updated_date = ?, updated_by = ? where id = ?';
		$this->ExecuteSQL($query, $ph);

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		// Name
		$this->Validator->isLength(	'name',
											$this->getName(),
											TTi18n::gettext('Name is invalid'),
											2, 50
										);
		if ( $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isTrue(	'name',
											$this->isUniqueName($this->getName()),
											TTi18n::gettext('Name is already in use')
										);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength(	'description',
											$this->getDescription(),
											TTi18n::gettext('Description is invalid'),
											1, 255
										);
		}
		// Level
		$this->Validator->inArrayKey(	'level',
											$this->getLevel(),
											TTi18n::gettext('Incorrect Level'),
											$this->getOptions('level')
										);
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Don't allow deleting permissions groups with users assigned to them, to prevent locking themselves out.
		if ( $this->getDeleted() == TRUE ) {
			$users = $this->getUser();
			if ( is_array($users) AND count($users) > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  FALSE,
										  TTi18n::gettext( 'This permission group is currently in use by employees' ) );
			}
			unset($users);

			//Also check for users assigned by way of Terminated Permission Group
			$ulf = TTnew('UserListFactory');
			$ulf->getByCompanyIdAndTerminatedPermissionControl( $this->getCompany(), $this->getId(), 1 ); //Limit=1
			if ( $ulf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  FALSE,
										  TTi18n::gettext( 'This permission group is currently in use by employees as their Terminated Permissions' ) );
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getLevel() == '' OR $this->getLevel() == 0 ) {
			$this->setLevel( 1 );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getID() );
		$this->removeCache( $this->getCompany().$this->getID() ); //Used in PermissionControlListFactory::getByIdAndCompanyId()

		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */

		$clear_cache_user_ids = array_merge( (array)$this->getUser(), (array)$this->tmp_previous_user_ids);
		foreach( $clear_cache_user_ids as $user_id ) {
			$pf->clearCache( $user_id, $this->getCompany() );
		}
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
	 * @param $data
	 * @return bool
	 */
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


	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'total_users':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Permission Group').': '. $this->getName(), NULL, $this->getTable(), $this );
	}
}
?>
