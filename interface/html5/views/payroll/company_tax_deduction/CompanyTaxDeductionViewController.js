CompanyTaxDeductionViewController = BaseViewController.extend( {
	el: '#company_tax_deduction_view_container', //Must set el here and can only set string, so events can work

	_required_files: {
		10: ['APICompanyDeduction', 'APIUser', 'APIUserDeduction', 'APIPayrollRemittanceAgency', 'APIPayStubEntryAccount', 'APILegalEntity', 'APIContributingPayCodePolicy'],
		20: ['APIDocument', 'APIDocumentGroup', 'APIDocumentRevision', 'APIDocumentAttachment']
	},

	type_array: null,
	status_array: null,
	tax_formula_type_array: null,
	calculation_array: null,
	account_amount_type_array: null,
	yes_no_array: null,
	federal_filing_status_array: null,
	state_basic_filing_status_array: null,
	apply_frequency_array: null,
	apply_payroll_run_type_array: null,
	length_of_service_unit_array: null,
	month_of_year_array: null,
	day_of_month_array: null,
	month_of_quarter_array: null,
	country_array: null,
	province_array: null,
	e_province_array: null,
	company_api: null,
	date_api: null,
	user_deduction_api: null,
	user_api: null,
	payroll_remittance_agency_api: null,
	employee_setting_grid: null,
	employee_setting_result: null,
	final_c_id: null, // calculation_id from setDynamic function. use to set employee setting grid
	show_c: false,
	show_p: false,
	show_dc: false,

	provice_district_array: null,

	original_current_record: null, //set when setCurrentEditRecordData, to keep the original data of the edit record

	length_dates: null,
	start_dates: null,
	end_dates: null,

	grid_parent: '.grid-div',

	init: function( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'CompanyTaxDeductionEditView.html';
		if ( this.sub_view_mode ) {
			this.permission_id = 'user_tax_deduction';
		} else {
			this.permission_id = 'company_tax_deduction';
		}
		this.viewId = 'CompanyTaxDeduction';
		this.script_name = 'CompanyTaxDeductionView';
		this.table_name_key = 'company_deduction';
		this.context_menu_name = $.i18n._( 'Tax / Deductions' );
		this.navigation_label = $.i18n._( 'Tax / Deductions' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APICompanyDeduction' ) )();
		this.date_api = new ( APIFactory.getAPIClass( 'APIDate' ) )();
		this.company_api = new ( APIFactory.getAPIClass( 'APICompany' ) )();
		this.user_deduction_api = new ( APIFactory.getAPIClass( 'APIUserDeduction' ) )();
		this.user_api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.payroll_remittance_agency_api = new ( APIFactory.getAPIClass( 'APIPayrollRemittanceAgency' ) )();
		this.month_of_quarter_array = Global.buildRecordArray( { 1: 1, 2: 2, 3: 3 } );
		this.document_object_type_id = 300;

		this.render();
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			//Load the FormulaBuilder as early as possible to help avoid some race conditions with input box not appearing, or appearing out of order when clicking "new" after a fresh reload.
			if ( ( Global.getProductEdition() >= 15 ) ) {
				Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js' );
			}

			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary();

	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.mass_edit],
			include: []
		};

		if ( this.sub_view_mode ) {
			context_menu_model.exclude.push(
				ContextMenuIconName.view,
				ContextMenuIconName.save_and_new,
				ContextMenuIconName.save_and_copy,
				ContextMenuIconName.copy_as_new,
				ContextMenuIconName.copy
			);
		}

		return context_menu_model;
	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'calculation' );
		this.initDropDownOption( 'apply_frequency' );
		this.initDropDownOption( 'apply_payroll_run_type' );
		this.initDropDownOption( 'account_amount_type' );
		this.initDropDownOption( 'account_amount_type' );
		this.initDropDownOption( 'length_of_service_unit' );
		this.initDropDownOption( 'look_back_unit' );
		this.initDropDownOption( 'country', 'country', this.company_api );
		this.initDropDownOption( 'apply_payroll_run_type' );

		this.initDropDownOption( 'yes_no' );

		this.initDropDownOption( 'form_w4_version' );
		this.initDropDownOption( 'federal_filing_status' );
		this.initDropDownOption( 'state_basic_filing_status' );
		this.initDropDownOption( 'state_dc_filing_status' );
		this.initDropDownOption( 'state_al_filing_status' );
		this.initDropDownOption( 'state_ct_filing_status' );
		this.initDropDownOption( 'state_dc_filing_status' );
		this.initDropDownOption( 'state_de_filing_status' );
		this.initDropDownOption( 'state_nj_filing_status' );
		this.initDropDownOption( 'state_nc_filing_status' );
		this.initDropDownOption( 'state_ma_filing_status' );
		this.initDropDownOption( 'state_ga_filing_status' );
		this.initDropDownOption( 'state_la_filing_status' );
		this.initDropDownOption( 'state_wv_filing_status' );
		this.initDropDownOption( 'state_filing_status' );
		this.initDropDownOption( 'tax_formula_type' );

		this.company_api.getOptions( 'district', {
			onResult: function( res ) {
				res = res.getResult();
				$this.district_array = res;
			}
		} );

		this.date_api.getMonthOfYearArray( false, {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = Global.buildRecordArray( res );
			}
		} );
		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = Global.buildRecordArray( res );
			}
		} );
	},

	//Override for: Do not show a few of the default columns when in Edit Employee sub-view "Tax" tab.
	setSelectLayout: function() {
		if ( this.sub_view_mode ) {
			this._super( 'setSelectLayout', ['legal_entity_legal_name', 'total_users'] );
		} else {
			this._super( 'setSelectLayout' );
		}
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	// The following functions are to disable various buttons on Employee Settings tab.
	// This was due to users getting confused as to what they were deleting (employee entry in table vs tax/deduc record). See issue #2688
	disableIconOnEmployeeSettingsTab: function( context_btn ) {
		if ( this.getEditViewActiveTabName() === 'tab_employee_setting' ) {
			context_btn.addClass( 'disable-image' );
		}
	},
	setEditMenuDeleteIcon: function( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	},
	setEditMenuDeleteAndNextIcon: function( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	},
	setEditMenuCopyIcon: function( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	},
	setEditMenuCopyAndAddIcon: function( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	},
	setEditMenuSaveAndCopyIcon: function( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	},

	saveInsideEditorData: function( callBack ) {
		var $this = this;

		// #2764 do not check for this.sub_view_mode as Save icon will fail to save. Save and Save&Continue should have the same logic regardless of sub_view. See issue or commit ee0102be0f45f954a78b7f96b6cf2f2350b73dd7 context on this.sub_view_mode and save&continue.
		// if ( !this.current_edit_record || !this.current_edit_record.id || this.sub_view_mode ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
			return;
		}

		if ( !this.employee_setting_grid ) {
			return;
		}

		var data = this.employee_setting_grid.getGridParam( 'data' );
		var columns = this.employee_setting_grid.getGridParam( 'colModel' );

		for ( var i = 0; i < data.length; i++ ) {
			var item = data[i];
			if ( this.start_dates && this.start_dates.length > 0 ) {
				item.start_date = this.start_dates[i].getValue();
			}
			if ( this.length_dates && this.length_dates.length > 0 ) {
				item.length_of_service_date = this.length_dates[i].getValue();
			}
			if ( this.end_dates && this.end_dates.length > 0 ) {
				item.end_date = this.end_dates[i].getValue();
			}
			for ( var j = 1; j < columns.length; j++ ) {
				var column = columns[j];
				if ( item[column.name] === this.original_current_record[column.name] ) {
					item[column.name] = false;  //Default column setting
				}
			}
		}

		if ( data && data.length > 0 ) {
			this.user_deduction_api.setUserDeduction( data, {
				onResult: function() {
					if ( Global.isSet( callBack ) ) {
						callBack();
					}
				}
			} );
		} else {
			//Still execute the callback so Save & Next can move to the next record when there is no Employees assigned to it
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {
		if ( this.select_grid_last_row ) {
			this.employee_setting_grid.grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.setDateCellsEnabled( false, this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		return this._super( 'onContextMenuClick', context_btn, menu_name );
	},

	getDeleteSelectedRecordId: function() {
		if ( !this.sub_view_mode ) {
			return this._super( 'getDeleteSelectedRecordId' );
		} else {
			var retval = [];

			if ( this.edit_view ) {
				retval.push( this.employee_setting_result[0].id );
			} else {
				var args = { filter_data: {} };
				var tax_ids = this.getGridSelectIdArray().slice();
				args.filter_data.company_deduction_id = tax_ids;
				args.filter_data.user_id = this.parent_value;

				var res = this.user_deduction_api.getUserDeduction( args, true, { async: false } ).getResult();

				for ( var i = 0; i < res.length; i++ ) {
					var item = res[i];
					retval.push( item.id );
				}
			}

			return retval;
		}
	},

	doDeleteAPICall: function( remove_ids, callback ) {
		if ( !this.sub_view_mode ) {
			return this._super( 'doDeleteAPICall', remove_ids, callback );
		} else {
			if ( !callback ) {
				callback = {
					onResult: function( result ) {
						this.onDeleteResult( result, remove_ids );
					}.bind( this )
				};
			}
			// return this.api['delete' + this.api.key_name]( remove_ids, callback );
			return this.user_deduction_api.deleteUserDeduction( remove_ids, callback );
		}
	},

	onSaveClick: function( ignoreWarning ) {
		var $this = this;
		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {
			var changed_fields = this.getChangedFields();
			record = this.buildMassEditSaveRecord( this.mass_edit_record_ids, changed_fields );
			// var check_fields = {};
			// for ( var key in this.edit_view_ui_dic ) {
			// 	var widget = this.edit_view_ui_dic[key];
			//
			// 	if ( Global.isSet( widget.isChecked ) ) {
			// 		if ( widget.isChecked() ) {
			// 			check_fields[key] = this.current_edit_record[key];
			// 		}
			// 	}
			// }
			// record = [];
			// $.each( this.mass_edit_record_ids, function( index, value ) {
			// 	var common_record = Global.clone( check_fields );
			// 	common_record.id = value;
			// 	record.push( common_record );
			//
			// } );
		} else {
			record = this.current_edit_record;
		}
		record = this.uniformVariable( record );
		if ( !this.sub_view_mode ) {
			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveResult( result );
				}
			} );
		} else {
			if ( !this.current_edit_record.id ) {
				this.user_deduction_api.setUserDeduction( record, false, ignoreWarning, {
					onResult: function( result ) {
						$this.onSaveResult( result );
					}
				} );
			} else {
				$this.saveInsideEditorData( function() {
					$this.refresh_id = $this.current_edit_record.id; // as add
					$this.search();

					$this.removeEditView();
				} );
			}
		}

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
		if ( !this.sub_view_mode ) {
			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveAndContinueResult( result );
				}
			} );
		} else {
			// Only edit record can go here
			$this.saveInsideEditorData( function() {
				$this.refresh_id = $this.current_edit_record.id;
				$this.search( false );
				$this.onEditClick( $this.refresh_id, true );
			} );
		}

	},

	onSaveAndNextClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = false;
		this.is_changed = false;

		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_next';
		record = this.uniformVariable( record );

		if ( !this.sub_view_mode ) {
			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveAndNextResult( result );
				}
			} );
		} else {
			// Only edit record can go here
			$this.saveInsideEditorData( function() {
				$this.refresh_id = $this.current_edit_record.id;
				$this.onRightArrowClick();
				$this.search( false );
			} );
		}

	},

	//Make sure this.current_edit_record is updated before validate
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
		if ( !this.sub_view_mode ) {
			this.api['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else {
			this.user_deduction_api.validateUserDeduction( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		}
	},

	uniformVariable: function( record ) {
		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {

			record = [];

			var selected_items = this.edit_view_ui_dic.company_tax_deduction_ids.getValue();
			for ( var i = 0; i < selected_items.length; i++ ) {
				var new_record = {};
				new_record.user_id = this.parent_value;
				new_record.company_deduction_id = selected_items[i].id;
				record.push( new_record );
			}

		}

		return record;
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();

			if ( !this.sub_view_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id; // as add
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) { // as new
					$this.refresh_id = result_data;
				}
				$this.saveInsideEditorData( function() {
					$this.search();
					$this.onSaveDone( result );

					$this.removeEditView();
				} );
			} else {
				$this.refresh_id = null;
				$this.search();
				$this.onSaveDone( result );

				$this.removeEditView();
			}

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	// onSaveAndContinueResult: function( result ) {
	//
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) { // as new
	// 			$this.refresh_id = result_data;
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.search( false );
	// 			$this.onEditClick( $this.refresh_id, true );
	// 			$this.onSaveAndContinueDone( result );
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	// onSaveAndNewResult: function( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	//
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) { // as new
	// 			$this.refresh_id = result_data;
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.search( false );
	// 			$this.onAddClick( true );
	//
	// 		} );
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	onSaveAndCopyResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	_continueDoCopyAsNew: function() {
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			this.employee_setting_grid.clearGridData();
			this.edit_view_ui_dic.calculation_id.setEnabled( true );
		}
		this._super( '_continueDoCopyAsNew' );
	},

	clearEditViewData: function() {

		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			this.edit_view_ui_dic[key].setValue( null );
			this.edit_view_ui_dic[key].clearErrorStyle();
		}

		if ( this.employee_setting_grid ) {
			this.employee_setting_grid.clearGridData();
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
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.onRightArrowClick();
	// 			$this.search( false );
	// 			$this.onSaveAndNextDone( result );
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	checkTabPermissions: function( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_tax_deductions':
			case 'tab_eligibility':
			case 'tab_employee_setting':
			case 'tab_attachment':
			case 'tab_audit':
				//Don't show these tabs when under Edit Employee, Tax tab.
				if ( this.sub_view_mode ) {
					if ( tab == 'tab_employee_setting' && this.current_edit_record.id ) {
						retval = true;
					} else {
						retval = false;
					}
				} else {
					retval = true;
				}
				break;
			case 'tab5':
				if ( this.sub_view_mode ) {
					if ( tab == 'tab5' && this.current_edit_record.id ) {
						retval = false;
					} else {
						retval = true;
					}
				}
				break;
			default:
				retval = this._super( 'checkTabPermissions', tab );
				break;
		}

		return retval;
	},

	setProvince: function( val, m ) {
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
					province_widget.setSourceData( $this.e_province_array );
					$this.setProvinceVisibility();

				}
			} );
		}
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
				var widget_2 = this.edit_view_ui_dic['district'];
				widget.setValue( null );
				widget_2.setValue( null );
				this.current_edit_record.province = false;
				this.current_edit_record.district = false;
				this.setDynamicFields( null, true );
				break;
			case 'province':
				widget_2 = this.edit_view_ui_dic['district'];
				this.setDistrict( this.current_edit_record['country'] );
				widget_2.setValue( null );
				this.setDynamicFields( null, true );
				break;
			case 'calculation_id':
				this.setDynamicFields();
				break;
			case 'apply_frequency_id':
				this.onApplyFrequencyChange();
				break;
			case 'minimum_length_of_service_unit_id':
			case 'maximum_length_of_service_unit_id':
				this.onLengthOfServiceChange();
				break;
			case 'start_date':
			case 'end_date':
			case 'minimum_length_of_service':
			case 'maximum_length_of_service':
				this.resetEmployeeSettingGridColumns();
				break;
			case 'legal_entity_id':
				this.onLegalEntityChange();
				this.updateEmployeeData();
				break;
			case 'user_value10':
				this.onFormW4VersionChange();
				break;
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	setCurrentEditRecordData: function() {
		var $this = this;

		this.original_current_record = Global.clone( this.current_edit_record );
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.eSetProvince( this.current_edit_record[key] );
						this.setDistrict( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( $this.current_edit_record.id ) {
			$this.edit_view_ui_dic.calculation_id.setEnabled( false );
		} else {
			$this.edit_view_ui_dic.calculation_id.setEnabled( true );
		}

		this.setDynamicFields( function() {
			$this.collectUIDataToCurrentEditRecord();
			$this.onLengthOfServiceChange();
			$this.setEditViewDataDone();
			$this.edit_view_ui_dic.company_tax_deduction_ids.setGridColumnsWidths();
			$this.onLegalEntityChange();
		} );

		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {
			this.initCompanyTaxDeductionData();
		}
	},

	onLegalEntityChange: function() {
		var pra_value = this.edit_view_ui_dic.payroll_remittance_agency_id.getValue();
		var new_arg = {};
		new_arg.filter_data = { legal_entity_id: this.edit_view_ui_dic.legal_entity_id.getValue() };
		new_arg.filter_columns = this.edit_view_ui_dic.payroll_remittance_agency_id.getColumnFilter();

		var $this = this;
		if ( this.edit_view_ui_dic.legal_entity_id.getValue() != TTUUID.zero_id ) {
			this.payroll_remittance_agency_api.getPayrollRemittanceAgency( new_arg, {
				onResult: function( task_result ) {
					var data = task_result.getResult();

					if ( $this.edit_view_ui_dic.payroll_remittance_agency_id ) {
						if ( data.length > 0 ) {
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setSourceData( data );

							var id_in_result = false;
							for ( var i in data ) {
								if ( data[i].id == pra_value ) {
									id_in_result = true;
									break;
								}
							}

							if ( id_in_result === false ) {
								pra_value = TTUUID.zero_id;
							}

							$this.current_edit_record.payroll_remittance_agency_id = pra_value;
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( pra_value );

						} else {
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( TTUUID.zero_id );
						}
						$this.edit_view_ui_dic.payroll_remittance_agency_id.setEnabled( true );
					}
				}
			} );
		} else {
			pra_value = TTUUID.zero_id;
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setSourceData( [TTUUID.zero_id] ); //wipe the box
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( pra_value );
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setEnabled( false );
		}
	},

	updateEmployeeData: function() {
		var request_data = { filter_data: {} };
		if ( this.edit_view_ui_dic && this.edit_view_ui_dic.legal_entity_id && this.edit_view_ui_dic.legal_entity_id.getValue() && this.edit_view_ui_dic.legal_entity_id.getValue() != TTUUID.zero_id ) {
			request_data.filter_data.legal_entity_id = this.edit_view_ui_dic.legal_entity_id.getValue();
		}
		if ( this.edit_view_ui_dic.user ) {
			this.edit_view_ui_dic.user.setDefaultArgs( request_data );
			this.edit_view_ui_dic.user.setSourceData( null );
		}
	},

	onLengthOfServiceChange: function() {

		if ( this.sub_view_mode ) {
			return;
		}

		if ( this.current_edit_record['minimum_length_of_service_unit_id'] == 50 || this.current_edit_record['maximum_length_of_service_unit_id'] == 50 ) {
			this.attachElement( 'length_of_service_contributing_pay_code_policy_id' );
		} else {
			this.detachElement( 'length_of_service_contributing_pay_code_policy_id' );
		}

		this.editFieldResize();
	},

	initCompanyTaxDeductionData: function() {

		var $this = this;

		var request_data = {
			filter_data: {
				legal_entity_id: [this.parent_edit_record.legal_entity_id, TTUUID.zero_id, TTUUID.not_exist_id],
				exclude_user_id: this.parent_edit_record.id //Don't show records the employee is already assinged to. Helps prevent duplicate mappings.
			}
		};

		this.api.getCompanyDeduction( request_data, true, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.edit_view_ui_dic.company_tax_deduction_ids.setUnselectedGridData( result_data );
			}
		} );
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onApplyFrequencyChange();
		this.initEmployeeSetting();
		this.updateEmployeeData();
	},

	setDistrict: function( c ) {
		var $this = this;
		var district_widget = $this.edit_view_ui_dic['district'];

		$this.provice_district_array = [];
		district_widget.setSourceData( $this.provice_district_array );
		if ( c ) {
			var pd_array = this.district_array[c];

			if ( pd_array ) {
				var pd_array_item = pd_array[$this.current_edit_record.province];

				if ( pd_array_item ) {
					$this.provice_district_array = Global.buildRecordArray( pd_array_item );
					district_widget.setSourceData( $this.provice_district_array );
				}

			}
		}

		$this.setDistrictVisibility();
	},

	hideAllDynamicFields: function( keepC, keepP ) {

		if ( !this.edit_view ) {
			return;
		}

		if ( !keepC ) {
			this.show_c = false;
			this.detachElement( 'country' );
		}

		if ( !keepP ) {
			this.show_p = false;
			this.show_dc = false;
			this.detachElement( 'province' );
			this.detachElement( 'district' );
		}

		this.detachElement( 'df_0' );
		this.detachElement( 'df_1' );
		this.detachElement( 'df_2' );
		this.detachElement( 'df_3' );
		this.detachElement( 'df_4' );
		this.detachElement( 'df_5' );
		this.detachElement( 'df_6' );
		this.detachElement( 'df_7' );
		this.detachElement( 'df_8' );
		this.detachElement( 'df_9' );
		this.detachElement( 'df_10' );
		this.detachElement( 'df_11' );
		this.detachElement( 'df_12' );
		this.detachElement( 'df_14' );
		this.detachElement( 'df_15' );

		this.detachElement( 'df_20' );
		this.detachElement( 'df_21' );
		this.detachElement( 'df_22' );
		this.detachElement( 'df_23' );
		this.detachElement( 'df_24' );
		this.detachElement( 'df_25' );

		if ( !( Global.getProductEdition() >= 15 ) ) {
			this.detachElement( 'df_100' );
		}
	},

	initEmployeeSetting: function() {
		var $this = this;

		if ( !$this.edit_view ) {
			return;
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			$this.employee_setting_result = [];
			//Don't display the Employee Settings grid headers when its a new record.
			//$this.setEmployeeSettingGridData( $this.buildEmployeeSettingGrid() );
			return;
		}

		// Specify which menu to use for Employee Settings tab, and use disableIconOnEmployeeSettingsTab() to disable certain icons. Related to #2688
		this.buildContextMenu( true );
		this.setEditMenu();

		var args = { filter_data: {} };

		args.filter_data.company_deduction_id = this.current_edit_record.id;

		if ( this.sub_view_mode ) {
			args.filter_data.user_id = this.parent_value;
		}

		this.user_deduction_api.getUserDeduction( args, true, {
			onResult: function( result ) {

				if ( !$this.edit_view ) {
					return;
				}

				$this.employee_setting_result = result.getResult();
				$this.setEmployeeSettingGridData( $this.buildEmployeeSettingGrid() );
			}
		} );

	},

	resetEmployeeSettingGridColumns: function() {
		if ( this.employee_setting_grid ) {
			var data = this.employee_setting_grid.getGridParam( 'data' );
			Global.formatGridData( data, this.api.key_name );
			this.buildEmployeeSettingGrid();
			this.employee_setting_grid.setData( data );
			this.removeEmployeeSettingNoResultCover();
			this.setEmployeeGridDateColumns();
			this.setEmployeeGridSize();
			if ( data.length < 1 && this.current_edit_record.id ) {
				this.showEmployeeSettingNoResultCover();
			}
		}
	},

	getColumnOptionsString: function( column_options_arr ) {
		var column_options_string = '';
		for ( var i = 0; i < column_options_arr.length; i++ ) {
			if ( i !== column_options_arr.length - 1 ) {
				column_options_string += column_options_arr[i].fullValue + ':' + column_options_arr[i].label + ';';
			} else {
				column_options_string += column_options_arr[i].fullValue + ':' + column_options_arr[i].label;
			}
		}

		return column_options_string;
	},

	/* jshint ignore:start */
	buildEmployeeSettingGrid: function() {
		var $this = this;
		var column_info_array = [];

		var column_info = {
			name: 'user_name',
			index: 'user_name',
			label: $.i18n._( 'Employees' ),
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		switch ( this.final_c_id ) {
			case '10':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '15':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Wage Base/Maximum Earnings' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '16':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Target Amount Limit' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Target YTD Limit/Balance' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '17':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Annual Fixed Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				break;
			case '18':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Wage Base/Maximum Earnings' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Exempt Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '19':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Annual Fixed Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '20':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '30':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '52':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Target YTD Limit/Balance' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '69':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Value1' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Value2' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Value3' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Value4' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Value5' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value6',
					index: 'user_value6',
					label: $.i18n._( 'Value6' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value7',
					index: 'user_value7',
					label: $.i18n._( 'Value7' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value8',
					index: 'user_value8',
					label: $.i18n._( 'Value8' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value9',
					index: 'user_value9',
					label: $.i18n._( 'Value9' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value10',
					index: 'user_value10',
					label: $.i18n._( 'Value10' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '100-CA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Claim Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '100-US':
				//2020 Form W4
				column_info = {
					name: 'user_value9',
					index: 'user_value9',
					label: $.i18n._( 'Form W-4<br>Version' ),
					width: 60,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {
						defaultValue: 2019, //This is required to prevent a blank cell from appearing if they haven't saved the Tax/Deduction record since the upgrade.
						value: this.getColumnOptionsString( this.form_w4_version_array ),
						//dataEvents: [ {type: 'change', fn:function(e) { $this.onFormItemChange( e.target, true )}} ],
					}
				};
				column_info_array.push( column_info );

				//2019 Form W4
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: '&nbsp;&nbsp;&nbsp;&nbsp;' + $.i18n._( 'Filing Status' ) + '&nbsp;&nbsp;&nbsp;&nbsp;', //Add some padding on the filing status as the auto sizing of columns doesn't work properly here.
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.federal_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 50,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				//2020 Form W4
				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Multiple Jobs or<br> Spouse Works' ),
					width: 60,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {
						defaultValue: 0, //This is required to prevent a blank cell from appearing if they haven't saved the Tax/Deduction record since the upgrade.
						value: this.getColumnOptionsString( this.yes_no_array )
					}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Claim<br>Dependents' ),
					width: 50,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Other<br>Income' ),
					width: 50,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value6',
					index: 'user_value6',
					label: $.i18n._( 'Deductions' ),
					width: 50,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value7',
					index: 'user_value7',
					label: $.i18n._( 'Extra<br>Withholding' ),
					width: 50,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '100-CR':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_basic_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-CA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Claim Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-AZ':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-AL':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_al_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-CT':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_ct_filing_status_array ) }
				};
				column_info_array.push( column_info );
				break;
			case '200-US-DC':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_dc_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-DE':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_de_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-NJ':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_nj_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-NM':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.federal_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-NC':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_nc_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-MA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_ma_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-MD':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_dc_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-MS':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_dc_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Exemption Claimed' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-OK':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_basic_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-GA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_ga_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Employee / Spouse Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Dependent Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-IL':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'IL-W-4 Line 1' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'IL-W-4 Line 2' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-OH':
				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-VA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Age 65/Blind' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-IN':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-LA':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_la_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Exemptions' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-WI':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_basic_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US-WV':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_wv_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '200-US': //Province/State Income TaxFormula
			case '300-US':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: this.getColumnOptionsString( this.state_filing_status_array ) }
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '300-US-IN':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '300-US-MD':

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '300-US-PERCENT':

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'District / County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
		}

		if ( ( this.current_edit_record.minimum_length_of_service && this.current_edit_record.minimum_length_of_service != 0 ) ||
			( this.current_edit_record.maximum_length_of_service && this.current_edit_record.maximum_length_of_service ) != 0 ) {
			column_info = {
				name: 'length_of_service_date',
				index: 'length_of_service_date',
				label: $.i18n._( 'Length of Service Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onLengthDateCellFormat
			};
			column_info_array.push( column_info );
		} else {
			$( '.row-date-picker-length-of-service-date' ).remove();
		}

		if ( this.current_edit_record.start_date || this.current_edit_record.end_date ) {
			column_info = {
				name: 'start_date',
				index: 'start_date',
				label: $.i18n._( 'Start Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onStartDateCellFormat
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'end_date',
				index: 'end_date',
				label: $.i18n._( 'End Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onEndDateCellFormat
			};
			column_info_array.push( column_info );
		} else {
			$( '.row-date-picker-start-date' ).remove();
			$( '.row-date-picker-end-date' ).remove();
		}

		//Add Exempt column to all Federal/Provincial/State/District taxes.
		if ( ( this.current_edit_record.calculation_id == 100 || this.current_edit_record.calculation_id == 200 || this.current_edit_record.calculation_id == 300 ) && this.current_edit_record.country == 'US' ) {
			column_info = {
				name: 'user_value10',
				index: 'user_value10',
				label: $.i18n._( 'Exempt' ),
				width: 30,
				sortable: false,
				formatter: 'select',
				editable: true,
				title: false,
				edittype: 'select',
				editoptions: { value: this.getColumnOptionsString( this.yes_no_array ) }
			};
			column_info_array.push( column_info );
		}

		if ( this.employee_setting_grid ) {
			this.employee_setting_grid.grid.jqGrid( 'GridUnload' );
			this.employee_setting_grid = null;
		}

		this.employee_setting_grid = new TTGrid( 'employee_setting_grid', {
			container_selector: '.edit-view-tab',
			multiselect: false,
			winMultiSelect: false,
			colModel: column_info_array,
			editurl: 'clientArray',
			onSelectRow: function( id ) {
				if ( id && !$this.is_viewing ) {

					if ( $this.select_grid_last_row ) {
						$this.employee_setting_grid.grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						$this.setDateCellsEnabled( false, $this.select_grid_last_row );
					}

					$this.employee_setting_grid.grid.jqGrid( 'editRow', id, true );
					$this.setDateCellsEnabled( true, id );
					$this.select_grid_last_row = id;
				}
			},
			onEndEditRow: function( id ) {
				$this.setDateCellsEnabled( false, id );
			},
			gridComplete: function() {
				$this.setEmployeeGridSize();
			}
		}, column_info_array );

		return column_info_array;

	},

	setEditViewTabSize: function() {
		this._super( 'setEditViewTabSize' );
		this.setEmployeeGridSize();
	},

	setDateCellsEnabled: function( flag, row_id ) {
		this.length_dates_dic[row_id] && this.length_dates_dic[row_id].setEnabled( flag );
		this.start_dates_dic[row_id] && this.start_dates_dic[row_id].setEnabled( flag );
		this.end_dates_dic[row_id] && this.end_dates_dic[row_id].setEnabled( flag );
	},

	onLengthDateCellFormat: function( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-length-of-service-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	},

	onStartDateCellFormat: function( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-start-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	},

	onEndDateCellFormat: function( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-end-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	},

	setEmployeeSettingGridData: function( column_info_array ) {

		var $this = this;
		var grid_source = [];
		if ( $.type( this.employee_setting_result ) === 'array' ) {
			grid_source = this.employee_setting_result.slice();
		}

		var len = grid_source.length;
		for ( var i = 0; i < len; i++ ) {
			var item = grid_source[i];
			item.user_name = ( ( item.user_status_id != 10 ) ? '(' + item.user_status + ') ' : '' ) + item.full_name;
			for ( var j = 1; j < column_info_array.length; j++ ) {

				var column = column_info_array[j];
				if ( !item[column.name] ) {
					item[column.name] = ( this.current_edit_record.hasOwnProperty( column.name ) && this.current_edit_record[column.name] !== false ) ? this.current_edit_record[column.name] : '';
				}
			}

		}

		$this.employee_setting_grid.setData( grid_source );
		this.removeEmployeeSettingNoResultCover();
		this.setEmployeeGridDateColumns();

		this.setEmployeeGridSize();

		if ( grid_source.length < 1 && this.current_edit_record.id ) {
			this.showEmployeeSettingNoResultCover();
		}

	},

	setEmployeeGridDateColumns: function() {
		var i, date_picker;
		this.length_dates = [];
		this.start_dates = [];
		this.end_dates = [];
		this.length_dates_dic = {};
		this.start_dates_dic = {};
		this.end_dates_dic = {};
		var date_pickers = $( '.row-date-picker-length-of-service-date' );
		for ( i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'length_of_service_date' + i } );
			date_picker.setEnabled( false );
			this.length_dates.push( date_picker );
			this.length_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
		date_pickers = $( '.row-date-picker-start-date' );
		for ( i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'start_date' + i } );
			date_picker.setEnabled( false );
			this.start_dates.push( date_picker );
			this.start_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
		date_pickers = $( '.row-date-picker-end-date' );
		for ( i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'end_date' + i } );
			date_picker.setEnabled( false );
			this.end_dates.push( date_picker );
			this.end_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
	},

	showEmployeeSettingNoResultCover: function() {

		this.removeEmployeeSettingNoResultCover();
		this.employee_setting_no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.employee_setting_no_result_box.NoResultBox( { related_view_controller: this, is_new: false } );
		this.employee_setting_no_result_box.attr( 'id', this.ui_id + 'employee_setting_no_result_box' );
		var grid_div = this.edit_view.find( '.employee-setting-grid-div' );

		grid_div.append( this.employee_setting_no_result_box );

	},

	removeEmployeeSettingNoResultCover: function() {
		if ( this.employee_setting_no_result_box && this.employee_setting_no_result_box.length > 0 ) {
			this.employee_setting_no_result_box.remove();
		}
		this.employee_setting_no_result_box = null;
	},

	setEmployeeGridSize: function() {
		if ( !this.employee_setting_grid ) {
			return;
		}

		var tab_employee_setting = this.edit_view.find( '#tab_employee_setting_content_div' );
		this.employee_setting_grid.grid.setGridWidth( tab_employee_setting.width() );
		this.employee_setting_grid.grid.setGridHeight( tab_employee_setting.height() );
	},

	setCountryVisibility: function() {

		if ( this.show_c ) {
			this.attachElement( 'country' );
		} else {
			this.detachElement( 'country' );
		}

	},

	setProvinceVisibility: function() {
		if ( this.show_p && this.e_province_array && this.e_province_array.length > 1 ) {
			this.attachElement( 'province' );
		} else {
			this.detachElement( 'province' );
		}
	},

	setDistrictVisibility: function() {

		if ( this.show_dc && this.provice_district_array && this.provice_district_array.length > 0 ) {
			this.attachElement( 'district' );
		} else {
			this.detachElement( 'district' );
			this.current_edit_record.district = false;
		}
	},

	setDynamicFields: function( callBack, countryOrP ) {

		var $this = this;
		if ( !this.current_edit_record.calculation_id ) {
			this.current_edit_record.calculation_id = '10';
			this.edit_view_ui_dic.calculation_id.setValue( 10 );
		}

		var c_id = this.current_edit_record.calculation_id;

		if ( c_id == 20 ) {
			this.detachElement( 'include_account_amount_type_id' );
			this.detachElement( 'exclude_account_amount_type_id' );
		} else {
			this.attachElement( 'include_account_amount_type_id' );
			this.attachElement( 'exclude_account_amount_type_id' );
		}

		if ( !countryOrP ) {
			this.hideAllDynamicFields();
			this.api.isCountryCalculationID( c_id, {
				onResult: function( result_1 ) {
					var res_data_1 = result_1.getResult();

					if ( res_data_1 === true ) {
						$this.show_c = true;
						$this.setCountryVisibility();
						$this.api.isProvinceCalculationID( c_id, {
							onResult: function( result_2 ) {
								var res_data_2 = result_2.getResult();
								if ( res_data_2 === true ) {
									$this.show_p = true;

									if ( $this.current_edit_record.country ) {
										$this.eSetProvince( $this.current_edit_record.country );
									}

									$this.api.isDistrictCalculationID( c_id, {
										onResult: function( result_3 ) {
											var res_data_3 = result_3.getResult();

											if ( res_data_3 === true ) {
												$this.show_dc = true;

												if ( $this.current_edit_record.country ) {
													$this.setDistrict( $this.current_edit_record.country );
												}

											}

											$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, $this.current_edit_record.province, { onResult: getCaResult } );

										}
									} );

								} else {
									if ( $this.current_edit_record ) {
										$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, '', { onResult: getCaResult } );
									}
								}

							}
						} );

					} else {
						$this.hideAllDynamicFields();

						$this.api.getCombinedCalculationID( c_id, '', '', { onResult: getCaResult } );
					}

				}
			} );
		} else {
			if ( !this.show_p ) {
				$this.hideAllDynamicFields( true, false );
				$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, '', { onResult: getCaResult } );
			} else {
				$this.hideAllDynamicFields( true, true );
				$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, $this.current_edit_record.province, { onResult: getCaResult } );
			}
		}

		function getCaResult( result ) {

			if ( !$this.edit_view ) {
				return;
			}

			var result_data = result.getResult();
			$this.final_c_id = result_data;
			if ( c_id.toString() === '100' || c_id.toString() === '200' || c_id.toString() === '300' ) {
				$this.attachElement( 'df_15' );
				$this.edit_view_form_item_dic.df_15.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Formula Type' ) + ': ' );
				$this.edit_view_ui_dic.df_15.setField( 'company_value1' );
				$this.edit_view_ui_dic.df_15.setValue( $this.current_edit_record.company_value1 );
			}
			switch ( result_data ) {
				case '10':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );
					break;
				case '15':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Wage Base/Maximum Earnings' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					break;
				case '16':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Target Amount Limit' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Target YTD Limit/Balance' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					break;
				case '17':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );

					$this.attachElement( 'df_4' );
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Fixed Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_4.setField( 'user_value5' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value5 );
					break;
				case '18':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Wage Base/Maximum Earnings' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Exempt Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );
					break;
				case '19':  //Advanced Percent (Tax Bracket Alt)
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );

					$this.attachElement( 'df_4' );
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Fixed Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_4.setField( 'user_value5' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value5 );
					break;
				case '20': //Fixed Amount
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );
					break;
				case '30': //Fixed Amount(Range Bracket)
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value3 );

					$this.attachElement( 'df_4' );
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_4.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value4 );
					break;
				case '52': //Fixed Amount (w/target)
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Target YTD Limit/Balance' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case '69':
					if ( Global.getProductEdition() >= 15 ) {
						$this.attachElement( 'df_1' );
						$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 1' ) + ': ' );
						$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
						$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

						$this.attachElement( 'df_2' );
						$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 2' ) + ': ' );
						$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
						$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

						$this.attachElement( 'df_3' );
						$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 3' ) + ': ' );
						$this.edit_view_ui_dic.df_3.setField( 'user_value3' );
						$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value3 );

						$this.attachElement( 'df_4' );
						$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 4' ) + ': ' );
						$this.edit_view_ui_dic.df_4.setField( 'user_value4' );
						$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value4 );

						$this.attachElement( 'df_5' );
						$this.edit_view_form_item_dic.df_5.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 5' ) + ': ' );
						$this.edit_view_ui_dic.df_5.setField( 'user_value5' );
						$this.edit_view_ui_dic.df_5.setValue( $this.current_edit_record.user_value5 );

						$this.attachElement( 'df_6' );
						$this.edit_view_form_item_dic.df_6.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 6' ) + ': ' );
						$this.edit_view_ui_dic.df_6.setField( 'user_value6' );
						$this.edit_view_ui_dic.df_6.setValue( $this.current_edit_record.user_value6 );

						$this.attachElement( 'df_7' );
						$this.edit_view_form_item_dic.df_7.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 7' ) + ': ' );
						$this.edit_view_ui_dic.df_7.setField( 'user_value7' );
						$this.edit_view_ui_dic.df_7.setValue( $this.current_edit_record.user_value7 );

						$this.attachElement( 'df_8' );
						$this.edit_view_form_item_dic.df_8.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 8' ) + ': ' );
						$this.edit_view_ui_dic.df_8.setField( 'user_value8' );
						$this.edit_view_ui_dic.df_8.setValue( $this.current_edit_record.user_value8 );

						$this.attachElement( 'df_9' );
						$this.edit_view_form_item_dic.df_9.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 9' ) + ': ' );
						$this.edit_view_ui_dic.df_9.setField( 'user_value9' );
						$this.edit_view_ui_dic.df_9.setValue( $this.current_edit_record.user_value9 );

						$this.attachElement( 'df_10' );
						$this.edit_view_form_item_dic.df_10.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 10' ) + ': ' );
						$this.edit_view_ui_dic.df_10.setField( 'user_value10' );
						$this.edit_view_ui_dic.df_10.setValue( $this.current_edit_record.user_value10 );

						$this.attachElement( 'df_11' );
						$this.edit_view_form_item_dic.df_11.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Formula' ) + ': ' );
						$this.edit_view_ui_dic.df_11.setField( 'company_value1' );
						$this.edit_view_ui_dic.df_11.setValue( $this.current_edit_record.company_value1 );

						$this.attachElement( 'df_12' );
						$this.edit_view_form_item_dic.df_12.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Look Back Period' ) + ': ' );
						$this.edit_view_ui_dic.df_12.setField( 'company_value2' );
						$this.edit_view_ui_dic.df_12.setValue( $this.current_edit_record.company_value2 );
						$this.edit_view_ui_dic.df_13.setField( 'company_value3' );
						$this.edit_view_ui_dic.df_13.setValue( $this.current_edit_record.company_value3 );
					} else {
						$this.attachElement( 'df_100' );
						$this.edit_view_ui_dic.df_100.html( Global.getUpgradeMessage() );
					}
					break;
				case '100-US':
					//2020 Form W4
					$this.attachElement( 'df_20' );
					$this.edit_view_form_item_dic.df_20.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Form W-4 Version' ) + ': ' );
					$this.edit_view_ui_dic.df_20.setSourceData( $this.form_w4_version_array );
					$this.edit_view_ui_dic.df_20.setField( 'user_value9' );
					$this.edit_view_ui_dic.df_20.setValue( $this.current_edit_record.user_value9 );

					// 2019 and older Form W4
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.federal_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					// 2020 and newer Form W4
					$this.attachElement( 'df_21' );
					$this.edit_view_form_item_dic.df_21.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Multiple Jobs or Spouse Works' ) + ': ' );
					$this.edit_view_ui_dic.df_21.setSourceData( $this.yes_no_array );
					$this.edit_view_ui_dic.df_21.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_21.setValue( $this.current_edit_record.user_value3 );

					$this.attachElement( 'df_22' );
					$this.edit_view_form_item_dic.df_22.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Claim Dependents' ) + ': ' );
					$this.edit_view_ui_dic.df_22.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_22.setValue( $this.current_edit_record.user_value4 );

					$this.attachElement( 'df_23' );
					$this.edit_view_form_item_dic.df_23.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Other Income' ) + ': ' );
					$this.edit_view_ui_dic.df_23.setField( 'user_value5' );
					$this.edit_view_ui_dic.df_23.setValue( $this.current_edit_record.user_value5 );

					$this.attachElement( 'df_24' );
					$this.edit_view_form_item_dic.df_24.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Deductions' ) + ': ' );
					$this.edit_view_ui_dic.df_24.setField( 'user_value6' );
					$this.edit_view_ui_dic.df_24.setValue( $this.current_edit_record.user_value6 );

					$this.attachElement( 'df_25' );
					$this.edit_view_form_item_dic.df_25.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Extra Withholding' ) + ': ' );
					$this.edit_view_ui_dic.df_25.setField( 'user_value7' );
					$this.edit_view_ui_dic.df_25.setValue( $this.current_edit_record.user_value7 );
					break;
				case '100-CR':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_basic_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				//
				//Canada
				//
				case '100-CA': //Federal Income Tax Formula -- CA
				case '200-CA': //Province/State Income TaxFormula -- CA-AB
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Claim Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );
					break;
				//
				//US - States
				//
				case '200-US': //Province/State Income TaxFormula
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-AL':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_al_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-AZ':
					$this.attachElement( 'df_0' );
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ': ' );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );
					break;
				case '200-US-CT':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ct_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );
					break;
				case '200-US-DC':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_dc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-DE':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_de_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-GA' :
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ga_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Employee / Spouse Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependent Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );
					break;
				case '200-US-IL' :
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'IL-W-4 Line 1' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'IL-W-4 Line 2' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-IN':
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-LA' :
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_la_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Exemptions' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );
					break;
				case '200-US-MA':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ma_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-MD':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_dc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value3 );
					break;
				case '200-US-MS':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_dc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Exemption Claimed' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-NC':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_nc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-NJ':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_nj_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-NM':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.federal_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-OK':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_basic_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-OH':
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-VA':
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Age 65/Blind' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-WI':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_basic_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-WV':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_wv_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '300-US':
					$this.attachElement( 'df_14' );
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ': ' );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '300-US-IN' :
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value2 );

					$this.attachElement( 'df_4' );
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ': ' );
					$this.edit_view_ui_dic.df_4.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value3 );
					break;
				case '300-US-MD' :
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value1 );

					$this.attachElement( 'df_3' );
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ': ' );
					$this.edit_view_ui_dic.df_3.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value2 );
					break;
				case '300-US-PERCENT' :
					$this.attachElement( 'df_1' );
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ': ' );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.attachElement( 'df_2' );
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Rate' ) + ': ' );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;

			}

			var key = $this.getEditViewTabIndex();
			$this.editFieldResize( key );

			if ( callBack ) {
				callBack();
			}
		}

	},

	//When its a 2020 or later Form W4, try to disable the Allowance field as its not on the form.
	//  This is most important when using the Employee Settings tab though.
	onFormW4VersionChange: function() {
		if ( this.current_edit_record.calculation_id == 100 && this.current_edit_record.country == 'US' ) {
			// if ( this.edit_view_ui_dic.df_20.getValue() == 2020 ) {
			// 	this.edit_view_ui_dic.df_1.setEnabled( false );
			// 	this.edit_view_ui_dic.df_21.setEnabled( true );
			// 	this.edit_view_ui_dic.df_22.setEnabled( true );
			// 	this.edit_view_ui_dic.df_23.setEnabled( true );
			// 	this.edit_view_ui_dic.df_24.setEnabled( true );
			// 	this.edit_view_ui_dic.df_25.setEnabled( true );
			// } else {
			// 	this.edit_view_ui_dic.df_1.setEnabled( true );
			// 	this.edit_view_ui_dic.df_21.setEnabled( false );
			// 	this.edit_view_ui_dic.df_22.setEnabled( false );
			// 	this.edit_view_ui_dic.df_23.setEnabled( false );
			// 	this.edit_view_ui_dic.df_24.setEnabled( false );
			// 	this.edit_view_ui_dic.df_25.setEnabled( false );
			// }
		}
	},

	onApplyFrequencyChange: function() {
		if ( this.current_edit_record.apply_frequency_id == 10 ||
			this.current_edit_record.apply_frequency_id == 100 ||
			this.current_edit_record == 110 ||
			this.current_edit_record.apply_frequency_id == 120 ||
			this.current_edit_record.apply_frequency_id == 130 ) {

			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );

		} else if ( this.current_edit_record.apply_frequency_id == 20 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		} else if ( this.current_edit_record.apply_frequency_id == 25 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'block' );
		} else if ( this.current_edit_record.apply_frequency_id == 30 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		}

		this.editFieldResize();
	},

	onCalculationChange: function() {

	},

	buildEditViewUI: function() {
		TTPromise.add( 'CompanyTaxDeduction', 'buildEditViewUI' );

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_tax_deductions': { 'label': $.i18n._( 'Tax / Deductions' ) },
			'tab_eligibility': { 'label': $.i18n._( 'Eligibility' ) },
			'tab_employee_setting': {
				'label': $.i18n._( 'Employee Settings' ),
				'init_callback': 'initEmployeeSetting',
				'display_on_mass_edit': false
			}, //Callback was: setEmployeeGridSize
			'tab5': { 'label': $.i18n._( 'Tax / Deductions' ), 'display_on_mass_edit': false },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.edit_view.children().eq( 0 ).css( 'min-width', 1170 );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: ( APIFactory.getAPIClass( 'APICompanyDeduction' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.COMPANY_DEDUCTION,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_tax_deductions = this.edit_view_tab.find( '#tab_tax_deductions' );

		var tab_tax_deductions_column1 = tab_tax_deductions.find( '.first-column' );
		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_tax_deductions_column1 );

		// Status
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_tax_deductions_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_tax_deductions_column1 );

		//Legal entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.TText( { field: 'legal_entity_id' } );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APILegalEntity' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LEGAL_ENTITY,
			show_search_inputs: false,
			set_empty: true,
			field: 'legal_entity_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_tax_deductions_column1 );

		//Payroll Remittance Agency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayrollRemittanceAgency' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAYROLL_REMITTANCE_AGENCY,
			show_search_inputs: false,
			set_empty: true,
			field: 'payroll_remittance_agency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Remittance Agency' ), form_item_input, tab_tax_deductions_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_tax_deductions_column1 );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_tax_deductions_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

		//Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'pay_stub_entry_description', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_tax_deductions_column1 );

		//Calculation Settings label
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Calculation Settings' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_tax_deductions_column1, '', null, true, false, 'separated_2' );

		//Calculation
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'calculation_id', set_empty: false, width: 400 } );
		form_item_input.setSourceData( $this.calculation_array );
		this.addEditFieldToColumn( $.i18n._( 'Calculation' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Dynamic Field 15
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_15', set_empty: false } );
		form_item_input.setSourceData( $this.tax_formula_type_array );
		this.addEditFieldToColumn( 'df_15', form_item_input, tab_tax_deductions_column1, '', null, true );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Province
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// District
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'district', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'District' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Dynamic Field 0
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_0' } );
		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var label = $( '<span class=\'widget-right-label\'> %</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( 'df_0', form_item_input, tab_tax_deductions_column1, '', widgetContainer, true );

		//Dynamic Field 20 -- Form W-4 Version (Should go above Filing Status)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_20', set_empty: false } );
		this.addEditFieldToColumn( 'df_20', form_item_input, tab_tax_deductions_column1, '', null, true );

		//Dynamic Field 14 -- Filing Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_14', set_empty: false } ); //Don't show empty value (NONE), so a filing status will always selected.
		this.addEditFieldToColumn( 'df_14', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_1' } );
		this.addEditFieldToColumn( 'df_1', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_2' } );
		this.addEditFieldToColumn( 'df_2', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_3' } );
		this.addEditFieldToColumn( 'df_3', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_4' } );
		this.addEditFieldToColumn( 'df_4', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_5' } );
		this.addEditFieldToColumn( 'df_5', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_6' } );
		this.addEditFieldToColumn( 'df_6', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 7
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_7' } );
		this.addEditFieldToColumn( 'df_7', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 8
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_8' } );
		this.addEditFieldToColumn( 'df_8', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 9
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_9' } );
		this.addEditFieldToColumn( 'df_9', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 10
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_10' } );
		this.addEditFieldToColumn( 'df_10', form_item_input, tab_tax_deductions_column1, '', null, true );

		if ( ( Global.getProductEdition() >= 15 ) ) {
			TTPromise.add( 'CompanyTaxDeduction', 'df_11' );
			Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js', function() {
				// Dynamic Field 11
				form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
				form_item_input.FormulaBuilder( {
					field: 'df_11', width: '100%', onFormulaBtnClick: function() {

						var custom_column_api = new ( APIFactory.getAPIClass( 'APIReportCustomColumn' ) )();

						custom_column_api.getOptions( 'formula_functions', {
							onResult: function( fun_result ) {
								var fun_res_data = fun_result.getResult();

								$this.api.getOptions( 'formula_variables', { onResult: onColumnsResult } );

								function onColumnsResult( col_result ) {
									var col_res_data = col_result.getResult();

									var default_args = {};
									default_args.functions = Global.buildRecordArray( fun_res_data );
									default_args.variables = Global.buildRecordArray( col_res_data );
									default_args.formula = $this.current_edit_record.company_value1;
									default_args.current_edit_record = Global.clone( $this.current_edit_record );
									default_args.api = $this.api;

									IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
										$this.current_edit_record.company_value1 = val;
										$this.edit_view_ui_dic.df_11.setValue( val );
									} );
								}

							}
						} );
					}
				} );

				$this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );
				$this.detachElement( 'df_11' );
				form_item_input.parent().width( '45%' );
				TTPromise.resolve( 'CompanyTaxDeduction', 'df_11' );
			} );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
			form_item_input.TTextInput( { field: 'df_11' } );
			this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );

			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'df_100' } );
			this.addEditFieldToColumn( 'Warning', form_item_input, tab_tax_deductions_column1, '', null, true );
		}

		//Dynamic Field 12,13
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_12', width: 30 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		var widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'df_13' } );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.look_back_unit_array ) );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );
		this.addEditFieldToColumn( 'df_12', [form_item_input, widget_combo_box], tab_tax_deductions_column1, '', widgetContainer, true );

		//Dynamic Field 21 -- Multiple Jobs or Spouse Works
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_21', set_empty: false } );
		this.addEditFieldToColumn( 'df_21', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 22
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_22' } );
		this.addEditFieldToColumn( 'df_22', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 23
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_23' } );
		this.addEditFieldToColumn( 'df_23', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 24
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_24' } );
		this.addEditFieldToColumn( 'df_24', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 25
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_25' } );
		this.addEditFieldToColumn( 'df_25', form_item_input, tab_tax_deductions_column1, '', null, true );

		if ( !this.sub_view_mode ) {
			//Pay Stub Account

			var default_args = {};
			default_args.filter_data = {};
			default_args.filter_data.type_id = [10, 20, 30, 50, 80];

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				show_search_inputs: true,
				set_empty: true,
				field: 'pay_stub_entry_account_id'

			} );
			form_item_input.setDefaultArgs( default_args );
			this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_tax_deductions_column1 );
		}

		// Calculation Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'calculation_order', width: 30 } );
		this.addEditFieldToColumn( $.i18n._( 'Calculation Order' ), form_item_input, tab_tax_deductions_column1 );

		// Include Pay Stub Accounts
		var v_box = $( '<div class=\'v-box\'></div>' );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'include_account_amount_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.account_amount_type_array ) );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		if ( !this.sub_view_mode ) {
			var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				show_search_inputs: true,
				set_empty: true,
				field: 'include_pay_stub_entry_account'
			} );
			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );
			this.addEditFieldToColumn( $.i18n._( 'Include Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, true, true );

		}

		// Exclude Pay Stub Accounts
		v_box = $( '<div class=\'v-box\'></div>' );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'exclude_account_amount_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.account_amount_type_array ) );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		if ( !this.sub_view_mode ) {
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input_1.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				show_search_inputs: true,
				set_empty: true,
				field: 'exclude_pay_stub_entry_account'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );
			this.addEditFieldToColumn( $.i18n._( 'Exclude Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, true, true );
		}

		if ( !this.sub_view_mode ) {
			// employees
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_empty: true,
				field: 'user'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_tax_deductions_column1, '' );
		}
		// Tab1  start

		var tab_eligibility = this.edit_view_tab.find( '#tab_eligibility' );

		var tab_eligibility_column1 = tab_eligibility.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_eligibility_column1 );

		// Apply Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.apply_frequency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Apply Frequency' ), form_item_input, tab_eligibility_column1, '' );

		// Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_month', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_year_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_day_of_month', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Month of Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_quarter_month', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_quarter_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month of Quarter' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Payroll Run Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {
			field: 'apply_payroll_run_type_id',
			set_empty: true,
			customFirstItemLabel: Global.any_item
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.apply_payroll_run_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Payroll Run Type' ), form_item_input, tab_eligibility_column1, '' );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no start date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'end_date' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no end date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Minimum Length Of Service

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_length_of_service', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'minimum_length_of_service_unit_id' } );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.length_of_service_unit_array ) );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );

		// Maximum Length Of Service

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_length_of_service', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'maximum_length_of_service_unit_id' } );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.length_of_service_unit_array ) );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );
		if ( !this.sub_view_mode ) {
			//Length of Service contributing pay codes.
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: ( APIFactory.getAPIClass( 'APIContributingPayCodePolicy' ) ),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.CONTRIBUTING_PAY_CODE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				set_default: true,
				field: 'length_of_service_contributing_pay_code_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Length Of Service Hours Based On' ), form_item_input, tab_eligibility_column1, '', null, true );
		}
		// Minimum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_user_age', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'years' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Maximum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_user_age', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'years' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		//Tab 5

		var tab5 = this.edit_view_tab.find( '#tab5' );

		var tab5_column1 = tab5.find( '.first-column' );

		this.edit_view_tabs[5] = [];

		this.edit_view_tabs[5].push( tab5_column1 );

		//Permissions

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

		var display_columns = ALayoutCache.getDefaultColumn( ALayoutIDs.COMPANY_DEDUCTION ); //Get Default columns base on different layout name
		display_columns = Global.convertColumnsTojGridFormat( display_columns, ALayoutIDs.COMPANY_DEDUCTION ); //Convert to jQgrid format

		form_item_input.ADropDown( {
			field: 'company_tax_deduction_ids',
			display_show_all: false,
			id: 'company_tax_deduction_ids',
			key: 'id',
			display_close_btn: false,
			allow_drag_to_order: false,
			display_column_settings: false
		} );
		form_item_input.addClass( 'splayed-adropdown' );
		this.addEditFieldToColumn( $.i18n._( 'Taxes / Deductions' ), form_item_input, tab5_column1, '', null, false, true );

		form_item_input.setColumns( display_columns );
