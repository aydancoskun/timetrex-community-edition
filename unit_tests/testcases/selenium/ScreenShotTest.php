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
require_once( 'TTSeleniumGlobal.php' );
/**
 * @group UI
 */
class UIScreenShotTest extends TTSeleniumGlobal  {

	public $user_name = '';
	public $screenshot_path = '';

	public function setUpPage() {
		//$this->currentWindow()->maximize();
		$this->currentWindow()->size(array('width' => $this->width, 'height' => $this->height ));

	}

	function testUIScreenShot() {
		$this->screenshot_path = DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . $this->getOSUser() . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'UIScreenShotTest' . DIRECTORY_SEPARATOR . APPLICATION_VERSION.'-'.date('His');

		$user_login_info = array(
				'demoadmin2' => 'demo.de',
				'john.doe2' => 'demo.jo',
				'jane.doe2' => 'demo.ja',
		);

		$resolution_array = array(
				//array( 'w' => 320, 'h' => 568 ), //iphone crashes tests, can find anything (maybe because it's off screen?)
				//array( 'w' => 1027, 'h' => 728 ), //too small

	//			array( 'w' => 1280 , 'h' => 720 ), //smallest?
				array( 'w' => 1920, 'h' => 1080 ),
//				array( 'w' => 1366, 'h' => 768 ),
				array( 'w' => 1440, 'h' => 900 ),
				array( 'w' => 1280, 'h' => 800 ),
		);

		//single entry point to make error trapping easier
		try {
			foreach ($resolution_array as $resolution) {
				$this->width = $resolution['w'];
				$this->height = $resolution['h'];
				$win = $this->currentWindow();
				$win->size(array('width' => $this->width, 'height' => $this->height));

				foreach ( $user_login_info as $user => $pass ) {
					Debug::Text('logging in as '.$user, __FILE__, __LINE__, __METHOD__, 10);
					$this->startTesting( $user, $pass );
					$this->assertEquals( TRUE, TRUE, 'Test Completed Successfully.' );
				}
			}
		} catch ( Exception $e ) {
			//Do not use $e->getTrace() here or there will be a very hard to diagnose infinite loop and memory exhaustion.
			Debug::Text($e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
			Debug::Arr($e->getTraceAsString(), 'An error occcured while running automated testing. in '.$e->getFile().' on line: '. $e->getLine(), __FILE__, __LINE__, __METHOD__, 10);
			$this->waitForUIInitComplete();
			$this->takeScreenshot( $this->screenshot_path .DIRECTORY_SEPARATOR. 'error.png' );
			$this->assertEquals( FALSE, TRUE, 'The test exited with an error: '.$e->getMessage() );
			$this->quit();
		}

	}

	function startTesting ( $user, $pass ) {
		$this->user_name = $user;
		//uncomment these to limit the tests to a specific top level menu
		//$debug_menu_item = 'block'; //used for testing user and resolution loops
		//$debug_menu_item = 'menu:attendance_menu';
		//$debug_menu_item = 'menu:company_menu';
		//$debug_menu_item = 'menu:employee_menu';
		//$debug_menu_item = 'menu:payroll_menu';
		//$debug_menu_item = 'menu:policy_menu';
		//$debug_menu_item = 'menu:invoiceMenu';
		//$debug_menu_item = 'menu:report_menu';
		//$debug_menu_item = 'menu:hr_menu';
		//$debug_menu_item = 'menu:reportMenu';
		//$debug_menu_item = 'menu:myAccountMenu';
		//$debug_menu_item = 'menu:helpMenu';

		$this->Login($user, $pass);


		//In case users are set to timesheet as default screen, prevents crash
		$this->waitForUIInitComplete();
		$this->waitThenClick('#leftLogo');



		$menu_elements = $this->getArrayBySelector('#ribbon ul li:not(.context-menu) a');

		$menu_element_ids = array();
		// looping because we need the ids to check that the elements are still connected to the
		// DOM as they may not be when we get to processing them in this array
		foreach ( $menu_elements as $root_element ) {
			if ( $root_element->attribute( 'id' ) != '') {
				$menu_element_ids[] = $root_element->attribute( 'id' );
			}
		}

		unset($menu_elements);
		$hit_debugger = FALSE;

		foreach ( $menu_element_ids as $id ) {
			echo "ID ".$id;
			Debug::Text( 'ROOT MENU ELEMENT: '.$id, __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->byId($id) == FALSE ) {
				Debug::Text( 'Menu item does not exist - A.', __FILE__, __LINE__, __METHOD__, 10);
				continue;
			}

			$root_el = $this->byId($id);

			if ( (isset( $debug_menu_item ) == FALSE AND $this->byId( $id ) == FALSE OR $id == '') OR
				 ( isset( $debug_menu_item ) AND $debug_menu_item != $id AND $hit_debugger == FALSE ) ) {

				Debug::Text( 'Menu item does not exist or debug limited - B.', __FILE__, __LINE__, __METHOD__, 10);
				continue;
			} else {
				$hit_debugger = TRUE; // comment this out to test just the debug item. Defualt is to test everything forward of specified debug menu item.
			}

			$resolution = $this->width .'x'.$this->height;
			$menu_screenshot_path = $this->screenshot_path . DIRECTORY_SEPARATOR .$resolution. DIRECTORY_SEPARATOR . $this->user_name. DIRECTORY_SEPARATOR . $id;
			$screenshot_filename = $menu_screenshot_path. '.png';

			if ( $id == 'menu:company_menu' ) {
				$this->waitThenClick('#'.$root_el->attribute('id'));
				//company and invoice need to start clean:
				$this->waitThenClick('#PayPeriodSchedule');
				$this->waitForUIInitComplete();
			} elseif($id == 'menu:invoiceMenu') {
				$this->waitForUIInitComplete();
			}
			$this->waitThenClick('#'.$root_el->attribute('id'));

			$this->waitForUIInitComplete();
			if ( $id != 'menu:helpMenu' AND $id != 'menu:reportMenu'  ) {
				Debug::Text( 'Processing Top Level Menu Element: [' . $id . '] screenshot filename: '.$screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshot_filename );
				$this->processSubMenu( $id, $root_el, $menu_screenshot_path );
			}

			$this->waitForUIInitComplete();
			$this->waitThenClick('#'.$root_el->attribute('id'));
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
		$no_action_icons = array(
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

		);

		$sub_menu_container_id = str_replace( 'menu:', '', $root_id );
		Debug::Text( 'Processing Submenus at: #'.$sub_menu_container_id.' .ribbon-sub-menu-icon', __FILE__, __LINE__, __METHOD__, 10 );

		$sub_menu_elements = $this->getArrayBySelector('#ribbon #'.$sub_menu_container_id.' .ribbon-sub-menu-icon');
		$sub_menu_ids = array();
		//blank or invalid ids should not be clicked (for example, don't log out before the test is done)
		if ( isset($debug_sub_menu) ) {
			$sub_menu_ids = $debug_sub_menu;
		} else {
			foreach ( $sub_menu_elements as $sub_el ) {
				if( $sub_el->attribute( 'id' ) != '') {
					$sub_menu_ids[] = $sub_el->attribute('id');
				}
			}
		}
		unset($sub_menu_elements);

		Debug::Arr($sub_menu_ids, 'Spotted The Following Sub menus in '.$root_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( count($sub_menu_ids) > 0 ) {
			foreach ( $sub_menu_ids as $id ) {

				$css_sel = '#'.$sub_menu_container_id.' #'.$id;
				if ( $id == 'Logout' OR $id == '' OR $this->byId($id) == FALSE ) {
					Debug::Text( 'Menu item does not exist or debug limited. - D.', __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				Debug::Text( 'Processing Submenus for: '.$css_sel, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitThenClick($css_sel);

				Debug::Text( $css_sel.' submenu clicked.', __FILE__, __LINE__, __METHOD__, 10 );

				$submenu_screenshot_path = $menu_screenshot_path . DIRECTORY_SEPARATOR . $id;
				$submenu_screenshot_filename = $submenu_screenshot_path . '.png';

				$this->waitForUIInitComplete();

				Debug::Text( 'Taking screenshot for submenu element: ' . $id . ' screenshot filename: ' . $submenu_screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $submenu_screenshot_filename );

				if ( in_array(strtolower($id), $no_action_icons) === FALSE ) {
					$this->processContextIcons( $submenu_screenshot_path, $root_el, $this->byId($id) );
				}

				$this->waitForUIInitComplete();

				//cleanup etc before going to the next submenu
				switch ( strtolower($id) ) {
					case 'company':
					case 'changepassword':
					case 'invoiceconfig':
						$this->clickCancel($id.'ContextMenu');
						break;
					case 'companybankaccount':
					case 'invoiceconfig':
						$this->processTabs ( $submenu_screenshot_path, $root_id, $sub_el->attribute('id') );
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, FALSE);

						$this->clickCancel( $sub_el->attribute('id') );
						break;
					case 'inout':
						$this->clickCancel($id. 'ContextMenu' );
						$this->waitThenClick('#yesBtn');
						break;
					case 'processpayrollwizard':
					case 'payrollremittanceagencyeventwizard':
					case 'importcsv':
						$this->waitThenClick('.wizard .close-btn');
						break;
					case 'quickstartwizard':
						$this->waitThenClick('.wizard .close-btn');

						//might need this for a fresh install, but not after shutting off quickstat nag screen.
//						if ( $this->byId('yesBtn') ) {
//							$this->byId('yesBtn')->click();
//						}
						break;
					case 'paystubtransactionicon':
					case 'punches':
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, FALSE);
						//none;
						$this->waitForUIInitComplete();
						break;
					case 'documentgroup':
					case 'governmentdocument':
					case 'timesheet':
						$this->waitForUIInitComplete();
						break;
					case 'PayPeriodSchedule':
					case 'schedule':
						$this->waitForUIInitComplete();
						break;
					default:
						//$this->processTabs ( $submenu_screenshot_path, $root_id, $sub_el->attribute('id'));
						$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, FALSE);
						//none;
						$this->waitForUIInitComplete();
						break;
				}

				$this->waitForUIInitComplete();
				Debug::Text( 'reset to the top-level menu: '.$root_id, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitUntilById( $root_id );
				$this->waitThenClick('#'.$root_el->attribute('id'));
				$this->waitForUIInitComplete();
			}
		}
	}

	function clickRootAndSub ($root_el, $sub_el) {
		$this->waitForUIInitComplete();
		$this->waitThenClick('#'.$root_el->attribute('id'));
		$this->waitForUIInitComplete();
		$this->waitThenClick('#'.$sub_el->attribute('id'));
		$this->waitForUIInitComplete();
	}

	function processContextIcons ( $path, $root_el, $sub_el) {

		Debug::Text( 'processContextIcons: '.$root_el->attribute('id').' '.$sub_el->attribute('id'), __FILE__, __LINE__, __METHOD__, 10 );
		//$context_menu_debug_id =  'editclienticon';

		$root_id = $root_el->attribute('id');
		$sub_el_id = $sub_el->attribute('id');
		//array of action icons we do not wish to click
		$skip_array = array(
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
		);

		//$this->clickRootAndSub( $root_el, $sub_el );
		$this->waitForUIInitComplete();
		//get the current view id from js

		$this->waitUntilByCssSelector('.context-menu a');
		$context_menu_id = $this->byCssSelector('.context-menu a')->attribute('ref');
		$action_icon_elements = $this->getArrayBySelector( '#'.$context_menu_id.' li:not(.disable-image):not(.invisible-image) .ribbon-sub-menu-icon' );
		$elements = array();
		foreach ( $action_icon_elements as $el ) {
			$elements[] = '#'.$context_menu_id.' #'.$el->attribute('id');
		}
		unset($action_icon_elements);

		foreach ( $elements as $selector ) {
			if ( $this->isThere($selector) == FALSE ) {
				continue;
			}
			$el = $this->byCssSelector($selector);
			$id = $el->attribute('id');
			if ( in_array(strtolower($id), $skip_array) == FALSE ) {
				if ( !$this->byCssSelector($selector) OR (!isset( $context_menu_debug_id ) OR stristr( strtolower($selector), $context_menu_debug_id )) == FALSE ) {
					$this->waitForUIInitComplete();
					continue;
				}

				//not in the skip list and not disabled or invisible.


				Debug::Text( 'processing #'.$id.': '.$selector, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->isThere('#ribbon .context-menu a') ) {
					Debug::Text( 'clicking: context menu.', __FILE__, __LINE__, __METHOD__, 10 );
					$this->waitUntilByCssSelector('#ribbon .context-menu a');
					$this->waitThenClick('#ribbon .context-menu a');

				} else {
					Debug::Text( 'clicking: root and sub.', __FILE__, __LINE__, __METHOD__, 10 );
					$this->clickRootAndSub( $root_el, $sub_el );
				}

				$this->waitForUIInitComplete();

				Debug::Text( 'clicking: #' .$id, __FILE__, __LINE__, __METHOD__, 10 );

				$this->waitThenClick($selector);
				$this->waitForUIInitComplete();
				$this->waitUntilByCssSelector('#ribbon .context-menu a');

				Debug::Text( '********   Taking screenshot for context menu element: ' . $id . ' screenshot filename: ' . $path . DIRECTORY_SEPARATOR . $id . '.png', __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $path . DIRECTORY_SEPARATOR . $id.  '.png', TRUE );

				//process tabs on applicable views
				$this->waitForUIInitComplete();
				//after screenshot is taken, some views need custom closure code
				switch ( strtolower($id) ) {
					case 'mapicon':
					//case 'editemployeeicon':
						$this->clickCancel();
						break;
					case 'inouticon':
						Debug::Text( 'shutting down an inout screen.'.$id, __FILE__, __LINE__, __METHOD__, 10 );

						$this->clickCancel('InOutContextMenu');
						$this->waitThenClick('#yesBtn');
						break;
					case 'scheduleicon':
					case 'paystubicon':
						$this->waitThenClick('.view-min-tab');
						Debug::Text( 'clicking minimized tab', __FILE__, __LINE__, __METHOD__, 10 );
						$this->waitForUIInitComplete();
						break;
					case 'clientcontacticon':
					case 'invoiceicon':
					case 'transactionicon':
					case 'paymentmethodicon':
						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id );
						$this->waitThenClick('.view-min-tab');
						Debug::Text( 'clicking minimized tab', __FILE__, __LINE__, __METHOD__, 10 );
						$this->waitForUIInitComplete();
						break;
					case 'recalculatetimesheet':
					case 'generatepaystub':
					case 'jobinvoiceicon':
					case 'importcsv':
					case 'importicon':
					case 'quickstartwizard':
					case 'migratepaycodeicon':
						$this->waitThenClick('.wizard .close-btn');
						Debug::Text( 'closing wizard', __FILE__, __LINE__, __METHOD__, 10 );
						break;
					case 'accumulatedtimeicon':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id , $sub_el_id, $el->attribute('id'));
						break;
					case 'paystubtransactionicon':
						Debug::Text( 'paystubtransaction context icon hit', __FILE__, __LINE__, __METHOD__, 10 );
						$this->waitForUIInitComplete();
						$this->waitThenClick('.view-min-tab');
						Debug::Text( 'clicking minimized tab', __FILE__, __LINE__, __METHOD__, 10 );
						break;
//					case 'remittancesourceaccount':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id, TRUE );
//						break;
					default:
						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id, TRUE );

						//no hashtag
						$this->clickCancel();
//						if ( $this->byCssSelector('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon') ) {
//							Debug::Text( 'clicking: #cancelIcon.', __FILE__, __LINE__, __METHOD__, 10 );
//
//							$this->waitThenClick('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon' );
//						}
				}
				//must make sure that cancel icon is not invisible or disabled.
				$this->waitForUIInitComplete();

			}
		}
		$this->waitThenClick('.context-menu a');
		$this->waitForUIInitComplete();
	}

	function processTabs ( $path, $root_id, $sub_id = FALSE, $el_id = FALSE, $is_edit_view = FALSE) {

		Debug::Text( 'Determining tab id:', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $el_id != FALSE ) {
			$id = $el_id;
		} elseif ( $sub_id != FALSE ) {
			$id = $sub_id;
		} else {
			$id = $root_id;
		}
		Debug::Text( 'Retrieving Tabs for: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		$css_selectors = $this->getTabs($id, $sub_id);

		Debug::Text( 'Processing Tabs for: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		$skip_first = FALSE;
		if ( count($css_selectors) > 1 ) {
			foreach ( $css_selectors as $name => $tab_el ) {
				echo "'Processing Tab: ' . $name";
				Debug::Text( 'Processing Tab: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $skip_first === FALSE ) {
					$skip_first = TRUE;
					continue;
				}

				$this->waitThenClick( $tab_el );

				$this->waitForUIInitComplete();

				if ( $is_edit_view != FALSE ) {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . 'edit_view_' . $name . '.png';
				} else {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . $name . '.png';
				}
				Debug::Text( 'Taking screenshot for Tab: ' . $name . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshotFileName, TRUE );
			}


			//Should this ever change to an iterator, you'll need to rework this as reset will only return the first element if the argument is an array.
			$this->waitThenClick( reset($css_selectors) ); //click first tab before cancel.

			Debug::Text( 'Clicking Cancel at end of processTabs()', __FILE__, __LINE__, __METHOD__, 10 );
			$this->clickCancel( $sub_id . 'ContextMenu' );
		} else {
			$this->waitForUIInitComplete();
		}
	}

	function getTabs ( $clicked_icon_name, $sub_menu_id ) {
		$ignore_array = array(
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
		);

		if ( in_array( strtolower($clicked_icon_name), $ignore_array) == TRUE ) {
			Debug::Text( 'Not even looking at tabs', __FILE__, __LINE__, __METHOD__, 10 );
			return array();
		}

		$this->waitForUIInitComplete();

		$css_selector = '.edit-view.'.$sub_menu_id.'EditView .edit-view-tab-bar .ui-tabs-nav a';

		if ( $sub_menu_id == 'TimeSheet' AND
				( strtolower($clicked_icon_name) == 'addicon' OR strtolower($clicked_icon_name) == 'addabsenceicon') ) {
			$css_selector = '.edit-view.timesheet-editview .edit-view-tab-bar .ui-tabs-nav a';
		}
		Debug::Text('tab find css: '. $css_selector, __FILE__, __LINE__, __METHOD__, 10 );
		$tabs = $this->getArrayBySelector( $css_selector );

		$tab_css_selectors = array();
		foreach ( $tabs as $el ) {
			Debug::Text( 'Noticing Tab.'.$el->attribute('ref'), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->isThere('#'. $el->attribute('id') ) ) {
				$tab_css_selectors[$el->attribute( 'ref' )] = '.edit-view a#'. $el->attribute('id');
			}
		}
		unset($tabs);
		return $tab_css_selectors;
	}

	function processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, $context_el) {
		Debug::Text( 'Looking At Edit View: ' . $root_el->attribute('id') .'=>'.$sub_el->attribute('id'), __FILE__, __LINE__, __METHOD__, 10 );
		$root_id = $root_el->attribute('id');
		$sub_el_id = $sub_el->attribute('id');
		if ( $context_el != FALSE ) {
			$context_id = $context_el->attribute('id');
		} else {
			$context_id = $sub_el->attribute('id');
		}

		$ignore_list = array(
			//popovers that can grids under them so the grid is found and causes a crash.
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


		);

		if ( ( isset($sub_el) AND in_array( strtolower($sub_el->attribute( 'id' )), $ignore_list )  ) OR
			( isset($sub_el) AND in_array( strtolower($sub_el->attribute( 'id' )), $ignore_list ) AND isset($context_el) AND in_array( strtolower($context_el->attribute( 'id' )), $ignore_list ) ) ) {
			return;
		}
		$css_selector = '.grid-div .ui-jqgrid .ui-jqgrid-btable tr:nth-child(2) td:nth-child(2)';
		$this->waitForUIInitComplete();
		if ( $this->isThere('.grid-div .no-result-div') === FALSE AND $this->isThere($css_selector) === TRUE AND $this->isThere('#'. $sub_el->attribute( 'id' ). 'ContextMenu #viewIcon') ) {
			Debug::Text( 'Processing Edit View: ' . $root_el->attribute('id') .'=>'.$sub_el->attribute('id'), __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitThenClick( $css_selector );
			if ( strtolower($sub_el_id) == 'paystub' OR strtolower($sub_el_id) == 'invoice'  ) {
				$this->waitThenClick( '#'. $sub_el->attribute( 'id' ). 'ContextMenu #editIcon' );
			} else {
				$this->waitThenClick( '#'. $sub_el->attribute( 'id' ). 'ContextMenu #viewIcon' );
			}

			$this->waitForUIInitComplete();
			$screenshotFileName = $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_'. $context_id .'.png';

			Debug::Text( 'Taking screenshot for Edit View: ' . $root_id .'=>'.$sub_el_id  . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
			$this->takeScreenshot($screenshotFileName );
			$this->processTabs ( $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_'. $context_id, $root_id, $sub_el_id, $context_id, TRUE);
			//does every edit have a cancel? NO. Exceptions is the exception, so it's in the ignore_list.
			Debug::Text( 'Clicking Cancel at end of processEditScreen()', __FILE__, __LINE__, __METHOD__, 10 );
			//$this->clickCancel($sub_el_id);
			$this->waitForUIInitComplete();

			$this->waitThenClick('.context-menu a');
		}

	}

}
?>