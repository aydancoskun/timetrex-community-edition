<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
 * --> NOTE TO Ubuntu/Debian users! <--
 *
 * In some cases you may have to generate the locale that you wish to use with the following command:
 * sudo locale-gen <locale name>
 *
 * ie:
 *
 * sudo locale-gen es_ES
 *
 */

/**
 * @package Core
 */
class TTi18n {
	static private $language = 'en';
	static private $country = 'US';

	static private $master_locale = NULL;
	static private $locale = NULL;
	static private $normalized_locale = NULL;
	static private $is_default_locale = TRUE;

	static private $currency_formatter = FALSE;
	static private $number_formatter = FALSE;

	//default precision is 2 to stay consistent with legacy code.
	static private $DEFAULT_NUMBER_FORMAT_PATTERN = '#,##0.##';

	static function setGetTextLocale( $locale ) {
		// Beware: this is changing the locale process-wide.
		// But *only* for LC_MESSAGES, not other LC_*.
		// This is not thread-safe.	 For threaded web servers,
		// the slower Translation2 classes should be used.

		//Setting the locale again here overrides what i18Nv2 just set
		//breaking Windows. However not setting it breaks some Linux distro's.
		//Because apparently LC_ALL doesn't matter on some Unix, it still doesn't set LC_MESSAGES.
		//So if we didn't explicity set LC_MESSAGES above, do it here.
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$rc = setlocale( LC_MESSAGES, $locale.'.UTF-8' );

			/* This often reports failure even if it works.
			if ( $rc == 0 ) {
				Debug::text('setLocale failed!: '. (int)$rc .' Locale: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
			}
			*/
		}

		// Normally, setting env var(s) would not be necessary, but I18Nv2
		// is explicitly setting LANG* env variables, which seem to be
		// overriding setlocale(). Setting the env var here, fixes it.
		// Yes, I know it seems backwards.	YMMV.
		@putEnv('LANGUAGE=' . $locale);

		$domain = 'messages';

		//This fixes the mysterious issue of the "sticky locale". Where PHP
		//wouldn't change locales half way through a script.
		textdomain( $domain );

		// Tell gettext where to find the locale translation files.
		bindtextdomain( $domain, Environment::getBasePath() . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'locale');

		// Tell gettext which codeset to use for output.
		bind_textdomain_codeset( $domain, 'UTF-8');

