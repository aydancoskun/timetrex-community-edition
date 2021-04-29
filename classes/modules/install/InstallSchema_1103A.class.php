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
 * @package Module_Install
 */
class InstallSchema_1103A extends InstallSchema_Base {

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

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll( null, null, null, [ 'created_date' => 'asc' ] );
		Debug::Text( 'Get all companies. Found: ' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				Debug::text( 'Processing company: ' . $c_obj->getId() . ' Name: ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );

				$slf = new StationListFactory();
				$slf->getByCompanyIdAndTypeId( $c_obj->getId(), 65 ); //65=Mobile App Kiosk Station
				if ( $slf->getRecordCount() > 0 ) {
					foreach ( $slf as $s_obj ) {
						$mode_flags = $s_obj->getModeFlag();

						if ( is_array( $mode_flags ) ) {
							if ( in_array( 16, $mode_flags ) ) { //Punch Mode: Facial Recognition
								$default_mode_flag = 16;
							} else if ( in_array( 4, $mode_flags ) ) { //Punch Mode: QRCode
								$default_mode_flag = 4;
							} else if ( in_array( 2, $mode_flags ) ) { //Punch Mode: Quick Punch
								$default_mode_flag = 2;
							}

							if ( isset( $default_mode_flag ) ) {
								Debug::text( 'Default Mode Flag: ' . $default_mode_flag, __FILE__, __LINE__, __METHOD__, 9 );
								$s_obj->setDefaultModeFlag( $default_mode_flag );

								if ( $s_obj->isValid() ) {
									$s_obj->Save();
								}
							}
						}

						unset( $default_mode_flag );
					}
				}
			}
		}

		$clf->CommitTransaction();

		//return FALSE; //REMOVE ME ZZZ
		return true;
	}
}

?>