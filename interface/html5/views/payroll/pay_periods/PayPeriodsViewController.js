export class PayPeriodsViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_periods_view_container',

			status_array: null,
			type_array: null,
			pay_period_schedule_api: null
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'PayPeriodsEditView.html';
		this.permission_id = 'pay_period_schedule';
		this.script_name = 'PayPeriodsView';
		this.viewId = 'PayPeriods';
		this.table_name_key = 'pay_period';
		this.context_menu_name = $.i18n._( 'Pay Period' );
		this.navigation_label = $.i18n._( 'Pay Period' ) + ':';
		this.api = TTAPI.APIPayPeriod;
		this.pay_period_schedule_api = TTAPI.APIPayPeriodSchedule;

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

		IndexViewController.setNotificationBar( 'pay_period' );
	}

	removeEditView( is_cancel ) {

		super.removeEditView();

		if ( this.parent_view_controller &&
			( this.parent_view_controller.viewId === 'TimeSheet' || this.parent_view_controller.viewId === 'PayStub' ) ) {
			this.parent_view_controller.onSubViewRemoved( is_cancel );
		}
	}

	initOptions( callBack ) {
		var $this = this;

		var options = [
			{ option_name: 'status', field_name: null, api: null },
			{ option_name: 'type', field_name: 'type_id', api: this.pay_period_schedule_api }
		];

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.copy],
			include: [
				{
					label: $.i18n._( 'Delete<br>Data' ),
					id: ContextMenuIconName.delete_data,
					group: 'other',
					icon: Icons.delete_icon
				},
				{
					label: $.i18n._( 'Import<br>Data' ),
					id: ContextMenuIconName.import_icon,
					group: 'other',
					icon: Icons.import_icon,
					sort_order: 8000
				}
			]
		};

		if ( this.edit_only_mode ) {
			context_menu_model.exclude.push( ContextMenuIconName.import_icon, ContextMenuIconName.delete_data );
		}

		return context_menu_model;
	}

	openEditView( id ) {

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}

				$this.getPayPeriodData( id, function( result ) {
					// Waiting for the TTAPI.API returns data to set the current edit record.
					$this.current_edit_record = result;

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

		}
	}

	/* jshint ignore:start */
	setDefaultMenu( doNotSetFocus ) {

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
				case ContextMenuIconName.delete_data:
					this.setDefaultMenuDeleteDataIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();
	}

	/* jshint ignore:end */
	setDefaultMenuImportIcon( context_btn, grid_selected_length ) {

		if ( !this.importValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	}

	importValidate( selected_item ) {
		var p_id = this.permission_id;

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.editPermissionValidate( p_id, selected_item ) || this.addPermissionValidate( p_id, selected_item ) ) {
			return true;
		}

		return false;
	}

	setDefaultMenuDeleteDataIcon( context_btn, grid_selected_length ) {

		if ( !this.importValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	}

	/* jshint ignore:start */
	setEditMenu() {

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
				case ContextMenuIconName.delete_data:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();
	}

	/* jshint ignore:end */
	setEditMenuImportIcon( context_btn, pId ) {
		if ( !this.importValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}
		context_btn.addClass( 'disable-image' );
	}

	onCustomContextClick( context_btn ) {
		var $this = this;

		var grid_selected_id_array;

		var ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
			} );
		}

		if ( ids.length > 0 ) {

			switch ( context_btn ) {

				case ContextMenuIconName.import_icon:
					TAlertManager.showConfirmAlert( $.i18n._( 'This will import employee attendance data from other pay periods into this pay period.Are you sure you wish to continue?' ), null, function( result ) {

						if ( result ) {
							ProgressBar.showOverlay();
							$this.api.importData( ids, {
								onResult: function( res ) {
									ProgressBar.closeOverlay();
									if ( res.isValid() ) {
										$this.search( false );
									} else {
										TAlertManager.showErrorAlert( res );
									}
								}
							} );

						} else {
							ProgressBar.closeOverlay();
						}
					} );
					break;
				case ContextMenuIconName.delete_data:

					TAlertManager.showConfirmAlert( $.i18n._( 'This will delete all attendance data assigned to this pay period. Are you sure you wish to continue?' ), null, function( result ) {

						if ( result ) {
							ProgressBar.showOverlay();
							$this.api.deleteData( ids, {
								onResult: function( res ) {
									ProgressBar.closeOverlay();
									if ( res.isValid() ) {
										$this.search( false );
									} else {
										TAlertManager.showErrorAlert( res );
									}
								}
							} );

						} else {
							ProgressBar.closeOverlay();
						}
					} );
					break;
				case ContextMenuIconName.export_excel:
					this.onExportClick( 'export' + this.api.key_name );
					break;
			}

		}
	}

	getPayPeriodData( id, callBack ) {
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

	buildSearchFields() {
		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Pay Period Schedule' ),
				in_column: 1,
				field: 'pay_period_schedule_id',
				layout_name: 'global_pay_period_schedule',
				api_class: TTAPI.APIPayPeriodSchedule,
				multiple: true,
				basic_search: true,
				script_name: 'PayPeriodScheduleView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
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
				script_name: 'EmployeeView',
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
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_pay_period': { 'label': $.i18n._( 'Pay Period' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				id: this.script_name + '_navigation',
				api_class: TTAPI.APIPayPeriod,
				allow_multiple_selection: false,
				layout_name: 'global_Pay_period',
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start

		var tab_pay_period = this.edit_view_tab.find( '#tab_pay_period' );

		var tab_pay_period_column1 = tab_pay_period.find( '.first-column' );
		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_period_column1 );

		// Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_period_column1, '' );

		// Pay Period Schedule
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'pay_period_schedule' } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_pay_period_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayPeriodSchedule,
			allow_multiple_selection: false,
			layout_name: 'global_pay_period_schedule',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_period_schedule_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_pay_period_column1, '', null, true );

		// Start date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_pay_period_column1, '', null, true );

		// End date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'end_date', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_pay_period_column1, '', null, true );

		// Transaction date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'transaction_date', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Date' ), form_item_input, tab_pay_period_column1, '', null, true );
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		if ( !this.current_edit_record ) {
			this.current_edit_record = {};
		}

		this.current_edit_record[key] = c_value;

		if ( key === 'status_id' ) {
			this.onStatusChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setDateColumnStatus( value, disabled ) {
		if ( this.edit_view_ui_dic[value] ) {
			if ( disabled ) {
				this.edit_view_ui_dic[value].find( 'input' ).attr( 'disabled', 'disabled' );
				this.edit_view_ui_dic[value].find( 'img' ).unbind( 'click' );
			} else {
				this.edit_view_ui_dic[value].find( 'input' ).removeAttr( 'disabled' );
				this.edit_view_ui_dic[value].find( 'img' ).bind( 'click' );
			}
		}
	}

	onStatusChange() {
		//TypeError: Cannot read property 'status_id' of undefined
		if ( this.current_edit_record && this.current_edit_record['status_id'] == 20 ) {
			this.setDateColumnStatus( 'start_date', true );
			this.setDateColumnStatus( 'end_date', true );
			this.setDateColumnStatus( 'transaction_date', true );
		} else {
			this.setDateColumnStatus( 'start_date', false );
			this.setDateColumnStatus( 'end_date', false );
			this.setDateColumnStatus( 'transaction_date', false );
		}
	}

	isEditChange() {
		if ( this.current_edit_record && this.current_edit_record.id ) {
			this.attachElement( 'pay_period_schedule' );
			this.detachElement( 'pay_period_schedule_id' );
		} else if ( this.is_mass_editing ) {
			this.detachElement( 'pay_period_schedule' );
			this.detachElement( 'pay_period_schedule_id' );
		} else {
			this.detachElement( 'pay_period_schedule' );
			this.attachElement( 'pay_period_schedule_id' );

		}

		this.editFieldResize();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onStatusChange();
		this.isEditChange();
	}

}

PayPeriodsViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'PayPeriods', 'SubPayPeriodsView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_pay_periods_view_controller );
				} );
			}

		}

	} );

};