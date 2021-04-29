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


/*
--	  id serial NOT NULL,
CREATE TABLE hierarchy_tree (
	tree_id integer DEFAULT 0 NOT NULL,
	parent_id integer DEFAULT 0 NOT NULL,
	object_id integer DEFAULT 0 NOT NULL,
	left_id bigint DEFAULT 0 NOT NULL,
	right_id bigint DEFAULT 0 NOT NULL
) WITHOUT OIDS;
--Order of the columsn in the below index seem to matter
create index "hierarchy_tree_left_id_right_id" on hierarchy_tree(left_id, right_id);
create index "hierarchy_tree_tree_id_object_id" on hierarchy_tree(tree_id, object_id);
create index "hierarchy_tree_tree_id_parent_id" on hierarchy_tree(tree_id, parent_id);
*/


/**
 * @package Core
 */
class FastTree {
	var $db = null;
	var $table = 'fast_tree';
	var $tree_id = 0;

	var $spacer = 0;

	/**
	 * FastTree constructor.
	 * @param null $options
	 */
	function __construct( $options = null ) {
		//Debug::Text(' Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

		$this->db = $options['db'];
		//Debug::Text(' Setting DB to: '. $options['db'], __FILE__, __LINE__, __METHOD__, 10);

		$this->table = $options['table'];
		//Debug::Text(' Setting Table to: '. $options['table'], __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $options['tree_id'] ) ) {
			$this->setTree( $options['tree_id'] );
			//$this->tree_id = $options['tree_id'];
			//Debug::Text(' Setting Tree ID to: '. $options['tree_id'], __FILE__, __LINE__, __METHOD__, 10);
		}

