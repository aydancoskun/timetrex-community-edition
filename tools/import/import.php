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
if ( PHP_SAPI != 'cli' ) {
	echo "This script can only be called from the Command Line.\n";
	exit;
}

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'TimeTrexClientAPI.class.php' );

error_reporting( E_ALL );
ini_set( 'display_errors', 1 ); //Try to display any errors that may arise from the API.

function Array2CSV( $data, $columns = null, $ignore_last_row = true, $include_header = true, $eol = "\n" ) {
	if ( is_array( $data ) AND count( $data ) > 0
			AND is_array( $columns ) AND count( $columns ) > 0 ) {

		if ( $ignore_last_row === true ) {
			array_pop( $data );
		}

		//Header
		if ( $include_header == true ) {
			foreach ( $columns as $column_name ) {
				$row_header[] = $column_name;
			}
			$out = '"' . implode( '","', $row_header ) . '"' . $eol;
		} else {
			$out = null;
		}

		foreach ( $data as $rows ) {
			foreach ( $columns as $column_key => $column_name ) {
				if ( isset( $rows[$column_key] ) ) {
					$row_values[] = str_replace( "\"", "\"\"", $rows[$column_key] );
				} else {
					//Make sure we insert blank columns to keep proper order of values.
					$row_values[] = null;
				}
			}

			$out .= '"' . implode( '","', $row_values ) . '"' . $eol;
			unset( $row_values );
		}

		return $out;
	}

	return false;
}

