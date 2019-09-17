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
 * @package Core
 */
class TTTree {
	/**
	 * Format flat array for JS tree grid.
	 * @param $nodes
	 * @param bool $include_root
	 * @return array
	 */
	static function FormatArray( $nodes, $include_root = TRUE ) {
		Debug::Text(' Formatting Array...', __FILE__, __LINE__, __METHOD__, 10);

		$nodes = self::createNestedArrayWithDepth( $nodes );

		if ( $include_root == TRUE ) {
			return array( 0 => array(	 'id' => '00000000-0000-0000-0000-000000000000',
										 'name' => TTi18n::getText('Root'),
										 'level' => 0,
										 'children' => $nodes )
			);
		} else {
			return $nodes;
		}

		return $nodes;
	}

	/**
	 * Flatten a nested array.
	 * @param $nodes
	 * @return array
	 */
	static function flattenArray( $nodes ) {
		$retarr = array();
		foreach ($nodes as $key => $node) {
			if ( isset($node['children']) ) {
				$retarr = array_merge( $retarr, self::flattenArray($node['children']) );
            	unset($node['children']);
            	$retarr[] = $node;
        	} else {
				$retarr[] = $node;
			}
		}

		return $retarr;
	}

	/**
	 * Get one specific element from all nodes in nested array.
	 * @param $nodes
	 * @param string $key
	 * @return array
	 */
	static function getElementFromNodes( $nodes, $key = 'id' ) {
		$retarr = array();
		if ( is_array($nodes ) ) {
			foreach( $nodes as $node ) {
				$retarr[] = $node[$key];
				if ( isset($node['children']) ) {
					$retarr[] = self::getElementFromNodes( $node['children'] );
				}
			}
		}

		return $retarr;
	}

	/**
	 * Get just the children of a specific parent.
	 * @param $nodes
	 * @param string $parent_id
	 * @return array
	 */
	static function getAllChildren( $nodes, $parent_id = '00000000-0000-0000-0000-000000000000' ) {
		$nodes = self::createNestedArrayWithDepth( $nodes, $parent_id );

		return $nodes;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	static function sortByName( $a, $b ) {
		if ( $a['name'] == $b['name'] ) {
			return 0;
		}
		return ( $a['name'] < $b['name'] ) ? -1 : 1;
	}

	/**
	 * Takes a flat array of nodes typically straight from the database and converts into a nested array with depth/level values.
	 * @param $nodes
	 * @param string $parent_id
	 * @param int $depth
	 * @return array
	 */
	static function createNestedArrayWithDepth( $nodes, $parent_id = '00000000-0000-0000-0000-000000000000', $depth = 1 ) {
		$retarr = array();

		if ( is_array($nodes ) ) {
			uasort( $nodes, array( 'self', 'sortByName' ) );
			foreach ( $nodes as $element ) {
				$element['level'] = $depth;
				if ( isset($element['parent_id']) AND isset($element['id']) AND $element['parent_id'] == $parent_id ) {
					$children = self::createNestedArrayWithDepth( $nodes, $element['id'], ( $depth + 1 ) );
					if ( $children ) {
						uasort( $children, array( 'self', 'sortByName' ) );
						$element['children'] = $children;
					}

					$retarr[] = $element;
				}
			}
		}

		return $retarr;
	}
}
?>
