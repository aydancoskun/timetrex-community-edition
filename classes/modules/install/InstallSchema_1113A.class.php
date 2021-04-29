<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
class InstallSchema_1113A extends InstallSchema_Base {

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

		$upf = TTNew( 'UserPreferenceFactory' );
		$deprecated_zones = $upf->getOptions( 'deprecated_timezone' );

		global $config_vars;
		if ( isset( $deprecated_zones[trim( $config_vars['other']['system_timezone'] )] ) ) {
			$new_time_zone = $deprecated_zones[trim( $config_vars['other']['system_timezone'] )];
			Debug::text( 'System TimeZone is deprecated: ' . $config_vars['other']['system_timezone'] . ' switching to: ' . $new_time_zone, __FILE__, __LINE__, __METHOD__, 9 );
			$tmp_config_vars['other']['system_timezone'] = $new_time_zone; //Extra quotes not needed.

			$install_obj = new Install();
			$install_obj->writeConfigFile( $tmp_config_vars );
			unset( $install_obj, $new_time_zone );
		} else {
			Debug::text( 'System TimeZone is valid.', __FILE__, __LINE__, __METHOD__, 9 );
		}

		//Go through each users preferences and convert deprecated timezones to their proper ones.
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				Debug::text( 'Company: ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );

				//Make sure all companies, cancelled or not get changed.
				$deprecated_zones = $upf->getOptions( 'deprecated_timezone', [ 'country' => $c_obj->getCountry() ] );


				$udlf = TTNew( 'UserDefaultListFactory' );
				$udlf->getByCompanyId( $c_obj->getId() );
				if ( $udlf->getRecordCount() > 0 ) {
					foreach ( $udlf as $ud_obj ) {
						$record_updated = false;

						if ( $ud_obj->getLanguage() == 'pt' || $ud_obj->getLanguage() == 'zh' || $ud_obj->getLanguage() == 'it' || $ud_obj->getLanguage() == 'da' ) {
							Debug::text( '  Found deprecated Language: ' . $ud_obj->getLanguage() . ' switching to: en', __FILE__, __LINE__, __METHOD__, 9 );
							$ud_obj->setLanguage( 'en' );
							$record_updated = true;
						}

						if ( isset( $deprecated_zones[trim( $ud_obj->getTimeZone() )] ) ) {
							$new_time_zone = $deprecated_zones[trim( $ud_obj->getTimeZone() )];
							Debug::text( '  Found deprecated TimeZone: ' . $ud_obj->getTimeZone() . ' switching to: ' . $new_time_zone . ' for User Default ID: ' . $ud_obj->getId(), __FILE__, __LINE__, __METHOD__, 9 );
							$ud_obj->setTimeZone( $new_time_zone );
							$record_updated = true;
							unset( $new_time_zone );
						}
						//else {
						//	Debug::text( 'NOT deprecated TimeZone: ' . $ud_obj->getTimeZone() .' for User Preference ID: ' . $ud_obj->getId() . ' User ID: ' . $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 9 );
						//}

						if ( $record_updated == true ) {
							if ( $ud_obj->isValid() ) {
								$ud_obj->Save();
							} else {
								Debug::text( '  WARNING: Unable to save user default preference!', __FILE__, __LINE__, __METHOD__, 9 );
							}
						}
					}
				}
				unset( $udlf, $ud_obj );


				$uplf = TTNew( 'UserPreferenceListFactory' );
				$uplf->getByCompanyId( $c_obj->getId() );
				if ( $uplf->getRecordCount() > 0 ) {
					foreach ( $uplf as $up_obj ) {
						$record_updated = false;

						if ( $up_obj->getLanguage() == 'pt' || $up_obj->getLanguage() == 'zh' || $up_obj->getLanguage() == 'it' || $up_obj->getLanguage() == 'da' ) {
							Debug::text( '  Found deprecated Language: ' . $up_obj->getLanguage() . ' switching to: en', __FILE__, __LINE__, __METHOD__, 9 );
							$up_obj->setLanguage( 'en' );
							$record_updated = true;
						}

						if ( isset( $deprecated_zones[trim( $up_obj->getTimeZone() )] ) ) {
							$new_time_zone = $deprecated_zones[trim( $up_obj->getTimeZone() )];
							Debug::text( '  Found deprecated TimeZone: ' . $up_obj->getTimeZone() . ' switching to: ' . $new_time_zone . ' for User Preference ID: ' . $up_obj->getId() . ' User ID: ' . $up_obj->getUser(), __FILE__, __LINE__, __METHOD__, 9 );
							$up_obj->setTimeZone( $new_time_zone );
							$record_updated = true;
							unset( $new_time_zone );
						}
						//else {
						//	Debug::text( 'NOT deprecated TimeZone: ' . $up_obj->getTimeZone() .' for User Preference ID: ' . $up_obj->getId() . ' User ID: ' . $up_obj->getUser(), __FILE__, __LINE__, __METHOD__, 9 );
						//}

						if ( $record_updated == true ) {
							if ( $up_obj->isValid() ) {
								$up_obj->Save();
							} else {
								Debug::text( '  WARNING: Unable to save user preference!', __FILE__, __LINE__, __METHOD__, 9 );
							}
						}
					}
				}
				unset( $uplf, $up_obj );


				$slf = TTNew( 'StationListFactory' );
				$slf->getByCompanyId( $c_obj->getId() );
				if ( $slf->getRecordCount() > 0 ) {
					foreach ( $slf as $s_obj ) {
						$record_updated = false;

						if ( isset( $deprecated_zones[trim( $s_obj->getTimeZone() )] ) ) {
							$new_time_zone = $deprecated_zones[trim( $s_obj->getTimeZone() )];
							Debug::text( '  Found deprecated TimeZone: ' . $s_obj->getTimeZone() . ' switching to: ' . $new_time_zone . ' for Station ID: ' . $s_obj->getId() .' Type: '. $s_obj->getType(), __FILE__, __LINE__, __METHOD__, 9 );
							$s_obj->setTimeZone( $new_time_zone );
							$record_updated = true;
							unset( $new_time_zone );
						}
						//else {
						//	Debug::text( 'NOT deprecated TimeZone: ' . $s_obj->getTimeZone() .' for User Preference ID: ' . $s_obj->getId() . ' User ID: ' . $s_obj->getUser(), __FILE__, __LINE__, __METHOD__, 9 );
						//}

						if ( $record_updated == true ) {
							if ( $s_obj->isValid() ) {
								$s_obj->Save();
							} else {
								Debug::text( '  WARNING: Unable to save station!', __FILE__, __LINE__, __METHOD__, 9 );
							}
						}
					}
				}
				unset( $slf, $s_obj );
			}
		}

		return true;
	}
}

?>
