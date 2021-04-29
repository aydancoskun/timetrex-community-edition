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
require_once( '../../includes/global.inc.php' );
forceNoCacheHeaders(); //Send headers to disable caching.
TTi18n::chooseBestLocale();
extract( FormVariables::GetVariables(
		[
				'action',
				'email',
				'email_confirmed',
				'key',
		] ) );
$validator = new Validator();
$action = Misc::findSubmitButton();
Debug::Text( 'Action: ' . $action, __FILE__, __LINE__, __METHOD__, 10 );
switch ( $action ) {
	case 'confirm_email':
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByEmailIsValidKey( $key );
		if ( $ulf->getRecordCount() == 1 ) {
			Debug::Text( 'FOUND Email Validation key! Email: ' . $email, __FILE__, __LINE__, __METHOD__, 10 );

			$valid_key = true;

			$ttsc = new TimeTrexSoapClient();

			$user_obj = $ulf->getCurrent();
			if ( $user_obj->getWorkEmailIsValidKey() == $key && $user_obj->getWorkEmail() == $email ) {
				$user_obj->setWorkEmailIsValidKey( '' );
				//$user_obj->setWorkEmailIsValidDate( '' ); //Keep date so we know when the address was validated last.
				$user_obj->setWorkEmailIsValid( true );

				$remote_validation_result = $ttsc->validateEmail( $user_obj->getWorkEmail() );
			} else if ( $user_obj->getHomeEmailIsValidKey() == $key && $user_obj->getHomeEmail() == $email ) {
				$user_obj->setHomeEmailIsValidKey( '' );
				//$user_obj->setHomeEmailIsValidDate( '' ); //Keep date so we know when the address was validated last.
				$user_obj->setHomeEmailIsValid( true );

				$remote_validation_result = $ttsc->validateEmail( $user_obj->getHomeEmail() );
			} else {
				$valid_key = false;
			}

			if ( $valid_key == true && $user_obj->isValid() ) {
				$user_obj->Save( false );
				Debug::Text( 'Email validation is succesful!', __FILE__, __LINE__, __METHOD__, 10 );

				TTLog::addEntry( $user_obj->getId(), 500, TTi18n::gettext( 'Validated email address' ) . ': ' . $email, $user_obj->getId(), 'users' );

				Redirect::Page( URLBuilder::getURL( [ 'email_confirmed' => 1, 'email' => $email ], Environment::getBaseURL() . 'html5/ConfirmEmail.php' ) );
				break;
			} else {
				Debug::Text( 'aDID NOT FIND email validation key!', __FILE__, __LINE__, __METHOD__, 10 );
				$email_confirmed = false;
			}
		} else {
			Debug::Text( 'bDID NOT FIND email validation key!', __FILE__, __LINE__, __METHOD__, 10 );
			$email_confirmed = false;
		}
		break;
	default:
		//Make sure we don't allow malicious users to use some long email address like:
		//"This is the FBI, you have been fired if you don't..."
		if ( $validator->isEmail( 'email', $email, TTi18n::getText( 'Invalid confirmation key' ) ) == false ) {
			$email = null;
			$email_confirmed = false;
		}

		break;
}
$BASE_URL = './';
$META_TITLE = TTi18n::getText( 'Confirm Email' );
require( '../../includes/Header.inc.php' );
?>
<div id="contentContainer" class="content-container">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<div id="contentBox-ConfirmEmail">
					<div class="textTitle2"><?php echo TTi18n::getText( 'Email Address Confirmed' ) ?></div>
					<?php if ( $email_confirmed == true ) { ?>
						<div id="rowWarning" class="text-center">
							<?php echo TTi18n::getText( 'Email address' ) . ' <b>' . $email . '</b> ' . TTi18n::getText( 'has been confirmed and activated.' ) ?>
						</div>
					<?php } else {
						if ( $email_confirmed == false ) { ?>
							<div id="rowWarning" valign="center">
								<?php echo TTi18n::getText( 'Invalid or expired confirmation key, please try again.' ) ?>
							</div>
						<?php }
					} ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
require( '../../includes/Footer.inc.php' );
?>
