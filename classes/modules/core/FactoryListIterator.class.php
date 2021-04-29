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
class FactoryListIterator implements Iterator {
	private $template_obj;
	private $template_validator_obj;
	private $obj;
	private $rs;
	private $class_name;

	/**
	 * FactoryListIterator constructor.
	 * @param object $obj
	 */
	function __construct( $obj ) {
		$this->class_name = get_class( $obj );

		//Save a cleanly instantiated object in memory so we can simply clone it rather than instantiate a new one every loop iteration in current()
		//  It appears this doesn't work for the sub-objects like Validator. If one iteration in a loop has a validation error, all the rest will too.
		$this->template_obj = new $this->class_name();
		$this->template_validator_obj = new Validator();

		if ( isset( $obj->rs ) ) {
			$this->rs = $obj->rs;
		}

		$this->obj = $obj;
	}

	/**
	 * @return bool
	 */
	function rewind() {
		if ( isset( $this->obj->rs ) ) {
			$this->obj->rs->MoveFirst();
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function valid() {
		if ( isset( $this->obj->rs ) ) {
			return !$this->obj->rs->EOF;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function key() {
		return $this->obj->rs->_currentRow;
	}

	/**
	 * @return mixed
	 */
	function current() {
		if ( isset( $this->obj->rs ) ) {            //Stop some warnings from coming up?
			//This automatically resets the object during each iteration in a foreach()
			//Without this, data can persist and cause undesirable results.

			//  It appears this doesn't work for the sub-objects like Validator. If one iteration in a loop has a validation error, all the rest will too.
			//  Tested in: FactoryTest.php->testFactoryListIteratorA()
			$this->obj = clone $this->template_obj; //Copy the template object to avoid having to instantiate it each loop iteration. This is about 30% faster.
			$this->obj->tmp_data = [];
			$this->obj->Validator = clone $this->template_validator_obj;      //Clone sub-objects here, rather than in the __clone function, as it seems to be about 10% faster here.
			$this->obj->is_valid = false;
//			$this->obj = new $this->class_name();

			$this->obj->rs = $this->rs;

			//Set old_data at the same time as data, so we can check to see what fields have changed by using getDataDifferences() in any other function (ie: Validate,preSave,postSave)
			//This used to be done in getUpdateQuery(), but that was too late for Validate/preSave().
			$this->obj->data = $this->obj->old_data = $this->obj->rs->fields; //Orignal
		}

		return $this->obj;
	}

	function next() {
		$this->obj->rs->MoveNext();
	}
}

?>
