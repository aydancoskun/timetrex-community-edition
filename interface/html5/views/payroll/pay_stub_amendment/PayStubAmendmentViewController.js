PayStubAmendmentViewController = BaseViewController.extend( {
	el: '#pay_stub_amendment_view_container',

	_required_files: ['APIPayPeriod', 'APIPayStubAmendment', 'APIUserGroup', 'APIPayStubEntryAccount', 'APIUserTitle', 'APIBranch', 'APIDepartment'],

	user_status_array: null,
	filtered_status_array: null,
	type_array: null,
	is_mass_adding: false,

	user_api: null,
	user_group_api: null,
	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayStubAmendmentEditView.html';
		this.permission_id = 'pay_stub_amendment';
		this.viewId = 'PayStubAmendment';
		this.script_name = 'PayStubAmendmentView';
		this.table_name_key = 'pay_stub_amendment';
		this.context_menu_name = $.i18n._( 'Pay Stub Amendment' );
		this.navigation_label = $.i18n._( 'Pay Stub Amendment' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIPayStubAmendment' ) )();
		this.user_api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.user_group_api = new ( APIFactory.getAPIClass( 'APIUserGroup' ) )();
		this.currency_api = new ( APIFactory.getAPIClass( 'APICurrency' ) )();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayStubAmendment' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status', 'user_status_id', this.user_api );
		this.initDropDownOption( 'filtered_status', 'status_id' );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );

		this.api.getOptions( 'status', false, false, {
			onResult: function( res ) {
				var status_array = Global.buildRecordArray( res.getResult() );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['status_id'] ) {
					$this.basic_search_field_ui_dic['status_id'].setSourceData( status_array );
					if ( $this.adv_search_field_ui_dic['status_id'] ) {
						$this.adv_search_field_ui_dic['status_id'].setSourceData( status_array );
					}
				}

			}
		} );
	},

	getFilterColumnsFromDisplayColumns: function() {

		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.effective_date = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	onReportPrintClick: function( key ) {
		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		var pay_period_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		var args = { filter_data: { id: ids } };
		var post_data = { 0: args, 1: true, 2: key };

		this.doFormIFrameCall( post_data );

	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'TimeSheet' ),
					id: ContextMenuIconName.timesheet,
					group: 'navigation',
					icon: Icons.timesheet
				},
				{
					label: $.i18n._( 'Pay Stubs' ),
					id: ContextMenuIconName.pay_stub,
					group: 'navigation',
					icon: Icons.pay_stubs
				},
				{
					label: $.i18n._( 'Edit<br>Employee' ),
					id: ContextMenuIconName.edit_employee,
					group: 'navigation',
					icon: Icons.employee
				},
				{
					label: $.i18n._( 'Import' ),
					id: ContextMenuIconName.import_icon,
					group: 'other',
					icon: Icons.import_icon,
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVPayStubAmendment' ),
					sort_order: 8000
				}
			]
		};

		return context_menu_model;
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
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.print_checks:
					this.setDefaultMenuPrintChecksIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.direct_deposit:
					this.setDefaultMenuDirectDepositIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	//Remove the copy button as it can never work due to API unique constraints.
	setDefaultMenuCopyIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'invisible-image' );
	},
	setEditMenuCopyIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'invisible-image' );
	},

	/* jshint ignore:end */
	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {

		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub' ) {
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

	setDefaultMenuPrintChecksIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDirectDepositIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},
	/* jshint ignore:start */
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
				case ContextMenuIconName.add:

					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
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
				case ContextMenuIconName.pay_stub:
					this.setEditMenuViewIcon( context_btn, 'pay_stub' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuViewIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},
	/* jshint ignore:end */
	setEditMenuViewIcon: function( context_btn, pId ) {

		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'user_id':
				if ( $.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1 ) {
					this.is_mass_adding = true;
				} else {
					this.is_mass_adding = false;
				}
				doNotValidate = true; //Don't validate since setCurrency() triggers calcAmount(), which changes the amount field asynchronously, only then should we validate.
				this.setCurrency();
				this.setEditMenu();
				break;
			case 'type_id':
				this.onTypeChange();
				break;
			case 'rate':
			case 'units':
			case 'amount':
				if ( this.is_mass_editing ) {
					if ( target.isChecked() ) {
						this.edit_view_ui_dic['rate'].setCheckBox( true );
						this.edit_view_ui_dic['units'].setCheckBox( true );
						this.edit_view_ui_dic['amount'].setCheckBox( true );
					} else {
						this.edit_view_ui_dic['rate'].setCheckBox( false );
						this.edit_view_ui_dic['units'].setCheckBox( false );
						this.edit_view_ui_dic['amount'].setCheckBox( false );
					}
				}
				this.current_edit_record['amount'] = this.edit_view_ui_dic['amount'].getValue();
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onTypeChange: function() {
		if ( this.current_edit_record.type_id == 10 ) {
			this.detachElement( 'percent_amount' );
			this.detachElement( 'percent_amount_entry_name_id' );
			this.attachElement( 'rate' );
			this.attachElement( 'units' );
			this.attachElement( 'amount' );

		} else if ( this.current_edit_record.type_id == 20 ) {
			this.attachElement( 'percent_amount' );
			this.attachElement( 'percent_amount_entry_name_id' );
			this.detachElement( 'rate' );
			this.detachElement( 'units' );
			this.detachElement( 'amount' );
		}

		this.editFieldResize();
	},

	calcAmount: function() {
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		var widget_amount = this.edit_view_ui_dic['amount'];

		if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {
			//widget_amount.setValue( ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ).toFixed( 2 ) ); //This fails on 17.07 * 9.50 as it rounds to 162.16 rather than 162.17
			//calc_amount = ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ); //This fails on 16.5 * 130.23
			var calc_amount = new Decimal( parseFloat( widget_rate.getValue() ) ).mul( parseFloat( widget_units.getValue() ) ).toFixed( 4 ); //Need to use Decimal() class for proper money math operations
			Debug.Text( 'Calculate Amount before rounding: ' + calc_amount, 'PayStubAmendmentViewController.js', 'PayStubAmendmentViewController', 'onFormItemKeyUp', 10 );

			var round_decimal_places;
			if ( this.currency_array.round_decimal_places ) {
				round_decimal_places = this.currency_array.round_decimal_places;
			} else {
				round_decimal_places = 2;
			}
			widget_amount.setValue( Global.MoneyRound( calc_amount, round_decimal_places ) );
		} else {
			if ( widget_amount.getValue() == '' ) {
				widget_amount.setValue( '0.00' );
			}
		}

		if ( !this.is_mass_editing ) { //Make sure this is only done when editing a single record otherwise Mass Edit will default to changing the amount to 0.00.
			this.current_edit_record['amount'] = this.edit_view_ui_dic['amount'].getValue(); //Update current record Amount, otherwise edit/save (without any changes) won't save the rounded value.
		}
	},

	onRateOrUnitChange: function() {
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		var widget_amount = this.edit_view_ui_dic['amount'];

		if ( widget_rate.getValue().length > 0 || widget_units.getValue().length > 0 ) {
			widget_amount.setReadOnly( true );
		} else {
			widget_amount.setReadOnly( false );
		}
	},

	onFormItemKeyUp: function( target ) {
		this.onRateOrUnitChange();
		this.calcAmount();
	},

	onFormItemKeyDown: function( target ) {
		this.onRateOrUnitChange();
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.import_icon:
				this.onImportClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.edit_employee:
				this.onNavigationClick( id );
				break;
		}
	},

	onImportClick: function() {

		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'paystubamendment', function() {
			$this.search();
		} );
	},
	/* jshint ignore:start */
	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.effective_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.effective_date;
			} );
		}

		var args = { filter_data: { id: ids } };

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Amendments' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.pay_stub:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Amendments' ), window.location.href );
					IndexViewController.goToView( 'PayStub', filter );
				}
				break;
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
		}

	},
	/* jshint ignore:end */
	onReportMenuClick: function( id ) {
		this.onReportPrintClick( id );
	},

	//not currently called. are we reimplementing the eft code commented out above in this class?
	doFormIFrameCall: function( postData ) {
		Global.APIFileDownload( this.api.className, 'get' + this.api.key_name, postData );
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
						$this.currency_array = res[0];
						$this.calcAmount();
					} else {
						$this.currency_array = null;
					}
				}
			} );
		}
	},

	setCurrentEditRecordData: function() {
		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'type_id': 10
		} );

		this._super( 'setCurrentEditRecordData' );
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.setCurrency();
		this.onTypeChange();
		this.onRateOrUnitChange();
	},

	validate: function() {

		var $this = this;

		var record = {};

		var records_data = null;

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				//#2536 - Never send status_id to the API.
				if ( key != 'status_id' ) {
					var widget = this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							record[key] = widget.getValue();
						}

					}
				}
			}

		} else {
			record = this.uniformVariable( this.current_edit_record );
		}

		var record = this.buildMassAddRecord( record );

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	},
	removeEditView: function() {
		this.is_mass_adding = false;
		this._super( 'removeEditView' );
	},

	buildMassAddRecord: function( record ) {
		if ( $.isArray( record.user_id ) ) {
			var records_data = [];
			var length = record.user_id.length;
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
				this.setEditMenu();

				return this.uniformVariable( records_data );

			} else {
				record.user_id = record.user_id.toString();
			}

		}

		return this.uniformVariable( record );
	},

	onSaveAndContinue: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';

		var record = this.buildMassAddRecord( this.current_edit_record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	},

	doSaveAPICall: function( record, ignoreWarning ) {
		// #2644: We have to handle the record as though its a mass_add, as the awesomebox will always return an array of user_id's. Cannot force is_mass_adding, as this affects the save&continue button disabling.
		record = this.buildMassAddRecord( record );
		this._super( 'doSaveAPICall', record, ignoreWarning );
	},

	onSaveAndCopy: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var records_data = null;
		this.clearNavigationData();

		var record = this.buildMassAddRecord( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;

					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search( false );
					$this.onCopyAsNewClick();
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	onSaveAndNewClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';

		var records_data = null;

		var record = this.buildMassAddRecord( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;

					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search( false );
					$this.onAddClick( true );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_adding || this.is_mass_editing || this.is_viewing || ( this.current_edit_record && Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1 ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;
		var allow_multiple_selection = false;

		var tab_model = {
			'tab_pay_stub_amendment': { 'label': $.i18n._( 'Pay Stub Amendment' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubAmendment' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_AMENDMENT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub_amendment = this.edit_view_tab.find( '#tab_pay_stub_amendment' );

		var tab_pay_stub_amendment_column1 = tab_pay_stub_amendment.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_amendment_column1 );

		if ( this.is_add ) {
			allow_multiple_selection = true;
		}

		//Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
			allow_multiple_selection: allow_multiple_selection,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_pay_stub_amendment_column1, '' );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [10, 20, 30, 50, 60, 65, 80];
		args.filter_data = filter_data;

		// Pay Stub Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_name_id',
			validation_field: 'pay_stub_entry_name'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Amount Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Amount Type' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Fixed

		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'rate', width: 114, hasKeyEvent: true } );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true, null, null, true );

		// Units
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'units', width: 114, hasKeyEvent: true } );
		this.addEditFieldToColumn( $.i18n._( 'Units' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true, null, null, true );

		// Amount

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'amount', width: 114 } );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Percent

		//Percent
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'percent_amount', width: 79 } );
		this.addEditFieldToColumn( $.i18n._( 'Percent' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		args = {};
		filter_data = {};
		filter_data.type_id = [10, 20, 30, 40, 50, 60, 65];
		args.filter_data = filter_data;

		// Percent of
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'percent_amount_entry_name_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Percent of' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_pay_stub_amendment_column1 );

		form_item_input.parent().width( '45%' );
		// Description (Private)

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'private_description' } );
		this.addEditFieldToColumn( $.i18n._( 'Description (Private)' ), form_item_input, tab_pay_stub_amendment_column1, '', null, null, true );

		// Effective Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'effective_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Effective Date' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Year to Date (YTD) Adjustment -- DISABLED
		//form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		//form_item_input.TCheckbox( {field: 'ytd_adjustment'} );
		//this.addEditFieldToColumn( $.i18n._( 'Year to Date (YTD) Adjustment' ), form_item_input, tab_pay_stub_amendment_column1, '' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';

		this.search_fields = [

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
				label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: ( APIFactory.getAPIClass( 'APIPayPeriod' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Pay Stub Account' ),
				in_column: 1,
				field: 'pay_stub_entry_name_id',
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: ( APIFactory.getAPIClass( 'APIUserTitle' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee Status' ),
				in_column: 2,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
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
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	uniformVariable: function( data ) {
		if ( data.status_id ) {
			delete data.status_id;
		}
		return this._super( 'uniformVariable', data );
	},

	copyAsNewResetIds: function( data ) {
		data = this.uniformVariable( data );
		data.id = null;
		data.effective_date = ( new Date ).format( Global.getLoginUserDateFormat() );
		return data;
	}

} );
