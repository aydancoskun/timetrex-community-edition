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
 * @package Modules\Request
 */
class RequestFactory extends Factory {
	protected $table = 'request';
	protected $pk_sequence_name = 'request_id_seq'; //PK Sequence name

	var $user_date_obj = NULL;

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
										10 => TTi18n::gettext('Missed Punch'),				//request_punch
										20 => TTi18n::gettext('Punch Adjustment'),			//request_punch_adjust
										30 => TTi18n::gettext('Absence (incl. Vacation)'),	//request_absence
										40 => TTi18n::gettext('Schedule Adjustment'),		//request_schedule
										100 => TTi18n::gettext('Other'),					//request_other
									);
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('INCOMPLETE'),
										20 => TTi18n::gettext('OPEN'),
										30 => TTi18n::gettext('PENDING'), //Used to be "Pending Authorizion"
										40 => TTi18n::gettext('AUTHORIZATION OPEN'),
										50 => TTi18n::gettext('AUTHORIZED'), //Used to be "Active"
										55 => TTi18n::gettext('DECLINED'), //Used to be "AUTHORIZATION DECLINED"
										60 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-1060-title' => TTi18n::gettext('Title'),
										'-1070-user_group' => TTi18n::gettext('Group'),
										'-1080-default_branch' => TTi18n::gettext('Branch'),
										'-1090-default_department' => TTi18n::gettext('Department'),

										'-1110-date_stamp' => TTi18n::gettext('Date'),
										'-1120-status' => TTi18n::gettext('Status'),
										'-1130-type' => TTi18n::gettext('Type'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp', 'status', 'type'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'type',
								'date_stamp',
								'status',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
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
										//'user_date_id' => 'UserDateID',
										'user_id' => 'User',
										'date_stamp' => 'DateStamp',
										'pay_period_id' => 'PayPeriod',

										//'user_id' => FALSE,

										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,

										'type_id' => 'Type',
										'type' => FALSE,
										'hierarchy_type_id' => 'HierarchyTypeId',
										'status_id' => 'Status',
										'status' => FALSE,
										'authorized' => 'Authorized',
										'authorization_level' => 'AuthorizationLevel',
										'message' => 'Message',

										'request_schedule' => FALSE,

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
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
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriod( $value = NULL) {
		if ( $value == NULL ) {
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}
		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	//Convert hierarchy type_ids back to request type_ids.

	/**
	 * @param int $type_id
	 * @return array|int
	 */
	function getTypeIdFromHierarchyTypeId( $type_id ) {
		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = ( $request_type_id >= 1000 AND $request_type_id < 2000 ) ? ( (int)$request_type_id - 1000 ) : (int)$request_type_id;
			}
		} else {
			$retval = ( $type_id >= 1000 AND $type_id < 2000 ) ? ( (int)$type_id - 1000 ) : (int)$type_id;
			Debug::text('Hierarchy Type ID: '. $type_id .' Request Type ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}

	/**
	 * @param int $type_id ID
	 * @return array|bool|int
	 */
	function getHierarchyTypeId( $type_id = NULL ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		if ( $type_id == FALSE ) {
			Debug::text( 'ERROR: Type ID is FALSE', __FILE__, __LINE__, __METHOD__, 10 );
			return FALSE;
		}

		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = ( (int)$request_type_id + 1000 );
			}
		} else {
			$retval = ( (int)$type_id + 1000 );
			Debug::text('Request Type ID: '. $type_id .' Hierarchy Type ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
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
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|null
	 */
	function getAuthorized() {
		return $this->fromBool( $this->getGenericDataValue( 'authorized' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorized( $value ) {
		return $this->setGenericDataValue( 'authorized', $this->toBool($value) );
	}

	/**
	 * @return bool|mixed
	 */
	function getAuthorizationLevel() {
		return $this->getGenericDataValue( 'authorization_level' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorizationLevel( $value) {
		$value = (int)trim( $value );
		if ( $value < 0 ) {
			$value = 0;
		}
		return $this->setGenericDataValue( 'authorization_level', $value );
	}

	/**
	 * @return bool
	 */
	function getMessage() {
		return $this->getGenericTempDataValue( 'message' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMessage( $value ) {
		$value = trim($value);
		return $this->setGenericTempDataValue( 'message', htmlspecialchars( $value ) );
	}

	/**
	 * @return bool
	 */
	function getRequestSchedule() {
		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$rslf = TTNew( 'RequestScheduleListFactory' );
			$rslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array('request_id' => $this->getId()) );
			if ( $rslf->getRecordCount() == 1 ) {
				foreach ( $rslf as $rs_obj ) {
					$result = $rs_obj->getObjectAsArray();
					Debug::Arr( $result, 'getRequestSchedule Result: ', __FILE__, __LINE__, __METHOD__, 10 );

					return $result;
				}
			} else {
				Debug::Text( 'Request Schedule rows: 0 ', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return FALSE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		$ulf = TTnew( 'UserListFactory' );
		$this->Validator->isResultSetWithRows(	'user',
														$ulf->getByID($this->getUser()),
														TTi18n::gettext('Invalid Employee')
													);
		// Pay Period
		if ( $this->getPayPeriod() !== FALSE AND $this->getPayPeriod() != TTUUID::getZeroID() ) {
			$pplf = TTnew( 'PayPeriodListFactory' );
			$this->Validator->isResultSetWithRows(	'pay_period',
															$pplf->getByID($this->getPayPeriod()),
															TTi18n::gettext('Invalid Pay Period')
														);
		}
		// Date
		$this->Validator->isDate(		'date_stamp',
												$this->getDateStamp(),
												TTi18n::gettext('Incorrect date').' (a)'
											);
		if ( $this->Validator->isError('date_stamp') == FALSE ) {
			if ( $this->getDateStamp() > 0 ) {
				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date').' (b)'
											);
			}
		}

		if ( $this->Validator->isError('date_stamp') == FALSE AND $this->getDateStamp() < ( time() - (86400 * 365 * 1 ) ) ) { //No more than 1 year in the past
			$this->Validator->isTRUE(		'date_stamp',
											 FALSE,
											 TTi18n::gettext('Date cannot be more than 1 year in the past')
			);
		}

		if ( $this->Validator->isError('date_stamp') == FALSE AND $this->getDateStamp() > ( time() + (86400 * 365 * 5 ) ) ) { //No more than 5 years in the future.
			$this->Validator->isTRUE(		'date_stamp',
											 FALSE,
											 TTi18n::gettext('Date cannot be more than 5 years in the future')
			);
		}

		//Make sure the user isn't entering requests before the employees hire or after termination date
		if ( $this->Validator->isError('date_stamp') == FALSE AND $this->getDeleted() == FALSE AND $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getHireDate() != '' AND TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											 FALSE,
											 TTi18n::gettext('Date cannot be before your hire date') );
			}
			//Don't bother checking termination date, as it leak sensitive information.
		}

		// Type
		$this->Validator->inArrayKey(	'type',
												$this->getType(),
												TTi18n::gettext('Incorrect Type'),
												$this->getOptions('type')
											);
		// Status
		if ( $this->getStatus() != FALSE ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}

		// Authorization level
		if ( $this->getAuthorizationLevel() !== FALSE ) {
			$this->Validator->isNumeric(	'authorization_level',
													$this->getAuthorizationLevel(),
													TTi18n::gettext('Incorrect authorization level')
												);
		}

		if ( $this->getMessage() !== FALSE ) {
			// HTML interface validates the message too soon, make it skip a 0 length message when only validating.
			if ( $this->Validator->getValidateOnly() == TRUE AND $this->getMessage() == '' ) {
				$minimum_length = 0;
			} else {
				$minimum_length = 2;
			}
			$this->Validator->isLength( 'message',
										$this->getMessage(),
										TTi18n::gettext( 'Reason / Message is too short or too long' ),
										$minimum_length,
										10240
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if (	$this->isNew() == TRUE
				AND $this->Validator->hasError('message') == FALSE
				AND $this->getMessage() == FALSE
				AND $this->Validator->getValidateOnly() == FALSE ) {
			$this->Validator->isTRUE(		'message',
											FALSE,
											TTi18n::gettext('Reason / Message must be specified') );
		}

		if ( $this->getDateStamp() == FALSE
			AND $this->Validator->hasError('date_stamp') == FALSE ) {
			$this->Validator->isTRUE(		'date_stamp',
											FALSE,
											TTi18n::gettext('Incorrect Date').' (c)' );
		}

		if ( !is_object( $this->getUserObject() ) AND $this->Validator->hasError('user_id') == FALSE ) {
			$this->Validator->isTRUE(		'user_id',
											FALSE,
											TTi18n::gettext('Invalid Employee') );
		}

		//Check to make sure this user has superiors to send a request too, otherwise we can't save the request.
		if ( is_object( $this->getUserObject() ) ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
			Debug::Arr($request_parent_level_user_ids, 'Check for Superiors: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !is_array($request_parent_level_user_ids) OR count($request_parent_level_user_ids) == 0 ) {
				$this->Validator->isTRUE(		'message',
												FALSE,
												TTi18n::gettext('No supervisors are assigned to you at this time, please try again later') );
			}
		}

		if ( $this->getDeleted() == TRUE AND in_array( $this->getStatus(), array(50, 55) ) ) {
			$this->Validator->isTRUE(		'status_id',
											FALSE,
											TTi18n::gettext('Unable to delete requests after they have been authorized/declined') );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//If this is a new request, find the current authorization level to assign to it.
		// isNew should be a force check due to request schedule child table
		if ( $this->isNew(TRUE) == TRUE ) {
			if ( $this->getStatus() == FALSE OR $this->getStatus() < 30 ) { //10=INCOMPLETE, 20=OPEN. When upgrading from v10 to v11 if the browser cache isn't cleared the status_id comes through as 20. We saw some cases of it coming through as 10 too.
				$this->setStatus( 30 ); //Pending Auth.
			}

			$hierarchy_highest_level = AuthorizationFactory::getInitialHierarchyLevel( ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getCompany() : 0 ), ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getID() : 0 ), $this->getHierarchyTypeId() );
			$this->setAuthorizationLevel( $hierarchy_highest_level );
		}
		if ( $this->getAuthorized() == TRUE ) {
			$this->setAuthorizationLevel( 0 );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Save message here after we have the request_id.
		if ( $this->getMessage() !== FALSE ) {
			$mcf = TTnew( 'MessageControlFactory' );
			$mcf->StartTransaction();

			$hlf = TTnew( 'HierarchyListFactory' );
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
			Debug::Arr($request_parent_level_user_ids, 'Sending message to current direct Superiors: ', __FILE__, __LINE__, __METHOD__, 10);

			$mcf = TTnew( 'MessageControlFactory' );
			$mcf->setFromUserId( $this->getUser() );
			$mcf->setToUserId( $request_parent_level_user_ids );
			$mcf->setObjectType( 50 ); //Messages don't break out request types like hierarchies do.
			$mcf->setObject( $this->getID() );
			$mcf->setParent( TTUUID::getZeroID() );
			$mcf->setSubject( Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. TTi18n::gettext('request from') .': '. $this->getUserObject()->getFullName(TRUE) );
			$mcf->setBody( $this->getMessage() );
			$mcf->setEnableEmailMessage( FALSE ); //Dont email message notification, send authorization notice instead.

			if ( $mcf->isValid() ) {
				$mcf->Save();
				$mcf->CommitTransaction();
			} else {
				$mcf->FailTransaction();
			}

			//Send initial Pending Authorization email to superiors. -- This should only happen on first save by the regular employee.
			AuthorizationFactory::emailAuthorizationOnInitialObjectSave( $this->getUser(), $this->getHierarchyTypeId(), $this->getId() );
		}

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( $this->getDeleted() == FALSE AND $this->getAuthorized() == TRUE ) {
				$rsf = TTNew('RequestScheduleFactory');
				$add_related_schedules_retval = $rsf->addRelatedSchedules( $this );
				if ( $add_related_schedules_retval == FALSE ) {
					Debug::Text('  addRelatedSchedules failed, passing along validation errors!', __FILE__, __LINE__, __METHOD__, 10);
					$this->Validator->Merge( $rsf->Validator );
				}
				unset($rsf);
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('Delete authorization history for this request...'. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$alf = TTnew( 'AuthorizationListFactory' );
			$alf->getByObjectTypeAndObjectId( $this->getHierarchyTypeId(), $this->getId() );
			foreach( $alf as $authorization_obj ) {
				Debug::Text('Deleting authorization ID: '. $authorization_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$authorization_obj->setDeleted(TRUE);
				$authorization_obj->Save();
			}
		}

		return TRUE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			/*
			if ( isset($data['user_id']) AND $data['user_id'] != ''
					AND isset($data['date_stamp']) AND $data['date_stamp'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
			}
			*/

			if ( isset($data['status_id']) AND $data['status_id'] == '' ) {
				unset($data['status_id']);
				$this->setStatus( 30 ); //Pending authorization
			}
			if ( isset($data['user_date_id']) AND $data['user_date_id'] == '' ) {
				unset($data['user_date_id']);
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {
					$function = 'set'.$function;
					switch( $key ) {
						case 'date_stamp':
							$this->setDateStamp( TTDate::parseDateTime( $data['date_stamp'] ) );
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

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'message': //Message is attached in the message factory, so we can't return it here.
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'request_schedule':
							if( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
								if ( $this->getType() == 30 OR $this->getType() == 40 ) {
									$request_schedule = $this->getRequestSchedule();
									if ( $request_schedule != FALSE AND count( $request_schedule ) > 0 ) {
										$data[$variable] = $request_schedule;
									}
								}
							}
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Request - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Type').': '. Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. TTi18n::getText('Date').': '. TTDate::getDate('DATE+TIME', $this->getDateStamp() ), NULL, $this->getTable(), $this );
	}
}
?>
