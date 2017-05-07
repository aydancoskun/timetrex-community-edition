<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

if ( $argc < 1 OR ( isset($argv[1]) AND in_array($argv[1], array('--help', '-help', '-h', '-?') ) ) ) {
	$help_output = "Usage: cleanup_storage_dir.php [options] [company_id]\n";
	$help_output .= "    -n				Dry-run\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = ( count($argv) - 1 );

	if ( in_array('-n', $argv) ) {
		$dry_run = TRUE;
		echo "Using DryRun!\n";
	} else {
		$dry_run = FALSE;
	}

	if ( isset($argv[$last_arg]) AND is_numeric($argv[$last_arg]) ) {
		$company_id = $argv[$last_arg];
	}

	//Force flush after each output line.
	ob_implicit_flush( TRUE );
	ob_end_flush();

	//Top level storage dir.
	$storage_dir = Environment::getStorageBasePath();

	//
	//Loop through all storage directories finding orphaned files.
	//

	//Punch Images
	$punch_image_dir = $storage_dir . DIRECTORY_SEPARATOR . 'punch_images';
	echo "Punch Images: ". $punch_image_dir ."\n";

	$plf = new PunchListFactory();

	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( $punch_image_dir ), RecursiveIteratorIterator::SELF_FIRST);
	$i = 0;
	foreach ( $files as $file ) {
		if ( $file->isFile() == TRUE ) {
			$punch_id = str_replace( pathinfo( $file->getFileName(), PATHINFO_EXTENSION ), '', $file->getFilename() );
			$plf->getById( $punch_id );
			if ( $plf->getRecordCount() == 0 OR ( $plf->getRecordCount() == 1 AND (bool)$plf->getCurrent()->getHasImage() == FALSE ) ) {
				echo 'Path+File: ' . $file->getPathName() . ' File: ' . $file->getFilename() . ' Punch ID: ' . $punch_id . ' File mTime: '. TTDate::getDate('DATE+TIME', filectime( $file->getPathName() ) ) . "\n";
				Debug::Text('Path+File: ' . $file->getPathName() . ' File: ' . $file->getFilename() . ' Punch ID: ' . $punch_id . ' File mTime: '. TTDate::getDate('DATE+TIME', filectime( $file->getPathName() ) ), __FILE__, __LINE__, __METHOD__, 10);

				echo '  Punch does not exist, or does not have image, deleting orphaned image file: '. (int)$plf->getRecordCount() ."\n";
				Debug::Text('  Punch does not exist, or does not have image, deleting orphaned image file: '. (int)$plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				if ( $dry_run == FALSE ) {
					@unlink( $file->getPathName() );
					$i++;
				}
			}
		}
	}
	echo "Deleted Punch Images: ". $i ."\n";
}
echo "Done...\n";
Debug::WriteToLog();
Debug::Display();
?>
