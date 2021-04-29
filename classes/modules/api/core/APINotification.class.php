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


/**
 * @package API\Core
 */
class APINotification extends APIFactory {
	protected $main_class = '';

	/**
	 * APINotification constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Returns array of notifications message to be displayed to the user.
	 * @param bool|string $action Action that is being performed, possible values: 'login', 'preference', 'notification', 'pay_period'
	 * @return array|bool
	 */
	function getNotifications( $action = false ) {
		global $config_vars, $disable_database_connection;

		$retarr = false;

		//Skip this step if disable_database_connection is enabled or the user is going through the installer still
		switch ( strtolower( $action ) ) {
			case 'login':
				if ( ( !isset( $disable_database_connection ) || ( isset( $disable_database_connection ) && $disable_database_connection != true ) )
						&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) ) ) {
					//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
					$sslf = new SystemSettingListFactory();
					$system_settings = $sslf->getAllArray();
				}
				unset( $sslf );

				//Check license validity
				if ( ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					if ( !isset( $system_settings['license'] ) ) {
						$system_settings['license'] = null;
					}

					$license = new TTLicense();
					$license_validate = $license->validateLicense( $system_settings['license'], null );
					$license_message = $license->getFullErrorMessage( $license_validate, true );
					if ( $license_message != '' ) {
						$destination_url = 'https://www.timetrex.com/r.php?id=899';

						if ( $license_validate === true ) {
							//License likely expires soon.
							if (
									( $license->getExpireDays() >= 15 && $this->getPermissionObject()->getLevel() >= 80 ) //When expires in more than 15 days only show administrators.
									||
									( $license->getExpireDays() <= 14 && $this->getPermissionObject()->getLevel() >= 40 ) //When expires in less than 14 days show administrators/supervisors
									||
									( $license->getExpireDays() <= 7 ) //When expires in less than 7 days show all employees.
							) {
								$retarr[] = [
										'delay'       => 0, //0= Show until clicked, -1 = Show until next getNotifications call.
										'bg_color'    => '#FFFF00', //Yellow
										'message'     => TTi18n::getText( 'WARNING: %1', $license_message ),
										'destination' => $destination_url,
								];
							}
						} else {
							//License error.
							$retarr[] = [
									'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
									'bg_color'    => '#FF0000', //Red
									'message'     => TTi18n::getText( 'WARNING: %1', $license_message ),
									'destination' => $destination_url,
							];
						}
					}
					unset( $license, $license_validate, $license_message, $destination_url );
				}

