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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

if ( isset($argv[1]) AND in_array($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: pivot_table.php [OPTIONS] [Input CSV File] [Output CSV File]\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -pivot_column [Column to pivot on]\n";
	$help_output .= "    -category_column [Column name for categories]\n";
	$help_output .= "    -data_column [Column name for data]\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count($argv)-1;

	if ( in_array('-pivot_column', $argv) ) {
		$data['pivot_column'] = trim($argv[(array_search('-pivot_column', $argv) + 1)]);
	} else {
		$data['pivot_column'] = FALSE; //Default to first column.
	}

	if ( in_array('-category_column', $argv) ) {
		$data['category_column'] = trim($argv[(array_search('-category_column', $argv) + 1)]);
	} else {
		$data['category_column'] = 'Category';
	}

	if ( in_array('-data_column', $argv) ) {
		$data['data_column'] = trim($argv[(array_search('-data_column', $argv) + 1)]);
	} else {
		$data['data_column'] = 'Data';
	}

	$input_file = $argv[count($argv)-2];
	$output_file = $argv[count($argv)-1];

	if ( file_exists($input_file) ) {
		$input_arr = Misc::parseCSV( $input_file, TRUE );
		if ( is_array($input_arr) ) {
			if ( $data['pivot_column'] == FALSE ) {
				$data['pivot_column'] = key($input_arr[0]);
			}

			$i = 0;
			foreach( $input_arr as $input_row ) {
				foreach( $input_row as $input_column_name => $input_column_data ) {
					if ( $input_column_name != $data['pivot_column'] AND $input_column_data != '' ) {
						if ( isset($input_row[$data['pivot_column']]) ) {
							$output_arr[$i][$data['pivot_column']] = $input_row[$data['pivot_column']];
						}

						$output_arr[$i][$data['category_column']] = $input_column_name;
						$output_arr[$i][$data['data_column']] = $input_column_data;

						$i++;
					}
				}
			}

			if ( isset($output_arr) ) {
				$column_keys = array_keys($output_arr[0]);
				foreach( $column_keys as $column_key ) {
					$columns[$column_key] = $column_key;
				}

				$output_csv = Misc::Array2CSV( $output_arr, $columns, FALSE );
				file_put_contents( $output_file, $output_csv );
			}
		} else {
			echo "ERROR: Unable to parse input file...\n";
		}
	}

	echo "Done.\n";
}

Debug::writeToLog();
//Debug::Display();
?>
