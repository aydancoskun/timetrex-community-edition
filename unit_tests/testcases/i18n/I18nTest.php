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
 * @group i18nTest
 */
class i18nTest extends PHPUnit_Framework_TestCase {

	function setUp() {
		TTi18n::setLocale( 'en_CA' );
	}

	function tearDown() {
		TTi18n::setLocale( 'en_CA' );
	}


	function testTryLocale() {
		TTi18n::setLocale( 'en_US' );
		$this->assertEquals( substr( TTi18n::getLocale(), 0, 5 ), 'en_US' );

		$this->assertEquals( TTi18n::getLanguage(), 'en' );

		$this->assertEquals( TTi18n::getCountry(), 'US' );

		$this->assertEquals( TTi18n::getCurrencySymbol( 'USD' ), '$' );

		$this->assertEquals( TTi18n::tryLocale( 'en_CA.UTF-8' ), 'en_CA.UTF-8' );
		$this->assertEquals( TTi18n::tryLocale( 'en_US.utf-8' ), 'en_US.utf-8' );
		$this->assertEquals( TTi18n::tryLocale( 'en_US.utf8' ), 'en_US.utf8' );
		$this->assertEquals( TTi18n::tryLocale( 'en_US.uTf-8' ), 'en_US.uTf-8' );
		$this->assertEquals( TTi18n::tryLocale( 'NOT REAL' ), false );
		//$this->assertEquals( TTi18n::tryLocale( 'en_CA' ), FALSE ); //Some installs may have this locale.
	}

	function testMisc() {
		if ( extension_loaded( 'intl' ) == false ) {
			return true;
		}

		//$expected_lang_arr = array('en' => 'English', 'da' => 'Danish (UO)', 'de' => 'German (UO)', 'es' => 'Spanish (UO)', 'id' => 'Indonesian (UO)', 'it' => 'Italian (UO)', 'fr' => 'French (UO)', 'pt' => 'Portuguese (UO)', 'ar' => 'Arabic (UO)', 'zh' => 'Chinese (UO)', 'yi' => 'Yiddish (UO)');
		//$expected_lang_arr = array('en' => 'English', 'da' => 'Dansk (UO)', 'de' => 'Deutsch (UO)', 'es' => 'Español (UO)', 'id' => 'Bahasa Indonesia (UO)', 'it' => 'Italiano (UO)', 'fr' => 'Français (UO)', 'pt' => 'Português (UO)', 'ar' => 'العربية (UO)', 'zh' => '中文 (UO)', 'yi' => 'ייִדיש (UO)');
		//$expected_lang_arr = array('en' => 'English', 'da' => 'Dansk (UO)', 'de' => 'Deutsch (UO)', 'es' => 'Español (UO)', 'id' => 'Indonesia (UO)', 'it' => 'Italiano (UO)', 'fr' => 'Français (UO)', 'pt' => 'Português (UO)', 'ar' => 'العربية (UO)', 'zh' => '中文 (UO)' );
		$expected_lang_arr = [ 'en' => 'English', 'de' => 'Deutsch (UO)', 'es' => 'Español (UO)', 'id' => 'Indonesia (UO)', 'fr' => 'Français (UO)', 'hu' => 'Magyar (UO)' ];
		unset( $expected_lang_arr['id'] ); //It seems Indonesian changes depending on the Ubuntu version, so just ignore it.

		TTi18n::setLocale( 'en_CA' );
		$lang_arr = TTi18n::getLanguageArray();
		unset( $lang_arr['id'] ); //It seems Indonesian changes depending on the Ubuntu version, so just ignore it.
		array_pop( $lang_arr ); //Pop off Yiddish as it may not be installed everywhere.
		$this->assertEquals( $lang_arr, $expected_lang_arr );

		TTi18n::setLocale( 'es_ES' );
		$lang_arr = TTi18n::getLanguageArray();
		unset( $lang_arr['id'] ); //It seems Indonesian changes depending on the Ubuntu version, so just ignore it.
		array_pop( $lang_arr ); //Pop off Yiddish as it may not be installed everywhere.
		$this->assertEquals( $lang_arr, $expected_lang_arr );

		TTi18n::setLocale( 'en_CA' );
		$this->assertEquals( TTi18n::getNormalizedLocale(), 'en_US' );
		$this->assertEquals( TTi18n::getLocaleCookie(), false );
		$this->assertEquals( TTi18n::getLanguageFromLocale(), 'en' );
		$this->assertEquals( TTi18n::getLanguageFromLocale( 'fr_CH.utf8' ), 'fr' );
	}

