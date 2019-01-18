UserContactViewController = BaseViewController.extend( {
	el: '#user_contact_view_container',

	_required_files: ['APIUserContact', 'APIEthnicGroup', 'APICompanyGenericTag'],

	user_api: null,
	company_api: null,
	ethnic_group_api: null,
	status_array: null,
	type_array: null,
	sex_array: null,
	country_array: null,
	province_array: null,
	e_province_array: null,

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserContactEditView.html';
		this.permission_id = 'user_contact';
		this.viewId = 'UserContact';
		this.script_name = 'UserContactView';
		this.table_name_key = 'user_contact';
		this.context_menu_name = $.i18n._( 'Employee Contacts' );
		this.navigation_label = $.i18n._( 'Employee Contact' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserContact' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.ethnic_group_api = new (APIFactory.getAPIClass( 'APIEthnicGroup' ))();
		this.document_object_type_id = 115;

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		//this.setSelectRibbonMenuIfNecessary( 'UserContact' )

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'sex' );
		this.initDropDownOption( 'country', 'country', this.company_api );
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		switch ( key ) {
			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
		}

		this.current_edit_record[key] = target.getValue();

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	setSelectLayout: function() {

		if ( this.sub_view_mode ) {
			this._super( 'setSelectLayout', 6 );
		} else {
			this._super( 'setSelectLayout' );
		}

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_employee_contact': { 'label': $.i18n._( 'Employee Contact' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserContact' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER_CONTACT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var tab_employee_contact = this.edit_view_tab.find( '#tab_employee_contact' );
		var tab_employee_contact_column1 = tab_employee_contact.find( '.first-column' );
		var tab_employee_contact_column2 = tab_employee_contact.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_employee_contact_column1 );
		this.edit_view_tabs[0].push( tab_employee_contact_column2 );

		// tab_employee_contact column1

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			field: 'user_id',
			set_empty: true,
			show_search_inputs: true
		} );

		var default_args = {};
		default_args.permission_section = 'user_contact';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_employee_contact_column1, '' );

		// Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'status_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_employee_contact_column1 );

		// Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'type_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_employee_contact_column1 );

		// First Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'first_name',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_employee_contact_column1 );

		// Middle Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'middle_name',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Middle Name' ), form_item_input, tab_employee_contact_column1 );

		// Last Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'last_name',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_employee_contact_column1 );

		// Gender

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'sex_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.sex_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Gender' ), form_item_input, tab_employee_contact_column1 );

		// Ethnicity

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIEthnicGroup' )),
			field: 'ethnic_group_id',
			set_empty: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ETHNIC_GROUP,
			show_search_inputs: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Ethnicity' ), form_item_input, tab_employee_contact_column1 );

		// Home Address(Line 1)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'address1',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address (Line 1)' ), form_item_input, tab_employee_contact_column1 );

		// Home Address(Line 2)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'address2',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address (Line 2)' ), form_item_input, tab_employee_contact_column1 );

		// City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'city',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_employee_contact_column1 );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'country'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_employee_contact_column1 );

		// Province/State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'province'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_employee_contact_column1 );

		// Postal / ZIP Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'postal_code',
			width: 90
		} );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_employee_contact_column1, '' );

		// tab_employee_contact column2

		// Work Phone

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'work_phone',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_employee_contact_column2, '' );

		// Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'work_phone_ext',
			width: 90
		} );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_employee_contact_column2 );

		// Home Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'home_phone',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Home Phone' ), form_item_input, tab_employee_contact_column2 );

		// Mobile Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'mobile_phone',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Mobile Phone' ), form_item_input, tab_employee_contact_column2 );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'fax_phone',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_employee_contact_column2 );

		// Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'work_email',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_employee_contact_column2 );

		// Home Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'home_email',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'Home Email' ), form_item_input, tab_employee_contact_column2 );

		// Birth Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {
			field: 'birth_date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Birth Date' ), form_item_input, tab_employee_contact_column2 );

		// SIN / SSN
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'sin',
			width: 200
		} );
		this.addEditFieldToColumn( $.i18n._( 'SIN / SSN' ), form_item_input, tab_employee_contact_column2 );

		// Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {
			field: 'note'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_employee_contact_column2, '', null, null, true );

		// Tags

		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );
		form_item_input.TTagInput( {
			field: 'tag',
			object_type_id: 230
		} );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_employee_contact_column2, '', null, null, true );

	},

	buildSearchFields: function() {
		//this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'user_contact';

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				default_args: default_args,
				field: 'user_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
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
				in_column: 1,
				field: 'last_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Phone' ),
				in_column: 1,
				field: 'any_phone',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Email' ),
				field: 'any_email',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 2,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
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
				label: $.i18n._( 'Tags' ),
				in_column: 2,
				field: 'tag',
				basic_search: true,
				adv_search: true,
				object_type_id: 230,
				form_item_type: FormItemType.TAG_INPUT
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
				in_column: 3,
				field: 'city',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'SIN/SSN' ),
				in_column: 3,
				field: 'sin',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} )

		];
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

	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	searchDone: function(){
		this._super('searchDone');
		TTPromise.resolve( 'ContactView', 'init' );
	}
} );

UserContactViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'UserContact', 'SubUserContactView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_contact_view_controller );
			}
		}
	} );
};