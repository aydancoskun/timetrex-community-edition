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
 * @package Core
 */
class Group {
	/**
	 * Usage: $arr2 = Group::GroupBy($arr1, array( 'name' => array('aggregate' => 'count', 'output' => 'MoneyFormat'), array( 'name' => array('aggregate' => 'count', 'output' => 'MoneyFormat') ) );
	 * Aggregate values: 'count', 'sum', 'min', 'max', 'avg'
	 * @param $array
	 * @param $cols
	 * @param bool $subtotal
	 * @return array
	 */
	static function GroupBy( $array, $cols, $subtotal = false ) {
		global $profiler;
		$profiler->startTimer( 'Group()' );

		$group_by_cols = [];
		$aggregate_cols = [];
		if ( is_array( $cols ) ) {
			foreach ( $cols as $col => $aggregate ) {
				if ( is_string( $aggregate ) && $aggregate != '' ) {
					$aggregate_cols[$col] = $aggregate;
				} else {
					$group_by_cols[$col] = $col; //Use $col as the key so we can use isset() instead of in_array() later on.
					//$sort_by_cols[$col] = SORT_ASC;
				}
			}
		}
		//Debug::Arr( $group_by_cols, 'Group By Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr( $aggregate_cols, 'Aggregate Columns: ', __FILE__, __LINE__, __METHOD__, 10);

		//Sort the array by group_by_col first, ONLY if subtotal mode, because order matters for merging the subtotal rows back into the main array.
		//The array should be sorted before subtotaling is done though, so we either need to pass the final sorting array here, or don't sort at all.
		//if ( $subtotal == 1 AND count($sort_by_cols) > 0 ) {
		//	$array = Sort::arrayMultiSort( $array, $sort_by_cols );
		//}

		$retarr = [];
		$row_map = [];
		if ( is_array( $array ) ) {
			$i = 0;
			foreach ( $array as $row ) {
				if ( !is_array( $row ) ) {
					continue;
				}

				$group_by_key_val = null;
				if ( $subtotal == 2 ) {
					$group_by_key_val = 0; //Total the entire array.
				} else {
					foreach ( $group_by_cols as $group_by_element ) {
						if ( isset( $row[$group_by_element] ) ) {
							//Check if the value is an array with a 'sort' column, ie: array('sort' => 12345678, 'display' => '01-Jan-10' )
							if ( is_array( $row[$group_by_element] ) && isset( $row[$group_by_element]['sort'] ) ) {
								$group_by_key_val .= $row[$group_by_element]['sort'];
							} else {
								$group_by_key_val .= $row[$group_by_element];
							}
						}
					}
				}
				//Debug::Text('Group By Key Val: '. $group_by_key_val, __FILE__, __LINE__, __METHOD__, 10);

				if ( !isset( $retarr[$group_by_key_val] ) ) {
					$retarr[$group_by_key_val] = [];
				}

				//Map the last row that each group_by_key_val was seen. Assume that the array is properly sorted first of course.
				//Suffix '_' to the end so we can do an array_merge and ksort( $arr, SORT_STRING).
				//Although for creating overall totals we will likely need to do that before we merge in any sub-totals.
				$row_map[$group_by_key_val] = $i . '_';

				foreach ( $row as $key => $val ) {
					//Debug::text(' aKey: '. $key .' Value: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $group_by_cols[$key] ) ) {
						//Only include a single key => value pair for the grouped columns to save memory.
						//If we are subtotaling, ignore all static columns.
						//Keep all columns even when sub-totalling so we can provide more information regarding the sub-total itself.
						if ( !isset( $retarr[$group_by_key_val][$key] ) ) {
							$retarr[$group_by_key_val][$key] = $val;
						}
					} else if ( isset( $aggregate_cols[$key] ) ) {
						$retarr[$group_by_key_val][$key][] = $val;
					} // else { //Ignore data that isn't in grouping or aggregate.
				}

				$i++;
			}
		}

