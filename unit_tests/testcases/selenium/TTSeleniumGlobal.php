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
 * Class TTSeleniumGlobal
 *  to diff between directories full of images: /etc/maint/unit_test_compare_screenshots.sh /home/joshr/public_html/tmp/UIScreenShotTest/10.0.3-124446 /home/joshr/public_html/tmp/UIScreenShotTest/10.0.3-125435 /home/joshr/public_html/tmp/UIScreenShotTest/10.0.3-125435_diff
 *
 * weird xpath examples:
// //*[starts-with(@id, 'ceil_')]

 */
class TTSeleniumGlobal extends PHPUnit_Extensions_Selenium2TestCase {

	private  $default_wait_timeout = 4000;//100000;
	private  $default_wait_interval = 50;

	public $width = 1440;
	public $height = 900;

	public function setUp() {
		global $selenium_config;
		$this->selenium_config = $selenium_config;

		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('Etc/GMT+8', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$this->setHost( $selenium_config['host'] );
		$this->setBrowser( $selenium_config['browser'] );
		$this->setBrowserUrl( $selenium_config['default_url'] );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function Login($user, $pass) {
		//disable the overlay to speed up testing

		Debug::text('Login to: '. $this->selenium_config['default_url'], __FILE__, __LINE__, __METHOD__, 10);
		$this->url( $this->selenium_config['default_url'] );

		$this->waitUntilById('user_name');

		$this->setUnitTestMode();
		$this->byId('user_name')->click();
		$this->keys($user);
		//$this->keys('demoadmin2');

		$this->byId('password')->click();
		$this->keys($pass);

		$this->byId('login_btn')->click();

		$this->waitForUIInitComplete();
		$this->waitUntilByCssSelector('#leftLogo:not(.login)'); //the css not() selector is there to differentiate the various calls in the server log.

		//needed as development mode reloads and clears the variables.
		$javascript = array('script' => 'Global.setUnitTestMode();', 'args' => array());
		$this->execute($javascript);

		Debug::text('Login Complete...', __FILE__, __LINE__, __METHOD__, 10);
	}

	function Logout() {
		//because we could want to log out from any point
		$this->goToDashboard();
		$this->waitForUIInitComplete();

		$this->waitUntilById('menu:myAccountMenu');
		$this->byId('menu:myAccountMenu')->click();

		$this->waitUntilById('Logout');
		$this->byId('Logout')->click();

		$this->waitUntilById('user_name');
		Debug::text('Logout...', __FILE__, __LINE__, __METHOD__, 10);
	}

	function waitUntilById( $id, $timeout = NULL, $sleep_interval = NULL ) {
		if ( $timeout == NULL ) {
			$timeout = $this->default_wait_timeout;
		}
		if ( $sleep_interval == NULL ) {
			$sleep_interval = $this->default_wait_interval;
		}

		$this->waitUntil( function () use ($id) {
			if ($this->byId($id)) {
				return TRUE;
			}
			return NULL;
		}, $timeout, $sleep_interval );
	}

	function waitUntilByCssSelector( $selector, $timeout = NULL, $sleep_interval = NULL ) {
		if ( $timeout == NULL ) {
			$timeout = $this->default_wait_timeout;
		}
		if ( $sleep_interval == NULL ) {
			$sleep_interval = $this->default_wait_interval;
		}

		$this->waitUntil( function () use ($selector) {
			if ( $this->byCssSelector( $selector ) ) {
				return TRUE;
			}
			return NULL;
		}, $timeout, $sleep_interval );
	}

	function takeScreenshot( $screenshot_file_name, $create_dir = TRUE ) {
		if ( $create_dir === TRUE ) {
			$dirname = dirname( $screenshot_file_name );
			if ( file_exists( $dirname ) == FALSE ) {
				mkdir( $dirname, 0777, TRUE );
			}
		}

		$this->waitForUIInitComplete();
		// get the mousepointer and focus away from hover effects and flashing cursors
		// these cause significant differences in the screenshots.
		$this->waitUntilById('rightLogo');
		$this->moveto($this->byId('rightLogo'));
		$this->waitUntilById('rightLogo');

		return file_put_contents( $screenshot_file_name, $this->currentScreenshot() );
	}

	function getOSUser() {
		if ( function_exists( 'posix_geteuid' ) AND function_exists( 'posix_getpwuid' ) ) {
			$user = posix_getpwuid( posix_geteuid() );
			Debug::Text( 'Webserver running as User: '. $user['name'], __FILE__, __LINE__, __METHOD__, 10 );

			return $user['name'];
		}
	}

	function goToDashboard() {
		$this->waitUntilByCssSelector('#leftLogo:not(.gotodashboard)');
		$this->byCssSelector('#leftLogo:not(.gotodashboard2)')->click();
		//dashboard will reliably use init_complete after everything is loaded.
		$this->waitForUIInitComplete();
	}

	function waitForUIInitComplete() {
		$this->waitUntil(function (){
			//Global.getUIReadyStatus will be == 2 when the screens are finished loading.
			$javascript = array('script' => 'return Global.getUIReadyStatus();', 'args' => array());
			$var = $this->execute($javascript);
			Debug::Text( 'waitForUI result: '. print_r($var, TRUE), __FILE__, __LINE__, __METHOD__, 10 );
			if ( isset($var) AND $var == 2) {
				return TRUE;
			}
			return NULL;
		}, 100000, 50);
	}

	function setUnitTestMode() {
		$sf = TTnew('StationFactory');
		$slf = TTnew('StationListFactory');

		$slf->getByStationId('UNITTEST');
		if ( $slf->getRecordCount() == 0 ) {
			$sf->setCompany( 2 ); //************************************************* make this dynamic when we programatically cycle through several users. this needs to be based off username.
			$sf->setStatus( 20 );
			$sf->setType( 10 );
			$sf->setDescription( 'Unit Testing Rig' );
			$sf->setStation( 'UNITTEST' );
			$sf->setSource( 'ANY' );
			if ( $sf->isValid() ) {
				$sf->Save();
			}
		}

		//run necessary js for unit tests
		$javascript = array('script' => 'Global.setUnitTestMode();', 'args' => array());
		$this->execute($javascript);

		$path = Environment::getCookieBaseURL();
		//enable detailed logging

		//set the same sessionid for all tests
		$javascript = array('script' => "$.cookie( 'StationID', 'UNITTESTS', {expires: 30, path: '$path'} );", 'args' => array());
		$javascript = array('script' => "Global.setStationID('UNITTEST')", 'args' => array());
		$this->execute($javascript);

	}

	function isThere( $css_selector ) {
		$result = $this->elements( $this->using( 'css selector' )->value( $css_selector ) );
		if ( count($result) > 0 ) {
			foreach ( $result as $el ) {
				if ( $el->displayed() AND $el->enabled() ) {
					return TRUE;
					break;
				}
			}
		}
		return FALSE;
	}

	function waitThenClick( $selector ) {
//		if ( stristr($selector, ':visible') == FALSE ) {
//			$selector .= ':visible';
//		}

		if ( substr( $selector, 0, 1 ) == '#' AND strstr( $selector, ' ' ) == FALSE ) {
			//need to do this because of malformed ids in the top menu causing wating by selector to fail.
			$id = substr( $selector, 1, strlen($selector) );
			Debug::Text( 'Waiting on id: '.$id, __FILE__, __LINE__, __METHOD__, 10 );
			$this->waitUntilById( $id, 10000 );
			Debug::Text( 'Clicking id: '.$id, __FILE__, __LINE__, __METHOD__, 10 );
			$this->byId($id)->click();
		} else {
			Debug::Text( 'Waiting on selector: '.$selector, __FILE__, __LINE__, __METHOD__, 10 );
			$this->waitUntilByCssSelector( $selector, 10000 );
			Debug::Text( 'Clicking selector: '.$selector, __FILE__, __LINE__, __METHOD__, 10 );
			$this->byCssSelector($selector)->click();
			Debug::Text( 'Done: '.$selector, __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	function getArrayBySelector( $css_selector ) {
		Debug::Text( 'Getting array by: '.$css_selector, __FILE__, __LINE__, __METHOD__, 10 );
		//$this->waitUntilByCssSelector( $css_selector,10000 );

		//http://stackoverflow.com/questions/16637806/select-all-matching-elements-in-phpunit-selenium-2-test-case
		$retval = $this->elements(
				$this->using( 'css selector' )->value( $css_selector )
		);
		if( isset($retval) ) {
			Debug::Text( count( $retval ) . ' RESULTS FOR: ' . $css_selector, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}
		return array();

	}
}