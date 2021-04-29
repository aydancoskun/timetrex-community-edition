class RecurringScheduleTemplateControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#recurring_schedule_template_control_view_container',


			sub_document_view_controller: null,

			document_object_type_id: null,

			recurring_schedule_template_api: null,

			schedule_api: null,

			recurring_schedule_status_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RecurringScheduleTemplateControlEditView.html';
		this.permission_id = 'recurring_schedule_template';
		this.viewId = 'RecurringScheduleTemplateControl';
		this.script_name = 'RecurringScheduleTemplateControlView';
		this.table_name_key = 'recurring_schedule_template_control';
		this.context_menu_name = $.i18n._( 'Recurring Templates' );
		this.navigation_label = $.i18n._( 'Recurring Template' ) + ':';
		this.api = TTAPI.APIRecurringScheduleTemplateControl;
		this.schedule_api = TTAPI.APISchedule;
		this.recurring_schedule_template_api = TTAPI.APIRecurringScheduleTemplate;

		this.document_object_type_id = 10;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RecurringScheduleTemplateControl' );
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', null, this.recurring_schedule_template_api, function( res ) {
			res = res.getResult();
			$this.recurring_schedule_status_array = Global.buildRecordArray( res );
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_recurring_template': { 'label': $.i18n._( 'Recurring Template' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRecurringScheduleTemplateControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		this.edit_view_tab.css( 'max-width', 'none' );

		if ( Global.getProductEdition() >= 20 ) {
			this.edit_view_tab.css( 'min-width', '1250px' );
		} else if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.css( 'min-width', '1050px' );
		} else {
			this.edit_view_tab.css( 'min-width', '950px' );
		}

		//Tab 0 start

		var tab_recurring_template = this.edit_view_tab.find( '#tab_recurring_template' );

		var tab_recurring_template_column1 = tab_recurring_template.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_template_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_template_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_recurring_template_column1 );

		form_item_input.parent().width( '45%' );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'created_by_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Created By' ), form_item_input, tab_recurring_template_column1 );

		form_item_input.parent().width( '45%' );

		//Inside editor

		var inside_editor_div = tab_recurring_template.find( '.inside-editor-div' );

		var args = {
			week: $.i18n._( 'Week' ),
			status: $.i18n._( 'Status' ),
			week_names: 'S&nbsp;&nbsp;M&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;T&nbsp&nbsp;F&nbsp;&nbsp;S',
			shift_time: $.i18n._( 'Shift Time' ),
			total: $.i18n._( 'Total' ),
			schedule_policy: $.i18n._( 'Schedule Policy' ),
			branch_department: $.i18n._( 'Branch/Department' ),
			job_task: $.i18n._( 'Job/Task' ),
			open_shift_multiplier: $.i18n._( 'Open Shift Multiplier' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			title: $.i18n._( 'NOTE: To set different In/Out times for each day of the week, add additional weeks all with the same week number.' ),
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			parent_controller: this,
			api: this.recurring_schedule_template_api,
			render: getRender(),
			render_args: args,
			row_render: getRowRender()

		} );

		function getRender() {
			var render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender_1.html';
			if ( Global.getProductEdition() >= 20 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender.html';
			} else if ( Global.getProductEdition() >= 15 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender_2.html';
			}

			return render;
		}

		function getRowRender() {
			var render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow_1.html';
			if ( Global.getProductEdition() >= 20 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow.html';
			} else if ( Global.getProductEdition() >= 15 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow_2.html';
			}

			return render;
		}

		inside_editor_div.append( this.editor );
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		if ( !this.is_mass_editing ) {
			this.initInsideEditorData();
			this.edit_view.find( '.inside-editor-div' ).show();
		} else {
			this.edit_view.find( '.inside-editor-div' ).hide();
		}
	}

	initInsideEditorData() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			$this.editor.removeAllRows();
			$this.editor.getDefaultData();

		} else {

			args.filter_data.recurring_schedule_template_control_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			$this.recurring_schedule_template_api['get' + $this.recurring_schedule_template_api.key_name]( args, {
				onResult: function( res ) {
					if ( !$this.edit_view ) {
						return;
					}
					var data = res.getResult();
					$this.editor.setValue( data );

				}
			} );

		}
	}

	insideEditorAddRow( data, index ) {

		var form_item_input;

		var $this = this;
		if ( !data ) {
			this.getDefaultData( index );
		} else {
			var form_item_input;
			var widgetContainer;

			var row_id = ( data.id && this.parent_controller.current_edit_record.id ) ? data.id : TTUUID.generateUUID();
			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = {}; //Save each row's widgets

			//Build row widgets

			// Week
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'week', width: 40 } );
			form_item_input.setValue( data.week ? data.week : 1 );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );
			form_item_input.attr( 'recurring_schedule_template_id', row_id );
			form_item_input.attr( 'date_stamp', data.date_stamp ); //Needed to prepend start/end times with so they can be parsed properly and calculate Total Time.

			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Status
			widgetContainer = $( '<div class=\'recurring-template-status-div\'></div>' );

			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'status_id' } );
			form_item_input.setSourceData( Global.addFirstItemToArray( this.parent_controller.recurring_schedule_status_array ) );
			form_item_input.setValue( data.status_id ? data.status_id : 10 );
			widgets[form_item_input.getField()] = form_item_input;

			form_item_input.bind( 'formItemChange', function( e, target ) {
				if ( target.getValue() == 10 ) {
					widgets['absence_policy_id'].hide();
				} else if ( target.getValue() == 20 ) {
					widgets['absence_policy_id'].show();
				}
			} );

			widgetContainer.append( form_item_input );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIAbsencePolicy,
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.ABSENCES_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'absence_policy_id'
			} );

			form_item_input.css( 'position', 'absolute' );
			form_item_input.css( 'left', '0' );
			form_item_input.css( 'top', '30px' );
			form_item_input.css( 'z-index', '1' ); //For some reason if this overlaps with the "checkboxes", it goes behind that div and makes the down arrow unclickable.
			form_item_input.setValue( data.absence_policy_id ? data.absence_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;
			this.setWidgetEnableBaseOnParentController( form_item_input );
			widgetContainer.append( form_item_input );

			row.children().eq( 1 ).append( widgetContainer );

			// sun mon tue wed thu fri sat
			var widgetContainer2 = $( '<div class=\'widget-h-box\'></div>' );
			// Sun
			var form_item_sun_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sun_checkbox.TCheckbox( { field: 'sun' } );
			form_item_sun_checkbox.setValue( data.sun ? data.sun : false );
			widgets[form_item_sun_checkbox.getField()] = form_item_sun_checkbox;
			widgetContainer2.append( form_item_sun_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_sun_checkbox );
			// Mon
			var form_item_mon_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_mon_checkbox.TCheckbox( { field: 'mon' } );
			form_item_mon_checkbox.setValue( data.mon ? data.mon : false );
			widgets[form_item_mon_checkbox.getField()] = form_item_mon_checkbox;
			widgetContainer2.append( form_item_mon_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_mon_checkbox );
			// Tue
			var form_item_tue_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_tue_checkbox.TCheckbox( { field: 'tue' } );
			form_item_tue_checkbox.setValue( data.tue ? data.tue : false );
			widgets[form_item_tue_checkbox.getField()] = form_item_tue_checkbox;
			widgetContainer2.append( form_item_tue_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_tue_checkbox );
			// Wed
			var form_item_wed_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_wed_checkbox.TCheckbox( { field: 'wed' } );
			form_item_wed_checkbox.setValue( data.wed ? data.wed : false );
			widgets[form_item_wed_checkbox.getField()] = form_item_wed_checkbox;
			widgetContainer2.append( form_item_wed_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_wed_checkbox );
			// Thu
			var form_item_thu_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_thu_checkbox.TCheckbox( { field: 'thu' } );
			form_item_thu_checkbox.setValue( data.thu ? data.thu : false );
			widgets[form_item_thu_checkbox.getField()] = form_item_thu_checkbox;
			widgetContainer2.append( form_item_thu_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_thu_checkbox );
			// Fri
			var form_item_fri_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_fri_checkbox.TCheckbox( { field: 'fri' } );
			form_item_fri_checkbox.setValue( data.fri ? data.fri : false );
			widgets[form_item_fri_checkbox.getField()] = form_item_fri_checkbox;
			widgetContainer2.append( form_item_fri_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_fri_checkbox );
			// Sat
			var form_item_sat_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sat_checkbox.TCheckbox( { field: 'sat' } );
			form_item_sat_checkbox.setValue( data.sat ? data.sat : false );
			widgets[form_item_sat_checkbox.getField()] = form_item_sat_checkbox;
			widgetContainer2.append( form_item_sat_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_sat_checkbox );

			row.children().eq( 2 ).append( widgetContainer2 );

			// Shift Time
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var divContainer1 = $( '<div style=\'text-align: left; \'></div>' );

			var label_1 = $( '<span class=\'widget-right-label recurring-template-widget-right-label\' style=\'display: inline-block; width: 28px; vertical-align: middle;\'> ' + $.i18n._( 'In' ) + ': ' + ' </span>' );
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'start_time' } );
			form_item_input.setValue( data.start_time ? data.start_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {

				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );

			widgets[form_item_input.getField() + row_id] = form_item_input;
			this.parent_controller.edit_view_ui_validation_field_dic[form_item_input.getField() + row_id] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			var divContainer2 = $( '<div style=\'text-align: left; margin-top: 5px;\'></div>' );

			var label_2 = $( '<span class=\'widget-right-label recurring-template-widget-right-label\' style=\'display: inline-block; width: 28px; vertical-align: middle;\' > ' + $.i18n._( 'Out' ) + ': ' + ' </span>' );
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'end_time' } );
			form_item_input.setValue( data.end_time ? data.end_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );

			widgets[form_item_input.getField() + row_id] = form_item_input;
			this.parent_controller.edit_view_ui_validation_field_dic[form_item_input.getField() + row_id] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 3 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Total
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'total_time' } );
			form_item_input.setValue( data.total_time ? Global.getTimeUnit( data.total_time ) : '' ); //
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 4 ).append( form_item_input );

			// Schedule Policy

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APISchedulePolicy,
				width: 80,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.SCHEDULE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'schedule_policy_id'
			} );

			form_item_input.setValue( data.schedule_policy_id ? data.schedule_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 5 ).append( form_item_input );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Branch / Department

			widgetContainer = $( '<div class=\'widget-h-box recurring-template-widget-h-box\'></div>' );

			divContainer1 = $( '<div></div>' );

			label_1 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 74px;\'> ' + $.i18n._( 'Branch' ) + ': ' + ' </span>' );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIBranch,
				width: 80,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.BRANCH,
				show_search_inputs: true,
				set_any: true,
				field: 'branch_id',
				custom_first_label: Global.default_item
			} );

			if ( data.branch_id.toUpperCase() === TTUUID.not_exist_id.toUpperCase() ) {
				form_item_input.set_default_value = true;
			} else {
				form_item_input.setValue( data.branch_id ? data.branch_id : '' );
			}
			widgets[form_item_input.getField()] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );

			divContainer2 = $( '<div style=\'margin-top: 5px; float: left\'></div>' );

			label_2 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 74px;\'> ' + $.i18n._( 'Department' ) + ': ' + ' </span>' );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIDepartment,
				width: 80,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.DEPARTMENT,
				show_search_inputs: true,
				set_any: true,
				field: 'department_id',
				custom_first_label: Global.default_item
			} );
			form_item_input.setValue( ( data.department_id ) ? data.department_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 6 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Job/Task

			if ( ( Global.getProductEdition() >= 20 ) ) {

				widgetContainer = $( '<div class=\'widget-h-box recurring-template-widget-h-box\'></div>' );

				divContainer1 = $( '<div></div>' );

				label_1 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 32px;\'> ' + $.i18n._( 'Job' ) + ': ' + ' </span>' );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJob,
					width: 80,
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB,
					show_search_inputs: true,
					set_any: true,
					field: 'job_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_id ? data.job_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer1.append( label_1 );
				divContainer1.append( form_item_input );

				widgetContainer.append( divContainer1 );

				divContainer2 = $( '<div style=\'margin-top: 5px; float: left\'></div>' );

				label_2 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 32px;\'> ' + $.i18n._( 'Task' ) + ': ' + ' </span>' );
				this.setWidgetEnableBaseOnParentController( form_item_input );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJobItem,
					width: 80,
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB_ITEM,
					show_search_inputs: true,
					set_any: true,
					field: 'job_item_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_item_id ? data.job_item_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer2.append( label_2 );
				divContainer2.append( form_item_input );

				widgetContainer.append( divContainer2 );

				row.children().eq( 7 ).append( widgetContainer );
				this.setWidgetEnableBaseOnParentController( form_item_input );

			}

			if ( Global.getProductEdition() >= 15 ) {
				// Open Shift Multiplier
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: 'open_shift_multiplier', width: 20 } );
				form_item_input.setValue( data.open_shift_multiplier ? data.open_shift_multiplier : 1 );
				widgets[form_item_input.getField()] = form_item_input;

				if ( Global.getProductEdition() <= 15 ) {
					row.children().eq( 7 ).append( form_item_input );
				} else {
					row.children().eq( 8 ).append( form_item_input );
				}

				this.setWidgetEnableBaseOnParentController( form_item_input );
			}

			if ( typeof index != 'undefined' ) {

				row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
				this.rows_widgets_array.splice( ( index ), 0, widgets );

			} else {
				$( render ).append( row );
				this.rows_widgets_array.push( widgets );
			}

			if ( this.parent_controller.is_viewing ) {
				row.find( '.control-icon' ).hide();
			}

			if ( widgets.status_id.getValue() == 10 ) {
				widgets.absence_policy_id.css( 'display', 'none' );
			} else if ( widgets.status_id.getValue() == 20 ) {
				widgets.absence_policy_id.css( 'display', 'block' );
			}

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();
		}
	}

	onRowChanges( row_widgets ) {
		var recurring_schedule_template_id = row_widgets.week.attr( 'recurring_schedule_template_id' );
		var date_stamp = row_widgets.week.attr( 'date_stamp' );

		if ( recurring_schedule_template_id ) {
			var startTime = date_stamp +' '+ row_widgets['start_time' + recurring_schedule_template_id].getValue();
			var endTime = date_stamp +' '+ row_widgets['end_time' + recurring_schedule_template_id].getValue();
			var schedulePolicyId = row_widgets.schedule_policy_id.getValue();

			if ( startTime !== '' && endTime !== '' && schedulePolicyId !== '' ) {
				var result = this.schedule_api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, { async: false } );
				if ( result ) {
					var total_time = result.getResult();

					row_widgets.total_time.setValue( Global.getTimeUnit( total_time ) );
				}
			}

			this.validate();
		}
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];

			var recurring_schedule_template_id = row.week.attr( 'recurring_schedule_template_id' );
			var date_stamp = row.week.attr( 'date_stamp' );

			if ( recurring_schedule_template_id ) {
				var data = {
					id: recurring_schedule_template_id,

					week: row.week.getValue(),
					status_id: row.status_id.getValue(),

					mon: row.mon.getValue(),
					tue: row.tue.getValue(),
					wed: row.wed.getValue(),
					thu: row.thu.getValue(),
					fri: row.fri.getValue(),
					sat: row.sat.getValue(),
					sun: row.sun.getValue(),

					date_stamp: date_stamp,
					start_time: row['start_time' + recurring_schedule_template_id].getValue(),
					end_time: row['end_time' + recurring_schedule_template_id].getValue(),
					total_time: null,

					branch_id: row.branch_id.getValue(),
					department_id: row.department_id.getValue(),

					absence_policy_id: row.absence_policy_id.getValue(),
					schedule_policy_id: row.schedule_policy_id.getValue()
				};

				if ( Global.getProductEdition() >= 15 ) {
					data.open_shift_multiplier = row.open_shift_multiplier.getValue();
				}

				if ( Global.getProductEdition() >= 20 ) {
					data.job_id = row.job_id.getValue();
					data.job_item_id = row.job_item_id.getValue();
				}

				data.recurring_schedule_template_control_id = current_edit_item_id;
				result.push( data );
			}
		}

		return result;
	}

	insideEditorSetValue( val ) {
		var len = val.length;
		this.removeAllRows();

		if ( len > 0 ) {
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.getDefaultData();
		}
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].week.attr( 'recurring_schedule_template_id' );
		if ( TTUUID.isUUID( remove_id ) && remove_id != TTUUID.zero_id && remove_id != TTUUID.not_exist_id ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );

		this.removeLastRowLine();
	}

	uniformVariable( records ) {
		if ( !this.is_mass_editing ) {
			records.recurring_schedule_template = this.editor.getValue( this.refresh_id );
		}
		return records;
	}

	renameObjectKey( obj, old_key, new_key ) {
		if ( old_key !== new_key ) {
			Object.defineProperty( obj, new_key,
				Object.getOwnPropertyDescriptor( obj, old_key ) );
			delete obj[old_key];
		}
	}

	_continueDoCopyAsNew() {
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				//Fix JS exception: Uncaught TypeError: Cannot read property 'getValue' of undefined
				//start_time,end_time object keys are appended with the recurring_schedule_template_id, so when copying records we need to rename them to use the new recurring_schedule_template_id
				var new_uuid = TTUUID.generateUUID();
				var old_recurring_schedule_template_id = this.editor.rows_widgets_array[i].week.attr( 'recurring_schedule_template_id' );

				this.renameObjectKey( this.editor.rows_widgets_array[i], 'start_time' + old_recurring_schedule_template_id, 'start_time' + new_uuid );
				this.renameObjectKey( this.editor.rows_widgets_array[i], 'end_time' + old_recurring_schedule_template_id, 'end_time' + new_uuid );

				this.editor.rows_widgets_array[i].week.attr( 'recurring_schedule_template_id', new_uuid );
			}
		}
		super._continueDoCopyAsNew();
	}

	onCopyAsNewResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];
		this.copied_record_id = result_data.id;
		result_data.id = '';
		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
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
				label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Template' ),
				in_column: 1,
				field: 'id',
				layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
				api_class: TTAPI.APIRecurringScheduleTemplateControl,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
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
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [{
				label: $.i18n._( 'Recurring<br>Schedules' ),
				id: ContextMenuIconName.recurring_schedule,
				group: 'navigation',
				icon: Icons.recurring_schedule
			}]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.recurring_schedule:
				this.onNavigationClick( id );
				break;
		}
	}

	onNavigationClick( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = { filter_data: {} };

		var recurring_schedule_template_control_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			recurring_schedule_template_control_ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				recurring_schedule_template_control_ids.push( grid_selected_row.id );
			} );
		}

		filter.filter_data.recurring_schedule_template_control_id = recurring_schedule_template_control_ids;

		switch ( iconName ) {
			case ContextMenuIconName.recurring_schedule:
				Global.addViewTab( this.viewId, $.i18n._( 'Recurring Templates' ), window.location.href );
				IndexViewController.goToView( 'RecurringScheduleControl', filter );
				break;
		}
	}

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
				case ContextMenuIconName.recurring_schedule:
					this.setDefaultMenuRecurringScheduleIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();
	}

	setDefaultMenuRecurringScheduleIcon( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'RecurringScheduleControl' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	}

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
				case ContextMenuIconName.recurring_schedule:
					this.setEditMenuRecurringScheduleIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();
	}

	setEditMenuRecurringScheduleIcon( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	}
}
