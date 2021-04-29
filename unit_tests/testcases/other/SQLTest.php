<?php /** @noinspection PhpMissingDocCommentInspection */

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

class SQLTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false );                     //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		//$dd->createPayStubAccount( $this->company_id );
		//$dd->createPayStubAccountLink( $this->company_id );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$this->assertGreaterThan( 0, $this->user_id );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getListFactoryClassList( $equal_parts = 1 ) {
		global $global_class_map;

		$retarr = [];

		//Get all ListFactory classes
		foreach ( $global_class_map as $class_name => $class_file_name ) {
			if ( strpos( $class_name, 'ListFactory' ) !== false ) {
				$retarr[] = $class_name;
			}
		}

		$chunk_size = ceil( ( count( $retarr ) / $equal_parts ) );

		return array_chunk( $retarr, $chunk_size );
	}

	/** @noinspection PhpMissingBreakStatementInspection */
	function runSQLTestOnListFactory( $factory_name ) {
		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			$class_file_name = $reflectionClass->getFileName();

			Debug::text( 'Checking Class: ' . $factory_name . ' File: ' . $class_file_name, __FILE__, __LINE__, __METHOD__, 10 );

			$filter_data_types = [
					'not_set',
					'true',
					'false',
					'null',
					'empty_string',
					'negative_small_int',
					'small_int',
					'large_int',
					'string',
					'array',
			];

			//Parse filter array keys from class file so we can populate them with dummy data.
			preg_match_all( '/\$filter_data\[\'([a-z0-9_]*)\'\]/i', file_get_contents( $class_file_name ), $filter_data_match );
			if ( isset( $filter_data_match[1] ) ) {
				//Debug::Arr($filter_data_match, 'Filter Data Match: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach ( $filter_data_types as $filter_data_type ) {
					Debug::Text( 'Filter Data Type: ' . $filter_data_type, __FILE__, __LINE__, __METHOD__, 10 );

					$filter_data = [];

					$filter_data_match[1] = array_unique( $filter_data_match[1] );
					foreach ( $filter_data_match[1] as $filter_data_key ) {
						//Skip sort_column/sort_order
						if ( in_array( $filter_data_key, [ 'sort_column', 'sort_order' ] ) ) {
							continue;
						}

						//Test with:
						// Small Integers
						// Large Integers (64bit)
						// Strings
						// Arrays
						switch ( $filter_data_type ) {
							case 'true':
								$filter_data[$filter_data_key] = true;
								break;
							case 'false':
								$filter_data[$filter_data_key] = false;
								break;
							case 'null':
								$filter_data[$filter_data_key] = null;
								break;
							case 'empty_string':
								$filter_data[$filter_data_key] = '';
								break;
							case 'negative_small_int':
								$filter_data[$filter_data_key] = ( rand( 0, 128 ) * -1 );
								break;
							case 'small_int':
								$filter_data[$filter_data_key] = rand( 0, 128 );
								break;
							case 'large_int':
								$filter_data[$filter_data_key] = rand( 2147483648, 21474836489 );
								break;
							case 'string':
								$filter_data[$filter_data_key] = 'A' . substr( md5( microtime() ), rand( 0, 26 ), 10 );
								break;
							case 'array':
								$filter_data[$filter_data_key] = [ rand( 0, 128 ), rand( 2147483648, 21474836489 ), 'A' . substr( md5( microtime() ), rand( 0, 26 ), 10 ) ];
								break;
							case 'not_set':
								break;
						}
					}
					//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

					$lf = TTNew( $factory_name );
					switch ( $factory_name ) {
						case 'RecurringScheduleControlListFactory':
							$retarr = $lf->getAPIExpandedSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, null, null );
							$this->assertNotEquals( false, $retarr );
							$this->assertTrue( is_object( $retarr ), true );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, null, null );
							$this->assertNotEquals( false, $retarr );
							$this->assertTrue( is_object( $retarr ), true );
							break;
						case 'ScheduleListFactory':
							$retarr = $lf->getSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, null, null );
							$this->assertNotEquals( false, $retarr );
							$this->assertTrue( is_object( $retarr ), true );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, null, null );
							$this->assertNotEquals( false, $retarr );
							$this->assertTrue( is_object( $retarr ), true );
							break;
						case 'MessageControlListFactory':
							$filter_data['current_user_id'] = $this->user_id;
							//break; //Intentional that break is not here so it spills into default?
						default:
							if ( method_exists( $lf, 'getAPISearchByCompanyIdAndArrayCriteria' ) ) {
								//Make sure we test pagination, especially with subqueries and the need for _ADODB_COUNT workarounds, $limit = NULL, $page = NULL, $where = NULL, $order = NULL
								$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, null, null );
								$this->assertNotEquals( false, $retarr );
								$this->assertTrue( is_object( $retarr ), true );
							}
							break;
					}
				}
			}
			unset( $filter_data_match );

			Debug::text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			Debug::text( 'Class does not exist: ' . $factory_name, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	function runSQLTestOnListFactoryMethods( $factory_name ) {
		if ( in_array( $factory_name, [
											'HierarchyListFactory',
											'PolicyGroupAccrualPolicyListFactory',
											'PolicyGroupOverTimePolicyListFactory',
											'PolicyGroupPremiumPolicyListFactory',
											'PolicyGroupRoundIntervalPolicyListFactory',
											'ProductTaxPolicyProductListFactory',
									]
		)
		) {
			return true; //Deprecated classes.
		}

		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			Debug::text( 'Checking Class: ' . $factory_name, __FILE__, __LINE__, __METHOD__, 10 );

			$raw_methods = $reflectionClass->getMethods( ReflectionMethod::IS_PUBLIC );
			if ( is_array( $raw_methods ) ) {
				foreach ( $raw_methods as $raw_method ) {
					if ( $factory_name == $raw_method->class
							&& (
									strpos( $raw_method->name, 'getAll' ) !== false
									|| strpos( $raw_method->name, 'getBy' ) !== false
									|| strpos( $raw_method->name, 'Report' ) !== false
							)
							&& (
								//Skip getByCompanyIdArray() functions, but include getBy*AndArrayCriteria(). So just check if its ends with Array or not.
							( substr( $raw_method->name, -5 ) !== 'Array' )
							)
					) {
						Debug::text( 'Class: ' . $factory_name . ' Method: ' . $raw_method->name, __FILE__, __LINE__, __METHOD__, 10 );

						$test_modes = [ 'default', 'fuzz' ];
						foreach ( $test_modes as $test_mode ) {
							Debug::text( '  Test Mode: ' . $test_mode, __FILE__, __LINE__, __METHOD__, 10 );
							//Get method arguments.
							$method_parameters = $raw_method->getParameters();
							if ( is_array( $method_parameters ) ) {
								$input_arguments = [];
								foreach ( $method_parameters as $method_parameter ) {
									Debug::text( '  Parameter: ' . $method_parameter->name, __FILE__, __LINE__, __METHOD__, 10 );

									switch ( $factory_name ) {
										case 'ClientContactListFactory':
											switch ( $method_parameter->name ) {
												case 'key':
													$input_argument = '900d6136975e3a728051a62ed119191034568745';
													break;
												case 'name':
													$input_argument = 'test';
													break;
											}
											break;
										case 'RoundIntervalPolicyListFactory':
											switch ( $method_parameter->name ) {
												case 'type_id':
													$input_argument = 40;
													break;
											}
											break;
										case 'ScheduleListFactory':
											switch ( $method_parameter->name ) {
												case 'direction':
													$input_argument = 'before';
													break;
											}
											break;
										case 'UserListFactory':
										case 'UserContactListFactory':
										case 'JobApplicantListFactory':
											switch ( $method_parameter->name ) {
												case 'email':
													$input_argument = 'hi@hi.com';
													break;
												case 'key':
													$input_argument = '900d6136975e3a728051a62ed119191034568745';
													break;
											}
											break;
										case 'PayPeriodTimeSheetVerifyListFactory':
										case 'RequestListFactory':
											switch ( $method_parameter->name ) {
												case 'hierarchy_level_map':
													$input_argument = [
															[
																	'hierarchy_control_id' => 1,
																	'level'                => 1,
																	'last_level'           => 2,
																	'object_type_id'       => 10,
															],
													];
													break;
											}
											break;
										case 'ExceptionListFactory':
											switch ( $method_parameter->name ) {
												case 'time_period':
													$input_argument = 'week';
													break;
											}
											break;
									}

									if ( $test_mode == 'fuzz' ) {
										//If LIMIT argument is available always set it to 1 to reduce memory usage.
										if ( in_array( $method_parameter->name, [ 'where', 'order', 'page' ] ) ) {
											$input_argument = null;
										} else if ( !isset( $input_argument ) && ( $method_parameter->name == 'id' || strpos( $method_parameter->name, '_id' ) !== false || $method_parameter->name == 'limit' ) ) { //Use integer as its a ID argument.
											$input_argument = 'false';                                                                                                                                               //Try passing a string where ID is expected.
										} else if ( !isset( $input_argument ) ) {
											$input_argument = 2;
										}
										$input_arguments[] = $input_argument;
									} else {
										//If LIMIT argument is available always set it to 1 to reduce memory usage.
										if ( in_array( $method_parameter->name, [ 'where', 'order', 'page' ] ) ) {
											$input_argument = null;
										} else if ( !isset( $input_argument ) && ( $method_parameter->name == 'id' || strpos( $method_parameter->name, '_id' ) !== false || $method_parameter->name == 'limit' ) ) { //Use integer as its a ID argument.
											$input_argument = 1;
										} else if ( !isset( $input_argument ) ) {
											$input_argument = 2;
										}
										$input_arguments[] = $input_argument;
									}
									unset( $input_argument );
								}

								if ( isset( $input_arguments ) && is_array( $input_arguments ) ) {
									Debug::Arr( $input_arguments, '    Calling Class: ' . $factory_name . ' Method: ' . $raw_method->name, __FILE__, __LINE__, __METHOD__, 10 );
									$lf = TTNew( $factory_name );
									switch ( $factory_name . '::' . $raw_method->name ) {
										case 'StationListFactory::getByUserIdAndStatusAndType':
										case 'PayStubEntryAccountListFactory::getByTypeArrayByCompanyIdAndStatusId':
											//Skip due to failures.
											break;
										case 'CompanyListFactory::getByPhoneID':
											$retarr = call_user_func_array( [ $lf, $raw_method->name ], $input_arguments );
											if ( $test_mode == 'fuzz' ) {
												$this->assertEquals( false, ( ( is_object( $retarr ) ) ? ( ( $retarr->getRecordCount() == 0 ) ? false : true ) : $retarr ) ); //This will be FALSE
											} else {
												$this->assertNotEquals( false, $retarr );
												$this->assertTrue( is_object( $retarr ), true );
											}
											break;
										case 'MessageControlListFactory::getByCompanyIdAndObjectTypeAndObjectAndNotUser':
											$retarr = call_user_func_array( [ $lf, $raw_method->name ], $input_arguments );
											$this->assertEquals( false, ( ( is_object( $retarr ) ) ? ( ( $retarr->getRecordCount() == 0 ) ? false : true ) : $retarr ) ); //This will be FALSE, but it still executes a query.
											//$this->assertTrue( is_object($retarr), TRUE );
											break;
										case 'PayStubEntryListFactory::getByPayStubIdAndEntryNameId':
											//FUZZ tests should return FALSE, otherwise they should be normal.
											$retarr = call_user_func_array( [ $lf, $raw_method->name ], $input_arguments );
											if ( $test_mode == 'fuzz' ) {
												$this->assertEquals( false, ( ( is_object( $retarr ) ) ? ( ( $retarr->getRecordCount() == 0 ) ? false : true ) : $retarr ) ); //This will be FALSE
											} else {
												$this->assertNotEquals( false, $retarr );
												$this->assertTrue( is_object( $retarr ), true );
											}
											break;
										default:
											$retarr = call_user_func_array( [ $lf, $raw_method->name ], $input_arguments );
											//Debug::Arr($retarr, '    RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
											$this->assertNotEquals( false, $retarr );
											$this->assertTrue( is_object( $retarr ), true );
											break;
									}
								} else {
									Debug::text( '  No INPUT arguments... Skipping Class: ' . $factory_name . ' Method: ' . $raw_method->name, __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						}
					} else {
						Debug::text( 'Skipping... Class: ' . $factory_name . ' Method: ' . $raw_method->name, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}

			Debug::text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			Debug::text( 'Class does not exist: ' . $factory_name, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	function runSQLSortTestOnListFactory( $factory_name ) {
		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			$class_file_name = $reflectionClass->getFileName();

			Debug::text( 'Checking Class: ' . $factory_name . ' File: ' . $class_file_name, __FILE__, __LINE__, __METHOD__, 10 );

			$lf = TTNew( $factory_name );
			if ( method_exists( $lf, 'getOptions' ) ) {
				if ( method_exists( $lf, 'getAPISearchByCompanyIdAndArrayCriteria' ) ) {
					$tmp_columns = $lf->getOptions( 'columns' );
					if ( is_array( $tmp_columns ) && count( $tmp_columns ) > 0 ) {
						$columns = array_fill_keys( array_keys( array_flip( array_keys( Misc::trimSortPrefix( $lf->getOptions( 'columns' ) ) ) ) ), 'asc' );        //Set sort order to ASC for all columns.
						unset( $columns['tag'] );                                                                                                                   //Remove columns that we can never sort by.
						if ( is_array( $columns ) ) {
							try {

								if ( !in_array( $factory_name, [ 'BankAccountListFactory' ] ) ) { //Skip legacy factories.
									if ( $factory_name == 'MessageControlListFactory' ) {
										$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, [ 'current_user_id' => TTUUID::getZeroID() ], 1, 1, null, $columns );
									} else if ( $factory_name == 'RecurringScheduleControlListFactory' ) {
										$retarr = $lf->getAPIExpandedSearchByCompanyIdAndArrayCriteria( $this->company_id, [ 'current_user_id' => TTUUID::getZeroID() ], 1, 1, null, $columns );
									} else {
										//$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, array(), 1, 1, NULL, array('a.bogus' => 'asc') );
										$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, [], 1, 1, null, $columns );
									}

									$this->assertNotEquals( false, $retarr );
									$this->assertTrue( is_object( $retarr ), true );
								}
							} catch ( Exception $e ) {
								Debug::Arr( $columns, 'Columns: ', __FILE__, __LINE__, __METHOD__, 10 );

								$this->assertTrue( false, $factory_name . ': ' . $e->getMessage() );
							}
						} else {
							Debug::text( 'getOptions(\'columns\') does not return any data, skipping... Factory: ' . $factory_name, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
					unset( $tmp_columns );
				}
			} else {
				Debug::text( 'getOptions() method does not exist, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			Debug::text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			Debug::text( 'Class does not exist: ' . $factory_name, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	function runSQLTestOnEdition( $product_edition = TT_PRODUCT_ENTERPRISE, $class_list ) {
		global $TT_PRODUCT_EDITION, $db;

		$original_product_edition = getTTProductEdition();

		$this->assertTrue( true );
		if ( $product_edition <= $original_product_edition ) {
			$TT_PRODUCT_EDITION = $product_edition;
			Debug::text( 'Checking against Edition: ' . getTTProductEditionName(), __FILE__, __LINE__, __METHOD__, 10 );

			//Loop through all ListFactory classes testing SQL queries.

			//Run tests with count rows enabled, then with it disabled as well.
			$db->pageExecuteCountRows = false;
			foreach ( $class_list as $class_name ) {
				$this->runSQLSortTestOnListFactory( $class_name );
				$this->runSQLTestOnListFactoryMethods( $class_name );
				$this->runSQLTestOnListFactory( $class_name );
			}

			$db->pageExecuteCountRows = true;
			foreach ( $class_list as $class_name ) {
				$this->runSQLTestOnListFactoryMethods( $class_name );
				$this->runSQLTestOnListFactory( $class_name );
			}
		}

		return true;
	}

	/**
	 * @group SQL_CommunityA
	 */
	function testSQLCommunityA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[0] );
	}

	/**
	 * @group SQL_CommunityB
	 */
	function testSQLCommunityB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[1] );
	}

	/**
	 * @group SQL_CommunityC
	 */
	function testSQLCommunityC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[2] );
	}

	/**
	 * @group SQL_CommunityD
	 */
	function testSQLCommunityD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[3] );
	}


	/**
	 * @group SQL_ProfessionalA
	 */
	function testSQLProfessionalA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[0] );
	}

	/**
	 * @group SQL_ProfessionalB
	 */
	function testSQLProfessionalB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[1] );
	}

	/**
	 * @group SQL_ProfessionalC
	 */
	function testSQLProfessionalC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[2] );
	}

	/**
	 * @group SQL_ProfessionalD
	 */
	function testSQLProfessionalD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[3] );
	}


	/**
	 * @group SQL_CorporateA
	 */
	function testSQLCorporateA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[0] );
	}

	/**
	 * @group SQL_CorporateB
	 */
	function testSQLCorporateB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[1] );
	}

	/**
	 * @group SQL_CorporateC
	 */
	function testSQLCorporateC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[2] );
	}

	/**
	 * @group SQL_CorporateD
	 */
	function testSQLCorporateD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[3] );
	}


	/**
	 * @group SQL_EnterpriseA
	 */
	function testSQLEnterpriseA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[0] );
	}

	/**
	 * @group SQL_EnterpriseB
	 */
	function testSQLEnterpriseB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[1] );
	}

	/**
	 * @group SQL_EnterpriseC
	 */
	function testSQLEnterpriseC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[2] );
	}

	/**
	 * @group SQL_EnterpriseD
	 */
	function testSQLEnterpriseD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[3] );
	}

	/**
	 * @group SQL_ADODBActiveRecordCount
	 */
	function testADODBActiveRecordCount() {
		global $db;

		//This will test the automatic functionality of ADODB to add count(*) in SQL queries.

		//PageExecute($query, $limit, $page, $ph)

		$db->pageExecuteCountRows = true;

		try {
			$query = 'SELECT id FROM currency';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT id, status_id FROM currency';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT a.id FROM currency AS a LEFT JOIN company as tmp ON a.id = tmp.id';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT a.id FROM currency AS a LEFT JOIN ( SELECT id FROM company ) as tmp ON a.id = tmp.id';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT a.id, a.status_id FROM currency AS a LEFT JOIN ( SELECT id, status_id FROM company ) as tmp ON a.id = tmp.id';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT * FROM ( SELECT a.id FROM currency AS a LEFT JOIN company as tmp ON a.id = tmp.id ) as tmp2';
			$db->PageExecute( $query, 2, 2 );
			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		try {
			$query = 'SELECT _ADODB_COUNT id, (SELECT 1 FROM currency LIMIT 1) _ADODB_COUNT AS tmp FROM users';
			$db->PageExecute( $query, 2, 2 );

			$this->assertTrue( true );
		} catch ( Exception $e ) {
			$this->assertTrue( false );
		}

		//This query should have the _ADODB_COUNT keyword above to make it work on all databases.
		//It works on PGSQL because it can wrap it in a sub-query, ie: SELECT count(*) FROM ( $query )
		try {
			$query = 'SELECT id, (SELECT 1 FROM currency LIMIT 1) AS tmp FROM users';
			$db->PageExecute( $query, 2, 2 );

			$this->assertTrue( true ); //PGSQL
		} catch ( Exception $e ) {
			$this->assertTrue( false ); //PGSQL
		}
	}

	/**
	 * @group SQL_SQLInjectionA
	 */
	function testSQLInjectionA() {
		//Test SQL injection with SORT SQL.
		//Test SQL injection with WHERE SQL.

		$utlf = new UserTitleListFactory();

		//Test standard case that should work.
		$sort_arr = [
				'a.name' => 'asc',
		];
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );

		//var_dump( $utlf->rs->sql );
		if ( stripos( $utlf->rs->sql, 'a.name' ) !== false ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false );
		}


		//Test advanced case that should work.
		$sort_arr = [
			//'a.name = \'test\'' => 'asc'
			'a.created_date = 0' => 'asc',
		];
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );

		//var_dump( $utlf->rs->sql );
		if ( stripos( $utlf->rs->sql, 'a.created_date = 0' ) !== false ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false );
		}


		//Test advanced case that does not work currently, but we may need to get to work.
		try {
			$pself = new PayStubEntryListFactory();
			$sort_arr = [
					'abs(a.created_date)' => 'asc',
			];

			$pself->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );
			//var_dump( $pself->rs->sql );
			if ( stripos( $pself->rs->sql, 'abs(a.created_date)' ) !== false ) {
				$this->assertTrue( false );
			} else {
				$this->assertTrue( true );
			}
		} catch ( Exception $e ) {
			$this->assertTrue( true );
		}


		//Test SQL injection in the ORDER BY clause.
		try {
			$sort_arr = [
					'created_by' => '(SELECT 1)-- .id.',
			];
			$utlf->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );

			//var_dump( $utlf->rs->sql );
			if ( stripos( $utlf->rs->sql, '(SELECT 1)' ) !== false ) {
				$this->assertTrue( false );
			} else {
				$this->assertTrue( true );
			}
		} catch ( Exception $e ) {
			$this->assertTrue( true );
		}


		//Test SQL injection with brackets and "--"
		try {
			$sort_arr = [
					'(SELECT 1)-- .id.' => 1,
			];
			$utlf->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );

			//var_dump( $utlf->rs->sql );
			if ( stripos( $utlf->rs->sql, '(SELECT 1)' ) !== false ) {
				$this->assertTrue( false );
			} else {
				$this->assertTrue( true );
			}
		} catch ( Exception $e ) {
			$this->assertTrue( true );
		}


		//Test SQL injection with ";" and "--"
		try {
			$sort_arr = [
					'; (SELECT 1)-- .id.' => 1,
			];
			$utlf->getAPISearchByCompanyIdAndArrayCriteria( 1, [], null, null, null, $sort_arr );

			//var_dump( $utlf->rs->sql );
			if ( stripos( $utlf->rs->sql, '(SELECT 1)' ) !== false ) {
				$this->assertTrue( false );
			} else {
				$this->assertTrue( true );
			}
		} catch ( Exception $e ) {
			$this->assertTrue( true );
		}


		//FIXME: Test around the WHERE clause, even though no user input should ever get to it.
