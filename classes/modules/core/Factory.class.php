<?php /** @noinspection PhpUndefinedFunctionInspection */
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
abstract class Factory {
	//**IMPORTANT** These must all be reset in FactoryListIterator->__construct()
	public $data = [];
	public $old_data = []; //Used for detailed audit log.
	public $tmp_data = [];

	protected $enable_system_log_detail = true;

	protected $progress_bar_obj = null;
	protected $api_message_id = null;

	public $Validator = null;
	public $validate_only = false; //Used by the API to ignore certain validation checks if we are doing validation only.
	private $is_valid = false;     //Flag that determines if the data is valid since it was last changed or not.
	//**IMPORTANT** These must all be reset in FactoryListIterator->__construct()

	/**
	 * Factory constructor.
	 */
	function __construct() {
		global $db, $cache;

		$this->db = $db;
		$this->cache = $cache;
		$this->Validator = new Validator();

		//Callback to the child constructor method.
		if ( method_exists( $this, 'childConstruct' ) ) {
			$this->childConstruct();
		}

		return true;
	}

	/**
	 * Used for updating progress bar for API calls.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		if ( $id != '' ) {
			$this->api_message_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setProgressBarObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->progress_bar_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * Allow method to pre-populate/overwrite the cache if needed.
	 * @param object $obj
	 * @param string $variable
	 * @return bool
	 */
	function setGenericObject( $obj, $variable ) {
		$this->$variable = $obj;

		return true;
	}

	/**
	 * Generic function to return and cache class objects
	 * ListFactory, ListFactoryMethod, Variable, ID, IDMethod
	 * @param string $list_factory
	 * @param string|int $id UUID
	 * @param string $variable
	 * @param string $list_factory_method
	 * @param string $id_method
	 * @return object|bool
	 */
	function getGenericObject( $list_factory, $id, $variable, $list_factory_method = 'getById', $id_method = 'getID' ) {
		if ( isset( $this->$variable ) && is_object( $this->$variable ) && $id == $this->$variable->$id_method() ) { //Make sure we always compare that the object IDs match.
			return $this->$variable;
		} else {
			$lf = TTnew( $list_factory );
			$lf->$list_factory_method( $id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->$variable = $lf->getCurrent();

				return $this->$variable;
			}

			return false;
		}
	}

