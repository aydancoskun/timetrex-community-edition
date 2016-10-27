<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
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
/*
 * $Revision: 2196 $
 * $Id: User.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Core
 */
class APIInstall extends APIFactory {
	protected $main_class = 'Install';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function getLicense() {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			$retval = $install_obj->getLicenseText();

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'license' ), 'pre_install.php'), "r");
			@fclose($handle);

			if ( $retval != FALSE ) {
				return $this->returnHandler( $retval );
			} else {
				return $this->returnHandler( TTi18n::getText( 'NO LICENSE FILE FOUND, Your installation appears to be corrupt!' ) );
			}
		}

		return $this->returnHandler( FALSE );
	}

	function getRequirements( $external_installer = 0 ) {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			if ( DEPLOYMENT_ON_DEMAND == FALSE ) {
				$install_obj->cleanCacheDirectory( '' ); //Don't exclude .ZIP files, so if there is a corrupt one it will be redownloaded after a manual installer is run.
			}
			if ( $install_obj->isInstallMode() == FALSE ) {
				Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
			}

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array_merge( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'require'), $install_obj->getFailedRequirements( FALSE, array('clean_cache', 'file_permissions','file_checksums') ) ), 'pre_install.php'), "r");
			@fclose($handle);

			//Need to handle disabling any attempt to connect to the database, do this by using GET params on the URL like: db=0, then look for that in json/api.php

			$check_all_requirements = $install_obj->checkAllRequirements();
			if ( $external_installer == 1 AND $check_all_requirements == 0 AND $install_obj->checkTimeTrexVersion() == 0 ) {
				//Using external installer and there is no missing requirements, automatically send to next page.
				Redirect::Page( URLBuilder::getURL( array('external_installer' => $external_installer, 'action:next' => 'next' ), $_SERVER['SCRIPT_NAME']) );
			} else {
				//Return array with the text for each requirement check.
				
			}


		}

		return $this->returnHandler( FALSE );
	}

}
?>