//		form_item_input.setUnselectedGridData( [] );
		TTPromise.resolve( 'CompanyTaxDeduction', 'buildEditViewUI' );

	},

	setEditViewTabHeight: function() {
		this._super( 'setEditViewTabHeight' );

		var tax_grid = this.edit_view_ui_dic.company_tax_deduction_ids;

		tax_grid.setHeight( ( this.edit_view_tab.height() - 160 ) );
	},

	putInputToInsideFormItem: function( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );

		form_item_label_div.attr( 'class', 'edit-view-form-item-sub-label-div' );

		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) + ': ' );
		form_item_input_div.append( form_item_input );

		return form_item;
	},

	setEmployeeSettingsGridColumnsWidth: function() {
		// var col_model = this.employee_setting_grid.getGridParam( 'colModel' );
		// var grid_data = this.employee_setting_grid.getGridParam( 'data' );
		// this.grid_total_width = 0;
		//
		// //Possible exception
		// //Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=TimeSheet&date=20141102&user_id=53130 line 4288
		// if ( !col_model ) {
		// 	return;
		// }
		//
		// for ( var i = 0; i < col_model.length; i++ ) {
		// 	var col = col_model[i];
		// 	var field = col.name;
		// 	var longest_words = '';
		//
		// 	for ( var j = 0; j < grid_data.length; j++ ) {
		// 		var row_data = grid_data[j];
		// 		if ( !row_data.hasOwnProperty( field ) ) {
		// 			break;
		// 		}
		//
		// 		var current_words = row_data[field];
		//
		// 		if ( !current_words ) {
		// 			current_words = '';
		// 		}
		//
		// 		if ( !longest_words ) {
		// 			longest_words = current_words.toString();
		// 		} else {
		// 			if ( current_words && current_words.toString().length > longest_words.length ) {
		// 				longest_words = current_words.toString();
		// 			}
		// 		}
		//
		// 	}
		//
		// 	if ( longest_words ) {
		// 		var width_test = $( '<span id="width_test" />' );
		// 		width_test.css( 'font-size', '11' );
		// 		width_test.css( 'font-weight', 'normal' );
		// 		$( 'body' ).append( width_test );
		// 		width_test.text( longest_words );
		//
		// 		var width = width_test.width();
		// 		width_test.text( col.label );
		// 		var header_width = width_test.width();
		//
		// 		if ( header_width > width ) {
		// 			width = header_width + 20;
		// 		}
		//
		// 		this.grid_total_width += width + 5;
		// 		this.grid.grid.setColProp( field, { widthOrg: width + 5 } );
		// 		width_test.remove();
		//
		// 	}
		// }
		// var gw = this.grid.getWidth();
		// this.grid.grid.setGridWidth( gw );
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
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
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: ALayoutIDs.LEGAL_ENTITY,
				api_class: ( APIFactory.getAPIClass( 'APILegalEntity' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
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
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Remittance Agency' ),
				in_column: 2,
				field: 'payroll_remittance_agency_id',
				layout_name: ALayoutIDs.PAYROLL_REMITTANCE_AGENCY,
				api_class: ( APIFactory.getAPIClass( 'APIPayrollRemittanceAgency' ) ),
				multiple: true,
				basic_search: true,
				script_name: 'PayrollRemittanceAgencyView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Calculation' ),
				in_column: 2,
				field: 'calculation_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];

	},
	searchDone: function() {

		TTPromise.resolve( 'TaxView', 'init' );
		this.__super( 'searchDone' );
	}

} );

CompanyTaxDeductionViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'CompanyTaxDeduction', 'SubCompanyTaxDeductionView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_company_tax_deduction_view_controller );
			}

		}

	} );

};
