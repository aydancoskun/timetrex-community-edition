<?php
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
 * @package Modules\Install
 */
class InstallSchema_1108A extends InstallSchema_Base {

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

		//Convert all serialize'd data to JSON encoded data instead to avoid potential security issues with unserialize().

		$urdlf = TTnew( 'UserReportDataListFactory' );
		$urdlf->getAll();
		if ( $urdlf->getRecordCount() > 0 ) {
			$urdlf->StartTransaction();
			foreach ( $urdlf as $urd_obj ) {
				$data = unserialize( $urd_obj->getGenericDataValue( 'data' ), [ 'allowed_classes' => false ] );
				if ( $data != '' ) {
					$urd_obj->setData( $data );
					if ( $urd_obj->isValid() ) {
						Debug::Text( 'Converted Data columns in UserReportData: ' . $urd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$urd_obj->Save();
					} else {
						Debug::Text( 'WARNING: Unable to save UserReportData: ' . $urd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'ERROR: Unable to unserialize UserReportData, deleting: ' . $urd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$urd_obj->setDeleted( true );
					if ( $urd_obj->isValid() ) {
						$urd_obj->Save();
					}
				}
			}
			//$urdlf->FailTransaction();
			$urdlf->CommitTransaction();
		}
		unset( $urdlf );


		$ugdlf = TTnew( 'UserGenericDataListFactory' );
		$ugdlf->getAll();
		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugdlf->StartTransaction();
			foreach ( $ugdlf as $ugd_obj ) {
				/** @var UserGenericDataFactory $ugd_obj */
				//Delete any legacy "-Default-" records that aren't import settings, as they are now "-- Default --" instead.
				//  Only delete default records for saved searches on regular views though (*View), to ensure we skip deleting default import wizard settings as that can cause major problems for some users.
				if ( ( $ugd_obj->getName() == '-Default-' && preg_match( '/View$/i', $ugd_obj->getScript() ) ) ) {
					$ugd_obj->setDeleted( true );
					if ( $ugd_obj->isValid() ) {
						Debug::Text( 'NOTICE: Deleting legacy -Default- UserGenericData record: ' . $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$ugd_obj->Save();
					}
				} else {
					//For default records that we are keeping,
					$original_name = $ugd_obj->getName();
					if ( $ugd_obj->getName() == '-Default-' ) {
						$ugd_obj->setName( '-- Default --' );

						//Check if there is a duplicate name, if so set it back to the original value.
						if ( $ugd_obj->isValid() == false ) {
							Debug::Text( '  Validation error, setting back to original name: ' . $original_name . ' Script: ' . $ugd_obj->getScript() . ' ID: ' . $ugd_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
							$ugd_obj->setName( $original_name );
						}
						unset( $original_name );
					}

					$data = unserialize( $ugd_obj->getGenericDataValue( 'data' ), [ 'allowed_classes' => false ] );

					if ( $data != '' ) {
						$ugd_obj->setData( $data );
						if ( $ugd_obj->isValid() ) {
							Debug::Text( 'Converted Data columns in UserGenericData: ' . $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$ugd_obj->Save();
						} else {
							Debug::Text( 'WARNING: Unable to save UserGenericData: ' . $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( 'ERROR: Unable to unserialize UserGenericData, deleting: ' . $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$ugd_obj->setDeleted( true );
						if ( $ugd_obj->isValid() ) {
							$ugd_obj->Save();
						}
					}
				}
			}

			//$ugdlf->FailTransaction();
			$ugdlf->CommitTransaction();
		}
		unset( $ugdlf );

		return true;
	}
}

?>
