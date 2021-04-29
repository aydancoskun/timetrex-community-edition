export class SchedulePolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#schedule_policy_view_container',

			over_time_policy_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'SchedulePolicyEditView.html';
		this.permission_id = 'schedule_policy';
		this.viewId = 'SchedulePolicy';
		this.script_name = 'SchedulePolicyView';
		this.table_name_key = 'schedule_policy';
		this.context_menu_name = $.i18n._( 'Schedule Policy' );
		this.navigation_label = $.i18n._( 'Schedule Policy' ) + ':';
		this.api = TTAPI.APISchedulePolicy;
		this.over_time_policy_api = TTAPI.APIOverTimePolicy;
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'SchedulePolicy' );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_schedule_policy': { 'label': $.i18n._( 'Schedule Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APISchedulePolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_schedule',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_schedule_policy = this.edit_view_tab.find( '#tab_schedule_policy' );

		var tab_schedule_policy_column1 = tab_schedule_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_schedule_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_schedule_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_schedule_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Meal Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIMealPolicy,
			allow_multiple_selection: true,
			layout_name: 'global_meal',
			show_search_inputs: true,
			set_any: true,
			field: 'meal_policy',
			custom_first_label: $.i18n._( '-- No Meal --' ),
			addition_source_function: this.onMealOrBreakSourceCreate,
			added_items: [
				{ value: TTUUID.zero_id, label: $.i18n._( '-- Defined By Policy Group --' ) }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Meal Policy' ), form_item_input, tab_schedule_policy_column1 );

		//Break Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIBreakPolicy,
			allow_multiple_selection: true,
			layout_name: 'global_break',
			show_search_inputs: true,
			set_any: true,
			field: 'break_policy',
			custom_first_label: '-- ' + $.i18n._( 'No Break' ) + ' --',
			addition_source_function: this.onMealOrBreakSourceCreate,
			added_items: [
				{ value: TTUUID.zero_id, label: '-- ' + $.i18n._( 'Defined By Policy Group' ) + ' --' }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Break Policy' ), form_item_input, tab_schedule_policy_column1 );

		// Regular Time Policy
		var v_box = $( '<div class=\'v-box\'></div>' );
		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRegularTimePolicy,
			allow_multiple_selection: true,
			layout_name: 'global_regular_time',
			show_search_inputs: true,
			set_empty: true,
			field: 'include_regular_time_policy'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIRegularTimePolicy,
			allow_multiple_selection: true,
			layout_name: 'global_regular_time',
			show_search_inputs: true,
			set_empty: true,
			field: 'exclude_regular_time_policy'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Regular Time Policy' ), [form_item_input, form_item_input_1], tab_schedule_policy_column1, '', v_box, false, true );

		//Overtime Policy

		v_box = $( '<div class=\'v-box\'></div>' );

		var default_args = {};
		default_args.filter_data = {};
		default_args.filter_data.type_id = [10, 40, 50, 60, 70, 80, 90, 100, 180, 200];

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIOverTimePolicy,
			allow_multiple_selection: true,
			layout_name: 'global_over_time',
			show_search_inputs: true,
			set_empty: true,
			field: 'include_over_time_policy'
		} );

		form_item_input.setDefaultArgs( default_args );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIOverTimePolicy,
			allow_multiple_selection: true,
			layout_name: 'global_over_time',
			show_search_inputs: true,
			set_empty: true,
			field: 'exclude_over_time_policy'
		} );

		form_item_input_1.setDefaultArgs( default_args );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Overtime Policy' ), [form_item_input, form_item_input_1], tab_schedule_policy_column1, '', v_box, false, true );

		//Premium Policy
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPremiumPolicy,
			allow_multiple_selection: true,
			layout_name: 'global_premium',
			show_search_inputs: true,
			set_empty: true,
			field: 'include_premium_policy'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPremiumPolicy,
			allow_multiple_selection: true,
			layout_name: 'global_premium',
			show_search_inputs: true,
			set_empty: true,
			field: 'exclude_premium_policy'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Premium Policy' ), [form_item_input, form_item_input_1], tab_schedule_policy_column1, '', v_box, false, true );

		//Full Shift Undertime Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			show_search_inputs: true,
			set_empty: true,
			field: 'full_shift_absence_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Full Shift Undertime Absence Policy' ), form_item_input, tab_schedule_policy_column1 );

		//Partial Shift Undertime Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			show_search_inputs: true,
			set_empty: true,
			field: 'partial_shift_absence_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Partial Shift Undertime Absence Policy' ), form_item_input, tab_schedule_policy_column1 );

		//Start / Stop Window
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'start_stop_window', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Start / Stop Window' ), form_item_input, tab_schedule_policy_column1, '', null );
	}

	onMealOrBreakSourceCreate( target, source_data ) {
		var display_columns = target.getDisplayColumns();

		var first_item = {};
		$.each( display_columns, function( index, content ) {

			first_item.id = TTUUID.zero_id;
			first_item[content.name] = $.i18n._( '-- Defined By Policy Group --' );

			return false;
		} );

		source_data.unshift( first_item );

		return source_data;
	}

	setCurrentEditRecordData() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country': //popular case
						this.setCountryValue( widget, key );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

//		switch ( key ) {
//			case 'start_stop_window':
//				c_value = this.date_api.parseTimeUnit( target.getValue(), {async: false} ).getResult();
//				break;
//		}

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
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
				label: $.i18n._( 'Full Shift Absence Policy' ),
				in_column: 1,
				field: 'full_shift_absence_policy_id',
				layout_name: 'global_absences',
				api_class: TTAPI.APIAbsencePolicy,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Partial Shift Absence Policy' ),
				in_column: 1,
				field: 'partial_shift_absence_policy_id',
				layout_name: 'global_absences',
				api_class: TTAPI.APIAbsencePolicy,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

}
