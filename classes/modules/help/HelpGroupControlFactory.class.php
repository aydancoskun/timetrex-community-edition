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
 * @package Modules\Help
 */
class HelpGroupControlFactory extends Factory {
	protected $table = 'help_group_control';
	protected $pk_sequence_name = 'help_group_control_id_seq'; //PK Sequence name

	/**
	 * @return mixed
	 */
	function getScriptName() {
		return $this->getGenericDataValue( 'script_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScriptName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'script_name', $value );
	}

	/**
	 * @return mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return array|bool
	 */
	function getHelp() {
		$hglf = TTnew( 'HelpGroupListFactory' ); /** @var HelpGroupListFactory $hglf */
		$hglf->getByHelpGroupControlId( $this->getId() );
		foreach ( $hglf as $help_group_obj ) {
			$help_list[] = $help_group_obj->getHelp();
		}

		if ( isset( $help_list ) ) {
			return $help_list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setHelp( $ids ) {
		//If needed, delete mappings first.
		$hglf = TTnew( 'HelpGroupListFactory' ); /** @var HelpGroupListFactory $hglf */
		$hglf->getByHelpGroupControlId( $this->getId() );

		$help_ids = [];
		foreach ( $hglf as $help_group_entry ) {
			$help_id = $help_group_entry->getHelp();
			Debug::text( 'Help ID: ' . $help_group_entry->getHelp(), __FILE__, __LINE__, __METHOD__, 10 );

			//Delete all items first.
			$help_group_entry->Delete();
		}

		if ( is_array( $ids ) && count( $ids ) > 0 ) {

			//Insert new mappings.
			$hgf = TTnew( 'HelpGroupFactory' ); /** @var HelpGroupFactory $hgf */
			$i = 0;
			foreach ( $ids as $id ) {
				//if ( !in_array($id, $help_ids) ) {
				$hgf->setHelpGroupControl( $this->getId() );
				$hgf->setOrder( $i );
				$hgf->setHelp( $id );


				if ( $this->Validator->isTrue( 'help',
											   $hgf->Validator->isValid(),
											   TTi18n::gettext( 'Incorrect Help Entry' ) ) ) {
					$hgf->save();
				}
				//}
				$i++;
			}
			//return TRUE;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Script Name
		$this->Validator->isLength( 'script_name',
									$this->getScriptName(),
									TTi18n::gettext( 'Incorrect Script Name' ),
									2, 255
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Incorrect Name' ),
									2, 255
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

}

?>
