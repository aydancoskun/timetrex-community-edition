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
 * @package Modules\Department
 */
class DepartmentFactory extends Factory {
	protected $table = 'department';
	protected $pk_sequence_name = 'department_id_seq'; //PK Sequence name


	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('ENABLED'),
										20 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-manual_id' => TTi18n::gettext('Code'),
										'-1030-name' => TTi18n::gettext('Name'),

										'-1300-tag' => TTi18n::gettext('Tags'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'manual_id',
								'name',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'manual_id'
								);
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
										'status_id' => 'Status',
										'status' => FALSE,
										'manual_id' => 'ManualID',
										'name' => 'Name',
										'name_metaphone' => 'NameMetaphone',
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'geo_fence_ids' => 'GEOFenceIds',
										'tag' => 'Tag',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		//Have to return the KEY because it should always be a drop down box.
		//return Option::getByKey($this->data['status_id'], $this->getOptions('status') );
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqueManualID( $id) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'manual_id' => $id,
					'company_id' =>	$this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where manual_id = ? AND company_id = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Department: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $company_id UUID
	 * @return int
	 */
	function getNextAvailableManualId( $company_id = NULL ) {
		global $current_company;

		if ( $company_id == '' AND is_object($current_company) ) {
			$company_id = $current_company->getId();
		} elseif ( $company_id == '' AND isset($this) AND is_object($this) ) {
			$company_id = $this->getCompany();
		}

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getHighestManualIDByCompanyId( $company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			$next_available_manual_id = ( $dlf->getCurrent()->getManualId() + 1 );
		} else {
			$next_available_manual_id = 1;
		}

		return $next_available_manual_id;
	}

