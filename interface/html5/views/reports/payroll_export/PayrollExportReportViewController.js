PayrollExportReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIPayrollExportReport', 'APITimeSheetVerify', 'APICurrency'],

	export_type_array: null,

	export_policy_array: null,

	export_setup_ui_dic: null,

	export_setup_data: null,

	export_grid: null,

	select_grid_last_row: null,

	save_export_setup_data: {},

	initReport: function( options ) {
		this.script_name = 'PayrollExportReport';
		this.viewId = 'PayrollExportReport';
		this.context_menu_name = $.i18n._( 'Payroll Export' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PayrollExportReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayrollExportReport' ))();
		this.include_form_setup = true;
		this.export_setup_data = {};
	},

	initOptions: function( callBack ) {
		var $this = this;
		var options = [
			{ option_name: 'page_orientation' },
			{ option_name: 'font_size' },
			{ option_name: 'chart_display_mode' },
			{ option_name: 'chart_type' },
			{ option_name: 'templates' },
			{ option_name: 'setup_fields' },
			{ option_name: 'export_type' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {

			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.

		} );

	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			groups: {
				form: {
					label: $.i18n._( 'Export' ), // Export is deliberate (rather than Form) due to export setup tab
					id: this.viewId + 'Form'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Export' ),
					id: ContextMenuIconName.export_export,
					group: 'editor',
					icon: Icons.export_export,
					sort_order: 1790 // To show icon before excel_export icon at 1800
				},
				{
					label: $.i18n._( 'Save Setup' ),
					id: ContextMenuIconName.save_setup,
					group: 'form',
					icon: Icons.save_setup
				}
			]
		};

		return context_menu_model;
	},

	onTabIndexChange: function() {

		// Don't do anything in this sub class
	},

	setEditMenuViewIcon: function() {
		// Don't do anything in this sub class
	},

	/* jshint ignore:start */
	onContextMenuClick: function( context_btn, menu_name ) {
		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		if ( this.select_grid_last_row ) {
			this.export_grid.grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		var message_override = $.i18n._( 'Setup data for this report has not been configured yet. Please click on the Export Setup tab to do so now.' );

		switch ( id ) {
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick( null, false, message_override );
				break;
			case ContextMenuIconName.view_html:
				ProgressBar.showOverlay();
				this.onViewClick( 'html', false, message_override );
				break;
				ProgressBar.showOverlay();
			case ContextMenuIconName.view_html_new_window:
				this.onViewClick( 'html', false, message_override );
				break;
			case ContextMenuIconName.export_excel:
				this.onViewExcelClick( message_override );
				break;
			case ContextMenuIconName.export_export:
				ProgressBar.showOverlay();
				this.onViewClick( 'payroll_export', false, message_override );
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.save_existed_report: //All report view
				this.onSaveExistedReportClick();
				break;
			case ContextMenuIconName.save_new_report: //All report view
				this.onSaveNewReportClick();
				break;
			case ContextMenuIconName.timesheet_view: //All report view
				ProgressBar.showOverlay();
				this.onViewClick( 'pdf_timesheet' );
				break;
			case ContextMenuIconName.timesheet_view_detail: //All report view
				ProgressBar.showOverlay();
				this.onViewClick( 'pdf_timesheet_detail' );
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup( $.i18n._( 'Export setup' ) );
				break;
		}
		Global.triggerAnalyticsContextMenuClick( context_btn, menu_name );
	},
	/* jshint ignore:end */
	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Export Format

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'export_type', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.export_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Export Format' ), form_item_input, tab3_column1, '' );

		form_item_input.bind( 'formItemChange', function( e, target ) {
			$this.onExportChange( target.getValue() );
		} );

	},

	onExportChange: function( type ) {

		var $this = this;

		this.removeCurrentExportUI();

		ProgressBar.showOverlay(); //End when set grid data complete

		this.api.getOptions( 'export_policy', {
			noCache: true, onResult: function( result ) {
				$this.export_policy_array = result.getResult();

				switch ( type ) {
					case 'adp':
					case 'adp_advanced':
						$this.api.getOptions( 'adp_hour_column_options', {
							onResult: function( result ) {
								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result.getResult() );
							}
						} );
						break;
					case 'adp_resource':
						$this.api.getOptions( 'adp_resource_hour_column_options', {
							onResult: function( result ) {
								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result.getResult() );
							}
						} );
						break;
					case 'accero':
						$this.api.getOptions( 'accero_hour_column_options', {
							onResult: function( result ) {
								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result.getResult() );
							}
						} );
						break;
					case 'va_munis':
						$this.api.getOptions( 'export_columns', true, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data.hasOwnProperty( '0' ) ) {
									result_data[0] = '-- Custom --';
								}

								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result_data );
							}
						} );
						break;
					case 'ceridian_insync':
					case 'paychex_preview_advanced_job':
					case 'paychex_preview':
					case 'quickbooks':
					case 'quickbooks_advanced':
					case 'csv':
					case 'csv_advanced':
					case 'sage_50':
					case 'meditech':
						$this.buildAdditionalInputBox( type );
						$this.buildGrid( type );
						break;
					case 'cms_pbj':
						$this.api.getOptions( 'cms_pbj_hour_column_options', {
							onResult: function( result ) {
								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result.getResult() );
							}
						} );
						break;
					default:
						$this.buildGrid( type );
						break;
				}

			}
		} );

	},

	/* jshint ignore:start */
	buildGrid: function( type, columnOptions ) {
		if ( typeof type == 'undefined' || type == 0 ) { //on first load and when user selects "choose one" we want to drop the grid
			if ( this.export_grid ) {
				var new_grid = $( '<table id=\'export_grid\'>' );
				this.export_grid.grid.jqGrid( 'GridUnload' );
				this.export_grid.grid.replaceWith( new_grid );
				this.export_grid = null;
			}
			return;
		}

		var $this = this;
		var column_info_array = [];
		var column_options_string = '';

		var column_info = {
			name: 'column_id',
			index: 'column_id',
			label: $.i18n._( 'Hours' ),
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		var hour_code_label = '';

		switch ( type ) {
			case 'adp':
			case 'adp_advanced':

				columnOptions = Global.buildRecordArray( columnOptions.adp_hour_column_options );
				for ( var i = 0; i < columnOptions.length; i++ ) {
					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}
				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: $.i18n._( 'ADP Hours' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: column_options_string }
				};
				column_info_array.push( column_info );

				hour_code_label = $.i18n._( 'ADP Hours Code' );
				break;
			case 'adp_resource':
				columnOptions = Global.buildRecordArray( columnOptions.adp_resource_hour_column_options );
				for ( var i = 0; i < columnOptions.length; i++ ) {
					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}
				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: $.i18n._( 'ADP Hours' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: column_options_string }
				};
				column_info_array.push( column_info );

				hour_code_label = $.i18n._( 'ADP Hours Code' );
				break;
			case 'accero':
				columnOptions = Global.buildRecordArray( columnOptions.accero_hour_column_options );
				for ( var i = 0; i < columnOptions.length; i++ ) {
					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}
				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: $.i18n._( 'Hour Type' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: column_options_string }
				};
				column_info_array.push( column_info );

				hour_code_label = $.i18n._( 'HED Override #' ); //Hours Code
				break;
			case 'paychex_preview_advanced_job':
			case 'paychex_preview':
				hour_code_label = $.i18n._( 'Paychex Hours Code' );
				break;
			case 'paychex_online':
				hour_code_label = $.i18n._( 'Earning Code' );
				break;
			case 'ceridian_insync':
				hour_code_label = $.i18n._( 'Ceridian Hours Code' );
				break;
			case 'millenium':
				hour_code_label = $.i18n._( 'Millenium Hours Code' );
				break;
			case 'quickbooks_advanced':
			case 'quickbooks':
				hour_code_label = $.i18n._( 'Quickbooks Payroll Item Name' );
				break;
			case 'surepayroll':
				hour_code_label = $.i18n._( 'Payroll Code' );
				break;
			case 'chris21':
				hour_code_label = $.i18n._( 'Chris21 Hours Code' );
				break;
			case 'va_munis':
				columnOptions = Global.buildRecordArray( columnOptions );
				for ( i = 0; i < columnOptions.length; i++ ) {
					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}
				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: $.i18n._( 'Columns' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: column_options_string }
				};
				column_info_array.push( column_info );

				hour_code_label = $.i18n._( 'Hours Code' );
				break;
			case 'compupay':
				hour_code_label = $.i18n._( 'DET Code' );
				break;
			case 'sage_50':
				hour_code_label = $.i18n._( 'Item Number' );
				break;
			case 'cms_pbj':
				columnOptions = Global.buildRecordArray( columnOptions.cms_pbj_hour_column_options );
				for ( var i = 0; i < columnOptions.length; i++ ) {
					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}
				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: $.i18n._( 'Export' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: column_options_string }
				};
				column_info_array.push( column_info );

				hour_code_label = false;
				break;
			case 'meditech':
				hour_code_label = $.i18n._( 'Earning Number' );
				break;
			case 'csv':
			case 'csv_advanced':
				hour_code_label = $.i18n._( 'Hours Code' );
				break;
		}

		if ( hour_code_label !== false ) {
			column_info = {
				name: 'hour_code',
				index: 'hour_code',
				label: hour_code_label,
				width: 100,
				sortable: false,
				title: false,
				editable: true,
				edittype: 'text'
			};
			column_info_array.push( column_info );
		}

		if ( this.export_grid ) {
			this.export_grid.grid.jqGrid( 'GridUnload' );
			this.export_grid = null;

		}

		this.export_grid = new TTGrid( 'export_grid', {
			multiselect: false,
			winMultiSelect: false,
			sortable: false,
			editurl: 'clientArray',
			onSelectRow: function( id ) {
				if ( id ) {

					if ( $this.select_grid_last_row ) {
						$this.export_grid.grid.jqGrid( 'saveRow', $this.select_grid_last_row );
					}
					$this.export_grid.grid.jqGrid( 'editRow', id, true );
					$this.select_grid_last_row = id;
				}
			},
			onResizeGrid: false

		}, column_info_array );

		$this.setExportGridData( type ); //Set Grid size at final

	},
	/* jshint ignore:end */
	setExportGridData: function( type ) {
		var $this = this;

		var grid_data = Global.buildRecordArray( this.export_policy_array );
		var export_columns = null;
		var len = grid_data.length;
		var grid_source = [];

		this.api.getOptions( 'default_hour_codes', {
			noCache: true,
			onResult: function( result ) {

				var res_data = result.getResult();
				var default_columns = [];
				if ( res_data[type] && res_data[type].columns ) {
					default_columns = res_data[type].columns;
				}

				if ( $this.save_export_setup_data && $this.save_export_setup_data[type] && $this.save_export_setup_data[type].columns ) {
					export_columns = $this.save_export_setup_data[type].columns;
					doNext( export_columns, default_columns );
				} else if ( $this.export_setup_data && $this.export_setup_data[type] && $this.export_setup_data[type].columns ) {
					export_columns = $this.export_setup_data[type].columns;
					doNext( export_columns, default_columns );
				} else if ( res_data[type] && res_data[type].columns ) {
					doNext( default_columns );
				}

				$this.setExportGridSize();
			}
		} );

		function doNext( export_columns, default_columns ) {
			var hour_code;
			var hour_column;
			for ( var i = 0; i < len; i++ ) {
				var row = grid_data[i];
				var column_id = row.label;
				var export_column_value = export_columns[row.value];
				// Error: Uncaught TypeError: Cannot read property 'hour_column' of undefined in /interface/html5/#!m=Exception&sm=PayrollExportReport&sid=1726 line 523
				if ( Global.isSet( export_column_value ) == false ) {
					if ( default_columns && row.value && default_columns[row.value] ) {
						export_column_value = default_columns[row.value];
					} else {
						export_column_value = {};
					}
				}
				hour_column = export_column_value.hour_column;
				hour_code = export_column_value.hour_code;

				switch ( type ) {
					case 'adp':
					case 'adp_advanced':
					case 'adp_resource':
					case 'accero':
					case 'va_munis':
					case 'sage_50':
					case 'cms_pbj':
						if ( !hour_column ) {
							hour_column = '0';
						}

						var row_data = {
							id: i + 200,
							column_id: column_id,
							hour_column: hour_column,
							hour_code: hour_code,
							column_id_key: row.value
						};
						break;
					default:
						row_data = {
							id: i + 200,
							column_id: column_id,
							hour_code: hour_code,
							column_id_key: row.value
						};
						break;
				}

				grid_source.push( row_data );
			}

			$this.export_grid.setData( grid_source );
			ProgressBar.closeOverlay();

		}

	},
	/* jshint ignore:start */
	setExportGridSize: function() {
		if ( !this.export_grid || !this.export_grid.grid.is( ':visible' ) ) {
			return;
		}

		var tab3 = this.edit_view.find( '#form_setup_content_div' );
		var first_row = this.edit_view.find( '.first-row' );
		this.export_grid.grid.setGridWidth( this.edit_view.find('.inside-editor-div ').width() + 14 );
		$('#gbox_export_grid').css('overflow', 'hidden');
		this.export_grid.grid.setGridHeight( tab3.height() - first_row.height() );

	},
	/* jshint ignore:end */
	onTabShow: function( e, ui ) {
		var key = ui.newTab.index();

		$('.edit-view-form-item-label').css('width','auto');
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		var last_index = this.getEditViewTabIndex();


		if ( (last_index === 1 || this.need_refresh_display_columns) && $( e.target ).parent().index() === 0 ) {
			this.buildReportUIBaseOnSetupFields();
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( key === 1 ) {
			this.edit_view_ui_dic.setup_field.setValue( this.current_edit_record.setup_field );
			if ( Global.getProductEdition() == 10 ) {
				this.edit_view_ui_dic.auto_refresh.parent().parent().css( 'display', 'none' );
			}
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( key === 2 ) {
			if ( Global.getProductEdition() >= 15 ) {
				this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			} else {
				this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
			}
		} else if ( key === 3 ) {
			this.setExportGridSize();
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( key === 4 ) {
			if ( Global.getProductEdition() >= 15 ) {
				this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
				this.initSubCustomColumnView();
			} else {
				this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}
		} else if ( key === 5 ) {
			this.initSubSavedReportView();
		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

		this.checkFormSetupSaved( last_index, $.i18n._( 'Export Setup' ) );
	},

	buildAdditionalInputBox: function( type ) {
		if ( this.save_export_setup_data[type] ) {
			this.export_setup_data = this.save_export_setup_data[type];

			if ( !this.export_setup_data.columns && this.export_setup_data[type] ) {
				this.export_setup_data = this.export_setup_data[type];
				this.export_setup_data.export_type = type;
			}

		} else {
			this.export_setup_data = {};
		}

		var $this = this;

		this.export_setup_ui_dic = {};

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		switch ( type ) {
			case 'adp':
				//Company code
				var code = 'company_code';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				var h_box = $( '<div class=\'h-box\'></div>' );

				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Company Code' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_company_code_options', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Batch ID
				var code1 = 'batch_id';
				var form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( { field: code1 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( { field: code1 + '_text' } );

				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'Batch ID' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input1, text_box1] );
				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'adp_batch_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				// Temp Department

				var code2 = 'temp_dept';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Temp Department' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_temp_dept_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				break;
			case 'adp_advanced':
			case 'adp_resource': //ADP Resource/Pay Expert
				//Company code
				var code = 'company_code';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				var h_box = $( '<div class=\'h-box\'></div>' );

				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Company Code' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_company_code_options', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Batch ID
				var code1 = 'batch_id';
				var form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( { field: code1 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( { field: code1 + '_text' } );

				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'Batch ID' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input1, text_box1] );
				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'adp_batch_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				// Temp Department

				var code2 = 'temp_dept';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Temp Department' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_temp_dept_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				// Job Cost #
				var code3 = 'job_cost';
				var form_item_input3 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input3.TComboBox( { field: code3 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box3 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box3.css( 'margin-left', '10px' );
				text_box3.TTextInput( { field: code3 + '_text' } );

				h_box.append( form_item_input3 );
				h_box.append( text_box3 );
				this.addEditFieldToColumn( $.i18n._( 'Job Cost #' ), [form_item_input3, text_box3], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input3, text_box3] );
				form_item_input3.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box3.css( 'display', 'inline' );
						text_box3.setValue( $this.export_setup_data[code3] );
					} else {
						text_box3.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_job_cost_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input3.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input3.setValue( $this.export_setup_data[code3] );
						form_item_input3.trigger( 'formItemChange', [form_item_input3, true] );
					}
				} );
				$this.export_setup_ui_dic[code3] = $this.edit_view_form_item_dic[code3];
				delete $this.edit_view_form_item_dic[code3];

				// Work Class
				var code4 = 'work_class';
				var form_item_input4 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input4.TComboBox( { field: code4 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box4 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box4.css( 'margin-left', '10px' );
				text_box4.TTextInput( { field: code4 + '_text' } );

				h_box.append( form_item_input4 );
				h_box.append( text_box4 );
				this.addEditFieldToColumn( $.i18n._( 'Work Class' ), [form_item_input4, text_box4], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input4, text_box4] );
				form_item_input4.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box4.css( 'display', 'inline' );
						text_box4.setValue( $this.export_setup_data[code4] );
					} else {
						text_box4.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_work_class_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input4.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input4.setValue( $this.export_setup_data[code4] );
						form_item_input4.trigger( 'formItemChange', [form_item_input4, true] );
					}
				} );
				$this.export_setup_ui_dic[code4] = $this.edit_view_form_item_dic[code4];
				delete $this.edit_view_form_item_dic[code4];

				//State
				var code7 = 'state_columns';
				var form_item_input7 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input7.TComboBox( {
					field: code7,
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'State' ), form_item_input7, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input7] );
				new (APIFactory.getAPIClass( 'APIJobDetailReport' ))().getOptions( 'static_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input7.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input7.setValue( $this.export_setup_data[code7] );
					}
				} );

				$this.export_setup_ui_dic[code7] = $this.edit_view_form_item_dic[code7];
				delete $this.edit_view_form_item_dic[code7];

				break;
			case 'accero':
				// Temp Department
				var code2 = 'temp_dept';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Department Override Code' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'accero_temp_dept_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				break;
			case 'va_munis':
				//Department
				code = 'department';
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				h_box = $( '<div class=\'h-box\'></div>' );

				text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Department' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();

						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}
						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Employee Number
				code1 = 'employee_number';
				form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( { field: code1 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( { field: code1 + '_text' } );

				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input1, text_box1] );
				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				// Long GL Account

				code2 = 'gl_account';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Long GL Account' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();

						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}

						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				break;
			case 'ceridian_insync':
				// Employer Number
				code = 'employer_number';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code } );
				this.addEditFieldToColumn( $.i18n._( 'Employer Number' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			case 'paychex_preview':
				// Client Number
				code = 'client_number';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code } );
				this.addEditFieldToColumn( $.i18n._( 'Client Number' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			case 'paychex_preview_advanced_job':
				// Client Number
				code = 'client_number_adv';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code } );
				this.addEditFieldToColumn( $.i18n._( 'Client Number' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data['client_number'] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Job
				code1 = 'job_columns';
				form_item_input1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input1.AComboBox( {
					field: code1,
					allow_multiple_selection: true,
					layout_name: ALayoutIDs.OPTION_COLUMN,
					key: 'value',
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'Job' ), form_item_input1, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input1] );
				new (APIFactory.getAPIClass( 'APIJobDetailReport' ))().getOptions( 'static_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				//State
				code2 = 'state_columns';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {
					field: code2,
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'State' ), form_item_input2, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input2] );
				new (APIFactory.getAPIClass( 'APIJobDetailReport' ))().getOptions( 'static_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				// Include Override Rates
				code = 'include_hourly_rate';
				form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
				form_item_input.TCheckbox( { field: code } );
				this.addEditFieldToColumn( $.i18n._( 'Include Override Rates' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			case 'quickbooks':
			case 'quickbooks_advanced':
				// Company Name
				code = 'company_name';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code } );

				var containerWithTextTip = this.buildWidgetContainerWithTextTip( form_item_input, '(Exactly as shown in Quickbooks)' );

				this.addEditFieldToColumn( $.i18n._( 'Company Name' ), form_item_input, tab3_column1, '', containerWithTextTip, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				// Company Created Time
				code = 'company_created_date';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code } );

				containerWithTextTip = this.buildWidgetContainerWithTextTip( form_item_input, '(Exactly as shown in exported timer list)' );

				this.addEditFieldToColumn( $.i18n._( 'Company Created Time' ), form_item_input, tab3_column1, '', containerWithTextTip, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Map PROJ Field To
				code1 = 'proj';
				form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( {
					field: code1
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map PROJ Field To' ), form_item_input1, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input1] );
				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				//Map ITEM Field To
				code2 = 'item';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {
					field: code2
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map ITEM Field To' ), form_item_input2, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input2] );
				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				//Map ITEM Field To
				var code3 = 'job';
				var form_item_input3 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input3.TComboBox( {
					field: code3
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map JOB Field To' ), form_item_input3, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input3] );
				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input3.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input3.setValue( $this.export_setup_data[code3] );
					}
				} );

				$this.export_setup_ui_dic[code3] = $this.edit_view_form_item_dic[code3];
				delete $this.edit_view_form_item_dic[code3];

				break;
			case 'sage_50':
				//Company code
				var code = 'customer_name';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				var h_box = $( '<div class=\'h-box\'></div>' );

				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Customer Name' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'sage_50_customer_name_options', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			case 'cms_pbj':
				//Facility ID
				var code = 'facility_code';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				var h_box = $( '<div class=\'h-box\'></div>' );
				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );
				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Facility ID' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'cms_pbj_facility_code_options', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];


				//State
				var code1 = 'state_code';
				var form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( { field: code1 } );

				h_box = $( '<div class=\'h-box\'></div>' );
				var text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( { field: code1 + '_text' } );
				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'State' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input1, text_box1] );
				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'cms_obj_state_code_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];


				// Pay Type Code
				var code2 = 'pay_type_code';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );
				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Pay Type Code' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'cms_pbj_pay_type_code_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				// Job Title Code
				var code3 = 'job_title_code';
				var form_item_input3 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input3.TComboBox( { field: code3 } );

				h_box = $( '<div class=\'h-box\'></div>' );
				var text_box3 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box3.css( 'margin-left', '10px' );
				text_box3.TTextInput( { field: code3 + '_text' } );

				h_box.append( form_item_input3 );
				h_box.append( text_box3 );
				this.addEditFieldToColumn( $.i18n._( 'Job Title Code' ), [form_item_input3, text_box3], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input3, text_box3] );
				form_item_input3.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box3.css( 'display', 'inline' );
						text_box3.setValue( $this.export_setup_data[code3] );
					} else {
						text_box3.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'cms_pbj_job_title_code_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input3.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input3.setValue( $this.export_setup_data[code3] );
						form_item_input3.trigger( 'formItemChange', [form_item_input3, true] );
					}
				} );
				$this.export_setup_ui_dic[code3] = $this.edit_view_form_item_dic[code3];
				delete $this.edit_view_form_item_dic[code3];

				break;
			case 'meditech': //Meditech
				//Payroll dictionary value
				code4 = 'payroll';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: code4 } );
				this.addEditFieldToColumn( $.i18n._( 'Payroll' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				form_item_input.setValue( $this.export_setup_data[code4] );

				$this.export_setup_ui_dic[code4] = $this.edit_view_form_item_dic[code4];
				delete $this.edit_view_form_item_dic[code4];

				//Employee Number
				var code = 'employee_number';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: code } );

				var h_box = $( '<div class=\'h-box\'></div>' );

				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( { field: code + '_text' } );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), [form_item_input, text_box], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input, text_box] );
				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'export_columns', 'meditech', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Department
				var code2 = 'department';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( { field: code2 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( { field: code2 + '_text' } );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Department' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input2, text_box2] );
				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'meditech_department_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				// Job Code
				var code3 = 'job_code';
				var form_item_input3 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input3.TComboBox( { field: code3 } );

				h_box = $( '<div class=\'h-box\'></div>' );

				var text_box3 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box3.css( 'margin-left', '10px' );
				text_box3.TTextInput( { field: code3 + '_text' } );

				h_box.append( form_item_input3 );
				h_box.append( text_box3 );
				this.addEditFieldToColumn( $.i18n._( 'Job Code' ), [form_item_input3, text_box3], tab3_column1, '', h_box, true );
				this.setWidgetVisible( [form_item_input3, text_box3] );
				form_item_input3.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box3.css( 'display', 'inline' );
						text_box3.setValue( $this.export_setup_data[code3] );
					} else {
						text_box3.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'meditech_job_code_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input3.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input3.setValue( $this.export_setup_data[code3] );
						form_item_input3.trigger( 'formItemChange', [form_item_input3, true] );
					}
				} );
				$this.export_setup_ui_dic[code3] = $this.edit_view_form_item_dic[code3];
				delete $this.edit_view_form_item_dic[code3];

				break;
			case 'csv_advanced':
				//Export Columns
				code = 'csv_export_columns';
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					field: code,
					allow_multiple_selection: true,
					layout_name: ALayoutIDs.OPTION_COLUMN,
					key: 'value',
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'Export Columns' ), form_item_input, tab3_column1, '', null, true );
				this.setWidgetVisible( [form_item_input] );
				this.api.getOptions( 'export_columns', 'csv_advanced', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input.setValue( $this.export_setup_data[code] );
					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			default:
				break;
		}

		this.editFieldResize( 3 );
	},

	buildEditViewUI: function() {
		this.__super( 'buildEditViewUI' );

		var tab_3_label = this.edit_view.find( 'a[ref=tab_form_setup]' );
		tab_3_label.text( $.i18n._( 'Export Setup' ) );
	},

	removeCurrentExportUI: function() {

		for ( var key in this.export_setup_ui_dic ) {
			var html_item = this.export_setup_ui_dic[key];
			html_item.remove();
		}

		//Error: Unable to get property 'find' of undefined or null reference in /interface/html5/ line 1033
		if ( !this.edit_view_tab ) {
			return;
		}

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );
		var tab3_column1 = tab3.find( '.first-column' );
		var clear_both_div = tab3_column1.find( '.clear-both-div' );

		clear_both_div.remove();
	},

	getExportColumns: function( type ) {
		var columns = {};

		if ( this.export_grid ) { //#2490 - can't return export columns if there's no export grid.
			var source = this.export_grid.getData();
			var len = source.length;
			for ( var i = 0; i < len; i++ ) {
				var item = source[i];
				columns[item.column_id_key] = {};
				columns[item.column_id_key].hour_code = item.hour_code;

				if ( type === 'adp' || type === 'adp_advanced' || type === 'adp_resource' || type === 'accero' || type === 'va_munis' || type === 'cms_pbj' ) {
					columns[item.column_id_key].hour_column = item.hour_column;
				}

			}
		}

		return columns;

	},

	/**
	 * Gets array of properly configured values for the export setup form.
	 *
	 * @param field_list Array
	 * @returns {{}|*}
	 */
	getFormSetupFieldValues: function( field_list ) {
		ret_arr = {};

		for ( var i = 0; i < field_list.length; i++ ) {
			if ( this.edit_view_ui_dic[field_list[i]] && !this.edit_view_ui_dic[field_list[i]].getValue() ) {
				ret_arr[field_list[i]] = this.edit_view_ui_dic[field_list[i] + '_text'].getValue();
				ret_arr[field_list[i] + '_value'] = this.edit_view_ui_dic[field_list[i] + '_text'].getValue();
			} else {
				if ( !this.edit_view_ui_dic[field_list[i]] ) {
					ret_arr[field_list[i]] = '';
				}
				else {
					ret_arr[field_list[i]] = this.edit_view_ui_dic[field_list[i]].getValue();
				}
			}
		}
		return ret_arr;
	},

	getFormData: function( other, for_display ) {
		if ( !other || ! other.export_type ) {
			return false;
		}

		switch ( other.export_type ) {
			case 'adp':
				other[other.export_type] = this.getFormSetupFieldValues( ['company_code', 'batch_id', 'temp_dept'] );
				break;
			case 'adp_advanced':
			case 'adp_resource':
				other[other.export_type] = this.getFormSetupFieldValues( ['company_code', 'batch_id', 'temp_dept', 'job_cost', 'work_class'] );
				other[other.export_type].state_columns = this.edit_view_ui_dic.state_columns.getValue();
				break;
			case 'accero':
				other[other.export_type] = this.getFormSetupFieldValues( ['temp_dept'] );
				break;
			case 'paychex_preview':
				other[other.export_type].client_number = this.edit_view_ui_dic.client_number.getValue();
				break;
			case 'paychex_preview_advanced_job':
				other[other.export_type].client_number = this.edit_view_ui_dic.client_number_adv.getValue();
				other[other.export_type].job_columns = this.edit_view_ui_dic.job_columns.getValue();
				other[other.export_type].state_columns = this.edit_view_ui_dic.state_columns.getValue();
				other[other.export_type].include_hourly_rate = this.edit_view_ui_dic.include_hourly_rate.getValue();
				break;
			case 'ceridian_insync':
				other[other.export_type].employer_number = this.edit_view_ui_dic.employer_number.getValue();
				break;
			case 'quickbooks':
			case 'quickbooks_advanced':
				other[other.export_type].company_name = this.edit_view_ui_dic.company_name.getValue();
				other[other.export_type].company_created_date = this.edit_view_ui_dic.company_created_date.getValue();
				other[other.export_type].proj = this.edit_view_ui_dic.proj.getValue();
				other[other.export_type].item = this.edit_view_ui_dic.item.getValue();
				other[other.export_type].job = this.edit_view_ui_dic.job.getValue();
				break;
			case 'sage_50':
				other[other.export_type] = this.getFormSetupFieldValues( ['customer_name'] );
				break;
			case 'va_munis':
				other[other.export_type] = this.getFormSetupFieldValues( ['department', 'employee_number', 'gl_account', 'customer_name', 'facility_code', 'state_code', 'pay_type_code', 'job_title_code', ''] );
				break;
			case 'meditech':
				other[other.export_type] = this.getFormSetupFieldValues( ['employee_number', 'department', 'job_code'] );
				other[other.export_type].payroll = this.edit_view_ui_dic.payroll.getValue();
				break;
			case 'csv_advanced':
				other[other.export_type].export_columns = this.edit_view_ui_dic.csv_export_columns.getValue();
				break;
			case 'cms_pbj':
				other[other.export_type] = this.getFormSetupFieldValues( ['facility_code', 'state_code', 'pay_type_code', 'job_title_code'] );
				break;
		}

		if ( !this.save_export_setup_data ) {
			this.save_export_setup_data = {};
		}
		this.save_export_setup_data[other.export_type] = other[other.export_type];
		this.save_export_setup_data[other.export_type]['columns'] = this.getExportColumns( other.export_type ); //This is needed for the api to build reports properly.
		this.save_export_setup_data['export_type'] = other.export_type;

		if ( for_display ) {
			for ( key in this.save_export_setup_data ) {
				if ( key !== false && typeof(this.save_export_setup_data[key]) !== 'String' ) {
					this.save_export_setup_data[key] = this.convertExportSetupValues( this.save_export_setup_data[key] );
				}
			}
		}

		return other;
	},

	/* jshint ignore:start */
	getFormSetupData: function( for_view ) {
		var other = {};
		other.export_type = this.edit_view_ui_dic.export_type.getValue();

		other[other.export_type] = {};
		other[other.export_type].columns = this.getExportColumns( other.export_type );

		other = this.getFormData( other, true );

		if ( !for_view && other.export_type ) {
			var export_type = other.export_type;
			other = other[export_type];
			other.export_type = export_type;
			other[export_type] = {};
			other[export_type].columns = this.getExportColumns( other.export_type );
		}

		return other;
	},

	/* jshint ignore:end */

	/**
	 * Backwards compatible function for custom data to be moved from the way the api stores it to the way the form needs it.
	 *
	 * the old custom field data was stored in obj[key]
	 * new custom field data is stored in obj[key+'_value']
	 *
	 * ie. obj[company_code] is now obj[company_code_value]
	 *
	 * @param data
	 * @returns {*}
	 */
	convertExportSetupValues: function( data ) {
		for ( var api_data_key in data ) {
			var form_data_key = api_data_key.substr( 0, api_data_key.indexOf( '_value' ) );
			if ( api_data_key.search( '_value' ) > 0 ) {
				data[form_data_key] = data[api_data_key];
			}
		}
		//conversion for lower export grid data from old format
		if ( data.export_columns && !data.columns && data.export_type != 0 && data.export_columns[data.export_type] ) {
			data.columns = {};
			data.columns = data.export_columns[data.export_type].columns;
		}

		return data;
	},

	/**
	 * Get the form setup data from the api
	 * @param res_Data
	 */
	setFormSetupData: function( res_Data ) {
		//this if is for backwards compatibility

		if ( this.edit_view_ui_dic.export_type && this.edit_view_ui_dic.export_type.getValue() ) {
			res_Data.export_type = this.edit_view_ui_dic.export_type.getValue();
		}

		if ( !res_Data.export_columns ) {
			for ( key in res_Data ) {
				if ( key !== false && typeof(res_Data[key]) !== 'String' ) {
					res_Data[key] = this.convertExportSetupValues( res_Data[key] );
				}
			}
			this.save_export_setup_data = res_Data;
		} else {
			res_Data = this.convertExportSetupValues( res_Data );
			this.save_export_setup_data[res_Data.export_type] = res_Data;
			this.save_export_setup_data['export_type'] = res_Data.export_type;
		}

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {

			if ( res_Data.export_type ) {
				this.edit_view_ui_dic.export_type.setValue( res_Data.export_type );
				this.current_edit_record.export_type = res_Data.export_type;
			}
		}

		//for backwards compatibility with old csv_advanced format
		if ( this.save_export_setup_data['csv_advanced'] ) {
			if ( this.save_export_setup_data['csv_advanced'].csv_export_columns ) {
				this.save_export_setup_data['csv_advanced'].export_columns = this.save_export_setup_data['csv_advanced'].csv_export_columns;
			} else {
				this.save_export_setup_data['csv_advanced'].csv_export_columns = this.save_export_setup_data['csv_advanced'].export_columns;
			}
		}


		this.onExportChange( res_Data.export_type );
	},

	/**
	 * Overridden to allow stateful export formats. This ensures your changes are put into memory..
	 *
	 * @param target
	 * @param doNotDoValidate
	 */
	onFormItemChange: function( target, doNotDoValidate ) {
		var $this = this;

		//If the edit grid has left any rows in edit mode, we need to finalize them now before the data is swept into memory.
		selRowId = $( '#export_grid' ).getGridParam( 'selrow' );
		$( '#export_grid' ).saveRow( selRowId );

		if ( target && target.getField && target.getField() == 'export_type' ) { // cannot read property getField of undefined
			var other = {};
			other.export_type = this.current_edit_record.export_type;
			other[other.export_type] = {};
			other[other.export_type].export_columns = { columns: this.getExportColumns( other.export_type ) };

			if ( !this.export_setup_data.export_columns ) {
				this.export_setup_data.export_columns = {};
				this.export_setup_data.export_columns[other.export_type] = {};
			}

			this.export_setup_data.export_columns[other.export_type] = { columns: this.getExportColumns( other.export_type ) };
			// this.save_export_setup_data[other.export_type] = this.getFormSetupData( other );
			this.form_setup_changed = true;
			return; //make room for the custom event above
		}
		this.__super( 'onFormItemChange', target, doNotDoValidate );
	},

} );