	/**
	 * Generic function to return and cache CompanyGenericMap data, this greatly improves performance of CalculatePolicy when many policies exist.
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $id         UUID
	 * @param string $variable
	 * @return mixed
	 */
	function getCompanyGenericMapData( $company_id, $object_type_id, $id, $variable ) {
		$tmp = &$this->$variable; //Works around a PHP issues where $this->$variable[$id] cause a fatal error on unknown string offset
		if ( TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID()
				&& isset( $tmp[$id] ) ) {
			return $tmp[$id];
		} else {
			$tmp[$id] = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $id );

			return $tmp[$id];
		}
	}

	/**
	 * Generic getter/setter functions that should be used when Validation code is moved from get/set functions to Validate() function.
	 * @param string $name
	 * @param null $cast
	 * @return bool|mixed
	 */
	function getGenericDataValue( $name, $cast = null ) {

		//FIXME: This won't pass through NULL values from the DB, because isset() checks for NULL.
		//Use array_key_exists(), instead, then return whatever it has, including NULL. Be sure to update getGenericTempDataValue() too.
		if ( isset( $this->data[$name] ) ) {
//			if ( $cast != '' ) {
//				$this->castGenericDataValue( $this->data[$name], $cast );
//			}

			return $this->data[$name];
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param mixed $data
	 * @param null $cast
	 * @return bool
	 */
	function setGenericDataValue( $name, $data, $cast = null ) {
		$this->is_valid = false; //Force revalidation when data is changed.

//		if ( $cast != '' ) {
//			$this->castGenericDataValue( $data, $cast );
//		}

		$this->data[$name] = $data;

		return true;
	}

	/**
	 * Generic casting function that all set/get*() functions should pass through.
	 * However for now lets wait until we have meta data from SQL schema so we can pass those datatypes directly into this.
	 * @param $value mixed
	 * @param $cast  string
	 * @return mixed
	 */
	function castGenericDataValue( &$value, $cast ) {
		if ( $cast != '' ) {
			$cast = strtolower( $cast );

			switch ( $cast ) {
				case 'uuid':
					$value = TTUUID::castUUID( $value );
					break;
				case 'uuid+zero':
					$value = TTUUID::castUUID( $value );
					if ( $value == '' ) {
						$value = TTUUID::getZeroID();
					}
					break;
				default:
					if ( settype( $value, $cast ) == false ) {
						Debug::Arr( $value, 'ERROR: Unable to cast variable to: ' . $cast, __FILE__, __LINE__, __METHOD__, 10 );
					}
					break;
			}
		}

		return $value;
	}

	/**
	 * Generic getter/setter functions that should be used when Validation code is moved from get/set functions to Validate() function.
	 * @param string $name
	 * @return bool
	 */
	function getGenericTempDataValue( $name ) {
		if ( isset( $this->tmp_data[$name] ) ) {
			return $this->tmp_data[$name];
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param mixed $data
	 * @return bool
	 */
	function setGenericTempDataValue( $name, $data ) {
		$this->is_valid = false; //Force revalidation when data is changed.
		$this->tmp_data[$name] = $data;

		return true;
	}


	/**
	 * @param string $name Gets data value from old_data array, or the original value in the database, prior to any changes currently in memory.
	 * @return bool|mixed
	 */
	function getGenericOldDataValue( $name ) {
		if ( isset( $this->old_data[$name] ) ) {
			return $this->old_data[$name];
		}

		return false;
	}

	/*
	 * Cache functions
	 */
	/**
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool|mixed
	 */
	function getCache( $cache_id, $group_id = null ) {
		if ( is_object( $this->cache ) ) {
			if ( $group_id == null ) {
				$group_id = $this->getTable( true );
			}

			$retval = $this->cache->get( $cache_id, $group_id );
			if ( is_object( $retval ) && get_class( $retval ) == 'PEAR_Error' ) {
				Debug::Arr( $retval, 'WARNING: Unable to read cache file, likely due to permissions or locking! Cache ID: ' . $cache_id . ' Table: ' . $this->getTable( true ) . ' File: ' . $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10 );
			} else if ( is_string( $retval ) && strpos( $retval, '====' ) === 0 ) { //Detect ADODB serialized record set so it can be properly unserialized.
				return $this->unserializeRS( $retval );
			} else {
				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param mixed $data
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool
	 */
	function saveCache( $data, $cache_id, $group_id = null ) {
		//Cache_ID can't have ':' in it, otherwise it fails on Windows.
		if ( is_object( $this->cache ) ) {
			if ( $group_id == null ) {
				$group_id = $this->getTable( true );
			}

			//Check to ADODB record set, then serialize properly. We only need to do special serializing when there are more than one record.
			if ( is_object( $data ) && strpos( get_class( $data ), 'ADORecordSet_' ) === 0 && $data->RecordCount() > 1 ) {
				$data = $this->serializeRS( $data );
			}
			$retval = $this->cache->save( $data, $cache_id, $group_id );
			if ( $this->cache->_caching == true && $retval === false ) { //If caching is disabled, save() will always return FALSE.
				//Due to locking, its common that cache files may fail writing once in a while.
				Debug::text( 'WARNING: Unable to write cache file, likely due to permissions or locking! Cache ID: ' . $cache_id . ' Table: ' . $this->getTable( true ) . ' File: ' . $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return $retval;
		}

		return false;
	}

	/**
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool
	 */
	function removeCache( $cache_id = null, $group_id = null ) {
		//See ContributingPayCodePolicyFactory() ->getPayCode() for comments on a bug with caching...
		Debug::text( 'Attempting to remove cache: ' . $cache_id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_object( $this->cache ) ) {
			$retval = false;

			if ( $group_id == '' ) {
				$group_id = $this->getTable( true );
			}

			//When using retryTransaction(), we set onlyMemoryCaching=TRUE.
			//  However Cache_Lite won't remove cache from persistent storage in that case. So whenever removing caching, set onlyMemoryCaching=FALSE so memory and persistent caches are cleared.
			$current_cache_memory_state = $this->cache->_onlyMemoryCaching;
			$this->cache->_onlyMemoryCaching = false;

			if ( $cache_id != '' ) {
				Debug::text( 'Removing cache: ' . $cache_id . ' Group Id: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $this->cache->remove( $cache_id, $group_id );
			} else if ( $group_id != '' ) {
				Debug::text( 'Removing cache group: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $this->cache->clean( $group_id );
			}

			$this->cache->_onlyMemoryCaching = $current_cache_memory_state;

			return $retval;
		}

		return false;
	}

	/**
	 * @param int $secs
	 * @return bool
	 */
	function setCacheLifeTime( $secs ) {
		if ( is_object( $this->cache ) ) {
			return $this->cache->setLifeTime( $secs );
		}

		return false;
	}

	/**
	 * Serialize ADODB recordset.
	 * @param object $rs
	 * @return string
	 * @noinspection PhpUndefinedConstantInspection
	 */
	function serializeRS( $rs ) {
		global $ADODB_INCLUDED_CSV;
		if ( empty( $ADODB_INCLUDED_CSV ) ) {
			include_once( ADODB_DIR . '/adodb-csvlib.inc.php' );
		}

		return _rs2serialize( $rs, false, $rs->sql );
	}

	/**
	 * UnSerialize ADODB recordset.
	 * @param string $rs
	 * @return mixed
	 */
	function unserializeRS( $rs ) {
		$rs = explode( "\n", $rs );
		unset( $rs[0] );
		$rs = join( "\n", $rs );

		return unserialize( $rs );
	}

	/**
	 * @param bool $strip_quotes
	 * @return bool|string
	 */
	function getTable( $strip_quotes = false ) {
		if ( isset( $this->table ) ) {
			if ( $strip_quotes == true ) {
				return str_replace( '"', '', $this->table );
			} else {
				return $this->table;
			}
		}

		return false;
	}

	/**
	 * Generic function get any data from the data array.
	 * Used mainly for the reports that return grouped queries and such.
	 * @param string $column
	 * @return bool|mixed
	 */
	function getColumn( $column ) {
		if ( isset( $this->data[$column] ) ) {
			return $this->data[$column];
		}

		return false;
	}

	/**
	 * Print primary columns from object.
	 * @return bool|string
	 */
	function __toString() {
		if ( method_exists( $this, 'getObjectAsArray' ) ) {
			$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
			$data = $this->getObjectAsArray( $columns );

			if ( is_array( $columns ) && is_array( $data ) ) {
				$retarr = [];
				foreach ( $columns as $column => $name ) {
					if ( isset( $data[$column] ) ) {
						$retarr[] = $name . ': ' . $data[$column];
					}
				}

				if ( count( $retarr ) > 0 ) {
					return implode( "\n", $retarr );
				}
			}
		}

		return false;
	}

	/**
	 * @param string|int|bool $value
	 * @return int
	 */
	function toBool( $value ) {
		$value = strtolower( trim( $value ) );

		if ( $value === true || $value == 1 || $value == 't' ) {
			//return 't';
			return 1;
		} else {
			//return 'f';
			return 0;
		}
	}

	/**
	 * @param string|int|bool $value
	 * @return bool
	 */
	function fromBool( $value ) {
		if ( $value == 1 ) {
			return true;
		} else if ( $value == 0 ) {
			return false;
		} else if ( strtolower( trim( $value ) ) == 't' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if the data is new data, or updated data. Basically determines if a database INSERT or UPDATE SQL statement is generated.
	 * @param bool $force_lookup
	 * @param string $id UUID
	 * @return bool
	 */
	function isNew( $force_lookup = false, $id = null ) {
		if ( $id === null ) {
			$id = $this->getId();
		}
		//Debug::Arr( $this->getId(), 'getId: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === false ) {
			//New Data
			return true;
		} else if ( $force_lookup == true ) {
			//See if we can find the ID to determine if the record needs to be inserted or update.
			$ph = [ 'id' => $id ]; // Do not cast to UUID as it needs to support both integer and UUID across v11 upgrade.
			$query = 'SELECT id FROM ' . $this->getTable() . ' WHERE id = ?';
			$retval = $this->db->GetOne( $query, $ph );
			if ( $retval === false ) {
				return true;
			}
		}

		//Not new data
		return false;
	}

	/**
	 * @return bool|mixed|string
	 */
	function getLabelId() {
		//Gets the ID used in validator labels. If no ID, uses "-1";
		if ( $this->getId() == false ) {
			return '-1';
		}

		return $this->getId();
	}

	/**
	 * @return bool|mixed
	 */
	function getId() {
//		if ( isset($this->data['id']) AND $this->data['id'] != NULL) {
////			return (int)$this->data['id'];
//			return $this->data['id'];
//		}

		$id = $this->getGenericDataValue( 'id' );
		if ( $id != null ) {
			return $id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setId( $id ) {
		global $PRIMARY_KEY_IS_UUID;

		if ( $PRIMARY_KEY_IS_UUID == false ) {
			if ( is_numeric( $id ) || is_bool( $id ) ) {
				$this->setGenericDataValue( 'id', $id ); //Allow ID to be set as FALSE. Essentially making a new entry.

				return true;
			}
		} else {
			if ( is_bool( $id ) || TTUUID::isUUID( $id ) ) {
				$this->setGenericDataValue( 'id', $id ); //Allow ID to be set as FALSE. Essentially making a new entry.

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableSystemLogDetail() {
		if ( isset( $this->enable_system_log_detail ) ) {
			return $this->enable_system_log_detail;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSystemLogDetail( $bool ) {
		$this->enable_system_log_detail = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getDeleted() {
		return $this->fromBool( $this->getGenericDataValue( 'deleted' ) );
	}

	/**
	 * @param bool $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		$value = (bool)$bool;

		//Handle Postgres's boolean values.
		if ( $value === true ) {
			//Only set this one we're deleting
			$this->setDeletedDate();
			$this->setDeletedBy();
		}

		$this->setGenericDataValue( 'deleted', $this->toBool( $value ) );

		return true;
	}

	/**
	 * @return int
	 */
	function getCreatedDate() {
		return (int)$this->getGenericDataValue( 'created_date' );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch ) ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'created_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'created_date', $epoch );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getCreatedBy() {
		return $this->getGenericDataValue( 'created_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		if ( TTUUID::isUUID( $id ) == false ) { //Don't change if its not a valid UUID.
			return false;
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'created_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->setGenericDataValue( 'created_by', $id );

			return TRUE;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'created_by', $id );

		return true;
	}

	/**
	 * @return int
	 */
	function getUpdatedDate() {
		return (int)$this->getGenericDataValue( 'updated_date' );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool|int|null|string
	 */
	function setUpdatedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch ) ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'updated_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'updated_date', $epoch );

			//return TRUE;
			//Return the value so we can use it in getUpdateSQL
			return $epoch;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUpdatedBy() {
		return $this->getGenericDataValue( 'updated_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool|null
	 */
	function setUpdatedBy( $id = null ) {
		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		if ( TTUUID::isUUID( $id ) == false ) { //Don't change if its not a valid UUID.
			return false;
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {
			$this->setGenericDataValue( 'updated_by', $id );

			//return TRUE;
			return $id;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'updated_by', $id );

		return $id;
	}


	/**
	 * @return bool|mixed
	 */
	function getDeletedDate() {
		return $this->getGenericDataValue( 'deleted_date' );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch ) ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'deleted_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'deleted_date', $epoch );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getDeletedBy() {
		return $this->getGenericDataValue( 'deleted_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool|null
	 */
	function setDeletedBy( $id = null ) {
		//$id = trim($id);

		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->setGenericDataValue( 'deleted_by', $id );

			return TRUE;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'deleted_by', $id );

		return $id;
	}

	/**
	 * Sets the is_valid flag, mostly used to set it to FALSE to force a full re-validation.
	 * Required because $this->is_valid is a private variable and should stay that way.
	 * @param bool $is_valid
	 * @return bool
	 */
	function setIsValid( $is_valid = false ) {
		$this->is_valid = $is_valid;

		return true;
	}

	/**
	 * @param array $data
	 * @param array $variable_to_function_map
	 * @return bool
	 */
	function setCreatedAndUpdatedColumns( $data, $variable_to_function_map = [] ) {
		//Debug::text(' Set created/updated columns...', __FILE__, __LINE__, __METHOD__, 10);

		//CreatedBy/Time needs to be set to original values when doing things like importing records.
		//However from the API, Created By only needs to be set for a small subset of classes like RecurringScheduleTemplateControl.
		//For now, only allow these fields to be changed from user input if its set in the variable_to_function_map.

		//Update array in-place.
		if ( isset( $data['created_by'] )
				&& TTUUID::isUUID( $data['created_by'] ) && $data['created_by'] != TTUUID::getZeroID() && $data['created_by'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['created_by'] ) ) {
			$this->setCreatedBy( $data['created_by'] );
		}
		if ( isset( $data['created_by_id'] )
				&& TTUUID::isUUID( $data['created_by_id'] ) && $data['created_by_id'] != TTUUID::getZeroID() && $data['created_by_id'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['created_by'] ) ) {
			$this->setCreatedBy( $data['created_by_id'] );
		}
		if ( isset( $data['created_date'] ) && $data['created_date'] != false && $data['created_date'] != '' && isset( $variable_to_function_map['created_date'] ) ) {
			$this->setCreatedDate( TTDate::parseDateTime( $data['created_date'] ) );
		}

		if ( isset( $data['updated_by'] )
				&& TTUUID::isUUID( $data['updated_by'] ) && $data['updated_by'] != TTUUID::getZeroID() && $data['updated_by'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['updated_by'] ) ) {
			$this->setUpdatedBy( $data['updated_by'] );
		}
		if ( isset( $data['updated_by_id'] ) && TTUUID::isUUID( $data['updated_by_id'] ) && $data['updated_by_id'] > 0 && isset( $variable_to_function_map['updated_by'] ) ) {
			$this->setUpdatedBy( $data['updated_by_id'] );
		}
		if ( isset( $data['updated_date'] ) && $data['updated_date'] != false && $data['updated_date'] != '' && isset( $variable_to_function_map['updated_date'] ) ) {
			$this->setUpdatedDate( TTDate::parseDateTime( $data['updated_date'] ) );
		}

		return true;
	}

	/**
	 * @param array $data
	 * @param null $include_columns
	 * @return bool
	 */
	function getCreatedAndUpdatedColumns( &$data, $include_columns = null ) {
		//Update array in-place.
		if ( $include_columns == null || ( isset( $include_columns['created_by_id'] ) && $include_columns['created_by_id'] == true ) ) {
			$data['created_by_id'] = $this->getCreatedBy();
		}
		if ( $include_columns == null || ( isset( $include_columns['created_by'] ) && $include_columns['created_by'] == true ) ) {
			$data['created_by'] = Misc::getFullName( $this->getColumn( 'created_by_first_name' ), $this->getColumn( 'created_by_middle_name' ), $this->getColumn( 'created_by_last_name' ) );
		}
		if ( $include_columns == null || ( isset( $include_columns['created_date'] ) && $include_columns['created_date'] == true ) ) {
			$data['created_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getCreatedDate() );
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_by_id'] ) && $include_columns['updated_by_id'] == true ) ) {
			$data['updated_by_id'] = $this->getUpdatedBy();
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_by'] ) && $include_columns['updated_by'] == true ) ) {
			$data['updated_by'] = Misc::getFullName( $this->getColumn( 'updated_by_first_name' ), $this->getColumn( 'updated_by_middle_name' ), $this->getColumn( 'updated_by_last_name' ) );
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_date'] ) && $include_columns['updated_date'] == true ) ) {
			$data['updated_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getUpdatedDate() );
		}

		return true;
	}

	/**
	 * @param array $data
	 * @param string $object_user_id          UUID
	 * @param string $created_by_id           UUID
	 * @param string $permission_children_ids UUID
	 * @param array $include_columns
	 * @return bool
	 */
	function getPermissionColumns( &$data, $object_user_id, $created_by_id, $permission_children_ids = null, $include_columns = null ) {
		$permission = new Permission();

		if ( $include_columns == null || ( isset( $include_columns['is_owner'] ) && $include_columns['is_owner'] == true ) ) {
			//If is_owner column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn( 'is_owner' ) !== false ) {
				$data['is_owner'] = (bool)$this->getColumn( 'is_owner' );
			} else {
				$data['is_owner'] = $permission->isOwner( $created_by_id, $object_user_id );
			}
		}

		if ( $include_columns == null || ( isset( $include_columns['is_child'] ) && $include_columns['is_child'] == true ) ) {
			//If is_child column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn( 'is_child' ) !== false ) {
				$data['is_child'] = (bool)$this->getColumn( 'is_child' );
			} else {
				if ( is_array( $permission_children_ids ) ) {
					//ObjectID should always be a user_id.
					$data['is_child'] = $permission->isChild( $object_user_id, $permission_children_ids );
				} else {
					$data['is_child'] = false;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $name
	 * @param string|int $parent
	 * @return array|bool
	 */
	function getOptions( $name, $parent = null ) {
		if ( $parent == null || $parent == '' ) {
			return $this->_getFactoryOptions( $name );
		} else if ( is_array( $parent ) ) {
			return $this->_getFactoryOptions( $name, $parent );
		} else {
			$retval = $this->_getFactoryOptions( $name, $parent );
			if ( isset( $retval[$parent] ) ) {
				return $retval[$parent];
			}
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param string|int $parent
	 * @return bool
	 */
	protected function _getFactoryOptions( $name, $parent = null ) {
		return false;
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function getVariableToFunctionMap( $data = null ) {
		return $this->_getVariableToFunctionMap( $data );
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	protected function _getVariableToFunctionMap( $data ) {
		return false;
	}

	/**
	 * @return int|bool
	 */
	function getAffectedRows() {
		return $this->db->Affected_Rows();
	}

	/**
	 * @return int|bool
	 */
	function getRecordCount() {
		if ( isset( $this->rs->_numOfRows ) ) { //Check a deep variable to make sure it is in fact a valid ADODB record set, just in case some other object is passed in.
			return $this->rs->RecordCount();
		}

		return false;
	}

	/**
	 * @param int $offset
	 * @return int|bool
	 */
	function getCurrentRow( $offset = 1 ) {
		if ( isset( $this->rs ) && isset( $this->rs->_currentRow ) ) {
			return ( $this->rs->_currentRow + (int)$offset );
		}

		return false;
	}

	/**
	 * @param null $milliseconds
	 * @return bool
	 */
	function setQueryStatementTimeout( $milliseconds = null ) {
		if ( $milliseconds == '' ) {
			$milliseconds = 0;
			if ( isset( $this->config['other']['query_statement_timeout'] ) ) {
				$milliseconds = (int)$this->config['other']['query_statement_timeout'];
			}
		}

		Debug::Text( 'Setting DB query statement timeout to: ' . $milliseconds, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getDatabaseType() == 'postgres' ) {
			$this->db->Execute( 'SET statement_timeout = ' . (int)$milliseconds );
		}

		return true;
	}

	/**
	 * @param object $rs
	 * @return array|bool
	 */
	private function getRecordSetColumnList( $rs ) {
		if ( is_object( $rs ) ) {
			for ( $i = 0, $max = $rs->FieldCount(); $i < $max; $i++ ) {
				$field = $rs->FetchField( $i );
				$fields[] = $field->name;
			}

			return $fields;
		}

		return false;
	}

	/**
	 * @param int|string $int
	 * @param string $type
	 * @return bool|int|string
	 */
	protected function castInteger( $int, $type = 'int' ) {
		//smallint	2 bytes	small-range integer	-32768 to +32767
		//integer	4 bytes	typical choice for integer	-2147483648 to +2147483647
		//bigint	8 bytes	large-range integer	-9223372036854775808 to 9223372036854775807
		switch ( $type ) {
			case 'smallint':
				if ( $int > 32767 || $int < -32768 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'numeric_string':
				//This is just numeric values, but not actualling cast to integers, ie: SSNs that start with leading 0s.
				//Since stripNonNumeric is already run on the input, just return the value untouched.
				$retval = $int;
				break;
			case 'numeric':
			case 'int':
				if ( $int > 2147483647 || $int < -2147483648 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'bigint':
				if ( $int > 9223372036854775807 || $int < -9223372036854775808 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'uuid':
				$retval = TTUUID::castUUID( $int );
				break;
			default:
				return $int; //Make sure if the $type is not recognized we just return the raw value again.
				break;
		}

		if ( $retval === false ) {
			Debug::Text( ' Integer outside range: ' . $int . ' Type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param array|string|int $array
	 * @param array $ph
	 * @param string|bool $cast
	 * @return bool|int|string|array
	 */
	protected function getListSQL( $array, &$ph = null, $cast = false ) {
		//Debug::Arr($array, 'List Values:', __FILE__, __LINE__, __METHOD__, 10);
		if ( $ph === null ) {
			if ( is_array( $array ) && count( $array ) > 0 ) {
				return '\'' . implode( '\', \'', $array ) . '\'';
			} else if ( is_array( $array ) ) {
				//Return NULL, because this is an empty array.
				return 'NULL';
			} else if ( $array == '' ) {
				return 'NULL';
			}

			//Just a single ID, return it.
			return $array;
		} else {
			//Debug::Arr($ph, 'Place Holder BEFORE:', __FILE__, __LINE__, __METHOD__, 10);

			//Append $array values to end of $ph, return
			//one "?, " for each element in $array.

			if ( is_array( $array ) && count( $array ) > 0 ) {
				foreach ( $array as $key => $val ) {
					$ph_arr[] = '?';

					//Make sure we filter out any FALSE or NULL values from going into a SQL list.
					//Replace them with "-1"'s so we keep the same number of place holders.
					//This should hopefully prevent SQL errors if a FALSE creeps into the SQL list array.
					//Check is_numeric/is_string before strtolower(), because if an array sneaks through it will cause a PHP warning.
					if ( is_null( $val ) === false && $val !== '' && ( is_numeric( $val ) || is_string( $val ) ) && strtolower( $val ) !== 'false' && strtolower( $val ) !== 'true' ) {
						$val = $this->castInteger( $val, $cast );
						if ( $val === false ) {
							$ph[] = -1;
						} else {
							$ph[] = $val;
						}
					} else {
						$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
					}
				}

				if ( isset( $ph_arr ) ) {
					$retval = implode( ',', $ph_arr );
				}
			} else if ( is_array( $array ) ) {
				//Return NULL, because this is an empty array.
				//This may have to return -1 instead of NULL
				//$ph[] = 'NULL';
				$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				$retval = '?';
			} else if ( $array === false || $array === '' ) { //Make sure we don't catch int(0) here.
				//$ph[] = 'NULL';
				//$ph[] = -1;
				$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				$retval = '?';
			} else {
				$array = $this->castInteger( $array, $cast );
				if ( $array === false ) {
					$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				} else {
					$ph[] = $array;
				}
				$retval = '?';
			}

			//Debug::Arr($ph, 'Place Holder AFTER: Cast: '. $cast, __FILE__, __LINE__, __METHOD__, 10);

			//Just a single ID, return it.
			return $retval;
		}
	}

	/**
	 * This function takes plain input from the user and creates a SQL statement for filtering based on a date range.
	 * Supported Syntax:
	 *       >=01-Jan-09
	 *       <=01-Jan-09
	 *       <01-Jan-09
	 *       >01-Jan-09
	 *       >01-Jan-09 & <10-Jan-09
	 * @param string $str
	 * @param string $column
	 * @param string $format
	 * @param bool $include_blank_dates
	 * @return bool|string
	 */
	function getDateRangeSQL( $str, $column, $format = 'epoch', $include_blank_dates = false ) {
		if ( $str == '' ) {
			return false;
		}

		if ( $column == '' ) {
			return false;
		}

		//Debug::text(' Format: '. $format .' String: '. $str .' Column: '. $column, __FILE__, __LINE__, __METHOD__, 10);

		$operators = [
				'>',
				'<',
				'>=',
				'<=',
				'=',
		];
		$operations = false;
		//Parse input, separate any subqueries first.
		$split_str = explode( '&', $str, 2 ); //Limit sub-queries
		if ( is_array( $split_str ) ) {
			foreach ( $split_str as $tmp_str ) {
				$tmp_str = trim( $tmp_str );
				$date = (int)TTDate::parseDateTime( str_replace( $operators, '', $tmp_str ) );
				//Debug::text(' Parsed Date: '. $tmp_str .' To: '. TTDate::getDate('DATE+TIME', $date) .' ('. $date .')', __FILE__, __LINE__, __METHOD__, 10);

				if ( $date != 0 && TTDate::isValidDate( $date ) == true ) {
					preg_match( '/^>=|>|<=|</i', $tmp_str, $operator );
					//Debug::Arr($operator, ' Operator: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $operator[0] ) && in_array( $operator[0], $operators ) ) {
						if ( TTDate::getHour( $date ) == 0 && TTDate::getMinute( $date ) == 0 ) { //If the date isn't midnight, its likely a timestamp has been specifically passed in, so don't modify it by using getEndOfDayEpoch()
							if ( $operator[0] == '<=' ) {
								$date = TTDate::getEndDayEpoch( $date );
							} else if ( $operator[0] == '>' ) {
								$date = TTDate::getEndDayEpoch( $date );
							}
						}

						if ( $format == 'timestamp' ) {
							$date = '\'' . $this->db->bindTimeStamp( $date ) . '\'';
						} else if ( $format == 'datestamp' ) {
							$date = '\'' . $this->db->bindDate( $date ) . '\'';
						}

						if ( $include_blank_dates == true ) {
							$operations[] = '(' . $column . ' ' . $operator[0] . ' ' . $date . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
						} else {
							$operations[] = $column . ' ' . $operator[0] . ' ' . $date;
						}
					} else {
						//FIXME: Need to handle date filters without any operators better.
						//for example JobListFactory and JobSummaryReport and the time period is specified.
						$date1 = TTDate::getBeginDayEpoch( $date );
						$date2 = TTDate::getEndDayEpoch( $date );
						if ( $format == 'timestamp' ) {
							$date1 = '\'' . $this->db->bindTimeStamp( $date1 ) . '\'';
							$date2 = '\'' . $this->db->bindTimeStamp( $date2 ) . '\'';
						} else if ( $format == 'datestamp' ) {
							$date1 = '\'' . $this->db->bindDate( $date1 ) . '\'';
							$date2 = '\'' . $this->db->bindDate( $date2 ) . '\'';
						}

						//Debug::text(' No operator specified... Using a 24hr period', __FILE__, __LINE__, __METHOD__, 10);
						if ( $include_blank_dates == true ) {
							if ( $format == 'epoch' ) {
								$operations[] = '(' . $column . ' >= ' . $date1 . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
								$operations[] = '(' . $column . ' <= ' . $date2 . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
							} else {
								//When $column is a date/timestamp datatype, can't use = 0 on it without causing SQL error.
								$operations[] = '(' . $column . ' >= ' . $date1 . ' OR ( ' . $column . ' is NULL ) )';
								$operations[] = '(' . $column . ' <= ' . $date2 . ' OR ( ' . $column . ' is NULL ) )';
							}
						} else {
							$operations[] = $column . ' >= ' . $date1;
							$operations[] = $column . ' <= ' . $date2;
						}
					}
				}
			}
		}

		//Debug::Arr($operations, ' Operations: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $operations ) ) {
			$retval = ' ( ' . implode( ' AND ', $operations ) . ' )';

			//Debug::text(' Query parts: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return false;
	}

	/**
	 * SQL where clause Syntax:
	 *   or % as wildcard.
	 *   "<query>" as exact match, no default wildcard and no metaphone
	 *
	 * Handles '*' and '%' as wildcards, defaults to wildcard on the end always.
	 * If no wildcard is to be added, the last character should be |
	 * @param string $arg
	 * @return string
	 */
	protected function handleSQLSyntax( $arg ) {
		$arg = str_replace( '*', '%', trim( $arg ) );

		//Make sure we don't add '%' if $arg is blank.
		if ( $arg != '' && strpos( $arg, '%' ) === false && ( strpos( $arg, '|' ) === false && strpos( $arg, '"' ) === false ) ) {
			$arg .= '%';
		}

		return addslashes( $this->stripSQLSyntax( $arg ) ); //Addaslashes to prevent SQL syntax error if %\ is at the end of the where clause.
	}

	/**
	 * @param string $arg
	 * @return mixed
	 */
	protected function stripSQLSyntax( $arg ) {
		return str_replace( [ '"' ], '', $arg ); //Strip syntax characters out.
	}

	/**
	 * @return string
	 */
	protected function getSQLToTimeStampFunction() {
		$to_timestamp_sql = 'to_timestamp';

		return $to_timestamp_sql;
	}

	/**
	 * @return string
	 */
	protected function getDatabaseType() {
		$database_driver = 'postgres';

		return $database_driver;
	}

	/**
	 * @param string $sql
	 * @return string
	 */
	protected function getSQLToEpochFunction( $sql ) {
		//In cases where the column is a timestamp without timezone column (ie: Pay Periods when used from PayPeriodTimeSheetVerify)
		//We need to case it to a timezone otherwise when adding/subtracting epoch seconds, it may be unexpectedly offset by the timezone amount.
		$to_timestamp_sql = 'EXTRACT( EPOCH FROM ' . $sql . '::timestamp with time zone )';

		return $to_timestamp_sql;
	}

	/**
	 * @param string $sql
	 * @return string
	 */
	protected function getSQLToTimeFunction( $sql ) {
		$to_time_sql = $sql . '::time';

		return $to_time_sql;
	}

	/**
	 * @param string $sql
	 * @param string $glue
	 * @return string
	 */
	protected function getSQLStringAggregate( $sql, $glue ) {
		//See Group.class.php aggegate() function with 'concat' argument, that is used in most reports instead.
		$agg_sql = 'array_to_string( array_agg( ' . $sql . ' ), \'' . $glue . '\')'; //Works with PGSQL 8.4+
		//$agg_sql = 'string_agg('. $sql .', \''. $glue .'\')'; //Works with PGSQL 9.1+

		return $agg_sql;
	}

	/**
	 * @param array|string $columns
	 * @param array|string $args
	 * @param string $type
	 * @param array $ph
	 * @param string $query_stub
	 * @param bool $and
	 * @return null|string
	 * @noinspection PhpParamsInspection
	 */
	protected function getWhereClauseSQL( $columns, $args, $type, &$ph, $query_stub = null, $and = true ) {
		//Debug::Text('Type: '. $type .' Query Stub: '. $query_stub .' AND: '. (int)$and, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($columns, 'Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($args, 'Args: ', __FILE__, __LINE__, __METHOD__, 10);
		switch ( strtolower( $type ) ) {
			case 'geo_overlaps':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' && polygon(' . $this->db->qstr( $args ) . ')'; //If we ever pass anything into here *not* from convertGEOPolygonToString(), this needs to changed to use placeholders instead to avoid SQL attacks.
					}
					$retval = $query_stub;
				}
				break;
			case 'geo_contains':
				//Args must always be two elements to make a point.
				if ( isset( $args ) && is_array( $args ) && count( $args ) == 2 && isset( $args[0] ) && isset( $args[1] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$ph[] = $args[0];
						$ph[] = $args[1];
					}

					if ( $query_stub == '' && !is_array( $columns ) ) {
						//$query_stub = 'circle('. $columns .') @> point( ? )'; //Sometimes polygons are passed into this, so we can't convert them to circles.
						$query_stub = $columns . ' @> point( ?, ? )';
					}
					$retval = $query_stub;
				}
				break;
			case 'full_text':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					$split_args = explode( ',', str_replace( [ ' ', ';' ], ',', $args ) ); //Support " " (space) and ";" and ", " as separators.
					if ( is_array( $split_args ) && count( $split_args ) > 0 && $query_stub == '' ) {
						foreach ( $split_args as $key => $arg ) {
							if ( trim( $arg ) != '' ) {
								$ph_arr[] = $this->stripSQLSyntax( TTi18n::strtolower( $arg ) );
							}
						}

						if ( $query_stub == '' && !is_array( $columns ) ) {
							$ph[] = implode( ' & ', $ph_arr );
						}
					}

					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' @@ to_tsquery( ? )';
					}
					$retval = $query_stub;
				}
				break;
			case 'string':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' = ?';
					}
					$ph[] = $this->handleSQLSyntax( $args );
					$retval = $query_stub;
				}
				break;
			case 'text':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$columns = [ $columns ];
					}

					if ( $query_stub == '' && is_array( $columns ) && count( $columns ) > 0 ) {
						foreach ( $columns as $column ) {
							$query_stub[] = 'lower(' . $column . ') LIKE ?';
							$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( $args ) );
						}

						$query_stub = implode( ' OR ', $query_stub );
					}

					$retval = $query_stub;
				}
				break;
			case 'text_metaphone':
				//See also: Option::getByFuzzyValue -- As it tries to replicate this.
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = '( lower(' . $columns . ') LIKE ? OR ' . $columns . '_metaphone LIKE ? )';
					}

					$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( $args ) );
					if ( strpos( $args, '"' ) !== false ) { //ignores metaphone search.
						$ph[] = '';
					} else {
						$ph[] = $this->handleSQLSyntax( metaphone( Misc::stripThe( $args ) ) ); //Strip "The " from metaphones so its easier to find company names.
					}
					$retval = $query_stub;
				}
				break;
			case 'uuid':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' && TTUUID::isUUID( $args ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' = ?';
					}
					$ph[] = TTUUID::castUUID( $args );
					$retval = $query_stub;
				}
				break;
			case 'uuid_list':
			case 'not_uuid_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( TTUUID::getNotExistID(), $args, true ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) { //Check for -1 as well for backwards compatibily with INT ID lists.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						if ( strtolower( $type ) == 'not_uuid_list' ) {
							$query_stub = $columns . ' NOT IN (?)';
						} else {
							$query_stub = $columns . ' IN (?)';
						}
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, 'uuid' ), $query_stub );
				}
				break;
			case 'uuid_list_with_all': //Doesn't check for -1 and ignore the filter completely. Used in KPIListFactory.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}
				if ( isset( $args ) && isset( $args[0] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', 'uuid_list' ) ), $query_stub );
				}
				break;
			case 'text_list':
			case 'lower_text_list':
			case 'upper_text_list':
				if ( !is_array( $args ) ) {
					$args = [ (string)$args ];
				}

				$sql_text_case_function = null;
				if ( $type == 'upper_text_list' || $type == 'lower_text_list' ) {
					if ( $type == 'upper_text_list' ) {
						$sql_text_case_function = 'UPPER';
						$text_case = CASE_UPPER;
					} else {
						$sql_text_case_function = 'LOWER';
						$text_case = CASE_LOWER;
					}
					$args = array_flip( array_change_key_case( array_flip( $args ), $text_case ) );
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) && !in_array( strtoupper( TTUUID::getNotExistID() ), $args, true ) && !in_array( TTUUID::getNotExistID(), $args, true ) && !in_array( '00', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $sql_text_case_function . '(' . $columns . ') IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, 'string' ), $query_stub );
				}

				break;
			case 'province':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) && !in_array( '00', $args ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph ), $query_stub );
				}
				break;
			case 'phone':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$columns = [ $columns ];
					}

					if ( $query_stub == '' && is_array( $columns ) && count( $columns ) > 0 ) {
						foreach ( $columns as $column ) {
							$query_stub[] = "( replace( replace( replace( replace( replace( replace( " . $column . ", ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') LIKE ? OR " . $column . " LIKE ? )";
							$ph[] = $ph[] = $this->handleSQLSyntax( preg_replace( '/[^0-9\%\*\"]/', '', strtolower( $args ) ) ); //Need the same value twice for the query stub.
						}

						$query_stub = implode( ' OR ', $query_stub );
					}

					$retval = $query_stub;
				}
				break;
			case 'smallint':
			case 'int':
			case 'bigint':
			case 'numeric':
			case 'numeric_string':
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						if ( $args === null ) {
							$query_stub = $columns . ' is NULL';
						} else {
							$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), $type );
							if ( is_numeric( $args ) ) {
								$ph[] = $args;
								$query_stub = $columns . ' = ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'smallint_list':
			case 'int_list':
			case 'bigint_list':
			case 'numeric_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', $type ) ), $query_stub );
				}
				break;
			case 'numeric_list_with_all': //Doesn't check for -1 and ignore the filter completely. Used in KPIListFactory.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}
				if ( isset( $args ) && isset( $args[0] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', 'numeric_list' ) ), $query_stub );
				}
				break;
			case 'not_smallint_list':
			case 'not_int_list':
			case 'not_bigint_list':
			case 'not_numeric_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' NOT IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( [ 'not_', '_list' ], '', $type ) ), $query_stub );
				}
				break;
			case 'tag':
				//We need company_id and object_type_id passed in.
				if ( isset( $args['company_id'] ) && isset( $args['object_type_id'] ) && isset( $args['tag'] ) ) {
					//Parse the tags search syntax to determine ANY, AND, OR searches.
					$parsed_tags = CompanyGenericTagFactory::parseTags( $args['tag'] );
					//Debug::Arr($parsed_tags, 'Parsed Tags: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( is_array( $parsed_tags ) ) {
						$retval = '';
						if ( isset( $parsed_tags['add'] ) && count( $parsed_tags['add'] ) > 0 ) {
							$query_stub = ' EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = \'' . TTUUID::castUUID( $args['company_id'] ) . '\'
															AND cgtm.object_type_id = ' . (int)$args['object_type_id'] . '
															AND ' . $columns . ' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = ' . count( $parsed_tags['add'] ) . '
													)';
							$retval .= str_replace( '?', $this->getListSQL( Misc::arrayChangeValueCase( $parsed_tags['add'] ), $ph ), $query_stub );
							if ( isset( $parsed_tags['delete'] ) && count( $parsed_tags['delete'] ) > 0 ) {
								$retval .= ' AND ';
							}
						}

						if ( isset( $parsed_tags['delete'] ) && count( $parsed_tags['delete'] ) > 0 ) {
							$query_stub = ' NOT EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = \'' . TTUUID::castUUID( $args['company_id'] ) . '\'
															AND cgtm.object_type_id = ' . (int)$args['object_type_id'] . '
															AND ' . $columns . ' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = ' . count( $parsed_tags['delete'] ) . '
													)';
							$retval .= str_replace( '?', $this->getListSQL( Misc::arrayChangeValueCase( $parsed_tags['delete'] ), $ph ), $query_stub );
						}
					}
				}
				if ( !isset( $retval ) ) {
					$retval = '';
				}
				break;
			case 'date_stamp': //Input epoch values, but convert bind to datestamp for datastamp datatypes.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					foreach ( $args as $tmp_arg ) {
						if ( TTDate::isValidDate( $tmp_arg ) ) {
							$converted_args[] = $this->db->bindDate( (int)$tmp_arg );
						}
					}

					if ( isset( $converted_args ) && count( $converted_args ) > 0 ) {
						if ( $query_stub == '' && !is_array( $columns ) ) {
							$query_stub = $columns . ' IN (?)';
						}
						$retval = str_replace( '?', $this->getListSQL( $converted_args, $ph ), $query_stub );
					}
				}
				break;
			case 'start_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $this->db->bindDate( $args );
							if ( strtolower( $type ) == 'start_datestamp' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $this->db->bindTimeStamp( $args );
							if ( strtolower( $type ) == 'start_timestamp' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_date': //Uses EPOCH values only, used for integer datatype columns
			case 'end_date':
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $args;
							if ( strtolower( $type ) == 'start_date' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'date_range': //Uses EPOCH values only, used for integer datatype columns
			case 'date_range_include_blank': //Include NULL/Blank dates.
			case 'date_range_datestamp':
			case 'date_range_datestamp_include_blank': //Include NULL/Blank dates.
			case 'date_range_timestamp': //Uses text timestamp values, used for timestamp datatype columns
			case 'date_range_timestamp_include_blank': //Include NULL/Blank dates.
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$include_blank_dates = ( strpos( $type, '_include_blank' ) !== false ) ? true : false;
						switch ( $type ) {
							case 'date_range_timestamp':
							case 'date_range_timestamp_include_blank':
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'timestamp', $include_blank_dates );
								break;
							case 'date_range_datestamp':
							case 'date_range_datestamp_include_blank':
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'datestamp', $include_blank_dates );
								break;
							default:
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'epoch', $include_blank_dates );
								break;
						}
					}
					//Debug::Text('Query Stub: '. $query_stub, __FILE__, __LINE__, __METHOD__, 10);
					$retval = $query_stub;
				}
				break;
			case 'user_id_or_name':
				if ( isset( $args ) && is_array( $args ) ) {
					$retval = $this->getWhereClauseSQL( $columns[0], $args, 'uuid_list', $ph, '', false );
				}
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					$ph[] = $ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $args ) ) );
					$retval = '(lower(' . $columns[1] . ') LIKE ? OR lower(' . $columns[2] . ') LIKE ? ) ';
				}
				break;
			case 'boolean':
				if ( is_bool( $args ) ) { //Handle strict boolean types here, convert to strings to be matched later on.
					if ( $args === true ) {
						$args = 'true';
					} else {
						$args = 'false';
					}
				} else if ( is_int( $args ) ) { //Handle strict integer types here, convert to strings to be matched later on.
					if ( $args === 1 ) {
						$args = 'true';
					} else {
						$args = 'false';
					}
				}

				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) { // trim($args) != '' won't match (bool)FALSE. So it must be changed to a string above.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						switch ( strtolower( trim( (string)$args ) ) ) { //Cast to string here is critical for the below CASE's to work properly.
							//Can't check for (int)1 or (bool)TRUE here as it matches even with (bool)FALSE. DocumentList passes (bool)FALSE for handling private documents.
							case '1':
							case 'yes':
							case 'y':
							case 'true':
							case 't':
							case 'on':
								$ph[] = 1;
								$query_stub = $columns . ' = ?';
								break;
							case '0':
							case 'no':
							case 'n':
							case 'false':
							case 'f':
							case 'off':
								$ph[] = 0;
								$query_stub = $columns . ' = ?';
								break;
							default:
								Debug::Text( 'Invalid boolean value: ' . $args, __FILE__, __LINE__, __METHOD__, 10 );
								break;
						}
					}
					$retval = $query_stub;
				}
				break;
			default:
				Debug::Text( 'Invalid type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
				break;
		}

		if ( isset( $retval ) ) {
			$and_sql = null;
			if ( $and == true && $retval != '' ) { //Don't prepend ' AND' if there is nothing to come after it.
				$and_sql = 'AND ';
			}

			//Debug::Arr($ph, 'Query Stub: '. $and_sql.$retval, __FILE__, __LINE__, __METHOD__, 10);
			return ' ' . $and_sql . $retval . ' '; //Wrap each query stub in spaces.
		}

		return null;
	}

	/**
	 * Parses out the exact column name, without any aliases, or = signs in it.
	 * @param string $column
	 * @return bool|string
	 */
	private function parseColumnName( $column ) {
		$column = trim( $column );

		//Make sure there isn't a SQL injection attack here, but still allow things like: "order by a.column = 1 asc"
		//  Example attack vectors:
		// 		'(SELECT 1)-- .id.' => 1
		//  This may cause problems if we want to use a function in sorting though.
		if ( preg_match( '/^([a-z0-9_\=\.\ ]+)$/i', $column ) !== 1 ) {
			if ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) {
				trigger_error( 'ERROR: Invalid column name: ' . $column ); //Trigger error so we can get feedback of any problems or potential attacks.
			} else {
				Debug::Text( 'ERROR: Invalid column name: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return false;
		}

		if ( strstr( $column, '=' ) ) {
			$tmp_column = explode( '=', $column );
			$retval = trim( $tmp_column[0] );
			unset( $tmp_column );
		} else {
			$retval = $column;
		}

		if ( strstr( $retval, '.' ) ) {
			$tmp_column = explode( '.', $retval );
			$retval = $tmp_column[1];
			unset( $tmp_column );
		}

		//Debug::Text('Column: '. $column .' RetVal: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param array $array
	 * @param bool $append_where
	 * @return bool|string
	 */
	protected function getWhereSQL( $array, $append_where = false ) {
		//Make this a multi-dimensional array, the first entry
		//is the WHERE clauses with '?' for placeholders, the second is
		//the array to replace the placeholders with.
		if ( is_array( $array ) ) {
			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList( $rs );

			if ( is_array( $fields ) ) {
				foreach ( $array as $orig_column => $expression ) {
					if ( is_array( $expression ) ) { //Handle nested arrays, so we the same column can be specified multiple times.
						foreach ( $expression as $tmp_orig_column => $tmp_expression ) {
							$tmp_orig_column = trim( $tmp_orig_column );
							$column = $this->parseColumnName( $tmp_orig_column );
							$tmp_expression = trim( $tmp_expression );

							if ( in_array( $column, $fields ) ) {
								$sql_chunks[] = $tmp_orig_column . ' ' . $tmp_expression;
							}
						}
					} else {
						$orig_column = trim( $orig_column );
						$column = $this->parseColumnName( $orig_column );
						$expression = trim( $expression );

						if ( in_array( $column, $fields ) ) {
							$sql_chunks[] = $orig_column . ' ' . $expression;
						}
					}
				}
			}

			if ( isset( $sql_chunks ) ) {
				//Don't escape this, as prevents quotes from being used in cases where they are required link bindTimeStamp
				//$sql = $this->db->escape( implode(' AND ', $sql_chunks) );
				$sql = implode( ' AND ', $sql_chunks );

				if ( $append_where == true ) {
					return ' WHERE ' . $sql;
				} else {
					return ' AND ' . $sql;
				}
			}
		}

		return false;
	}

	/**
	 * @param array $columns
	 * @param array $aliases
	 * @return array
	 */
	protected function getColumnsFromAliases( $columns, $aliases ) {
		// Columns is the original column array.
		//
		// Aliases is an array of search => replace key/value pairs.
		//
		// This is used so the frontend can sort by the column name (ie: type) and it can be converted to type_id for the SQL query.
		if ( is_array( $columns ) && is_array( $aliases ) ) {
			$columns = $this->convertFlexArray( $columns );

			//Debug::Arr($columns, 'Columns before: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach ( $columns as $column => $sort_order ) {
				if ( isset( $aliases[$column] ) && !isset( $columns[$aliases[$column]] ) ) {
					if ( $aliases[$column] != '' ) { //If the alias column is set to null/false, just ignore that sort column completely.
						$retarr[$aliases[$column]] = $sort_order;
					}
				} else {
					$retarr[$column] = $sort_order;
				}
			}
			//Debug::Arr($retarr, 'Columns after: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		}

		return $columns;
	}

	/**
	 * @param array $array
	 * @return array
	 */
	function convertFlexArray( $array ) {
		//NOTE: This needs to stick around to handle saved search & layouts created in Flex and still in use.
		//Flex doesn't appear to be consistent on the order the fields are placed into an assoc array, so
		//handle this type of array too:
		// array(
		//		0 => array('first_name' => 'asc')
		//		1 => array('last_name' => 'desc')
		//		)

		if ( isset( $array[0] ) && is_array( $array[0] ) ) {
			Debug::text( 'Found Flex Sort Array, converting to proper format...', __FILE__, __LINE__, __METHOD__, 10 );

			//Debug::Arr($array, 'Before conversion...', __FILE__, __LINE__, __METHOD__, 10);

			$new_arr = [];
			foreach ( $array as $tmp_order => $tmp_arr ) {
				if ( is_array( $tmp_arr ) ) {
					foreach ( $tmp_arr as $tmp_column => $tmp_order ) {
						$new_arr[$tmp_column] = $tmp_order;
					}
				}
			}
			$array = $new_arr;
			unset( $tmp_key, $tmp_arr, $tmp_order, $tmp_column, $new_arr );
			//Debug::Arr($array, 'Converted format...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $array;
	}

	/**
	 * @param array $array
	 * @param bool $strict
	 * @param array $additional_fields
	 * @return array
	 * @throws Exception
	 */
	public function getValidSQLColumns( $array, $strict = true, $additional_fields = null ) {
		$retarr = [];

		$fields = $this->getRecordSetColumnList( $this->getEmptyRecordSet() );

		//Merge additional fields
		if ( is_array( $fields ) && is_array( $additional_fields ) ) {
			$fields = array_merge( $fields, $additional_fields );
		}
		//Debug::Arr($fields, 'Column List:', __FILE__, __LINE__, __METHOD__, 10);

		foreach ( $array as $orig_column => $expression ) {
			$orig_column = trim( $orig_column );

			if ( $strict == false ) {
				$retarr[$orig_column] = $expression;
			} else {
				if ( in_array( $orig_column, $fields ) ) {
					$retarr[$orig_column] = $expression;
				} else {
					$column = $this->parseColumnName( $orig_column );
					if ( in_array( $column, $fields ) ) {
						$retarr[$orig_column] = $expression;
					} else {
						Debug::text( 'Invalid Column: ' . $orig_column, __FILE__, __LINE__, __METHOD__, 10 );
						if ( defined( 'UNIT_TEST_MODE' ) && UNIT_TEST_MODE === true ) {
							throw new Exception( 'Invalid column: ' . $orig_column );
						}
					}
				}
			}
		}

		//Debug::Arr($retarr, 'Valid Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retarr;
	}

	/**
	 * @param array $array
	 * @param bool $strict
	 * @param array $additional_fields
	 * @return bool|string
	 */
	protected function getSortSQL( $array, $strict = true, $additional_fields = null ) {
		if ( is_array( $array ) ) {
			$sql_reserved_words = [ 'group' ];

			//Disabled in v10 to start migrating away from FlexArray formats.
			//  This is still needed, as clicking on a column header to sort by that seems to use the wrong format.
			$array = $this->convertFlexArray( $array );

			$alt_order_options = [ 1 => 'ASC', -1 => 'DESC' ];
			$order_options = [ 'ASC', 'DESC' ];

			$valid_columns = $this->getValidSQLColumns( $array, $strict, $additional_fields );
			if ( is_array( $valid_columns ) ) {
				foreach ( $valid_columns as $orig_column => $order ) {
					$order = trim( strtoupper( $order ) );
					//Handle both order types.
					if ( is_numeric( $order ) ) {
						if ( isset( $alt_order_options[$order] ) ) {
							$order = $alt_order_options[$order];
						}
					}

					if ( $strict == false || in_array( $order, $order_options ) ) {
						if ( in_array( $orig_column, $sql_reserved_words ) ) { //Quote reserved words such as 'group'.
							$orig_column = '"' . $orig_column . '"';
						}

						$sql_chunks[] = $orig_column . ' ' . $order;
					} else {
						Debug::text( 'Invalid Sort Order: ' . $orig_column . ' Order: ' . $order, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}

			if ( isset( $sql_chunks ) ) {
				$sql = implode( ',', $sql_chunks );
				//We can't escape the quotes needed to order by specific UUID's such as UUID_ZERO...
				//For example: ScheduleListFactory::getSearchByCompanyIdAndArrayCriteria()
				if ( $strict === false ) {
					return ' ORDER BY ' . $sql;
				} else {
					return ' ORDER BY ' . $this->db->escape( $sql );
				}
			}
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	public function getColumnList() {
		if ( is_array( $this->data ) && count( $this->data ) > 0 ) {
			//Possible errors can happen if $this->data[<invalid_column>] is passed to save/update the database,
			//like what happens with APIPunch when attempting to delete a punch.

			//Remove all columns that are not directly part of the table itself, or those mapped not mapped to a function in the object.
			$variable_to_function_map = $this->getVariableToFunctionMap();
			if ( is_array( $variable_to_function_map ) ) {
				foreach ( $variable_to_function_map as $variable => $function ) {
					if ( $function !== false ) {
						$valid_column_list[] = $variable;
					}
				}
				$column_list = array_intersect( $valid_column_list, array_keys( $this->data ) );
			} else {
				$column_list = array_keys( $this->data );
			}
			unset( $variable_to_function_map, $variable, $function );

			//Don't set updated_date when deleting records, we use deleted_date/deleted_by for that.
			if ( $this->getDeleted() == false && $this->getUpdatedDate() !== false ) {
				$column_list[] = 'updated_date';
			}
			if ( $this->getDeleted() == false && $this->getUpdatedBy() !== false ) {
				$column_list[] = 'updated_by';
			}
			//Make sure if the record is deleted we update the deleted columns.
			if ( $this->getDeleted() == true && $this->getDeletedDate() !== false && $this->getDeletedBy() !== false ) {
				$column_list[] = 'deleted_date';
				$column_list[] = 'deleted_by';
			}

			$column_list = array_unique( $column_list );

			//Debug::Arr($this->data, 'aColumn List', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($column_list, 'bColumn List', __FILE__, __LINE__, __METHOD__, 10);

			return $column_list;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return mixed
	 * @throws DBError
	 */
	public function getEmptyRecordSet( $id = null ) {
		global $profiler, $config_vars;
		$profiler->startTimer( 'getEmptyRecordSet()' );

		if ( $id == null ) {
			$where_clause = 'FALSE'; //Was: TTUUID::getNotExistID(); //Was $id = -1 -- This helps avoid failures in serializable mode as no data is actually selected.
		} else {
			$where_clause = 'id = \'' . TTUUID::castUUID( $id ) . '\'';
		}

		//Possible errors can happen if $this->data[<invalid_column>] is passed, like what happens with APIPunch when attempting to delete a punch.
		//Why are we not using '*' for all empty record set queries? Will using * cause more fields to be updated then necessary?
		//Yes, it will, as well the updated_by/updated_date fields aren't controllable by getColumnList() then either.
		//Therefore any ListFactory queries used to potentially delete data should only include columns from its own table,
		//Or collect the IDs and use bulkDelete instead.
		//**getColumnList() now only returns valid table columns based on the variable to function map.
		$column_list = $this->getColumnList();

		//ignore_column_list can be set in InstallSchema files to prevent column names from being used which may cause SQL errors during upgrade process.
		if ( is_array( $column_list ) && !isset( $this->ignore_column_list ) ) {
			//Implode columns.
			$column_str = implode( ',', $column_list );
		} else {
			$column_str = '*'; //Get empty RS with all columns.
		}

		$query = 'SELECT ' . $column_str . ' FROM ' . $this->table . ' WHERE ' . $where_clause;
		if ( $id == null && isset( $config_vars['cache']['enable'] ) && $config_vars['cache']['enable'] == true ) {
			//When caching empty record sets, always write to persistent cache as it doesn't matter if we are inside a retry transaction for this or not, this data will always be the same.
			$current_cache_memory_state = $this->cache->_onlyMemoryCaching;
			if ( $current_cache_memory_state == false && ( !isset( $config_vars['cache']['only_memory_cache_enable'] ) || $config_vars['cache']['only_memory_cache_enable'] == false ) ) {
				$this->cache->_onlyMemoryCaching = false;
			}

			//Try to use Cache Lite instead of ADODB, to avoid cache write errors from causing a transaction rollback, especially important for serializable transactions. It should be faster too.
			$cache_id = 'empty_rs_' . $this->table; //No need to add $id to the end as its always NULL here, but we may need to handle different columns that may be passed in with a md5() perhaps?
			$rs = $this->getCache( $cache_id );
			if ( $rs === false ) {
				$rs = $this->ExecuteSQL( $query );
				$rs = $this->db->_rs2rs( $rs );                           //Needed to include the _fieldObjects property for ADODB.
				$this->saveCache( $this->serializeRS( $rs ), $cache_id ); //Only run serializeRS() when passing to saveCache() otherwise it corrupts the $rs being returned in this function.
			}

			$this->cache->_onlyMemoryCaching = $current_cache_memory_state;

//			try {
//				$save_error_handlers = $this->db->IgnoreErrors(); //Prevent a cache write error from causing a transaction rollback.
//				$rs = $this->db->CacheExecute(604800, $query);
//				$this->db->IgnoreErrors( $save_error_handlers ); //Prevent a cache write error from causing a transaction rollback.
//			} catch ( Exception $e ) {
//				if ( $e->getCode() == -32000 OR $e->getCode() == -32001 ) { //Cache write error/cache file lock error.
//					//Likely a cache write error occurred, fall back to non-cached query and log this error.
//					Debug::Text('ERROR: Unable to write cache file, likely due to permissions or locking! Code: '. $e->getCode() .' Msg: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
//				}
//
//				//Execute non-cached query
//				$rs = $this->ExecuteSQL( $query );
//			}
		} else {
			$rs = $this->ExecuteSQL( $query );
		}

		$profiler->stopTimer( 'getEmptyRecordSet()' );

		return $rs;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function getUpdateQuery() {
		//Debug::text('Update', __FILE__, __LINE__, __METHOD__, 9);

		//
		// If the table has timestamp columns without timezone set
		// this function will think the data has changed, and update it.
		// PayStubFactory() had this issue.
		//

		//Debug::arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);

		//Add new columns to record set.
		//Check to make sure the columns exist in the table first though
		//Classes like station don't have updated_date, so we need to take that in to account.
		$rs = $this->getEmptyRecordSet( $this->getId() );
		//Set old_data in FactoryListIterator->getCurrent() instead, that way getDataDfifferences() can be used in Validate/preSave functions as well.
		//$this->old_data = $rs->fields; //Store old data in memory for detailed audit log.

		if ( !$rs ) {
			Debug::text( 'No Record Found! (ID: ' . $this->getID() . ') Insert instead?', __FILE__, __LINE__, __METHOD__, 9 );
			//Throw exception?
		}

		//Debug::Arr($rs->fields, 'RecordSet: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr($this->data, 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr( array_diff_assoc($rs->fields, $this->data), 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);

		//If no columns changed, this will be FALSE.
		$query = $this->db->GetUpdateSQL( $rs, $this->data );

		//No updates are fine. We still want to run postsave() etc...
		if ( $query === false ) {
			$query = true;
		} else {
			Debug::text( 'Data changed, set updated date: ', __FILE__, __LINE__, __METHOD__, 9 );
		}

		//Debug::text('Update Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	/**
	 * @return mixed
	 */
	private function getInsertQuery() {
		Debug::text( 'Insert', __FILE__, __LINE__, __METHOD__, 9 );

		//Debug::Arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);\

		//This prevents SQL errors (ie: NULL columns when they shouldn't be) caused by only certain columns being cached in the empty record set, and therefore being ignored in the INSERT query.
		$this->ignore_column_list = true;
		$rs = $this->getEmptyRecordSet();
		$this->ignore_column_list = false;

		if ( !$rs ) {
			Debug::text( 'ERROR: Unable to get empty record set for insert!', __FILE__, __LINE__, __METHOD__, 9 );
			//Throw exception?
		}

		$query = $this->db->GetInsertSQL( $rs, $this->data );

		$query = $this->modifyInsertQuery( $query );
		//Debug::text('Insert Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	/**
	 * Modifies the automatically generated SQL INSERT query for adding things like " ON CONFLICT ..."
	 * @param $query
	 * @return mixed
	 */
	function modifyInsertQuery( $query ) {
		return $query;
	}

	/**
	 * @return mixed
	 */
	function StartTransaction() {
		Debug::text( 'StartTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );

		return $this->db->StartTrans();
	}

	/**
	 * @return mixed
	 */
	function FailTransaction() {
		Debug::text( 'FailTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );

		return $this->db->FailTrans();
	}

	/**
	 * @param bool $unnest_transactions
	 * @return mixed
	 * @throws DBError
	 */
	function CommitTransaction( $unnest_transactions = false ) {
		if ( $this->db->transOff == 1 ) {
			Debug::text( 'CommitTransaction(): Final Commit... Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );
		} else {
			if ( $this->db->transCnt == 0 ) {
				Debug::text( 'CommitTransaction(): ERROR: Double Commit... Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );
			} else {
				Debug::text( 'CommitTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
			}
		}

		try {
			if ( $unnest_transactions == true && $this->db->_transOK == 0 ) { //Only unnest if the transaction has failed.
				Debug::text( 'CommitTransaction(): Unnesting transactions... Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
				do {
					$retval = $this->db->CompleteTrans();
				} while ( $this->db->transCnt > 0 );
				Debug::text( 'CommitTransaction(): Done unnesting transactions... Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
			} else {
				//throw new Exception( 'could not serialize access due to concurrent' ); //Use only for testing transaction retries on commit failures.
				$retval = $this->db->CompleteTrans();
			}
		} catch ( Exception $e ) {
			//SQL serialization failures can occur on commit, so make sure we catch those and can trigger a retry.
			// This is done in Factory->ExecuteSQL() and Factory->CommitTransaction() too.
			if ( $this->isSQLExceptionRetryable( $e ) == true ) {
				Debug::Text( 'WARNING: Rethrowing Serialization Exception from commit so it can be caught in an outside TRY block...', __FILE__, __LINE__, __METHOD__, 10 );
				//Fail transaction, so it can automatically be restarted in the outter retry loop.
				$this->FailTransaction(); //Don't call Commit after, as that complicates transaction nesting later on.
				throw $e;
			} else {
				throw new DBError( $e );
			}
		}

		if ( $retval == false ) { //Check to see if the transaction has failed.
			//In PostgreSQL, when SESSION/LOCAL variables are set within a transaction that later rollsback, the session variables also rollback. This ensures the timezone still matches what we think it should.
			TTDate::setTimeZone( TTDate::getTimeZone(), true );
		}

		return $retval;
	}

	/**
	 * @param string $mode
	 * @return mixed
	 */
	function setTransactionMode( $mode = '' ) {
		Debug::text( 'setTransactionMode(): Mode: ' . $mode . ' Transaction Count: ' . $this->db->transCnt, __FILE__, __LINE__, __METHOD__, 9 );

		if ( $mode != '' && $this->db->transCnt > 0 ) {
			Debug::text( 'setTransactionMode(): WARNING: Nested transaction, unlikely to be able to set transaction mode.', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return $this->db->setTransactionMode( $mode );
	}

	/**
	 * @param bool $force
	 * @return string
	 */
	function getTransactionMode( $force = false ) {
		if ( $force == true ) {
			$mode = $this->db->GetOne( 'select current_setting(\'transaction_isolation\')' );
		} else {
			if ( isset( $this->db->_transmode ) ) {
				$mode = $this->db->_transmode;
			} else {
				$mode = 'DEFAULT';
			}
		}

		Debug::text( 'getTransactionMode(): Mode: ' . $mode . ' Force: ' . (bool)$force, __FILE__, __LINE__, __METHOD__, 9 );

		return strtoupper( $mode );
	}

	/**
	 * Call class specific validation function just before saving.
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function isValid( $ignore_warning = true ) {
		if ( $this->is_valid == false ) {
			//Most preSave()'s should actually be preValidates, so they are always run prior to validation.
			//  This will only get called if the data is not valid.
			if ( method_exists( $this, 'preValidate' ) ) {
				Debug::text( 'Calling preValidate()', __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->preValidate() === false ) {
					throw new GeneralError( 'preValidate() failed.' );
				}
			}

			if ( method_exists( $this, 'Validate' ) ) {
				Debug::text( 'Calling Validate()', __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->Validate( $ignore_warning ) == true ) {
					$this->is_valid = true; //Set flag so we don't revalidate all data unless it has changed.
				}
			}
		} else {
			Debug::text( 'Data has already been validated...', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return $this->Validator->isValid();
	}

	/**
	 * Call class specific validation function just before saving.
	 * @return bool
	 */
	function isWarning() {
		if ( method_exists( $this, 'validateWarning' ) ) {
			Debug::text( 'Calling validateWarning()', __FILE__, __LINE__, __METHOD__, 10 );
			$this->validateWarning();
		}

		return $this->Validator->isWarning();
	}

	/**
	 * @return bool
	 */
	function getSequenceName() {
		if ( isset( $this->pk_sequence_name ) ) {
			return $this->pk_sequence_name;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getNextInsertId() {
		global $PRIMARY_KEY_IS_UUID;

		if ( $PRIMARY_KEY_IS_UUID == false ) {
			if ( isset( $this->pk_sequence_name ) ) {
				return $this->db->GenID( $this->pk_sequence_name );
			}

			return false;
		} else {
			return TTUUID::generateUUID();
		}
	}

	/**
	 * Execute SQL queries and handle paging properly for select statements.
	 * @param string $query
	 * @param array $ph
	 * @param int $limit Limit the number of records returned
	 * @param int $page  Page number of records to return for pagination
	 * @return bool
	 * @throws DBError
	 * @throws Exception
	 */
	function ExecuteSQL( $query, $ph = null, $limit = null, $page = null ) {
		try {
			if ( $ph === null ) { //Work around ADODB change that requires $ph === FALSE, otherwise it changes it to a array( 0 => NULL ) and causes SQL errors.
				$ph = false;
			}

			//$start_time = microtime(TRUE);
			if ( $limit == null ) {
				$rs = $this->db->Execute( $query, $ph );
			} else {
				$rs = $this->db->PageExecute( $query, (int)$limit, (int)$page, $ph );
			}
			//$total_time = (microtime(TRUE)-$start_time);
			//Debug::text('Slow Query Executed in: '. $total_time .'ms. Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

			//throw new Exception( 'could not serialize access due to concurrent' ); //Use only for testing transaction retries on SQL failures.
		} catch ( Exception $e ) {
			if ( $this->isSQLExceptionRetryable( $e ) == true ) { // This is done in Factory->ExecuteSQL() and Factory->CommitTransaction() too.
				Debug::Text( 'WARNING: Rethrowing Serialization Exception so it can be caught in an outside TRY block...', __FILE__, __LINE__, __METHOD__, 10 );
				//Fail transaction, so it can automatically be restarted in the outter retry loop.
				$this->FailTransaction();                         //Don't call Commit after, as that complicates transaction nesting later on.
				throw $e;
			} else {
				throw new DBError( $e );
			}
		}

		return $rs;
	}

	/**
	 * Determines if a SQL exception is one that can be retried or not.
	 * @param $e Exception
	 * @return bool
	 */
	function isSQLExceptionRetryable( $e ) {
		if ( $e instanceof Exception && $e->getMessage() != ''
				&& ( stristr( $e->getMessage(), 'could not serialize' ) !== false
						|| stristr( $e->getMessage(), 'deadlock' ) !== false
						|| stristr( $e->getMessage(), 'lock timeout' ) !== false
						|| stristr( $e->getMessage(), 'current transaction is aborted' ) !== false ) //There seems to be cases where the "could not serialize" error is not picked up by PHP and therefore not triggered, so on the next query we get this error instead.
		) {
			Debug::text( 'Retryable SQL Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		Debug::text( 'Non-Retryable SQL Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * Accepts a Closure and is retried at set intervals which should be in a transaction.
	 * @param $transaction_function Closure
	 * @param int $retry_max_attempts
	 * @param int $retry_sleep      in seconds
	 * @return mixed
	 * @throws DBError
	 */
	function RetryTransaction( $transaction_function, $retry_max_attempts = 4, $retry_sleep = 1 ) { //When changing function definition, also see APIFactory->RetryTransaction()
		// Help mitigate function injection attacks due to the variable function call below $transaction_function();
		if ( !$transaction_function instanceof Closure ) {
			Debug::text( 'ERROR: Retry function is not a closure, unable to execute!', __FILE__, __LINE__, __METHOD__, 10 );
			return null;
		}

		$is_nested_retry_transaction = false;

		if ( $this->db->transCnt > 0 ) {
			//This can happen during import validation, because we need to wrap everything in a transaction that will always be rolled back.
			Debug::text( 'WARNING: RetryTransaction called from within a transaction, as the entire transaction cant be rolled back max retry attempts will be 1. Trans Cnt: ' . $this->db->transCnt, __FILE__, __LINE__, __METHOD__, 10 );
			//throw new Exception('ERROR: RetryTransaction cannot be called from within a transaction, as the entire transaction cant be rolled back then...');
			$retry_max_attempts = 1;
			$is_nested_retry_transaction = true;
		}

		if ( $retry_max_attempts < 1 ) { //Make sure max attempts is set to at least 1.
			$retry_max_attempts = 1;
		}

		//$current_cache_state = $this->cache->_caching;
		$current_cache_memory_state = $this->cache->_onlyMemoryCaching;

		$tmp_sleep = ( $retry_sleep * 1000000 );
		$retry_attempts = 0;
		while ( $retry_attempts < $retry_max_attempts ) {
			try {
				//In PostgreSQL, may need to increase "max_pred_locks_per_transaction" setting to avoid transactions waiting on more lock slots to become available.
				// Can monitor this with: select count(*) from pg_locks where mode = 'SIReadLock';

				unset( $e );                             //Clear any exceptions on retry.

				//$this->cache->_caching = FALSE; //Disable caching when retrying blocks of transaction, since we can't rollback cached data.
				$this->cache->_onlyMemoryCaching = true; //Disable persistent caching and switch to memory caching only when retrying blocks of transaction, this allows us to clear all memory cache on rollback below.

				Debug::text( '==================START: TRANSACTION BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $transaction_function(); //This function should call StartTransaction() at the beginning, and CommitTransaction() at the end.
				Debug::text( '==================END: TRANSACTION BLOCK=====================================', __FILE__, __LINE__, __METHOD__, 10 );

				//$this->cache->_caching = $current_cache_state;
				$this->cache->_onlyMemoryCaching = $current_cache_memory_state;
			} catch ( Exception $e ) {
				if ( $is_nested_retry_transaction == true ) {
					//If we are inside a nested retry transaction block that fails, we can't fail/retry just part of the transaction,
					// so instead immediately re-throw a new NestedRetryTransaction exception so we can pass that up to the outer retry transaction block, for retrying at the outer most retry block instead.
					// Don't need to bother with any sleep intervals, or transaction fail/commit calls, as the outer block will handle that itself.
					// See APIAuthorization->setAuthorization() and search for "NestedRetryTransaction" for example usage.
					Debug::text( 'WARNING: Inner nested RetryTransaction failed, passing exception to outer block for retry there...', __FILE__, __LINE__, __METHOD__, 10 );
					throw new NestedRetryTransaction( $e ); //'SQL exception in Nested RetryTransaction...'
				} else {
					if ( $this->isSQLExceptionRetryable( $e ) == true ) {
						//Quick way to clear all memory cache on retry.
						$this->cache->_memoryCachingArray = [];
						$this->cache->_memoryCachingCounter = 0;

						//When we get here, fail transaction should already be called.
						// But if it hasn't, call it again just in case.
						if ( $this->db->_transOK == true ) {
							$this->FailTransaction();
						}

						$this->CommitTransaction( true ); //Make sure we fully unnest all transactions so the retry is in a good state that can be fully restarted.

						$random_sleep_interval = ( ceil( ( rand() / getrandmax() ) * ( ( $tmp_sleep * 0.33 ) * 2 ) - ( $tmp_sleep * 0.33 ) ) ); //+/- 33% of the sleep time.

						Debug::text( 'WARNING: SQL query failed, likely due to transaction isolation: Retry Attempt: ' . $retry_attempts . ' Sleep: ' . ( $tmp_sleep + $random_sleep_interval ) . '(' . $tmp_sleep . ') Code: ' . $e->getCode() . ' Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						Debug::text( '==================END: TRANSACTION BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );

						if ( $retry_attempts < ( $retry_max_attempts - 1 ) ) { //Don't sleep on the last iteration as its serving no purpose.
							usleep( $tmp_sleep + $random_sleep_interval );
						}

						$tmp_sleep = ( $tmp_sleep * 2 ); //Exponential back-off with 25% of retry sleep time as a random value.
						$retry_attempts++;

						continue;
					} else {
						Debug::text( 'ERROR: Non-Retryable SQL failure (syntax error?), aborting... Code: ' . $e->getCode() . ' Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						break;
					}
				}
			}
			break;
		}

		if ( isset( $e ) ) {
			Debug::text( 'ERROR: SQL query failed after max attempts: ' . $retry_attempts . ' Max: ' . $retry_max_attempts, __FILE__, __LINE__, __METHOD__, 10 );
			throw new DBError( $e );
		}

		if ( isset( $retval ) ) {
			Debug::Arr( $retval, 'Returning Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return null;
	}

	/**
	 * Returns the differences in data from the DB vs the in-memory object, so the data will be the OLD data. Used in Validation/postSave() functions to determine if a field has changed or not.
	 * @return array
	 */
	function getDataDifferences() {
		$retarr = array_diff_assoc( (array)$this->old_data, (array)$this->data );
		Debug::Arr( $retarr, 'Calling getDataDifferences()', __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * Used to check the differences between a single key in the $old_data vs. $data arrays.
	 * This is especially important to use when trying to see if a date or timestamp field in the DB has changed, as they need to be handled in special ways.
	 * @param $key           string
	 * @param $data_diff     array
	 * @param null $type_id  string
	 * @param null $new_data mixed
	 * @return bool
	 */
	function isDataDifferent( $key, $data_diff, $type_id = null, $new_data = null ) {
		// Must use array_key_exists as there could be a NULL value which is old value and is different of course.
		if ( is_array( $data_diff ) && array_key_exists( $key, $data_diff ) == true ) {
			$retval = false;

			$old_data = $data_diff[$key];

			if ( $new_data === null && array_key_exists( $key, $this->data ) ) {
				$new_data = $this->data[$key];
			}

			switch ( strtolower( $type_id ) ) {
				case 'date':
					//When comparing dates, the old_data is likely from the DB and a string date, while the new data is likely epoch.
					if ( TTDate::getMiddleDayEpoch( strtotime( $old_data ) ) != TTDate::getMiddleDayEpoch( $new_data ) ) {
						$retval = true;
					}
					break;
				default:
					$retval = true;
					break;
			}

			return $retval;
		}

		return false;
	}


	/**
	 * Used to handle any "RETURNING ..." clause on a SQL INSERT/UPDATE query.
	 * @param $rs
	 * @return bool
	 */
	function handleSaveSQLReturning( $rs ) {
		return true;
	}

	/**
	 * Determines to insert or update, and does it.
	 * Have this handle created, createdby, updated, updatedby.
	 * @param bool $reset_data
	 * @param bool $force_lookup
	 * @return bool|int|string
	 * @throws DBError
	 * @throws GeneralError
	 */
	function Save( $reset_data = true, $force_lookup = false ) {
		$this->StartTransaction();

		//Run Pre-Save function
		//This is called before validate so it can do extra calculations, etc before validation.
		//Should this AND validate() NOT be called when delete flag is set?
		if ( method_exists( $this, 'preSave' ) ) {
			Debug::text( 'Calling preSave()', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->preSave() === false ) {
				throw new GeneralError( 'preSave() failed.' );
			}
		}

		//Don't validate when deleting, so we can delete records that may have some invalid options.
		//However we can still manually call this function to check if we need too.
		if ( $this->getDeleted() == false && $this->isValid() === false ) {
			throw new GeneralError( 'Invalid Data, not saving.' );
		}

		//Should we insert, or update?
		if ( $this->isNew( $force_lookup ) ) {
			//Insert
			$time = TTDate::getTime();

			//CreatedBy/Time needs to be set to original values when doing things like importing records.
			//However from the API, Created By only needs to be set for a small subset of classes like RecurringScheduleTemplateControl.
			//We handle this in setCreatedAndUpdatedColumns().
			if ( empty( $this->getCreatedDate() ) ) {
				$this->setCreatedDate( $time );
			}
			if ( empty( $this->getCreatedBy() ) ) {
				$this->setCreatedBy();
			}

			//Set updated date at the same time, so we can easily select last
			//updated, or last created records.
			if ( empty( $this->getUpdatedDate() ) ) {
				$this->setUpdatedDate( $time );
			}
			if ( empty( $this->getUpdatedBy() ) ) {
				$this->setUpdatedBy();
			}

			unset( $time );

			$insert_id = $this->getID();
			if ( $insert_id == false ) {
				//Append insert ID to data array.
				$insert_id = $this->getNextInsertId();
				Debug::text( 'Insert ID: ' . $insert_id . ' Table: ' . $this->getTable(), __FILE__, __LINE__, __METHOD__, 9 );
				$this->setId( $insert_id );
			}

			try {
				$query = $this->getInsertQuery();
			} catch ( Exception $e ) {
				throw new DBError( $e );
			}
			$retval = TTUUID::castUUID( $insert_id );
			$log_action = 10; //'Add';
		} else {
			Debug::text( ' Updating...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->getDeleted() == true ) {
				$this->setDeletedDate();
				$this->setDeletedBy();
			} else {
				//Don't set updated_date when deleting records, we use deleted_date for that instead.
				$this->setUpdatedDate();
				$this->setUpdatedBy();
			}

			//Update
			$query = $this->getUpdateQuery(); //Don't pass data, too slow

			//Debug::Arr($this->data, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
			$retval = true;

			if ( $this->getDeleted() === true ) {
				$log_action = 30; //'Delete';
			} else {
				$log_action = 20; //'Edit';
			}
		}

		//Debug::text('Save(): Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($query, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $query != '' || $query === true ) {

			if ( is_string( $query ) && $query != '' ) {
				$rs = $this->ExecuteSQL( $query );
				$this->handleSaveSQLReturning( $rs );
			}

			if ( method_exists( $this, 'addLog' ) ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				global $config_vars;
				if ( !isset( $config_vars['other']['disable_audit_log'] ) || $config_vars['other']['disable_audit_log'] != true ) {
					$this->addLog( $log_action );
				}
			}

			//Run postSave function.
			if ( method_exists( $this, 'postSave' ) ) {
				Debug::text( 'Calling postSave()', __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->postSave() === false ) {
					throw new GeneralError( 'postSave() failed.' );
				}
			}

			//Clear the data.
			if ( $reset_data == true ) {
				$this->clearData();
			}
			//IF YOUR NOT RESETTING THE DATA, BE SURE TO CLEAR THE OBJECT MANUALLY
			//IF ITS IN A LOOP!! VERY IMPORTANT!

			$this->CommitTransaction();

			//Debug::Arr($retval, 'Save Retval: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		Debug::text( 'Save(): returning FALSE! Very BAD!', __FILE__, __LINE__, __METHOD__, 10 );

		throw new GeneralError( 'Save(): failed.' );

		//return false; //This should return false here?
	}

	/**
	 * Deletes the record directly from the database.
	 * @param bool $disable_audit_log
	 * @return bool
	 * @throws DBError
	 */
	function Delete( $disable_audit_log = false ) {
		Debug::text( 'Delete: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 9 );

		if ( $this->getId() !== false ) {
			if ( $disable_audit_log == false && method_exists( $this, 'addLog' ) ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				global $config_vars;
				if ( !isset( $config_vars['other']['disable_audit_log'] ) || $config_vars['other']['disable_audit_log'] != true ) {
					$this->addLog( 30 ); //30=Delete
				}
			}

			$ph = [
					'id' => $this->getId(),
			];

			$query = 'DELETE FROM ' . $this->getTable() . ' WHERE id = ?';
			$this->ExecuteSQL( $query, $ph );

			return true;
		}

		return false;
	}

	/**
	 * @param object $lf
	 * @return array|bool
	 */
	function getIDSByListFactory( $lf ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		foreach ( $lf as $lf_obj ) {
			$retarr[] = $lf_obj->getID();
		}

		if ( isset( $retarr ) ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * @param string|array $ids UUID
	 * @return bool
	 * @throws DBError
	 */
	function bulkDelete( $ids ) {
		//Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		//Make SURE you get the right table when calling this.
		if ( is_array( $ids ) && count( $ids ) > 0 ) {
			$ph = [];

			$query = 'DELETE FROM ' . $this->getTable() . ' WHERE id in (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')';
			$this->ExecuteSQL( $query, $ph );
			Debug::text( 'Bulk Delete Query: ' . $query . ' Affected Rows: ' . $this->getAffectedRows() . ' IDs: ' . count( $ph ), __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		return false;
	}

	/**
	 * @param array $data_diff
	 * @return bool
	 */
	function clearGeoCode( $data_diff = null ) {
		if ( is_array( $data_diff )
				&& ( $this->isDataDifferent( 'address1', $data_diff ) || $this->isDataDifferent( 'address2', $data_diff ) || $this->isDataDifferent( 'city', $data_diff ) || $this->isDataDifferent( 'province', $data_diff ) || $this->isDataDifferent( 'country', $data_diff ) || $this->isDataDifferent( 'postal_code', $data_diff ) ) ) {
			//Run a separate custom query to clear the geocordinates. Do we really want to do this for so many objects though...
			Debug::text( 'Address has changed, clear geocordinates!', __FILE__, __LINE__, __METHOD__, 10 );
			$query = 'UPDATE ' . $this->getTable() . ' SET longitude = NULL, latitude = NULL where id = ?';
			$this->ExecuteSQL( $query, [ 'id' => $this->getID() ] );

			return true;
		}

		return false;
	}


	/**
	 * Removes array elements from $data that are not in the function map.
	 * @param array|null $data
	 * @return array|null
	 */
	function clearNonMappedData( $data = null ) {
		if ( is_array( $data ) && method_exists( $this, '_getVariableToFunctionMap' ) ) {
			$function_map = $this->getVariableToFunctionMap();
			if ( is_array( $function_map ) ) {
				foreach ( $data as $column => $value ) {
					if ( !isset( $function_map[$column] ) || ( $function_map[$column] == '' ) ) {
						unset( $data[$column] );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	function clearData() {
		$this->data = $this->tmp_data = [];

		$this->clearOldData();

		return true;
	}

	/**
	 * @return bool
	 */
	function clearOldData() {
		$this->old_data = [];

		return true;
	}


	/**
	 * @return FactoryListIterator
	 */
	final function getIterator() {
		return new FactoryListIterator( $this );
	}

	/**
	 * Grabs the current object
	 * @return mixed
	 */
	final function getCurrent() {
		return $this->getIterator()->current();
	}
}

?>