	/**
	 * @return bool|int
	 */
	function getManualID() {
		return (int)$this->getGenericDataValue( 'manual_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setManualID( $value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
		return $this->setGenericDataValue( 'manual_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->table .'
					where company_id = ?
						AND lower(name) = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		//Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool|mixed
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
		$this->setNameMetaphone( $value );
		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNameMetaphone() {
		return $this->getGenericDataValue( 'name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNameMetaphone( $value) {
		$value = metaphone( trim($value) );

		if	( $value != '' ) {
			$this->setGenericDataValue( 'name_metaphone', $value );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return array|bool
	 */
	function getBranch() {
		$branch_list = array();
		$dblf = TTnew( 'DepartmentBranchListFactory' );
		$dblf->getByDepartmentId( $this->getId() );
		foreach ($dblf as $department_branch) {
			$branch_list[] = $department_branch->getBranch();
		}

		if ( empty($branch_list) == FALSE) {
			return $branch_list;
		}

		return FALSE;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setBranch( $ids) {
		if (is_array($ids) AND count($ids) > 0) {
			//If needed, delete mappings first.
			$dblf = TTnew( 'DepartmentBranchListFactory' );
			$dblf->getByDepartmentId( $this->getId() );

			$branch_ids = array();
			foreach ($dblf as $department_branch) {
				$branch_id = $department_branch->getBranch();
				Debug::text('Department ID: '. $department_branch->getDepartment() .' Branch: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);

				//Delete branches that are not selected.
				if ( !in_array($branch_id, $ids) ) {
					Debug::text('Deleting DepartmentBranch: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
					$department_branch->Delete();
				} else {
					//Save branch ID's that need to be updated.
					Debug::text('NOT Deleting DepartmentBranch: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
					$branch_ids[] = $branch_id;
				}
			}

			//Insert new mappings.
			$dbf = TTnew( 'DepartmentBranchFactory' );
			foreach ($ids as $id) {
				if ( !in_array($id, $branch_ids) ) {
					$dbf->setDepartment( $this->getId() );
					$dbf->setBranch( $id );

					if ($this->Validator->isTrue(		'branch',
														$dbf->Validator->isValid(),
														TTi18n::gettext('Branch selection is invalid'))) {
						$dbf->save();
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getOtherID1() {
		return $this->getGenericDataValue( 'other_id1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID1( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id1', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID2() {
		return $this->getGenericDataValue( 'other_id2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID2( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id2', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID3() {
		return $this->getGenericDataValue( 'other_id3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID3( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id3', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID4() {
		return $this->getGenericDataValue( 'other_id4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID4( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id4', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID5() {
		return $this->getGenericDataValue( 'other_id5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID5( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id5', $value );
	}


	/**
	 * @return array|bool
	 */
	function getGEOFenceIds() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 4010, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setGEOFenceIds( $ids) {
		Debug::text('Setting GEO Fence IDs...', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 4010, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== FALSE ) {
			return $value;
		} elseif ( TTUUID::isUUID( $this->getCompany() ) AND $this->getCompany() != TTUUID::getZeroID() AND $this->getCompany() != TTUUID::getNotExistID()
				AND TTUUID::isUUID( $this->getID() ) AND $this->getID() != TTUUID::getZeroID() AND $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 120, $this->getID() );
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim($value);
		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}
	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' );
			$this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($this->getCompany()),
															TTi18n::gettext('Company is invalid')
														);
		}
		// Status
		if ( $this->getStatus() !== FALSE ) {
			$this->Validator->inArrayKey(	'status',
													$this->getStatus(),
													TTi18n::gettext('Incorrect Status'),
													$this->getOptions('status')
												);
		}
		// Code
		if ( $this->getManualID() != '' ) {
			$this->Validator->isNumeric(	'manual_id',
													$this->getManualID(),
													TTi18n::gettext('Code is invalid')
												);
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isLength(	'manual_id',
													$this->getManualID(),
													TTi18n::gettext('Code has too many digits'),
													0,
													10
												);
			}
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isTrue(	'manual_id',
											( $this->Validator->stripNon32bitInteger( $this->getManualID() ) === 0 ) ? FALSE : TRUE,
											TTi18n::gettext('Code is invalid, maximum value exceeded')
										);
			}
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isTrue(		'manual_id',
														$this->isUniqueManualID($this->getManualID()),
														TTi18n::gettext('Code is already in use, please enter a different one')
													);
			}
		}
		// Department name
		if ( $this->getName() !== FALSE ) {
			$this->Validator->isLength(		'name',
													$this->getName(),
													TTi18n::gettext('Department name is too short or too long'),
													2,
													100
												);
			if ( $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue(		'name',
														$this->isUniqueName($this->getName()),
														TTi18n::gettext('Department already exists')
													);
			}
		}
		// Other ID 1
		if ( $this->getOtherID1() != '' ) {
			$this->Validator->isLength(	'other_id1',
												$this->getOtherID1(),
												TTi18n::gettext('Other ID 1 is invalid'),
												1, 255
											);
		}
		// Other ID 2
		if ( $this->getOtherID2() != '' ) {
			$this->Validator->isLength(	'other_id2',
												$this->getOtherID2(),
												TTi18n::gettext('Other ID 2 is invalid'),
												1, 255
											);
		}
		// Other ID 3
		if ( $this->getOtherID3() != '' ) {
			$this->Validator->isLength(	'other_id3',
												$this->getOtherID3(),
												TTi18n::gettext('Other ID 3 is invalid'),
												1, 255
											);
		}
		// Other ID 4
		if ( $this->getOtherID4() != '' ) {
			$this->Validator->isLength(	'other_id4',
												$this->getOtherID4(),
												TTi18n::gettext('Other ID 4 is invalid'),
												1, 255
											);
		}
		// Other ID 5
		if ( $this->getOtherID5() != '' ) {
			$this->Validator->isLength(	'other_id5',
												$this->getOtherID5(),
												TTi18n::gettext('Other ID 5 is invalid'),
												1, 255
											);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == FALSE ) {
			$this->setStatus(10);
		}

		if ( $this->getManualID() == FALSE ) {
			$this->setManualID( $this->getNextAvailableManualId( $this->getCompany() ) );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 120, $this->getID(), $this->getTag() );
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Hours from Department: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this department.
			$pcf = TTnew( 'PunchControlFactory' );
			$udtf = TTnew( 'UserDateTotalFactory' );
			$uf = TTnew( 'UserFactory' );
			$sf = TTnew( 'StationFactory' );
			$sdf = TTnew( 'StationDepartmentFactory' );
			$sf_b = TTnew( 'ScheduleFactory' );
			$udf = TTnew( 'UserDefaultFactory' );
			$rstf = TTnew( 'RecurringScheduleTemplateFactory' );
			$rsf = TTnew( 'RecurringScheduleFactory' );

			$query = 'update '. $pcf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $udtf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $sf_b->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $uf->getTable() .' set default_department_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND default_department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $udf->getTable() .' set default_department_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND default_department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $sf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'delete from '. $sdf->getTable() .' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $rstf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$query = 'update '. $rsf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				$jf = TTNew('JobFactory');
				$query = 'update '. $jf->getTable() .' set department_id = \''. TTUUID::getZeroID() .'\' where department_id = \''. TTUUID::castUUID($this->getId()) .'\'';
				$this->db->Execute($query);

				//Job employee criteria
				$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
				$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1020, $this->getID() );
				if ( $cgmlf->getRecordCount() > 0 ) {
					foreach( $cgmlf as $cgm_obj ) {
						Debug::text('Deleteing from Company Generic Map: '. $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$cgm_obj->Delete();
					}
				}

			}


		}

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	/**
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
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Department') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}
}
?>
