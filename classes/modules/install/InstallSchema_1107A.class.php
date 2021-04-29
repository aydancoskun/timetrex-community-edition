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
 * @package Modules\Install
 */
class InstallSchema_1107A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//We changed the API and UI to always check the system -> login permission before allowing a user to login. It turns out quite a few customers removed this permission when customizing their permission groups.
		// So make sure we go through and add it back to all permission groups to prevent locking them out.

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll( null, null, null, [ 'created_date' => 'asc' ] );
		Debug::Text( 'Get all companies. Found: ' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $cf ) {
				Debug::text( 'Processing company: ' . $cf->getId() . ' Name: ' . $cf->getName(), __FILE__, __LINE__, __METHOD__, 9 );

				//Make sure system -> login permissions are allowed.
				$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
				$pclf->getByCompanyId( $cf->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {
						$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'system', 'login', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() == 0 ) {
							Debug::text( '  Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
							Debug::text( '    Found permission group WITHOUT system -> login allowed, add enabled: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission( [ 'system' => [ 'login' => true ] ] );
						}
					}
				}
			}
		}

//		$clf->FailTransaction(); return FALSE; //FOR DEBUG DO NOT COMMIT THIS UNLESS IT IS COMMENTED OUT!!!!
		$clf->CommitTransaction();
		unset( $clf );

		return true;
	}
}

?>