		return true;
	}

	/**
	 * @return int
	 */
	function getTree() {
		return $this->tree_id;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setTree( $id ) {
		if ( $id != '' ) {
			//Debug::Text(' Setting Tree ID to: '. $id, __FILE__, __LINE__, __METHOD__, 10);
			$this->tree_id = $id;

			$this->_setupTree();

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function _setupTree() {
		//Add the root node if its missing.
		$node_data = $this->getNode( 0 );
		if ( $node_data === false ) {
			Debug::Text( ' Initiating Tree with Root object: ', __FILE__, __LINE__, __METHOD__, 10 );
			$this->add( 0, -1 );

			return true;
		}

		//Debug::Text(' NOT Initiating Tree with Root object: ', __FILE__, __LINE__, __METHOD__, 10);
		return false;
	}

	/**
	 * @return mixed
	 */
	function getRootId() {
		$ph = [
				'tree_id' => $this->tree_id,
		];

		// get all children of this node
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = ? AND parent_id = -1';
		$root_id = $this->db->GetOne( $query, $ph );

		return $root_id;
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getNode( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		//Check to make sure object_id doesn't exceed 32bit integer.
		if ( !is_numeric( $object_id ) || ( $object_id > 2147483647 || $object_id < -2147483648 ) ) {
			Debug::Text( ' aReturning False', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$ph = [
				'tree_id'   => (int)$this->getTree(),
				'object_id' => (int)$object_id,
		];

		// get all children of this node
		$query = '	SELECT a.object_id, a.parent_id, a.left_id, a.right_id, count(b.object_id)-1 as level
					FROM ' . $this->table . ' a
					LEFT JOIN ' . $this->table . ' b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id

					WHERE a.tree_id = ?
						AND a.object_id = ?
					GROUP BY a.object_id, a.left_id, a.object_id, a.parent_id, a.right_id
				';
		$data = $this->db->GetRow( $query, $ph );

		if ( count( $data ) == 0 ) {
			return false;
		}

		return $data;
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getLevel( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->getNode( $object_id );
		if ( $data === false ) {
			return false;
		}

		return $data['level'];
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getRightId( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->getNode( $object_id );
		if ( $data === false ) {
			return false;
		}

		return $data['right_id'];
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getLeftId( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->getNode( $object_id );
		if ( $data === false ) {
			return false;
		}

		return $data['left_id'];
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getParentId( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->getNode( $object_id );
		if ( $data === false ) {
			return false;
		}

		return $data['parent_id'];
	}

	/**
	 * @param bool $object_id
	 * @return bool
	 */
	function rebuildTree( $object_id = false ) {
		Debug::Text( ' Object ID: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10 );

		$this->db->BeginTrans();
		$this->db->SetTransactionMode( 'SERIALIZABLE' ); //Serialize rebuild tree transactions so concurrency issues don't corrupt the tree.

		if ( $object_id === false ) {
			Debug::Text( ' Object ID not specified, using root: ', __FILE__, __LINE__, __METHOD__, 10 );
			$object_id = $this->getRootId();
			$left_id = 1;
		} else {
			Debug::Text( ' Object ID specified: ', __FILE__, __LINE__, __METHOD__, 10 );
			$left_id = $this->getLeftId( $object_id );
		}

		if ( $left_id === false ) {
			Debug::Text( ' Error getting left id: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( ' aObject ID: ' . $object_id . ' - Left ID: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10 );
		$rebuilt = $this->_rebuildTree( $object_id, $left_id );

		if ( $rebuilt === false ) {
			Debug::Text( ' Error rebuilding tree: ', __FILE__, __LINE__, __METHOD__, 10 );
			$this->db->RollBackTrans();

			return false;
		}

		//$this->db->RollBackTrans();

		$this->db->CommitTrans();

		$this->db->SetTransactionMode( '' ); //Restore default transaction mode.

		Debug::Text( ' Tree Rebuilt: ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param string $object_id UUID
	 * @param string $left_id   UUID
	 * @return bool
	 */
	function _rebuildTree( $object_id, $left_id ) {
		Debug::Text( ' Object ID: ' . $object_id . ' - Left: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10 );

		$ph = [
				'tree_id'   => (int)$this->getTree(),
				'parent_id' => (int)$object_id,
		];

		// get all children of this node
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = ? AND parent_id = ?';
		$rs = $this->db->Execute( $query, $ph );

		if ( !is_object( $rs ) ) {
			Debug::Text( ' Select failed', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		// the right value of this node is the left value + 1 (or more)
		$right_id = ( $left_id + 10 );

		while ( $row = $rs->FetchRow() ) {
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			Debug::Text( ' Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10 );
			$right_id = $this->_rebuildTree( $row['object_id'], $right_id );

			if ( $right_id === false ) {
				Debug::Text( ' Right was false: ', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		$ph = [
				'left_id'   => (int)$left_id,
				'right_id'  => (int)$right_id,
				'tree_id'   => (int)$this->getTree(),
				'object_id' => (int)$object_id,
		];

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$query = 'UPDATE ' . $this->table . ' SET left_id = ?, right_id = ? WHERE tree_id = ? AND object_id = ?';
		$rs = $this->db->Execute( $query, $ph );

		//Use this to help debug concurrency issues.
		//usleep(100000);

		if ( !is_object( $rs ) ) {
			Debug::Text( ' Rebuild Failed... ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		// return the right value of this node + 1
		return ( $right_id + 1 );
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getAllParents( $object_id ) {
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $object_id === '' ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$ph = [
				'tree_id'    => (int)$this->getTree(),
				'object_id'  => (int)$object_id,
				'object_id2' => (int)$object_id,
		];

		$query = '
				SELECT		b.object_id
				FROM		' . $this->table . ' as a
				LEFT JOIN ' . $this->table . ' as b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id
				WHERE		a.tree_id = ?
					AND		a.object_id = ?
					AND		b.object_id != 0
					AND		b.object_id != ?
				ORDER BY	b.left_id desc
				';

		return $this->db->GetCol( $query, $ph );
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 */
	function getChild( $object_id ) {
		if ( !is_numeric( $object_id ) ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$ph = [
				'tree_id'   => (int)$this->getTree(),
				'object_id' => (int)$object_id,
		];

		Debug::Text( ' Getting Last Child of: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10 );
		//Order by last child first.
		//GetOne() automatically sets LIMIT 1;
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = ? AND parent_id = ? ORDER BY left_id desc';
		$child_id = $this->db->GetOne( $query, $ph );

		//var_dump($child_id);

		return $child_id;
	}

	/**
	 * @param string $object_id UUID
	 * @param bool $recurse
	 * @param int $data_format
	 * @return array|bool
	 */
	function getAllChildren( $object_id = null, $recurse = false, $data_format = 0 ) {
		$original_object_id = $object_id;
		//Debug::Text(' Object ID: '. $object_id .' Recurse: '. $recurse, __FILE__, __LINE__, __METHOD__, 10);

		if ( $object_id === '' ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $object_id === null || $object_id === false ) {
			$object_id = $this->getRootId();
			Debug::Text( ' Getting Root ID: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$node_data = $this->getNode( $object_id );

		if ( $node_data === false ) {
			Debug::Text( ' Getting node data of object id failed.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		//Debug::Text(' Left ID: '. $node_data['left_id'] .' Level: '. $node_data['level'], __FILE__, __LINE__, __METHOD__, 10);

		$query = '
				SELECT		a.object_id, a.parent_id, count(b.object_id) as level
				FROM		' . $this->table . ' a
				LEFT JOIN ' . $this->table . ' b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id
				';
		switch ( strtoupper( $recurse ) ) {
			case 'RECURSE':
				$ph = [
						'tree_id'  => $this->getTree(),
						'left_id'  => $node_data['left_id'],
						'right_id' => $node_data['right_id'],
				];

				//Don't use >= <= (use > < ) - instead to not include the parent object.
				//Make sure current node is not included in the result as well. Otherwise we are saying the current node
				//is a child of itself.
				$query .= '
				WHERE		a.tree_id = ?
					AND		b.left_id > ?
					AND		b.right_id <= ?';

				//Exclude the parnet, but only when the passed object is forsure NULL!
				if ( $original_object_id === null || $original_object_id === false ) {
					$ph['object_id'] = $object_id;

					$query .= '
					AND		a.object_id != ?
					';
				}

				break;
			default:
				$ph = [
						'tree_id'   => (int)$this->getTree(),
						'object_id' => (int)$object_id,
				];

				$query .= '
						WHERE a.tree_id = ?
							AND a.parent_id = ?';
		}
		$query .= '
				GROUP BY	a.object_id, a.parent_id, a.left_id
				ORDER BY	a.left_id';

		$rs = $this->db->Execute( $query, $ph );

		$retarr = [];
		while ( $row = $rs->FetchRow() ) {
			if ( $data_format == 1 ) {
				$retarr[$row['object_id']] = $row;
			} else {
				$retarr[$row['object_id']] = $row['level'];
			}
		}

		if ( empty( $retarr ) == false ) {
			//Debug::Arr( $retarr, ' Children: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		return false;
	}

	/**
	 * @param string $parent_id UUID
	 * @return array|bool
	 */
	function _getLeftAndRightIds( $parent_id ) {
		Debug::Text( ' getLeftAndRightIds: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10 );

		$node_data = $this->getNode( $parent_id );

		$parent_left = $node_data['left_id'];
		$parent_right = $node_data['right_id'];

		$child_id = $this->getChild( $parent_id );
		if ( $child_id !== false ) {
			Debug::Text( ' Child found, getting Child data: ' . $child_id, __FILE__, __LINE__, __METHOD__, 10 );
			$child_node_data = $this->getNode( $child_id );
			$child_left_id = $child_node_data['left_id'];
			$child_right_id = $child_node_data['right_id'];
			unset( $child_node_data );
			Debug::Text( ' Child Left ID: ' . $child_left_id, __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Text( ' Child Right ID: ' . $child_right_id, __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Text( ' Parent Right ID: ' . $parent_right, __FILE__, __LINE__, __METHOD__, 10 );

			$left_id = ( $child_right_id + 1 );
			$right_id = ( $child_right_id + 10 );

			if ( $right_id >= $parent_right
					|| $left_id >= $parent_right ) {
				Debug::Text( ' NO CHILD GAP LEFT: ', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		} else {
			//Nothing yet.

			//Try to keep a large gap for these.
			$left_id = ( $parent_left + 1 );
			$right_id = ( $parent_right - 1 );

			if ( $right_id >= $parent_right
					|| $left_id >= $parent_right ) {
				Debug::Text( ' NO PARENT GAP LEFT: ', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		Debug::Text( ' Next Left ID: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( ' Next Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10 );

		return [ 'left_id' => (int)$left_id, 'right_id' => (int)$right_id ];
	}

	/**
	 * @param string $parent_id UUID
	 * @return bool
	 */
	function insertGaps( $parent_id ) {
		$this->spacer++;

		Debug::Text( ' Attempting to insert gaps: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10 );

		$node_data = $this->getNode( $parent_id );

		if ( $node_data != false ) {
			Debug::Text( ' Inserting gaps: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10 );

			$ph = [
					'tree_id'  => $this->getTree(),
					'right_id' => $node_data['right_id'],
			];

			$query = 'UPDATE ' . $this->table . ' SET right_id = right_id + 1000 WHERE tree_id = ? AND right_id >= ?';
			$this->db->Execute( $query, $ph );

			$query = 'UPDATE ' . $this->table . ' SET left_id = left_id + 1000 WHERE tree_id = ? AND left_id > ?';
			$this->db->Execute( $query, $ph );

			return true;
		}
		Debug::Text( ' Node Data Null: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * MPTT + Gap add function.
	 * @param string $object_id UUID
	 * @param int $parent_id
	 * @return bool
	 */
	function add( $object_id, $parent_id = 0 ) {
		Debug::Text( ' Object ID: ' . $object_id . ' Parent ID: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( !is_numeric( $object_id ) ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		/*
		if ( $object_id == $parent_id ) {
			Debug::Text(' bReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		*/
		//$insert_id = $this->db->GenID( $this->table.'_id_seq', 10);

		//Make sure object doesn't exist already
		if ( $this->getNode( $object_id ) !== false ) {
			Debug::Text( ' cReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$this->db->BeginTrans();

		if ( $parent_id == -1 ) {
			Debug::Text( ' Parent is 0', __FILE__, __LINE__, __METHOD__, 10 );

			$ph = [
					'tree_id' => $this->getTree(),
			];

			$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = ? AND parent_id = -1';
			$rs = $this->db->Execute( $query, $ph );

			if ( !is_object( $rs ) ) {
				Debug::Text( ' Select failed', __FILE__, __LINE__, __METHOD__, 10 );
				$this->db->RollBackTrans();

				return false;
			}

			if ( $rs->RowCount() > 0 ) {
				Debug::Text( ' A root node already exists', __FILE__, __LINE__, __METHOD__, 10 );
				$this->db->RollBackTrans();

				return false;
			}

			$left_id = 0;

			//Get max right_id, just incase other nodes exist in the tree.
			$ph = [
					'tree_id' => $this->getTree(),
			];

			$query = 'SELECT max(right_id) as right_id FROM ' . $this->table . ' WHERE tree_id = ?';
			$right_id = ( $this->db->GetOne( $query, $ph ) + 1000 );
		} else {
			Debug::Text( ' Parent IS NOT 0', __FILE__, __LINE__, __METHOD__, 10 );

			$left_and_right_ids = $this->_getLeftAndRightIds( $parent_id );

			if ( $left_and_right_ids === false ) {
				$this->insertGaps( $parent_id );
				$left_and_right_ids = $this->_getLeftAndRightIds( $parent_id );
			}

			$left_id = $left_and_right_ids['left_id'];
			$right_id = $left_and_right_ids['right_id'];
		}

		if ( is_numeric( $this->getTree() )
				&& is_numeric( $parent_id )
				&& is_numeric( $object_id )
				&& is_numeric( $left_id )
				&& is_numeric( $right_id ) ) {
			$ph = [
					'tree_id'   => (int)$this->getTree(),
					'parent_id' => (int)$parent_id,
					'object_id' => (int)$object_id,
					'left_id'   => (int)$left_id,
					'right_id'  => (int)$right_id,
			];

			Debug::Text( ' Inserting Node... Left ID: ' . $left_id . ' Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10 );
			$query = 'INSERT INTO ' . $this->table . ' (tree_id, parent_id, object_id, left_id, right_id) VALUES (?, ?, ?, ?, ?)';
			$rs = $this->db->Execute( $query, $ph );

			if ( !is_object( $rs ) ) {
				Debug::Text( ' Error inserting node', __FILE__, __LINE__, __METHOD__, 10 );
				$this->db->RollBackTrans();

				return false;
			}

			$this->db->CommitTrans();

			Debug::Text( ' Returning True.', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @param $array
	 * @param null $ph
	 * @return string
	 */
	protected function getListSQL( $array, &$ph = null ) {
		//Debug::Arr($ph, 'Place Holder BEFORE:', __FILE__, __LINE__, __METHOD__, 10);

		//Append $array values to end of $ph, return
		//one "?, " for each element in $array.

		$ph_arr = [];
		$array_count = count( $array );
		if ( is_array( $array ) && $array_count > 0 ) {
			foreach ( $array as $val ) {
				$ph_arr[] = '?';
				$ph[] = $val;
			}

			if ( empty( $ph_arr ) == false ) {
				$retval = implode( ',', $ph_arr );
			}
		} else if ( is_array( $array ) ) {
			//Return NULL, because this is an empty array.
			//This may have to return -1 instead of NULL
			//$ph[] = 'NULL';
			$ph[] = -1;
			$retval = '?';
		} else if ( $array == '' ) {
			//$ph[] = 'NULL';
			$ph[] = -1;
			$retval = '?';
		} else {
			$ph[] = $array;
			$retval = '?';
		}

		//Debug::Arr($ph, 'Place Holder AFTER:', __FILE__, __LINE__, __METHOD__, 10);

		//Just a single ID, return it.
		return $retval;
	}

	/**
	 * @param string $object_id UUID
	 * @param bool $recurse
	 * @return bool
	 */
	function delete( $object_id, $recurse = false ) {
		Debug::Text( ' Deleting Object: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $object_id == '' ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Find out if this node has children
		$this->db->BeginTrans();

		//This was the source of a bug that was causing the below recurse delete query
		//to delete the root node of the tree. getAllChildren was returning FALSE and array_keys()
		//was turning that into array(0 => 0), so we were deleting node 0 and XXX in a single operation.
		$children_ids = $this->getAllChildren( $object_id, 'RECURSE' );
		if ( $children_ids !== false && is_array( $children_ids ) ) {
			$children_ids = array_keys( $children_ids );
		} else {
			$children_ids = [];
		}

		if ( count( $children_ids ) == 0 ) {
			Debug::Text( ' No Children: ', __FILE__, __LINE__, __METHOD__, 10 );

			$ph = [
					'tree_id'   => (int)$this->getTree(),
					'object_id' => (int)$object_id,
			];

			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = ? AND object_id = ?';
			$this->db->Execute( $query, $ph );
		} else if ( strtolower( $recurse ) == 'recurse' ) {
			Debug::Arr( $children_ids, ' Recursing Delete - Current Object: ' . $object_id . ' Child IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

			$ph = [
					'tree_id' => $this->getTree(),
			];

			//Add current object_id to children for delete.
			$children_ids[] = $object_id;

			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = ? AND object_id in (' . $this->getListSQL( $children_ids, $ph ) . ')';
			$this->db->Execute( $query, $ph );
		} else {
			Debug::Text( ' Re-parenting children: ', __FILE__, __LINE__, __METHOD__, 10 );

			$parent_id = $this->getParentId( $object_id );

			$ph = [
					'tree_id'   => (int)$this->getTree(),
					'object_id' => (int)$object_id,
			];

			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = ? AND object_id = ?';
			$this->db->Execute( $query, $ph );

			$ph = [
					'parent_id' => (int)$parent_id,
					'tree_id'   => (int)$this->getTree(),
					'object_id' => (int)$object_id,
			];

			$query = '	UPDATE ' . $this->table . '
						SET parent_id = ?
						WHERE tree_id = ?
							AND parent_id = ?';
			$this->db->Execute( $query, $ph );
		}

		$this->db->CommitTrans();

		return true;
	}

	/**
	 * @param string $object_id UUID
	 * @param string $parent_id UUID
	 * @return bool
	 */
	function move( $object_id, $parent_id ) {
		Debug::Text( ' Object ID: ' . $object_id . ' Parent ID: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $object_id === '' ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $parent_id === '' ) {
			Debug::Text( ' bReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Make sure we don't reparent to self.
		$children_ids = array_keys( (array)$this->getAllChildren( $object_id, 'RECURSE' ) );

		if ( $parent_id != TTUUID::getZeroID() && is_array( $children_ids ) && in_array( $parent_id, $children_ids ) == true ) {
			Debug::Text( ' Objects cant be re-parented to their own children...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$this->db->BeginTrans();

		$ph = [
				'parent_id' => (int)$parent_id,
				'tree_id'   => (int)$this->getTree(),
				'object_id' => (int)$object_id,
		];

		$query = '	UPDATE ' . $this->table . '
					SET parent_id = ?
					WHERE tree_id = ?
						AND object_id = ?';
		$this->db->Execute( $query, $ph );

		//FIXME: rebuild tree starting from object_id and parent_id only perhaps?
		//Might cut down on some work.
		$this->rebuildTree();

		$this->db->CommitTrans();

		return true;
	}

	/**
	 * @param string $object_id     UUID
	 * @param string $new_object_id UUID
	 * @return bool
	 */
	function edit( $object_id, $new_object_id ) {
		Debug::Text( ' Object ID: ' . $object_id . ' New Object ID: ' . $new_object_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $object_id == '' ) {
			Debug::Text( ' aReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $new_object_id == '' ) {
			Debug::Text( ' bReturning False...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $object_id == $new_object_id ) {
			Debug::Text( ' Object is the same ', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		//Make sure new_object_id isn't already in the tree.
		if ( $this->getNode( $new_object_id ) === false ) {
			Debug::Text( ' Editing object ', __FILE__, __LINE__, __METHOD__, 10 );

			$this->db->BeginTrans();

			$ph = [
					'new_object_id' => (int)$new_object_id,
					'tree_id'       => (int)$this->getTree(),
					'object_id'     => (int)$object_id,
			];

			//Update parent IDs
			$query = '	UPDATE ' . $this->table . '
						SET parent_id = ?
						WHERE tree_id = ?
							AND parent_id = ?';
			$this->db->Execute( $query, $ph );

			//Update object ID
			$query = '	UPDATE ' . $this->table . '
						SET object_id = ?
						WHERE tree_id = ?
							AND object_id = ?';
			$this->db->Execute( $query, $ph );

			$this->db->CommitTrans();

			return true;
		} else {
			Debug::Text( ' New Object ID is already in the tree', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	/**
	 * Flex requires that all index keys start at 0, even in the children section,
	 * So we need to handle that as well so Flex doesn't need any post processing.
	 * @param $nodes
	 * @param bool $include_root
	 * @return array
	 */
	static function FormatFlexArray( $nodes, $include_root = true ) {
		Debug::Text( ' Formatting Flex Array...', __FILE__, __LINE__, __METHOD__, 10 );
		$nested = [];
		$depths = [];

		if ( is_array( $nodes ) ) {
			foreach ( $nodes as $node ) {
				if ( $node['level'] == 1 ) {

					//Using sequential keys
					$nested[] = $node; //Each new branch of the tree the key should start at 0 and be a sequence without holes.
					end( $nested );
					$depths[( $node['level'] + 1 )] = key( $nested );
					/*
					//Using non-sequential keys:
					$nested[$key] = $node;
					$depths[$node['level'] + 1] = $key;
					*/
				} else {
					$parent =& $nested;
					for ( $i = 2; $i <= $node['level']; $i++ ) {
						//In cases where parent nodes were deleted without reparenting, prevent PHP warning.
						if ( !isset( $depths[$i] ) ) {
							$depths[$i] = 0;
						}

						if ( $i == 2 ) {
							$parent =& $parent[$depths[$i]];
						} else {
							$parent =& $parent['children'][$depths[$i]];
						}
					}

					//Using sequential keys.
					$parent['children'][] = $node; //Each new branch of the tree the key should start at 0 and be a sequence without holes.
					end( $parent['children'] );
					$depths[( $node['level'] + 1 )] = key( $parent['children'] );
					/*
					//Using non-sequential keys:
					$parent['children'][$key] = $node;
					$depths[$node['level'] + 1] = $key;
					*/
				}
			}
		}

		if ( $include_root == true ) {
			return [
					0 => [
							'id'       => 0,
							'name'     => TTi18n::getText( 'Root' ),
							'level'    => 0,
							'children' => $nested,
					],
			];
		} else {
			return $nested;
		}
	}

	/**
	 * @param $nodes
	 * @param string $type
	 * @param bool $include_root
	 * @return array|bool
	 */
	static function FormatArray( $nodes, $type = 'HTML', $include_root = false ) {
		$type = strtolower( $type );

		if ( $include_root === true ) {
			if ( !is_array( $nodes ) ) {
				$nodes = [];
			}

			$root_node = [
					'id'    => 0,
					'name'  => 'Root',
					'level' => 0,
			];

			array_unshift( $nodes, $root_node );
		}

		if ( $nodes === false ) {
			return false;
		}

		$retarr = [];
		foreach ( $nodes as $node ) {
			switch ( $type ) {
				case 'no_tree_text':
					$spacing = str_repeat( '|  &nbsp;', ( $node['level'] * 1 ) );
					$text = $node['name'];
					break;
				case 'text':
					$spacing = str_repeat( '|  &nbsp;', ( $node['level'] * 1 ) );
					$text = $spacing . $node['name'];
					break;
				case 'plain_text':
					$spacing = str_repeat( '|  ', ( $node['level'] * 1 ) );
					$text = $spacing . $node['name'];
					break;
				case 'html':
					$width = ( ( $node['level'] - 1 ) * 20 );
					$spacing = '<img src="' . Environment::getBaseURL() . 'images/s.gif" width="' . $width . '">';
					$text = $spacing . ' ' . $node['name'];
					break;
				case 'array':
					break;
			}

			$node['spacing'] = $spacing;
			$node['text'] = $text;

			$retarr[] = $node;

			unset( $node );
		}

		return $retarr;
	}
}

?>
