CompanyViewController = BaseViewController.extend( {

	_required_files: ['TImage', 'TImageAdvBrowser', 'APICompany'],

	product_edition_array: null,
	industry_array: null,
	country_array: null,
	province_array: null,
	e_province_array: null,
	terminated_user_disable_login_type_array: null,
	password_policy_type_array: null,
	password_minimum_permission_level_array: null,
	password_minimum_strength_array: null,
	ldap_authentication_type_array: null,

	file_browser: null,

	init: function( options ) {
		var $this = this;

		this.permission_id = 'company';
		this.viewId = 'Company';
		this.script_name = 'CompanyView';
		this.table_name_key = 'company';
		this.context_menu_name = $.i18n._( 'Company Information' );
		this.api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
	},

	getCustomContextMenuModel: function () {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.save,
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	},

	initOptions: function( callBack ) {

		var options = [
			{ option_name: 'product_edition' },
			{ option_name: 'terminated_user_disable_login_type' },
			{ option_name: 'industry' },
			{ option_name: 'country' },
			{ option_name: 'password_policy_type' },
			{ option_name: 'password_minimum_permission_level', field_name: 'password_minimum_permission_level' },
			{ option_name: 'password_minimum_strength', field_name: 'password_minimum_strength' },
			{ option_name: 'ldap_authentication_type' }
		];

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );

	},

	getCompanyData: function( callBack ) {
		var $this = this;

		// First to get current company's user default data, if no have any data to get the default data which has been set up in (APIFactory.getAPIClass( 'APIUserDefault.' ))
		var args = { filter_data: { id: LocalCacheData.getLoginUser().company_id } };

		$this.api['get' + $this.api.key_name]( args, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( Global.isSet( result_data[0] ) ) {
					callBack( result_data[0] );
				}

			}
		} );
	},

	openEditView: function() {

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'Company', 'CompanyEditView.html' );
				}

				$this.getCompanyData( function( result ) {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this.current_edit_record = result;

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( 'Company', 'CompanyEditView.html' );
			}
		}

		var new_url = window.location.href;
		if ( new_url.indexOf('company_id') == -1 ) {
			new_url = new_url + '&company_id=' + LocalCacheData.getLoginUser().company_id;
			Global.setURLToBrowser( new_url );
		}

	},

	removeCompanyIdFromUrl: function(){
		var new_url = window.location.href;
		if ( new_url.indexOf('company_id') != -1 ) {
			var parts = new_url.split('&');
			new_url = parts[0];
			for ( var i = 1; i < ( parts.length - 1 ) ; i++ ) {
			new_url += ('&' + parts[i]);
		}
			Global.setURLToBrowser( new_url );
		}
	},

	removeEditView: function(){
		this.removeCompanyIdFromUrl();
		this._super('removeEditView');

	},

	setCurrentEditRecordData: function() {
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.setCountryValue( widget, key );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.file_browser.setImage( ServiceCaller.companyLogo );

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onTypeChange();
	},

	initSubPasswordPolicyView: function() {
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_password_policy' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else {
			this.edit_view_tab.find( '#tab_password_policy' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	},

	setEditMenuSaveIcon: function( context_btn, pId ) {
		//#2542 - Always needs a save icon as this view is always in edit-only mode, ver in view mode
	},

	onFormItemChange: function( target, doNotValidate ) {

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

		if ( key === 'ldap_authentication_type_id' ) {

			this.onTypeChange();
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onSaveDone: function ( result ) {
		if ( result.isValid() && result.getResult() === true ) {
			this.updateCurrentCompanyCache();
			return true;
		}
		return false;
	},

	updateCurrentCompanyCache: function() {

		var authentication_api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
		authentication_api.getCurrentCompany( { onResult: this.onGetCurrentCompany } );
	},

	onGetCurrentCompany: function( e ) {

		var result = e.getResult();
		if ( result.is_setup_complete === '1' || result.is_setup_complete === 1 ) {
			result.is_setup_complete = true;
		} else {
			result.is_setup_complete = false;
		}

		LocalCacheData.setCurrentCompany( result );
	},

	updateCompanyLogo: function() {
		var d = new Date();
		$( '#rightLogo' ).css( 'opacity', 0 );
		$( '#rightLogo' ).attr( 'src', ServiceCaller.companyLogo + '&t=' + d.getTime() );

		$( '#rightLogo' ).on( 'load', function() {

			var ratio = 42 / $( this ).height();

			if ( $( this ).height() > 42 ) {
				$( this ).css( 'height', 42 );

				if ( $( this ).width > 177 ) {
					$( this ).css( 'width', 177 );
				}
			}

			if ( $( this ).width > 177 ) {
				$( this ).css( 'width', 177 );
			}

			$( this ).animate( {
				opacity: 1
			}, 100 );
		} );
	},

	setErrorMenu: function() {

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
	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];

		} else {

			this.api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );

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
			this.api.getOptions( 'province', val, {
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

	buildEditViewUI: function() {
		var $this = this;
		this._super( 'buildEditViewUI' );


		var tab_model = {
			'tab_company': { 'label': $.i18n._( 'Company' ) },
			'tab_password_policy': { 'label': $.i18n._( 'Password Policy' ), 'init_callback': 'initSubPasswordPolicyView' },
			'tab_ldap': { 'label': $.i18n._( 'LDAP Authentication' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );


		//Tab 0 start

		var tab_company = this.edit_view_tab.find( '#tab_company' );

		var tab_company_column1 = tab_company.find( '.first-column' );
		var tab_company_column2 = tab_company.find( '.second-column' );

		var form_item_input;

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_company_column1 );
		this.edit_view_tabs[0].push( tab_company_column2 );

		// Product Edition
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'product_edition_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.product_edition_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Product Edition' ), form_item_input, tab_company_column1, '' );

		// Full Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Full Name' ), form_item_input, tab_company_column1 );
		form_item_input.parent().width( '45%' );

		// Short Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'short_name', width: 128 } );
		this.addEditFieldToColumn( $.i18n._( 'Short Name' ), form_item_input, tab_company_column1 );

		// Industry
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'industry_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.industry_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Industry' ), form_item_input, tab_company_column1 );

		// Business/Employer ID Number
//		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
//		form_item_input.TTextInput( {field: 'business_number', width: 149} );
//		this.addEditFieldToColumn( $.i18n._( 'Business/Employer ID Number' ), form_item_input, tab_company_column1 );

		// Address (Line 1)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address1', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 1)' ), form_item_input, tab_company_column1 );
		form_item_input.parent().width( '45%' );

		// Address (Line 2)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address2', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 2)' ), form_item_input, tab_company_column1 );
		form_item_input.parent().width( '45%' );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_company_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_company_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_company_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'postal_code', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_company_column1, '' );

		// Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Phone' ), form_item_input, tab_company_column2, '' );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'fax_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_company_column2 );

		// Administrative Contact
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'admin_contact'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Administrative Contact' ), form_item_input, tab_company_column2 );

		// billing contact
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'billing_contact'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Billing Contact' ), form_item_input, tab_company_column2 );

		// Primary Support contact
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'support_contact'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Primary Support Contact' ), form_item_input, tab_company_column2 );

		// Company Settings
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Company Settings' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_company_column2 );

		// Terminated User Disable Login Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'terminated_user_disable_login_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.terminated_user_disable_login_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Disable Terminated Employees' ), form_item_input, tab_company_column2, '' );


		// Terminated User Disable Login After Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'terminated_user_disable_login_after_days', width: 25 } );
		terminated_user_disable_login_after_days_description = $( '<div class=\'widget-h-box\'></div>' );
		terminated_user_disable_login_after_days_description.append( form_item_input );
		terminated_user_disable_login_after_days_description.append( $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'Days' ) + ' )</span>' ) );
		this.addEditFieldToColumn( $.i18n._( 'Disable Terminated Employees Login After' ), form_item_input, tab_company_column2, '', terminated_user_disable_login_after_days_description );

		// Logo

		if ( typeof FormData == 'undefined' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

			this.file_browser = form_item_input.TImageBrowser( { field: '', default_width: 128, default_height: 128 } );

			this.file_browser.bind( 'imageChange', function( e, target ) {
				new ServiceCaller().uploadFile( target.getValue(), 'object_type=company_logo', {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( ServiceCaller.companyLogo );
							$this.updateCompanyLogo();
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			} );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );

			this.file_browser = form_item_input.TImageAdvBrowser( {
				field: '', callBack: function( form_data ) {
					new ServiceCaller().uploadFile( form_data, 'object_type=company_logo', {
						onResult: function( result ) {

							if ( result.toLowerCase() === 'true' ) {
								$this.file_browser.setImage( ServiceCaller.companyLogo );
								$this.updateCompanyLogo();
							} else {
								TAlertManager.showAlert( result, 'Error' );
							}
						}
					} );

				}
			} );
		}

		if ( this.is_edit || this.edit_only_mode ) {
			this.file_browser.setEnableDelete( true );
			this.file_browser.bind( 'deleteClick', function( e, target ) {
				$this.api.deleteImage( $this.current_edit_record.id, {
					onResult: function( result ) {
						$this.initEditView( result );
					}
				} );
			} );
		}

		this.addEditFieldToColumn( $.i18n._( 'Logo' ), this.file_browser, tab_company_column2, '', null, false, true );

		// // Enable Second Surname
		// form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		// form_item_input.TCheckbox( {field: 'enable_second_last_name'} );
		// this.addEditFieldToColumn( $.i18n._( 'Enable Second Surname' ), form_item_input, tab_company_column2, '' );

		//Tab 1 start

		var tab_password_policy = this.edit_view_tab.find( '#tab_password_policy' );

		var tab_password_policy_column1 = tab_password_policy.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_password_policy_column1 );

		// Password Policy
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'password_policy_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.password_policy_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Password Policy' ), form_item_input, tab_password_policy_column1, '' );

		// Minimum Permission Level

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'password_minimum_permission_level' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.password_minimum_permission_level_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Permission Level' ), form_item_input, tab_password_policy_column1 );

		// Minimum Strength

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'password_minimum_strength' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.password_minimum_strength_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Strength' ), form_item_input, tab_password_policy_column1 );

		// Minimum Length
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'password_minimum_length', width: 30 } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Length' ), form_item_input, tab_password_policy_column1 );

		// Minimum Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'password_minimum_age', width: 30 } );

		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'in Days' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Age' ), form_item_input, tab_password_policy_column1, '', widgetContainer );

		// Maximum Age

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'password_maximum_age', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'in Days' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Age' ), form_item_input, tab_password_policy_column1, '', widgetContainer );

		//Tab 1 start

		var tab_ldap = this.edit_view_tab.find( '#tab_ldap' );

		var tab_ldap_column1 = tab_ldap.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_ldap_column1 );

		//
		// LDAP Authentication
		//
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'ldap_authentication_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.ldap_authentication_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'LDAP Authentication' ), form_item_input, tab_ldap_column1 );

		// Server
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_host', width: 240 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(ie: ldap.example.com or ldaps://ldap.example.com for SSL)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Server' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// Port
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_port', width: 50 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(ie: 389 or 636 for SSL)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Port' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// Bind User Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_bind_user_name' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Used to search for the user, for anonymous binding enter: anonymous' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Bind User Name' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// Bind Password
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_bind_password' } );
		this.addEditFieldToColumn( $.i18n._( 'Bind Password' ), form_item_input, tab_ldap_column1, '', null, true );

		// Base DN

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_base_dn', width: 300 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(ie: ou=People,dc=example,dc=com)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Base DN' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// Bind Attribute
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_bind_attribute', width: 150 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'For binding the LDAP user. (ie: AD/openLDAP: userPrincipalName, Mac OSX: uid)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Bind Attribute' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// User Filter
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_user_filter', width: 150 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Additional filter parameters. (ie: is_timetrex_user=1)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'User Filter' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

		// Login Attribute
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ldap_login_attribute', width: 150 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'For searching the LDAP user. (ie: AD: sAMAccountName, openLDAP: dn, Mac OSX: dn)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Login Attribute' ), form_item_input, tab_ldap_column1, '', widgetContainer, true );

	},

	onTypeChange: function() {
		if ( this.current_edit_record.ldap_authentication_type_id == 0 ) {
			this.detachElement( 'ldap_host' );
			this.detachElement( 'ldap_port' );
			this.detachElement( 'ldap_bind_user_name' );
			this.detachElement( 'ldap_bind_password' );
			this.detachElement( 'ldap_base_dn' );
			this.detachElement( 'ldap_bind_attribute' );
			this.detachElement( 'ldap_user_filter' );
			this.detachElement( 'ldap_login_attribute' );

		} else {
			this.attachElement( 'ldap_host' );
			this.attachElement( 'ldap_port' );
			this.attachElement( 'ldap_bind_user_name' );
			this.attachElement( 'ldap_bind_password' );
			this.attachElement( 'ldap_base_dn' );
			this.attachElement( 'ldap_bind_attribute' );
			this.attachElement( 'ldap_user_filter' );
			this.attachElement( 'ldap_login_attribute' );
		}

		this.editFieldResize();
	}


} );

//
//CompanyViewController.loadView = function() {
//
//	  Global.loadViewSource( 'Company', 'CompanyView.html', function( result ) {
//
//		  var args = {};
//		  var template = _.template( result, args );
//
//		  Global.contentContainer().html( template );
//	  } )
//
//};