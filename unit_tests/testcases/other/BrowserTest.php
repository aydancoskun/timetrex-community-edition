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
 * @group Browser
 */
class BrowserTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( !class_exists( 'Browser', false ) ) {
			require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
		}
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function testBrowserIE() {
		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/2)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '7.0', $browser->getVersion() ); //Use Trident Version

		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '7.0', $browser->getVersion() ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; LEN2)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '9.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '11.0', $browser->getVersion() ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '6.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '6.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '7.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; c .NET CLR 3.0.04506; .NET CLR 3.5.30707; InfoPath.1; el-GR)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '7.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '8.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '8.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '9.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '9.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '10.0', $browser->getVersion() );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)' );
		$this->assertEquals( Browser::BROWSER_IE, $browser->getBrowser() );
		$this->assertEquals( '10.0', $browser->getVersion() ); //Take MSIE over Trident here. -- Internet Explorer 8 on Windows 7, Internet Explorer 10 Compatibility View

		return true;
	}

	function testBrowserIOS() {
		$browser = new Browser( 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/21.0 Mobile/16B92 Safari/605.1.15' );
		$this->assertEquals( Browser::BROWSER_FIREFOX, $browser->getBrowser() );
		$this->assertEquals( true, $browser->isMobile() );
		$this->assertEquals( '21.0', $browser->getVersion() );

		return true;
	}

	function testDetectMobileBrowser() {
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)' ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36' ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134' ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0' ) );

		$this->assertEquals( true, Misc::detectMobileBrowser( 'Mozilla/5.0 (Linux; Android 8.0.0; SM-N9500 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)' ) );
		$this->assertEquals( true, Misc::detectMobileBrowser( 'Mozilla/5.0 (Android 9; Mobile; rv:68.0) Gecko/68.0 Firefox/68.0' ) );
		$this->assertEquals( true, Misc::detectMobileBrowser( 'Mozilla/5.0 (Linux; Android 7.1.2; AFTMM Build/NS6265; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/70.0.3538.110 Mobile Safari/537.36' ) );
		$this->assertEquals( true, Misc::detectMobileBrowser( 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/21.0 Mobile/16B92 Safari/605.1.15' ) );
		$this->assertEquals( true, Misc::detectMobileBrowser( 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.5 Mobile/15E148 Safari/604.1' ) );

		return true;
	}

	function testDetectMobileBrowserPlatform() {
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)', true ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36', true ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134', true ) );
		$this->assertEquals( false, Misc::detectMobileBrowser( 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0', true ) );

		$this->assertEquals( 'android', Misc::detectMobileBrowser( 'Mozilla/5.0 (Linux; Android 8.0.0; SM-N9500 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)', true ) );
		$this->assertEquals( 'android', Misc::detectMobileBrowser( 'Mozilla/5.0 (Android 9; Mobile; rv:68.0) Gecko/68.0 Firefox/68.0', true ) );
		$this->assertEquals( 'android', Misc::detectMobileBrowser( 'Mozilla/5.0 (Linux; Android 7.1.2; AFTMM Build/NS6265; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/70.0.3538.110 Mobile Safari/537.36', true ) );
		$this->assertEquals( 'ios', Misc::detectMobileBrowser( 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/21.0 Mobile/16B92 Safari/605.1.15', true ) );
		$this->assertEquals( 'ios', Misc::detectMobileBrowser( 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.5 Mobile/15E148 Safari/604.1', true ) );

		return true;
	}

}

?>