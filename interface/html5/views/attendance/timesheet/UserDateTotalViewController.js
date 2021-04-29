UserDateTotalViewController = BaseViewController.extend( {

	el: '#user_date_total_view_container', //Must set el here and can only set string, so events can work

	_required_files: ['APIUserDateTotal', 'APICurrency'],

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserDateTotalEditView.html';
		this.permission_id = 'user_date_total';
		this.script_name = 'UserDateTotalView';
		this.viewId = 'UserDateTotal';
		this.table_name_key = 'user_date_total';
		this.context_menu_name = $.i18n._( 'Accumulated Time' );
		this.navigation_label = $.i18n._( 'Accumulated Time' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIUserDateTotal' ) )();
		this.currency_api = new ( APIFactory.getAPIClass( 'APICurrency' ) )();

		if ( PermissionManager.validate( this.permission_id, 'add' ) || PermissionManager.validate( this.permission_id, 'edit' ) ) {
			$( this.el ).find( '.warning-message' ).text( $.i18n._( 'WARNING: Manually modifying Accumulated Time records may prevent policies from being calculated properly and should only be done as a last resort when instructed to do so by a support representative.' ) );
		} else {
			$( this.el ).find( '.warning-message' ).hide();
		}

		this.initPermission();
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

		this.setSelectRibbonMenuIfNecessary();

	},

	setGridSize: function() {
		// if ( ( !this.grid || !this.grid.grid.is( ':visible' ) ) ) {
		// 	return;
		// }
		//this.grid.grid.setGridWidth( $( this.el ).parent().width() - 2 );

		message_offset = ( $( this.el ).find( '.warning-message' ).outerHeight() * 2 ) + 27;
		this.grid.grid.setGridHeight( $( this.el ).parents( '#tab_user_date_total_parent' ).height() - message_offset );
	},

	setGridCellBackGround: function() {

		var data = this.grid.getGridParam( 'data' );

		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.is_override === true ) {
				$( 'tr[id=\'' + item.id + '\']' ).addClass( 'user-data-total-override' );
			}
		}
	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'object_type' );

	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.copy, ContextMenuIconName.export_excel],
			include: ['default']
		};

		return context_menu_model;
	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'object_type_id':
				this.onTypeChange( true );
				break;
			case 'regular_policy_id':
			case 'absence_policy_id':
			case 'overtime_policy_id':
			case 'premium_policy_id':
			case 'break_policy_id':
			case 'meal_policy_id':
				this.current_edit_record.src_object_id = c_value;
				delete this.current_edit_record[key];
				this.onSrcObjectChange( key );
				break;
			case 'total_time':
				if ( this.current_edit_record.total_time == 0 ) {
					this.edit_view_ui_dic.start_time_stamp.setValue( '' );
					this.edit_view_ui_dic.end_time_stamp.setValue( '' );
					this.current_edit_record.start_time_stamp = '';
					this.current_edit_record.end_time_stamp = '';

					//Trigger onChange event for above fields, so in mass edit they are marked as changed.
					this.edit_view_form_item_dic.start_time_stamp.find( 'input' ).trigger( 'change', '' );
					this.edit_view_form_item_dic.end_time_stamp.find( 'input' ).trigger( 'change', '' );
				}
				this.calculateAmount();
				break;
			case 'hourly_rate':
				this.calculateAmount();
				break;
		}

		if ( key !== 'override' ) {
			this.edit_view_ui_dic.override.setValue( true );
			this.current_edit_record.override = true;
			this.edit_view_form_item_dic.override.find( 'input' ).trigger( 'change', '1' ); //Trigger onChange event for above fields, so in mass edit they are marked as changed.
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	calculateAmount: function() {
		this.current_edit_record.total_time_amount = ( this.current_edit_record.total_time / 3600 ) * parseFloat( this.current_edit_record.hourly_rate );
		this.edit_view_ui_dic.total_time_amount.setValue( this.current_edit_record.total_time_amount.toFixed( 4 ) );
	},

	onAddClick: function() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		$this.openEditView();

		//Error: Uncaught TypeError: undefined is not a function in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 897
		if ( $this.api ) {
			$this.api['get' + $this.api.key_name + 'DefaultData'](
				this.parent_edit_record.user_id,
				this.parent_edit_record.date_stamp, {
					onResult: function( result ) {
						$this.onAddResult( result );
					}
				} );
		}

	},

	onAddResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		if ( !result_data.date_stamp ) {
			result_data.date_stamp = this.parent_edit_record.date_stamp;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	/* jshint ignore:start */
	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Employee&a=edit&id=42411&tab=UserDateTotal line 282
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
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	/* jshint ignore:end */

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

		if ( this.branchUIValidate() ) {
			this.show_branch_ui = true;
		} else {
			this.show_branch_ui = false;
		}

		if ( this.departmentUIValidate() ) {
			this.show_department_ui = true;
		} else {
			this.show_department_ui = false;
		}

		if ( this.goodQuantityUIValidate() ) {
			this.show_good_quantity_ui = true;
		} else {
			this.show_good_quantity_ui = false;
		}

		if ( this.badQuantityUIValidate() ) {
			this.show_bad_quantity_ui = true;
		} else {
			this.show_bad_quantity_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_note_ui = true;
		} else {
			this.show_note_ui = false;
		}

	},

	noteUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	jobUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	setCurrency: function() {
		var $this = this;
		if ( Global.isSet( this.current_edit_record.user_id ) ) {
			var filter = {};
			filter.filter_data = { user_id: this.current_edit_record.user_id };
			this.currency_api.getCurrency( filter, false, false, {
				onResult: function( res ) {
					res = res.getResult();
					if ( Global.isArray( res ) ) {
						$( '.userDateTotal-currency' ).text( res[0].symbol );
						$( '.userDateTotal-code' ).text( res[0].iso_code );
					} else {
						$( '.userDateTotal-currency' ).text( '' );
						$( '.userDateTotal-code' ).text( '' );
					}
				}
			} );
		}
	},

	setCurrentEditRecordData: function() {
		this.setCurrency();
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			switch ( key ) {
				case 'user_id':
					var current_widget = this.edit_view_ui_dic['first_last_name'];
					new ( APIFactory.getAPIClass( 'APIUser' ) )().getUser( { filter_data: { id: this.current_edit_record[key] } }, {
						onResult: function( result ) {

							if ( result.isValid() ) {
								var user_data = result.getResult()[0];
							}

							//Error: Unable to get property 'first_name' of undefined or null reference in /interface/html5/ line 511
							if ( user_data && user_data.first_name ) {
								current_widget.setValue( user_data.first_name + ' ' + user_data.last_name );
							} else {
								current_widget.setValue( '' );
							}

						}
					} );
					break;
				case 'date_stamp':
					widget.setEnabled( false );
					widget.setValue( this.current_edit_record[key] );
					break;
				default:
					if ( widget ) {
						widget.setValue( this.current_edit_record[key] );
					}
					break;
			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );
		this.onTypeChange();
	},

	onTypeChange: function( reset ) {
		this.detachElement( 'regular_policy_id' );
		this.detachElement( 'absence_policy_id' );
		this.detachElement( 'overtime_policy_id' );
		this.detachElement( 'premium_policy_id' );
		this.detachElement( 'meal_policy_id' );
		this.detachElement( 'break_policy_id' );
		var key = '';
		if ( this.current_edit_record['object_type_id'] == 20 ) {
			key = 'regular_policy_id';
		} else if ( this.current_edit_record['object_type_id'] == 25 || this.current_edit_record['object_type_id'] == 50 ) {
			key = 'absence_policy_id';
		} else if ( this.current_edit_record['object_type_id'] == 30 ) {
			key = 'overtime_policy_id';
		} else if ( this.current_edit_record['object_type_id'] == 40 ) {
			key = 'premium_policy_id';
		} else if ( this.current_edit_record['object_type_id'] == 100 || this.current_edit_record['object_type_id'] == 101 ) {
			key = 'meal_policy_id';
		} else if ( this.current_edit_record['object_type_id'] == 110 || this.current_edit_record['object_type_id'] == 111 ) {
			key = 'break_policy_id';
		}
		if ( key ) {
			this.attachElement( key );
			if ( reset ) {
				this.edit_view_ui_dic[key].setValue( '' );
				this.edit_view_ui_dic['pay_code_id'].setValue( '' );
				this.current_edit_record.src_object_id = false;
				this.current_edit_record.pay_code_id = false;
				this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			} else if ( this.current_edit_record.src_object_id ) {
				this.edit_view_ui_dic[key].setValue( this.current_edit_record.src_object_id );
				this.edit_view_ui_dic['pay_code_id'].setEnabled( false );
			}
		} else {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			this.current_edit_record.src_object_id = false;
		}
		this.editFieldResize();
	},

	onSrcObjectChange: function( key ) {
		var full_value = this.edit_view_ui_dic[key].getValue( true );
		if ( full_value && full_value.pay_code_id ) {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( false );
			this.edit_view_ui_dic['pay_code_id'].setValue( full_value.pay_code_id );
			this.current_edit_record.pay_code_id = full_value.pay_code_id;
		} else {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			this.edit_view_ui_dic['pay_code_id'].setValue( '' );
			this.current_edit_record.pay_code_id = false;
		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = null;
		this._super( 'search', set_default_menu, page_action, page_number, callBack );
	},

	getProperObjectType: function() {
		var array = [];

		for ( var i = 0; i < this.object_type_array.length; i++ ) {
			var item = this.object_type_array[i];

			if ( item.value == 20 ||
				item.value == 25 ||
				item.value == 30 ||
				item.value == 40 ||
				item.value == 100 ||
				item.value == 110 ) {
				array.push( item );
			}

		}

		return array;
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_user_date_total': { 'label': $.i18n._( 'Accumulated Time' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIUserDateTotal' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE,
			show_search_inputs: true,
			navigation_mode: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_user_date_total = this.edit_view_tab.find( '#tab_user_date_total' );

		var tab_user_date_total_column1 = tab_user_date_total.find( '.first-column' );
		var tab_user_date_total_column2 = tab_user_date_total.find( '.second-column' );

		//Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'first_last_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_user_date_total_column1, '' );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'date_stamp' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_user_date_total_column1 );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'total_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_user_date_total_column1, '', null, true );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'object_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( this.getProperObjectType() ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_user_date_total_column1 );

		//Regular Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIRegularTimePolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REGULAR_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'regular_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'regular_policy_id' );

		//Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIAbsencePolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'absence_policy_id' );

		//Overtime Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIOvertimePolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'overtime_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'overtime_policy_id' );

		//Premium Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPremiumPolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PREMIUM_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'premium_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'premium_policy_id' );

		//Meal Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIMealPolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MEAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'meal_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'meal_policy_id' );

		//Break Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIBreakPolicy' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BREAK_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'break_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.detachElement( 'break_policy_id' );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayCode' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_user_date_total_column1 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIBranch' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_user_date_total_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.detachElement( 'branch_id' );
		}

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_user_date_total_column1, '', null, true );

		if ( !this.show_department_ui ) {
			this.detachElement( 'department_id' );
		}

		if ( ( Global.getProductEdition() >= 20 ) ) {

			//Job
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIJob' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: ( function( val ) {

					if ( val ) {
						job_coder.setValue( val.manual_id );
					}
				} ),
				field: 'job_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_user_date_total_column1, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.detachElement( 'job_id' );
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIJobItem' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: ( function( val ) {

					if ( val ) {
						job_item_coder.setValue( val.manual_id );
					}
				} ),
				field: 'job_item_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_user_date_total_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.detachElement( 'job_item_id' );
			}

		}

		//Start Date Time
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'start_time_stamp', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date/Time' ), form_item_input, tab_user_date_total_column2, '', null, true, true );

		//End Date Time
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'end_time_stamp', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'End Date/Time' ), form_item_input, tab_user_date_total_column2, '', null, true, true );

		//Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APICurrency' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			show_search_inputs: true,
			field: 'currency_id',
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_user_date_total_column2 );

		//Base Hourly Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'base_hourly_rate', width: 90 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var currency = $( '<span class=\'userDateTotal-currency widget-left-label\'></span>' );
		var code = $( '<span class=\'userDateTotal-code widget-right-label\'></span>' );
		widgetContainer.append( currency );
		widgetContainer.append( form_item_input );
		widgetContainer.append( code );

		this.addEditFieldToColumn( $.i18n._( 'Base Hourly Rate' ), form_item_input, tab_user_date_total_column2, '', widgetContainer, true );

		//Hourly Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'hourly_rate', width: 90 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		currency = $( '<span class=\'userDateTotal-currency widget-left-label\'></span>' );
		code = $( '<span class=\'userDateTotal-code widget-right-label\'></span>' );
		widgetContainer.append( currency );
		widgetContainer.append( form_item_input );
		widgetContainer.append( code );

		this.addEditFieldToColumn( $.i18n._( 'Hourly Rate' ), form_item_input, tab_user_date_total_column2, '', widgetContainer, true );

		//Total Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'total_time_amount' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		currency = $( '<span class=\'userDateTotal-currency widget-left-label\'></span>' );
		code = $( '<span class=\'userDateTotal-code widget-right-label\'></span>' );
		widgetContainer.append( currency );
		widgetContainer.append( form_item_input );
		widgetContainer.append( code );
		this.addEditFieldToColumn( $.i18n._( 'Total Amount' ), form_item_input, tab_user_date_total_column2, '', widgetContainer, true );

		if ( ( Global.getProductEdition() >= 20 ) ) {

			//Quanitity
			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( { field: 'quantity', width: 50 } );
			good.addClass( 'quantity-input' );

			var good_label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Good' ) + ': </span>' );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( { field: 'bad_quantity', width: 50 } );
			bad.addClass( 'quantity-input' );

			var bad_label = $( '<span class=\'widget-right-label\'>/ ' + $.i18n._( 'Bad' ) + ': </span>' );

			var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_user_date_total_column2, '', widgetContainer, true );

			if ( !this.show_bad_quantity_ui && !this.show_good_quantity_ui ) {
				this.detachElement( 'quantity' );
			} else {
				if ( !this.show_bad_quantity_ui ) {
					bad_label.hide();
					bad.hide();
				}

				if ( !this.show_good_quantity_ui ) {
					good_label.hide();
					good.hide();
				}
			}
		}
		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_user_date_total_column2, '', null, true, true );
		form_item_input.parent().width( '45%' );

		if ( !this.show_note_ui ) {
			this.detachElement( 'note' );
		}

		//Override
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'override' } );
		this.addEditFieldToColumn( $.i18n._( 'Override' ), form_item_input, tab_user_date_total_column2, '', null, true, true );

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#user_date_total_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	}

} );

UserDateTotalViewController.loadView = function( container ) {

	Global.loadViewSource( 'UserDateTotal', 'UserDateTotalView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
		} else {
			Global.contentContainer().html( template( args ) );
		}

	} );

};

UserDateTotalViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'UserDateTotal', 'SubUserDateTotalView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {

				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_user_date_total_view_controller );
				} );
			}

		}

	} );

};
