<?php /** @noinspection PhpUnusedLocalVariableInspection */
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
 * @package Modules\Install
 */
class InstallSchema_1093A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @return int
	 */
	function getLastTransactionNumber( $company_id ) {
		$ugdlf = TTnew( 'UserGenericDataListFactory' ); /** @var UserGenericDataListFactory $ugdlf */
		$ugdlf->getByCompanyIdAndScriptAndDefault( $company_id, 'PayStubFactory', true );
		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugd_obj = $ugdlf->getCurrent();
			$setup_data = $ugd_obj->getData();

			if ( isset( $setup_data['file_creation_number'] ) ) {
				Debug::Text( '      Using file_creation_number from UserGenericDataListFactory' );

				return $setup_data['file_creation_number']; // company generic data
			}
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//Remove maintenance file that shouldn't be there in the COMMUNITY EDITION, and causes PHP fatal errors.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$maint_base_path = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'maint' . DIRECTORY_SEPARATOR;
			$scheduled_report_maint_file = $maint_base_path . 'ScheduledReport.php';
			if ( file_exists( $scheduled_report_maint_file ) ) {
				@unlink( $scheduled_report_maint_file );
			}
		}

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll( null, null, null, [ 'created_date' => 'asc' ] );
		Debug::Text( 'Get all companies. Found: ' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $cf ) {
				Debug::text( 'Processing company: ' . $cf->getId() . ' Name: ' . $cf->getName(), __FILE__, __LINE__, __METHOD__, 9 );
				//echo 'Processing company: ' . $cf->getId() .' Name: '. $cf->getName() ."\n";

				//Setup new permissions.
				$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
				$pclf->getByCompanyId( $cf->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {
						Debug::text( 'Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
						$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'company', 'edit_own', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( 'Found permission group with over_time_policy, add enabled: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'legal_entity' => [
													'enabled' => true,
													'view'    => true,
													'add'     => true,
													'edit'    => true,
													'delete'  => true,
											],
									]
							);
						} else {
							Debug::text( 'Permission group does NOT have company, edit_own enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'company_tax_deduction', 'edit', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( 'Found permission group with over_time_policy, add enabled: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'payroll_remittance_agency' => [
													'enabled' => true,
													'view'    => true,
													'add'     => true,
													'edit'    => true,
													'delete'  => true,
											],
									]
							);
						} else {
							Debug::text( 'Permission group does NOT have company_tax_deduction, edit enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'company', 'edit_own_bank', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( ' Found company->edit_own_bank. Adding remittance_source_account permissions: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'remittance_source_account' => [ 'enabled' => true, 'add' => true, 'view' => true, 'edit' => true, 'delete' => true ],
									]
							);
						} else {
							Debug::text( ' Permission group does NOT have company, edit_own_bank enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'user', 'edit_own_bank', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Found user->edit_own_bank. Adding remittance_destination_account permissions: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'remittance_destination_account' => [ 'enabled' => true, 'add' => true, 'view_own' => true, 'edit_own' => true, 'delete_own' => true ],
									]
							);
						} else {
							Debug::text( '  Permission group does NOT have user, edit_own_bank enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'user', [ 'edit_child_bank' ], 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Found user->edit_child_bank. Adding remittance_destination_account permissions: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'remittance_destination_account' => [ 'enabled' => true, 'view_child' => true, 'add' => true, 'edit_child' => true, 'delete_child' => true ],
									]
							);
						} else {
							Debug::text( ' Permission group does NOT have user->edit_child_bank enabled...', __FILE__, __LINE__, __METHOD__, 9 );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $cf->getId(), $pc_obj->getId(), 'user', [ 'edit_bank' ], 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Found permission group with user->edit_bank adding remitance_destination_account permissions: ' . $plf->getCurrent()->getValue() . ' Permission Group ID: ' . $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission(
									[
											'remittance_destination_account' => [ 'enabled' => true, 'view' => true, 'add' => true, 'edit' => true, 'delete' => true ],
									]
							);
						} else {
							Debug::text( '  Permission group does NOT have user->edit_bank enabled... Permission Group ID: ' . $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 9 );
						}
					}
				}
				unset( $pclf, $plf, $pc_obj );

				/***
				 * Migrate old objects to new objects using new schema.
				 */


				$lef = TTnew( 'LegalEntityFactory' ); /** @var LegalEntityFactory $lef */
				$lef->setCompany( $cf->getId() );
				$lef->setStatus( 10 ); //set status to active.
				$lef->setType( 10 );   //defaulting to corporation.
				$lef->setLegalName( $cf->getname() );
				$lef->setTradeName( $cf->getname() );
				$lef->setAddress1( $cf->getAddress1() );
				$lef->setAddress2( $cf->getAddress2() );
				$lef->setCountry( $cf->getCountry() );
				$lef->setCity( $cf->getCity() );
				$lef->setProvince( $cf->getProvince() );
				$lef->setPostalCode( $cf->getPostalCode() );
				$lef->setWorkPhone( $cf->getWorkPhone() );
				$lef->setFaxPhone( $cf->getFaxPhone() );
				$lef->setEnableAddRemittanceSource( false ); //Don't create default remittance source accounts as they will be created below instead.
				if ( $lef->isValid() ) {
					$legal_entity_id = $lef->save();
					Debug::Text( '  Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( '  ERROR: Invalid legal entity.', __FILE__, __LINE__, __METHOD__, 10 );

					//Don't continue with installation if this fails for any company.
					$clf->FailTransaction();

					return false;
				}

				if ( isset( $legal_entity_id ) && $legal_entity_id != '' ) {
					//Update user_default values to use the new legal entity.
					$sql = 'UPDATE user_default set legal_entity_id = ' . $legal_entity_id . ' WHERE company_id = ' . $cf->getId(); //Update even terminated/inactive/deleted users
					$this->getDatabaseConnection()->Execute( $sql );

					//Assign all users to new legal entity.
					$sql = 'UPDATE users set legal_entity_id = ' . $legal_entity_id . ' WHERE company_id = ' . $cf->getId();        //Update even terminated/inactive/deleted users
					$this->getDatabaseConnection()->Execute( $sql );


					$currency_to_eft_source_account_map = [];
					$currency_to_check_source_account_map = [];
					$currency_lf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $currency_lf */
					$currency_lf->getByCompanyId( $cf->getId() );
					Debug::Text( '  Get all currencies. Found: ' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $currency_lf->getRecordCount() > 0 ) {
						$query = 'SELECT * FROM bank_account WHERE company_id = ' . (int)$cf->getId() . ' AND ( user_id != 0 AND user_id IS NOT NULL ) AND deleted = 0'; //DO NOT CAST! This is before we switch to UUID.
						$user_bank_account_rs = $this->getDatabaseConnection()->Execute( $query );
						Debug::Text( '    User Bank Total Records: ' . $user_bank_account_rs->RecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

						foreach ( $currency_lf as $currency_obj ) {
							Debug::Text( '     Currency: ' . $currency_obj->getISOCode() . ' ID: ' . $currency_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							if ( $currency_obj->getDefault() == true ) {
								$query = 'SELECT * FROM bank_account WHERE company_id = ' . (int)$cf->getId() . ' AND ( user_id = 0 OR user_id IS NULL ) AND deleted = 0'; //DO NOT CAST! This is before we switch to UUID.
								$rs = $this->getDatabaseConnection()->Execute( $query );

								Debug::Text( '    Get company bank accounts. Found: ' . $rs->RecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
								if ( $rs->RecordCount() > 0 ) {
									foreach ( $rs as $bank_row ) {
										$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */

										Debug::Text( '    Processing Bank Account. institution: ' . $bank_row['institution'] . ' transit: ' . $bank_row['transit'] . ' ID: ' . $bank_row['id'], __FILE__, __LINE__, __METHOD__, 10 );

										$rsaf->setLegalEntity( $legal_entity_id );
										$rsaf->setType( 3000 ); //default to EFT
										$rsaf->setStatus( 10 ); //default to active
										$rsaf->setCountry( $cf->getCountry() );
										$rsaf->setName( 'Checking (' . $currency_obj->getISOCode() . ') [EFT/ACH]' );
										$rsaf->setCurrency( $currency_obj->getId() );
										$rsaf->setValue1( $bank_row['institution'] ); //Institution
										$rsaf->setValue2( $bank_row['transit'] );     //Routing
										$rsaf->setValue3( $bank_row['account'] );     //Account
										$rsaf->setValue4( $cf->getBusinessNumber() ); //Business Number

										Debug::Text( 'OriginatorID: ' . $cf->getOriginatorID() . ' DataCenterID: ' . $cf->getDataCenterID(), __FILE__, __LINE__, __METHOD__, 10 );
										if ( $cf->getOriginatorID() != '' ) {
											$rsaf->setValue5( $cf->getOriginatorID() ); //Origin
										}

										$rsaf->setValue6( $cf->getShortName() ); //Originator short name

										if ( $cf->getDataCenterID() != '' ) {
											$rsaf->setValue7( $cf->getDataCenterID() ); //Destination/data center
										}

										if ( $cf->getOtherID4() != '' ) {
											$rsaf->setValue8( $cf->getOtherID4() ); //Data center name
										}

										if ( $cf->getOtherID5() != '' ) {
											$rsaf->setValue9( $cf->getOtherID5() ); //Trace Number
										}

										$is_balanced = CompanySettingFactory::getCompanySettingValueByName( $cf->getId(), 'pay_stub.eft.balance_ach' );
										if ( $is_balanced == true ) {
											Debug::Text( '  Enabling OFFSET transaction...', __FILE__, __LINE__, __METHOD__, 10 );
											$rsaf->setValue24( 1 ); //Enable OFFSET transaction.
										}
										unset( $is_balanced );

										$rsaf->setLastTransactionNumber( $this->getLastTransactionNumber( $cf->getId() ) );
										Debug::Text( 'Last transaction Number: ' . $rsaf->getLastTransactionNumber(), __FILE__, __LINE__, __METHOD__, 10 );
										//Debug::Arr( $rsaf->data, '    New Remittance Source Account: ', __FILE__, __LINE__, __METHOD__, 10 );

										unset ( $ugdlf, $ugd_obj, $setup_data );

										//10=US, 20=CA
										$data_format_id = 10;
										if ( strtoupper( $cf->getCountry() ) == 'CA' ) {
											$data_format_id = 20;
										}

										$rsaf->setDataFormat( $data_format_id );

										if ( $rsaf->isValid() ) {
											$remittance_source_account_id = $rsaf->save();
											$currency_to_eft_source_account_map[$currency_obj->getId()] = $remittance_source_account_id;
											Debug::Text( '    New remittance EFT source account saved: ' . $remittance_source_account_id, __FILE__, __LINE__, __METHOD__, 10 );
										} else {
											$create_checking_source_account = true;
											Debug::Arr( $rsaf->data, '    ERROR: Invalid EFT source account. Creating checking account instead...', __FILE__, __LINE__, __METHOD__, 10 );
										}

										unset( $rsaf );
										break; //Only process first company bank account;
									}
								}
								unset( $rs );
							}

							//Always create a CHECK type source account so employees without bank accounts can use it below.
							$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */
							$rsaf->setType( 2000 ); //checking
							$rsaf->setStatus( 10 ); //default to active
							$rsaf->setLegalEntity( $legal_entity_id );
							$rsaf->setCurrency( $currency_obj->getId() );
							$rsaf->setCountry( $cf->getCountry() );

							$rsaf->setName( 'Checking (' . $currency_obj->getISOCode() . ')' );

							$rsaf->setLastTransactionNumber( 0 );
							//$rsaf->setValue9( $this->getLastTransactionNumber($cf->getId()) );

							$data_format_id = 10;
							if ( strtoupper( $cf->getCountry() ) == 'CA' ) {
								$data_format_id = 20;
							}

							$rsaf->setDataFormat( $data_format_id );

							//Debug::Arr( $rsaf->data, '    New Remittance Source Account: ', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $rsaf->isValid() ) {
								$remittance_source_account_id = $rsaf->save();
								$currency_to_check_source_account_map[$currency_obj->getId()] = $remittance_source_account_id;
								Debug::Text( '  New remittance source CHK account saved: ' . $remittance_source_account_id, __FILE__, __LINE__, __METHOD__, 10 );
							} else {
								Debug::Arr( $rsaf->data, '  ERROR: Invalid source CHK account. ', __FILE__, __LINE__, __METHOD__, 10 );
							}

							unset( $rsaf );
						}
					} else {
						Debug::Text( '    WARNING: no currencies for company: ' . $cf->getName(), __FILE__, __LINE__, __METHOD__, 10 );
					}

					Debug::Arr( $currency_to_eft_source_account_map, '    EFT Currency map.', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Arr( $currency_to_check_source_account_map, '    Check Currency map.', __FILE__, __LINE__, __METHOD__, 10 );
					if ( isset( $user_bank_account_rs ) ) {
						Debug::Text( '  User bank accounts: ' . $user_bank_account_rs->RecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $user_bank_account_rs->RecordCount() > 0 ) {
							foreach ( $user_bank_account_rs as $bank_row ) {

								$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
								$ulf->getById( $bank_row['user_id'] );
								if ( $ulf->getRecordCount() === 1 ) {
									$user_obj = $ulf->getCurrent();

									if ( isset( $currency_to_eft_source_account_map[$user_obj->getCurrencyObject()->getId()] ) ) {
										$account_name = 'Default';
										if ( strtoupper( $cf->getCountry() ) == 'US' ) {
											if ( $bank_row['institution'] == 32 ) {
												$account_name = 'Savings';
											} else {
												$account_name = 'Checking';
											}
										}

										$rdaf = TTnew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $rdaf */
										$rdaf->setRemittanceSourceAccount( $currency_to_eft_source_account_map[$user_obj->getCurrencyObject()->getId()] );
										$rdaf->setUser( $bank_row['user_id'] );                                 // DO NOT CAST! This is before we switch to UUID.
										$rdaf->setStatus( 10 );                                                 //default to enabled.
										$rdaf->setType( $rdaf->getRemittanceSourceAccountObject()->getType() ); //default to EFT
										$rdaf->setName( $account_name );                                        //!!!!  BASED ON THE ACCOUNT TYPE
										//$rdaf->setCurrency( $user_obj->getCurrency() );//from employee record - Not used in destination accounts currently.
										$rdaf->setPriority( 5 );                                                //default to 5
										$rdaf->setAmountType( 10 );                                             //10 = percent, 20 = fixed amount
										$rdaf->setPercentAmount( 100 );                                         //default to 100 percent
										$rdaf->setValue1( $bank_row['institution'] );                           //routing
										$rdaf->setValue2( $bank_row['transit'] );                               //routing
										$rdaf->setValue3( $bank_row['account'] );                               //account

										//Debug::Arr( $rdaf->data, '    New Remittance Destination Account: ', __FILE__, __LINE__, __METHOD__, 10 );

										if ( $rdaf->isValid() ) {
											$rd_id = $rdaf->save();
											Debug::Text( '    New remittance destination account: ' . $rd_id, __FILE__, __LINE__, __METHOD__, 10 );
										} else {
											Debug::Arr( $rdaf->data, '    ERROR: invalid remittance destination account.', __FILE__, __LINE__, __METHOD__, 10 );
										}
										unset( $rdaf );
									} else {
										Debug::Text( '    User Currency not in currency to EFT source account map. User ID: ' . $bank_row['id'] . ' Currency ID: ' . $user_obj->getCurrencyObject()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}
					} else {
						Debug::Text( '    WARNING: no user_bank_accounts for company: ' . $cf->getName(), __FILE__, __LINE__, __METHOD__, 10 );
					}

					//For users with no bank accounts, create a default check remittance destination so we can generate paystubs for them
					$sql = 'SELECT uf.* 
							FROM users as uf 
							WHERE uf.company_id = ' . (int)$cf->getId() . '
								AND NOT EXISTS ( SELECT rda.user_id FROM remittance_destination_account as rda WHERE uf.id = rda.user_id AND rda.deleted = 0 )
								AND ( uf.deleted = 0 OR uf.deleted IS NULL )';
					$rs = $this->getDatabaseConnection()->Execute( $sql );
					foreach ( $rs as $user_row ) {
						if ( isset( $currency_to_check_source_account_map[$user_row['currency_id']] ) ) {
							$rdaf = TTnew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $rdaf */
							$rdaf->setRemittanceSourceAccount( $currency_to_check_source_account_map[$user_row['currency_id']] );
							$rdaf->setUser( $user_row['id'] ); // DO NOT CAST! This is before we switch to UUID.
							$rdaf->setStatus( 10 );            //default to enabled.
							$rdaf->setType( 2000 );            //default to Checking
							$rdaf->setName( 'Checking' );      //!!!!  BASED ON THE ACCOUNT TYPE
							//$rdaf->setCurrency( $user_obj->getCurrency() );//from employee record - Not used in destination accounts currently.
							$rdaf->setPriority( 5 );           //default to 5
							$rdaf->setAmountType( 10 );        //10 = percent, 20 = fixed amount
							$rdaf->setPercentAmount( 100 );    //default to 100 percent

							//Debug::Arr( $rdaf->data, '    New Remittance Destination Account: ', __FILE__, __LINE__, __METHOD__, 10 );

							if ( $rdaf->isValid() ) {
								$rd_id = $rdaf->save();
								Debug::Text( '    New remittance destination account: ' . $rd_id, __FILE__, __LINE__, __METHOD__, 10 );
							} else {
								Debug::Arr( $rdaf->data, '    ERROR: invalid remittance destination account.', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $uf, $rdaf );
						} else {
							Debug::Text( '    User Currency not in currency to CHECK source account map. User ID: ' . $user_row['id'] . ' Currency ID: ' . $user_row['currency_id'], __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
					unset( $currency_to_eft_source_account_map, $currency_to_check_source_account_map, $user_bank_account_rs, $rdaf, $sql, $rs, $user_row );


					//Pick a oldest user from the highest permission level to use as the payroll contact, as we really have no guaranteed way to know who to use otherwise.
					$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
					$pclf->getByCompanyId( $cf->getId(), null, null, null, [ 'level' => 'desc' ] ); //Force order to prevent references to columns that haven't been created yet.
					if ( $pclf->getRecordCount() > 0 ) {
						Debug::Text( '  Found Permission Control records: ' . $pclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $pclf as $pc_obj ) {
							$permission_control_user_ids = (array)$pclf->getCurrent()->getUser();
							sort( $permission_control_user_ids ); //Use the oldest (lowest ID) user as a best guess.
							if ( is_array( $permission_control_user_ids ) && count( $permission_control_user_ids ) > 0 ) {
								foreach ( $permission_control_user_ids as $admin_user_id ) {
									Debug::Text( '  Permission Control User ID: ' . $admin_user_id . ' Company ID: ' . $cf->getId(), __FILE__, __LINE__, __METHOD__, 10 );
									if ( $admin_user_id > 0 ) { //Not a UUID yet.
										Debug::Text( '    Admin User ID: ' . $admin_user_id . ' Company ID: ' . $cf->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										break 2;
									}
								}
							}
						}
					}
					unset( $pclf, $pc_obj, $permission_control_user_ids );

					//Create default remittance agencies.
					$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
					$sp->setCompany( $cf->getID() );
					$sp->setUser( $admin_user_id );

					//Go through all CompanyDeductions and match them to a remittance agency. Then use that list of extract out country/province pairs for SetupPresets.
					//  Do it this way instead of using country/province fields from the CompanyDeductions, as things like UI don't them specified, and for states without income tax, they won't be created from that either.
					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyId( $cf->getId(), null, [ 'calculation_id' => 'desc', 'country' => 'asc', 'province' => 'asc' ] );
					if ( $cdlf->getRecordCount() ) {
						$ra_obj = TTnew( 'PayrollRemittanceAgencyFactory' ); /** @var PayrollRemittanceAgencyFactory $ra_obj */
						$processed_agency_countries = [];
						$processed_agency_provinces = [];
						foreach ( $cdlf as $cd_obj ) {
							$agency_id = $cd_obj->getPayrollRemittanceAgencyIdByNameOrCalculation();
							if ( $agency_id != '' ) {
								$agency_data = $ra_obj->parseAgencyID( $agency_id );
								if ( is_array( $agency_data ) && isset( $agency_data['country'] ) ) {
									Debug::Text( '    Creating Payroll Remittance Agencies for Country: ' . $agency_data['country'] . ' Province: ' . $agency_data['province'], __FILE__, __LINE__, __METHOD__, 10 );
									//Make sure we don't call the country presets more than once for the same country to avoid validation failures.
									if ( $agency_data['country'] != '' && !in_array( $agency_data['country'], $processed_agency_countries ) ) {
										$sp->PayrollRemittanceAgencys( $agency_data['country'] );
										$processed_agency_countries[] = $agency_data['country'];
									}

									//Because Canada doesn't have Province specific remittance agencies other than WCB, we need to figure out which provinces to apply the presets for based on the company and add that WCB agency.
									if ( strtoupper( $agency_data['country'] ) == 'CA' && $agency_data['province'] == '00' && is_object( $cd_obj->getCompanyObject() ) ) {
										$agency_data['province'] = strtoupper( $cd_obj->getCompanyObject()->getProvince() );
									}

									if ( $agency_data['country'] != '' && $agency_data['province'] != '' && $agency_data['province'] != '00' && !in_array( $agency_data['province'], $processed_agency_provinces ) ) {
										$sp->PayrollRemittanceAgencys( $agency_data['country'], $agency_data['province'] );
										$processed_agency_provinces[] = $agency_data['province'];
									}
								}
							} else {
								Debug::Text( '  Company Deduction cannot be mapped to agency: ' . $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
					unset( $ra_obj, $cdlf, $cd_obj, $processed_agency_countries, $processed_agency_provinces, $agency_id, $agency_data );

					//Assign Tax/Deductions to above created legal entity and remittance agencies.
					//  This should happen after the remittance source accounts are created, so they can be used by the agencies.
					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyId( $cf->getId() );
					if ( $cdlf->getRecordCount() ) {
						foreach ( $cdlf as $cd_obj ) {
							$cd_obj->setLegalEntity( $legal_entity_id );
							$cd_obj->setPayrollRemittanceAgency( $sp->getPayrollRemittanceAgencyByLegalEntityAndAgencyID( $legal_entity_id, $cd_obj->getPayrollRemittanceAgencyIdByNameOrCalculation() ) );
							if ( $cd_obj->isValid() ) {
								$cd_obj->Save();
							} else {
								Debug::Text( '  ERROR: Unable to assign Tax/Deduction to legal entity or agency...', __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
					unset( $sp, $cdlf, $cd_obj );


					//Loop over remittance agencies and add the Business Number from the Company record into the agency record for just federal agencies.
					if ( $cf->getBusinessNumber() != '' ) {
						Debug::Text( '  Company business number is specified, try copying it to remittance agencies...', __FILE__, __LINE__, __METHOD__, 10 );
						$pralf = TTNew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
						$pralf->getByCompanyId( $cf->getId() );
						if ( $pralf->getRecordCount() > 0 ) {
							foreach ( $pralf as $pra_obj ) {
								Debug::Text( '  Agency ID: ' . $pra_obj->getAgency() . ' Business Number: ' . $cf->getBusinessNumber(), __FILE__, __LINE__, __METHOD__, 10 );
								switch ( $pra_obj->getAgency() ) {
									case '10:CA:00:00:0010':
									case '10:US:00:00:0010':
									case '10:US:00:00:0020':
										Debug::Text( '    Copying business number to agency...', __FILE__, __LINE__, __METHOD__, 10 );
										$pra_obj->setPrimaryIdentification( $cf->getBusinessNumber() );
										break;
									default:
										Debug::Text( '    NOT copying business number for this agency...', __FILE__, __LINE__, __METHOD__, 10 );
										break;
								}

								if ( $pra_obj->isValid() ) {
									$pra_obj->Save();
								}
							}
						}
						unset( $pralf, $pra_obj );
					} else {
						Debug::Text( '  Company business number is not specified, unable to copy it to remittance agencies...', __FILE__, __LINE__, __METHOD__, 10 );
					}

					unset( $legal_entity_id, $lef ); //Unset at end of foreach loop.
				}
			}
		}

		$cjf = TTnew( 'CronJobFactory' ); /** @var CronJobFactory $cjf */
		$cjf->setName( 'RemittanceAgencyEvent' );
		$cjf->setMinute( 0 ); //Random time once a day for load balancing
		$cjf->setHour( '*' ); //Random time once a day for load balancing
		$cjf->setDayOfMonth( '*' );
		$cjf->setMonth( '*' );
		$cjf->setDayOfWeek( '*' );
		$cjf->setCommand( 'RemittanceAgencyEvent.php' );
		$cjf->Save();

		//$clf->FailTransaction(); return FALSE; //FOR DEBUG DO NOT COMMIT THIS UNLESS IT IS COMMENTED OUT!!!!
		$clf->CommitTransaction();
		unset( $clf );

		return true;
	}
}

?>
