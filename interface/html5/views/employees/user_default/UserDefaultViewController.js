class UserDefaultViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			company_api: null,
			user_preference_api: null,

			country_array: null,
			province_array: null,

			e_province_array: null,
			language_array: null,
			date_format_array: null,
			time_format_array: null,
			time_unit_format_array: null,
			distance_format_array: null,
			time_zone_array: null,
			start_week_day_array: null
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );

		this.permission_id = 'user';
		this.viewId = 'UserDefault';
		this.script_name = 'UserDefaultView';
		this.table_name_key = 'user_default';
		this.context_menu_name = $.i18n._( 'New Hire Defaults' );
		this.api = TTAPI.APIUserDefault;
		this.company_api = TTAPI.APICompany;
		this.user_preference_api = TTAPI.APIUserPreference;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	render() {
		super.render();
	}

	initOptions( callBack ) {

		var options = [
			{ option_name: 'language', field_name: 'language', api: this.user_preference_api },
			{ option_name: 'date_format', field_name: 'date_format', api: this.user_preference_api },
			{ option_name: 'time_format', field_name: 'time_format', api: this.user_preference_api },
			{ option_name: 'time_unit_format', field_name: 'time_unit_format', api: this.user_preference_api },
			{ option_name: 'distance_format', field_name: 'distance_format', api: this.user_preference_api },
			{ option_name: 'time_zone', field_name: 'time_zone', api: this.user_preference_api },
			{ option_name: 'start_week_day', field_name: 'start_week_day', api: this.user_preference_api },
			{ option_name: 'country', field_name: 'country', api: this.company_api }
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.save,
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	}

	getUserDefaultData( callBack ) {
		var $this = this;

		// First to get current company's user default data, if no have any data to get the default data which has been set up in TTAPI.APIUserDefault.

		$this.api['get' + $this.api.key_name]( {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( Global.isSet( result_data[0] ) ) {
					callBack( result_data[0] );
				} else {
					$this.api['get' + $this.api.key_name + 'DefaultData']( {
						onResult: function( result ) {
							var result_data = result.getResult();
							callBack( result_data );
						}
					} );
				}

			}
		} );
	}

	openEditView() {

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'UserDefault', 'UserDefaultEditView.html' );
				}

				$this.getUserDefaultData( function( result ) {
					// Waiting for the TTAPI.API returns data to set the current edit record.
					$this.current_edit_record = result;

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( 'UserTitle', 'UserTitleEditView.html' );
			}

		}
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {

			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setErrorMenu() {

		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.cancel:
					break;
				default:
					context_btn.addClass( 'disable-image' );
					break;
			}

		}
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {
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
	}

	buildEditViewUI() {
		var $this = this;
		super.buildEditViewUI();

		var tab_model = {
			'tab_employee_id': { 'label': $.i18n._( 'Employee Identification' ) },
			'tab_contact_info': { 'label': $.i18n._( 'Contact Information' ) },
			'tab_employee_preference': { 'label': $.i18n._( 'Employee Preferences' ) },
			'tab_email_info': { 'label': $.i18n._( 'Email Notifications' ) },
			'tab_tax_deduction': { 'label': $.i18n._( 'Employee Tax / Deductions' ) },

			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_employee_id = this.edit_view_tab.find( '#tab_employee_id' );

		var tab_employee_id_column1 = tab_employee_id.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_employee_id_column1 );

		//Legal Entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LEGAL_ENTITY,
			show_search_inputs: true,
//			set_empty: true,
			field: 'legal_entity_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_employee_id_column1 );

		//Permission Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPermissionControl,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			set_empty: true,
			show_search_inputs: true,
			field: 'permission_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Permission Group' ), form_item_input, tab_employee_id_column1, '' );

		//Terminated Permission Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPermissionControl,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			set_empty: true,
			show_search_inputs: true,
			field: 'terminated_permission_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Terminated Permission Group' ), form_item_input, tab_employee_id_column1, '' );

		// Pay Period Schedule
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayPeriodSchedule,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD_SCHEDULE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_period_schedule_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_employee_id_column1 );

		//Policy Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPolicyGroup,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.POLICY_GROUP,
			show_search_inputs: true,
			set_empty: true,
			field: 'policy_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy Group' ), form_item_input, tab_employee_id_column1 );

		//Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APICurrency,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			show_search_inputs: true,
			set_empty: true,
			field: 'currency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_employee_id_column1 );

		//Title

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIUserTitle,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.JOB_TITLE,
			show_search_inputs: true,
			set_empty: true,
			field: 'title_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Title' ), form_item_input, tab_employee_id_column1 );

		//Employee Number
		// form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		//
		// form_item_input.TTextInput( { field: 'employee_number', width: 149 } );
		// this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), form_item_input, tab_employee_id_column1 );

		//Hire Date
		// form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		//
		// form_item_input.TDatePicker( { field: 'hire_date' } );
		// this.addEditFieldToColumn( $.i18n._( 'Hire Date' ), form_item_input, tab_employee_id_column1 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'default_branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), form_item_input, tab_employee_id_column1 );

		//Default Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'default_department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Department' ), form_item_input, tab_employee_id_column1, '' );

		//Tab 1 start

		var tab_contact_info = this.edit_view_tab.find( '#tab_contact_info' );

		var tab_contact_info_column1 = tab_contact_info.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_contact_info_column1 );
		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_contact_info_column1, '' );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_contact_info_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province / State' ), form_item_input, tab_contact_info_column1 );

		//Work Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_contact_info_column1 );

		//Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone_ext' } );
		form_item_input.css( 'width', '50' );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_contact_info_column1 );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_email', width: 219 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_contact_info_column1, '' );

		//Tab 2 start

		var tab_employee_preference = this.edit_view_tab.find( '#tab_employee_preference' );

		var tab_employee_preference_column1 = tab_employee_preference.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_employee_preference_column1 );

		// Language
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'language', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.language_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_employee_preference_column1, '' );

		// Date Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'date_format', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.date_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Date Format' ), form_item_input, tab_employee_preference_column1 );

		// Time Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_format', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Format' ), form_item_input, tab_employee_preference_column1 );

		// Time Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_unit_format', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_unit_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Units' ), form_item_input, tab_employee_preference_column1 );

		// Distance Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'distance_format', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.distance_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Distance Units' ), form_item_input, tab_employee_preference_column1 );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_zone_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_employee_preference_column1 );

		// Start Weeks on
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'start_week_day' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.start_week_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Calendar Starts On' ), form_item_input, tab_employee_preference_column1 );

		// Rows per page
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'items_per_page', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Rows per page' ), form_item_input, tab_employee_preference_column1, '' );

		//Tab 3 start

		var tab_email_info = this.edit_view_tab.find( '#tab_email_info' );

		var tab_email_info_column1 = tab_email_info.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab_email_info_column1 );

		// Exceptions

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_email_notification_exception' } );
		this.addEditFieldToColumn( $.i18n._( 'Exceptions' ), form_item_input, tab_email_info_column1, '' );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_email_notification_message' } );
		this.addEditFieldToColumn( $.i18n._( 'Messages' ), form_item_input, tab_email_info_column1 );

		// Pay Stubs

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_email_notification_pay_stub' } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stubs' ), form_item_input, tab_email_info_column1 );

		// Send Notifications to Home Email

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_email_notification_home' } );
		this.addEditFieldToColumn( $.i18n._( 'Send Notifications to Home Email' ), form_item_input, tab_email_info_column1, '' );

		//Tab 4 start

		var tab_tax_deduction = this.edit_view_tab.find( '#tab_tax_deduction' );

		var tab_tax_deduction_column1 = tab_tax_deduction.find( '.first-column' );

		this.edit_view_tabs[4] = [];

		this.edit_view_tabs[4].push( tab_tax_deduction_column1 );

		// Tax / Deductions

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			field: 'company_deduction',
			layout_name: ALayoutIDs.COMPANY_DEDUCTION,
			api_class: TTAPI.APICompanyDeduction,
			allow_multiple_selection: true,
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Tax / Deductions' ), form_item_input, tab_tax_deduction_column1, 'first_last' );

	}

}
