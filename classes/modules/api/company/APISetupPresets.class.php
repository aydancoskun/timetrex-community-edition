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
 * @package API\Company
 */
class APISetupPresets extends APIFactory {
	protected $main_class = 'SetupPresets';

	/**
	 * APISetupPresets constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * @param $location_data
	 * @param $legal_entity_id
	 * @return bool
	 */
	function createPresets( $location_data, $legal_entity_id ) {
		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( is_array( $location_data ) ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( count( $location_data ) + 1 ), null, TTi18n::getText( 'Creating policies...' ) );

			$sp = $this->getMainClassObject();
			$sp->setCompany( $this->getCurrentCompanyObject()->getId() );
			$sp->setUser( $this->getCurrentUserObject()->getId() );

			$sp->createPresets();

			$already_processed_country = [];
			$i = 1;
			if ( $legal_entity_id == TTUUID::getZeroID() ) {
				$legal_entity_id = null;
			}

			foreach ( $location_data as $location ) {
				if ( isset( $location['country'] ) && isset( $location['province'] ) ) {
					if ( $location['province'] == '00' ) {
						$location['province'] = null;
					}

					if ( !in_array( $location['country'], $already_processed_country ) ) {
						$sp->createPresets( $location['country'], null, null, null, null, $legal_entity_id );
					}

					$sp->createPresets( $location['country'], $location['province'], null, null, null, $legal_entity_id );
					Debug::text( 'Creating presets for Country: ' . $location['country'] . ' Province: ' . $location['province'], __FILE__, __LINE__, __METHOD__, 9 );

					$already_processed_country[] = $location['country'];
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i );
				$i++;
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i, TTi18n::getText( 'Creating Permissions...' ) );
			$sp->Permissions();
			$sp->UserDefaults( $this->getCurrentUserObject()->getLegalEntity() );

			//Assign the current user to the only existing pay period schedule.
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
			if ( $ppslf->getRecordCount() == 1 ) {
				$pps_obj = $ppslf->getCurrent();

				//In case the user runs the quick start wizard after they are already setup, assign all users to the only existing pay period schedule.
				$user_ids = [];
				$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
				if ( $ulf->getRecordCount() > 0 ) {
					foreach ( $ulf as $u_obj ) {
						$user_ids[] = $u_obj->getId();
					}
				}
				$pps_obj->setUser( $user_ids );
				unset( $user_ids );

				Debug::text( 'Assigning current user to pay period schedule: ' . $pps_obj->getID(), __FILE__, __LINE__, __METHOD__, 9 );
				if ( $pps_obj->isValid() ) {
					$pps_obj->Save();
				}
			}

			$this->getCurrentCompanyObject()->setSetupComplete( true );
			if ( $this->getCurrentCompanyObject()->isValid() ) {
				$this->getCurrentCompanyObject()->Save();
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
		}

		return true;
	}
}

?>
