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
class InstallSchema_1109A extends InstallSchema_Base {

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

		//Handle new permissions.
		$clf = TTNew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$x = 0;
			foreach ( $clf as $company_obj ) {
				Debug::text( 'Company: ' . $company_obj->getName() . ' X: ' . $x . ' of :' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );

				//Go through each permission group, and adjust the Levels so we can fit Terminated Employee in.
				$pclf = new PermissionControlListFactory;
				$pclf->getByCompanyId( $company_obj->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {

						//Migrate old permission control levels to new ones.
						if ( $pc_obj->getLevel() >= 25 ) {
							$new_level = ( $pc_obj->getLevel() + 75 );
							if ( $new_level > 100 ) {
								$new_level = 100;
							}
						} else if ( $pc_obj->getLevel() >= 20 ) {
							$new_level = ( $pc_obj->getLevel() + 60 );
						} else if ( $pc_obj->getLevel() >= 18 ) {
							$new_level = ( $pc_obj->getLevel() + 52 );
						} else if ( $pc_obj->getLevel() >= 15 ) {
							$new_level = ( $pc_obj->getLevel() + 35 );
						} else if ( $pc_obj->getLevel() >= 10 ) {
							$new_level = ( $pc_obj->getLevel() + 30 );
						} else if ( $pc_obj->getLevel() >= 3 ) {
							$new_level = ( $pc_obj->getLevel() + 27 );
						} else if ( $pc_obj->getLevel() >= 2 ) {
							$new_level = ( $pc_obj->getLevel() + 18 );
						} else if ( $pc_obj->getLevel() >= 1 ) {
							$new_level = ( $pc_obj->getLevel() + 9 );
						}
						Debug::text( 'Permission Group: ' . $pc_obj->getName() . ' Current Level: ' . $pc_obj->getLevel() . ' New Level: ' . $new_level, __FILE__, __LINE__, __METHOD__, 9 );

						$pc_obj->setLevel( $new_level );
						if ( $pc_obj->isValid() ) {
							$pc_obj->Save();
						}
					}
				}
				unset( $pclf, $plf, $pc_obj );

				//Add: "Terminated Employee" permission group.
				Debug::text( '  Add Terminated Employee permission group: ', __FILE__, __LINE__, __METHOD__, 9 );
				$pf = new PermissionFactory;

				$preset_flags = array_keys( $pf->getOptions( 'preset_flags' ) );
				$preset_options = $pf->getOptions( 'preset' );
				$preset_level_options = $pf->getOptions( 'preset_level' );

				$pcf = TTnew( 'PermissionControlFactory' ); /** @var PermissionControlFactory $pcf */
				$pcf->setCompany( $company_obj->getID() );
				$pcf->setName( $preset_options[5] );
				$pcf->setDescription( '' );
				$pcf->setLevel( $preset_level_options[5] );
				if ( $pcf->isValid() ) {
					$pcf_id = $pcf->Save( false );
					$pf->applyPreset( $pcf_id, 5, $preset_flags );

					Debug::Text( '  Terminated Employee Permission Group ID: ' . $pcf_id, __FILE__, __LINE__, __METHOD__, 10 );
					if ( TTUUID::isUUID( $pcf_id ) && $pcf_id != TTUUID::getZeroID() ) {
						//Add terminated employee permission group to User Defaults
						$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
						$udlf->getByCompanyId( $company_obj->getID() );
						if ( $udlf->getRecordCount() > 0 ) {
							Debug::Text( 'Adding Terminated Employee permission group to New Hire Defaults...', __FILE__, __LINE__, __METHOD__, 10 );
							$udf_obj = $udlf->getCurrent();

							$udf_obj->setTerminatedPermissionControl( $pcf_id );
							if ( $udf_obj->isValid() ) {
								$udf_obj->Save();
							}
						}
						unset( $udlf, $udf_obj );

						//Add terminated employee permission group to all users.
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getByCompanyId( $company_obj->getID() );
						if ( $ulf->getRecordCount() > 0 ) {
							foreach ( $ulf as $user_obj ) {
								Debug::Text( 'Updating User: ' . $user_obj->getId() . ' Username: ' . $user_obj->getUserName(), __FILE__, __LINE__, __METHOD__, 10 );

								$login_expire_date = null;
								if ( $user_obj->getStatus() != 10 ) {
									$enable_login = false;
									if ( $user_obj->getTerminationDate() != '' ) {
										$login_expire_date = TTDate::incrementDate( ( ( $company_obj->getTerminatedUserDisableLoginType() == 10 ) ? TTDate::getEndYearEpoch( $user_obj->getTerminationDate() ) : $user_obj->getTerminationDate() ), $company_obj->getTerminatedUserDisableLoginAfterDays(), 'day' );
										if ( TTDate::getMiddleDayEpoch( $login_expire_date ) > TTDate::getMiddleDayEpoch( time() ) ) {
											$enable_login = true;
										} else {
											$login_expire_date = null;
										}
									}
								} else {
									$enable_login = true;
								}

								//Update user with direct SQL query to speed it up and avoid running into non-related validation errors like wage effective dates and such.
								$ph = [
										'enable_login'                     => (int)$enable_login,
										'login_expire_date'                => ( $login_expire_date !== null ) ? $this->db->bindDate( $login_expire_date ) : null,
										'terminated_permission_control_id' => $pcf_id,
								];

								$ph[] = $user_obj->getId(); //User ID

								//Fix cases where birth date is the same or after the hire date.
								$birth_date_sql = '';
								if ( $user_obj->getBirthDate() != '' && $user_obj->getHireDate() != '' && TTDate::getMiddleDayEpoch( $user_obj->getBirthDate() ) >= TTDate::getMiddleDayEpoch( $user_obj->getHireDate() ) ) {
									Debug::Text( '  Birth Date (' . TTDate::getDATE( 'DATE', $user_obj->getBirthDate() ) . ') is on or after Hire Date (' . TTDate::getDATE( 'DATE', $user_obj->getHireDate() ) . '), clearing it... User: ' . $user_obj->getId() . ' Username: ' . $user_obj->getUserName(), __FILE__, __LINE__, __METHOD__, 10 );
									$birth_date_sql = ', birth_date = NULL'; //Only update birth_date if its invalid. Otherwise don't modify it at all to make sure its modified to be incorrect somehow.
								}

								$query = 'UPDATE ' . $user_obj->getTable() . ' SET enable_login = ?, login_expire_date = ?, terminated_permission_control_id = ? ' . $birth_date_sql . ' WHERE id = ?';
								//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
								$this->db->Execute( $query, $ph );
								unset( $enable_login, $login_expire_date );
							}
						}
						unset( $ulf, $user_obj );
					}
				}
				unset( $preset_flags, $preset_options, $preset_level_options, $pcf, $pf, $pcf_id );

				$x++;
			}
		}

		return true;
	}
}

?>
