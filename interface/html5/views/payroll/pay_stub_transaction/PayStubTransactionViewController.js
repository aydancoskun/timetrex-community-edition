PayStubTransactionViewController = BaseViewController.extend( {
	el: '#pay_stub_transaction_view_container',

	_required_files: ['APIPayStubTransaction', 'APICurrency', 'APIRemittanceSourceAccount', 'APIRemittanceDestinationAccount', 'APIPayStubEntry', 'APIPayStub', 'APIUserGroup', 'APIPayPeriod'],

	status_array: null,
	currency_array: null,
	user_status_array: null,
	user_group_array: null,
	type_array: null,

	user_api: null,
	user_group_api: null,
	company_api: null,
	pay_stub_entry_api: null,

	include_entries: true,

	init: function() {
		//this._super('initialize' );
		this.edit_view_tpl = 'PayStubTransactionEditView.html';
		this.permission_id = 'pay_stub';
		this.viewId = 'PayStubTransaction';
		this.script_name = 'PayStubTransactionView';
		this.table_name_key = 'pay_stub_transaction';
		this.context_menu_name = $.i18n._( 'Pay Stub Transaction' );
		this.navigation_label = $.i18n._( 'Pay Stub Transactions' ) + ':';

		this.api = new ( APIFactory.getAPIClass( 'APIPayStubTransaction' ) )();
		this.currency_api = new ( APIFactory.getAPIClass( 'APICurrency' ) )();
		this.remittance_source_account_api = new ( APIFactory.getAPIClass( 'APIRemittanceSourceAccount' ) )();
		this.remittance_destination_account_api = new ( APIFactory.getAPIClass( 'APIRemittanceDestinationAccount' ) )();
		this.user_api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.pay_stub_entry_api = new ( APIFactory.getAPIClass( 'APIPayStubEntry' ) )();
		this.user_group_api = new ( APIFactory.getAPIClass( 'APIUserGroup' ) )();
		this.company_api = new ( APIFactory.getAPIClass( 'APICompany' ) )();
		this.pay_period_api = new ( APIFactory.getAPIClass( 'APIPayPeriod' ) )();

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayStub' );
	},

	initPermission: function() {
		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

	},

	initOptions: function( callBack ) {
		var $this = this;

		this.initDropDownOption( 'status', 'transaction_status_id' );
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.view,
				ContextMenuIconName.edit,
				ContextMenuIconName.mass_edit,
				ContextMenuIconName.save,
				ContextMenuIconName.save_and_continue,
				ContextMenuIconName.save_and_next,
				ContextMenuIconName.cancel,
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
					label: $.i18n._( 'Edit<br>Employee' ),
					id: ContextMenuIconName.edit_employee,
					group: 'navigation',
					icon: Icons.employee
				},
				{
					label: $.i18n._( 'Edit Pay<br>Period' ),
					id: ContextMenuIconName.edit_pay_period,
					group: 'navigation',
					icon: Icons.pay_period
				}
			]
		};

		return context_menu_model;
	},

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
				case ContextMenuIconName.pay_stub_transaction:

				case ContextMenuIconName.pay_stub:
				case ContextMenuIconName.view:
					//View icon should be displayed separate from Employee Pay Stub/Employer Pay Stub icons.
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length );
					break;
			}
		}
		this.setContextMenuGroupVisibility();
	},

	setDefaultMenuEditPayPeriodIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}
		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub_amendment' ) {
			this._super( 'setDefaultMenuViewIcon', context_btn, grid_selected_length, pId );
		} else {
			if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
				context_btn.addClass( 'invisible-image' );
			}

			if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}
		}
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}
				continue;
			}

			switch ( id ) {
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.import_icon:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuViewIcon( context_btn, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_transaction:
					this.setEditMenuViewIcon( context_btn, 'pay_stub_transaction' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setEditMenuViewIcon( context_btn, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuViewIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setEditMenuViewIcon( context_btn, 'pay_period_schedule' );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuEditIcon( context_btn );
					break;

			}
		}

		this.setContextMenuGroupVisibility();
	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setCurrentEditRecordData: function() {
		this.include_entries = true;
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}
			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );

		this.edit_view_ui_dic.user_id.setEnabled( false );
		this.edit_view_ui_dic.remittance_source_account_id.setEnabled( false );
		this.edit_view_ui_dic.remittance_destination_account_id.setEnabled( false );
		this.edit_view_ui_dic.currency_id.setEnabled( false );
		this.edit_view_ui_dic.amount.setEnabled( false );
		this.edit_view_ui_dic.confirmation_number.setEnabled( false );
	},

	onSaveClick: function( ignoreWarning ) {
		if ( this.is_mass_editing ) {
			this.include_entries = false; // Note: not sure if we really need this, as a code search for this variable shows it only set in one other place, but not used. Was in original onSaveClick, so including it here for now.
		}
		this._super( 'onSaveClick', ignoreWarning );
	},

	onSaveAndContinue: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );
			}
		} );
	},

	onSaveAndContinueResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;

			}
			$this.search( false );
			//     $this.editor.show_cover = false;

			$this.onSaveAndContinueDone( result );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	// onSaveAndNextResult: function( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	// 		}
	// 		//     $this.editor.show_cover = true;
	// 		$this.onRightArrowClick();
	// 		$this.search( false );
	// 		$this.onSaveAndNextDone( result );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.pay_stub_transaction_date = true;
		column_filter.pay_stub_start_date = true;
		column_filter.pay_stub_end_date = true;
		column_filter.id = true;
		column_filter.status_id = true;
		column_filter.is_owner = true;
		column_filter.user_id = true;
		column_filter.pay_stub_id = true;
		column_filter.pay_period_id = true;
		column_filter.pay_stub_run_id = true;
		column_filter.currency_id = true;
		column_filter.remittance_source_account_type_id = true;
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			for ( var i = 0; i < display_columns.length; i++ ) {
				column_filter[display_columns[i].name] = true;
			}
		}
		return column_filter;
	},

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	validate: function() {
		var $this = this;
		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}
				}
			}
		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );
		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );
		var $this = this;

		var tab_model = {
			'tab_pay_stub_transaction': { 'label': $.i18n._( 'Pay Stub Transaction' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStub' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_pay_stub_transaction = this.edit_view_tab.find( '#tab_pay_stub_transaction' );
		var tab_pay_stub_transaction_column1 = tab_pay_stub_transaction.find( '.first-column' );
		var form_item_input;
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: false,
			set_empty: false,
			field: 'user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIRemittanceSourceAccount' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REMITTANCE_SOURCE_ACCOUNT,
			show_search_inputs: false,
			set_empty: false,
			field: 'remittance_source_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Source Account' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIRemittanceDestinationAccount' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REMITTANCE_DESTINATION_ACCOUNT,
			show_search_inputs: false,
			set_empty: false,
			field: 'remittance_destination_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Destination Account' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			field: 'currency_id',
			set_empty: false,
			layout_name: ALayoutIDs.CURRENCY,
			allow_multiple_selection: false,
			show_search_inputs: false,
			api_class: ( APIFactory.getAPIClass( 'APICurrency' ) )
		} );
		;
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'amount', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'transaction_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Date' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'confirmation_number', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Confirmation #' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_pay_stub_transaction_column1 );
	},

	buildSearchFields: function() {
		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'transaction_status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Source Account' ),
				in_column: 2,
				field: 'remittance_source_account_id',
				layout_name: ALayoutIDs.REMITTANCE_SOURCE_ACCOUNT,
				api_class: ( APIFactory.getAPIClass( 'APIRemittanceSourceAccount' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: ( APIFactory.getAPIClass( 'APIPayPeriod' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.USER,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Currency' ),
				in_column: 2,
				field: 'currency_id',
				api_class: ( APIFactory.getAPIClass( 'APICurrency' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.CURRENCY,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Transaction Date' ),
				in_column: 2,
				field: 'transaction_date',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.DATE_PICKER
			} )

		];
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub_amendment:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.generate_pay_stub:
			case ContextMenuIconName.pay_stub_transaction:
			case ContextMenuIconName.edit_pay_period:
			case ContextMenuIconName.pay_stub:
				this.onNavigationClick( id );
				break;
		}
	},

	onViewClick: function( editId, noRefreshUI ) {
		this.onNavigationClick( ContextMenuIconName.view );
	},

	onNavigationClick: function( iconName ) {
		var $this = this;
		var grid_selected_id_array;
		var filter = {};
		var ids = [];
		var user_ids = [];
		var base_date;
		var pay_period_ids = [];
		var pay_stub_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			pay_stub_ids.push( $this.current_edit_record.pay_stub_id );
			base_date = $this.current_edit_record.pay_stub_start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				pay_stub_ids.push( grid_selected_row.pay_stub_id );
				base_date = grid_selected_row.pay_stub_start_date;
			} );
		}

		var args = { filter_data: { id: ids } };

		var post_data;
		switch ( iconName ) {
			case ContextMenuIconName.pay_stub:
				filter.filter_data = {};
				filter.filter_data.id = { value: pay_stub_ids };
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'PayStub', filter );
				break;
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case ContextMenuIconName.edit_pay_period:
				if ( pay_period_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_ids[0] );
				}
				break;
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = { value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case ContextMenuIconName.pay_stub_amendment:
				filter.filter_data = {};
				filter.filter_data.user_id = user_ids[0];
				filter.filter_data.pay_period_id = pay_period_ids[0];
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case ContextMenuIconName.view:
				this.setCurrentEditViewState( 'view' );
				this.openEditView();
				filter.filter_data = {};

				var grid_selected_id_array = this.getGridSelectIdArray();
				var selectedId = grid_selected_id_array[0];
				filter.filter_data.id = [selectedId];

				this.api['get' + this.api.key_name]( filter, {
					onResult: function( result ) {
						var result_data = result.getResult();
						if ( !result_data ) {
							result_data = [];
						}

						result_data = result_data[0];

						if ( !result_data ) {
							TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
							$this.onCancelClick();
							return;
						}

						if ( $this.sub_view_mode && $this.parent_key ) {
							result_data[$this.parent_key] = $this.parent_value;
						}

						$this.current_edit_record = result_data;

						$this.initEditView();

					}
				} );
				break;
			case ContextMenuIconName.pay_stub_transaction:
				IndexViewController.openEditView( this, 'PayStubTransaction', user_ids[0] );
				break;
		}

	}

} );

PayStubTransactionViewController.loadView = function() {
	Global.loadViewSource( 'PayStubTransaction', 'PayStubTransactionView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );
		Global.contentContainer().html( template );
	} );

};
