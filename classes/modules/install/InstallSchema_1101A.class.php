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
 * THIS IS THE SCHEMA VERSION THAT SWITCHES TO UUIDs.
 * @package Modules\Install
 */
class InstallSchema_1101A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//No need to manually generate the UUID SEED as its done and written to the config file automatically as part of InstallSchema_Base->replaceSQLVariables()

		return true;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		Debug::text( 'Starting convert company logo...', __FILE__, __LINE__, __METHOD__, 10 );
		$this->convertCompanyLogos();
		Debug::text( 'Finished convert company logo.', __FILE__, __LINE__, __METHOD__, 10 );

		Debug::text( 'Starting convert user photos...', __FILE__, __LINE__, __METHOD__, 10 );
		$this->convertUserPhoto();
		Debug::text( 'Finished convert user photos.', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool
	 */
	function convertCompanyLogos() {
		$root_path = realpath( Environment::getStorageBasePath() . 'company_logo' . DIRECTORY_SEPARATOR );
		if ( $root_path === false ) {
			Debug::text( 'ERROR: Directory does not exist: ' . Environment::getStorageBasePath() . 'company_logo' . DIRECTORY_SEPARATOR, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		try {
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file_obj ) {
				if ( $file_obj->isDir() == false ) {
					$file = $file_obj->getRealPath();
					$file_chunks = explode( DIRECTORY_SEPARATOR, $file );
					$total_file_chunks = count( $file_chunks );
					if ( $total_file_chunks > 1 ) {
						$company_file_chunk = ( $total_file_chunks - 2 );

						//only convert the path if it's still an int
						if ( TTUUID::isUUID( $file_chunks[$company_file_chunk] ) == false ) {
							$file_chunks[$company_file_chunk] = TTUUID::convertIntToUUID( $file_chunks[$company_file_chunk] );
							$new_path = implode( $file_chunks, DIRECTORY_SEPARATOR );
							$this->renameFile( $file, $new_path );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			Debug::Text( 'Failed opening/reading file or directory: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function convertUserPhoto() {
		$root_path = realpath( Environment::getStorageBasePath() . 'user_photo' . DIRECTORY_SEPARATOR );
		if ( $root_path === false ) {
			Debug::text( 'ERROR: Directory does not exist: ' . Environment::getStorageBasePath() . 'user_photo' . DIRECTORY_SEPARATOR, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$changed = false;

		try {
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file_obj ) {
				if ( $file_obj->isDir() == false ) {
					$file = $file_obj->getRealPath();
					$file_chunks = explode( DIRECTORY_SEPARATOR, $file );
					$total_file_chunks = count( $file_chunks );

					if ( $total_file_chunks > 1 ) {
						$company_file_chunk = ( $total_file_chunks - 2 );
						$filename_chunk = count( $file_chunks ) - 1;
						$filename_chunks = explode( '.', $file_chunks[$filename_chunk] );
						$user_id = $filename_chunks[0];
						$extension = $filename_chunks[1];

						//only convert the path if it's still an int
						if ( TTUUID::isUUID( $file_chunks[$company_file_chunk] ) == false ) {
							$file_chunks[$company_file_chunk] = TTUUID::convertIntToUUID( $file_chunks[$company_file_chunk] );
							$changed = true;
						}

						if ( TTUUID::isUUID( $user_id ) == false ) {
							$file_chunks[$filename_chunk] = TTUUID::convertIntToUUID( $user_id ) . '.' . $extension;
							$changed = true;
						}

						if ( $changed == true ) {
							$new_path = implode( $file_chunks, DIRECTORY_SEPARATOR );
							$this->renameFile( $file, $new_path );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			Debug::Text( 'Failed opening/reading file or directory: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return true;
	}

	/**
	 * @param $before
	 * @param $after
	 * @param int $counter
	 * @return bool
	 */
	function renameFile( $before, $after, $counter = 0 ) {
		Debug::text( $counter . '. Renaming file: ' . $before . ' to: ' . $after, __FILE__, __LINE__, __METHOD__, 10 );
		@mkdir( dirname( $after ), 0755, true );

		return Misc::rename( $before, $after );
	}
}