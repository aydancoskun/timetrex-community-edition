PolicyGroupViewController = BaseViewController.extend( {
	el: '#policy_group_view_container',

	_required_files: {
		10: ['APIPolicyGroup', 'APIExceptionPolicyControl', 'APIOvertimePolicy', 'APIRoundIntervalPolicy', 'APIAbsencePolicy', 'APIAccrualPolicy', 'APIPremiumPolicy', 'APIHolidayPolicy', 'APIRegularTimePolicy', 'APIMealPolicy', 'APIBreakPolicy'],
		25: ['APIExpensePolicy']
	},

	sub_document_view_controller: null,
	document_object_type_id: null,
	exception_policy_control_api: null,
	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PolicyGroupEditView.html';
		this.permission_id = 'policy_group';
		this.viewId = 'PolicyGroup';
		this.script_name = 'PolicyGroupView';
		this.table_name_key = 'policy_group';
		this.document_object_type_id = 200;
		this.context_menu_name = $.i18n._( 'Policy Group' );
		this.navigation_label = $.i18n._( 'Policy Group' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPolicyGroup' ))();
		this.exception_policy_control_api = new (APIFactory.getAPIClass( 'APIExceptionPolicyControl' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PolicyGroup' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_policy_group': { 'label': $.i18n._( 'Policy Group' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPolicyGroup' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.POLICY_GROUP,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_policy_group = this.edit_view_tab.find( '#tab_policy_group' );

		var tab_policy_group_column1 = tab_policy_group.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_policy_group_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_policy_group_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_policy_group_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		var default_args = {};
		default_args.permission_section = 'policy_group';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_policy_group_column1 );

		// Regular Time Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRegularTimePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.REGULAR_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'regular_time_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Regular Time Policies' ), form_item_input, tab_policy_group_column1 );

		// Overtime Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'over_time_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Overtime Policies' ), form_item_input, tab_policy_group_column1 );

		// Rounding Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRoundIntervalPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ROUND_INTERVAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'round_interval_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Rounding Policies' ), form_item_input, tab_policy_group_column1 );

		// Meal Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMealPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.MEAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'meal_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Meal Policies' ), form_item_input, tab_policy_group_column1 );

		// Break Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBreakPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.BREAK_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'break_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Break Policies' ), form_item_input, tab_policy_group_column1 );

		// Accrual Policies

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ACCRUAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Policies' ), form_item_input, tab_policy_group_column1 );

		// Premium Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPremiumPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PREMIUM_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'premium_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Premium Policies' ), form_item_input, tab_policy_group_column1 );

		// Holiday Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIHolidayPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.HOLIDAY_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'holiday_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Holiday Policies' ), form_item_input, tab_policy_group_column1 );

		if ( LocalCacheData.getCurrentCompany().product_edition_id >= 25 ) {
			// Expense Policies
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIExpensePolicy' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.EXPENSE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'expense_policy'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Expense Policies' ), form_item_input, tab_policy_group_column1 );
		}

		// Exception Policy

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIExceptionPolicyControl' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.EXCEPTION_POLICY_CONTROL,
			show_search_inputs: true,
			set_empty: true,
			field: 'exception_policy_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Exception Policies' ), form_item_input, tab_policy_group_column1 );

		// Absence Policies

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Absence Policies' ), form_item_input, tab_policy_group_column1, '' );

	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		var $this = this;
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'exception_policy_control_id':
						widget.setValue( $this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'policy_group';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Employees' ),
				in_column: 1,
				field: 'user',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Overtime Policy' ),
				in_column: 1,
				field: 'over_time_policy',
				layout_name: ALayoutIDs.OVER_TIME_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Rounding Policies' ),
				in_column: 1,
				field: 'round_interval_policy',
				layout_name: ALayoutIDs.ROUND_INTERVAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIRoundIntervalPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Absence Policies' ),
				in_column: 1,
				field: 'absence_policy',
				layout_name: ALayoutIDs.ABSENCES_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Accrual Policies' ),
				in_column: 2,
				field: 'accrual_policy',
				layout_name: ALayoutIDs.ACCRUAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Premium Policies' ),
				in_column: 2,
				field: 'premium_policy',
				layout_name: ALayoutIDs.PREMIUM_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIPremiumPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Holiday Policies' ),
				in_column: 2,
				field: 'holiday_policy',
				layout_name: ALayoutIDs.HOLIDAY_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIHolidayPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Exception Policy' ),
				in_column: 2,
				field: 'exception_policy_control',
				layout_name: ALayoutIDs.EXCEPTION_POLICY_CONTROL,
				api_class: (APIFactory.getAPIClass( 'APIExceptionPolicyControl' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}


} );
