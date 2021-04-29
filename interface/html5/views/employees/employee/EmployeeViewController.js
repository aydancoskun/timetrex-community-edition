EmployeeViewController = BaseViewController.extend( {
	el: '#employee_view_container', //Must set el here and can only set string, so events can work

	_required_files: {
		10: [
			'APIUserGroup', 'APIHierarchyControl', 'APIBranch', 'APIDepartment', 'APIUserTitle', 'APICompanyGenericTag', 'TImage', 'APILegalEntity',
			'APIPayPeriodSchedule', 'APIPolicyGroup', 'APICurrency', 'APIEthnicGroup', 'APIUserSkill', 'APIQualification', 'APIQualificationGroup',
			'APIUserEducation', 'APIUserMembership', 'APIUserLicense', 'APIUserLanguage', 'APIRemittanceDestinationAccount', 'APIUserDefault', 'APIRemittanceSourceAccount', 'APIAccrualPolicyUserModifier',
			'APIUserReviewControl', 'APIKPIGroup', 'APIUserReview', 'APIKPI', 'TImageAdvBrowser'
		],
		15: ['leaflet', 'leaflet-timetrex'],
		20: ['APIJob', 'APIJobItem']
	},

	user_api: null,
	user_group_api: null,
	company_api: null,
	hierarchyControlAPI: null,
	status_array: null,
	sex_array: null,
	user_group_array: null,
	country_array: null,
	province_array: null,
	e_province_array: null,

	sub_wage_view_controller: null,
	sub_user_contact_view_controller: null,
	sub_accrual_policy_user_modifier_view_controller: null,
	sub_log_view_controller: null,
	sub_company_tax_view_controller: null,
	sub_job_application_view_controller: null,
	sub_user_skill_view_controller: null,
	sub_user_education_view_controller: null,
	sub_user_membership_view_controller: null,
	sub_user_license_view_controller: null,
	sub_user_language_view_controller: null,
	sub_user_review_control_view_controller: null,

	sub_payment_methods_view_controller: null,

	hierarchy_options_dic: null,
	hierarchy_ui_model: null,
	show_hierarchy: false,
	select_company_id: null,

	sub_view_grid_autosize: true,

	init: function( options ) {
		//this._super( 'initialize', options );

		this.edit_view_tpl = 'EmployeeEditView.html';
		this.permission_id = 'user';
		this.viewId = 'Employee';
		this.script_name = 'EmployeeView';
		this.table_name_key = 'users';
		this.document_object_type_id = 100;
		this.context_menu_name = $.i18n._( 'Employees' );
		this.navigation_label = $.i18n._( 'Employee' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.select_company_id = LocalCacheData.getCurrentCompany().id;

		if ( ( Global.getProductEdition() >= 20 ) ) {

			this.job_api = new ( APIFactory.getAPIClass( 'APIJob' ) )();
			this.job_item_api = new ( APIFactory.getAPIClass( 'APIJobItem' ) )();

		}
		this.user_group_api = new ( APIFactory.getAPIClass( 'APIUserGroup' ) )();
		this.company_api = new ( APIFactory.getAPIClass( 'APICompany' ) )();
		this.hierarchyControlAPI = new ( APIFactory.getAPIClass( 'APIHierarchyControl' ) )();

		this.render();

		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	},

	jobUIValidate: function() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			( PermissionManager.validate( 'job', 'view' ) ||
				PermissionManager.validate( 'job', 'view_child' ) ||
				PermissionManager.validate( 'job', 'view_own' ) ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function() {

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
			( PermissionManager.validate( 'job_item', 'view' ) ||
				PermissionManager.validate( 'job_item', 'view_child' ) ||
				PermissionManager.validate( 'job_item', 'view_own' ) ) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
	initPermission: function() {
		this._super( 'initPermission' );

		if ( this.jobUIValidate() ) {
			this.show_job_ui = true;
		} else {
			this.show_job_ui = false;
		}

		if ( this.jobItemUIValidate() ) {
			this.show_job_item_ui = true;
		} else {
			this.show_job_item_ui = false;
		}
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			'exclude': [ContextMenuIconName.copy],
			'include': [
				{
					label: $.i18n._( 'TimeSheet' ),
					id: ContextMenuIconName.timesheet,
					group: 'navigation',
					icon: Icons.timesheet
				},

				{
					label: $.i18n._( 'Schedule' ),
					id: ContextMenuIconName.schedule,
					group: 'navigation',
					icon: Icons.schedule
				},

				{
					label: $.i18n._( 'Pay<br>Stubs' ),
					id: ContextMenuIconName.pay_stub,
					group: 'navigation',
					icon: Icons.pay_stubs
				},

				{
					label: $.i18n._( 'Pay Stub<br>Amendments' ),
					id: ContextMenuIconName.pay_stub_amendment,
					group: 'navigation',
					icon: Icons.pay_stub_amendment
				},
				{
					label: $.i18n._( 'Map' ),
					id: ContextMenuIconName.map,
					group: 'other',
					icon: Icons.map
				},

				{
					label: $.i18n._( 'Import' ),
					id: ContextMenuIconName.import_icon,
					group: 'other',
					icon: Icons.import_icon,
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVEmployee' ),
					sort_order: 8000
				}
			]
		};

		if ( this.edit_only_mode ) {
			context_menu_model.exclude.push(
				ContextMenuIconName.timesheet,
				ContextMenuIconName.schedule,
				ContextMenuIconName.pay_stub,
				ContextMenuIconName.pay_stub_amendment,
				ContextMenuIconName.map,
				ContextMenuIconName.import_icon,
				ContextMenuIconName.export_excel
			);
		}

		return context_menu_model;
	},

	openEditView: function( id ) {
		if ( this.edit_only_mode ) {
			var $this_obj = this;
			this.initOptions( function( result ) {
				if ( !$this_obj.edit_view ) {
					$this_obj.initEditViewUI( $this_obj.viewId, $this_obj.edit_view_tpl );
					var $this = this;
					doNext();
				} else {
					doNext();
				}

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( this.viewId, this.edit_view_tpl );
			}
		}

		function doNext() {
			$this_obj.getEmployeeData( id, function( result ) {
				//Error: Uncaught TypeError: Cannot read property 'user_id' of null in interface/html5/#!m=TimeSheet&date=20150915&user_id=42175&show_wage=0 line 79
				if ( !result ) {
					TAlertManager.showAlert( $.i18n._( 'Invalid employee id' ) );
					$this_obj.onCancelClick();
				} else {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this_obj.current_edit_record = result;
					$this_obj.initEditView();
				}

				if ( $this_obj.edit_only_mode ) {
					$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
				}
			} );
		}
	},

	setEditViewDataDone: function() {
		this.showCurrentPasswordField();
		this._super( 'setEditViewDataDone' );
	},

	showCurrentPasswordField: function() {
		//Only show this field if they are editing the currently logged in user record.
		if ( ( this.is_edit == true || this.edit_only_mode ) && LocalCacheData.loginUser && LocalCacheData.loginUser.id && this.current_edit_record && this.current_edit_record.id && LocalCacheData.loginUser.id == this.current_edit_record.id ) {
			this.attachElement( 'current_password' );
		} else {
			this.detachElement( 'current_password' );
		}
	},

	getEmployeeData: function( id, callBack ) {
		if ( typeof id === 'object' ) {

			id.id = '';
			id.company = LocalCacheData.current_company.name;

			callBack( id );
		} else {
			var filter = {};
			filter.filter_data = {};
			filter.filter_data.id = [id];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {
					var result_data = result.getResult();

					if ( !result_data ) {
						result_data = [];
					}
					result_data = result_data[0];

					callBack( result_data );

				}
			} );
		}

	},

	initOptions: function( callBack ) {

		var options = [
			{ option_name: 'status' },
			{ option_name: 'sex' },
			{ option_name: 'country', field_name: 'country', api: this.company_api }
		];

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );

	},

	initDropDownOptions: function( options, callBack ) {
		var $this = this;
		var len = options.length + 2;
		var complete_count = 0;
		var option_result = [];
		if ( this.hierarchyPermissionValidate() ) {

			$this.hierarchyControlAPI.getOptions( 'object_type', {
				onResult: function( res_1 ) {
					var data_1 = res_1.getResult();
					if ( data_1 ) {
						var array = [];

						for ( var key in data_1 ) {
							array.push( { id: Global.removeSortPrefix( key ), value: data_1[key] } );
						}

						$this.hierarchy_ui_model = array;
					}

					complete_count = complete_count + 1;
					if ( complete_count === len ) {
						callBack( option_result );
					}
				}
			} );

		} else {
			this.show_hierarchy = false;
			complete_count = complete_count + 1;
		}

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.edit_only_mode ) {
					if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
						$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
						$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
					}

				}

				$this.user_group_array = res;

				complete_count = complete_count + 1;

				if ( complete_count === len ) {

					callBack( option_result );
				}

			}
		} );

		for ( var i = 0; i < len - 2; i++ ) {
			var option_info = options[i];

			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result );
			}
		}
	},

	/* jshint ignore:start */
	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length, 'pay_stub' );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	/* jshint ignore:end */

	setDefaultMenuPayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	initSubQualificationView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		$( '#tab_qualifications .first-column-sub-view' ).css( 'opacity', '0' );
		TTPromise.add( 'Employee_Qualifications_Tab', 'initSubQualificationView' );

		TTPromise.add( 'Employee_Qualifications_Tab', 'UserSkillViewController' );
		Global.loadScript( 'views/hr/qualification/UserSkillViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.first-sub-view' );
			Global.trackView( 'Sub' + 'UserSkill' + 'View' );
			UserSkillViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserSkillView );

		} );

		TTPromise.add( 'Employee_Qualifications_Tab', 'UserEducationViewController' );
		Global.loadScript( 'views/hr/qualification/UserEducationViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.second-sub-view' );
			Global.trackView( 'Sub' + 'UserEducation' + 'View' );
			UserEducationViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserEducationView );

		} );

		TTPromise.add( 'Employee_Qualifications_Tab', 'UserMembershipViewController' );
		Global.loadScript( 'views/hr/qualification/UserMembershipViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.third-sub-view' );
			Global.trackView( 'Sub' + 'UserMembership' + 'View' );
			UserMembershipViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserMembershipView );

		} );

		TTPromise.add( 'Employee_Qualifications_Tab', 'UserLicenseViewController' );
		Global.loadScript( 'views/hr/qualification/UserLicenseViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.forth-sub-view' );
			Global.trackView( 'Sub' + 'UserLicense' + 'View' );
			UserLicenseViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserLicenseView );

		} );

		TTPromise.add( 'Employee_Qualifications_Tab', 'UserLanguageViewController' );
		Global.loadScript( 'views/hr/qualification/UserLanguageViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.fifth-sub-view' );
			Global.trackView( 'Sub' + 'UserLanguage' + 'View' );
			UserLanguageViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserLanguageView );

		} );

		function beforeLoadView() {

		}

		function afterLoadUserSkillView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Skills' ) );
			$this.sub_user_skill_view_controller = subViewController;
			$this.sub_user_skill_view_controller.parent_key = 'user_id';
			$this.sub_user_skill_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_skill_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_skill_view_controller.parent_view_controller = $this;
			$this.sub_user_skill_view_controller.postInit = function() {
				this.initData();
			};
		}

		function afterLoadUserLicenseView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Licenses' ) );
			$this.sub_user_license_view_controller = subViewController;
			$this.sub_user_license_view_controller.parent_key = 'user_id';
			$this.sub_user_license_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_license_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_license_view_controller.parent_view_controller = $this;
			$this.sub_user_license_view_controller.postInit = function() {
				this.initData();
			};
		}

		function afterLoadUserLanguageView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Languages' ) );
			$this.sub_user_language_view_controller = subViewController;
			$this.sub_user_language_view_controller.parent_key = 'user_id';
			$this.sub_user_language_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_language_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_language_view_controller.parent_view_controller = $this;
			$this.sub_user_language_view_controller.postInit = function() {
				this.initData();
			};
		}

		function afterLoadUserEducationView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Education' ) );
			$this.sub_user_education_view_controller = subViewController;
			$this.sub_user_education_view_controller.parent_key = 'user_id';
			$this.sub_user_education_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_education_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_education_view_controller.parent_view_controller = $this;
			$this.sub_user_education_view_controller.postInit = function() {
				this.initData();
			};
		}

		function afterLoadUserMembershipView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Memberships' ) );
			$this.sub_user_membership_view_controller = subViewController;
			$this.sub_user_membership_view_controller.parent_key = 'user_id';
			$this.sub_user_membership_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_membership_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_membership_view_controller.parent_view_controller = $this;
			$this.sub_user_membership_view_controller.postInit = function() {
				this.initData();
			};
		}

		TTPromise.wait( 'Employee_Qualifications_Tab', null, function() {
			$( '#contentContainer' ).trigger( 'resize' );
			$( '#tab_qualifications .first-column-sub-view' ).css( 'opacity', '1' );
		} );

		TTPromise.resolve( 'Employee_Qualifications_Tab', 'initSubQualificationView' );
	},

	initSubCompanyTaxView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'TaxView', 'init' );

		if ( this.sub_company_tax_view_controller ) {
			this.sub_company_tax_view_controller.buildContextMenu( true );
			this.sub_company_tax_view_controller.setDefaultMenu();
			$this.sub_company_tax_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_company_tax_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_company_tax_view_controller.initData();
			return;
		}

		Global.loadViewSource( 'CompanyTaxDeduction', 'CompanyTaxDeductionViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_employee = $this.edit_view_tab.find( '#tab_tax' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'UserContact' + 'View' );
			CompanyTaxDeductionViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_company_tax_view_controller = subViewController;
			$this.sub_company_tax_view_controller.parent_key = 'include_user_id';
			$this.sub_company_tax_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_company_tax_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_company_tax_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_company_tax_view_controller.initData();
			} );
		}
	},

	initSubUserReviewControlView: function() {
		;
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'ReviewView', 'init' );

		if ( this.sub_user_review_control_view_controller ) {
			this.sub_user_review_control_view_controller.buildContextMenu( true );
			this.sub_user_review_control_view_controller.setDefaultMenu();
			$this.sub_user_review_control_view_controller.parent_key = 'user_id';
			$this.sub_user_review_control_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_review_control_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_review_control_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/hr/kpi/UserReviewControlViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_employee = $this.edit_view_tab.find( '#tab_reviews' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'UserReviewControl' + 'View' );
			UserReviewControlViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_review_control_view_controller = subViewController;
			$this.sub_user_review_control_view_controller.parent_key = 'user_id';
			$this.sub_user_review_control_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_review_control_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_review_control_view_controller.parent_view_controller = $this;
			$this.sub_user_review_control_view_controller.initData();
		}

	},

	initSubPaymentMethodsView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'PaymentMethodsView', 'init' );

		if ( this.sub_payment_methods_view_controller ) {
			this.sub_payment_methods_view_controller.buildContextMenu( true );
			this.sub_payment_methods_view_controller.setDefaultMenu();
			$this.sub_payment_methods_view_controller.parent_key = 'user_id';
			$this.sub_payment_methods_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_payment_methods_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_payment_methods_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/employees/remittance_destination_account/RemittanceDestinationAccountViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_payment_methods = $this.edit_view_tab.find( '#tab_payment_methods' );
			var firstColumn = tab_payment_methods.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'RemittanceDestinationAccount' + 'View' );
			RemittanceDestinationAccountViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_payment_methods_view_controller = subViewController;
			$this.sub_payment_methods_view_controller.is_subview = true;
			$this.sub_payment_methods_view_controller.parent_key = 'user_id';
			$this.sub_payment_methods_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_payment_methods_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_payment_methods_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_payment_methods_view_controller.initData();
			} );
		}

	},

	initSubJobApplicationView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'JobAppView', 'init' );

		if ( this.sub_job_application_view_controller ) {
			this.sub_job_application_view_controller.buildContextMenu( true );
			this.sub_job_application_view_controller.setDefaultMenu();
			$this.sub_job_application_view_controller.parent_key = 'interviewer_user_id';
			$this.sub_job_application_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_job_application_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_job_application_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/hr/recruitment/JobApplicationViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_applications = $this.edit_view_tab.find( '#tab_applications' );
			var firstColumn = tab_applications.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'JobApplication' + 'View' );
			JobApplicationViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_job_application_view_controller = subViewController;
			$this.sub_job_application_view_controller.parent_key = 'interviewer_user_id';
			$this.sub_job_application_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_job_application_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_job_application_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_job_application_view_controller.initData();
			} );
		}
	},

	initSubUserContactView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'ContactView', 'init' );

		if ( this.sub_user_contact_view_controller ) {
			this.sub_user_contact_view_controller.buildContextMenu( true );
			this.sub_user_contact_view_controller.setDefaultMenu();
			$this.sub_user_contact_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_contact_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_contact_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/employees/user_contact/UserContactViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_employee = $this.edit_view_tab.find( '#tab_contacts' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'UserContact' + 'View' );
			UserContactViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_contact_view_controller = subViewController;
			$this.sub_user_contact_view_controller.parent_key = 'user_id';
			$this.sub_user_contact_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_contact_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_contact_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_user_contact_view_controller.initData();
			} );
		}
	},

	initSubWageView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'WageView', 'init' );

		if ( this.sub_wage_view_controller ) {
			this.sub_wage_view_controller.buildContextMenu( true );
			this.sub_wage_view_controller.setDefaultMenu();
			$this.sub_wage_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_wage_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_wage_view_controller.initData(); //Init data in this parent view
			TTPromise.resolve( 'WageView', 'init' );
			return;
		}

		Global.loadScript( 'views/company/wage/WageViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_employee = $this.edit_view_tab.find( '#tab_wage' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Wage' + 'View' );
			WageViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController, firstColumn ) {

			$this.sub_wage_view_controller = subViewController;
			$this.sub_wage_view_controller.parent_key = 'user_id';
			$this.sub_wage_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_wage_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_wage_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				//#2581 - $this.sub_wage_view_controller is null
				if ( $this.sub_wage_view_controller ) {
					$this.sub_wage_view_controller.initData(); //Init data in this parent view
				}
			} );
		}
	},

	initSubAccrualPolicyUserModifier: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'AccrualView', 'init' );

		if ( this.sub_accrual_policy_user_modifier_view_controller ) {
			this.sub_accrual_policy_user_modifier_view_controller.buildContextMenu( true );
			this.sub_accrual_policy_user_modifier_view_controller.setDefaultMenu();
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'user_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			return;
		}

		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );

			Global.loadScript( 'views/policy/accrual_policy/AccrualPolicyUserModifierViewController.js', function() {
				if ( !$this.edit_view_tab ) {
					return;
				}
				var tab_employee = $this.edit_view_tab.find( '#tab_accruals' );

				var firstColumn = tab_employee.find( '.first-column-sub-view' );

				Global.trackView( 'Sub' + 'AccrualPolicyUserModifier' + 'View' );
				AccrualPolicyUserModifierViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

			} );
		} else {
			this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}

		function beforeLoadView( tpl ) {
			var args = { parent_view: 'employee' };

			return { template: _.template( tpl ), args: args };
		}

		function afterLoadView( subViewController ) {
			$this.sub_accrual_policy_user_modifier_view_controller = subViewController;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'user_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_view_controller = $this;
			$this.sub_accrual_policy_user_modifier_view_controller.sub_view_mode = true;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			} );
		}
	},

	setDefaultUserName: function() {
		if ( this.is_add == true ) {
			var first_name_widget = this.edit_view_ui_dic['first_name'];
			var last_name_widget = this.edit_view_ui_dic['last_name'];

			var user_name_widget = this.edit_view_ui_dic['user_name'];
			user_name_widget.setValue( first_name_widget.getValue().toLowerCase() + '.' + last_name_widget.getValue().toLowerCase() );
			this.current_edit_record.user_name = user_name_widget.getValue();
		}
	},

	// setDefaultQuickPunch: function() {
	// 	if ( this.is_add == true ) {
	// 		var home_phone_widget = this.edit_view_ui_dic['home_phone'];
	// 		clean_home_phone = home_phone_widget.getValue().replace(/\D/g,'');
	//
	// 		var quick_punch_id_widget = this.edit_view_ui_dic['phone_id'];
	// 		quick_punch_id_widget.setValue( clean_home_phone.substring( ( clean_home_phone.length - 7 ) ) );
	// 		this.current_edit_record.phone_id = quick_punch_id_widget.getValue();
	//
	// 		var quick_punch_password_widget = this.edit_view_ui_dic['phone_password'];
	// 		quick_punch_password_widget.setValue( quick_punch_id_widget.getValue().substring( ( quick_punch_id_widget.getValue().length - 4 ) ) );
	// 		this.current_edit_record.phone_password = quick_punch_password_widget.getValue();
	// 	}
	// },

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		if ( !this.current_edit_record ) {
			return;
		}

		if ( parseInt( key ) > 0 ) {

			if ( !this.current_edit_record.hierarchy_control ) {
				this.current_edit_record.hierarchy_control = {};
			}

			this.current_edit_record.hierarchy_control[key] = target.getValue();
		} else {
			this.current_edit_record[key] = target.getValue();
		}

		switch ( key ) {
			case 'first_name':
				var widget = this.edit_view_ui_dic['first_name_1'];
				widget.setValue( target.getValue() );
				this.current_edit_record.first_name_1 = target.getValue();
				this.setDefaultUserName();
				break;
			case 'last_name':
				widget = this.edit_view_ui_dic['last_name_1'];
				widget.setValue( target.getValue() );
				this.current_edit_record.last_name_1 = target.getValue();
				this.setDefaultUserName();
				break;
			case 'first_name_1':
				widget = this.edit_view_ui_dic['first_name'];
				widget.setValue( target.getValue() );
				this.current_edit_record.first_name = target.getValue();
				this.setDefaultUserName();
				break;
			case 'last_name_1':
				widget = this.edit_view_ui_dic['last_name'];
				widget.setValue( target.getValue() );
				this.current_edit_record.last_name = target.getValue();
				this.setDefaultUserName();
				break;
			// case 'home_phone':
			// 	this.setDefaultQuickPunch();
			// 	break;
			case 'country':
				widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
			case 'default_job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'default_job_item_id', {
						status_id: 10,
						job_id: this.current_edit_record.default_job_id,
						company_id: this.select_company_id
					} );
					this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
				}
				break;
			case 'default_job_item_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, target.getValue(), 'default_job_id', 'default_job_item_id', {
						status_id: 10,
						company_id: $this.select_company_id
					} );

					//Don't validate immediately as onJobQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	hierarchyPermissionValidate: function( p_id, selected_item ) {

		if ( PermissionManager.validate( 'hierarchy', 'edit' ) ||
			PermissionManager.validate( 'user', 'edit_hierarchy' ) ) {

			return true;
		}

		return false;

	},

	checkTabPermissions: function( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_hierarchy':
				if ( this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_wage':
				if ( PermissionManager.checkTopLevelPermission( 'Wage' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_tax':
				if ( PermissionManager.checkTopLevelPermission( 'UserTaxDeduction' ) &&
					this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_payment_methods':
				if ( PermissionManager.checkTopLevelPermission( 'RemittanceDestinationAccount' ) &&
					this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_contacts':
				if ( PermissionManager.checkTopLevelPermission( 'UserContact' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_applications':
				if ( PermissionManager.subJobApplicationValidate( 'JobApplication' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_accruals':
				if ( PermissionManager.checkTopLevelPermission( 'AccrualPolicy' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_qualifications':
				if ( PermissionManager.checkTopLevelPermission( 'Qualification' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			case 'tab_reviews':
				if ( PermissionManager.checkTopLevelPermission( 'UserReviewControl' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			default:
				retval = this._super( 'checkTabPermissions', tab );
				break;
		}

		return retval;
	},

	/* jshint ignore:start */
	setCurrentEditRecordData: function() {
		var dont_set_dic = {};
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) || key === 'hierarchy_control' ) {
				switch ( key ) {
					case 'first_name':
					case 'last_name':
						dont_set_dic[key + '_1'] = true;
						var brother = this.edit_view_ui_dic[key + '_1'];
						brother.setValue( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'country':
						this.setCountryValue( widget, key );
						break;
					case 'hierarchy_control':
						if ( this.show_hierarchy ) {
							for ( var h_key in this.current_edit_record.hierarchy_control ) {
								var value = this.current_edit_record.hierarchy_control[h_key];
								if ( this.edit_view_ui_dic[h_key] ) {
									widget = this.edit_view_ui_dic[h_key];
									dont_set_dic[h_key] = true;
									widget.setValue( value );
								}
							}
						}
						break;
					case 'default_job_id':
						var args = {};
						args.filter_data = { status_id: 10, user_id: this.current_edit_record.id };
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'default_job_item_id':
						args = {};
						args.filter_data = { status_id: 10, job_id: this.current_edit_record.default_job_id };
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					default:
						if ( !dont_set_dic[key] ) {
							widget.setValue( this.current_edit_record[key] );
							break;
						}
						break;
				}

			}
		}

		if ( this.current_edit_record.id ) {
			if ( this.file_browser ) {
				this.file_browser.show();
				this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + this.current_edit_record.id );
				if ( this.is_viewing ) {
					this.file_browser.setEnable( false );
				} else {
					this.file_browser.setEnable( true );
				}
				$( '.upload-image-alert' ).remove();
			}
		} else {
			if ( this.file_browser ) {
				this.file_browser.hide();
				var span = $( '<span class="upload-image-alert">' );
				span.text( $.i18n._( 'Please save this record before uploading a photo' ) );
				this.file_browser.parent().append( span );
			}
		}

		// Error: TypeError: this.edit_view_ui_dic.company_id is undefined in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-162143 line 2 > eval line 1374
		this.edit_view_ui_dic['company_id'] && this.edit_view_ui_dic['company_id'].setEnabled( false );

		this.collectUIDataToCurrentEditRecord();
		this.setEmailIcon();

		this.setEditViewDataDone();

	},
	/* jshint ignore:end */

	setEmailIcon: function() {
		var $this = this;
		$( '.employee-email-icon' ).remove();
		var work_email = $( '<img title="' + $.i18n._( 'ReValidate Email Address' ) + '" class="employee-email-icon work-email" src="theme/default/images/email16x16.png">' );
		var home_email = $( '<img title="' + $.i18n._( 'ReValidate Email Address' ) + '" class="employee-email-icon home-email" src="theme/default/images/email16x16.png">' );
		if ( this.current_edit_record.hasOwnProperty( 'work_email_is_valid' ) && !this.current_edit_record.work_email_is_valid ) {
			this.edit_view_form_item_dic.work_email.children().eq( 1 ).append( work_email );
			work_email.on( 'click', function() {
				checkEmail( 'work_email' );
			} );
		} else {

		}

		if ( this.current_edit_record.hasOwnProperty( 'home_email_is_valid' ) && !this.current_edit_record.home_email_is_valid ) {
			this.edit_view_form_item_dic.home_email.children().eq( 1 ).append( home_email );
			home_email.on( 'click', function() {
				checkEmail( 'home_email' );
			} );
		}

		function checkEmail() {
			$this.api.sendValidationEmail( $this.current_edit_record.id, {
				onResult: function( result ) {
					if ( result.isValid() ) {
						TAlertManager.showAlert( $.i18n._( 'Validation email sent...' ) );
					} else {
						TAlertManager.showAlert( $.i18n._( 'No validation email sent...' ) );
					}
				}
			} );
		}
	},

	onSaveDone: function( result ) {
		if ( this.edit_only_mode && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
			LocalCacheData.current_open_primary_controller.updateSelectUserAndRefresh( this.current_edit_record );
		}
	},

	getCopyAsNewFilter: function( filter ) {
		// overriding BaseViewController for _continueDoCopyAsNew()
		filter.filter_data.company_id = this.select_company_id;
		return filter;
	},

	onAddClick: function() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		$this.openEditView();

		$this.api['get' + $this.api.key_name + 'DefaultData']( this.select_company_id, {
			onResult: function( result ) {
				$this.onAddResult( result );

			}
		} );

	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;
		filter.filter_data.company_id = this.select_company_id;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	getAPIFilters: function() {
		var filter = this._super( 'getAPIFilters' );
		filter.filter_data.company_id = this.select_company_id;

		return filter;
	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {

				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}

			}

		} else {
			filter.filter_page = 1;
		}

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}

		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {
			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );
		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		if ( this.select_layout && this.select_layout.data ) { //Fix: Uncaught TypeError: Cannot read property 'data' of null
			filter.filter_sort = this.select_layout.data.filter_sort;
		}

		if ( TTUUID.isUUID( this.refresh_id ) ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {
			this.last_select_ids = [];
			var ids = this.getGridSelectIdArray();
			//ensure detached reference to value source or lose this.last_select_ids when grid is cleared.
			for ( var i = 0; i < ids.length; i++ ) {
				this.last_select_ids.push( ids[i] );
			}
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();
				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );
				}
				if ( TTUUID.isUUID( $this.refresh_id ) ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.getData();
					var len = grid_source_data.length;

					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}

					var found = false;
					var new_record = result_data[0];

					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
						for ( var i = 0; i < len; i++ ) {
							var record = grid_source_data[i];

							//Fixed === issue. The id set by jQGrid is string type.
							// if ( !isNaN( parseInt( record.id ) ) ) {
							// 	record.id = parseInt( record.id );
							// }

							if ( record.id == new_record.id ) {
								$this.grid.grid.setRowData( new_record.id, new_record );
								found = true;
								break;
							}
						}

						if ( !found ) {
//							$this.grid.addRowData( new_record.id, new_record, 0 );
							$this.grid.setData( grid_source_data.concat( new_record ) );
							$this.highLightGridRowById( new_record.id );
						}
					}

				} else {
					//Set Page data to widget, next show display info when setDefault Menu
					$this.pager_data = result.getPagerData();

					//CLick to show more mode no need this step
					if ( LocalCacheData.paging_type !== 0 ) {
						$this.paging_widget.setPagerData( $this.pager_data );
						$this.paging_widget_2.setPagerData( $this.pager_data );
					}

					if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
						var current_data = $this.grid.getData();
						result_data = current_data.concat( result_data );
					}

					if ( $this.grid ) {
						$this.grid.setData( result_data );

						$this.reSelectLastSelectItems();
					}
				}

				$this.setGridCellBackGround(); //Set cell background for some views

				ProgressBar.closeOverlay(); //Add this in initData

				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				$this.searchDone();

			}
		} );

		//This seems to be the only difference from BaseViewController search() function.
		if ( filter && filter.filter_data && filter.filter_data.company_id ) {
			this.select_company_id = filter.filter_data.company_id;

			this.user_group_api.getUserGroup( { filter_data: { company_id: this.select_company_id } }, false, false, {
				onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );

					if ( !$this.edit_only_mode ) {
						if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
							$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
							$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
						}

					}

					$this.user_group_array = res;

				}
			} );

		} else {
			this.select_company_id = LocalCacheData.getCurrentCompany().id;
		}

	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.import_icon:
				this.onImportClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.pay_stub_amendment:
				this.onNavigationClick( id );
				break;
		}
	},

	onMapClick: function() {
		// only trigger map load in specific product editions.
		if ( ( Global.getProductEdition() >= 15 ) ) {
			this.is_viewing = false;
			ProgressBar.showProgressBar();
			var data = {
				filter_columns: {
					id: true,
					first_name: true,
					last_name: true,
					address1: true,
					address2: true,
					city: true,
					province: true,
					country: true,
					postal_code: true,
					latitude: true,
					longitude: true
				}
			};

			var cells = [];
			if ( this.is_edit ) {
				//when editing, if the user reloads, the grid's selected id array become the whole grid.
				//to avoid mapping every punch in that scenario we need to grab the current_edit_record, rather than pull data from getGridSelectIdArray()
				//check for mass edit as well. <-- not sure what this refers to, assuming the same happens in mass edit, but maps are disabled on mass edit atm.
				cells.push( this.current_edit_record );
			} else {
				var ids = this.getGridSelectIdArray();
				data.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				if ( ids.length > 0 ) {
					data.filter_data.id = ids;
				}
				cells = this.api.getUser( data, { async: false } ).getResult();
			}

			if ( !this.is_mass_editing ) {
				var processed_data_for_map = TTMapLib.TTConvertMapData.processBasicFromGenericViewController( cells );
				IndexViewController.openEditView( this, 'Map', processed_data_for_map );
			}
		}
	},

	onImportClick: function() {

		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'user', function() {
			$this.search();
		} );
	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var base_date = new Date().format();

		if ( $this.edit_view && $this.current_edit_record.id ) {
			user_ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				user_ids.push( grid_selected_row.id );
			} );
		}

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Employees' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = { value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Employees' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case ContextMenuIconName.pay_stub:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, $.i18n._( 'Employees' ), window.location.href );
					IndexViewController.goToView( 'PayStub', filter );
				}
				break;
			case ContextMenuIconName.pay_stub_amendment:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( this.viewId, $.i18n._( 'Employees' ), window.location.href );
					IndexViewController.goToView( 'PayStubAmendment', filter );
				}
				break;
		}

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_user_contact_view_controller = null;
		this.sub_wage_view_controller = null;
		this.sub_company_tax_view_controller = null;
		this.sub_accrual_policy_user_modifier_view_controller = null;
		this.sub_user_review_control_view_controller = null;
		this.sub_job_application_view_controller = null;
		this.sub_user_skill_view_controller = null;
		this.sub_user_education_view_controller = null;

		this.sub_user_membership_view_controller = null;

		this.sub_user_license_view_controller = null;

		this.sub_user_language_view_controller = null;

		this.sub_payment_methods_view_controller = null;
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_employee': { 'label': $.i18n._( 'Employee' ) },
			'tab_contact_info': { 'label': $.i18n._( 'Contact Info' ) },
			'tab_hierarchy': { 'label': $.i18n._( 'Hierarchy' ), 'display_on_mass_edit': false },
			'tab_login': { 'label': $.i18n._( 'Login' ) },
			'tab_wage': {
				'label': $.i18n._( 'Wage' ),
				'init_callback': 'initSubWageView',
				'display_on_mass_edit': false
			},
			'tab_tax': {
				'label': $.i18n._( 'Tax' ),
				'init_callback': 'initSubCompanyTaxView',
				'display_on_mass_edit': false
			},
			'tab_payment_methods': {
				'label': $.i18n._( 'Pay Methods' ),
				'init_callback': 'initSubPaymentMethodsView',
				'display_on_mass_edit': false
			},
			'tab_contacts': {
				'label': $.i18n._( 'Contacts' ),
				'init_callback': 'initSubUserContactView',
				'display_on_mass_edit': false
			},
			'tab_applications': {
				'label': $.i18n._( 'Applications' ),
				'init_callback': 'initSubJobApplicationView',
				'display_on_mass_edit': false
			},
			'tab_accruals': {
				'label': $.i18n._( 'Accruals' ),
				'init_callback': 'initSubAccrualPolicyUserModifier',
				'display_on_mass_edit': false
			},
			'tab_qualifications': {
				'label': $.i18n._( 'Qualifications' ),
				'init_callback': 'initSubQualificationView',
				'display_on_mass_edit': false
			},
			'tab_reviews': {
				'label': $.i18n._( 'Reviews' ),
				'init_callback': 'initSubUserReviewControlView',
				'display_on_mass_edit': false
			},
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				id: this.script_name + '_navigation',
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.USER,
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Employee Tab start
		var tab_employee = this.edit_view_tab.find( '#tab_employee' );
		var tab_employee_column1 = tab_employee.find( '.first-column' );
		var tab_employee_column2 = tab_employee.find( '.second-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_employee_column1 );
		this.edit_view_tabs[0].push( tab_employee_column2 );

		//Company
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APICompany' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.COMPANY,
			show_search_inputs: true,
			set_empty: true,
			field: 'company_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_employee_column1 );
		form_item_input.setEnabled( false );

		//Legal Entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APILegalEntity' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LEGAL_ENTITY,
			show_search_inputs: true,
			field: 'legal_entity_id',
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			set_empty: true,
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_employee_column1 );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_employee_column1 );

		//First Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'first_name', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_employee_column1, '' );

		//Last Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'last_name', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_employee_column1 );

		//Employee Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'employee_number', width: 90 } );
		this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), form_item_input, tab_employee_column1 );

		//Permission Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPermissionControl' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			show_search_inputs: true,
			field: 'permission_control_id',
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Permission Group' ), form_item_input, tab_employee_column1 );

		//Pay Period Schedule
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayPeriodSchedule' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD_SCHEDULE,
			show_search_inputs: true,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			set_empty: true,
			field: 'pay_period_schedule_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_employee_column1 );

		//Policy Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPolicyGroup' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.POLICY_GROUP,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			show_search_inputs: true,
			set_empty: true,
			field: 'policy_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy Group' ), form_item_input, tab_employee_column1 );

		//Title
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIUserTitle' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.JOB_TITLE,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			show_search_inputs: true,
			set_empty: true,
			field: 'title_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Title' ), form_item_input, tab_employee_column1, '' );

		//Second Column Start

		//Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APICurrency' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			show_search_inputs: true,
			field: 'currency_id',
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_employee_column2 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIBranch' ) ),
			allow_multiple_selection: false,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'default_branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), form_item_input, tab_employee_column2 );

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			show_search_inputs: true,
			set_empty: true,
			field: 'default_department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Department' ), form_item_input, tab_employee_column2 );

		this.initPermission(); //#2398 - job/task permissions were getting broken on multiple opens of same employee.
		if ( ( Global.getProductEdition() >= 20 ) ) {

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIJob' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				customSearchFilter: ( function( args ) {
					return $this.setCompanyIdFilter( args );
				} ),
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: ( function( val ) {

					if ( val ) {
						job_coder.setValue( val.manual_id );
					}
				} ),
				field: 'default_job_id'
			} );

			var default_job_description = $( '<div class=\'widget-h-box\'></div>' );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
			job_coder.addClass( 'job-coder' );

			default_job_description.append( job_coder );
			default_job_description.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Default Job' ), [form_item_input, job_coder], tab_employee_column2, '', default_job_description, true );

			if ( !this.show_job_ui ) {
				this.detachElement( 'default_job_id' );
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIJobItem' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				customSearchFilter: ( function( args ) {
					return $this.setCompanyIdFilter( args );
				} ),
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: ( function( val ) {
					if ( val ) {
						job_item_coder.setValue( val.manual_id );
					}
				} ),
				field: 'default_job_item_id'
			} );

			var default_task_description = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			default_task_description.append( job_item_coder );
			default_task_description.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Default Task' ), [form_item_input, job_item_coder], tab_employee_column2, '', default_task_description, true );

			if ( !this.show_job_item_ui ) {
				this.detachElement( 'default_job_item_id' );
			}
		}

		//Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_group_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Group' ), form_item_input, tab_employee_column2 );

		// Ethnicity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIEthnicGroup' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ETHNIC_GROUP,
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			show_search_inputs: true,
			field: 'ethnic_group_id',
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Ethnicity' ), form_item_input, tab_employee_column2 );

		//SIN
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'sin', width: 90 } );
		this.addEditFieldToColumn( $.i18n._( 'SIN / SSN' ), form_item_input, tab_employee_column2 );

		//Birth Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'birth_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Birth Date' ), form_item_input, tab_employee_column2 );

		//Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'hire_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Hire Date' ), form_item_input, tab_employee_column2 );

		//Termination Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'termination_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Termination Date' ), form_item_input, tab_employee_column2 );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );
		form_item_input.TTagInput( { field: 'tag', object_type_id: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_employee_column2, '', null, null, true );

		//Contact Tab start
		var tab_contact_info = this.edit_view_tab.find( '#tab_contact_info' );
		var tab_contact_info_column1 = tab_contact_info.find( '.first-column' );
		var tab_contact_info_column2 = tab_contact_info.find( '.second-column' );

		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_contact_info_column1 );
		this.edit_view_tabs[1].push( tab_contact_info_column2 );

		// Photo
		if ( typeof FormData == 'undefined' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );
			this.file_browser = form_item_input.TImageBrowser( {
				field: '',
				default_width: 128,
				default_height: 128,
				enable_delete: true
			} );

			this.file_browser.bind( 'imageChange', function( e, target ) {
				new ServiceCaller().uploadFile( target.getValue(), 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + $this.current_edit_record.id );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			} );

		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );
			this.file_browser = form_item_input.TImageAdvBrowser( {
				field: '',
				default_width: 128,
				default_height: 128,
				enable_delete: true,
				callBack: function( form_data ) {
					new ServiceCaller().uploadFile( form_data, 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
						onResult: function( result ) {

							if ( result.toLowerCase() === 'true' ) {
								$this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + $this.current_edit_record.id );
							} else {
								TAlertManager.showAlert( result, 'Error' );
							}
						}
					} );

				},
				deleteImageHandler: function( e ) {
					$this.onDeleteImage();
				}
			} );

		}

		if ( this.is_edit ) {
			this.file_browser.setEnableDelete( true );
			this.file_browser.bind( 'deleteClick', function( e, target ) {
				$this.api.deleteImage( $this.current_edit_record.id, {
					onResult: function( result ) {
						$this.onDeleteImage();
					}
				} );
			} );
		}

		this.addEditFieldToColumn( $.i18n._( 'Photo' ), this.file_browser, tab_contact_info_column1, '', null, false, true );

		//First Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'first_name_1', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_contact_info_column1 );

		//Middle Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'middle_name', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Middle Name' ), form_item_input, tab_contact_info_column1 );

		//Last Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'last_name_1', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_contact_info_column1 );

		//Home Address(Line 1)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address1', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Home Address (Line 1)' ), form_item_input, tab_contact_info_column1 );
		form_item_input.parent().width( '45%' );

		//Home Address(Line 2)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address2', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Home Address (Line 2)' ), form_item_input, tab_contact_info_column1 );
		form_item_input.parent().width( '45%' );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'city', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_contact_info_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_contact_info_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_contact_info_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'postal_code', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_contact_info_column1, '' );

		//Column 2

		//Gender
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'sex_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.sex_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Gender' ), form_item_input, tab_contact_info_column2 );

		//Work Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'work_phone', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_contact_info_column2, '' );

		//Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'work_phone_ext' } );
		form_item_input.css( 'width', '50' );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_contact_info_column2 );

		//Home Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'home_phone', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Home Phone' ), form_item_input, tab_contact_info_column2 );

		//Mobile Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'mobile_phone', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Mobile Phone' ), form_item_input, tab_contact_info_column2 );

		//Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'fax_phone', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_contact_info_column2 );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'work_email', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_contact_info_column2, '', null, true );

		//Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'home_email', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Home Email' ), form_item_input, tab_contact_info_column2, '', null, true );

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: '100%', rows: 4 } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_contact_info_column2, '', null, null, true );
		form_item_input.parent().width( '45%' );

		//Hierarchy Tab start
		var tab_hierarchy = this.edit_view_tab.find( '#tab_hierarchy' );
		var tab_hierarchy_column1 = tab_hierarchy.find( '.first-column' );

		this.edit_view_tabs[2] = [];
		this.edit_view_tabs[2].push( tab_hierarchy_column1 );

		if ( this.hierarchyPermissionValidate() ) {
			var res = this.hierarchyControlAPI.getHierarchyControlOptions( { async: false } );
			$this.hierarchy_options_dic = {};
			var data = res.getResult();
			for ( var key in data ) {
				if ( parseInt( key ) === 200 && Global.getProductEdition() != 25 ) {
					continue;
				}
				$this.hierarchy_options_dic[key] = Global.buildRecordArray( data[key] );
			}
			if ( _.size( $this.hierarchy_options_dic ) > 0 ) {
				$this.show_hierarchy = true;
			} else {
				$this.show_hierarchy = false;
			}
		}

		if ( this.show_hierarchy && this.hierarchy_ui_model ) {
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'none' );
			var len = this.hierarchy_ui_model.length;
			for ( var i = 0; i < len; i++ ) {
				var ui_model = this.hierarchy_ui_model[i];
				var options = this.hierarchy_options_dic[ui_model.id];
				if ( options ) {
					form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
					form_item_input.TComboBox( { field: ui_model.id } );
					form_item_input.setSourceData( options );
					this.addEditFieldToColumn( ui_model.value, form_item_input, tab_hierarchy_column1 );
				}
			}
		} else {
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).NoHierarchyBox( { related_view_controller: this } );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'block' );
		}

		//Login Tab start
		var tab_login = this.edit_view_tab.find( '#tab_login' );
		var tab_login_column1 = tab_login.find( '.first-column' );
		var tab_login_column2 = tab_login.find( '.second-column' );

		this.edit_view_tabs[3] = [];
		this.edit_view_tabs[3].push( tab_login_column1 );
		this.edit_view_tabs[3].push( tab_login_column2 );

		// Current Password - This is shown/hidden in setCurrentPasswordField()
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'current_password', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Current Password' ), form_item_input, tab_login_column1, '', null, true );

		//Login Enabled
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_login' } );
		this.addEditFieldToColumn( $.i18n._( 'Login Enabled' ), form_item_input, tab_login_column1 );

		//User Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'user_name', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, tab_login_column1 );

		//Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TTextInput( { field: 'password', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Change Password' ), form_item_input, tab_login_column1 );

		//Password Confirm
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TTextInput( { field: 'password_confirm', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Confirm Password' ), form_item_input, tab_login_column1 );

		//Quick Punch ID
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'phone_id', width: 90 } );
		var quick_punch_id_description = $( '<div class=\'widget-h-box\'></div>' );
		quick_punch_id_description.append( form_item_input );
		quick_punch_id_description.append( $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'Optional' ) + ' )</span>' ) );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch ID' ), form_item_input, tab_login_column2, '', quick_punch_id_description );

		//Quick Punch Password
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'phone_password', width: 90 } );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch Password' ), form_item_input, tab_login_column2 );

		//Login Expire Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'login_expire_date' } );
		var login_expire_date_description = $( '<div class=\'widget-h-box\'></div>' );
		login_expire_date_description.append( form_item_input );
		login_expire_date_description.append( $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'Leave blank to never expire' ) + ' )</span>' ) );
		this.addEditFieldToColumn( $.i18n._( 'Login Expire Date' ), form_item_input, tab_login_column2, '', login_expire_date_description );

		//Terminated Permission Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPermissionControl' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			show_search_inputs: true,
			field: 'terminated_permission_control_id',
			customSearchFilter: ( function( args ) {
				return $this.setCompanyIdFilter( args );
			} ),
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Terminated Permission Group' ), form_item_input, tab_login_column2 );

		TTPromise.resolve( 'employeeEditView', 'openEditView' );

	},

	initCountryList: function() {

	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	},

	eSetProvince: function( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );

		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}
					$this.e_province_array = Global.buildRecordArray( res );

					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}

					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
	},

	onSetSearchFilterFinished: function() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}

	},

	onBuildBasicUIFinished: function() {
		var basicSearchTabPanel = this.search_panel.find( 'div #basic_search' );
	},

	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	events: {},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var $this = this;
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Company' ),
				in_column: 1,
				field: 'company_id',
				layout_name: ALayoutIDs.COMPANY,
				api_class: ( APIFactory.getAPIClass( 'APICompany' ) ),
				multiple: false,
				custom_first_label: Global.default_item,
				basic_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				adv_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: ALayoutIDs.LEGAL_ENTITY,
				api_class: ( APIFactory.getAPIClass( 'APILegalEntity' ) ),
				multiple: true,
				custom_first_label: Global.any_item,
				basic_search: PermissionManager.checkTopLevelPermission( 'LegalEntity' ) ? true : false,
				adv_search: PermissionManager.checkTopLevelPermission( 'LegalEntity' ) ? true : false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'First Name' ),
				in_column: 1,
				field: 'first_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				field: 'last_name',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee Number' ),
				field: 'employee_number',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Phone' ),
				field: 'any_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Email' ),
				field: 'any_email',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 2,
				object_type_id: 200,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Gender' ),
				in_column: 2,
				field: 'sex_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: ( APIFactory.getAPIClass( 'APIBranch' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				customSearchFilter: ( function( args ) {
					return $this.setCompanyIdFilter( args );
				} ),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				customSearchFilter: ( function( args ) {
					return $this.setCompanyIdFilter( args );
				} ),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 3,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: ( APIFactory.getAPIClass( 'APIUserTitle' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				customSearchFilter: ( function( args ) {
					return $this.setCompanyIdFilter( args );
				} ),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 3,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 3,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: false,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'SIN/SSN' ),
				field: 'sin',
				basic_search: false,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} )
		];

	},

	setCompanyIdFilter: function( args ) {

		if ( !args ) {
			args = { filter_data: { company_id: this.select_company_id } };

		} else {
			if ( !args.filter_data ) {
				args.filter_data = { company_id: this.select_company_id };
			} else {
				args.filter_data.company_id = this.select_company_id;
			}
		}

		return args;

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#employee_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	},

	getFilterColumnsFromDisplayColumns: function( column_filter, enable_system_columns ) {
		if ( column_filter == undefined ) {
			column_filter = {};
		}
		column_filter.company_id = true;
		column_filter.latitude = true;
		column_filter.longitude = true;
		return this._getFilterColumnsFromDisplayColumns( column_filter, enable_system_columns );
	},

} );
