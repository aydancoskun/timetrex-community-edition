RecurringScheduleControlViewController = BaseViewController.extend( {
	el: '#recurring_schedule_control_view_container',

	_required_files: ['APIRecurringScheduleControl', 'APIUserGroup', 'APIRecurringScheduleTemplateControl', 'APIUserTitle', 'APIBranch', 'APIDepartment'],
	user_status_array: null,

	user_group_array: null,
	user_api: null,

	user_group_api: null,

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RecurringScheduleControlEditView.html';
		this.permission_id = 'recurring_schedule';
		this.viewId = 'RecurringScheduleControl';
		this.script_name = 'RecurringScheduleControlView';
		this.table_name_key = 'recurring_schedule_control';
		this.context_menu_name = $.i18n._( 'Recurring Schedules' );
		this.navigation_label = $.i18n._( 'Recurring Schedule' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRecurringScheduleControl' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

//		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, function( res ) {
			res = res.getResult();
			$this.user_status_array = Global.buildRecordArray( res );
		} );

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

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_recurring_schedule': { 'label': $.i18n._( 'Recurring Schedule' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRecurringScheduleControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_SCHEDULE_CONTROL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_recurring_schedule = this.edit_view_tab.find( '#tab_recurring_schedule' );

		var tab_recurring_schedule_column1 = tab_recurring_schedule.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_schedule_column1 );

		// Template
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
			show_search_inputs: true,
			set_empty: true,
			field: 'recurring_schedule_template_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Template' ), form_item_input, tab_recurring_schedule_column1, '' );

		// Start Week
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'start_week', width: 40 } );
		this.addEditFieldToColumn( $.i18n._( 'Template Start Week' ), form_item_input, tab_recurring_schedule_column1 );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'start_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_recurring_schedule_column1, '', null );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'end_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no end date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_recurring_schedule_column1, '', widgetContainer );

		// Display Weeks
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'display_weeks', width: 20 } );
		this.addEditFieldToColumn( $.i18n._( 'Display Weeks' ), form_item_input, tab_recurring_schedule_column1, '', null );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 15 ) ) {
			// Auto-Punch
			form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input.TCheckbox( { field: 'auto_fill' } );
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			label = $( '<span class=\'widget-right-label t-checkbox-padding-left\'> (' + $.i18n._( 'Punches employees in/out automatically' ) + ')</span>' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( label );
			this.addEditFieldToColumn( $.i18n._( 'Auto-Punch' ), form_item_input, tab_recurring_schedule_column1, '', widgetContainer );
		}

		// Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		var default_args = {};
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 15 ) ) {
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_any: true,
				field: 'user',
				custom_first_label: Global.empty_item,
				addition_source_function: function( target, source_data ) {

					if ( !source_data ) {
						return source_data;
					}

					var first_item = form_item_input.createItem( 0, Global.open_item );
					source_data.unshift( first_item );
					return source_data;

				},
				added_items: [
					{ value: TTUUID.zero_id, label: Global.open_item }
				]

			} );

			default_args.permission_section = 'recurring_schedule';
			form_item_input.setDefaultArgs( default_args );
		} else {
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_any: true,
				custom_first_label: Global.empty_item,
				field: 'user'
			} );
			default_args.permission_section = 'recurring_schedule';
			form_item_input.setDefaultArgs( default_args );
		}

		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_recurring_schedule_column1, '', null, true );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Template' ),
				in_column: 1,
				field: 'recurring_schedule_template_control_id',
				layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
				api_class: (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				addition_source_function: this.onEmployeeSourceCreate,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
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
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	onEmployeeSourceCreate: function( target, source_data ) {

		if ( LocalCacheData.getCurrentCompany().product_edition_id <= 10 ) {
			return source_data;
		}

		var display_columns = target.getDisplayColumns();
		var first_item = {};
		$.each( display_columns, function( index, content ) {

			first_item.id = '0';
			first_item[content.name] = Global.open_item;

			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in /interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}
		source_data.unshift( first_item );

		return source_data;
	},

	buildContextMenuModels: function() {
		//Context Menu
		var menu = this._super( 'buildContextMenuModels' )[0];
		//menu group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var recurring_template = new RibbonSubMenu( {
			label: $.i18n._( 'Recurring<br>Templates' ),
			id: ContextMenuIconName.recurring_template,
			group: navigation_group,
			icon: Icons.recurring_template,
			permission_result: true,
			permission: null
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedules' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.recurring_template:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.export_excel:
				this.onNavigationClick( id );
				break;
		}
	},


	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );

		if ( this.is_mass_editing ) {
			//Do not show the employee dropdown for mass edit, because that does not make sense.
			this.detachElement( 'user' );
		} else {
			this.attachElement( 'user' );
		}

	},

	parseToUserId: function( id ) {
		if ( !id ) {
			return false;
		}

		id = id.toString();

		if ( id.indexOf( '_' ) > 0 ) {
			return id.split( '_' )[1];
		}

		return id;
	},

	parseToRecordId: function( id ) {

		if ( !id ) {
			return false;
		}

		id = id.toString();

		if ( id.indexOf( '_' ) > 0 ) {
			return id.split( '_' )[0];
		}

		return id;

	},

	uniformVariable: function( records ) {
		records.id = this.parseToRecordId( records.id );

		return records;
	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		$this.is_viewing = true;
		$this.is_edit = false;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		user_id = this.parseToUserId( selectedId );
		selectedId = this.parseToRecordId( selectedId );

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}
				var index = 0;

				for ( var i = 0; i < result_data.length; i++ ) {
					//FIXES: js error id is undefined.
					//weird because id is always set as a filter. we don't need it in this if.
					//if ( result_data[i].id == id && result_data[i].user_id == user_id ) {
					if ( result_data[i].user_id == user_id ) {
						index = i;
					}
				}
				result_data = result_data[index];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( result_data.id ) {
					$this.onEditClick( result_data.id );
					return;
				}

				$this.openEditView();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;
		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		this.is_mass_editing = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();
		var filter = {};

		var user_id = this.parseToUserId( selectedId );
		selectedId = this.parseToRecordId( selectedId );

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				$this.onEditClickResult( result, selectedId, user_id );
			}
		} );

	},

	onEditClickResult: function( result, id, user_id ) {
		var $this = this;
		var result_data = result.getResult();
		var index = 0;

		for ( var i = 0; i < result_data.length; i++ ) {
			if ( result_data[i].id == id && result_data[i].user_id == user_id ) {
				index = i;
			}
		}
		result_data = result_data[index];

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		result_data.id = result_data.id + '_' + result_data.user_id;

		$this.current_edit_record = result_data;

		$this.initEditView();
	},

	_continueDoCopyAsNew: function() {

		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			selectedId = this.parseToRecordId( selectedId );

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {

					$this.onCopyAsNewResult( result );

				}
			} );
		}

	},

	onCopyClick: function() {
		var $this = this;
		var copyIds = [];
		$this.is_add = false;
		if ( $this.edit_view ) {
			copyIds.push( $this.current_edit_record.id );
			if ( this.is_changed ) {
				TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
					if ( flag === true ) {
						$this.is_changed = false;
						$this._continueDoCopy( copyIds );
					}
					ProgressBar.closeOverlay();
				} );
			} else {
				$this._continueDoCopy( copyIds );
			}
		} else {
			var ids = $this.getGridSelectIdArray().slice();
			for ( var i = 0; i < ids.length; i++ ) {
				var record_id = ids[i].split( '_' )[0];
				copyIds.push( record_id );
			}
			$this._continueDoCopy( copyIds );
		}
	},

	onDeleteAndNextClick: function() {
		var $this = this;
		$this.is_add = false;

		TAlertManager.showConfirmAlert( $.i18n._( Global.delete_confirm_message ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids[$this.current_edit_record.id] = [$this.current_edit_record.user_id];
			}

			if ( result ) {

				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteAndNextResult( result, remove_ids );

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	onDeleteClick: function() {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = {};
			if ( $this.edit_view ) {
				remove_ids[$this.current_edit_record.id] = [$this.current_edit_record.user_id];
			} else {
				var ids = $this.getGridSelectIdArray().slice();

				for ( var i = 0; i < ids.length; i++ ) {
					var record_id = ids[i].split( '_' )[0];
					var user_id = ids[i].split( '_' )[1];

					if ( remove_ids[record_id] ) {
						remove_ids[record_id].push( user_id );
					} else {
						remove_ids[record_id] = [user_id];
					}

				}

			}
			if ( result ) {
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteResult( result, remove_ids );
					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	onNavigationClick: function( iconName ) {

		var $this = this;
		switch ( iconName ) {
			case ContextMenuIconName.schedule:

				var filter = { filter_data: {} };
				var selected_item = this.getSelectedItem();
				var include_users = null;
				var now_date = new Date();

				if ( !Global.isSet( selected_item.user_id ) ) {

					var temp_filter = {};
					temp_filter.filter_data = {};
					temp_filter.filter_data.id = [selected_item.id];
					temp_filter.filter_columns = { user_id: true };

					this.api['get' + this.api.key_name]( temp_filter, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							include_users = [result_data.user_id];

							filter.filter_data.include_user_ids = { value: include_users };
							filter.select_date = now_date.format();
							Global.addViewTab( $this.viewId, 'Recurring Schedules', window.location.href );
							IndexViewController.goToView( 'Schedule', filter );

						}
					} );

				} else {
					include_users = [selected_item.user_id];
					filter.filter_data.include_user_ids = { value: include_users };
					filter.select_date = now_date.format();

					Global.addViewTab( this.viewId, 'Recurring Schedules', window.location.href );
					IndexViewController.goToView( 'Schedule', filter );

				}

				break;
			case ContextMenuIconName.recurring_template:

				filter = { filter_data: {} };
				selected_item = this.getSelectedItem();

				if ( !Global.isSet( selected_item.recurring_schedule_template_control_id ) ) {

					temp_filter = {};
					temp_filter.filter_data = {};
					temp_filter.filter_data.id = [this.parseToRecordId( selected_item.id )];

					this.api['get' + this.api.key_name]( temp_filter, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							filter.filter_data.id = [result_data.recurring_schedule_template_control_id];

							Global.addViewTab( $this.viewId, 'Recurring Schedules', window.location.href );
							IndexViewController.goToView( 'RecurringScheduleTemplateControl', filter );

						}
					} );

				} else {
					filter.filter_data.id = [selected_item.recurring_schedule_template_control_id];

					Global.addViewTab( this.viewId, 'Recurring Schedules', window.location.href );
					IndexViewController.goToView( 'RecurringScheduleTemplateControl', filter );

				}

				break;
			case ContextMenuIconName.export_excel:
				this.onExportClick( 'exportRecurringScheduleControl' );
				break;
		}
	},

	getRightArrowClickSelectedIndex: function( selected_index ) {
		if ( selected_index == 0 ) {
			//selected_index is wrong because of the underscore in id.
			var source_data = this.navigation.getSourceData();
			var hash_arr = window.location.hash.split( '&' );
			var hash_id = '0_0';
			for ( var i = 0; i < hash_arr.length; i++ ) {
				if ( hash_arr[i].indexOf( 'id=' ) != -1 ) {
					hash_id = hash_arr[i].substring( 3 );
				}
			}

			if ( hash_id.indexOf( '_' ) != -1 ) {
				for ( var i = 0; i < source_data.length; i++ ) {
					if ( hash_id == source_data[i].id ) {
						selected_index = i;
						break; //exit loop
					}
				}
			}
		}

		return selected_index;
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
				case ContextMenuIconName.recurring_template:
					this.setDefaultMenuRecurringTemplateIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenuRecurringTemplateIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuScheduleIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	search: function( set_default_menu, page_action, page_number, callBack ) {

		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {

				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}

			}

		} else {
			filter.filter_page = 1;
		}

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}
		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		this.last_select_ids = this.getGridSelectIdArray();

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();
				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );
				}
				//Set Page data to widget, next show display info when setDefault Menu
				$this.pager_data = result.getPagerData();

				//CLick to show more mode no need this step
				if ( LocalCacheData.paging_type !== 0 ) {
					$this.paging_widget.setPagerData( $this.pager_data );
					$this.paging_widget_2.setPagerData( $this.pager_data );
				}

				if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
					var current_data = $this.grid.getGridParam( 'data' );
					result_data = current_data.concat( result_data );
				}

				///Override to reset id, because id of each record is the same if employess assigned to this id.
				var len = result_data.length;

				for ( var i = 0; i < len; i++ ) {
					var item = result_data[i];

					item.id = item.id + '_' + item.user_id;
				}

				$this.grid.clearGridData();
				$this.grid.setData( result_data );

				$this.reSelectLastSelectItems();

				$this.setGridCellBackGround(); //Set cell background for some views

				ProgressBar.closeOverlay(); //Add this in initData
				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				$this.searchDone();

			}
		} );

	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.user_id = true;

		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
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
				case ContextMenuIconName.recurring_template:
					this.setEditMenuRecurringTemplateIcon( context_btn );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuScheduleIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuRecurringTemplateIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuScheduleIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	},

	initSubLogView: function( tab_id ) {
		var $this = this;

		if ( !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) {
			return;
		}

		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_value = $this.parseToRecordId( $this.current_edit_record.id ); //Need to parse to record ID before passing to Audit tab.
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;

			this.sub_log_view_controller.search();
		} else {

			Global.loadScript( 'views/core/log/LogViewController.js', function() {
				if ( !$this.edit_view_tab ) {
					return;
				}
				var tab = $this.edit_view_tab.find( '#' + tab_id );
				var firstColumn = tab.find( '.first-column-sub-view' );

				TTPromise.add( 'initSubAudit', 'init' );
				TTPromise.wait( 'initSubAudit', 'init', function() {
					firstColumn.css('opacity', '1');
				} );

				firstColumn.css('opacity', '0'); //Hide the grid while its loading/sizing.

				Global.trackView( 'Sub' + 'Log' + 'View', LocalCacheData.current_doing_context_action );
				LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
			} );
		}

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_key = 'object_id';
			$this.sub_log_view_controller.parent_value = $this.parseToRecordId( $this.current_edit_record.id ); //Need to parse to record ID before passing to Audit tab.
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.parent_view_controller = $this;

			$this.sub_log_view_controller.postInit = function() {
				this.initData();
			};

		}
	}
} );
