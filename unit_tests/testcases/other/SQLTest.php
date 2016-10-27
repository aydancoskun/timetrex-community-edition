<?php
require_once('PHPUnit/Framework/TestCase.php');

/**
 * @group SQL
 */
class SQLTest extends PHPUnit_Framework_TestCase {
    public function __construct() {
        global $db, $cache;
    }

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		//$dd->createPayStubAccount( $this->company_id );
		//$dd->createPayStubAccountLink( $this->company_id );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);
        return TRUE;
    }

	function runSQLTestOnListFactory( $factory_name ) {
		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			$class_file_name = $reflectionClass->getFileName();

			Debug::text('Checking Class: '. $factory_name .' File: '. $class_file_name, __FILE__, __LINE__, __METHOD__,10);

			$filter_data_types = array(
										'not_set', //passes
										'true', //passes
										'false', //passes
										'null', //passes
										'negative_small_int', //passes
										'small_int', //passes
										'large_int',
										'string', //passes
										'array', //passes
									);

			//Parse filter array keys from class file so we can populate them with dummy data.
			preg_match_all( '/\$filter_data\[\'([a-z0-9_]*)\'\]/i', file_get_contents($class_file_name), $filter_data_match);
			if ( isset($filter_data_match[1]) ) {
				//Debug::Arr($filter_data_match, 'Filter Data Match: ', __FILE__, __LINE__, __METHOD__,10);
				foreach( $filter_data_types as $filter_data_type ) {
					Debug::Text('Filter Data Type: '. $filter_data_type, __FILE__, __LINE__, __METHOD__,10);

					$filter_data = array();

					$filter_data_match[1] = array_unique( $filter_data_match[1] );
					foreach( $filter_data_match[1] as $filter_data_key ) {
						//Skip sort_column/sort_order
						if ( in_array( $filter_data_key, array('sort_column', 'sort_order' ) ) ) {
							continue;
						}

						//Test with:
						// Small Integers
						// Large Integers (64bit)
						// Strings
						// Arrays
						switch ( $filter_data_type ) {
							case 'true':
								$filter_data[$filter_data_key] = TRUE;
								break;
							case 'false':
								$filter_data[$filter_data_key] = FALSE;
								break;
							case 'null':
								$filter_data[$filter_data_key] = NULL;
								break;
							case 'negative_small_int':
								$filter_data[$filter_data_key] = ( rand(0, 128) * -1 );
								break;
							case 'small_int':
								$filter_data[$filter_data_key] = rand(0, 128);
								break;
							case 'large_int':
								$filter_data[$filter_data_key] = rand(2147483648, 21474836489);
								break;
							case 'string':
								$filter_data[$filter_data_key] = 'A'.substr( md5(microtime()), rand(0,26), 10 );
								break;
							case 'array':
								$filter_data[$filter_data_key] = array( rand(0, 128), rand(2147483648, 21474836489), 'A'.substr( md5(microtime()), rand(0,26), 10 ) );
								break;
							case 'not_set':
								break;
						}
					}
					//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);

					$lf = TTNew( $factory_name );
					switch( $factory_name ) {
						case 'RecurringScheduleControlListFactory':
							$retarr = $lf->getAPIExpandedSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );
							break;
						case 'ScheduleListFactory':
							$retarr = $lf->getSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );
							break;
						case 'MessageControlListFactory':
							$filter_data['current_user_id'] = $this->user_id;
						default:
							if ( method_exists( $lf, 'getAPISearchByCompanyIdAndArrayCriteria' ) ) {
								//Make sure we test pagination, especially with MySQL due to its limitation with subqueries and need for _ADODB_COUNT workarounds, $limit = NULL, $page = NULL, $where = NULL, $order = NULL
								$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
								$this->assertNotEquals( $retarr, FALSE );
								$this->assertTrue( is_object($retarr), TRUE );
							}
							break;
					}
				}
			}
			unset($filter_data_match);
		} else {
			Debug::text('Class does not exist: '. $factory_name, __FILE__, __LINE__, __METHOD__,10);
		}
	}

	function testSQL() {
		global $TT_PRODUCT_EDITION, $global_class_map;

		$original_product_edition = getTTProductEdition();

		//Check all SQL queries in each product edition.
		$product_editions = array( TT_PRODUCT_COMMUNITY, TT_PRODUCT_PROFESSIONAL, TT_PRODUCT_CORPORATE, TT_PRODUCT_ENTERPRISE );

		foreach( $product_editions as $product_edition ) {
			if ( $product_edition <= $original_product_edition ) {
				$TT_PRODUCT_EDITION = $product_edition;
				Debug::text('Checking against Edition: '. getTTProductEditionName(), __FILE__, __LINE__, __METHOD__,10);

				//Loop through all ListFactory classes testing SQL queries.
				foreach( $global_class_map as $class_name => $class_file_name ) {
					if ( strpos( $class_name, 'ListFactory' ) !== FALSE ) {
						$this->runSQLTestOnListFactory( $class_name );
					}
				}
			}
		}

		return TRUE;
	}
}