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
 * @package Modules\Qualification
 */
class UserSkillFactory extends Factory {
	protected $table = 'user_skill';
	protected $pk_sequence_name = 'user_skill_id_seq'; //PK Sequence name
	protected $qualification_obj = null;
	//protected $experience_validator_regex = '/^[0-9]{1,250}$/i';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'proficiency':
				$retval = [
						10 => TTi18n::gettext( 'Excellent' ),
						20 => TTi18n::gettext( 'Very Good' ),
						30 => TTi18n::gettext( 'Good' ),
						40 => TTi18n::gettext( 'Above Average' ),

						50 => TTi18n::gettext( 'Average' ),

						60 => TTi18n::gettext( 'Below Average' ),
						70 => TTi18n::gettext( 'Fair' ),
						80 => TTi18n::gettext( 'Poor' ),
						90 => TTi18n::gettext( 'Bad' ),
				];
				break;
			case 'source_type':
				$qf = TTnew( 'QualificationFactory' ); /** @var QualificationFactory $qf */
				$retval = $qf->getOptions( $name );
				break;
			case 'columns':
				$retval = [
						'-1010-first_name'             => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'              => TTi18n::gettext( 'Last Name' ),
						'-2050-qualification'          => TTi18n::gettext( 'Skill' ),
						'-2040-group'                  => TTi18n::gettext( 'Group' ),
						'-2060-proficiency'            => TTi18n::gettext( 'Proficiency' ),
						'-2070-experience'             => TTi18n::gettext( 'Experience' ),
						'-2080-first_used_date'        => TTi18n::gettext( 'First Used Date' ),
						'-2090-last_used_date'         => TTi18n::gettext( 'Last Used Date' ),
						'-3010-enable_calc_experience' => TTi18n::gettext( 'Automatic Experience' ),
						'-3020-expiry_date'            => TTi18n::gettext( 'Expiry Date' ),
						'-1040-description'            => TTi18n::getText( 'Description' ),

						'-1300-tag' => TTi18n::gettext( 'Tags' ),


						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Employee Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'qualification',
						'proficiency',
						'experience',
						'first_used_date',
						'last_used_date',
						//'enable_calc_experience',
						'expiry_date',
						'description',
				];
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
				'id'                     => 'ID',
				'user_id'                => 'User',
				'first_name'             => false,
				'last_name'              => false,
				'qualification_id'       => 'Qualification',
				'qualification'          => false,
				'group'                  => false,
				'proficiency_id'         => 'Proficiency',
				'proficiency'            => false,
				'experience'             => 'Experience',
				'first_used_date'        => 'FirstUsedDate',
				'last_used_date'         => 'LastUsedDate',
				'enable_calc_experience' => 'EnableCalcExperience',
				'expiry_date'            => 'ExpiryDate',
				'description'            => 'Description',
				'tag'                    => 'Tag',

