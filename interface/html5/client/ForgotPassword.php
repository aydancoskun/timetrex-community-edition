<?php
/**
 * $License$
 */

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
require_once('../../../includes/global.inc.php');
forceNoCacheHeaders(); //Send headers to disable caching.
TTi18n::chooseBestLocale();
extract	(FormVariables::GetVariables(
										array	(
													'action',
													'email',
													'key',
													'email_sent',
													'password',
													'password2',
												) ) );

$rl = TTNew('RateLimit');
$rl->setID( 'client_contact_password_reset_'. Misc::getRemoteIPAddress() );
$rl->setAllowedCalls( 10 );
$rl->setTimeFrame( 900 ); //15 minutes

$validator = new Validator();

//All HTML special chars are encoded prior to getting here, which makes things like "&" be saved as "&amp;", corrupting passwords.
$password = FormVariables::reverseSanitize( $password );
$password2 = FormVariables::reverseSanitize( $password2 );

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'change_password':
		Debug::Text('Change Password: '. $key, __FILE__, __LINE__, __METHOD__,10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive change password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$action = 'reset_password';
		} else {
			$cclf = TTnew( 'ClientContactListFactory' );
			$cclf->getByPasswordResetKey( $key );
			if ( $cclf->getRecordCount() == 1 ) {
				Debug::Text('FOUND Password reset key! ', __FILE__, __LINE__, __METHOD__,10);

				$cc_obj = $cclf->getCurrent();
				if ( $cc_obj->checkPasswordResetKey( $key ) == TRUE ) {
					$user_name = $cc_obj->getUserName();

					//Make sure passwords match
					if ( $password == $password2 ) {
						//Change password
						$cc_obj->setPassword( $password ); //Password reset key is cleared when password is changed.
						if ( $cc_obj->isValid() ) {
							$cc_obj->Save();
							Debug::Text('Password Change succesful!', __FILE__, __LINE__, __METHOD__,10);

							$rl->delete(); //Clear password reset rate limit upon successful reset.

							//Redirect::Page( URLBuilder::getURL( array('password_reset' => 1 ), 'Login.php' ) );
							Redirect::Page( 'https://www.timetrex.com/store?step=login' );
						}

					} else {
						$validator->isTrue('password',FALSE, 'Passwords do not match');
					}
				} else {
					Debug::Text('DID NOT FIND Valid Password reset key! ', __FILE__, __LINE__, __METHOD__,10);
					$action = 'reset_password';
				}
			} else {
				Debug::Text('DID NOT FIND Password reset key! ', __FILE__, __LINE__, __METHOD__,10);
				$action = 'reset_password';
			}

			Debug::text('Change Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}
		break;
	case 'password_reset':
		//Debug::setVerbosity( 11 );
		Debug::Text('Key: '. $key, __FILE__, __LINE__, __METHOD__,10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive reset password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$action = 'reset_password';
		} else {
			$cclf = TTnew( 'ClientContactListFactory' );
			$cclf->getByPasswordResetKey( $key );
			if ( $cclf->getRecordCount() == 1 ) {
				Debug::Text('FOUND Password reset key! ', __FILE__, __LINE__, __METHOD__,10);
				$cc_obj = $cclf->getCurrent();
				if ( $cc_obj->checkPasswordResetKey( $key ) == TRUE ) {
					$user_name = $cc_obj->getUserName();
					$rl->delete(); //Clear password reset rate limit upon successful reset.
				} else {
					Debug::Text('DID NOT FIND Valid Password reset key! ', __FILE__, __LINE__, __METHOD__,10);
					$action = 'reset_password';
				}
			} else {
				Debug::Text('DID NOT FIND Password reset key! ', __FILE__, __LINE__, __METHOD__,10);
				$action = 'reset_password';
			}

			Debug::text('Reset Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}
		break;
	case 'reset_password':
		//Debug::setVerbosity( 11 );
		Debug::Text('Email: '. $email, __FILE__, __LINE__, __METHOD__,10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive reset password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (z)') );
		} else {
			$cclf = TTnew( 'ClientContactListFactory' );
			//$cclf->getByHomeEmailOrWorkEmail( $email );
			$cclf->getByUserName( $email );
			if ( $cclf->getRecordCount() == 1 ) {
				$cc_obj = $cclf->getCurrent();
				$cc_obj->sendPasswordResetEmail();
				Debug::Text('Found USER! ', __FILE__, __LINE__, __METHOD__,10);

				$rl->delete(); //Clear password reset rate limit upon successful login.

				Redirect::Page( URLBuilder::getURL( array('email_sent' => 1, 'email' => $email ), Environment::getBaseURL().'html5/client/ForgotPassword.php' ) );
			} else {
				//Error
				Debug::Text('DID NOT FIND USER! ', __FILE__, __LINE__, __METHOD__,10);
				$validator->isTrue('email',FALSE, 'Email address was not found in our database');
			}

			Debug::text('Reset Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}
		break;
	default:
		break;
}
$BASE_URL = '../';
$META_TITLE = TTi18n::getText('Password Reset');
require ('../../../includes/Header.inc.php');
?>
<div id="contentContainer" class="content-container">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<div id="contentBox-ForgotPassword">
					<div class="textTitle2"><?php echo TTi18n::getText('Password Reset') ?></div>
					<?php
						if ( $action == 'password_reset' OR $action == 'change_password' ) {
							?>
							<?php if ( !$validator->isValid() ) { ?>
                                <div class="alert alert-danger alert-dismissible fade in" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
									<?php echo TTi18n::getText('Incorrect Input!'); ?>
                                    <script	language=JavaScript>
										var form_modified = true;
                                    </script>
                                    <br>
									<?php echo $validator->geterrors(); ?>
                                </div>
							<?php } ?>
                            <form method="post" name="password_reset" class="form-horizontal" action="">
                            <div class="form-group">
                                <label class="col-sm-3 col-xs-12 control-label"><?php echo TTi18n::getText('Email:') ?> </label>
                                <div class="col-sm-9 col-xs-12">
                                    <p class="form-control-static"><?php echo $user_name; ?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-xs-12 col-sm-3 control-label"><?php echo TTi18n::getText('New Password:') ?> </label>
                                <div class="col-xs-12 col-sm-9">
                                    <input type="password" id="password" class="form-control" name="password">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password2" class="col-xs-12 col-sm-3 control-label"><?php echo TTi18n::getText('New Password (confirm):') ?> </label>
                                <div class="col-xs-12 col-sm-9">
                                    <input type="password" id="password2" class="form-control" name="password2">
                                </div>
                            </div>
							<input type="hidden" name="key" value="<?php echo $key; ?>">
                            <input type="submit" class="button btn btn-default" name="action:change_password" value="<?php echo TTi18n::getText('Change Password') ?>">
                            </form>
							<?php
						} else if ( $email_sent == 1 ) {
							?>
                            <div id="rowWarning" class="text-center">
								<?php echo TTi18n::getText('An email has been sent to') .' <b>'. $email .'</b> '. TTi18n::getText('with instructions on how to change your password.'); ?>
                            </div>
							</div>
							<?php
						} else {
							?>
								<?php if ( !$validator->isValid() ) { ?>
                                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										<?php echo TTi18n::getText('Incorrect Input!'); ?>
                                        <script	language=JavaScript>
											var form_modified = true;
                                        </script>
                                        <br>
										<?php echo $validator->geterrors(); ?>
                                    </div>
								<?php } ?>
                                <form method="post" name="password_reset" class="form-horizontal" action="">
                                <div class="form-group">
                                    <label for="email" class="col-xs-12 col-sm-3 control-label"><?php echo TTi18n::getText('Email Address:'); ?> </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text" id="email" class="form-control" name="email" value="<?php echo $email; ?>">
                                    </div>
                                </div>
                                <input type="submit" class="button btn btn-default" name="action:reset_password" value="<?php echo TTi18n::getText('Reset Password'); ?>">
                                </form>
							</div>
							<?php
						}
					?>
				</div>

			</div>
		</div>
	</div>
</div>
<?php
require ('../../../includes/Footer.inc.php');
?>