//		$pslf = TTnew('PayStubListFactory');
//		//$pslf->getByCompanyId( 1, 1, NULL, ( array('a.start_date' => ">= '". $pslf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - (86400 * 30) ) )."'") ) );
//		$pslf->getByCompanyId( 1, 1, NULL, ( array('a.start_date >=' => $pslf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - (86400 * 30) ) ) ) ) );
//		//$pslf->getByCompanyId( 1, 1, NULL, ( array('a.created_date' => "=1-- (SELECT 1)--") ) );
//		var_dump( $pslf->rs->sql );
	}


	/**
	 * Used to call protected methods in the Factory class.
	 * @param $name
	 * @return ReflectionMethod
	 */
	protected static function getMethod( $name ) {
		$class = new ReflectionClass( 'UserListFactory' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}

	/**
	 * @group SQL_testWhereClauseSQL
	 */
	function testWhereClauseSQL() {
		$method = self::getMethod( 'getWhereClauseSQL' );
		$ulf = new UserListFactory();


		//Boolean TRUE
		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', (bool)true, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', (int)1, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 1, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 1.00, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', '1', 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 'TRUE', 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 1, $ph[0] );


		//Boolean FALSE
		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', (bool)false, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', (int)0, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 0, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 0.00, 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', '0', 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );

		$ph = [];
		$retval = $method->invokeArgs( $ulf, [ 'a.private', 'FALSE', 'boolean', &$ph ] );
		$this->assertEquals( ' AND a.private = ? ', $retval );
		$this->assertEquals( 0, $ph[0] );
	}

	/**
	 * @group SQL_testTransactionNestingA
	 */
	function testTransactionNestingA() {
		$uf = new UserFactory();

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );


		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 1, $uf->db->transOff );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 2, $uf->db->transOff );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 3, $uf->db->transOff );

		//$uf->FailTransaction();
		$uf->CommitTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 2, $uf->db->transOff );

		$uf->CommitTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 1, $uf->db->transOff );

		$uf->CommitTransaction();
		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );
	}

	/**
	 * @group SQL_testTransactionNestingB
	 */
	function testTransactionNestingB() {
		$uf = new UserFactory();

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );


		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 1, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 2, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 3, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->FailTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 3, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );

		$uf->CommitTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 2, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );

		$uf->CommitTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 1, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );

		$uf->CommitTransaction();
		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );


		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );
	}

	/**
	 * @group SQL_testTransactionNestingC
	 */
	function testTransactionNestingC() {
		$uf = new UserFactory();

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );


		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 1, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 2, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->StartTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 3, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );

		$uf->FailTransaction();
		$this->assertEquals( 1, $uf->db->transCnt );
		$this->assertEquals( 3, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );

		$uf->CommitTransaction( true ); //Unest all transactions.
		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );


		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );
	}

	/**
	 * @group SQL_testTransactionNestingC2
	 */
	function testTransactionNestingC2() {
		$uf = new UserFactory();

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );


		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->FailTransaction();

		$uf->CommitTransaction( true ); //Unest all transactions.

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );
	}

	/**
	 * @group SQL_testTransactionNestingC3
	 */
	function testTransactionNestingC3() {
		$uf = new UserFactory();

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( true, $uf->db->_transOK );


		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->StartTransaction();
		$uf->FailTransaction();
		$uf->FailTransaction();
		$uf->FailTransaction();

		$uf->CommitTransaction( true ); //Unest all transactions.

		$this->assertEquals( 0, $uf->db->transCnt );
		$this->assertEquals( 0, $uf->db->transOff );
		$this->assertEquals( false, $uf->db->_transOK );
	}

	/**
	 * @group SQL_testMaximumQueryLengthA
	 */
	function testMaximumQueryLengthA() {
		$ulf = new UserListFactory();

		//Build list of many UUIDs.
		for( $i = 0; $i < 70000; $i++ ) { //65535 appears to be the max number of parameters for a WHERE IN clause in PostgreSQL.
			$ids[] = TTUUID::generateUUID();
		}

		//This should fail with an exception.
		try {
			$ulf->getByIdAndCompanyId( $ids, TTUUID::getZeroID() );
			$this->assertTrue( false, $ulf->getRecordCount() );
		} catch ( Exception $e ) {
			$this->assertTrue( true, $e->getMessage() );
			$this->assertGreaterThan( 10, strlen( $e->getMessage() ) );
			$this->assertEquals( -1, $e->getCode() );
		}

		//This should succeed with still a very high number.
		$ids = array_slice( $ids, 0, 5000 );
		try {
			$ulf->getByIdAndCompanyId( $ids, TTUUID::getZeroID() );
			$this->assertTrue( true, $ulf->getRecordCount() );
		} catch ( Exception $e ) {
			$this->assertTrue( false, $e->getMessage() );
		}

	}

}