		//Substitude group_by_key_val with sparse row values so we know where to insert totals within the main array if sub-totaling.
		//Debug::Arr($row_map, ' Row Map: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $subtotal == 1 ) {
			if ( is_array( $retarr ) && is_array( $row_map ) ) {
				foreach ( $row_map as $key => $count ) {
					$retarr[$count] = $retarr[$key];
					$retarr[$count]['_subtotal'] = true;
					unset( $retarr[$key] );
				}
			}
		}

		if ( is_array( $retarr ) && count( $aggregate_cols ) > 0 ) {
			foreach ( $retarr as $i => $row ) {
				foreach ( $row as $key => $val ) {
					//Debug::text(' bKey: '. $key .' Value: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $aggregate_cols[$key] ) ) {
						//Debug::Arr($aggregate_cols[$key], 'Aggregate MetaData: ', __FILE__, __LINE__, __METHOD__, 10);
						//Debug::Arr($val, 'Aggregate Data: ', __FILE__, __LINE__, __METHOD__, 10);
						$retarr[$i][$key] = self::aggregate( $val, $aggregate_cols[$key], $subtotal );
						//Debug::Arr($retarr[$i][$key], 'Aggregate Result: ', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
		}

		$profiler->stopTimer( 'Group()' );

		if ( $subtotal == 1 ) {
			return $retarr;
		} else {
			return array_values( $retarr ); //Use array_values() to reindex array starting at 0.
		}
	}

	/**
	 * Usage: $arr2 = Group::GroupBy($arr1, array( 'name' => array('aggregate' => 'count', 'output' => 'MoneyFormat'), array( 'name' => array('aggregate' => 'count', 'output' => 'MoneyFormat') ) );
	 * Aggregate values: 'count', 'sum', 'min', 'max', 'avg'
	 * @param $array
	 * @param $cols
	 * @return array
	 */
	static function PageBreakBy( $array, $cols ) {
		global $profiler;
		$profiler->startTimer( 'PageBreak()' );

		$group_by_cols = [];
		if ( is_array( $cols ) ) {
			foreach ( $cols as $key => $col ) {
				$group_by_cols[$col] = $col; //Use $col as the key so we can use isset() instead of in_array() later on.
			}
		}
		//Debug::Arr( $group_by_cols, 'Group By Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr( $aggregate_cols, 'Aggregate Columns: ', __FILE__, __LINE__, __METHOD__, 10);

		$retarr = [];
		$row_map = [];
		if ( is_array( $array ) ) {
			$i = 0;
			foreach ( $array as $row ) {
				if ( !is_array( $row ) ) {
					continue;
				}

				$group_by_key_val = null;
				foreach ( $group_by_cols as $group_by_element ) {
					if ( isset( $row[$group_by_element] ) ) {
						//Check if the value is an array with a 'sort' column, ie: array('sort' => 12345678, 'display' => '01-Jan-10' )
						if ( is_array( $row[$group_by_element] ) && isset( $row[$group_by_element]['sort'] ) ) {
							$group_by_key_val .= $row[$group_by_element]['sort'];
						} else {
							$group_by_key_val .= $row[$group_by_element];
						}
					}
				}
				//Debug::Text('Group By Key Val: '. $group_by_key_val, __FILE__, __LINE__, __METHOD__, 10);

				if ( !isset( $retarr[$group_by_key_val] ) ) {
					$retarr[$group_by_key_val] = [];
				}

				//Map the last row that each group_by_key_val was seen. Assume that the array is properly sorted first of course.
				$row_map[$group_by_key_val] = $i;

				foreach ( $row as $key => $val ) {
					//Debug::text(' aKey: '. $key .' Value: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $group_by_cols[$key] ) ) {
						//Only include a single key => value pair for the grouped columns to save memory.
						//If we are subtotaling, ignore all static columns.
						//Keep all columns even when sub-totalling so we can provide more information regarding the sub-total itself.
						if ( !isset( $retarr[$group_by_key_val][$key] ) ) {
							$retarr[$group_by_key_val][$key] = $val;
						}
					} // else { //Ignore data that isn't in grouping or aggregate.
				}

				$i++;
			}
		}

		//Substitude group_by_key_val with sparse row values so we know where to insert totals within the main array if sub-totaling.
		//Debug::Arr($row_map, ' Row Map: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $retarr ) && is_array( $row_map ) ) {
			foreach ( $row_map as $key => $count ) {
				//$retarr[$count] = $retarr[$key];  //Uncomment this to show the columns that are causing it to insert a page break.
				$retarr[$count]['_page_break'] = true;
				unset( $retarr[$key] );
			}
		}

		$profiler->stopTimer( 'PageBreak()' );

		return $retarr;
	}

	/**
	 * Need to use bcmath for large numbers, especially on 32bit PHP installs.
	 * @param $array
	 * @return int|string
	 */
	static function arraySum( $array ) {
		$retval = 0;
		foreach ( $array as $value ) {
			$retval = bcadd( $retval, $value );
		}

		return $retval;
	}

	/**
	 * @param $array
	 * @param $type
	 * @param bool $subtotal
	 * @return float|int|mixed|string
	 */
	static function aggregate( $array, $type, $subtotal = false ) {
		switch ( $type ) {
			default:
			case 'sum':
				//$retarr = array_sum($array);
				$retarr = self::arraySum( $array );
				break;
			case 'average':
			case 'avg':
				//$retarr = ( array_sum($array) / count($array) );
				$retarr = ( self::arraySum( $array ) / count( $array ) );
				break;
			case 'minimum':
			case 'min':
				$retarr = min( $array );
				break;
			case 'min_not_null':
				$retarr = self::MinNotNull( $array );
				break;
			case 'maximum':
			case 'max':
			case 'max_not_null':
				$retarr = max( $array );
				break;
			case 'first':
				reset( $array );
				$retarr = current( $array );
				break;
			case 'last':
				end( $array );
				$retarr = current( $array );
				break;
			case 'count':
				$retarr = count( $array );
				break;
			case 'concat':
				if ( $subtotal == false ) {
					//Pass $array through array_filter() without any callback, as that will remove any FALSE/NULL values, elimating strings like: ' -- -- -- -- '
					$retarr = implode( ' -- ', array_filter( $array ) ); //Using \n or <br> doesn't work properly on HTML/PDF reports, so just use " -- " for now.
				} else {
					//Don't concat for subtotal/total rows, as there is no point for text/string columns.
					$retarr = '';
				}
				break;
		}
		//Debug::Arr($array, 'Aggregate Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($retarr, 'Aggregate Result: Aggregate: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	/**
	 * @param $values
	 * @return mixed
	 */
	static function MinNotNull( $values ) {
		$arr_diff = array_diff( array_map( 'intval', $values ), [ 0 ] );
		if ( count($arr_diff) > 0 ) {
			return min( $arr_diff );
		}

		return false;
	}

}

?>