				//Database schema still in sync.
				if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY && isset( $system_settings['schema_version_group_B'] ) ) {
					$retarr[] = [
							'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: %1 database schema is out of sync with edition and likely corrupt. Please contact your %1 administrator immediately.', APPLICATION_NAME ),
							'destination' => null,
					];
				}

				//Give early warning to installs using older stack components before the next version is released that forces the upgrade.
				if ( version_compare( PHP_VERSION, '7.2.0', '<' ) == true || PHP_INT_SIZE === 4 ) { //Check for 32-bit installs as well, since accrual policies with length of service dates >2038 will fail.
					if ( OPERATING_SYSTEM == 'WIN' ) {
						$message = TTi18n::getText( 'WARNING: System stack components are out-of-date and not supported with this version of %1! Please perform a manual upgrade to the latest version of %1 immediately!', APPLICATION_NAME );
					} else {
						$message = TTi18n::getText( 'WARNING: System stack components (PHP/%2) are out-of-date and not supported with this version of %1! Please upgrade them immediately!', [ APPLICATION_NAME, strtoupper( $config_vars['database']['type'] ) ] );
					}

					$retarr[] = [
							'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
							'bg_color'    => '#FF0000', //Red
							'message'     => $message,
							'destination' => null,
					];
					unset( $message );
				}

				//Check to make sure hostname specified in .ini file matches the hostname used to login to TimeTrex.
				if ( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '' && isset( $config_vars['other']['hostname'] ) && $config_vars['other']['hostname'] != '' && $this->getPermissionObject()->getLevel() >= 80 ) {
					if ( stripos( $_SERVER['HTTP_HOST'], $config_vars['other']['hostname'] ) === FALSE ) {
						$retarr[] = [
								'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
								'bg_color'    => '#FF0000', //Red
								'message'     => TTi18n::getText( 'WARNING: Hostname specified in %1 config file does not match the accessed URL. Please contact your %1 administrator immediately.', APPLICATION_NAME ),
								'destination' => null,
						];
					}
				}

				//System Requirements not being met.
				if ( isset( $system_settings['valid_install_requirements'] ) && DEPLOYMENT_ON_DEMAND == false && (int)$system_settings['valid_install_requirements'] == 0 ) {
					$retarr[] = [
							'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: %1 system requirement check has failed! Please contact your %1 administrator immediately to re-run the %1 installer to correct the issue.', APPLICATION_NAME ),
							'destination' => null,
					];
				}

				//AutoUpgrade failed.
				if ( isset( $system_settings['auto_upgrade_failed'] ) && DEPLOYMENT_ON_DEMAND == false && (int)$system_settings['auto_upgrade_failed'] == 1 ) {
					$retarr[] = [
							'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: %1 automatic upgrade has failed due to a system error! Please contact your %1 administrator immediately to re-run the %1 installer to correct the issue.', APPLICATION_NAME ),
							'destination' => null,
					];
				}

				//Check version mismatch
				if ( isset( $system_settings['system_version'] ) && DEPLOYMENT_ON_DEMAND == false && APPLICATION_VERSION != $system_settings['system_version'] ) {
					$retarr[] = [
							'delay'       => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: %1 application version does not match database version. Please re-run the %1 installer to complete the upgrade process.', APPLICATION_NAME ),
							'destination' => null,
					];
				}


				$application_version_date_days_old = TTDate::getDays( ( time() - (int)APPLICATION_VERSION_DATE ) );
				if (
						//After 1yr, show message only to primary company, supervisors or higher permissions.
						( $application_version_date_days_old > 365 && $this->getPermissionObject()->getLevel() >= 40 && ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) )

						//After 1yr + 30 days, show message only to primary company, all employees.
						|| ( $application_version_date_days_old > 395 && ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) )

						//After 1yr + 60 days, show message only to all companies, supervisors or higher permissions.
						|| ( $application_version_date_days_old > 425 && $this->getPermissionObject()->getLevel() >= 40 )

						//After 1yr + 90 days, show message to all companies, all employees
						|| ( $application_version_date_days_old > 455 )
				) {
					$retarr[] = [
							'delay'       => -1,
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: This %1 version (v%2) is severely out of date and may no longer be supported. Please upgrade to the latest version as soon as possible as invalid calculations may already be occurring.', [ APPLICATION_NAME, APPLICATION_VERSION ] ),
							'destination' => null,
					];
				}
				unset( $application_version_date_days_old );

				//New version available notification.
				if ( DEMO_MODE == false
						&& ( isset( $system_settings['new_version'] ) && $system_settings['new_version'] == 1 )
						&& ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] )
						&& $this->getPermissionObject()->getLevel() >= 80 ) { //Payroll Admin

					//Only display this every two weeks.
					$new_version_available_notification_arr = UserSettingFactory::getUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_available_notification' );
					if ( !isset( $new_version_available_notification_arr['value'] ) || ( isset( $new_version_available_notification_arr['value'] ) && $new_version_available_notification_arr['value'] <= ( time() - ( 86400 * 14 ) ) ) ) {
						UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_available_notification', time() );

						$retarr[] = [
								'delay'       => -1,
								'bg_color'    => '#FFFF00', //Yellow
								'message'     => TTi18n::getText( 'NOTICE: A new version of %1 available, it is highly recommended that you upgrade as soon as possible. Click here to download the latest version.', [ APPLICATION_NAME ] ),
								'destination' => ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) ? 'https://www.timetrex.com/r.php?id=19' : 'https://www.timetrex.com/r.php?id=9',
						];
					}
					unset( $new_version_available_notification_arr );
				}

				//Check for major new version.
				$new_version_notification_arr = UserSettingFactory::getUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_notification' );
				if ( DEMO_MODE == false
						&& ( !isset( $config_vars['branding']['application_name'] ) || ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) )
						&& $this->getPermissionObject()->getLevel() >= 80 //Payroll Admin
						&& $this->getCurrentUserObject()->getCreatedDate() <= APPLICATION_VERSION_DATE
						&& ( !isset( $new_version_notification_arr['value'] ) || ( isset( $new_version_notification_arr['value'] ) && Misc::MajorVersionCompare( APPLICATION_VERSION, $new_version_notification_arr['value'], '>' ) ) ) ) {
					UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_notification', APPLICATION_VERSION );

					$retarr[] = [
							'delay'       => -1,
							'bg_color'    => '#FFFF00', //Yellow
							'message'     => TTi18n::getText( 'NOTICE: Your instance of %1 has been upgraded to v%2, click here to see whats new.', [ APPLICATION_NAME, APPLICATION_VERSION ] ),
							'destination' => 'https://www.timetrex.com/h.php?id=changelog&v=' . APPLICATION_VERSION . '&e=' . $this->getCurrentCompanyObject()->getProductEdition(),
					];
				}
				unset( $new_version_notification_arr );

				//Check installer enabled.
				if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) {
					$retarr[] = [
							'delay'       => -1,
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: %1 is currently in INSTALL MODE. Please go to your timetrex.ini.php file and set "installer_enabled" to "FALSE".', APPLICATION_NAME ),
							'destination' => null,
					];
				}

				//Make sure CronJobs are running correctly.
				$cjlf = new CronJobListFactory();
				$cjlf->getMostRecentlyRun();
				if ( $cjlf->getRecordCount() > 0 ) {
					//Is last run job more then 48hrs old?
					$cj_obj = $cjlf->getCurrent();

					if ( PRODUCTION == true
							&& DEMO_MODE == false
							&& $cj_obj->getLastRunDate() < ( time() - 172800 )
							&& $cj_obj->getCreatedDate() < ( time() - 172800 ) ) {
						$retarr[] = [
								'delay'       => -1,
								'bg_color'    => '#FF0000', //Red
								'message'     => TTi18n::getText( 'WARNING: Critical maintenance jobs have not run in the last 48hours. Please contact your %1 administrator immediately.', APPLICATION_NAME ),
								'destination' => null,
						];
					}
				}
				unset( $cjlf, $cj_obj );

				//Check if any pay periods are past their transaction date and not closed.
				if ( DEMO_MODE == false && $this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' ) && $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) ) {
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$pplf->getByCompanyIdAndStatusAndTransactionDate( $this->getCurrentCompanyObject()->getId(), [ 10, 12, 30 ], TTDate::getBeginDayEpoch( time() ) ); //Open or Locked or Post Adjustment pay periods.
					if ( $pplf->getRecordCount() > 0 ) {
						foreach ( $pplf as $pp_obj ) {
							if ( is_object( $pp_obj->getPayPeriodScheduleObject() ) && $pp_obj->getPayPeriodScheduleObject()->getCreatedDate() < ( time() - ( 86400 * 40 ) ) ) { //Ignore pay period schedules newer than 40 days. They automatically start being closed after 45 days.
								$retarr[] = [
										'delay'       => 0,
										'bg_color'    => '#FF0000', //Red
										'message'     => TTi18n::getText( 'WARNING: Pay periods past their transaction date have not been closed yet. It\'s critical that these pay periods are closed to prevent data loss, click here to close them now.' ),
										'destination' => [ 'menu_name' => 'Pay Periods' ],
								];
								break;
							}
						}
					}
					unset( $pplf, $pp_obj );
				}

				if ( $this->getPermissionObject()->Check( 'message', 'enabled' ) && ( $this->getPermissionObject()->Check( 'message', 'view' ) || $this->getPermissionObject()->Check( 'message', 'view_own' ) ) ) {
					//Check for unread messages
					$mclf = new MessageControlListFactory();
					$unread_messages = $mclf->getNewMessagesByCompanyIdAndUserId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
					Debug::text( 'UnRead Messages: ' . $unread_messages, __FILE__, __LINE__, __METHOD__, 10 );
					if ( $unread_messages > 0 ) {
						$retarr[] = [
								'delay'       => 25,
								'bg_color'    => '#FFFF00', //Yellow
								'message'     => TTi18n::getText( 'NOTICE: You have %1 new message(s) waiting, click here to read them now.', $unread_messages ),
								'destination' => [ 'menu_name' => 'Messages' ],
						];
					}
					unset( $mclf, $unread_messages );
				}

				if ( DEMO_MODE == false && ( $this->getPermissionObject()->Check( 'punch', 'enabled' ) && ( $this->getPermissionObject()->Check( 'punch', 'view_own' ) || $this->getPermissionObject()->Check( 'punch', 'punch_in_out' ) ) ) ) { //Exceptions are only viewable if they permissions to punch in/out.
					$elf = new ExceptionListFactory();
					$elf->getFlaggedExceptionsByUserIdAndPayPeriodStatus( $this->getCurrentUserObject()->getId(), 10 );
					$display_exception_flag = false;
					if ( $elf->getRecordCount() > 0 ) {
						foreach ( $elf as $e_obj ) {
							if ( $e_obj->getColumn( 'severity_id' ) == 30 ) {
								$display_exception_flag = 'red';
							}
							break;
						}
					}
					if ( isset( $display_exception_flag ) && $display_exception_flag !== false ) {
						Debug::Text( 'Exception Flag to Display: ' . $display_exception_flag, __FILE__, __LINE__, __METHOD__, 10 );
						$retarr[] = [
								'delay'       => 30,
								'bg_color'    => '#FFFF00', //Yellow
								'message'     => TTi18n::getText( 'NOTICE: You have critical severity exceptions pending, click here to view them now.' ),
								'destination' => [ 'menu_name' => 'Exceptions' ],
						];
					}
					unset( $elf, $e_obj, $display_exception_flag );
				}

				if ( DEMO_MODE == false
						&& $this->getPermissionObject()->getLevel() >= 80 //Payroll Admin
						&& ( $this->getCurrentUserObject()->getWorkEmail() == '' && $this->getCurrentUserObject()->getHomeEmail() == '' ) ) {
					$retarr[] = [
							'delay'       => 30,
							'bg_color'    => '#FF0000', //Red
							'message'     => TTi18n::getText( 'WARNING: Please click here and enter an email address for your account, this is required to receive important notices and prevent your account from being locked out.' ),
							'destination' => [ 'menu_name' => 'Contact Information' ],
					];
				}

				break;
			default:
				break;
		}

		//Check timezone is proper.
		$current_user_prefs = $this->getCurrentUserObject()->getUserPreferenceObject();
		if ( $current_user_prefs->setDateTimePreferences() == false ) {
			//Setting timezone failed, alert user to this fact.
			//WARNING: %1 was unable to set your time zone. Please contact your %1 administrator immediately.{/t} {if $permission->Check('company', 'enabled') AND $permission->Check('company', 'edit_own')}<a href="https://forums.timetrex.com/viewtopic.php?t=40">{t}For more information please click here.{/t}</a>{/if}
			if ( $this->getPermissionObject()->Check( 'company', 'enabled' ) && $this->getPermissionObject()->Check( 'company', 'edit_own' ) ) {
				$destination_url = 'https://www.timetrex.com/r.php?id=1010';
				$sub_message = TTi18n::getText( 'For more information please click here.' );
			} else {
				$destination_url = null;
				$sub_message = null;
			}

			$retarr[] = [
					'delay'       => -1,
					'bg_color'    => '#FF0000', //Red
					'message'     => TTi18n::getText( 'WARNING: %1 was unable to set your time zone. Please contact your %1 administrator immediately.', APPLICATION_NAME ) . ' ' . $sub_message,
					'destination' => $destination_url,
			];
			unset( $destination_url, $sub_message );
		}

		return $retarr;
	}
}

?>
