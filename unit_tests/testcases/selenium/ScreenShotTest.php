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
require_once( 'TTSeleniumGlobal.php' );

/**
 * @group UI
 */
class UIScreenShotTest extends TTSeleniumGlobal {

	public $user_name = '';
	public $screenshot_path = '';

	public function setUpPage() {
		//$this->currentWindow()->maximize();
		$this->currentWindow()->size( [ 'width' => $this->width, 'height' => $this->height ] );
	}

	function testUIScreenShot() {
		//$this->screenshot_path = DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . $this->getOSUser() . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'UIScreenShotTest' . DIRECTORY_SEPARATOR . APPLICATION_VERSION . '-' . date( 'Ymd-His' );
		$this->screenshot_path = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'UIScreenShotTest' . DIRECTORY_SEPARATOR . $this->getOSUser() . '-' . APPLICATION_VERSION . '-' . date( 'Ymd-His' );

		$user_login_info = [
				'demoadmin2' => 'demo.de',
				'john.doe2'  => 'demo.jo',
				'jane.doe2'  => 'demo.ja',
		];

		$resolution_array = [
				[ 'w' => 1920, 'h' => 1080 ],
				//array( 'w' => 1440, 'h' => 900), //Not serving much purpose at this time.
				[ 'w' => 1280, 'h' => 800 ],
				//array( 'w' => 1366, 'h' => 768 ),

				//array( 'w' => 320, 'h' => 568 ), //iphone crashes tests, can find anything (maybe because it's off screen?)
				//array( 'w' => 1027, 'h' => 728 ), //too small
				//array( 'w' => 1280 , 'h' => 720 ), //smallest?
		];

		//single entry point to make error trapping easier
		try {
			foreach ( $resolution_array as $resolution ) {
				$this->width = $resolution['w'];
				$this->height = $resolution['h'];
				$win = $this->currentWindow();
				$win->size( [ 'width' => $this->width, 'height' => $this->height ] );

				foreach ( $user_login_info as $user => $pass ) {
					Debug::Text( 'logging in as ' . $user, __FILE__, __LINE__, __METHOD__, 10 );
					$this->startTesting( $user, $pass );
					$this->assertEquals( true, true, 'Test Completed Successfully.' );
				}
			}
		} catch ( Exception $e ) {
			//Do not use $e->getTrace() here or there will be a very hard to diagnose infinite loop and memory exhaustion.
			Debug::Text( $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Arr( $e->getTraceAsString(), 'An error occcured while running automated testing. in ' . $e->getFile() . ' on line: ' . $e->getLine(), __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitForUIInitComplete();
			$this->takeScreenshot( $this->screenshot_path . DIRECTORY_SEPARATOR . 'error.png' );
			$this->assertEquals( false, true, 'The test exited with an error: ' . $e->getMessage() );

			$this->quit(); //Close browser.
		}
	}

	function startTesting( $user, $pass ) {
		$this->user_name = $user;
		//uncomment these to limit the tests to a specific top level menu
		//$debug_menu_item = 'block'; //used for testing user and resolution loops
		//$debug_menu_item = 'menu:attendance_menu';
		//$debug_menu_item = 'menu:employee_menu';
		//$debug_menu_item = 'menu:company_menu';
		//$debug_menu_item = 'menu:payroll_menu';
		//$debug_menu_item = 'menu:policy_menu';
		//$debug_menu_item = 'menu:invoiceMenu';
		//$debug_menu_item = 'menu:hr_menu';
		//$debug_menu_item = 'menu:reportMenu';
		//$debug_menu_item = 'menu:myAccountMenu';
		//$debug_menu_item = 'menu:helpMenu';

		$this->Login( $user, $pass );


		//In case users are set to timesheet as default screen, prevents crash
		$this->waitForUIInitComplete();
		$this->waitThenClick( '#leftLogo' );


		$menu_elements = $this->getArrayBySelector( '#ribbon ul li:not(.context-menu) a' );

		$menu_element_ids = [];
		// looping because we need the ids to check that the elements are still connected to the
		// DOM as they may not be when we get to processing them in this array
		foreach ( $menu_elements as $root_element ) {
			if ( $root_element->attribute( 'id' ) != '' ) {
				$menu_element_ids[] = $root_element->attribute( 'id' );
			}
		}

		unset( $menu_elements );
		$hit_debugger = false;

		foreach ( $menu_element_ids as $id ) {
			Debug::Text( 'ROOT MENU ELEMENT: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->byId( $id ) == false ) {
				Debug::Text( 'Menu item does not exist - A.', __FILE__, __LINE__, __METHOD__, 10 );
				continue;
			}

			$root_el = $this->byId( $id );

			if ( ( isset( $debug_menu_item ) == false && $this->byId( $id ) == false || $id == '' ) ||
					( isset( $debug_menu_item ) && $debug_menu_item != $id && $hit_debugger == false ) ) {

				Debug::Text( 'Menu item does not exist or debug limited - B.', __FILE__, __LINE__, __METHOD__, 10 );
				continue;
			} else {
				$hit_debugger = true; // comment this out to test just the debug item. Defualt is to test everything forward of specified debug menu item.
			}

			$resolution = $this->width . 'x' . $this->height;
			$menu_screenshot_path = $this->screenshot_path . DIRECTORY_SEPARATOR . $resolution . DIRECTORY_SEPARATOR . $this->user_name . DIRECTORY_SEPARATOR . $id;
			$screenshot_filename = $menu_screenshot_path . '.png';

			if ( $id == 'menu:company_menu' ) {
				$this->waitThenClick( '#' . $root_el->attribute( 'id' ) );
				//company and invoice need to start clean:
				$this->waitThenClick( '#PayPeriodSchedule' );
				$this->waitForUIInitComplete();
			} else if ( $id == 'menu:invoiceMenu' ) {
				$this->waitForUIInitComplete();
			}
			$this->waitThenClick( '#' . $root_el->attribute( 'id' ) );

			$this->waitForUIInitComplete();
			if ( $id != 'menu:helpMenu' && $id != 'menu:reportMenu' ) {
				Debug::Text( 'Processing Top Level Menu Element: [' . $id . '] screenshot filename: ' . $screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshot_filename );
				$this->processSubMenu( $id, $root_el, $menu_screenshot_path );
			}

			$this->waitForUIInitComplete();
			$this->waitThenClick( '#' . $root_el->attribute( 'id' ) );
		}


		Debug::Text( 'logging out', __FILE__, __LINE__, __METHOD__, 10 );
		$this->waitForUIInitComplete();
		$this->Logout();
		$this->waitForUIInitComplete();
	}

	function processSubMenu( $root_id, $root_el, $menu_screenshot_path ) {
		//array of submenus to limit testing to
		//$debug_sub_menu = array('PayPeriodSchedule');

		//array of sub elements in which we do not want to click any action icons.
		//these are mostly actions that have only save and cancel buttons
		$no_action_icons = [
				'inout',
				'userdefault',
				'company',
				'companybankaccount',
				'logout',

				//wizards
				'processpayrollwizard',
				'payrollremittanceagencyeventwizard',
				'importcsv',
				'quickstartwizard',

		];

		$sub_menu_container_id = str_replace( 'menu:', '', $root_id );
		Debug::Text( 'Processing Submenus at: #' . $sub_menu_container_id . ' .ribbon-sub-menu-icon', __FILE__, __LINE__, __METHOD__, 10 );

		$sub_menu_elements = $this->getArrayBySelector( '#ribbon #' . $sub_menu_container_id . ' .ribbon-sub-menu-icon' );
		$sub_menu_ids = [];
		//blank or invalid ids should not be clicked (for example, don't log out before the test is done)
		if ( isset( $debug_sub_menu ) ) {
			$sub_menu_ids = $debug_sub_menu;
		} else {
			foreach ( $sub_menu_elements as $sub_el ) {
				if ( $sub_el->attribute( 'id' ) != '' ) {
					$sub_menu_ids[] = $sub_el->attribute( 'id' );
				}
			}
		}
		unset( $sub_menu_elements );

		Debug::Arr( $sub_menu_ids, 'Found the following sub menus in: ' . $root_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( count( $sub_menu_ids ) > 0 ) {
			foreach ( $sub_menu_ids as $id ) {
				$root_el = $this->byId( $root_el->attribute( 'id' ) ); //Refresh root element to avoid: stale element reference: element is not attached to the page document

				$css_sel = '#' . $sub_menu_container_id . ' #' . $id;
				if ( $id == 'Logout' || $id == '' || $this->byId( $id ) == false ) {
					Debug::Text( 'Menu item does not exist or debug limited. - D.', __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				Debug::Text( 'Processing Submenus for selector: ' . $css_sel, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitThenClick( $css_sel );

				Debug::Text( $css_sel . ' submenu clicked.', __FILE__, __LINE__, __METHOD__, 10 );

				$submenu_screenshot_path = $menu_screenshot_path . DIRECTORY_SEPARATOR . $id;
				$submenu_screenshot_filename = $submenu_screenshot_path . '.png';

				$this->waitForUIInitComplete();

				Debug::Text( 'Taking screenshot for submenu element: ' . $id . ' screenshot filename: ' . $submenu_screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $submenu_screenshot_filename );

				if ( in_array( strtolower( $id ), $no_action_icons ) === false ) {
					$this->processContextIcons( $submenu_screenshot_path, $root_el, $this->byId( $id ) );
				}

				$this->waitForUIInitComplete();

				//cleanup etc before going to the next submenu
				switch ( strtolower( $id ) ) {
					case 'company':
					case 'changepassword':
					case 'invoiceconfig':
						$this->clickCancel( $id . 'ContextMenu' );
						break;
					case 'companybankaccount':
						$this->processTabs( $submenu_screenshot_path, $root_id, $sub_el->attribute( 'id' ) );
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, false );

						$this->clickCancel( $sub_el->attribute( 'id' ) );
						break;
					case 'inout':
						$this->clickCancel( $id . 'ContextMenu' );
						$this->waitThenClick( '#yesBtn' );
						break;
					case 'processpayrollwizard':
					case 'payrollremittanceagencyeventwizard':
					case 'importcsv':
						$this->waitThenClick( '.wizard .close-btn' );
						break;
					case 'quickstartwizard':
						$this->waitThenClick( '.wizard .close-btn' );

						//might need this for a fresh install, but not after shutting off quick start nag screen.
//						if ( $this->byId('yesBtn') ) {
//							$this->byId('yesBtn')->click();
//						}
						break;
					case 'paystubtransactionicon':
					case 'punches':
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, false );
						break;
					case 'documentgroup':
					case 'governmentdocument':
					case 'timesheet':
					case 'payperiodschedule':
					case 'schedule':
						break;
					default:
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, false );
						break;
				}

				$this->waitForUIInitComplete();

				Debug::Text( 'Reset to the top-level menu: ' . $root_id, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitThenClick( '#' . $root_id );

				$this->waitForUIInitComplete();
			}
		}
	}

	function clickRootAndSub( $root_el, $sub_el ) {
		$this->waitForUIInitComplete();
		$this->waitThenClick( '#' . $root_el->attribute( 'id' ) );
		$this->waitForUIInitComplete();
		$this->waitThenClick( '#' . $sub_el->attribute( 'id' ) );
		$this->waitForUIInitComplete();

		return true;
	}

	function clickMinimizedWindow() {
		Debug::Text( 'Clicking minimized tab...', __FILE__, __LINE__, __METHOD__, 10 );
		$this->waitThenClick( '.view-min-tab' );
		sleep( 1 ); //For some reason without this we get this exception often when going to Payroll -> Pay Stubs, Pay Stub Transaction, then clicking the minimized window: #ribbon .context-menu a - stale element reference: element is not attached to the page document

		return true;
	}

	function processContextIcons( $path, $root_el, $sub_el ) {
		Debug::Text( 'processContextIcons: Root ID: ' . $root_el->attribute( 'id' ) . ' Sub Element: ' . $sub_el->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
		//$context_menu_debug_id =  'editclienticon';

		$root_id = $root_el->attribute( 'id' );
		$sub_el_id = $sub_el->attribute( 'id' );

		//array of action icons we do not wish to click
		$skip_array = [
			//no need
			'overrideicon',
			'swapicon',
			'saveicon',
			'deleteicon',
			'cancelicon',
			'saveandnewicon',
			'dragcopyicon',
			'moveicon',
			'importicon',
			'importcsvicon',
			'exportexcelicon',
			'printicon',
			'exportexcelicon',
			'migratepaystubaccount',
			'inboxicon',
			'senticon',
			'authorizationrequesticon',
			'authorizationtimesheeticon',
			'authorizationexpenseicon',
			'sharereporticon',

			//disabled for now due to bugs
			//all of these have submenu icons and the context icons are not working yet
			'recalculatetimesheet',
			'clientcontacticon',
			'invoiceicon',
			'transactionicon',
			'paymentmethodicon',

			'generatepaystub',
			'accumulatedtimeicon',
		];

		//$this->clickRootAndSub( $root_el, $sub_el );
		$this->waitForUIInitComplete();

		//Make sure the context menu matches the expected one.
		$this->waitUntil( function () use ( $sub_el ) {
			$this->waitUntilByCssSelector( '#ribbon .context-menu a' );
			$context_menu_id = $this->byCssSelector( '#ribbon .context-menu a' )->attribute( 'ref' );
			$sub_element_id = $sub_el->attribute( 'id' ) . 'ContextMenu';
			Debug::Text( 'Context menu should match:  ID: ' . $context_menu_id . ' does not match: ' . $sub_element_id, __FILE__, __LINE__, __METHOD__, 10 );
			if ( $context_menu_id == $sub_element_id ) {
				return true;
			} else {
				Debug::Text( 'Waiting for context menu to switch to expected one:  ID: ' . $context_menu_id . ' does not match: ' . $sub_element_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return null;
		}, 2000 );

		//get the current view id from js
		$this->waitUntilByCssSelector( '#ribbon .context-menu a' );
		$context_menu_id = $this->byCssSelector( '#ribbon .context-menu a' )->attribute( 'ref' );

		$action_icon_elements = $this->getArrayBySelector( '#' . $context_menu_id . ' li:not(.disable-image):not(.invisible-image) .ribbon-sub-menu-icon' );
		$elements = [];
		foreach ( $action_icon_elements as $el ) {
			$elements[] = '#' . $context_menu_id . ' #' . $el->attribute( 'id' );
		}
		unset( $action_icon_elements );

		foreach ( $elements as $selector ) {
			if ( $this->isThere( $selector ) == false ) {
				continue;
			}
			$el = $this->byCssSelector( $selector );
			$id = $el->attribute( 'id' );
			if ( in_array( strtolower( $id ), $skip_array ) == false ) {
				if ( !$this->byCssSelector( $selector ) || ( !isset( $context_menu_debug_id ) || stristr( strtolower( $selector ), $context_menu_debug_id ) ) == false ) {
					$this->waitForUIInitComplete();
					continue;
				}

				//not in the skip list and not disabled or invisible.


				Debug::Text( 'Processing ID: ' . $id . ': ' . $selector, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->isThere( '#ribbon .context-menu a' ) ) {
					Debug::Text( 'Clicking: Context menu.', __FILE__, __LINE__, __METHOD__, 10 );
					$this->waitUntilByCssSelector( '#ribbon .context-menu a' );
					$this->waitThenClick( '#ribbon .context-menu a' );
				} else {
					Debug::Text( 'Clicking: Root and Sub element.', __FILE__, __LINE__, __METHOD__, 10 );
					$this->clickRootAndSub( $root_el, $sub_el );
				}

				$this->waitForUIInitComplete();

				Debug::Text( 'Clicking: #' . $id, __FILE__, __LINE__, __METHOD__, 10 );

				$this->waitThenClick( $selector );
				$this->waitForUIInitComplete();
				$this->waitUntilByCssSelector( '#ribbon .context-menu a' );

				Debug::Text( '********   Taking screenshot for context menu element: ' . $id . ' screenshot filename: ' . $path . DIRECTORY_SEPARATOR . $id . '.png', __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $path . DIRECTORY_SEPARATOR . $id . '.png', true );

				//process tabs on applicable views
				$this->waitForUIInitComplete();
				//after screenshot is taken, some views need custom closure code
				switch ( strtolower( $id ) ) {
					case 'mapicon':
						//case 'editemployeeicon':
						$this->clickCancel();
						break;
					case 'inouticon':
						Debug::Text( 'Shutting down an inout screen: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$this->clickCancel( 'InOutContextMenu' );
						$this->waitThenClick( '#yesBtn' );
						break;
					case 'scheduleicon':
					case 'paystubicon':
						$this->clickMinimizedWindow();
						break;
					case 'clientcontacticon':
					case 'invoiceicon':
					case 'transactionicon':
					case 'paymentmethodicon':
						$this->processTabs( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id );
						$this->clickMinimizedWindow();
						break;
					case 'recalculatetimesheet':
					case 'generatepaystub':
					case 'jobinvoiceicon':
					case 'importcsv':
					case 'importicon':
					case 'quickstartwizard':
					case 'migratepaycodeicon':
						$this->waitThenClick( '.wizard .close-btn' );
						Debug::Text( 'closing wizard', __FILE__, __LINE__, __METHOD__, 10 );
						break;
					case 'accumulatedtimeicon':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id , $sub_el_id, $el->attribute('id'));
						break;
					case 'paystubtransactionicon':
						Debug::Text( 'paystubtransaction context icon...', __FILE__, __LINE__, __METHOD__, 10 );
						$this->clickMinimizedWindow();
						break;
//					case 'remittancesourceaccount':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id, TRUE );
//						break;
					default:
						$this->processTabs( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id, true );

						//no hashtag
						$this->clickCancel();
//						if ( $this->byCssSelector('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon') ) {
//							Debug::Text( 'clicking: #cancelIcon.', __FILE__, __LINE__, __METHOD__, 10 );
//
//							$this->waitThenClick('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon' );
//						}
				}

				//Make sure that cancel icon is not invisible or disabled.
				$this->waitForUIInitComplete();
			}
		}

		Debug::Text( 'Done! Clicking context menu now...', __FILE__, __LINE__, __METHOD__, 10 );
		$this->waitThenClick( '#ribbon .context-menu a' );
		$this->waitForUIInitComplete();
	}

	function processTabs( $path, $root_id, $sub_id = false, $el_id = false, $is_edit_view = false ) {

		Debug::Text( 'Determining tab id:', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $el_id != false ) {
			$id = $el_id;
		} else if ( $sub_id != false ) {
			$id = $sub_id;
		} else {
			$id = $root_id;
		}
		Debug::Text( 'Retrieving Tabs for: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		$css_selectors = $this->getTabs( $id, $sub_id );

		Debug::Text( 'Processing Tabs for: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		$skip_first = false;
		if ( count( $css_selectors ) > 1 ) {
			foreach ( $css_selectors as $name => $tab_el ) {
				echo "'Processing Tab: ' . $name";
				Debug::Text( 'Processing Tab: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $skip_first === false ) {
					$skip_first = true;
					continue;
				}

				$this->waitThenClick( $tab_el );

				$this->waitForUIInitComplete();

				if ( $is_edit_view != false ) {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . 'edit_view_' . $name . '.png';
				} else {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . $name . '.png';
				}
				Debug::Text( 'Taking screenshot for Tab: ' . $name . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshotFileName, true );
			}


			//Should this ever change to an iterator, you'll need to rework this as reset will only return the first element if the argument is an array.
			$this->waitThenClick( reset( $css_selectors ) ); //click first tab before cancel.

			Debug::Text( 'Clicking Cancel at end of processTabs()', __FILE__, __LINE__, __METHOD__, 10 );
			$this->clickCancel( $sub_id . 'ContextMenu' );
		} else {
			$this->waitForUIInitComplete();
		}
	}

	function getTabs( $clicked_icon_name, $sub_menu_id ) {
		$ignore_array = [
			//causes crash, does not have more than one tab we care about.
			'loginuserbankaccount',
			'inouticon',
			'timesheet',
			'schedule',

			//wizards
			'recalculatetimesheet',
			'generatepaystub',
			'jobinvoiceicon',
			'importcsv',
			'importicon',
			'quickstartwizard',
			'migratepaycodeicon',

			'processpayrollwizard',
			'payrollremittanceagencyeventwizard',
		];

		if ( in_array( strtolower( $clicked_icon_name ), $ignore_array ) == true ) {
			Debug::Text( 'Not even looking at tabs', __FILE__, __LINE__, __METHOD__, 10 );

			return [];
		}

		$this->waitForUIInitComplete();

		$css_selector = '.edit-view.' . $sub_menu_id . 'EditView .edit-view-tab-bar .ui-tabs-nav a';

		if ( $sub_menu_id == 'TimeSheet' &&
				( strtolower( $clicked_icon_name ) == 'addicon' || strtolower( $clicked_icon_name ) == 'addabsenceicon' ) ) {
			$css_selector = '.edit-view.timesheet-editview .edit-view-tab-bar .ui-tabs-nav a';
		}
		Debug::Text( 'tab find css: ' . $css_selector, __FILE__, __LINE__, __METHOD__, 10 );
		$tabs = $this->getArrayBySelector( $css_selector );

		$tab_css_selectors = [];
		foreach ( $tabs as $el ) {
			Debug::Text( 'Noticing Tab.' . $el->attribute( 'ref' ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->isThere( '#' . $el->attribute( 'id' ) ) ) {
				$tab_css_selectors[$el->attribute( 'ref' )] = '.edit-view a#' . $el->attribute( 'id' );
			}
		}
		unset( $tabs );

		return $tab_css_selectors;
	}

	function processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, $context_el ) {
		Debug::Text( 'Looking At Edit View: ' . $root_el->attribute( 'id' ) . ' Sub View: ' . $sub_el->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
		$root_id = $root_el->attribute( 'id' );
		$sub_el_id = $sub_el->attribute( 'id' );
		if ( $context_el != false ) {
			$context_id = $context_el->attribute( 'id' );
		} else {
			$context_id = $sub_el->attribute( 'id' );
		}

		$ignore_list = [
			//popovers that have grids under them so the grid is found and causes a crash.
			'inout',
			'changepassword',
			'userdefault',

			//overly complex grids that will need special considerations:
			'timesheet',
			'schedule',

			//treeview grids can't be opened for edit with a doubleclick but trigger our grid detector.
			'jobgroup',
			'jobitemgroup',
			'usergroup',
			'qualificationgroup',
			'kpigroup',
			'documentgroup',
			'clientgroup',
			'productgroup',
			'exception',
			'companybankaccount',
			'company',
		];

		if ( ( isset( $sub_el ) && in_array( strtolower( $sub_el->attribute( 'id' ) ), $ignore_list ) ) ||
				( isset( $sub_el ) && in_array( strtolower( $sub_el->attribute( 'id' ) ), $ignore_list ) && isset( $context_el ) && in_array( strtolower( $context_el->attribute( 'id' ) ), $ignore_list ) ) ) {
			Debug::Text( 'Skipping due to ignore list...', __FILE__, __LINE__, __METHOD__, 10 );

			return;
		}

		$css_selector = '.grid-div .ui-jqgrid .ui-jqgrid-btable tr:nth-child(2) td:nth-child(2)';
		$this->waitForUIInitComplete();
		if ( $this->isThere( '.grid-div .no-result-div' ) === false && $this->isThere( $css_selector ) === true && $this->isThere( '#' . $sub_el->attribute( 'id' ) . 'ContextMenu #viewIcon' ) ) {
			Debug::Text( 'Processing Edit View: ' . $root_el->attribute( 'id' ) . '=>' . $sub_el->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitThenClick( $css_selector );
			if ( strtolower( $sub_el_id ) == 'paystub' || strtolower( $sub_el_id ) == 'invoice' ) {
				$this->waitThenClick( '#' . $sub_el->attribute( 'id' ) . 'ContextMenu #editIcon' );
			} else {
				$this->waitThenClick( '#' . $sub_el->attribute( 'id' ) . 'ContextMenu #viewIcon' );
			}

			$this->waitForUIInitComplete();
			$screenshotFileName = $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_' . $context_id . '.png';

			Debug::Text( 'Taking screenshot for Edit View: ' . $root_id . '=>' . $sub_el_id . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
			$this->takeScreenshot( $screenshotFileName );
			$this->processTabs( $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_' . $context_id, $root_id, $sub_el_id, $context_id, true );
			//does every edit have a cancel? NO. Exceptions is the exception, so it's in the ignore_list.
			Debug::Text( 'Clicking Cancel at end of processEditScreen()', __FILE__, __LINE__, __METHOD__, 10 );
			//$this->clickCancel($sub_el_id);
			$this->waitForUIInitComplete();

			$this->waitThenClick( '#ribbon .context-menu a' );
		}

		Debug::Text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );
	}
}

?>