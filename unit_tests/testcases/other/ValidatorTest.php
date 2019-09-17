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
 * @group DateTime
 */
class ValidatorTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('Etc/GMT+8', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//If using loadbalancer, we need to make a SQL query to initiate at least one connection to a database.
		//This is needed for testTimeZone() to work with the load balancer.
		global $db;
		$db->Execute( 'SELECT 1' );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function testValidatorIsFloat() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( $validator->isFloat( 'unit_test', 12.9 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', 12.91 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', 12.9123 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', 12.91234 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '12.9' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '12.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '12.9123' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '12.91234' ), TRUE );

		$this->assertEquals( $validator->isFloat( 'unit_test', -12.9 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', -12.91 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', -12.9123 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', -12.91234 ), TRUE );

		$this->assertEquals( $validator->isFloat( 'unit_test', '123.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '30 000.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1 234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1,234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1, 234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91 ' ), TRUE );

		$this->assertEquals( $validator->isFloat( 'unit_test', '1 234.91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1.234,91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '30 000,91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '1. 234,91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91 ' ), TRUE );

		$this->assertEquals( $validator->isFloat( 'unit_test', .91 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', ',91' ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', 12,9 ), TRUE );
		$this->assertEquals( $validator->isFloat( 'unit_test', '12,9' ), TRUE );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' AND TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( $validator->isFloat( 'unit_test', .91 ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ',91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', 12,9 ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '12,9' ), TRUE );

			$this->assertEquals( $validator->isFloat( 'unit_test', '123.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1 234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1,234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1, 234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1, 234.91 ' ), TRUE );

			$this->assertEquals( $validator->isFloat( 'unit_test', '1 234.91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1.234,91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', '1. 234,91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91' ), TRUE );
			$this->assertEquals( $validator->isFloat( 'unit_test', ' 1. 234,91 ' ), TRUE );
		}
	}

	function testValidatorStripNonFloat() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( $validator->stripNonFloat( 12.9 ), 12.9 );
		$this->assertEquals( $validator->stripNonFloat( 12.91 ), 12.91 );
		$this->assertEquals( $validator->stripNonFloat( 12.9123 ), 12.9123 );
		$this->assertEquals( $validator->stripNonFloat( 12.91234 ), 12.91234 );
		$this->assertEquals( $validator->stripNonFloat( '12.9' ), '12.9' );
		$this->assertEquals( $validator->stripNonFloat( '12.91' ), '12.91' );
		$this->assertEquals( $validator->stripNonFloat( '12.9123' ), '12.9123' );
		$this->assertEquals( $validator->stripNonFloat( '12.91234' ), '12.91234' );

		$this->assertEquals( $validator->stripNonFloat( -12.9 ), -12.9 );
		$this->assertEquals( $validator->stripNonFloat( -12.91 ), -12.91 );
		$this->assertEquals( $validator->stripNonFloat( -12.9123 ), -12.9123 );
		$this->assertEquals( $validator->stripNonFloat( -12.91234 ), -12.91234 );

		$this->assertEquals( $validator->stripNonFloat( '-123.91' ), '-123.91' );
		$this->assertEquals( $validator->stripNonFloat( '123.91' ), '123.91' );
		$this->assertEquals( $validator->stripNonFloat( '1234.91' ), '1234.91' );
		$this->assertEquals( $validator->stripNonFloat( '1 234.91' ), '1234.91' );
		$this->assertEquals( $validator->stripNonFloat( '1,234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( '1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91 ' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( $validator->stripNonFloat( '1 234.91' ), '1234.91' );
		$this->assertEquals( $validator->stripNonFloat( '1.234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( '1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91 ' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( $validator->stripNonFloat( .91 ), .91 );
		$this->assertEquals( $validator->stripNonFloat( ',91' ), '91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( 12,9 ), 12 ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( $validator->stripNonFloat( '12,9' ), '129' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( $validator->stripNonFloat( 'A123.91' ), '123.91' );
		$this->assertEquals( $validator->stripNonFloat( 'A123.91B' ), '123.91' );
		$this->assertEquals( $validator->stripNonFloat( '12A3.91' ), '123.91' );
		$this->assertEquals( $validator->stripNonFloat( '123A.91' ), '123.91' );
		$this->assertEquals( $validator->stripNonFloat( '123.A91' ), '123.91' );

		$this->assertEquals( $validator->stripNonFloat( '*&#$#\'"123.JKLFDJFL91' ), '123.91' );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' AND TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( $validator->stripNonFloat( .91 ), .91 );
			$this->assertEquals( $validator->stripNonFloat( ',91' ), '91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( 12,9 ), 12 ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( '12,9' ), '129' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

			$this->assertEquals( $validator->stripNonFloat( '123.91' ), '123.91' );
			$this->assertEquals( $validator->stripNonFloat( '1234.91' ), '1234.91' );
			$this->assertEquals( $validator->stripNonFloat( '1 234.91' ), '1234.91' );
			$this->assertEquals( $validator->stripNonFloat( '1,234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( '1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1, 234.91 ' ), '1234.91' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

			$this->assertEquals( $validator->stripNonFloat( '1 234.91' ), '1234.91' );
			$this->assertEquals( $validator->stripNonFloat( '1.234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( '1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( $validator->stripNonFloat( ' 1. 234,91 ' ), '1.23491' ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		}
	}
}
?>