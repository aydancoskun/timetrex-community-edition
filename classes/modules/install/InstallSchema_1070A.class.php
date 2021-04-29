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
 * @package Module_Install
 */
class InstallSchema_1070A extends InstallSchema_Base {

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

		//Loop through all contributing shift policies switch include_shift_type_id from boolean to integer.
		$csplf = new ContributingShiftPolicyListFactory();
		$csplf->getAll();
		if ( $csplf->getRecordCount() > 0 ) {
			Debug::text( 'ContributingShiftPolicies: ' . $csplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );
			foreach ( $csplf as $csp_obj ) {
				$previous_shift_type_id = (int)$csp_obj->getIncludeShiftType();
				if ( $previous_shift_type_id === 0 ) {
					$csp_obj->setIncludeShiftType( 200 ); //Full Shift (this was the default before)
				} else {
					$csp_obj->setIncludeShiftType( 100 ); //Partial Shift
				}

				Debug::text( ' IncludeShiftType ID: Previous: ' . $previous_shift_type_id . ' New: ' . (int)$csp_obj->getIncludeShiftType(), __FILE__, __LINE__, __METHOD__, 9 );
				if ( $csp_obj->isValid() ) {
					$csp_obj->Save();
				}
			}
		}

		//Handle new permissions.
		$clf = TTNew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$x = 0;
			foreach ( $clf as $company_obj ) {
				Debug::text( 'Company: ' . $company_obj->getName() . ' X: ' . $x . ' of :' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );

				//Add: "Regular Employee (Manual TimeSheet)
				if ( $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					Debug::text( '  Add Regular Employee (Manual TimeSheet) permission group: ', __FILE__, __LINE__, __METHOD__, 9 );
					$pf = new PermissionFactory;

					$preset_flags = array_keys( $pf->getOptions( 'preset_flags' ) );
					$preset_options = $pf->getOptions( 'preset' );
					$preset_level_options = $pf->getOptions( 'preset_level' );

					$pcf = TTnew( 'PermissionControlFactory' ); /** @var PermissionControlFactory $pcf */
					$pcf->setCompany( $company_obj->getID() ); //Regular Employee (Manual TimeSheet)
					$pcf->setName( $preset_options[14] );
					$pcf->setDescription( '' );
					$pcf->setLevel( $preset_level_options[14] );
					if ( $pcf->isValid() ) {
						$pcf_id = $pcf->Save( false );
						$pf->applyPreset( $pcf_id, 14, $preset_flags );
					}
					unset( $preset_flags, $preset_options, $preset_level_options, $pcf, $pf, $pcf_id );
				}

				//Go through each permission group, and rename "Regular Employee (Manual Entry)" to "Regular Employee (Manual Punch)" -- that can punch in/out manually.
				$pclf = new PermissionControlListFactory;
				$pclf->getByCompanyId( $company_obj->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {
						Debug::text( 'Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
						if ( stripos( $pc_obj->getName(), 'Manual Entry' ) ) {
							$pc_obj->setName( str_ireplace( 'Manual Entry', 'Manual Punch', $pc_obj->getName() ) );
							Debug::text( '  Renaming Permission Group to: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
						}

						//Add punch_timesheet to all existing permission groups.
						$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'punch', 'add', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Found permission group with punch,add, add punch_timesheet: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'punch' => [ 'punch_timesheet' => true ],
									]
							);
						} else {
							Debug::text( '  Permission group does NOT have punch,add enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						if ( $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
							//Add manual_timesheet to all existing permission groups. Since in theory any regular employee could have manual timesheet mode enabled, all supervisors need to have it enabled too.
							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'punch', 'edit_child', 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Found permission group with punch,edit_child, adding manual_timesheet: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission(
										[
												'punch' => [ 'manual_timesheet' => true ],
										]
								);
							} else {
								Debug::text( '  Permission group does NOT have punch,edit_child enabled...', __FILE__, __LINE__, __METHOD__, 9 );
							}
						}

						if ( $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
							//Add request,add_advanced to all existing permission groups by default.
							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'request', 'add', 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Found permission group with request,add, adding add_advanced: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission(
										[
												'request' => [ 'add_advanced' => true ],
										]
								);
							} else {
								Debug::text( '  Permission group does NOT have request,add enabled...', __FILE__, __LINE__, __METHOD__, 9 );
							}

							//
							//Add government_document,view_own to all existing permission groups by default.
							//
							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'pay_stub', 'view_own', 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Found permission group with request,add, adding add_advanced: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission(
										[
												'government_document' => [ 'enabled' => true, 'view_own' => true ],
										]
								);
							} else {
								Debug::text( '  Permission group does NOT have request,add enabled...', __FILE__, __LINE__, __METHOD__, 9 );
							}

							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'pay_stub', [ 'add', 'edit', 'delete' ], 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Found permission group with request,add, adding add_advanced: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission(
										[
												'government_document' => [ 'enabled' => true, 'view' => true, 'add' => true, 'edit' => true, 'delete' => true ],
										]
								);
							} else {
								Debug::text( '  Permission group does NOT have request,add enabled...', __FILE__, __LINE__, __METHOD__, 9 );
							}
						}
					}
				}
				unset( $pclf, $plf, $pc_obj );

				$x++;
			}
		}

		return true;
	}
}

?>