	function testFormatCurrency() {
		if ( extension_loaded( 'intl' ) == false ) {
			return true;
		}

		//Canadian Dollars - INTL wants to us CA$ rather than just $.
		TTi18n::setLocale( 'en_CA' );
		$this->assertEquals( TTi18n::formatCurrency( 8.9901 ), '$8.99' );
		$this->assertEquals( TTi18n::formatCurrency( -87.9901 ), '-$87.99' );
		$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'JPY', 0 ), '¥8,799' );

		//US Dollars
		TTi18n::setLocale( 'en_US' );
		$this->assertEquals( TTi18n::formatCurrency( 8.9901 ), '$8.99' );
		$this->assertEquals( TTi18n::formatCurrency( -87.9901 ), '-$87.99' );
		$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'JPY', 0 ), '¥8,799' );

		//test euros
		TTi18n::setLocale( 'it_IT' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( TTi18n::formatCurrency( 8.9901 ), '8,99 €' );
			$this->assertEquals( TTi18n::formatCurrency( -87.9901 ), '-87,99 €' );
			$this->assertEquals( TTi18n::formatCurrency( 1888799.012345 ), '1.888.799,01 €' );
			$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'EUR', 1 ), 'EUR 8.799,01 €' );
			$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'EUR', 0 ), '8.799,01 €' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( TTi18n::formatCurrency( 8.9901 ), '8,99 €' );
			$this->assertEquals( TTi18n::formatCurrency( -87.9901 ), '-87,99 €' );
			$this->assertEquals( TTi18n::formatCurrency( 1888799.012345 ), '1.888.799,01 €' );
			$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'EUR', 1 ), 'EUR 8.799,01 €' );
			$this->assertEquals( TTi18n::formatCurrency( 8799.012345, 'EUR', 0 ), '8.799,01 €' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}
	}

	function testNumberFormat() {
		//english locales use #,###.##
		TTi18n::setLocale( 'en_CA' );
		$this->assertEquals( TTi18n::formatNumber( 8.9901 ), '8.99' );
		$this->assertEquals( TTi18n::formatNumber( -87.9901 ), '-87.99' );
		$this->assertEquals( TTi18n::formatNumber( -87.9991 ), '-88' );
		$this->assertEquals( TTi18n::formatNumber( 8987.990122 ), '8,987.99' );
		$this->assertEquals( TTi18n::formatNumber( 1234.990122 ), '1,234.99' );
		$this->assertEquals( TTi18n::formatNumber( 123456.990122 ), '123,456.99' );
		$this->assertEquals( TTi18n::formatNumber( 123456789.990122 ), '123,456,789.99' );
		$this->assertEquals( TTi18n::formatNumber( 12345.12345 ), '12,345.12' );
		$this->assertEquals( TTi18n::formatNumber( -12345.12345 ), '-12,345.12' );
		$this->assertEquals( TTi18n::formatNumber( 123.12345 ), '123.12' );
		$this->assertEquals( TTi18n::formatNumber( -123.12345 ), '-123.12' );

		$this->assertEquals( TTi18n::formatNumber( -87.990122, true, 2, 6 ), '-87.990122' );
		$this->assertEquals( TTi18n::formatNumber( -87.9000, true, 2, 4 ), '-87.9000' );
		$this->assertEquals( TTi18n::formatNumber( -87.9901, true, 2, 4 ), '-87.9901' );
		$this->assertEquals( TTi18n::formatNumber( 1234.990122, true, 2, 4 ), '1,234.9901' );
		$this->assertEquals( TTi18n::formatNumber( 123456.990122, true, 3, 5 ), '123,456.99012' );
		$this->assertEquals( TTi18n::formatNumber( 123456789.990122, true, 2, 4 ), '123,456,789.9901' );

		//spanish locales show numbers as # ###,##
		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( TTi18n::formatNumber( 8.9901 ), '8,99' );
			$this->assertEquals( TTi18n::formatNumber( -87.9901 ), '-87,99' );
			$this->assertEquals( TTi18n::formatNumber( -87.9901 ), '-87,99' );
			$this->assertEquals( TTi18n::formatNumber( 8987.990122 ), '8.987,99' );
			$this->assertEquals( TTi18n::formatNumber( 1234.990122 ), '1.234,99' );
			$this->assertEquals( TTi18n::formatNumber( 123456789.990122 ), '123.456.789,99' );

			$this->assertEquals( TTi18n::formatNumber( -87.99015555, true, 2, 6 ), '-87,990156' ); //rounding
			$this->assertEquals( TTi18n::formatNumber( -87.9000, true, 2, 4 ), '-87,90' );
			$this->assertEquals( TTi18n::formatNumber( -87.990155, true, 2, 4 ), '-87,9902' ); //rounding
			$this->assertEquals( TTi18n::formatNumber( 1234.990122, true, 2, 4 ), '1.234,9901' );
			$this->assertEquals( TTi18n::formatNumber( 123456789.990122, true, 2, 4 ), '123.456.789,9901' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}

		//comparing TTi18n::formatNumber to Misc::MoneyFormat due to high usage of the MoneyFormat() in existing code.
		TTi18n::setLocale( 'en_CA' );
		$this->assertEquals( TTi18n::formatNumber( 12345.152, true, 2, 2 ), '12,345.15' );
		$this->assertEquals( TTi18n::formatNumber( 12345.151, false ), '12,345.15' );
		$this->assertEquals( TTi18n::formatNumber( 12345.15, true ), '12,345.15' );
		$this->assertEquals( TTi18n::formatNumber( 12345.15, false ), '12,345.15' );
		$this->assertEquals( TTi18n::formatNumber( 12345.1, true ), '12,345.10' );
		$this->assertEquals( TTi18n::formatNumber( 12345.5, false ), '12,345.5' );
		$this->assertEquals( TTi18n::formatNumber( 12345.12345 ), '12,345.12' );
		$this->assertEquals( TTi18n::formatNumber( -12345.12345 ), '-12,345.12' );
		$this->assertEquals( TTi18n::formatNumber( 123.12345 ), '123.12' );
		$this->assertEquals( TTi18n::formatNumber( -123.12345 ), '-123.12' );
		$this->assertEquals( TTi18n::formatNumber( 123 ), '123' );
		$this->assertEquals( TTi18n::formatNumber( -123 ), '-123' );

		$this->assertEquals( Misc::MoneyFormat( 12345.152, true ), '12,345.15' );
		$this->assertEquals( Misc::MoneyFormat( 12345.151, false ), '12345.15' );
		$this->assertEquals( Misc::MoneyFormat( 12345.15, true ), '12,345.15' );
		$this->assertEquals( Misc::MoneyFormat( 12345.15, false ), '12345.15' );
		$this->assertEquals( Misc::MoneyFormat( 12345.1, true ), '12,345.10' );
		$this->assertEquals( Misc::MoneyFormat( 12345.5, false ), '12345.50' );
		$this->assertEquals( Misc::MoneyFormat( 12345.12345 ), '12,345.12' );
		$this->assertEquals( Misc::MoneyFormat( -12345.12345 ), '-12,345.12' );
		$this->assertEquals( Misc::MoneyFormat( 123.12345 ), '123.12' );
		$this->assertEquals( Misc::MoneyFormat( -123.12345 ), '-123.12' );
	}

	function testBeforeAndAfterDecimal() {
		$this->assertEquals( Misc::getBeforeDecimal( 12345.92345 ), '12345' );
		$this->assertEquals( Misc::getBeforeDecimal( -12345.92345 ), '-12345' );
		$this->assertEquals( Misc::getAfterDecimal( 12345.92345, false ), '92345' );
		$this->assertEquals( Misc::getAfterDecimal( -12345.92345, false ), '92345' );

		TTi18n::setLocale( 'es_ES' );
		$this->assertEquals( Misc::getBeforeDecimal( 12345.92345 ), '12345' );
		$this->assertEquals( Misc::getBeforeDecimal( -12345.92345 ), '-12345' );
		$this->assertEquals( Misc::getAfterDecimal( 12345.92345, false ), '92345' );
		$this->assertEquals( Misc::getAfterDecimal( -12345.92345, false ), '92345' );
	}

	function testParseFloatFunctions() {
		if ( extension_loaded( 'intl' ) == false ) {
			return true;
		}

		TTi18n::setLocale( 'en_US' );
		$this->assertEquals( TTI18n::parseFloat( '1,234.123' ), '1234.123' );
		$this->assertEquals( TTI18n::parseFloat( '1, 234.123' ), '1234.123' );
		$this->assertEquals( TTI18n::parseFloat( '12.91' ), '12.91' );
		$this->assertEquals( TTI18n::parseFloat( '-12.91' ), '-12.91' );
		$this->assertEquals( TTI18n::parseFloat( (float)12.123 ), '12.123' );
		$this->assertEquals( TTI18n::parseFloat( '0.00' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( '0' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( 0 ), '0' );

		$this->assertEquals( TTI18n::parseFloat( '' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( true ), '0' );
		$this->assertEquals( TTI18n::parseFloat( false ), '0' );
		$this->assertEquals( TTI18n::parseFloat( null ), '0' );

		$this->assertEquals( TTI18n::parseFloat( INF ), '0' );
		$this->assertEquals( TTI18n::parseFloat( -INF ), '0' );
		$this->assertEquals( TTI18n::parseFloat( +INF ), '0' );
		$this->assertEquals( TTI18n::parseFloat( acos( 8 ) ), '0' ); //acos(8) = NaN

		//
		//Test parsing both comma and decimal separated in a locale that uses just decimal separator
		//
		$this->assertEquals( TTI18n::parseFloat( '1.234,123' ), '1234.123' );
		$this->assertEquals( TTI18n::parseFloat( '1. 234,123' ), '1234.123' );
		$this->assertEquals( TTI18n::parseFloat( '12,91' ), '12.91' );

		$this->assertEquals( TTI18n::parseFloat( '.12' ), '0.12' );
		$this->assertEquals( TTI18n::parseFloat( ',12' ), '0.12' );
		$this->assertEquals( TTI18n::parseFloat( '-.12' ), '-0.12' );
		$this->assertEquals( TTI18n::parseFloat( '-,12' ), '-0.12' );

		$this->assertEquals( TTI18n::parseFloat( '0.12' ), '0.12' );
		$this->assertEquals( TTI18n::parseFloat( '0,12' ), '0.12' );
		$this->assertEquals( TTI18n::parseFloat( '-0.12' ), '-0.12' );
		$this->assertEquals( TTI18n::parseFloat( '-0,12' ), '-0.12' );

		$this->assertEquals( TTI18n::parseFloat( '12.9', 1 ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
		$this->assertEquals( TTI18n::parseFloat( '12.91', 2 ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
		$this->assertEquals( TTI18n::parseFloat( '12.912', 3 ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
		$this->assertEquals( TTI18n::parseFloat( '12.9123', 4 ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

		$this->assertEquals( TTI18n::parseFloat( '12,9', 1 ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
		$this->assertEquals( TTI18n::parseFloat( '12,91', 2 ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
		$this->assertEquals( TTI18n::parseFloat( '12,912', 3 ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
		$this->assertEquals( TTI18n::parseFloat( '12,9123', 4 ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

		$this->assertEquals( TTI18n::parseFloat( '12.9' ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
		$this->assertEquals( TTI18n::parseFloat( '12.91' ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
		$this->assertEquals( TTI18n::parseFloat( '12.912' ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
		$this->assertEquals( TTI18n::parseFloat( '12.9123' ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.

		$this->assertEquals( TTI18n::parseFloat( '12,9' ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
		$this->assertEquals( TTI18n::parseFloat( '12,91' ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
		$this->assertEquals( TTI18n::parseFloat( '12,912' ), '12912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
		$this->assertEquals( TTI18n::parseFloat( '12,9123' ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

		$this->assertEquals( TTI18n::parseFloat( '123' ), '123.00' );
		$this->assertEquals( TTI18n::parseFloat( '1, 234' ), '1234' ); //Ambiguous as it could be assumed to be 1,234, or 1.234.
		$this->assertEquals( TTI18n::parseFloat( '1. 234' ), '1.234' ); //Ambiguous as it could be assumed to be 1,234, or 1.234 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.

		$this->assertEquals( TTI18n::parseFloat( '123.91' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '123,91' ), '123.91' );

		$this->assertEquals( TTI18n::parseFloat( '1, 234.91' ), '1234.91' );
		$this->assertEquals( TTI18n::parseFloat( '1. 234,91' ), '1234.91' );

		$this->assertEquals( TTI18n::parseFloat( '123 456 789.91' ), '123456789.91' );
		$this->assertEquals( TTI18n::parseFloat( '123 456 789,91' ), '123456789.91' );
		$this->assertEquals( TTI18n::parseFloat( '123, 456, 789.91' ), '123456789.91' );
		$this->assertEquals( TTI18n::parseFloat( '123. 456. 789,91' ), '123456789.91' );
		$this->assertEquals( TTI18n::parseFloat( '123. 456. 789,912' ), '123456789.912' );
		$this->assertEquals( TTI18n::parseFloat( '123. 456. 789,912345678' ), '123456789.912345678' );

		$this->assertEquals( TTI18n::parseFloat( '1,234,567,890,123,456,789,000.123' ), '1234567890123456789000.123' );
		$this->assertEquals( TTI18n::parseFloat( '1,234,567,890,123,456,789,123.123' ), '1234567890123456789123.123' );

		$this->assertEquals( TTI18n::parseFloat( '0,00' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( '0' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( 0 ), '0' );

		//Test floats with other bogus characters.
		$this->assertEquals( TTI18n::parseFloat( '$123.91ABC%' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '$123,91ABC%' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( 'A123.91' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( 'A123.91B' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '12A3.91' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '123A.91' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '123.A91' ), '123.91' );
		$this->assertEquals( TTI18n::parseFloat( '*&#$#\'"123.JKLFDJFL91%' ), '123.91' );

		$this->assertEquals( TTI18n::parseFloat( '54333.12', 2 ), '54333.12' ); //Has only one separator, and input matches precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.12', 3 ), '54333.12' ); //Has only one separator, and input is 2 decimal places which DOES NOT match precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.123', 3 ), '54333.123' ); //Has only one separator, and input matches precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.1234', 4 ), '54333.1234' ); //Has only one separator, and input matches precision value.

		//Make sure parseFloat() can handle output from formatNumber()
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.1234' ) ), '54333.12' ); //Auto formatting decimals.
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.12' ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.123', true, 3, 3 ), 3 ), '54333.123' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.1234', true, 4, 4 ), 4 ), '54333.1234' );

		//Make sure parseFloat() can handle output from formatCurrency()
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234' ) ), '54333.12' ); //Auto formatting decimals.
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.12' ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.123' ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234' ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234', 'EUR', true ) ), '54333.12' ); //Auto formatting decimals.
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.12', 'EUR', true ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.123', 'EUR', true ) ), '54333.12' );
		$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234', 'EUR', true ) ), '54333.12' );


		TTi18n::setLocale( 'fr_CA' );
		$this->assertEquals( TTI18n::parseFloat( '54333.12', 2 ), '54333.12' ); //Has only one separator, and input matches precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.12', 3 ), '54333.12' ); //Has only one separator, and input is 2 decimal places which DOES NOT match precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.123', 3 ), '54333.123' ); //Has only one separator, and input matches precision value.
		$this->assertEquals( TTI18n::parseFloat( '54333.1234', 4 ), '54333.1234' ); //Has only one separator, and input matches precision value.
		$this->assertEquals( TTI18n::parseFloat( '1.234,123' ), '1234.123' ); //Has both separators, so can be parsed properly.
		$this->assertEquals( TTI18n::parseFloat( '1. 234,123' ), '1234.123' ); //Has both separators, so can be parsed properly.
		$this->assertEquals( TTI18n::parseFloat( '1 234,91' ), '1234.91' );
		$this->assertEquals( TTI18n::parseFloat( '12,91' ), '12.91' );
		$this->assertEquals( TTI18n::parseFloat( '0,00' ), '0' );
		$this->assertEquals( TTI18n::parseFloat( (float)12.123 ), '12.123' ); //If its input as an actual float value, it shouldn't be touched.


		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( TTI18n::parseFloat( '1.234,123' ), '1234.123' );
			$this->assertEquals( TTI18n::parseFloat( '1. 234,123' ), '1234.123' );
			$this->assertEquals( TTI18n::parseFloat( '12,91' ), '12.91' );
			$this->assertEquals( TTI18n::parseFloat( '0,00' ), '0' );
			$this->assertEquals( TTI18n::parseFloat( (float)12.123 ), '12.123' ); //If its input as an actual float value, it shouldn't be touched.

			$this->assertEquals( TTI18n::parseFloat( '12.9', 1 ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
			$this->assertEquals( TTI18n::parseFloat( '12.91', 2 ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
			$this->assertEquals( TTI18n::parseFloat( '12.912', 3 ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
			$this->assertEquals( TTI18n::parseFloat( '12.9123', 4 ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

			$this->assertEquals( TTI18n::parseFloat( '12,9', 1 ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
			$this->assertEquals( TTI18n::parseFloat( '12,91', 2 ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
			$this->assertEquals( TTI18n::parseFloat( '12,912', 3 ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
			$this->assertEquals( TTI18n::parseFloat( '12,9123', 4 ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

			$this->assertEquals( TTI18n::parseFloat( '12,9' ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
			$this->assertEquals( TTI18n::parseFloat( '12,91' ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
			$this->assertEquals( TTI18n::parseFloat( '12,912' ), '12.912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.
			$this->assertEquals( TTI18n::parseFloat( '12,9123' ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123 -- However since there is only one separator and it matches the decimal separator in the locale we can be certain.

			$this->assertEquals( TTI18n::parseFloat( '12.9' ), '12.9' ); //Ambiguous as it could be assumed to be 12.9, or 129
			$this->assertEquals( TTI18n::parseFloat( '12.91' ), '12.91' ); //Ambiguous as it could be assumed to be 12.91, or 12, 91
			$this->assertEquals( TTI18n::parseFloat( '12.912' ), '12912' ); //Ambiguous as it could be assumed to be 12.912, or 12, 912
			$this->assertEquals( TTI18n::parseFloat( '12.9123' ), '12.9123' ); //Ambiguous as it could be assumed to be 12.9123, or 12, 9123

			//Make sure parseFloat() can handle output from formatNumber()
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.1234' ) ), '54333.12' ); //Auto formatting decimals.
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.12' ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.123', true, 3, 3 ), 3 ), '54333.123' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatNumber( '54333.1234', true, 4, 4 ), 4 ), '54333.1234' );

			//Make sure parseFloat() can handle output from formatCurrency()
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234' ) ), '54333.12' ); //Auto formatting decimals.
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.12' ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.123' ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234' ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234', 'EUR', true ) ), '54333.12' ); //Auto formatting decimals.
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.12', 'EUR', true ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.123', 'EUR', true ) ), '54333.12' );
			$this->assertEquals( TTI18n::parseFloat( TTi18n::formatCurrency( '54333.1234', 'EUR', true ) ), '54333.12' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}
	}

	function testUserWage() {
		//
		//Test end-to-end setting of user wage and viewing it in a report.
		//
		TTDate::setTimeZone( 'PST8PDT', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$company_id = $dd->createCompany();
		$legal_entity_id = $dd->createLegalEntity( $company_id, 10 );
		Debug::text( 'Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertGreaterThan( 0, $company_id );

		//Permissions are required so the user has permissions to run reports.
		$dd->createPermissionGroups( $company_id, 40 ); //Administrator only.

		$dd->createCurrency( $company_id, 10 );
		$dd->createUserWageGroups( $company_id );

		$user_id = $dd->createUser( $company_id, $legal_entity_id, 100 );
		$user_idb = $dd->createUser( $company_id, $legal_entity_id, 10 );

		//Get User Object.
		$ulf = new UserListFactory();
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		$this->assertGreaterThan( 0, $user_id );

		TTi18n::setLocale( 'en_US' );
		$uw = new UserWageFactory();
		$data = [
				'user_id'              => $user_id,
				'type_id'              => 20, //Salary Annual
				'wage'                 => '54, 333.12',
				'weekly_time'          => ( 40 * 3600 ),
				'hourly_rate'          => '1, 123.98',
				'labor_burden_percent' => '12.98%',
				'effective_date'       => strtotime( '01-Jan-2019' ),
		];
		$uw->setObjectFromArray( $data );
		$insert_id = $uw->Save( false );

		$uwlf = new UserWageListFactory();
		$uwlf->getById( $insert_id );
		$retarr = $uwlf->getCurrent()->getObjectAsArray();
		//var_dump($retarr);

		$this->assertEquals( $retarr['wage'], '54333.12' );
		$this->assertEquals( $retarr['hourly_rate'], '1123.98' );
		$this->assertEquals( $retarr['labor_burden_percent'], '12.98' );

		TTi18n::setLocale( 'fr_CA' );
		$uw = new UserWageFactory();
		$data = [
				'user_id'              => $user_idb,
				'type_id'              => 20, //Salary Annual
				'wage'                 => '54. 334,12',
				'weekly_time'          => ( 40 * 3600 ),
				'hourly_rate'          => '1. 124,98',
				'labor_burden_percent' => '13,98%',
				'effective_date'       => strtotime( '01-Jan-2019' ),
		];
		$uw->setObjectFromArray( $data );
		$insert_id = $uw->Save( false );

		$uwlf = new UserWageListFactory();
		$uwlf->getById( $insert_id );
		$retarr = $uwlf->getCurrent()->getObjectAsArray();
		//var_dump($retarr);

		$this->assertEquals( $retarr['wage'], '54334.12' );
		$this->assertEquals( $retarr['hourly_rate'], '1124.98' );
		$this->assertEquals( $retarr['labor_burden_percent'], '13.98' );


		//Generate Report in en_US
		TTi18n::setLocale( 'en_US' );
		$report_obj = new UserSummaryReport();
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_employee+wage' ) );
		$report_config['columns'][] = 'labor_burden_percent';
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['wage'], 54333.12 );
		$this->assertEquals( $report_output[0]['hourly_rate'], 1123.98 );
		$this->assertEquals( $report_output[0]['labor_burden_percent'], 12.98 );

		$this->assertEquals( $report_output[1]['wage'], 54334.12 );
		$this->assertEquals( $report_output[1]['hourly_rate'], 1124.98 );
		$this->assertEquals( $report_output[1]['labor_burden_percent'], 13.98 );

		//Generate Report in fr_CA
		TTi18n::setLocale( 'fr_CA' );
		$report_obj = new UserSummaryReport();
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_employee+wage' ) );
		$report_config['columns'][] = 'labor_burden_percent';
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['wage'], 54333.12 );
		$this->assertEquals( $report_output[0]['hourly_rate'], 1123.98 );
		$this->assertEquals( $report_output[0]['labor_burden_percent'], 12.98 );

		$this->assertEquals( $report_output[1]['wage'], 54334.12 );
		$this->assertEquals( $report_output[1]['hourly_rate'], 1124.98 );
		$this->assertEquals( $report_output[1]['labor_burden_percent'], 13.98 );
	}

	function testRemoveTrailingZeros() {
		TTi18n::setLocale( 'en_US' );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 0 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.450000, 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( '123.450000', 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( 'test', 2 ), 'test' ); //Make sure if it can't work with the input value, we just output it untouched.

		TTi18n::setLocale( 'fr_CA' );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 0 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( (float)123.450000, 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( '123.450000', 2 ), (float)123.45 );
		$this->assertEquals( Misc::removeTrailingZeros( 'test', 2 ), 'test' ); //Make sure if it can't work with the input value, we just output it untouched.

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 2 ), (float)123.45 );
			$this->assertEquals( Misc::removeTrailingZeros( (float)123.45, 0 ), (float)123.45 );
			$this->assertEquals( Misc::removeTrailingZeros( (float)123.450000, 2 ), (float)123.45 );
			$this->assertEquals( Misc::removeTrailingZeros( '123.450000', 2 ), (float)123.45 );
			$this->assertEquals( Misc::removeTrailingZeros( 'test', 2 ), 'test' ); //Make sure if it can't work with the input value, we just output it untouched.
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}
	}

	function testSetLocale() {
		$this->assertEquals( TTi18n::setLocale( 'it_CH' ), true );
		$this->assertEquals( TTi18n::getLocale(), 'it_CH.UTF-8' );
		$this->assertEquals( TTi18n::getCurrencySymbol( 'EUR' ), '€' );
		$this->assertEquals( TTi18n::setLocale( 'en_CA' ), true );
		$this->assertEquals( TTi18n::getLocale(), 'en_CA.UTF-8' );
		$this->assertEquals( TTi18n::getCurrencySymbol( 'CAD' ), '$' );
	}

	function testTranslations() {
		TTi18n::setLocale( 'es_ES' );
		$this->assertEquals( TTi18n::getText( 'Employee' ), 'Empleado' );
		TTi18n::setLocale( 'en_CA' );
		$this->assertEquals( TTi18n::getText( 'Employee' ), 'Employee' );
		TTi18n::setLocale( 'yi_US' );
		$this->assertEquals( TTi18n::getText( 'Employee' ), 'Z' );
		TTi18n::setLocale( 'fr_CA' );
		//$this->assertEquals( TTi18n::getText( 'Saved Reports' ), 'Rapports sauvs' );
		$this->assertEquals( TTi18n::getText( 'Saved Reports' ), 'Rapports enregistrés' );
		TTi18n::setLocale( 'fr_FR' );
		$this->assertEquals( TTi18n::getText( 'Saved Reports' ), 'Enregistrer le rapport' );

		TTi18n::setLocale( 'ar_EG' );
		$this->assertEquals( TTi18n::getText( 'Saved Reports' ), 'Saved Reports' ); //valid locale with no translations returns the original string.
		TTi18n::setLocale( 'zz_ZZ' );
		$this->assertEquals( TTi18n::getText( 'Saved Reports' ), 'Saved Reports' ); //invalid locale returns the original string.

		$this->assertEquals( TTi18n::detectUTF8( 'The quick brown fox jumped over the lazy dog 1234567890!@#$%^&*()_+' ), false );
		$this->assertEquals( TTi18n::detectUTF8( 'ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ' ), true );
	}

	function testStringFunctions() {
		TTi18n::setLanguage( 'en' );
		$this->assertEquals( TTi18n::strtolower( 'TesT' ), 'test' );

		TTi18n::setLanguage( 'fr' );
		$this->assertEquals( TTi18n::strtolower( 'Cumulé' ), 'cumulé' );

		TTi18n::setLanguage( 'cn' );
		$this->assertEquals( TTi18n::strtolower( '壹' ), '壹' );
	}

	//30,000 currrency formats on existing code took less than a second.
	//30,000 currrency formats on new code took 1.24 seconds
	//500,000 currrency formats on new code took 8.2 seconds
	//500,000 currrency formats on old code took 9.95 seconds
//	function testNumberFormatBenchmark() {
//
//		TTi18n::setLocale('en_CA');
//		$start_benchmark = time();
//		for($i =0; $i<=500000; $i++){
//			$n = TTi18n::formatCurrency($i.'.55555', 'CAD', true);
//		}
//		$end_benchmark = time();
//		echo "time: " . ($end_benchmark - $start_benchmark);
//	}

	function testBCMath() {
		TTi18n::setLocale( 'en_US' );
		$amount1 = 510.9;
		$amount2 = 90.9;
		$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 601.80 );

		$amount1 = '510.9';
		$amount2 = '90.9';
		$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 601.80 );

		//If we switch setLocale() back to setting LC_NUMERIC, need to make sure bcmath handles comma decimal separators correctly like in UserDateTotalFactory->calcTotalAmount()
		TTi18n::setLocale( 'es_ES' );
		$amount1 = 510.9;
		$amount2 = 90.9;
		$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 601.80 ); //BCMath fails handling floating point values with comma separator.

		$amount1 = '510.9';
		$amount2 = '90.9';
		$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 601.80 ); //BCMath fails handling floating point values with comma separator.

		$amount1 = '510,9';
		$amount2 = '90,9';
		@$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 0.00 ); //BCMath fails handling floating point values with comma separator.


		//
		//Test to show that bcmath() breaks when using LC_NUMERIC locales.
		//
		TTi18n::setLocale( 'es_ES' );
		$valid_locale = setlocale( LC_ALL, TTi18n::generateLocale( 'es_ES' ) ); //Could return 'es_ES' or 'es_ES.utf8' or 'es_ES.UTF-8'
		$normalized_locale = TTi18n::stripUTF8( $valid_locale );
		$this->assertEquals( $normalized_locale, 'es_ES' );

		$amount1 = 510.9;
		$amount2 = 90.9;
		@$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 0.00 ); //BCMath fails handling floating point values with comma separator.

		$amount1 = '510,9';
		$amount2 = '90,9';
		@$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 0.00 ); //BCMath fails handling floating point values with comma separator.

		$amount1 = '123456710,9';
		$amount2 = '90,9';
		@$this->assertEquals( bcadd( $amount1, $amount2, 2 ), 0.00 ); //BCMath fails handling floating point values with comma separator.

		//Set locale back to the default so it doesn't affect other tests.
		setlocale( LC_ALL, TTi18n::generateLocale( 'en_US' ) );
		TTi18n::setLocale( 'en_US' );
	}
}