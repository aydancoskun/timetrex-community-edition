RequestViewCommonController = BaseViewController.extend( {

	authorization_history: null,
	selected_absence_policy_record: null,
	enable_edit_view_ui:false,

	setGridCellBackGround: function() {
		//Error: Unable to get property 'getGridParam' of undefined or null reference
		if ( !this.grid ) {
			return;
		}

		var data = this.grid.getGridParam( 'data' );
		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;
		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.status_id == 30 ) {
				$( 'tr#' + item.id ).addClass( 'bolder-request' );
			}
		}
	},

	onCancelClick: function( force, cancel_all, callback ) {
		TTPromise.add( 'base', 'onCancelClick' );
		var $this = this;

		//#2571 - Unable to get property 'id' of undefined or null reference
		if ( this.current_edit_record && this.current_edit_record.id ) {
			var $record_id = this.current_edit_record.id;
		}

		LocalCacheData.current_doing_context_action = 'cancel';
		if ( this.is_changed && !force ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {

				if ( flag === true ) {
					doNext();
				}

			} );
		} else {
			doNext();
		}

		function doNext() {
			if ( !$this.edit_view && $this.parent_view_controller && $this.sub_view_mode ) {
				$this.parent_view_controller.is_changed = false;
				$this.parent_view_controller.buildContextMenu( true );
				$this.parent_view_controller.onCancelClick();

			} else {
				if ( $this.is_edit && $record_id ) {
					$this.setCurrentEditViewState( 'view' );
					$this.onViewClick( $record_id, true );
					$this.setEditMenu();
				} else {
					$this.removeEditView();
				}

			}
			if ( callback ) {
				callback();
			}

			$this.search(); //Refresh the grid, as we don't do that during authorize/decline clicks anymore.

			Global.setUIInitComplete();
			ProgressBar.closeOverlay();

			TTPromise.resolve( 'base', 'onCancelClick' );

		}

	},

	onCloseIconClick: function() {
		this.onCancelClick();
	},

	buildDataForAPI: function( data ) {
		if ( this.viewId == 'RequestAuthorization' && (!data.request_schedule_id || data.request_schedule_id <= 0) ) {
			return data;
		}

		var user_id = LocalCacheData.loginUser.id;
		if ( Global.isSet( this.current_edit_record.user_id ) ) {
			user_id = this.current_edit_record.user_id;
		}
		var data_for_api = { 'user_id': user_id };
		var request_schedule = {};

		var request_schedule_keys = '';

		var afn = this.getAdvancedFieldNames();

		for ( var key in this.current_edit_record ) {
			if ( key == 'start_date' && this.edit_view_ui_dic[key] ) {
				data_for_api.date_stamp = this.edit_view_ui_dic[key].getValue();
			}

			if ( afn.indexOf( key ) > -1 ) {
				if ( key == 'request_schedule_id' ) {
					request_schedule['id'] = this.current_edit_record.request_schedule_id;
				} else if ( key == 'request_schedule_status_id' ) {
					//this case is for when asking for default data
					request_schedule['status_id'] = this.edit_view_ui_dic.request_schedule_status_id.getValue();
				} else if ( this.edit_view_ui_dic[key] ) {
					request_schedule[key] = this.edit_view_ui_dic[key].getValue();
				}
			} else if ( key == 'available_balance' || key == 'job_item_quick_search' || key == 'job_quick_search' ) {
				//ignore. these fields do not need to be saved and break the insert sql.
			} else {
				data_for_api[key] = this.current_edit_record[key];
			}
		}
		//data_for_api['status_id'] = 30; //Manually set pending status -- This is done at the API automatically now.
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) {
			data_for_api.request_schedule = { 0: request_schedule };
		}
		return data_for_api;
	},

	buildDataFromAPI: function( data ) {
		if ( Global.isSet( data ) && Global.isSet( data.request_schedule ) ) {
			for ( var key in data.request_schedule ) {
				if ( key == 'id' ) {
					data['request_schedule_id'] = data.request_schedule.id;
				} else if ( key == 'status_id' ) {
					data['request_schedule_status_id'] = data.request_schedule.status_id;
				} else if ( typeof(data[key]) == 'undefined' ) {
					data[key] = data.request_schedule[key];
				} else {
					//Debug.Text('Not overwriting: '+key+' request_schedule: '+data.request_schedule[key]+' request: '+data[key], 'RequestViewCommonController.js', 'RequestViewCommonController','buildDataFromAPI' ,10)
				}

			}
			delete data.request_schedule;
			this.pre_request_schedule = false; //is this a request from before request schedule was added? we need to know if this is an "old version" request
		} else {
			this.pre_request_schedule = true;
		}

		var retval = $.extend( this.current_edit_record, data );
		return retval;
	},

	showAdvancedFields: function( update_schedule_total_time ) {
		if (
				Global.getProductEdition() >= 15 &&
				( PermissionManager.validate( 'request', 'add_advanced' )
						|| ( TTUUID.isUUID( this.current_edit_record.request_schedule_id ) && this.current_edit_record.request_schedule_id != TTUUID.zero_id && this.current_edit_record.request_schedule_id != TTUUID.not_exist_id ) )
				&& ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) && ( !this.pre_request_schedule || this.is_add )
		) {
			var advanced_field_names = this.getAdvancedFieldNames();
			if ( this.edit_view_ui_dic ) {
				for ( var i = 0; i < advanced_field_names.length; i++ ) {
					if ( advanced_field_names[i] == 'absence_policy_id' && this.edit_view_ui_dic.request_schedule_status_id && this.edit_view_ui_dic.request_schedule_status_id.getValue() != 20 ) {
						this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						continue;
					}
					if ( this.edit_view_ui_dic[advanced_field_names[i]] ) {
						if ( advanced_field_names[i] == 'branch_id' && !this.show_branch_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'department_id' && !this.show_department_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'job_id' && !this.show_job_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'job_item_id' && !this.show_job_item_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'available_balance' && !this.is_viewing ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).show();
						}
					}
				}

				if ( this.edit_view_ui_dic.date ) {
					this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).hide();
				}

				if ( this.edit_view_ui_dic.available_balance ) {
					if ( this.is_viewing == true && this.viewId == 'Request' ) {
						this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
					}
				}

				if ( this.current_edit_record.type_id != 30 && this.current_edit_record.type_id != 40 ) {
					if ( this.edit_view_ui_dic.total_time ) {
						this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
					}
				} else {
					if ( update_schedule_total_time != false ) {
						this.getScheduleTotalTime();
					}
				}
			}
		} else {
			if ( this.edit_view_ui_dic.date_stamp ) {
				this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).show();
			}
			this.hideAdvancedFields();
		}
	},

	hideAdvancedFields: function() {
		var advanced_field_names = this.getAdvancedFieldNames();
		if ( this.edit_view_ui_dic ) {
			for ( var i = 0; i < advanced_field_names.length; i++ ) {
				if ( this.edit_view_ui_dic[advanced_field_names[i]] ) {
					this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
				}
			}
			if ( this.edit_view_ui_dic.date ) {
				this.edit_view_ui_dic.date.parents( '.edit-view-form-item-div' ).show();
			}
		}
	},

	getAdvancedFieldNames: function() {
		return [
			'request_id',
			'request_schedule_status_id',
			'request_schedule_id',
			'start_date',
			'end_date',

			'sun',
			'mon',
			'tue',
			'wed',
			'thu',
			'fri',
			'sat',

			'start_time',
			'end_time',
			'total_time',

			'schedule_policy_id',
			'absence_policy_id',
			'branch_id',
			'department_id',
			'job_id',
			'job_item_id',

			'schedule_policy',
			'absence_policy',
			'branch',
			'department',
			'job',
			'job_item',
			'available_balance'
		];
	},

	getScheduleTotalTime: function() {
		if ( Global.getProductEdition() >= 15
				&& ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 )
				&& ( this.edit_view_ui_dic && this.edit_view_ui_dic['total_time'] )
		) {

			var start_time = false;
			if ( this.current_edit_record['start_date'] && this.current_edit_record['start_time'] ) {
				start_time = this.current_edit_record['start_date'] + ' ' + this.current_edit_record['start_time'];
			}

			var end_time = false;
			if ( this.current_edit_record['start_date'] && this.current_edit_record['end_time'] ) {
				end_time = this.current_edit_record['start_date'] + ' ' + this.current_edit_record['end_time'];
			}

			var schedulePolicyId = ( this.current_edit_record['schedule_policy_id'] ) ? this.current_edit_record['schedule_policy_id'] : null;
			var user_id = this.current_edit_record.user_id;

			if ( typeof user_id == 'undefined' && LocalCacheData.getLoginUser().id ) {
				user_id = LocalCacheData.getLoginUser().id;
			}

			if ( start_time && end_time ) {
				var schedule_api = new (APIFactory.getAPIClass( 'APISchedule' ))();
				result = schedule_api.getScheduleTotalTime( start_time, end_time, schedulePolicyId, user_id, { async: false } );
				if ( result.isValid() ) {
					this.total_time = result.getResult();
				} else {
					this.total_time = 0;
				}

				this.current_edit_record['total_time'] = this.total_time;
				var total_time = Global.getTimeUnit( this.total_time );
				this.edit_view_ui_dic['total_time'].setValue( total_time );
				this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).show();
			} else {
				if ( this.edit_view_ui_dic.total_time ) {
					this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
				}
			}
		} else {
			if ( this.edit_view_ui_dic.total_time ) {
				this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
			}
		}

		this.onAvailableBalanceChange();
	},

	onWorkingStatusChanged: function() {
		if ( Global.getProductEdition() >= 15 ) {
			if ( this.edit_view_ui_dic.request_schedule_status_id && this.edit_view_ui_dic.absence_policy_id ) {
				var type_id = this.edit_view_ui_dic.type_id ? this.edit_view_ui_dic.type_id.getValue() : this.current_edit_record.type_id;
				this.showAbsencePolicyField( type_id, this.edit_view_ui_dic.request_schedule_status_id.getValue(), this.edit_view_ui_dic.absence_policy_id );
			}
		}
	},

	showAbsencePolicyField: function( type_id, request_schedule_status_id, ui_field ) {
		if ( request_schedule_status_id == 20 && ( type_id == 30 || type_id == 40) ) {
			ui_field.parents( '.edit-view-form-item-div' ).show();
			if ( (this.viewId == 'Request' && this.is_viewing) == false ) {
				this.onAvailableBalanceChange();
			}
		} else {
			ui_field.parents( '.edit-view-form-item-div' ).hide();
			this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
		}
	},

	onDateStampChanged: function() {
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) ) {
			this.edit_view_ui_dic.start_date.setValue( this.current_edit_record.date_stamp );
			this.current_edit_record.start_date = this.current_edit_record.date_stamp;
		}
	},

	onStartDateChanged: function() {
		this.edit_view_ui_dic.date_stamp.setValue( this.current_edit_record.start_date );
		this.current_edit_record.date_stamp = this.current_edit_record.start_date;
	},

	getAvailableBalance: function() {
		if ( ( this.is_viewing && this.viewId == 'Request' ) || Global.isSet( this.current_edit_record ) == false ) {
			return;
		}

		if ( ( this.viewId != 'Request' || this.is_viewing == false ) &&
				this.current_edit_record.absence_policy_id &&
				( PermissionManager.validate( 'request', 'add_advanced' ) || ( TTUUID.isUUID( this.current_edit_record.request_schedule_id ) && this.current_edit_record.request_schedule_id != TTUUID.zero_id && this.current_edit_record.request_schedule_id != TTUUID.not_exist_id ) ) &&
				LocalCacheData.loginUser.id &&
				this.current_edit_record.total_time &&
				this.current_edit_record.total_time != '00:00' &&
				this.current_edit_record.start_date ) {

			var days = 1;
			if ( this.current_edit_record.start_date != this.current_edit_record.end_date ) {
				var days = Global.getDaysInSpan( this.current_edit_record.start_date, this.current_edit_record.end_date, this.current_edit_record.sun, this.current_edit_record.mon, this.current_edit_record.tue, this.current_edit_record.wed, this.current_edit_record.thu, this.current_edit_record.fri, this.current_edit_record.sat );
			}

			var $this = this;
			var user_id = this.current_edit_record.user_id;
			var total_time = this.current_edit_record.total_time * days;
			var date_stamp = this.current_edit_record.date_stamp;
			var policy_id = this.current_edit_record.absence_policy_id ? this.current_edit_record.absence_policy_id : 0;

			if ( user_id && date_stamp && total_time ) {
				this.api_absence_policy.getProjectedAbsencePolicyBalance(
						policy_id,
						user_id,
						date_stamp,
						total_time,
						{
							onResult: function( result ) {
								if ( $this.edit_view_ui_dic && $this.edit_view_ui_dic.available_balance ) {
									$this.getBalanceHandler( result, date_stamp );
									if ( result && $this.selected_absence_policy_record ) {
										$this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).show();
									} else {
										$this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
									}
								}
							}
						}
				);
			}
			// If unset or set to --None--...
		} else if ( this.current_edit_record.absence_policy_id == false || this.current_edit_record.absence_policy_id == TTUUID.zero_id ) {
			if ( this.edit_view_ui_dic.available_balance ) {
				this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
			}
		}
	},


	getFilterColumnsFromDisplayColumns: function( authorization_history ) {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( authorization_history ) {
			if ( this.authorization_history.authorization_history_grid ) {
				display_columns = AuthorizationHistory.getAuthorizationHistoryDefaultDisplayColumns();
			}
		} else {
			if ( this.grid ) {
				display_columns = this.grid.getGridParam( 'colModel' );
			}
		}
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.user_id = true;
		column_filter.status_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	jobUIValidate: function() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
				PermissionManager.validate( 'punch', 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	processAPICallbackResult: function ( result_data ) {
		this.current_edit_record = this.buildDataFromAPI( result_data[0] );
		this.current_edit_record.total_time = Global.getTimeUnit( this.current_edit_record.total_time );

		return result_data;
	},

	doViewClickResult: function ( result_data ) {
		if ( Global.isSet( this.current_edit_record.start_date ) && this.edit_view_tab ) {
			this.edit_view_tab.find( '#tab_request' ).find( '.third-column' ).show();
		}

		this.initEditView();
		this.initViewingView();

		//This line is required to avoid problems with the absence policy box not showing properly on initial load.
		this.onWorkingStatusChanged();

		var $this = this;
		EmbeddedMessage.init( this.current_edit_record.id, 50, this, this.edit_view, this.edit_view_tab, this.edit_view_ui_dic, function() {
			$this.authorization_history = AuthorizationHistory.init( $this );
		} );
		return this.clearCurrentSelectedRecord();
	},

	onViewClick: function( editId, clear_edit_view ) {
		this.real_this = this.constructor.__super__; // this seems first entry point. needed where view controller is extended twice, Base->Tree-View, used with onViewClick _super
		if ( clear_edit_view ) {
			this.clearEditView();
		}
		this._super( 'onViewClick', editId );
	},

	initSubLogView: function( tab_id ) {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {

				filter['table_name_object_id'] = {
					'request': [this.parent_edit_record.id],
					'request_schedule': [this.parent_edit_record.request_schedule_id]
				};

				return filter;
			};

			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/core/log/LogViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );

			var firstColumn = tab.find( '.first-column-sub-view' );

			TTPromise.add( 'initSubAudit', 'init' );
			TTPromise.wait( 'initSubAudit', 'init', function() {
				firstColumn.css('opacity', '1');
			} );

			firstColumn.css('opacity', '0'); //Hide the grid while its loading/sizing.

			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {
				filter['table_name_object_id'] = {
					'request': [this.parent_edit_record.id],
					'request_schedule': [this.parent_edit_record.request_schedule_id]
				};

				return filter;
			};
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.postInit = function() {
				this.initData();
			}
		}
	},

	/**
	 * This function exists because the edit form is not actually an edit mode form, so we need to do some
	 * stuff differently in view mode than in edit mode.
	 */
	initViewingView: function() {
		this.showAdvancedFields();
	},

	initEditViewUI: function( view_id, edit_view_file_name ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();
		var $this = this;

		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );
		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		this.setTabOVisibility( false );

		this.edit_view_tab = this.edit_view_tab.tabs( {
			activate: function( e, ui ) {
				$this.onTabShow( e, ui );
			}
		} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui );
		} );

		Global.contentContainer().append( this.edit_view );
		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_viewing ) {
			LocalCacheData.current_doing_context_action = 'view';
			this.buildViewUI();
		} else if ( this.is_edit ) {
			LocalCacheData.current_doing_context_action = 'edit';
			this.buildEditViewUI();
		}

		$this.setEditViewTabHeight();
	},

	onEditClick: function( editId, noRefreshUI ) {
		this.setCurrentEditViewState('edit');
		this.initEditViewUI( this.viewId, this.edit_view_tpl );
		this.initEditView();
		//Clear last sent message body value.
		this.edit_view_ui_dic.body.setValue( '' );
		//ensure send button is available
		this.setEditMenu();
	},

	buildViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_request': { 'label': $.i18n._( 'Request' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRequest' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REQUEST,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 first column start
		var tab_request = this.edit_view_tab.find( '#tab_request' );
		var tab_request_column1 = tab_request.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_request_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_request_column1 );

		// Type
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'type', set_empty: false } );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_request_column1 );

		// Date
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_request_column1 );

		if ( Global.getProductEdition() >= 15 ) {

			//Working Status
			var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'request_schedule_status_id', set_empty: false } );
			form_item_input.setSourceData( Global.addFirstItemToArray( { 10: 'Working', 20: 'Absent' } ) );
			this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_request_column1 );
			form_item_input.bind( 'change', function( e ) {
				$this.onWorkingStatusChanged();
			} );

			//Start Date
			form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
			form_item_input.TDatePicker( { field: 'start_date' } );
			this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_request_column1, '' );

			//End  Date
			form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
			form_item_input.TDatePicker( { field: 'end_date' } );
			this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_request_column1, '' );

			// Effective Days
			var form_item_sun_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sun_checkbox.TCheckbox( { field: 'sun' } );

			var form_item_mon_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_mon_checkbox.TCheckbox( { field: 'mon' } );

			var form_item_tue_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_tue_checkbox.TCheckbox( { field: 'tue' } );

			var form_item_wed_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_wed_checkbox.TCheckbox( { field: 'wed' } );

			var form_item_thu_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_thu_checkbox.TCheckbox( { field: 'thu' } );

			var form_item_fri_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_fri_checkbox.TCheckbox( { field: 'fri' } );

			var form_item_sat_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sat_checkbox.TCheckbox( { field: 'sat' } );

			var widgetContainer = $( '<div/>' );

			var sun = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Sun' ) + ' <br> ' + ' </span>' );
			var mon = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Mon' ) + ' <br> ' + ' </span>' );
			var tue = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Tue' ) + ' <br> ' + ' </span>' );
			var wed = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Wed' ) + ' <br> ' + ' </span>' );
			var thu = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Thu' ) + ' <br> ' + ' </span>' );
			var fri = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Fri' ) + ' <br> ' + ' </span>' );
			var sat = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Sat' ) + ' <br> ' + ' </span>' );

			sun.append( form_item_sun_checkbox );
			mon.append( form_item_mon_checkbox );
			tue.append( form_item_tue_checkbox );
			wed.append( form_item_wed_checkbox );
			thu.append( form_item_thu_checkbox );
			fri.append( form_item_fri_checkbox );
			sat.append( form_item_sat_checkbox );

			widgetContainer.append( sun );
			widgetContainer.append( mon );
			widgetContainer.append( tue );
			widgetContainer.append( wed );
			widgetContainer.append( thu );
			widgetContainer.append( fri );
			widgetContainer.append( sat );

			widgetContainer.addClass('request_edit_view_effective_days');
			this.addEditFieldToColumn( $.i18n._( 'Effective Days' ), [form_item_sun_checkbox, form_item_mon_checkbox, form_item_tue_checkbox, form_item_wed_checkbox, form_item_thu_checkbox, form_item_fri_checkbox, form_item_sat_checkbox], tab_request_column1, '', widgetContainer, false, true );

			//Start time
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'start_time' } );
			this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_request_column1 );

			//End  time
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'end_time' } );
			this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_request_column1 );

			// Total
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'total_time' } );
			this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_request_column1 );

			//Schedule Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APISchedulePolicy' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.SCHEDULE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'schedule_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Schedule Policy' ), form_item_input, tab_request_column1 );

			//Absence Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.ABSENCES_POLICY,
				set_empty: true,
				field: 'absence_policy_id',
				customSearchFilter: function( filter ) {
					return $this.setAbsencePolicyFilter( filter );
				},
				setRealValueCallBack: function( value ) {
					// #2135 fix for cases where user is removed from absence policies between creating request and approval
					$this.selected_absence_policy_record = value;
					$this.onAvailableBalanceChange();
				}
			} );
			this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_request_column1 );

			//Available Balance
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'available_balance' } );
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			this.available_balance_info = $( '<img class="available-balance-info" src="' + Global.getRealImagePath( 'images/infox16x16.png' ) + '">' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( this.available_balance_info );
			this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_request_column1, '', widgetContainer, true );

			//branch
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.BRANCH,
				show_search_inputs: true,
				set_empty: true,
				field: 'branch_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_request_column1 );
			if ( !this.show_branch_ui ) {
				this.detachElement( 'branch_id' );
			}

			//Dept
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.DEPARTMENT,
				show_search_inputs: true,
				set_empty: true,
				field: 'department_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_request_column1 );
			if ( !this.show_department_ui ) {
				this.detachElement( 'department_id' );
			}


			if ( Global.getProductEdition() >= 20 ) {
				//Job
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJob' )),
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB,
					show_search_inputs: true,
					set_empty: true,
					setRealValueCallBack: (function( val ) {

						if ( val ) {
							job_coder.setValue( val.manual_id );
						}
					}),
					field: 'job_id',
					added_items: [
						{ value: '-1', label: Global.default_item },
						{ value: '-2', label: Global.selected_item }
					]
				} );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

				var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
				job_coder.addClass( 'job-coder' );

				widgetContainer.append( job_coder );
				widgetContainer.append( form_item_input );
				this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_request_column1, '', widgetContainer, true );


				if ( !this.show_job_ui ) {
					this.detachElement( 'job_id' );
				}

				//Job Item
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB_ITEM,
					show_search_inputs: true,
					set_empty: true,
					setRealValueCallBack: (function( val ) {

						if ( val ) {
							job_item_coder.setValue( val.manual_id );
						}
					}),
					field: 'job_item_id',
					added_items: [
						{ value: '-1', label: Global.default_item },
						{ value: '-2', label: Global.selected_item }
					]
				} );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

				var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
				job_item_coder.addClass( 'job-coder' );

				widgetContainer.append( job_item_coder );
				widgetContainer.append( form_item_input );
				this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_request_column1, '', widgetContainer, true );


				if ( !this.show_job_item_ui ) {
					this.detachElement( 'job_item_id' );
				}
			}
		}

		EmbeddedMessage.initUI( this, tab_request );
	},


	setAbsencePolicyFilter: function( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}
		filter.filter_data.user_id = this.current_edit_record.user_id;

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}
		return filter;
	},

	needShowNavigation: function() {
		if ( this.is_viewing && this.current_edit_record && Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			return true;
		} else {
			return false;
		}
	},

	// needShowNavigation: function() {
	// 	if ( this.is_viewing ) {
	// 		return this._super( 'needShowNavigation', [] );
	// 	} else {
	// 		return false;
	// 	}
	// },

	onNavigationClick: function( iconName ) {

		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		var selectedId;
		/* jshint ignore:start */
		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id;
					filter.base_date = this.current_edit_record.date_stamp;

					Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );

				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true, date_stamp: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								filter.user_id = result_data.user_id;
								filter.base_date = result_data.date_stamp;
								Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
								IndexViewController.goToView( 'TimeSheet', filter );

							}
						} );
					}

				}

				break;

			case ContextMenuIconName.edit_employee:
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								IndexViewController.openEditView( $this, 'Employee', result_data.user_id );

							}
						} );
					}

				}
				break;
			case ContextMenuIconName.schedule:

				filter = { filter_data: {} };

				var include_users = null;

				if ( Global.isSet( this.current_edit_record ) ) {

					include_users = [this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id];
					filter.filter_data.include_user_ids = { value: include_users };
					filter.select_date = this.current_edit_record.date_stamp;

					Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
					IndexViewController.goToView( 'Schedule', filter );

				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true, date_stamp: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								include_users = [result_data.user_id];

								filter.filter_data.include_user_ids = include_users;
								filter.select_date = result_data.date_stamp;

								Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
								IndexViewController.goToView( 'Schedule', filter );

							}
						} );
					}

				}
				break;
		}

		/* jshint ignore:end */
	},

	initPermission: function() {
		// this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

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

		// Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemEnabled is not a function on line 207
		var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		if ( company_api && _.isFunction( company_api.isBranchAndDepartmentAndJobAndJobItemEnabled ) ) {
			result = company_api.isBranchAndDepartmentAndJobAndJobItemEnabled( { async: false } ).getResult();
		}

		if ( !result ) {
			this.show_branch_ui = false;
			this.show_department_ui = false;
			this.show_job_ui = false;
			this.show_job_item_ui = false;
		} else {
			if ( !result.branch ) {
				this.show_branch_ui = false;
			}

			if ( !result.department ) {
				this.show_department_ui = false;
			}

			if ( !result.job ) {
				this.show_job_ui = false;
			}

			if ( !result.job_item ) {
				this.show_job_item_ui = false;
			}
		}

	},

	setEditMenuEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		//If edit_child is FALSE and this is a child record, inputs should be read-only.
		if ( this.editOwnerOrChildPermissionValidate( pId ) ) {
			this.enable_edit_view_ui = true;
		} else {
			this.enable_edit_view_ui = false;
		}

		if ( !this.editOwnerOrChildPermissionValidate( pId ) || this.is_add || this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	// Creates the record shipped to the API at setMesssage
	uniformMessageVariable: function( records ) {
		var msg = {};

		msg.subject = this.edit_view_ui_dic['subject'].getValue();
		msg.body = this.edit_view_ui_dic['body'].getValue();
		msg.object_id = this.current_edit_record['id'];
		msg.object_type_id = 50;

		return msg;
	}

} );
