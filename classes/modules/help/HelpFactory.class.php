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
 * @package Modules\Help
 */
class HelpFactory extends Factory {
	protected $table = 'help';
	protected $pk_sequence_name = 'help_id_seq'; //PK Sequence name


	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Form' ),
						20 => TTi18n::gettext( 'Page' ),
				];
				break;
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'NEW' ),
						15 => TTi18n::gettext( 'Pending Approval' ),
						20 => TTi18n::gettext( 'ACTIVE' ),
				];
				break;
		}

		return $retval;
	}


	/**
	 * @return int
	 */
	function getType() {
		return (int)$this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = trim( $value );

		$key = Option::getByValue( $value, $this->getOptions( 'type' ) );
		if ( $key !== false ) {
			$value = $key;
		}

		Debug::Text( 'bType: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return (int)$this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = trim( $value );

		$key = Option::getByValue( $value, $this->getOptions( 'status' ) );
		if ( $key !== false ) {
			$value = $key;
		}

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getHeading() {
		return $this->getGenericDataValue( 'heading' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHeading( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'heading', $value );
	}

	/**
	 * @return mixed
	 */
	function getBody() {
		return $this->getGenericDataValue( 'body' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBody( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'body', $value );
	}

	/**
	 * @return mixed
	 */
	function getKeywords() {
		return $this->getGenericDataValue( 'keywords' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setKeywords( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'keywords', $value );
	}

	/**
	 * @return bool
	 */
	function getPrivate() {
		return $this->fromBool( $this->getGenericDataValue( 'private' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrivate( $value ) {
		return $this->setGenericDataValue( 'private', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);
		// Status
		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status' ),
									  $this->getOptions( 'status' )
		);
		// Heading length
		if ( $this->getHeading() != null ) {
			$this->Validator->isLength( 'heading',
										$this->getHeading(),
										TTi18n::gettext( 'Incorrect Heading length' ),
										2, 255
			);
		}
		// Body
		if ( $this->getBody() != null ) {
			$this->Validator->isLength( 'body',
										$this->getBody(),
										TTi18n::gettext( 'Incorrect Body length' ),
										2, 2048
			);
		}
		// Keywords
		if ( $this->getKeywords() != null ) {
			$this->Validator->isLength( 'keywords',
										$this->getKeywords(),
										TTi18n::gettext( 'Incorrect Keywords length' ),
										2, 1024
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

}

?>