				'default_branch'     => false,
				'default_department' => false,
				'user_group'         => false,
				'title'              => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}


	/**
	 * @return bool
	 */
	function getQualificationObject() {
		return $this->getGenericObject( 'QualificationListFactory', $this->getQualification(), 'qualification_obj' );
	}

	/**
	 * @return bool|mixed
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

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function getQualification() {
		return $this->getGenericDataValue( 'qualification_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setQualification( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'qualification_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getProficiency() {
		return $this->getGenericDataValue( 'proficiency_id' );
	}

	/**
	 * @param string $value int
	 * @return bool
	 */
	function setProficiency( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'proficiency_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getExperience() {
		$value = $this->getGenericDataValue( 'experience' );
		if ( $value !== false && $value != '' ) {

			//Because experience is stored in a different column in the database, it doesn't get updated
			//in real-time. So each time this function is called and EnableCalcExperience is enabled,
			//calculate the experience again to its always accurate.
			//This is especially required when no last_used_date is set.
			$retval = ( $this->getEnableCalcExperience() == true ) ? $this->calcExperience() : ( $value / 1000 ); //Divide by 1000 to convert to non-float value.

			return Misc::removeTrailingZeros( round( $retval, 4 ), 2 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExperience( $value ) {
		//This should always be set as years.
		$value = (float)$this->Validator->stripNonFloat( trim( $value ) );
		//Assume they passed in number of seconds, convert to years.
		if ( $value >= 1000 ) {
			$value = ( $value / 1000 );
		}
		if ( $value < 0 ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'experience', $this->Validator->stripNon32bitInteger( $value * 1000 ) ); //Multiply by 1000 to convert to non-float value.
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getFirstUsedDate( $raw = false ) {
		return (int)$this->getGenericDataValue( 'first_used_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setFirstUsedDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'first_used_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getLastUsedDate( $raw = false ) {
		return (int)$this->getGenericDataValue( 'last_used_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastUsedDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'last_used_date', $value );
	}

	/**
	 * @return bool|float|int
	 */
	function calcExperience() {
		if ( !empty( $this->getFirstUsedDate() ) ) {
			$last_used_date = $this->getLastUsedDate();
			if ( empty( $this->getLastUsedDate() ) ) {
				$last_used_date = TTDate::getEndDayEpoch( time() );
			}

			$total_time = round( TTDate::getYears( ( $last_used_date - TTDate::getBeginDayEpoch( $this->getFirstUsedDate() ) ) ), 2 );
			if ( $total_time < 0 ) {
				$total_time = 0;
			}

			Debug::text( ' First Used Date: ' . $this->getFirstUsedDate() . ' Last Used Date: ' . $last_used_date . ' Total Yrs: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10 );

			return $total_time;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcExperience() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_calc_experience' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableCalcExperience( $value ) {
		return $this->setGenericDataValue( 'enable_calc_experience', $this->toBool( $value ) );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getExpiryDate( $raw = false ) {
		return (int)$this->getGenericDataValue( 'expiry_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setExpiryDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'expiry_date', $value );
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
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== false ) {
			return $value;
		} else if ( is_object( $this->getQualificationObject() )
				&& TTUUID::isUUID( $this->getQualificationObject()->getCompany() ) && $this->getQualificationObject()->getCompany() != TTUUID::getZeroID() && $this->getQualificationObject()->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 251, $this->getID() );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}


	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Employee
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Employee must be specified' )
			);
		}
		// Qualification
		if ( $this->getQualification() !== false ) {
			$qlf = TTnew( 'QualificationListFactory' ); /** @var QualificationListFactory $qlf */
			$this->Validator->isResultSetWithRows( 'qualification_id',
												   $qlf->getById( $this->getQualification() ),
												   TTi18n::gettext( 'Skill must be specified' )
			);
		}
		// Proficiency
		if ( $this->getProficiency() !== false ) {
			$this->Validator->inArrayKey( 'proficiency_id',
										  $this->getProficiency(),
										  TTi18n::gettext( 'Proficiency must be specified' ),
										  $this->getOptions( 'proficiency' )
			);
		}
		// Experience number
		if ( $this->getExperience() != '' ) {
			$this->Validator->isNumeric( 'experience',
										 $this->getExperience(),
										 TTi18n::gettext( 'Years experience must only be digits' )
			);
			if ( $this->Validator->isError( 'experience' ) == false ) {
				$this->Validator->isLessThan( 'experience',
											  $this->getExperience(),
											  TTi18n::gettext( 'Years experience is too high' ),
											  110
				);
			}
		}
		// First used date
		if ( $this->getFirstUsedDate() != '' ) {
			$this->Validator->isDate( 'first_used_date',
									  $this->getFirstUsedDate(),
									  TTi18n::gettext( 'First used date is invalid' )
			);
		}
		// Last used date
		if ( $this->getLastUsedDate() != '' ) {
			$this->Validator->isDate( 'last_used_date',
									  $this->getLastUsedDate(),
									  TTi18n::gettext( 'Last used date is invalid' )
			);
		}
		// Expiry time stamp
		if ( $this->getExpiryDate() != '' ) {
			$this->Validator->isDate( 'expiry_date',
									  $this->getExpiryDate(),
									  TTi18n::gettext( 'Expiry date is invalid' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is invalid' ),
										2, 255
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getEnableCalcExperience() == true ) {
			$this->setExperience( $this->calcExperience() );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getUser() . $this->getQualification() );

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 251, $this->getID(), $this->getTag() );
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
						case 'first_used_date':
							$this->setFirstUsedDate( TTDate::parseDateTime( $data['first_used_date'] ) );
							break;
						case 'last_used_date':
							$this->setLastUsedDate( TTDate::parseDateTime( $data['last_used_date'] ) );
							break;
						case 'expiry_date':
							$this->setExpiryDate( TTDate::parseDateTime( $data['expiry_date'] ) );
							break;
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
						case 'qualification':
						case 'group':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'proficiency':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'first_used_date':
							$data['first_used_date'] = TTDate::getAPIDate( 'DATE', $this->getFirstUsedDate() );
							break;
						case 'last_used_date':
							$data['last_used_date'] = TTDate::getAPIDate( 'DATE', $this->getLastUsedDate() );
							break;
						case 'expiry_date':
							$data['expiry_date'] = TTDate::getAPIDate( 'DATE', $this->getExpiryDate() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Skill' ), null, $this->getTable(), $this );
	}

}

?>