		return TRUE;
	}


	/*

		Locale setting functions

	*/
	static public function getLanguage() {
		return self::$language;
	}
	static public function setLanguage( $language ) {
		if ( $language == '' OR strlen( $language ) > 7 ) {
			$language = 'en';
		}

		self::$language = $language;

		return TRUE;
	}

	static public function getCountry() {
		return self::$country;
	}
	static public function setCountry( $country ) {
		if ( $country == '' OR strlen( $country ) > 7 ) {
			$country = 'US';
		}

		self::$country = $country;

		return TRUE;
	}

	static public function getLocaleArrayAsString( $locale_arr ) {
		if ( !is_array($locale_arr) ) {
			$locale_arr = (array)$locale_arr;
		}


		return implode(',', $locale_arr);
	}

	static public function tryLocale( $locale ) {
		if ( !is_array($locale) ) {
			$locale = (array)$locale;
		}

		//Don't call setLocale() with LC_ALL here, only LC_MESSAGES or some other LC_* type that is *NOT* numeric.
		//  If we change the numeric locale to say es_ES, then PHP converts (float)1.234 to '1,1234' as a string which causes a SQL syntax error when inserting into SQL.
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$valid_locale = setlocale( ( LC_MESSAGES | LC_COLLATE | LC_CTYPE ), $locale );
		} else {
			$valid_locale = setlocale( ( LC_COLLATE | LC_CTYPE ), $locale );
		}

		if ( $valid_locale != '' ) {
			//Check if the locale is the default locale, so we can more quickly determine if translation is needed or not.
			global $config_vars;
			if ( ( isset($config_vars['other']['enable_default_language_translation']) AND $config_vars['other']['enable_default_language_translation'] == TRUE )
					OR strpos( $valid_locale, 'en_US' ) === FALSE ) {
				self::$is_default_locale = FALSE;
			}
			Debug::Text('Found valid locale: '. $valid_locale .' Default: '. (int)self::$is_default_locale, __FILE__, __LINE__, __METHOD__, 11);

			return $valid_locale;
		}

		Debug::Text('FAILED TRYING LOCALE: '. self::getLocaleArrayAsString( $locale ), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	static public function generateLocale( $locale_arg = NULL ) {
		//Generate an array of possible locales to try in order.
		//1. <language>_<country>
		//2. Normalized locale.
		//3. Just Language
		//4a. If Linux then try with ".UTF8" appended to all of the above.
		//4b. If windows, let i18Nv2 normalize to windows locale names.

		//Debug::Text('Locale Argument: '. $locale_arg, __FILE__, __LINE__, __METHOD__, 11);
		if ( $locale_arg != '' AND strlen( $locale_arg ) <= 7 ) {
			//If locale argument is passed, normalize it and just attempt to use it.
			$locale_arr[] = $locale_arg;
			$locale_arr[] = self::_normalizeLocale( $locale_arg );
		} elseif ( self::getLanguage() != '' AND self::getCountry() != '' ) {
			//If no locale argument is passed, try to generate the locale based on just a Language/Country pair.
			//  Otherwise Language/Country could be from the previous locale (if it was changed multiple times like in a maintenance job) and $locale_arg could be from the current locale.
			//  If that happens, and locale_arg is not installed on the OS, it will revert back to the previous locale and
			//  therefore seem like the locales are incorrect or not changing. This has caused some unit tests to fail in the past where the locale changes multiple times without ever calling setLanguage/setCountry
			//  $locale_arg should never be set if Language/Country are though.
			Debug::Text('Lanuage: '. self::getLanguage() .' Country: '. self::getCountry(), __FILE__, __LINE__, __METHOD__, 11);
			$locale_arr[] = $locale = self::getLanguage() . '_' . self::getCountry();
			$locale_arr[] = self::_normalizeLocale( $locale );
			$locale_arr[] = self::getLanguage();
		}

		//Finally add a fallback locale that should always work, in theory.
		$locale_arr[] = 'en_US';

		$locale_arr = array_unique($locale_arr);

		if ( OPERATING_SYSTEM == 'LINUX' ) {
			//Duplicate each locale with .UTF8 appended to it to try, as some distro's like Ubuntu require this.
			$retarr = array();
			foreach( $locale_arr as $tmp_locale ) {
				$retarr[] = $tmp_locale;
				$retarr[] = $tmp_locale.'.UTF-8';
				$retarr[] = $tmp_locale.'.utf8';
			}
		} else {
			//Normalize each locale to Windows. Switch en_US to en-US instead. See: https://msdn.microsoft.com/en-us/library/39cwe7zf(v=vs.140).aspx
			$retarr = str_replace( '_', '-', $locale_arr );
		}

		Debug::Text('Array of Locales to try in order for "'. $locale_arg .'": '. self::getLocaleArrayAsString( $retarr ), __FILE__, __LINE__, __METHOD__, 11);

		return $retarr;
	}

	static public function setMasterLocale() {
		if ( self::$master_locale != '' ) {
			return self::setLocale( self::$master_locale );
		}

		return FALSE;
	}

	static public function stripUTF8($str) {
		//Only strip UTF8 if it actually exists, based on the ".".
		$position_of_dot = strpos($str, '.');
		if ( $position_of_dot !== FALSE ) {
			return substr( $str, 0, $position_of_dot );
		}

		return $str;
	}

	static public function setLocaleCookie( $locale = NULL ) {
		if ( $locale == '' ) {
			//Ensure locale never has ".UTF-8" on the end.
			//There are 2 strlen calls that only accept locales with less than 7 chars.
			$locale = self::stripUTF8( self::getLocale() );
		}

		if ( self::getLocaleCookie() != $locale ) {
			Debug::Text('Setting Locale cookie: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
			setcookie( 'language', $locale, ( time() + 9999999 ), Environment::getCookieBaseURL() );
		}

		return TRUE;
	}
	static public function getLocaleCookie() {
		if ( isset($_COOKIE['language']) AND strlen( $_COOKIE['language'] ) <= 7 ) { //Prevent user supplied locale from attempting XSS/SQL injection.
			return $_COOKIE['language'];
		}

		return FALSE;
	}

	static public function getLanguageFromLocale( $locale = NULL ) {
		if ( $locale == '' ) {
			$locale = self::getLocale();
		}

		Debug::Text('Locale: '. $locale, __FILE__, __LINE__, __METHOD__, 11);

		$language = substr( $locale, 0, 2);
		$language_arr = self::getLanguageArray();

		if ( isset( $language_arr[$language] ) ) {
			return $language;
		}

		return FALSE;
	}

	static function getCountryFromLocale( $locale = NULL ) {
		if ( $locale == '' ) {
			$locale = self::getLocale();
		}

		$split_locale = explode('_', $locale);
		if ( isset($split_locale[2]) ) {
			return $split_locale[2];
		}

		return FALSE;
	}

	static public function getNormalizedLocale() {
		return self::$normalized_locale;
	}

	static public function getLocale() {
		return self::$locale;
	}
	static public function setLocale( $locale_arg = NULL, $category = LC_ALL, $force = FALSE ) {
		Debug::Text('Generated/Passed In Locale: '. $locale_arg, __FILE__, __LINE__, __METHOD__, 11);
		$locale = self::tryLocale( self::generateLocale( $locale_arg ) );

		Debug::Text('Attempting to set Locale(s) to: '. $locale .' Category: '. $category .' Current Locale: '. self::getLocale(), __FILE__, __LINE__, __METHOD__, 11);

		//In order to validate Windows locales with tryLocale() we have to always force the locale to be set, otherwise
		//if tryLocale() doesn't get it right on the first try, the locale is reverted to something that may not work.
		if ( $force == TRUE OR $locale != self::getLocale() ) {
			if ( in_array( $category, array( LC_ALL, LC_MONETARY, LC_NUMERIC ) ) ) {
				Debug::Text('Setting currency/numeric Locale to: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
				//if ( self::getLocaleHandler()->setLocale( $locale, $category ) != $locale ) {
				//Setting the locale in Windows can cause the locale names to not match at all, so check for FALSE
//				if ( self::getLocaleHandler()->setLocale( $locale, $category ) == FALSE ) {
//					Debug::Text('Failed setting currency/numeric locale: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
//				}
			}

			if ( in_array( $category, array( LC_ALL, LC_MESSAGES ) ) ) {
				// We normalize locales to a single "standard" locale for each lang
				// to avoid having to maintain lots of mostly duplicate translation files
				// for each lang/country combination.
				$normal_locale = self::_normalizeLocale( $locale );
				Debug::Text('Setting translator to normalized locale: '. $normal_locale, __FILE__, __LINE__, __METHOD__, 11);
				if ( self::setGetTextLocale( $normal_locale ) === FALSE ) {
					//Fall back on non-normalized locale
					Debug::Text('Failed setting translator normalized locale: '. $normal_locale .' Falling back to: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
					if ( self::setGetTextLocale( $locale ) === FALSE ) {
						Debug::Text('Failed setting translator locale: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
						return FALSE;
					} else {
						self::$normalized_locale = $locale;
					}
				} else {
					self::$normalized_locale = $normal_locale;
				}
			}

			self::$locale = $locale;
			self::$language = substr($locale, 0, 2 );  //save language here to avoid becoming out of sync.

			//INTL extension is optional as of v10.0, so check if its loaded before instantiating the class. If its not we simply fall back to native functions elsewhere.
			if ( class_exists('NumberFormatter') ) {
				self::$currency_formatter = new NumberFormatter( self::$locale, NumberFormatter::CURRENCY );

				//default precision is 6 because it's a little more than the max used in high-precision currency (4).
				self::$number_formatter = new NumberFormatter( self::$locale, NumberFormatter::PATTERN_DECIMAL, self::$DEFAULT_NUMBER_FORMAT_PATTERN ); //default format shows up to 6 decimal places
			}

			if ( $category == LC_ALL ) {
				if ( self::$master_locale == NULL ) {
					self::$master_locale = $locale;
				}
			}

			Debug::Text('Set Master Locale To: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/**
	 *
	 * @param string|array $user_locale_pref
	 * @return string|boolean
	 * @author Dan Libby <dan@osc.co.cr>
	 */
	static public function getBrowserLanguage() {
		$list = array();

		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			Debug::text('HTTP_ACCEPT_LANGUAGE: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'], __FILE__, __LINE__, __METHOD__, 10);

			$accept = str_replace( ',', ';', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			$accept = str_replace( '-', '_', $accept );
			$locales = explode( ';', $accept );

			foreach( $locales as $l ) {
				if ( substr( $l, 0, 2 ) != "q=" ) {
					$list[] = $l;
				}
			}
		}

		return $list;
	}

	/**
	 * Determines the most appropriate locale, based on user metadata including
	 * the user's saved locale preference (if any), the user's browser lang pref,
	 * and the application's default locale. It also allows an override via
	 * setting URL param 'ttlang' to a valid locale.
	 *
	 * Returns the best locale, or false if unable to find and set a locale.
	 *
	 * @param string|array $user_locale_pref
	 * @return string|boolean
	 * @author Dan Libby <dan@osc.co.cr>
	 */
	static public function chooseBestLocale( $user_locale_pref = NULL ) {
		Debug::text('Choosing Best Locale...', __FILE__, __LINE__, __METHOD__, 11);

		$success = FALSE;
		$category = LC_ALL; //LC_MESSAGES isn't defined on Windows.

		// First, we'll check if 'ttlang' url param (override) is specified.
		//Check cookie first, as we want GET/POST to override the cookie, incase of form errors on Login page etc...
		if ( TTi18n::getLocaleCookie() != FALSE ) {
			Debug::text('Using Language from cookie: ' . TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 11);
			$success = TTi18n::setLocale( TTi18n::getLocaleCookie(), $category );
		}

		if ( isset( $_GET['language'] ) AND $_GET['language'] != '' ) {
			Debug::text('Using Language from _GET: ' . $_GET['language'], __FILE__, __LINE__, __METHOD__, 11);
			$success = self::setLocale( $_GET['language'] );
		}

		if ( isset( $_POST['language'] ) AND $_POST['language'] != '' ) {
			Debug::text('Using Language from _POST: ' . $_POST['language'], __FILE__, __LINE__, __METHOD__, 11);
			$success = self::setLocale( $_POST['language'] );
		}

		if ( $success == FALSE ) {
			// Check for a user pref first.
			if ( $user_locale_pref != '' ) {
				// Could be an array of preferred locales.
				if ( is_array( $user_locale_pref ) ) {
					foreach( $user_locale_pref as $locale ) {
						Debug::text('aSetting Locale: ' . $user_locale_pref, __FILE__, __LINE__, __METHOD__, 11);
						if ( $success == self::setLocale( $locale, $category ) ) {
							break;
						}
					}
				} else {
					Debug::text('bSetting Locale: ' . $user_locale_pref, __FILE__, __LINE__, __METHOD__, 11);
					// or a single locale
					$success = self::setLocale( $user_locale_pref, $category );
				}
			}
		}

		// Otherwise, check for lang prefs from the browser
		if ( $success == FALSE ) {
			// browser can specify more than one, so we get an array.
			$browser_lang_prefs = self::getBrowserLanguage();
			foreach( $browser_lang_prefs as $locale ) {
				//The country code needs to be upper case for locales to work correctly.
				if ( strpos($locale, '_') !== FALSE ) {
					$split_locale = explode('_', $locale);
					if ( isset($split_locale[1]) ) {
						$locale = $split_locale[0].'_'.strtoupper($split_locale[1]);
					}
				}

				Debug::text('cSetting Locale: ' . $locale, __FILE__, __LINE__, __METHOD__, 11);
				if ( $success == self::setLocale( $locale, $category ) ) {
					break;
				}
			}
		}

		if ( $success == FALSE ) {
			global $config_vars;

			//Use system locale if its set from timetrex.ini.php
			if ( isset($config_vars['other']['system_locale']) AND $config_vars['other']['system_locale'] != '' ) {
				Debug::text('Using system locale from .ini: ' . $config_vars['other']['system_locale'], __FILE__, __LINE__, __METHOD__, 11);
				$success = self::setLocale( $config_vars['other']['system_locale'], $category );
			}
		}

		// If it worked, then we save this for future reference.
		if ( $success !== FALSE ) {
			Debug::text('Using Locale: ' . self::getLocale(), __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::text('Unable to find and set a locale.', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/*

		Language Functions

	*/
	static public function getLanguageArray() {

		$this_language = self::getLanguage();
		// Return supported languages
		$supported_langs = array( 'en', 'da', 'de', 'es', 'id', 'it', 'fr', 'pt', 'ar', 'zh');
		$beta_langs = array( 'da', 'de', 'es', 'id', 'it', 'fr', 'pt', 'ar', 'zh' );

		if ( PRODUCTION == FALSE ) {
			//YI is for testing only.
			$supported_langs[] = 'yi';
			$beta_langs[] = 'yi';
		}

		$retarr = array();
		if ( class_exists('Locale') ) {
			foreach( $supported_langs as $language ) {
				$language_label = mb_convert_case( Locale::getDisplayLanguage( $language, $language ), MB_CASE_TITLE, 'UTF-8' );
				if ( in_array( $language, $beta_langs ) ) {
					$language_label = $language_label .' (UO)'; //UO = UnOfficial languages
				}
				$retarr[$language] = $language_label;
			}
		} else {
			//If INTL extension is not installed, only show English language. Do not translate this one.
			$retarr['en'] = 'English';
		}

		return $retarr;
	}

	static public function getTextStringArgs( $str, $args ) {
		if ( $args != '' ) {
			if ( !is_array($args) ) {
				$args = (array)$args;
			}

			$i = 1;
			foreach( $args as $arg ) {
				$tr['%'.$i] = $arg;

				$i++;
			}

			return strtr($str, $tr);
		}

		return $str;
	}

	static public function getText( $str, $args = FALSE ) {
		if ( $args != '' ) {
			$str = self::getTextStringArgs( $str, $args );
		}

		if ( self::$is_default_locale == TRUE ) { //Optimization: If default locale and config isn't set to enable default translation, just return the string immediately.
			return $str;
		}

		return gettext( $str );
	}

	static public function gt( $str, $args = FALSE ) {
		return self::getText( $str, $args );
	}

	/**
	* Returns a fully normalized locale string, or the original string
	* if no match was found.
	*
	* @param string $locale a locale string of the form 'es', or 'es_CR'. Both will be converted to 'es_ES'
	* @return string
	*/
	static protected function _normalizeLocale($locale) {

		static $language = array(
								'af' => 'af_ZA',	// Afrikaans	South Africa
								'am' => 'am_ET',	// Amharic	Ethiopia
								'ar' => 'ar_EG',	// Arabic Egypt
								'as' => 'as_IN',	// Assamese	India
								'az' => 'az_AZ',	// Azerbaijani	Azerbaijan
								'be' => 'be_BY',	// Belarusian	Belarus
								'bg' => 'bg_BG',	// Bulgarian	Bulgaria
								'bn' => 'bn_IN',	// Bengali	India
								'bo' => 'bo_CN',	// Tibetan	China
								'br' => 'br_FR',	// Breton	France
								'bs' => 'bs_BA',	// Bosnian	Bosnia
								'ca' => 'ca_ES',	// Catalan	Spain
								'ce' => 'ce_RU',	// Chechen	Russia
								'co' => 'co_FR',	// Corsican	France
								'cs' => 'cs_CZ',	// Czech	Czech Republic
								'cy' => 'cy_GB',	// Welsh	Britain
								'da' => 'da_DK',	// Danish	Denmark
								'de' => 'de_DE',	// German	Germany
								'dz' => 'dz_BT',	// Dzongkha	Bhutan
								'el' => 'el_GR',	// Greek	Greece
								'en' => 'en_US',	// English USA
								'es' => 'es_ES',	// Spanish	Spain
								'et' => 'et_EE',	// Estonian	Estonia
								'fa' => 'fa_IR',	// Persian	Iran
								'fi' => 'fi_FI',	// Finnish	Finland
								'fj' => 'fj_FJ',	// Fijian	Fiji
								'fo' => 'fo_FO',	// Faroese	Faeroe Islands
								'fr' => 'fr_FR',	// French	France
								'fr_CA' => 'fr_CA',		// French	Canada
								'ga' => 'ga_IE',	// Irish	Ireland
								'gd' => 'gd_GB',	// Scots	Britain
								'gu' => 'gu_IN',	// Gujarati	India
								'he' => 'he_IL',	// Hebrew	Israel
								'hi' => 'hi_IN',	// Hindi	India
								'hr' => 'hr_HR',	// Croatian	Croatia
								'hu' => 'hu_HU',	// Hungarian	Hungary
								'hy' => 'hy_AM',	// Armenian	Armenia
								'id' => 'id_ID',	// Indonesian	Indonesia
								'is' => 'is_IS',	// Icelandic	Iceland
								'it' => 'it_IT',	// Italian	Italy
								'ja' => 'ja_JP',	// Japanese	Japan
								'jv' => 'jv_ID',	// Javanese	Indonesia
								'ka' => 'ka_GE',	// Georgian	Georgia
								'kk' => 'kk_KZ',	// Kazakh	Kazakhstan
								'kl' => 'kl_GL',	// Kalaallisut	Greenland
								'km' => 'km_KH',	// Khmer	Cambodia
								'kn' => 'kn_IN',	// Kannada	India
								'ko' => 'ko_KR',	// Korean	Korea (South)
								'ko' => 'kok_IN',	// Konkani	India
								'lo' => 'lo_LA',	// Laotian	Laos
								'lt' => 'lt_LT',	// Lithuanian	Lithuania
								'lv' => 'lv_LV',	// Latvian	Latvia
								'mg' => 'mg_MG',	// Malagasy	Madagascar
								'mk' => 'mk_MK',	// Macedonian	Macedonia
								'ml' => 'ml_IN',	// Malayalam	India
								'mn' => 'mn_MN',	// Mongolian	Mongolia
								'mr' => 'mr_IN',	// Marathi	India
								'ms' => 'ms_MY',	// Malay	Malaysia
								'mt' => 'mt_MT',	// Maltese	Malta
								'my' => 'my_MM',	// Burmese	Myanmar
								'mn' => 'mni_IN',	// Manipuri	India
								'na' => 'na_NR',	// Nauru	Nauru
								'nb' => 'nb_NO',	// Norwegian Bokml	Norway
								'ne' => 'ne_NP',	// Nepali	Nepal
								'nl' => 'nl_NL',	// Dutch	Netherlands
								'nn' => 'nn_NO',	// Norwegian Nynorsk	Norway
								'no' => 'no_NO',	// Norwegian	Norway
								'oc' => 'oc_FR',	// Occitan	France
								'or' => 'or_IN',	// Oriya	India
								'pa' => 'pa_IN',	// Punjabi	India
								'pl' => 'pl_PL',	// Polish	Poland
								'ps' => 'ps_AF',	// Pashto	Afghanistan
								'pt' => 'pt_PT',	// Portuguese	Portugal
								'pt_BR' => 'pt_BR',		// Portuguese	Brazilian
								'rm' => 'rm_CH',	// Rhaeto-Roman	Switzerland
								'rn' => 'rn_BI',	// Kirundi	Burundi
								'ro' => 'ro_RO',	// Romanian	Romania
								'ru' => 'ru_RU',	// Russian	Russia
								'sa' => 'sa_IN',	// Sanskrit	India
								'sc' => 'sc_IT',	// Sardinian	Italy
								'sg' => 'sg_CF',	// Sango	Central African Rep.
								'si' => 'si_LK',	// Sinhalese	Sri Lanka
								'sk' => 'sk_SK',	// Slovak	Slovakia
								'sl' => 'sl_SI',	// Slovenian	Slovenia
								'so' => 'so_SO',	// Somali	Somalia
								'sq' => 'sq_AL',	// Albanian	Albania
								'sr' => 'sr_YU',	// Serbian	Yugoslavia
								'sv' => 'sv_SE',	// Swedish	Sweden
								'te' => 'te_IN',	// Telugu	India
								'tg' => 'tg_TJ',	// Tajik	Tajikistan
								'th' => 'th_TH',	// Thai		Thailand
								'tk' => 'tk_TM',	// Turkmen	Turkmenistan
								'tl' => 'tl_PH',	// Tagalog	Philippines
								'to' => 'to_TO',	// Tonga	Tonga
								'tr' => 'tr_TR',	// Turkish	Turkey
								'uk' => 'uk_UA',	// Ukrainian	Ukraine
								'ur' => 'ur_PK',	// Urdu		Pakistan
								'uz' => 'uz_UZ',	// Uzbek	Uzbekistan
								'vi' => 'vi_VN',	// Vietnamese	Vietnam
								'wa' => 'wa_BE',	// Walloon	Belgium
								'we' => 'wen_DE',	// Sorbian	Germany
								'zh' => 'zh_CN',	// Chinese Simplified

								//Test locale to make sure all strings are translated.
								'yi' => 'yi_US',
								);

		$locale = self::stripUTF8($locale); //Make sure .utf8 is not passed as part of the locale.

		//Using .UTF-8 fails using Translation2 or Windows. Perhaps we attempt to add this later if it fails on the first try.
		if ( isset($language[$locale]) ) { //Check for full language first for cases where the same language has different translations for different countries (ie: France/Canada)
			return $language[$locale];
		} else {
			$lang = trim( substr( $locale, 0, 2 ) );
			if ( isset($language[$lang]) ) {
				return $language[$lang];// . '.UTF-8';	// setlocale fails on ubuntu if UTF-8 not specified
			}
		}

		return $locale;
	}

	//Returns PDF font appropriate for language.
	static function getPDFDefaultFont( $language = NULL, $encoding = FALSE ) {
		if ( $language == '' ) {
			$language = self::getLanguage();
		}

		//Helvetica is a PDF core font that should always work.
		//But does it not support many unicode characters?
		if ( $language == 'en' AND ( $encoding == '' OR $encoding == FALSE OR $encoding == 'ISO-8859-1' ) ) {
			return 'helvetica'; //Core PDF font, works with setFontSubsetting(TRUE) and is fast with small PDF sizes.
		}

		Debug::text('Using international font: freeserif', __FILE__, __LINE__, __METHOD__, 10);
		return 'freeserif'; //Slow with setFontSubsetting(TRUE), produces PDFs at least 1mb.
	}

	/*

	  String functions

	 */
	static public function strtolower( $str ) {
		//Can't optimize this unfortunately, as users using 'en' may want to update records or names in 'fr' and such.
		//if ( self::getLanguage() == 'en' ) {
		//	$str = strtolower( $str );
		//} else {
			$str = mb_strtolower( $str, 'UTF-8' ); //Force UTF8 encoding always.
		//}

		return $str;
	}

	static public function strtoupper( $str ) {
		//Can't optimize this unfortunately, as users using 'en' may want to update records or names in 'fr' and such.
		//if ( self::getLanguage() == 'en' ) {
		//	$str = strtoupper( $str );
		//} else {
			$str = mb_strtoupper( $str, 'UTF-8' ); //Force UTF8 encoding always.
		//}

		return $str;
	}

	/*

		Number/Currency functions

	*/
	static public function getCurrencyArray() {

		$code_arr = array(
				'ADP' => 'Andorran Peseta',
				'AED' => 'United Arab Emirates Dirham',
				'AFA' => 'Afghani (1927-2002)',
				'AFN' => 'Afghani',
				'ALL' => 'Albanian Lek',
				'AMD' => 'Armenian Dram',
				'ANG' => 'Netherlands Antillan Guilder',
				'AOA' => 'Angolan Kwanza',
				'AOK' => 'Angolan Kwanza (1977-1990)',
				'AON' => 'Angolan New Kwanza (1990-2000)',
				'AOR' => 'Angolan Kwanza Reajustado (1995-1999)',
				'ARA' => 'Argentine Austral',
				'ARP' => 'Argentine Peso (1983-1985)',
				'ARS' => 'Argentine Peso',
				'ATS' => 'Austrian Schilling',
				'AUD' => 'Australian Dollar',
				'AWG' => 'Aruban Guilder',
				'AZM' => 'Azerbaijanian Manat',
				'BAD' => 'Bosnia-Herzegovina Dinar',
				'BAM' => 'Bosnia-Herzegovina Convertible Mark',
				'BBD' => 'Barbados Dollar',
				'BDT' => 'Bangladesh Taka',
				'BEC' => 'Belgian Franc (convertible)',
				'BEF' => 'Belgian Franc',
				'BEL' => 'Belgian Franc (financial)',
				'BGL' => 'Bulgarian Hard Lev',
				'BGN' => 'Bulgarian New Lev',
				'BHD' => 'Bahraini Dinar',
				'BIF' => 'Burundi Franc',
				'BMD' => 'Bermudan Dollar',
				'BND' => 'Brunei Dollar',
				'BOB' => 'Boliviano',
				'BOP' => 'Bolivian Peso',
				'BOV' => 'Bolivian Mvdol',
				'BRB' => 'Brazilian Cruzeiro Novo (1967-1986)',
				'BRC' => 'Brazilian Cruzado',
				'BRE' => 'Brazilian Cruzeiro (1990-1993)',
				'BRL' => 'Brazilian Real',
				'BRN' => 'Brazilian Cruzado Novo',
				'BRR' => 'Brazilian Cruzeiro',
				'BSD' => 'Bahamian Dollar',
				'BTN' => 'Bhutan Ngultrum',
				'BUK' => 'Burmese Kyat',
				'BWP' => 'Botswanan Pula',
				'BYB' => 'Belarussian New Ruble (1994-1999)',
				'BYR' => 'Belarussian Ruble',
				'BZD' => 'Belize Dollar',
				'CAD' => 'Canadian Dollar',
				'CDF' => 'Congolese Franc Congolais',
				'CHE' => 'WIR Euro',
				'CHF' => 'Swiss Franc',
				'CHW' => 'WIR Franc',
				'CLF' => 'Chilean Unidades de Fomento',
				'CLP' => 'Chilean Peso',
				'CNY' => 'Chinese Yuan Renminbi',
				'COP' => 'Colombian Peso',
				'COU' => 'Unidad de Valor Real',
				'CRC' => 'Costa Rican Colon',
				'CSD' => 'Serbian Dinar',
				'CSK' => 'Czechoslovak Hard Koruna',
				'CUP' => 'Cuban Peso',
				'CVE' => 'Cape Verde Escudo',
				'CYP' => 'Cyprus Pound',
				'CZK' => 'Czech Republic Koruna',
				'DDM' => 'East German Ostmark',
				'DEM' => 'Deutsche Mark',
				'DJF' => 'Djibouti Franc',
				'DKK' => 'Danish Krone',
				'DOP' => 'Dominican Peso',
				'DZD' => 'Algerian Dinar',
				'ECS' => 'Ecuador Sucre',
				'ECV' => 'Ecuador Unidad de Valor Constante (UVC)',
				'EEK' => 'Estonian Kroon',
				'EGP' => 'Egyptian Pound',
				'EQE' => 'Ekwele',
				'ERN' => 'Eritrean Nakfa',
				'ESA' => 'Spanish Peseta (A account)',
				'ESB' => 'Spanish Peseta (convertible account)',
				'ESP' => 'Spanish Peseta',
				'ETB' => 'Ethiopian Birr',
				'EUR' => 'Euro',
				'FIM' => 'Finnish Markka',
				'FJD' => 'Fiji Dollar',
				'FKP' => 'Falkland Islands Pound',
				'FRF' => 'French Franc',
				'GBP' => 'British Pound Sterling',
				'GEK' => 'Georgian Kupon Larit',
				'GEL' => 'Georgian Lari',
				'GHC' => 'Ghana Cedi',
				'GIP' => 'Gibraltar Pound',
				'GMD' => 'Gambia Dalasi',
				'GNF' => 'Guinea Franc',
				'GNS' => 'Guinea Syli',
				'GQE' => 'Equatorial Guinea Ekwele Guineana',
				'GRD' => 'Greek Drachma',
				'GTQ' => 'Guatemala Quetzal',
				'GWE' => 'Portuguese Guinea Escudo',
				'GWP' => 'Guinea-Bissau Peso',
				'GYD' => 'Guyana Dollar',
				'HKD' => 'Hong Kong Dollar',
				'HNL' => 'Hoduras Lempira',
				'HRD' => 'Croatian Dinar',
				'HRK' => 'Croatian Kuna',
				'HTG' => 'Haitian Gourde',
				'HUF' => 'Hungarian Forint',
				'IDR' => 'Indonesian Rupiah',
				'IEP' => 'Irish Pound',
				'ILP' => 'Israeli Pound',
				'ILS' => 'Israeli New Sheqel',
				'INR' => 'Indian Rupee',
				'IQD' => 'Iraqi Dinar',
				'IRR' => 'Iranian Rial',
				'ISK' => 'Icelandic Krona',
				'ITL' => 'Italian Lira',
				'JMD' => 'Jamaican Dollar',
				'JOD' => 'Jordanian Dinar',
				'JPY' => 'Japanese Yen',
				'KES' => 'Kenyan Shilling',
				'KGS' => 'Kyrgystan Som',
				'KHR' => 'Cambodian Riel',
				'KMF' => 'Comoro Franc',
				'KPW' => 'North Korean Won',
				'KRW' => 'South Korean Won',
				'KWD' => 'Kuwaiti Dinar',
				'KYD' => 'Cayman Islands Dollar',
				'KZT' => 'Kazakhstan Tenge',
				'LAK' => 'Laotian Kip',
				'LBP' => 'Lebanese Pound',
				'LKR' => 'Sri Lanka Rupee',
				'LRD' => 'Liberian Dollar',
				'LSL' => 'Lesotho Loti',
				'LSM' => 'Maloti',
				'LTL' => 'Lithuanian Lita',
				'LTT' => 'Lithuanian Talonas',
				'LUC' => 'Luxembourg Convertible Franc',
				'LUF' => 'Luxembourg Franc',
				'LUL' => 'Luxembourg Financial Franc',
				'LVL' => 'Latvian Lats',
				'LVR' => 'Latvian Ruble',
				'LYD' => 'Libyan Dinar',
				'MAD' => 'Moroccan Dirham',
				'MAF' => 'Moroccan Franc',
				'MDL' => 'Moldovan Leu',
				'MGA' => 'Madagascar Ariary',
				'MGF' => 'Madagascar Franc',
				'MKD' => 'Macedonian Denar',
				'MLF' => 'Mali Franc',
				'MMK' => 'Myanmar Kyat',
				'MNT' => 'Mongolian Tugrik',
				'MOP' => 'Macao Pataca',
				'MRO' => 'Mauritania Ouguiya',
				'MTL' => 'Maltese Lira',
				'MTP' => 'Maltese Pound',
				'MUR' => 'Mauritius Rupee',
				'MVR' => 'Maldive Islands Rufiyaa',
				'MWK' => 'Malawi Kwacha',
				'MXN' => 'Mexican Peso',
				'MXP' => 'Mexican Silver Peso (1861-1992)',
				'MXV' => 'Mexican Unidad de Inversion (UDI)',
				'MYR' => 'Malaysian Ringgit',
				'MZE' => 'Mozambique Escudo',
				'MZM' => 'Mozambique Metical',
				'NAD' => 'Namibia Dollar',
				'NGN' => 'Nigerian Naira',
				'NIC' => 'Nicaraguan Cordoba',
				'NIO' => 'Nicaraguan Cordoba Oro',
				'NLG' => 'Netherlands Guilder',
				'NOK' => 'Norwegian Krone',
				'NPR' => 'Nepalese Rupee',
				'NZD' => 'New Zealand Dollar',
				'OMR' => 'Oman Rial',
				'PAB' => 'Panamanian Balboa',
				'PEI' => 'Peruvian Inti',
				'PEN' => 'Peruvian Sol Nuevo',
				'PES' => 'Peruvian Sol',
				'PGK' => 'Papua New Guinea Kina',
				'PHP' => 'Philippine Peso',
				'PKR' => 'Pakistan Rupee',
				'PLN' => 'Polish Zloty',
				'PLZ' => 'Polish Zloty (1950-1995)',
				'PTE' => 'Portuguese Escudo',
				'PYG' => 'Paraguay Guarani',
				'QAR' => 'Qatari Rial',
				'RHD' => 'Rhodesian Dollar',
				'ROL' => 'Romanian Leu',
				'RON' => 'Romanian Leu',
				'RUB' => 'Russian Ruble',
				'RUR' => 'Russian Ruble (1991-1998)',
				'RWF' => 'Rwandan Franc',
				'SAR' => 'Saudi Riyal',
				'SBD' => 'Solomon Islands Dollar',
				'SCR' => 'Seychelles Rupee',
				'SDD' => 'Sudanese Dinar',
				'SDP' => 'Sudanese Pound',
				'SEK' => 'Swedish Krona',
				'SGD' => 'Singapore Dollar',
				'SHP' => 'Saint Helena Pound',
				'SIT' => 'Slovenia Tolar',
				'SKK' => 'Slovak Koruna',
				'SLL' => 'Sierra Leone Leone',
				'SOS' => 'Somali Shilling',
				'SRD' => 'Surinam Dollar',
				'SRG' => 'Suriname Guilder',
				'STD' => 'Sao Tome and Principe Dobra',
				'SUR' => 'Soviet Rouble',
				'SVC' => 'El Salvador Colon',
				'SYP' => 'Syrian Pound',
				'SZL' => 'Swaziland Lilangeni',
				'THB' => 'Thai Baht',
				'TJR' => 'Tajikistan Ruble',
				'TJS' => 'Tajikistan Somoni',
				'TMM' => 'Turkmenistan Manat',
				'TND' => 'Tunisian Dinar',
				'TOP' => 'Tonga Paʻanga',
				'TPE' => 'Timor Escudo',
				'TRL' => 'Turkish Lira',
				'TRY' => 'New Turkish Lira',
				'TTD' => 'Trinidad and Tobago Dollar',
				'TWD' => 'Taiwan New Dollar',
				'TZS' => 'Tanzanian Shilling',
				'UAH' => 'Ukrainian Hryvnia',
				'UAK' => 'Ukrainian Karbovanetz',
				'UGS' => 'Ugandan Shilling (1966-1987)',
				'UGX' => 'Ugandan Shilling',
				'USD' => 'US Dollar',
				'USN' => 'US Dollar (Next day)',
				'USS' => 'US Dollar (Same day)',
				'UYP' => 'Uruguay Peso (1975-1993)',
				'UYU' => 'Uruguay Peso Uruguayo',
				'UZS' => 'Uzbekistan Sum',
				'VEB' => 'Venezuelan Bolivar',
				'VND' => 'Vietnamese Dong',
				'VUV' => 'Vanuatu Vatu',
				'WST' => 'Western Samoa Tala',
				'XAF' => 'CFA Franc BEAC',
				'XAG' => 'Silver',
				'XAU' => 'Gold',
				'XBA' => 'European Composite Unit',
				'XBB' => 'European Monetary Unit',
				'XBC' => 'European Unit of Account (XBC)',
				'XBD' => 'European Unit of Account (XBD)',
				'XCD' => 'East Caribbean Dollar',
				'XDR' => 'Special Drawing Rights',
				'XEU' => 'European Currency Unit',
				'XFO' => 'French Gold Franc',
				'XFU' => 'French UIC-Franc',
				'XOF' => 'CFA Franc BCEAO',
				'XPD' => 'Palladium',
				'XPF' => 'CFP Franc',
				'XPT' => 'Platinum',
				'XRE' => 'RINET Funds',
				'XTS' => 'Testing Currency Code',
				'XXX' => 'No Currency',
				'YDD' => 'Yemeni Dinar',
				'YER' => 'Yemeni Rial',
				'YUD' => 'Yugoslavian Hard Dinar',
				'YUM' => 'Yugoslavian Noviy Dinar',
				'YUN' => 'Yugoslavian Convertible Dinar',
				'ZAL' => 'South African Rand (financial)',
				'ZAR' => 'South African Rand',
				'ZMK' => 'Zambian Kwacha',
				'ZMW' => 'Zambian Kwacha',
				'ZRN' => 'Zairean New Zaire',
				'ZRZ' => 'Zairean Zaire',
				'ZWD' => 'Zimbabwe Dollar'
		);

		foreach( $code_arr as $iso_code => $name ) {
			$retarr[$iso_code] = ''.$iso_code.' - '. $name;
		}

		//Add support for Bitcoin (XBT)
		$retarr['XBT'] = TTi18n::getText('XBT').' - '. TTi18n::getText('Bitcoin');


		return $retarr;
	}

	/**
	 * Parses a locale specific string that represents a float (ie: -46,1234 or -46.1234) and converts it to a float that PHP will recognize for functions like number_format()/round, etc...
	 * @return string
	 */
	public static function parseFloat( $value ) {
		if ( is_float( $value ) === TRUE OR is_int( $value ) === TRUE ) {
			if ( is_infinite( $value ) OR is_nan( $value ) ) { //Check for INF, -INF, +INF and NaN so we just return 0 instead.
				return 0;
			}

			return $value;
		} elseif ( is_string( $value ) ) {
			if ( $value == '' ) {
				return 0;
			}

			//We can't short circuit this check for locales where decimal symbol = '.', because we still need to handle commas for thousands separators, and casting to float won't do that.
			$retval = (string)str_replace( array(self::getThousandsSymbol(), self::getDecimalSymbol(), ' '), array('', '.', ''), trim( $value ) ); //Cant cast this to float as it will put back in the "," for a decimal in other locales. Always remove spaces.
			//Debug::Arr( $retval, 'Symbol: Thousands: ' . self::getThousandsSymbol() . ' Decimal: ' . self::getDecimalSymbol() . ' Value: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		//Return boolean TRUE/FALSE, or NULL as 0.
		return 0;
	}

	/**
	 * Get the current locale's decimal symbol
	 * @return string
	 */
	public static function getDecimalSymbol() {
		if ( is_object(self::$number_formatter) ) {
			$retval = self::$number_formatter->getSymbol( NumberFormatter::DECIMAL_SEPARATOR_SYMBOL );
		} else {
			$retval = '.';
		}

		//Due to PHPUnit and singleton handling, sometimes the number_formatter gets out of sync and returns a blank string here.
		//Always check for that and default to a decimal point instead.
		if ( $retval == '' ) {
			return '.';
		}

		return $retval;
	}

	/**
	 * Get the current locale's thousands separator symbol
	 * @return string
	 */
	public static function getThousandsSymbol() {
		if ( is_object(self::$number_formatter) ) {
			return self::$number_formatter->getSymbol( NumberFormatter::GROUPING_SEPARATOR_SYMBOL );
		} else {
			return ',';
		}
	}

	public static function getAfterDecimal( $number, $min_decimals = 0, $max_decimals = 16 ) {
		//$seperator = self::getDecimalSymbol();
		$seperator = '.'; //Since we call setLocale() *without* LC_NUMERIC now so casted (float) values always use decimal symbol, we should force it to decimal symbol always here.
		$split_number = explode( $seperator, $number );
		if ( isset( $split_number[1] ) ) {
			$decimal_places = strlen( $split_number[1] );
		} else {
			$decimal_places = 0;
		}

		if ( $decimal_places > $max_decimals ) {
			$decimal_places = $max_decimals;
		} elseif ( $decimal_places < $min_decimals ) {
			$decimal_places = $min_decimals;
		}

		return $decimal_places;
	}

	/**
	 * Format a number in the manner consistent with the current locale
	 * -In the first case, only the number is provided. this formats to the default pattern for the locale.
	 * -In the second case, $auto_format_decimals is provided as TRUE with both $min_decimals and $max_decimals set. this defines the number of decimals.
	 *
	 * @param decimal $number
	 * @param bool $auto_format_decimals
	 * @param int $min_decimals
	 * @param int $max_decimals
	 * @return string [formatted number]
	 */
	static public function formatNumber( $number, $auto_format_decimals = FALSE, $min_decimals = 2, $max_decimals = 4 ) {
		if ( is_object( self::$number_formatter ) ) { //Make sure INTL extension is installed before using it.
			if ( $auto_format_decimals == TRUE ) {
				$number = Misc::removeTrailingZeros( $number, $min_decimals );
				$decimal_places = self::getAfterDecimal( $number, $min_decimals, $max_decimals );

				//http://icu-project.org/apiref/icu4c/classDecimalFormatSymbols.html
				$pattern = '#,##0';
				if ( $decimal_places > 0 ) {
					$pattern .= '.' . str_repeat( '0', $decimal_places ); //If decimal places are 0, we can't put a "." in the pattern, otherwise it will show "0." or "134.".
				}
				self::$number_formatter->setPattern( $pattern );

				//Debug::Arr( $number, 'Symbol: Thousands: ' . self::getThousandsSymbol() . ' Decimal: ' . self::getDecimalSymbol() . ' Value: ' . $number .' Decimal Places: '. $decimal_places .' Pattern: '. $pattern, __FILE__, __LINE__, __METHOD__, 10 );
				return self::$number_formatter->format( $number );
			} else {
				self::$number_formatter->setPattern( self::$DEFAULT_NUMBER_FORMAT_PATTERN );

				return self::$number_formatter->format( $number );
			}
		} else {
			if ( $auto_format_decimals == TRUE ) {
				$number = Misc::removeTrailingZeros( $number, $min_decimals );
				$decimal_places = self::getAfterDecimal( $number, $min_decimals, $max_decimals );
				return number_format( $number, $decimal_places );
			} else {
				$number = Misc::removeTrailingZeros( $number, 0 );
				$decimal_places = self::getAfterDecimal( $number, 0, 2 ); //Force min to 0 and max to 2 without auto formatting.
				return number_format( $number, $decimal_places );
			}
		}

	}

	//
	// Show Code: 0 = No, 1 = Left, 2 = Right
	//
	static public function formatCurrency( $amount, $currency_code = NULL, $show_code = 0 ) {
		$currency_code_left_str = NULL;
		$currency_code_right_str = NULL;

		//Always need to make sure currency_code is set if an object is passed in, even if show_code == 0, as formatCurrency() below requires a code.
		if ( is_object( $currency_code ) ) {
			//CurrencyFactory Object, grab ISO code for this.
			$currency_code = $currency_code->getISOCode();
		}

		if ( !is_object($currency_code) AND $show_code != 0 AND $currency_code != '' ) {
			if ( $show_code == 1 ) {
				$currency_code_left_str = $currency_code .' ';
			} elseif ( $show_code == 2 ) {
				$currency_code_right_str = ' '. $currency_code;
			}
		}

		if ( is_object( self::$currency_formatter ) ) { //Make sure INTL extension is installed before using it.
			if ( $currency_code == NULL ) {
				$currency_code = self::$currency_formatter->getTextAttribute( NumberFormatter::CURRENCY_CODE );
			}

			//If currency_code is still blank, force it to a default value, otherwise the currency formatter won't output anything at all.
			if ( $currency_code == '' ) {
				Debug::text('ERROR: Unable to determine currency code, defaulting to USD!', __FILE__, __LINE__, __METHOD__, 10);
				$currency_code = 'USD';
			}

			if ( $show_code == 0 ) {
				//Since currency formatting can be different in different locale/currency pairs, we need get the symbol ($US) in the current locale and the current currency pair so it can be stripped and replaced with just an actual symbol ($)
				//For example, USD could be $US, US$, $ depending on the exact locale. Though the exact formatting seems to vary depending on Linux distro's too.
				$tmp_formatter = new NumberFormatter( self::getLocale() .'@currency='. $currency_code, NumberFormatter::CURRENCY );

				//Debug::text('INTL Currency Symbol: '. $tmp_formatter->getSymbol( NumberFormatter::CURRENCY_SYMBOL ) .'('. self::$currency_formatter->getSymbol( NumberFormatter::CURRENCY_SYMBOL ) .') TTi18n Symbol: '. self::getCurrencySymbol( $currency_code ) .' Raw Currency Format: '. self::$currency_formatter->formatCurrency( $amount, $currency_code ), __FILE__, __LINE__, __METHOD__, 10);
				//self::$currency_formatter->setPattern( str_replace('¤', '', self::$currency_formatter->getPattern() ) ); //Strip currency code off pattern. This seems to strip currency symbol too though.
				return str_replace( array( self::$currency_formatter->getSymbol( NumberFormatter::CURRENCY_SYMBOL ), $tmp_formatter->getSymbol( NumberFormatter::CURRENCY_SYMBOL ) ), self::getCurrencySymbol( $currency_code ), self::$currency_formatter->formatCurrency( $amount, $currency_code ) ); //This seemed to always show US$1.23, or CA$1.23
			} else {
				return $currency_code_left_str . self::$currency_formatter->formatCurrency( $amount, $currency_code ) . $currency_code_right_str;
			}
		} else {
			//Fallback to this if INTL extension is not installed.
			return $currency_code_left_str . self::formatNumber( $amount, TRUE, 2, 2 ) . $currency_code_right_str;
		}
	}

	static function getCurrencySymbol( $iso_code ) {
		static $currency_symbols = array (
										'AED' => 'د.إ', //('United Arab Emirates')
										'AFA' => 'نی', //('Afghanistan')
										'ALL' => 'Lek', //('Albania')
										'AMD' => 'դր.', // ('Armenia')
										'ANG' => 'ƒ', //('Netherlands Antilles')
										'AON' => 'Kz', //('Angola')
										'ARA' => '$', //('Argentina'),
										'AUD' => '$', //('Australia')('Christmas Island')('Cocos (Keeling) Islands')('Heard Island and Mcdonald Islands')('Kiribati')('Nauru')('Tuvalu')
										'AWG' => 'ƒ', //('Aruba')
										'AZM' => 'm', //('Azerbaijan')
										'BAM' => 'KM', //('Bosnia and Herzegovina')
										'BBD' => '$', //('Barbados')
										'BDT' => 'Tk', //('Bangladesh')
										'BGL' => 'лв', //('Bulgaria')
										'BHD' => 'دج', //('Bahrain')
										'BIF' => '₣', //('Burundi')
										'BMD' => '$', //('Bermuda')
										'BND' => '$', //('Brunei Darussalam')
										'BOB' => '$b', //('Bolivia')
										'BRR' => 'R$', //('Brazil')
										'BSD' => '$', //('Bahamas')
										'BTN' => 'Nu', //('Bhutan')
										'BWP' => 'P', //('Botswana')
										'BYR' => 'p.', //('Belarus')
										'BZD' => 'BZ$', //('Belize')
										'CAD' => '$', //('Canada')
										'CDF' => 'F', //('Congo, Democratic Republic')
										'CDZ' => 'CDZ', //('Congo, the Democratic Republic of')
										'CHF' => '₣', //('Liechtenstein')('Switzerland')
										'CLF' => '$', //('Chile')
										'CNY' => '¥', //('China')
										'COP' => '$', //('Colombia')
										'CRC' => '₡', //Costa Rica
										'CSD' => 'دج', //('Serbia and Montenegro')
										'CUP' => '$', //('Cuba')
										'CVE' => '$', //('Cape Verde')
										'CYP' => '£', //('Cyprus')
										'CZK' => 'Kč', //('Czech Republic')
										'DJF' => '₣', //('Djibouti')
										'DKK' => 'kr', //('Denmark')('Faroe Islands')('Greenland')
										'DOP' => 'RD$', //('Dominican Republic')
										'DZD' => 'دج', //('Algeria')
										'EEK' => 'kr', //('Estonia')
										'EGP' => '£', //('Egypt')
										'ERN' => 'Nfk', //('Eritrea')
										'ETB' => 'Br', //('Ethiopia')
										'EUR' => '€', //('Germany')('Andorra')('Austria')('Belgium')('Finland')('France')('Greece')('French Guiana')('French Southern Territories')('Guadeloupe')('Holy See (Vatican City State)')('Ireland')('Italy')('Luxembourg')('Martinique')('Mayotte')('Monaco')('Netherlands')('Portugal')('Reunion')('San Marino')('Spain')('Saint Pierre and Miquelon')
										'FJD' => '$', //'Fiji')
										'FKP' => '£', //('Falkland Islands (Malvinas)')
										'GBP' => '£', //('United Kingdom')('British Indian Ocean Territory')('South Georgia, South Sandwich Islands')
										'GEL' => '$', //('Georgia')
										'GHC' => '₵', //('Ghana')
										'GIP' => '£', //('Gibraltar')
										'GMD' => 'D', //('Gambia')
										'GNS' => '$', //('Guinea')
										'GTQ' => 'Q', //('Guatemala')
										'GWP' => '$', //('Guinea-Bissau')
										'GYD' => '$', //('Guyana')
										'HKD' => 'HK$', //('Hong Kong')
										'HNL' => 'L', //('Honduras')
										'HRK' => 'kn', //('Croatia')
										'HTG' => 'G', //('Haiti')
										'HUF' => 'Ft', //('Hungary')
										'IDR' => 'Rp', //('Indonesia')
										'ILS' => '₪', //('Israel')
										'INR' => '₨', //('India')
										'IQD' => 'ع.د', //('Iraq')
										'IRR' => '﷼', //('Iran, Islamic Republic of')
										'ISK' => 'kr', //('Iceland'),
										'JMD' => 'J$', //('Jamaica')
										'JOD' => 'ع.د', //('Jordan')
										'JPY' => '¥', //('Japan')
										'KES' => 'KSh', //('Kenya')
										'KGS' => 'лв', //('Kyrgyzstan')
										'KHR' => '$', //('Cambodia')
										'KMF' => '₣', //('Comoros')
										'KPW' => '₩', //('Korea, Democratic People\'s Republic of')
										'KRW' => '₩', //('Korea, Republic of')
										'KWD' => 'د.ك', //('Kuwait')
										'KYD' => '$', //('Cayman Islands')
										'KZT' => 'лв', //('Kazakhstan')
										'LAK' => '₭', //('Lao People\'s Democratic Republic')
										'LBP' => '£', //('Lebanon')
										'LKR' => '₨', //('Sri Lanka')
										'LRD' => '$', //('Liberia')
										'LSL' => 'L', //('Lesotho')
										'LTL' => 'Lt', //('Lithuania')
										'LVL' => 'Ls', //('Latvia')
										'LYD' => 'ل.د', //('Libyan Arab Jamahiriya')
										'MAD' => 'د.م', //('Morocco')('Western Sahara')
										'MDL' => 'L', //('Moldova, Republic of')
										'MGF' => '₣', //('Madagascar')
										'MKD' => 'ден', //('Macedonia, Former Yugoslav Republic of')
										'MMK' => 'K', //('Myanmar')
										'MNT' => '₮', //('Mongolia')
										'MOP' => 'MOP$', //('Macao')
										'MRO' => 'UM', //('Mauritania')
										'MTL' => 'Lm', //('Malta')
										'MUR' => '₨', //('Mauritius')
										'MVR' => 'Rf', //('Maldives')
										'MWK' => 'MK', //('Malawi')
										'MXN' => '$', //('Mexico')
										'MYR' => 'RM', //('Malaysia')
										'MZM' => 'MTn', //('Mozambique')
										'NAD' => '$', //('Namibia')
										'NGN' => '₦', //('Nigeria')
										'NIC' => 'C$', //('Nicaragua')
										'NOK' => 'kr', //('Antarctica')('Bouvet Island')('Norway')('Svalbard and Jan Mayen')
										'NPR' => '₨', //('Nepal')
										'NZD' => '$', //('New Zealand')
										'OMR' => '﷼', //('Oman')
										'PAB' => '$', //('Panama')
										'PEI' => 'I/.', //('Peru')
										'PGK' => 'K', //('Papua New Guinea')
										'PHP' => 'Php', //('Philippines')
										'PKR' => '₨', //('Pakistan')
										'PLN' => 'zł', //('Poland')
										'PYG' => 'Gs', //('Paraguay')
										'QAR' => '﷼', //('Qatar')
										'ROL' => 'L', //('Romania')
										'RUB' => 'руб', //('Russian Federation')
										'RWF' => '₣', //('Rwanda')
										'SAR' => '﷼', //('Saudi Arabia')
										'SBD' => '$', //('Solomon Islands')
										'SCR' => '₨', //('Seychelles')
										'SDP' => '£Sd', //('Sudan')
										'SEK' => 'kr', //('Sweden')
										'SGD' => '$', //('Singapore')
										'SHP' => '£', //('Saint Helena')
										'SIT' => 'SIT', //('Slovenia')
										'SKK' => 'kr', //('Slovakia')
										'SLL' => 'Le', //('Sierra Leone')
										'SOS' => 'S', //('Somalia')
										'SRG' => 'ƒ', //('Suriname')
										'STD' => 'Db', //('Sao Tome and Principe')
										'SUR' => 'руб',
										'SVC' => '₡', //('El Salvador')
										'SYP' => '£', //('Syrian Arab Republic')
										'SZL' => 'L', //('Swaziland')
										'THB' => '฿', //('Thailand')
										'TMM' => 'm', //('Turkmenistan')
										'TND' => 'د.ت', //('Tunisia')
										'TOP' => 'T$', //('Tonga')
										'TPE' => '$',
										'TRL' => '₤', //('Turkey')
										'TTD' => '$', //('Trinidad and Tobago')
										'TWD' => '$', //('Taiwan, Province of China')
										'TZS' => 'x/y', //('Tanzania, United Republic of')
										'UAH' => '₴', //('Ukraine')
										'UGS' => 'USh', //('Uganda')
										'USD' => '$',	 //('United States')('American Samoa')('Ecuador')('Guam')('Marshall Islands')('Micronesia, Federated States of')('Northern Mariana Islands')('Palau')('Puerto Rico')('Turks and Caicos Islands')('United States Minor Outlying Islands')('Virgin Islands, British')('Virgin Islands, U.s.')
										'UYU' => '$U', //('Uruguay')
										'UZS' => 'лв', //('Uzbekistan')
										'VEB' => 'Bs', //('Venezuela')
										'VND' => '₫', //('Viet Nam')
										'VUV' => 'Vt', //('Vanuatu')
										'WST' => 'WS$', //('Samoa')
										'XAF' => '₣', //('Benin')
										'XCD' => '$', //('Anguilla')('Antigua and Barbuda')('Dominica')('Grenada')('Montserrat')('Saint Kitts and Nevis')('Saint Lucia')('Saint Vincent, Grenadines')
										'XOF' => '₣', //('Niger')('Senegal')
										'XPF' => '₣', //('Wallis and Futuna')('French Polynesia')('New Caledonia')
										'YER' => '﷼', //('Yemen')
										'ZAR' => 'R', //('South Africa')
										'ZMK' => 'ZK', //('Zambia')
										'ZMW' => 'ZK', //('Zambia') new as of August 2012
										'ZWD' => 'Z$', //('Zimbabwe')
								);

		if ( isset($currency_symbols[$iso_code]) ) {
			return($currency_symbols[$iso_code]);
		}

		return '$';
	}

	static function detectUTF8($string) {
			return (bool)preg_match('%(?:
			[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
			|\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
			|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
			|\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
			|\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
			|[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
			|\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
			)+%xs', $string);
	}

	/*

		Date Functions

	*/
}
?>