function parseCSV( $file, $head = false, $first_column = false, $delim = ',', $len = 9216, $max_lines = null ) {
	if ( !file_exists( $file ) ) {
		Debug::text( 'Files does not exist: ' . $file, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	//mime_content_type is being deprecated in PHP, and it doesn't work properly on Windows. So if its not available just accept any file type.
	if ( function_exists( 'mime_content_type' ) ) {
		$mime_type = mime_content_type( $file );
		if ( $mime_type !== false AND !in_array( $mime_type, [ 'text/plain', 'plain/text', 'text/comma-separated-values', 'text/csv', 'application/csv', 'text/anytext', 'text/x-c' ] ) ) {
			Debug::text( 'Invalid MIME TYPE: ' . $mime_type, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	ini_set( 'auto_detect_line_endings', true ); //PHP can have problems detecting MAC line endings in some case, this should help solve that.

	$return = false;
	$handle = fopen( $file, 'r' );
	if ( $head !== false ) {
		if ( $first_column !== false ) {
			while ( ( $header = fgetcsv( $handle, $len, $delim ) ) !== false ) {
				if ( $header[0] == $first_column ) {
					$found_header = true;
					break;
				}
			}

			if ( $found_header !== true ) {
				return false;
			}
		} else {
			$header = fgetcsv( $handle, $len, $delim );
		}
	}

	//Excel adds a Byte Order Mark (BOM) to the beginning of files with UTF-8 characters. That needs to be stripped off otherwise it looks like a space and columns don't match up.
	if ( isset( $header ) AND isset( $header[0] ) ) {
		$header[0] = str_replace( "\xEF\xBB\xBF", '', $header[0] );
	}

	$i = 1;
	while ( ( $data = fgetcsv( $handle, $len, $delim ) ) !== false ) {
		if ( $data !== [ null ] ) { // Ignore blank lines
			if ( $head == true AND isset( $header ) ) {
				$row = [];
				foreach ( $header as $key => $heading ) {
					$row[trim( $heading )] = ( isset( $data[$key] ) ) ? $data[$key] : '';
				}
				$return[] = $row;
			} else {
				$return[] = $data;
			}

			if ( $max_lines !== null AND $max_lines != '' AND $i == $max_lines ) {
				break;
			}

			$i++;
		}
	}

	fclose( $handle );

	ini_set( 'auto_detect_line_endings', false );

	return $return;
}

if ( $argc < 3 OR in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: import.php [OPTIONS] [Column MAP file] [CSV File]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -server <URL>				URL to API server\n";
	$help_output .= "    -username <username>		API username\n";
	$help_output .= "    -password <password>		API password\n";
	$help_output .= "    -object <object>			Object to import (ie: User,Branch,Punch)\n";
	$help_output .= "    -f <flag>				Custom flags, ie: fuzzy_match,update\n";
	$help_output .= "    -n 					Dry-run, display the first two lines to confirm mapping is correct\n";
	$help_output .= "    -export_map <name>		Export the mapping information from the web interface saved as <name>\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '-n', $argv ) ) {
		$dry_run = true;
	} else {
		$dry_run = false;
	}

	if ( in_array( '-server', $argv ) ) {
		$api_url = trim( $argv[( array_search( '-server', $argv ) + 1 )] );
	} else {
		$api_url = false;
	}

	if ( in_array( '-username', $argv ) ) {
		$username = trim( $argv[( array_search( '-username', $argv ) + 1 )] );
	} else {
		$username = false;
	}

	if ( in_array( '-password', $argv ) ) {
		$password = trim( $argv[( array_search( '-password', $argv ) + 1 )] );
	} else {
		$password = false;
	}

	if ( in_array( '-object', $argv ) ) {
		$object = trim( $argv[( array_search( '-object', $argv ) + 1 )] );
	} else {
		$object = false;
	}

	if ( in_array( '-f', $argv ) ) {
		$raw_flags = trim( $argv[( array_search( '-f', $argv ) + 1 )] );
		if ( strpos( $raw_flags, ',' ) !== false ) {
			$raw_flag_split = explode( ',', $raw_flags );
			if ( is_array( $raw_flag_split ) ) {
				foreach ( $raw_flag_split as $tmp_flag ) {
					$flags[$tmp_flag] = true;
				}
			}
		} else {
			$flags = [ $raw_flags => true ];
		}
	} else {
		$flags = [];
	}

	if ( in_array( '-export_map', $argv ) ) {
		$export_map = trim( $argv[( array_search( '-export_map', $argv ) + 1 )] );
	} else {
		$export_map = false;
	}

	if ( $export_map == false ) {
		if ( isset( $argv[( $last_arg - 1 )] ) AND $argv[( $last_arg - 1 )] != '' ) {
			if ( !file_exists( $argv[( $last_arg - 1 )] ) OR !is_readable( $argv[( $last_arg - 1 )] ) ) {
				echo "Column MAP File: " . $argv[( $last_arg - 1 )] . " does not exist or is not readable!\n";
			} else {
				$column_map_file = $argv[( $last_arg - 1 )];
			}
		}

		if ( isset( $argv[$last_arg] ) AND $argv[$last_arg] != '' ) {
			if ( !file_exists( $argv[$last_arg] ) OR !is_readable( $argv[$last_arg] ) ) {
				echo "Import CSV File: " . $argv[$last_arg] . " does not exist or is not readable!\n";
			} else {
				$import_csv_file = $argv[$last_arg];
			}
		}

		if ( !isset( $column_map_file ) ) {
			echo "ERROR: Column Map File not set!\n";
			exit;
		}
	} else {
		if ( isset( $argv[$last_arg] ) AND $argv[$last_arg] != '' ) {
			if ( file_exists( $argv[$last_arg] ) ) { //OR !is_writable( $argv[$last_arg] ) ) {
				echo "Column Map File: " . $argv[$last_arg] . " already exists or is not writable!\n";
			} else {
				$column_map_file = $argv[$last_arg];
			}
		}

		if ( !isset( $column_map_file ) ) {
			echo "ERROR: Column Map File not set!\n";
			exit;
		}
	}

	$TIMETREX_URL = $api_url;

	$api_session = new TimeTrexClientAPI();
	$api_session->Login( $username, $password );
	if ( $TIMETREX_SESSION_ID == false ) {
		echo "API Username/Password is incorrect!\n";
		exit;
	}
	//echo "Session ID: $TIMETREX_SESSION_ID\n";

	if ( $object != '' ) {
		if ( $export_map == false ) {
			$column_map = parseCSV( $column_map_file, true, false, ',', 9216 );
			if ( is_array( $column_map ) ) {
				foreach ( $column_map as $column_map_row ) {
					if ( isset( $column_map_row['timetrex_column'] ) ) {
						$column_map_arr[$column_map_row['timetrex_column']] = [ 'map_column_name' => $column_map_row['csv_column'], 'default_value' => $column_map_row['default_value'], 'parse_hint' => $column_map_row['parse_hint'] ];
					} else if ( isset( $column_map_row['import_column'] ) ) {
						$column_map_arr[$column_map_row['import_column']] = [ 'map_column_name' => $column_map_row['map_column_name'], 'default_value' => $column_map_row['default_value'], 'parse_hint' => $column_map_row['parse_hint'] ];
					}
				}
			} else {
				echo "Column map is invalid!\n";
			}

			$obj = new TimeTrexClientAPI( 'Import' . ucfirst( $object ) );
			$obj->setRawData( file_get_contents( $import_csv_file ) );
			//var_dump( $obj->getOptions('columns') );

			$retval = $obj->Import( $column_map_arr, $flags, $dry_run );
			if ( is_object( $retval ) AND $retval->getResult() == true ) {
				echo "Import successful!\n";
			} else {
				echo "ERROR: Failed importing data...\n";
				echo $retval;
				exit( 1 );
			}
		} else {
			//Get export mapping.
			$obj = new TimeTrexClientAPI( 'UserGenericData' );
			$result = $obj->getUserGenericData( [ 'filter_data' => [ 'script' => 'import_wizard' . strtolower( $object ), 'name' => $export_map ] ] );
			$retval = $result->getResult();
			if ( is_array( $retval ) AND isset( $retval[0]['data'] ) ) {
				$output = [];

				$i = 0;
				foreach ( $retval[0]['data'] as $column_map ) {
					unset( $column_map['row_1'], $column_map['id'] ); //Strip unneeded columns.

					if ( $i == 0 ) {
						$columns = [
								'field'           => 'import_column',
								'map_column_name' => 'map_column_name',
								'default_value'   => 'default_value',
								'parse_hint'      => 'parse_hint',
						];
					}

					$output[] = $column_map;
					$i++;
				}

				if ( isset( $columns ) AND count( $output ) > 0 ) {
					file_put_contents( $column_map_file, Array2CSV( $output, $columns, false ) );
					echo "Column map written to: " . $column_map_file . "\n";
				}
			} else {
				echo "ERROR: No Column map matching that object/name...\n";
				exit( 1 );
			}
		}
	} else {
		echo "ERROR: Object argument not specified!\n";
		exit( 1 );
	}
}
echo "Done!\n";
?>
