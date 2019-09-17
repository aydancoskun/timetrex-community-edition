PunchesViewController = BaseViewController.extend( {
	el: '#punches_view_container',

	_required_files: {
		10: ['APIPunch', 'APIUser', 'APIUserGroup', 'APIStation', 'APIBranch', 'APIDepartment', 'APIPayPeriod', 'APIUserTitle', 'TImage'],
		15: ['leaflet-timetrex'],
		20: ['APIJob', 'APIJobItem']
	},
	// TODO: breakdown leaflet-timetrex so only the convert functions are needed in ViewControllers.

	user_api: null,
	user_group_api: null,
	api_station: null,
	type_array: null,

	actual_time_label: null,

	is_mass_adding: false,

	init: function ( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PunchesEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'Punches';
		this.script_name = 'PunchesView';
		this.table_name_key = 'punch';
		this.context_menu_name = $.i18n._( 'Punches' );
		this.navigation_label = $.i18n._( 'Punch' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIPunch' ) )();
		this.user_api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.user_group_api = new ( APIFactory.getAPIClass( 'APIUserGroup' ) )();

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = new ( APIFactory.getAPIClass( 'APIJob' ) )();
			this.job_item_api = new ( APIFactory.getAPIClass( 'APIJobItem' ) )();
		}

		this.api_station = new ( APIFactory.getAPIClass( 'APIStation' ) )();

		this.initPermission();
		this.render();

		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	},

	jobUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	noteUIValidate: function ( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	stationValidate: function () {
		if ( PermissionManager.validate( 'station', 'enabled' ) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
	initPermission: function () {
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

		if ( this.stationValidate() ) {
			this.show_station_ui = true;
		} else {
			this.show_station_ui = false;
		}

	},

	initOptions: function () {
		var $this = this;

		this.initDropDownOption( 'type' );

		this.initDropDownOption( 'status', 'status_id', this.api, null, 'status_array' );

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, null, 'user_status_array' );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function ( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );

			}
		} );

	},

	onEditStationDone: function () {
		this.setStation();
	},

	setStation: function () {

		var $this = this;
		var arg = { filter_data: { id: this.current_edit_record.station_id } };

		this.api_station.getStation( arg, {
			onResult: function ( result ) {
				$this.station = result.getResult()[0];
				var widget = $this.edit_view_ui_dic['station_id'];
				widget.setValue( $this.station.type + '-' + $this.station.description );
				widget.css( 'cursor', 'pointer' );

			}
		} );
	},

	uniformVariable: function ( records ) {
		if ( !records.hasOwnProperty( 'time_stamp' ) ) {
			records.time_stamp = false;
		}

		return records;
	},

	buildEditViewUI: function () {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_punch': { 'label': $.i18n._( 'Punch' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPunch' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PUNCH,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_punch_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'punch';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '', null, true );

		// Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );

		form_item_input.TTimePicker( { field: 'punch_time', validation_field: 'time_stamp' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		this.actual_time_label = $( '<span class=\'widget-right-label\'></span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'punch_date', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TRangePicker( { field: 'punch_dates', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		// Punch

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( { field: 'disable_rounding' } );

		var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Disable Rounding' ) + '</span>' );

		widgetContainer.append( form_item_input );

		// Check if view only mode. To prevent option appearing but disabled, as disabled checkboxes are not very clear - same in TimeSheetViewController
		if( this.is_viewing ) {
			// dev-note: not sure if we need to pass widgetContainer here, or if we can omit if its only one element now (due to the if is_viewing).
			// to be safe, will continue to use widgetContainer for this case. We only want to affect viewing mode (hide rounding checkbox), less risk of regression to keep widget container in.
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_punch_column1, '', widgetContainer, true );
		} else {
			widgetContainer.append( label );
			widgetContainer.append( check_box );
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );
		}

		// In/Out
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1 );

		// Branch

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIBranch' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.detachElement( 'branch_id' );
		}

		// Department

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

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
				setRealValueCallBack: ( function ( val ) {

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
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

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
				setRealValueCallBack: ( function ( val ) {
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
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.detachElement( 'job_item_id' );
			}

		}

		if ( ( Global.getProductEdition() >= 20 ) ) {
			// Quantity
			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( { field: 'quantity', width: 40 } );
			good.addClass( 'quantity-input' );

			var good_label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Good' ) + ': </span>' );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( { field: 'bad_quantity', width: 40 } );
			bad.addClass( 'quantity-input' );

			var bad_label = $( '<span class=\'widget-right-label\'>/ ' + $.i18n._( 'Bad' ) + ': </span>' );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_punch_column1, '', widgetContainer, true );

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

		//Location
		if ( Global.getProductEdition() >= 15 ) {

			var latitude = Global.loadWidgetByName( FormItemType.TEXT );
			latitude.TText( { field: 'latitude' } );
			var longitude = Global.loadWidgetByName( FormItemType.TEXT );
			longitude.TText( { field: 'longitude' } );
			widgetContainer = $( '<div class=\'widget-h-box link-widget-box\'></div>' );
			var accuracy = Global.loadWidgetByName( FormItemType.TEXT );
			accuracy.TText( { field: 'position_accuracy' } );
			label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Accuracy' ) + ':</span>' );

			var map_icon = $( '<img class="widget-h-box-mapIcon" src="framework/leaflet/images/marker-icon-red.png" >' );

			this.location_wrapper = $( '<div class="widget-h-box-mapLocationWrapper"></div>' );
			widgetContainer.append( map_icon );
			widgetContainer.append( this.location_wrapper );
			this.location_wrapper.append( latitude );
			this.location_wrapper.append( $( '<span>, </span>' ) );
			this.location_wrapper.append( longitude );
			this.location_wrapper.append( label );
			this.location_wrapper.append( accuracy );
			this.location_wrapper.append( $( '<span>m</span>' ) );
			this.addEditFieldToColumn( $.i18n._( 'Location' ), [latitude, longitude, accuracy], tab_punch_column1, '', widgetContainer, true );
			widgetContainer.click( function () {
				$this.onMapClick();
			} );

			// #2117 - Manual location only supported in edit because we need a punch record to append the data to.
			if ( !this.is_edit && !this.is_viewing ) {
				widgetContainer.parents( '.edit-view-form-item-div' ).hide();
			}
		}
		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'note', width: '100%' } );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.parent().width( '45%' );

		if ( !this.show_note_ui ) {
			this.detachElement( 'note' );
		}

		// Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'station_id' } );
		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.click( function () {
			if ( $this.current_edit_record.station_id && $this.show_station_ui ) {
				IndexViewController.openEditView( $this, 'Station', $this.current_edit_record.station_id );
			}

		} );

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( { field: 'punch_image' } );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );

		if ( this.is_mass_editing ) {
			this.detachElement( 'punch_image' );
			this.detachElement( 'user_id' );
		}

	},

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function () {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			switch ( key ) {
				case 'punch_dates':
					if ( !this.is_mass_editing && ( this.is_mass_adding || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) ) {
						this.attachElement( key );
						widget.css( 'opacity', 1 ); //show
						break;
					} else {
						this.detachElement( key );
						widget.css( 'opacity', 0 ); //hide
						break;
					}
					break;
				case 'punch_date':
					if ( !this.is_mass_editing && ( this.is_mass_adding || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) ) {
						this.detachElement( key );
						widget.css( 'opacity', 0 ); //hide - opposite from above
						break;
					} else {
						this.attachElement( key );
						widget.css( 'opacity', 1 ); //show
						break;
					}
					break;
			}
			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {

					widget.setEnabled( true );

				}
			}

		}

	},

	//Make sure this.current_edit_record is updated before validate
	validate: function () {

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

			record.id = this.mass_edit_record_ids[0];
			record = this.uniformVariable( record );

		} else if ( this.is_mass_adding ) {

			record = this.setMassAddingRecord();

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['validate' + this.api.key_name]( record, {
			onResult: function ( result ) {
				$this.validateResult( result );

			}
		} );
	},

	setMassAddingRecord: function () {
		var record = [];
		var dates_array = this.current_edit_record.punch_dates;

		if ( dates_array.indexOf( ' - ' ) > 0 ) {
			dates_array = this.parserDatesRange( dates_array );
		}

		for ( var i = 0; i < dates_array.length; i++ ) {
			var common_record = Global.clone( this.current_edit_record );
			delete common_record.punch_dates;
			common_record.punch_date = dates_array[i];
			var user_id = this.current_edit_record.user_id;

			if ( Global.isArray( user_id ) ) {
				for ( var j = 0; j < user_id.length; j++ ) {
					var final_record = Global.clone( common_record );
					final_record.user_id = this.current_edit_record.user_id[j];
					final_record = this.uniformVariable( final_record );
					record.push( final_record );
				}
			} else {
				common_record = this.uniformVariable( common_record );
				record.push( common_record );
			}

		}

		return record;
	},

	parserDatesRange: function ( date ) {
		var dates = date.split( ' - ' );
		var resultArray = [];
		var beginDate = Global.strToDate( dates[0] );
		var endDate = Global.strToDate( dates[1] );

		var nextDate = beginDate;

		while ( nextDate.getTime() < endDate.getTime() ) {
			resultArray.push( nextDate.format() );
			nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
		}

		resultArray.push( dates[1] );

		return resultArray;
	},

	setCurrentEditRecordData: function () {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array;
						if ( !this.current_edit_record.punch_dates ) {
							date_array = [this.current_edit_record['punch_date']];
							this.current_edit_record.punch_dates = date_array;
						} else {
							date_array = this.current_edit_record.punch_dates;
						}
						widget.setValue( date_array );
						break;
					case 'country': //popular case
						this.setCountryValue( widget, key );
						break;
					case 'enable_email_notification_message':
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'job_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {

							var args = {};
							args.filter_data = { status_id: 10, user_id: this.current_edit_record.user_id };
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							args = {};
							args.filter_data = { status_id: 10, job_id: this.current_edit_record.job_id };
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					case 'station_id':
						if ( this.current_edit_record[key] ) {
							this.setStation();
						} else {
							widget.setValue( 'N/A' );
							widget.css( 'cursor', 'default' );
						}
						break;
					case 'punch_image':
						var station_form_item = this.edit_view_form_item_dic['station_id'];
						if ( this.current_edit_record['has_image'] ) {
							this.attachElement( 'punch_image' );
							widget.setValue( ServiceCaller.fileDownloadURL + '?object_type=punch_image&parent_id=' + this.current_edit_record.user_id + '&object_id=' + this.current_edit_record.id );

						} else {
							this.detachElement( 'punch_image' );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		}
		this.actual_time_label.text( actual_time_value );

		this.collectUIDataToCurrentEditRecord();
		this.setLocationValue();

		this.setEditViewDataDone();
		this.isEditChange();

	},
	setLocationValue: function ( location_data ) {
		if ( Global.getProductEdition() >= 15 ) {
			if( location_data ) {
				this.current_edit_record.latitude = location_data.latitude;
				this.current_edit_record.longitude = location_data.longitude;
				this.current_edit_record.position_accuracy = location_data.position_accuracy; //If position is manually modified, it should always be set to 0m.
			}
			this.edit_view_ui_dic['latitude'].setValue( this.current_edit_record.latitude );
			this.edit_view_ui_dic['longitude'].setValue( this.current_edit_record.longitude );
			this.edit_view_ui_dic['position_accuracy'].setValue( this.current_edit_record.position_accuracy ? this.current_edit_record.position_accuracy : 0 );

			if ( !this.current_edit_record.latitude && !this.is_mass_editing ) {
				this.location_wrapper.hide();
			} else {
				this.location_wrapper.show();
			}
		}

	},

	isEditChange: function () {

		if ( this.current_edit_record.id || this.is_mass_editing ) {
			this.edit_view_ui_dic['user_id'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_id'].setEnabled( true );
		}
	},

	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function () {
		// Remove this on 14.9.14 because adding tab url support, ned set url when tab index change and
		// need know what's current doing action. See if this cause any problem
		//LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );

		if ( this.is_edit == true ) {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( false );
		} else {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( true );
		}

		if ( this.is_edit == false && ( this.current_edit_record.latitude == 0 || this.current_edit_record.longitude == 0 ) ) {
			$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).hide();
		} else {
			$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).show();
		}

		this.navigation.setValue( this.current_edit_record.id );

		$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
		TTPromise.resolve( 'init', 'init' );
	},

	initSubLogView: function ( tab_id ) {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			return;
		}

		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function ( filter ) {

				filter['table_name_object_id'] = {
					'punch': [this.parent_edit_record.id],
					'punch_control': [this.parent_edit_record.punch_control_id]
				};

				return filter;
			};

			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/core/log/LogViewController.js', function () {
			var tab = $this.edit_view_tab.find( '#' + tab_id );

			var firstColumn = tab.find( '.first-column-sub-view' );

			TTPromise.add( 'initSubAudit', 'init' );
			TTPromise.wait( 'initSubAudit', 'init', function () {
				firstColumn.css( 'opacity', '1' );
			} );

			firstColumn.css( 'opacity', '0' ); //Hide the grid while its loading/sizing.

			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function ( filter ) {
				filter['table_name_object_id'] = {
					'punch': [this.parent_edit_record.id],
					'punch_control': [this.parent_edit_record.punch_control_id]
				};

				return filter;
			};
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.postInit = function () {
				this.initData();
			};
		}
	},

