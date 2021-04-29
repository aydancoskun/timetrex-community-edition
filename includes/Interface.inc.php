<?php /** @noinspection PhpUndefinedVariableInspection */
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

//
//Primary purpose of this include now is to just setup the $current_user, $current_user_prefs and $current_company globals when accessing pages outside of the API, such as send_file.php and upload_file.php.
//

forceNoCacheHeaders(); //Send headers to disable caching.


//Skip this step if disable_database_connection is enabled or the user is going through the installer still
$clf = new CompanyListFactory();
if ( ( !isset( $disable_database_connection ) || ( isset( $disable_database_connection ) && $disable_database_connection != true ) )
		&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) ) ) {
	//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
	try {
		$sslf = new SystemSettingListFactory();
		$system_settings = $sslf->getAllArray();
		unset( $sslf );

		//Get primary company data needs to be used when user isn't logged in as well.
		$clf->getByID( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() == 1 ) {
			$primary_company = $clf->getCurrent();
		}
	} catch ( Exception $e ) {
		//Database not initialized, or some error, redirect to Install page.
		throw new DBError( $e, 'DBInitialize' );
	}
}

$permission = new Permission();

$authentication = new Authentication();
if ( isset( $authenticate ) && $authenticate === false ) {
	Debug::text( 'Bypassing Authentication', __FILE__, __LINE__, __METHOD__, 10 );
	TTi18n::chooseBestLocale();
} else {
	if ( $authentication->Check() === true ) {
		$profiler->startTimer( 'Interface.inc - Post-Authentication' );

		/*
		 * Get default interface data here. Things like User info, Company info etc...
		 */

		$current_user = $authentication->getObject();
		Debug::text( 'User Authenticated: ' . $current_user->getUserName() . ' Created Date: ' . $authentication->getCreatedDate(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $primary_company ) && PRIMARY_COMPANY_ID == $current_user->getCompany() ) {
			$current_company = $primary_company;
		} else {
			$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();
		}

		$current_user_prefs = $current_user->getUserPreferenceObject();

		//If user doesnt have any preferences set, we need to bootstrap the preference object.
		if ( $current_user_prefs->getUser() == '' ) {
			$current_user_prefs->setUser( $current_user->getId() );
		}

		/*
		 *	Check locale cookie, if it varies from UserPreference Language,
		 *	change user preferences to match. This could cause some unexpected behavior
		 *  as the change is happening behind the scenes, but if we don't change
		 *  the user prefs then they could login for weeks/months as a different
		 *  language from their preferences, therefore making the user preference
		 *  setting almost useless. Causing issues when printing pay stubs and in each
		 *  users language.
		 */
		Debug::text( 'Locale Cookie: ' . TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $current_user_prefs->isNew() == false && TTi18n::getLocaleCookie() != '' && $current_user_prefs->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
			Debug::text( 'Changing User Preference Language to match cookie...', __FILE__, __LINE__, __METHOD__, 10 );
			$current_user_prefs->setLanguage( TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) );
			if ( $current_user_prefs->isValid() ) {
				$current_user_prefs->Save( false );
			}
		} else {
			Debug::text( 'User Preference Language matches cookie!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( isset( $_GET['language'] ) && $_GET['language'] != '' ) {
			TTi18n::setLocale( $_GET['language'] ); //Sets master locale
		} else {
			TTi18n::setLanguage( $current_user_prefs->getLanguage() );
			TTi18n::setCountry( $current_user->getCountry() );
			TTi18n::setLocale(); //Sets master locale
		}

		//Debug::text('Current Company: '. $current_company->getName(), __FILE__, __LINE__, __METHOD__, 10);
		$profiler->stopTimer( 'Interface.inc - Post-Authentication' );
	} else {
		Debug::text( 'User NOT Authenticated!', __FILE__, __LINE__, __METHOD__, 10 );
		Redirect::Page( URLBuilder::getURL( null, Environment::GetBaseURL() . 'html5/' ) );
		//exit;
	}
}
unset( $clf );

$profiler->startTimer( 'Main' );
?>