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

/**
 * @group APITest
 */
class APITest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $user_id = null;
	protected $currency_id = null;
	protected $branch_ids = null;
	protected $department_ids = null;
	protected $user_title_ids = null;
	protected $user_ids = null;

	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false );                     //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only. **MUST CREATE THIS SO THEY HAVE LOGIN PERMISSIONS**

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

		$this->user_title_ids[] = $dd->createUserTitle( $this->company_id, 10 );

		$this->user_ids[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10 );

		//
		//Specify LastLoginDate and also change password so we can login remotely without isFirstLogin() and isCompromisedPassword() being triggered.
		//
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->user_obj = $ulf->getById( $this->user_id )->getCurrent();
		$this->user_obj->setLastLoginDate( time() );
		$this->user_obj->setPassword( 'demo', 'demo', true );
		if ( $this->user_obj->Save( false ) == false ) {
			$this->assertEquals( false, true );
		}

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	/**
	 * @group testSOAPAPILoginWithSessionID
	 */
	public function testSOAPAPILoginWithSessionID() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'soap' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$api_session->Login( 'demoadmin' . $dd->getUserNamePostfix(), 'demo' );
			$this->assertNotEquals( false, $TIMETREX_SESSION_ID );
			Debug::text( 'Session ID: ' . $TIMETREX_SESSION_ID, __FILE__, __LINE__, __METHOD__, 10 );

			//Make sure we can get the user record and the IDs match who we logged in as.
			$user_obj = new TimeTrexClientAPI( 'User' );
			$result = $user_obj->getUser( [ 'filter_data' => [ 'id' => $this->user_ids[0] ] ] );
			$user_data = $result->getResult();
			$this->assertEquals( $user_data[0]['id'], $this->user_ids[0] );

			$api_session = new TimeTrexClientAPI();
			$this->assertEquals( true, $api_session->isLoggedIn() );


			//Test that isLoggedIn() called above doesn't change the class and allows the same class to be called again after.
			$result = $user_obj->getUser( [ 'filter_data' => [ 'id' => $this->user_ids[0] ] ] );
			$user_data = $result->getResult();
			$this->assertEquals( $user_data[0]['id'], $this->user_ids[0] );


			$this->assertEquals( true, $api_session->Logout() );
		}

	}

	/**
	 * @group testSOAPAPILoginWithSessionIDFail
	 */
	public function testSOAPAPILoginWithSessionIDFail() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'soap' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_SESSION_ID = $api_session->Login( 'demoadmin' . $dd->getUserNamePostfix(), 'demoZZZ' ); //Incorrect password.
			$this->assertEquals( false, $TIMETREX_SESSION_ID );
		}
	}

	/**
	 * @group testSOAPAPILoginWithAPIKeySessionID
	 */
	public function testSOAPAPILoginWithAPIKeySessionID() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'soap' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_API_KEY = $api_session->registerAPIKey( 'demoadmin' . $dd->getUserNamePostfix(), 'demo' );
			$this->assertNotEquals( false, $TIMETREX_API_KEY );
			Debug::text( 'API KEY Session ID: ' . $TIMETREX_SESSION_ID, __FILE__, __LINE__, __METHOD__, 10 );

			//  When running unit tests, there must be a web server available for on the same database this test to work.
			$headers = @get_headers( $TIMETREX_URL );
			if ( strpos( $headers[0], '200' ) !== false ) {
				//Make sure we can get the user record and the IDs match who we logged in as.
				$user_obj = new TimeTrexClientAPI( 'User' );
				$result = $user_obj->getUser( [ 'filter_data' => [ 'id' => $this->user_ids[0] ] ] );
				$user_data = $result->getResult();
				$this->assertEquals( $user_data[0]['id'], $this->user_ids[0] );
			}
		}
	}

	/**
	 * @group testSOAPAPILoginWithAPIKeySessionIDFail
	 */
	public function testSOAPAPILoginWithAPIKeySessionIDFail() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'soap' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_API_KEY = $api_session->registerAPIKey( 'demoadmin' . $dd->getUserNamePostfix(), 'demoZZZ' ); //Incorrect password.
			$this->assertEquals( false, $TIMETREX_API_KEY );
		}
	}



	/**
	 * @group testJSONAPILoginWithSessionID
	 */
	public function testJSONAPILoginWithSessionID() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'json' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$api_session->Login( 'demoadmin' . $dd->getUserNamePostfix(), 'demo' );
			$this->assertNotEquals( false, $TIMETREX_SESSION_ID );
			Debug::text( 'Session ID: ' . $TIMETREX_SESSION_ID, __FILE__, __LINE__, __METHOD__, 10 );

			//Make sure we can get the user record and the IDs match who we logged in as.
			$user_obj = new TimeTrexClientAPI( 'User' );
			$result = $user_obj->getUser( [ 'filter_data' => [ 'id' => $this->user_ids[0] ] ] );
			$user_data = $result->getResult();
			$this->assertEquals( $user_data[0]['id'], $this->user_ids[0] );

			$api_session = new TimeTrexClientAPI();
			$this->assertEquals( true, $api_session->isLoggedIn() );
			$this->assertEquals( true, $api_session->Logout() );
		}
	}

	/**
	 * @group testJSONAPILoginWithSessionIDFail
	 */
	public function testJSONAPILoginWithSessionIDFail() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'json' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_SESSION_ID = $api_session->Login( 'demoadmin' . $dd->getUserNamePostfix(), 'demoZZZ' ); //Incorrect password.
			$this->assertEquals( false, $TIMETREX_SESSION_ID );
		}
	}

	/**
	 * @group testJSONAPILoginWithAPIKeySessionID
	 */
	public function testJSONAPILoginWithAPIKeySessionID() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'json' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_API_KEY = $api_session->registerAPIKey( 'demoadmin' . $dd->getUserNamePostfix(), 'demo' );
			$this->assertNotEquals( false, $TIMETREX_API_KEY );
			Debug::text( 'API KEY Session ID: ' . $TIMETREX_SESSION_ID, __FILE__, __LINE__, __METHOD__, 10 );

			//Make sure we can get the user record and the IDs match who we logged in as.
			$user_obj = new TimeTrexClientAPI( 'User' );
			$result = $user_obj->getUser( [ 'filter_data' => [ 'id' => $this->user_ids[0] ] ] );
			$user_data = $result->getResult();
			$this->assertEquals( $user_data[0]['id'], $this->user_ids[0] );
		}
	}

	/**
	 * @group testJSONAPILoginWithAPIKeySessionIDFail
	 */
	public function testJSONAPILoginWithAPIKeySessionIDFail() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . Environment::getAPIURL( 'json' );
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$TIMETREX_API_KEY = $api_session->registerAPIKey( 'demoadmin' . $dd->getUserNamePostfix(), 'demoZZZ' ); //Incorrect password.
			$this->assertEquals( false, $TIMETREX_API_KEY );
		}
	}

	/**
	 * @group testJSONAPILoginWithSessionIDAndBadURL
	 */
	public function testJSONAPILoginWithSessionIDAndBadURL() {
		global $dd;

		global $TIMETREX_URL, $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
		$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = null; //Reset before logging in.
		$TIMETREX_URL = 'http://' . Environment::getHostName() . '/bogus_url_here';
		Debug::text( 'Remote API URL: ' . $TIMETREX_URL, __FILE__, __LINE__, __METHOD__, 10 );

		//  When running unit tests, there must be a web server available for on the same database this test to work.
		$headers = @get_headers( $TIMETREX_URL );
		if ( is_array( $headers ) && strpos( $headers[0], '404' ) === false ) {
			//Test using normal Session ID to ensure backwards compatibility.
			require_once( Environment::getBasePath() . '/classes/modules/api/client/TimeTrexClientAPI.class.php' );
			$api_session = new TimeTrexClientAPI();
			$api_session->Login( 'demoadmin' . $dd->getUserNamePostfix(), 'demo' );
			$this->assertEquals( false, $TIMETREX_SESSION_ID );
		}
	}
}

?>
