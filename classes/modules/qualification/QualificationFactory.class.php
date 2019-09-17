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
 * @package Modules\Qualification
 */
class QualificationFactory extends Factory {
	protected $table = 'qualification';
	protected $pk_sequence_name = 'qualification_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Skill'),
										20 => TTi18n::gettext('Education'),
										30 => TTi18n::gettext('License'),
										40 => TTi18n::gettext('Language'),
										50 => TTi18n::gettext('Membership')
									);
				break;
			case 'source_type':
				//Remove sections that don't apply to the current product edition.
				$product_edition = Misc::getCurrentCompanyProductEdition();

				$retval = array();
				$retval[10] = TTi18n::gettext('Internal');
				if ( $product_edition >= TT_PRODUCT_ENTERPRISE ) {
					$retval[20] = TTi18n::gettext( 'Portal' );
				}
				break;
			case 'visibility_type':
				//Remove sections that don't apply to the current product edition.
				$product_edition = Misc::getCurrentCompanyProductEdition();

				$retval = array();
				$retval[10] = TTi18n::gettext('Internal Only');
				if ( $product_edition >= TT_PRODUCT_ENTERPRISE ) {
					$retval[20] = TTi18n::gettext('Portal Only');
					$retval[100] = TTi18n::gettext('Both');
				}
				break;
			case 'columns':
				$retval = array(
										'-1030-name' => TTi18n::gettext('Name'),
										'-1040-description' => TTi18n::getText('Description'),
										'-1050-type' => TTi18n::getText('Type'),
										'-1060-visibility_type' => TTi18n::gettext('Visibility'),
										'-1070-source_type' => TTi18n::gettext('Source'),
										'-2040-group' => TTi18n::gettext('Group'),
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
								'type',
								'name',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name'
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
										'type_id' => 'Type',
										'type' => FALSE,
										'visibility_type_id' => 'VisibilityType',
										'visibility_type' => FALSE,
										'source_type_id' => 'SourceType',
										'source_type' => FALSE,
										'company_id' => 'Company',
										'group_id' => 'Group',
										'group' => FALSE,
										'name' => 'Name',
										'name_metaphone' => 'NameMetaphone',
										'description' => 'Description',

										'tag' => 'Tag',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param $name
	 * @param int $source_type_id
	 * @param int $visibility_type_id
	 * @return bool
	 */
	static function getOrCreateQualification( $company_id, $type_id, $name, $source_type_id = 20, $visibility_type_id = 20 ) {
		$qlf = new QualificationListFactory();
		$qlf->getByCompanyIdAndTypeIdAndName( $company_id, $type_id, $name );
		if ( $qlf->getRecordCount() == 1 ) {
			$qualification_id = $qlf->getCurrent()->getID();
		} else {
			$qf = TTnew('QualificationFactory'); /** @var QualificationFactory $qf */
			$qf->setType( $type_id );
			$qf->setVisibilityType( $visibility_type_id );
			$qf->setSourceType( $source_type_id );
			$qf->setCompany( $company_id );
			$qf->setName( $name );
			if ( $qf->isValid() ) {
				$qualification_id = $qf->Save();
			}
		}

		if ( isset( $qualification_id ) ) {
			return $qualification_id;
		}

		return FALSE;
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
	function setType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getSourceType() {
		return $this->getGenericDataValue( 'source_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSourceType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'source_type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getVisibilityType() {
		return $this->getGenericDataValue( 'visibility_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setVisibilityType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'visibility_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}


	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getGroup() {
		return $this->getGenericDataValue( 'group_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setGroup( $value) {
		$value = TTUUID::castUUID( $value );

		Debug::Text('Group ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'group_id', $value );
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
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

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
	 * @return bool|mixed
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
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 250, $this->getID() );
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
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Type
		if ( $this->getType() !== FALSE ) {
			$this->Validator->inArrayKey(	'type_id',
												$this->getType(),
												TTi18n::gettext('Type is invalid'),
												$this->getOptions('type')
											);
		}
		// Source Type
		if ( $this->getSourceType() !== FALSE ) {
			$this->Validator->inArrayKey(	'source_type_id',
												$this->getSourceType(),
												TTi18n::gettext('Source Type is invalid'),
												$this->getOptions('source_type')
											);
		}

		// Visibility Type
		if ( $this->getVisibilityType() !== FALSE ) {
			$this->Validator->inArrayKey(	'visibility_type_id',
												$this->getVisibilityType(),
												TTi18n::gettext('Visibility Type is invalid'),
												$this->getOptions('visibility_type')
											);
		}
		// Company
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows(	'company_id',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		}
		// Group
		if ( $this->getGroup() !== FALSE AND $this->getGroup() != TTUUID::getZeroID() ) {
			$qglf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $qglf */
			$this->Validator->isResultSetWithRows(	'group_id',
														$qglf->getByID($this->getGroup()),
														TTi18n::gettext('Group is invalid')
													);
		}
		// Qualification name
		if ( $this->getName() !== FALSE ) {
			$this->Validator->isLength( 'name',
												$this->getName(),
												TTi18n::gettext('Qualification name is too short or too long'),
												1,
												255
											);
			if ( $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue(	'name',
												$this->isUniqueName($this->getName()),
												TTi18n::gettext('Qualification name already exists')
											);
			}
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
											$this->getDescription(),
											TTi18n::gettext('Description is invalid'),
											2, 255
										);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getVisibilityType() == FALSE ) {
			$this->setVisibilityType( 10 ); //Internal Only
		}

		if ( $this->getSourceType() == FALSE ) {
			$this->setSourceType( 10 ); //Internal
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {

		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 250, $this->getID(), $this->getTag() );
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Hours from Qualification: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this qualification.

			$sf = TTnew( 'UserSkillFactory' ); /** @var UserSkillFactory $sf */
			$ef = TTnew( 'UserEducationFactory' ); /** @var UserEducationFactory $ef */
			$lf = TTnew( 'UserLicenseFactory' ); /** @var UserLicenseFactory $lf */
			$lg = TTnew( 'UserLanguageFactory' ); /** @var UserLanguageFactory $lg */
			$mf = TTnew( 'UserMembershipFactory' ); /** @var UserMembershipFactory $mf */

			$query = 'update '. $sf->getTable() .' set qualification_id = \''. TTUUID::getZeroID() .'\' where qualification_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $ef->getTable() .' set qualification_id = \''. TTUUID::getZeroID() .'\' where qualification_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $lf->getTable() .' set qualification_id = \''. TTUUID::getZeroID() .'\' where qualification_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $lg->getTable() .' set qualification_id = \''. TTUUID::getZeroID() .'\' where qualification_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $mf->getTable() .' set qualification_id = \''. TTUUID::getZeroID() .'\' where qualification_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);
			//Job employee criteria
		}

		return TRUE;
	}

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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE   ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;

					switch( $variable ) {
						case 'group':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'source_type':
							$function = 'getSourceType';
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'visibility_type':
							$function = 'getVisibilityType';
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'type':
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
			$this->getPermissionColumns( $data, $this->getCreatedBy(), FALSE, $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Qualification') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