//	showNoResultCover: function() {
//
//		this.removeNoResultCover();
//		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
//		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: false} );
//		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );
//
//		var grid_div = $( this.el ).find( '.grid-div' );
//
//		grid_div.append( this.no_result_box );
//
//		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
//	},

	buildOtherFieldUI: function ( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab_punch = this.edit_view_tab.find( '#tab_punch' );
		var tab_punch_column1 = tab_punch.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: field } );
			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab_punch_column1 );

			input_div.insertBefore( this.edit_view_form_item_dic['note'] );

			form_item_input.setValue( $this.current_edit_record[field] );
		}
		form_item_input.css( 'opacity', 1 );
		form_item_input.css( 'minWidth', 300 );

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	onAddResult: function ( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;
		result_data.punch_date = ( new Date() ).format();

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	buildSearchFields: function () {

		this._super( 'buildSearchFields' );
		var default_args = { permission_section: 'punch' };
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
				layout_name: ALayoutIDs.USER,
				default_args: default_args,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

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
				label: $.i18n._( 'Title' ),
				in_column: 1,
				field: 'title_id',
				layout_name: ALayoutIDs.USER_TITLE,
				api_class: ( APIFactory.getAPIClass( 'APIUserTitle' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
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
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
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
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Punch Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: ( APIFactory.getAPIClass( 'APIBranch' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Punch Department' ),
				in_column: 2,
				field: 'department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: ( APIFactory.getAPIClass( 'APIDepartment' ) ),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: ALayoutIDs.JOB,
				api_class: ( Global.getProductEdition() >= 20 ) ? ( APIFactory.getAPIClass( 'APIJob' ) ) : null,
				multiple: true,
				basic_search: false,
				adv_search: ( this.show_job_ui && ( Global.getProductEdition() >= 20 ) ),
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: ALayoutIDs.JOB_ITEM,
				api_class: ( Global.getProductEdition() >= 20 ) ? ( APIFactory.getAPIClass( 'APIJobItem' ) ) : null,
				multiple: true,
				basic_search: false,
				adv_search: ( this.show_job_item_ui && ( Global.getProductEdition() >= 20 ) ),
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

	buildContextMenuModels: function () {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var edit = new RibbonSubMenu( {
			label: $.i18n._( 'Edit' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		var mass_edit = new RibbonSubMenu( {
			label: $.i18n._( 'Mass<br>Edit' ),
			id: ContextMenuIconName.mass_edit,
			group: editor_group,
			icon: Icons.mass_edit,
			permission_result: true,
			permission: null
		} );

		var del = new RibbonSubMenu( {
			label: $.i18n._( 'Delete' ),
			id: ContextMenuIconName.delete_icon,
			group: editor_group,
			icon: Icons.delete_icon,
			permission_result: true,
			permission: null
		} );

		var delAndNext = new RibbonSubMenu( {
			label: $.i18n._( 'Delete<br>& Next' ),
			id: ContextMenuIconName.delete_and_next,
			group: editor_group,
			icon: Icons.delete_and_next,
			permission_result: true,
			permission: null
		} );

		var copy_as_new = new RibbonSubMenu( {
			label: $.i18n._( 'Copy<br>as New' ),
			id: ContextMenuIconName.copy_as_new,
			group: editor_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var save = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save,
			group: editor_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_and_continue = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Continue' ),
			id: ContextMenuIconName.save_and_continue,
			group: editor_group,
			icon: Icons.save_and_continue,
			permission_result: true,
			permission: null
		} );

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		var save_and_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Copy' ),
			id: ContextMenuIconName.save_and_copy,
			group: editor_group,
			icon: Icons.save_and_copy,
			permission_result: true,
			permission: null
		} );

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
			permission_result: true,
			permission: null
		} );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		if ( Global.getProductEdition() >= 15 ) {
			var map = new RibbonSubMenu( {
				label: $.i18n._( 'Map' ),
				id: ContextMenuIconName.map,
				group: other_group,
				icon: Icons.map,
				permission_result: true,
				permission: null
			} );

			var ttimport = new RibbonSubMenu( {
				label: $.i18n._( 'Import' ),
				id: ContextMenuIconName.import_icon,
				group: other_group,
				icon: Icons.import_icon,
				permission_result: true,
				permission: null,
				sort_order: 8000
			} );
		}

		var export_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.export_excel,
			group: other_group,
			icon: Icons.export_excel,
			permission_result: true,
			permission: null,
			sort_order: 9000
		} );

		return [menu];

	},

	onMapClick: function () {
		// only trigger map load in specific product editions.
		if ( ( Global.getProductEdition() >= 15 ) ) {
			ProgressBar.showProgressBar();

			// TODO: this is repeated below, perhaps in future now that getFilterColumnsFromDisplayColumns() is commented out, this can be consolidated?
			var data = {
				filter_columns: {
					id: true,
					latitude: true,
					longitude: true,
					punch_date: true,
					punch_time: true,
					position_accuracy: true,
					user_id: true
				}
			};

			var punches = [];
			var map_options = {};

			if ( this.is_edit ) {
				//when editing, if the user reloads, the grid's selected id array become the whole grid.
				//to avoid mapping every punch in that scenario we need to grab the current_edit_record, rather than pull data from getGridSelectIdArray()
				//check for mass edit as well. <-- not sure what this refers to, assuming the same happens in mass edit, but maps are disabled on mass edit atm.
				punches.push( this.current_edit_record );
				// from the edit view we want to allow single markers to be draggable.
				if( !this.is_viewing ) {
					// make sure that when view only (so no save) marker is not draggable, and thus no new marker can be added either.
					map_options.single_marker_draggable = true;
				}
			} else {
				var ids = this.getGridSelectIdArray();
				// from the map icon on the ribbon bar we want to PREVENT single markers being draggable. As this is intended as a read only view.
				map_options.single_marker_draggable = false;

				data.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				if ( ids.length > 0 ) {
					data.filter_data.id = ids;
				}
				// data.filter_columns = this.getFilterColumnsFromDisplayColumns()
				data.filter_columns.first_name = true;
				data.filter_columns.last_name = true;
				data.filter_columns.user_id = true;
				data.filter_columns.punch_date = true;
				data.filter_columns.punch_time = true;
				data.filter_columns.time_stamp = true;
				data.filter_columns.status = true;
				data.filter_columns.punch_control_id = true;
				data.filter_columns.branch = true;
				data.filter_columns.branch_id = true;
				data.filter_columns.department = true;
				data.filter_columns.department_id = true;
				data.filter_columns.job_manual_id = true;
				data.filter_columns.job = true;
				data.filter_columns.job_id = true;
				data.filter_columns.job_item_manual_id = true;
				data.filter_columns.job_item = true; // also known as Task
				data.filter_columns.job_item_id = true;
				data.filter_columns.total_time = true;
				data.filter_columns.latitude = true;
				data.filter_columns.longitude = true;
				data.filter_columns.position_accuracy = true;

				punches = this.api.getPunch( data, { async: false } ).getResult();
			}

			if ( !this.is_mass_editing ) {
				var processed_punches_for_map = TTMapLib.TTConvertMapData.processPunchesFromViewController( punches, map_options );
				IndexViewController.openEditView( this, 'Map', processed_punches_for_map );
			}
		}
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.edit_employee:
				this.onNavigationClick( id );
				break;
			case ContextMenuIconName.map:
				this.onMapClick();
				break;
			case ContextMenuIconName.import_icon:
				this.onImportClick();
				break;
		}
	},

	onImportClick: function() {
		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'punch', function() {
			$this.search();
		} );
	},

	setDefaultMenu: function ( doNotSetFocus ) {

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
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function () {

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
				case ContextMenuIconName.timesheet:
					this.setEditMenuNavViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuNavEditIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	onNavigationClick: function ( iconName ) {
		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id;
					filter.base_date = this.current_edit_record.punch_date;
					Global.addViewTab( this.viewId, $.i18n._( 'Punches' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						var selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function ( result ) {

								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								filter.user_id = result_data.user_id;
								filter.base_date = result_data.punch_date;

								Global.addViewTab( $this.viewId, $.i18n._( 'Punches' ), window.location.href );
								IndexViewController.goToView( 'TimeSheet', filter );

							}
						} );
					}

				}

				break;

			case ContextMenuIconName.edit_employee:
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function ( result ) {
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
		}
	},

	setEditMenuSaveAndContinueIcon: function ( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDateOrMassUser() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	copyAsNewResetIds: function( data ) {
		//override where needed.
		data.id = '';
		data.punch_control_id = ''; //Clear the punch_control_id record as well so we don't force the punch to be assigned to it.
		return data;
	},

	_continueDoCopyAsNew: function () {
		var $this = this;
		this.is_add = true;
		this.is_mass_adding = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record = this.copyAsNewResetIds( this.current_edit_record );
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();
		} else {
			this._super( '_continueDoCopyAsNew' );
		}
	},

	isMassDateOrMassUser: function () {
		if ( this.is_mass_adding ) {
			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
				return true;
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length > 1 ) {
				return true;
			}

			return false;
		}

		return false;
	},

	onSaveAndCopy: function ( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();
		} else {
			record = this.uniformVariable( record );
		}

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function ( result ) {
				$this.onSaveAndCopyResult( result );

			}
		} );
	},

	onSaveAndNewClick: function ( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';
		if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();
		} else {
			record = this.uniformVariable( record );
		}
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function ( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
	},

	onMassEditClick: function () {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		this.is_mass_adding = false;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function ( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function ( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function ( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function ( result1 ) {
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

	onEditClick: function ( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
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
		this.is_mass_adding = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function ( result ) {
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

	},

	onSaveAndContinue: function ( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';

		if ( this.is_mass_adding ) {

			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
				this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length === 1 ) {
				this.current_edit_record.user_id = this.current_edit_record.user_id[0];
			}

		}

		this.current_edit_record = this.uniformVariable( this.current_edit_record );

		this.api['set' + this.api.key_name]( this.current_edit_record, false, ignoreWarning, {
			onResult: function ( result ) {
				$this.onSaveAndContinueResult( result );
			}
		} );
	},

	onFormItemChange: function ( target, doNotValidate ) {

		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'user_id':
				if ( $.isArray( this.current_edit_record.user_id ) ) {
					this.is_mass_adding = true;
				} else {
					this.is_mass_adding = false;
				}
				this.setEditMenu();
				break;
			case 'punch_date':
				this.current_edit_record.punch_dates = [c_value];
			case 'punch_dates':
				this.setEditMenu();
				break;
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue() );
					this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_item_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, c_value );
				}
				break;
			default:
				this.current_edit_record[key] = c_value;
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onSaveClick: function ( ignoreWarning ) {
		var $this = this;
		var record;
		var key;
		var rows;
		var i;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function ( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				record.push( common_record );

			} );
		} else if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function ( result ) {

				$this.onSaveResult( result );

			}
		} );
	},

	onMapSaveClick: function(dataset, successCallback) {
		this.savePunchPosition(dataset, successCallback);
	},

	savePunchPosition: function (moved_unsaved_markers, successCallback) {
		if ( !moved_unsaved_markers || moved_unsaved_markers.length !== 1) {
			Debug.Text( 'ERROR: Invalid params/data passed to function.', 'PunchesViewController.js', 'PunchesViewController', 'savePunchPosition', 1 );
			return false;
		}

		// Regardless of record type, we want to just pass the value back, rather than a api save from map, then another save from parent view.
		// Map info will only be saved if user clicks save on the parent edit view.
		this.setLocationValue(moved_unsaved_markers[0]);
		successCallback();
		this.is_changed = true;
		return true;

		// check if this is a new record
		// if (moved_unsaved_markers[0].id === undefined) {
		// 	// 1. data is found in unconverted, with no id, but this has no start point.
		// 	// 2. data ends up in tt_marker, with no id, but it does have a marker point.
		// 	// Likely user route was -> New punch -> Open Map or -> New punch -> Open Map -> save map -> re-open map before new punch is saved
		//
		// 	this.setLocationValue(moved_unsaved_markers[0]);
		// 	successCallback();
		// 	return true;
		// } else {
		// 	// save the changes to the API if existing record
		// 	var this_PunchesViewController = this; // Reference local scope for callbacks to reference further down in the function. Var name also identifies what 'this' is referring to.
		// 	this.api.setPunch( moved_unsaved_markers[0], {
		// 		onResult: function ( result ) {
		// 			if ( result.isValid() ) {
		// 				successCallback();
		// 				this_PunchesViewController.search();
		// 				if ( this_PunchesViewController.edit_view ) {
		// 					this_PunchesViewController.onEditClick( this_PunchesViewController.current_edit_record.id );
		// 				}
		// 				return true;
		// 			}
		// 		}
		// 	} );
		// }
	},

	getSelectEmployee: function ( full_item ) {
		var user;
		if ( full_item ) {
			user = LocalCacheData.getLoginUser();
		} else {
			user = LocalCacheData.getLoginUser().id;
		}
		return user;
	},

	getFilterColumnsFromDisplayColumns: function ( column_filter, enable_system_columns ) {
		if ( column_filter == undefined ) {
			column_filter = {};
		}
		column_filter.latitude = true;
		column_filter.longitude = true;
		return this._getFilterColumnsFromDisplayColumns( column_filter, enable_system_columns );
	},
} );
