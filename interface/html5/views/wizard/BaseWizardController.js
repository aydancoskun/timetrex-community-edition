BaseWizardController = BaseWindowController.extend( {

	steps: 0,
	title: 'Wizard',
	current_step: 1,

	content_div: null,

	next_btn: null,
	back_btn: null,
	done_btn: null,
	progress: null,
	progress_label: null,

	stepsWidgetDic: null,
	stepsDataDic: null,

	default_data: null,

	call_back: null,

	saved_user_generic_data: null,

	script_name: null,

	user_generic_data_api: null,

	wizard_id: null,

	events: {
		'click .close-btn': 'onCloseClick',
		'click .forward-btn': 'onNextClick',
		'click .back-btn': 'onBackClick',
		'click .done-btn': 'onDoneClick'
	},

	initialize: function() {
		this.content_div = $( this.el ).find( '.content' );
		this.stepsWidgetDic = {};
		this.stepsDataDic = {};

		this.default_data = BaseWizardController.default_data;
		this.call_back = BaseWizardController.call_back;

		BaseWizardController.call_back = null;
		BaseWizardController.default_data = null;

		this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();

		LocalCacheData.current_open_wizard_controller = this;

		this.setDefaultDataToSteps();
	},

	setDefaultDataToSteps: function() {

	},

	getDefaultData: function( key ) {

		if ( !this.default_data ) {
			return null;
		}

		return this.default_data[key];
	},

	render: function() {
		var title = $( this.el ).find( '.title' );
		var title_1 = $( this.el ).find( '.title-1' );
		this.progress = $( this.el ).find( '.progress' );
		this.progress_label = $( this.el ).find( '.steps' );

		this.progress.attr( 'max', 10 );
		this.progress.val( 0 );

		this.next_btn = $( this.el ).find( '.forward-btn' );
		this.back_btn = $( this.el ).find( '.back-btn' );
		this.done_btn = $( this.el ).find( '.done-btn' );

		title.text( this.title );
		title_1.text( this.title );

	},

	setButtonsStatus: function() {

		if ( this.current_step === 1 ) {
			Global.setWidgetEnabled( this.back_btn, false );
		} else {
			Global.setWidgetEnabled( this.back_btn, true );
		}

		if ( this.current_step !== this.steps ) {
			Global.setWidgetEnabled( this.done_btn, false );
			Global.setWidgetEnabled( this.next_btn, true );
		} else {
			Global.setWidgetEnabled( this.done_btn, true );
			Global.setWidgetEnabled( this.next_btn, false );
		}
	},

	onNextClick: function() {
		this.saveCurrentStep();
		this.current_step = this.current_step + 1;
		this.initCurrentStep();
	},

	onBackClick: function() {
		this.saveCurrentStep();
		this.current_step = this.current_step - 1;
		this.initCurrentStep();
	},

	onDoneClick: function() {

	},

	cleanStepsData: function() {
		this.stepsDataDic = {};
		this.current_step = 1;
	},

	onCloseClick: function() {
		if ( this.script_name ) {
			this.saveCurrentStep();

			this.saveAllStepsToUserGenericData( function() {

			} );
		}
		LocalCacheData.current_open_wizard_controller = null;
		$( this.el ).remove();

	},

	saveCurrentStep: function() {

	},

	saveAllStepsToUserGenericData: function( callBack ) {

		if ( this.script_name ) {
			this.saved_user_generic_data.data = this.stepsDataDic;
			this.saved_user_generic_data.data.current_step = this.current_step;

			this.user_generic_data_api.setUserGenericData( this.saved_user_generic_data, {onResult: function( result ) {
				callBack( result.getResult() );
			}} );
		} else {
			callBack( true );
		}

	},

	initUserGenericData: function() {
		var $this = this;
		var args = {};

		if ( this.script_name ) {
			args.filter_data = {script: this.script_name, deleted: false};
			this.user_generic_data_api.getUserGenericData( args, {onResult: function( result ) {

				var result_data = result.getResult();

				if ( $.type( result_data ) === 'array' ) {
					$this.saved_user_generic_data = result_data[0];
					$this.stepsDataDic = $this.saved_user_generic_data.data;
				} else {
					$this.saved_user_generic_data = {};
					$this.saved_user_generic_data.script = $this.script_name;
					$this.saved_user_generic_data.name = $this.script_name;
					$this.saved_user_generic_data.is_default = false;
					$this.saved_user_generic_data.data = {current_step: 1};

				}

				$this.current_step = $this.saved_user_generic_data.data.current_step;

				if ( $this.current_step > $this.steps ) {
					$this.current_step = 1;
				}

				$this.initCurrentStep();

			}} );
		} else {
			$this.initCurrentStep();
		}

	},

	initCurrentStep: function() {

		var $this = this;
		$this.progress_label.text( 'Step ' + $this.current_step + ' of ' + $this.steps );
		$this.progress.attr( 'max', $this.steps );
		$this.progress.val( $this.current_step );

		$this.setButtonsStatus(); // set button enabled or disabled
		$this.buildCurrentStepUI();
		$this.buildCurrentStepData();
		$this.setCurrentStepValues();

	},

	buildCurrentStepUI: function() {

	},

	buildCurrentStepData: function() {

	},

	//Don't use this any more. Use BuildCurrentStepData to set values too
	setCurrentStepValues: function() {

	},

	getLabel: function() {
		var label = $( "<span class='wizard-label clear-both-div'></span>" );
		return label;
	},

	getCheckBox: function( field ) {
		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( {field: field} );

		return check_box;
	},

	getDatePicker: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		widget.TDatePicker( {field: field} );

		return widget;
	},

	getPasswordInput: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

		widget = widget.TPasswordInput( {
			field: field
		} );

		return widget;
	},

	getText: function() {
		var widget = Global.loadWidgetByName( FormItemType.TEXT );

		widget = widget.TText( {
		} );

		return widget;
	},

	getTextArea: function( field, width, height ) {

		if ( !width ) {
			width = 300;
		}

		if ( !height ) {
			height = 200;
		}
		var widget = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		widget = widget.TTextArea( {
			field: field,
			width: width,
			height: height
		} );

		return widget;
	},

	getComboBox: function( field, set_empty ) {
		var widget = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		widget = widget.TComboBox( {
			field: field,
			set_empty: set_empty
		} );

		return widget;
	},

	getImageCutArea: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.IMAGE_CUT );

		widget = widget.TImageCutArea( {
			field: field
		} );

		return widget;
	},

	getCameraBrowser: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.CAMERA_BROWSER );

		widget = widget.CameraBrowser( {
			field: field
		} );

		return widget;
	},

	getFileBrowser: function( field, accept_filter, width, height ) {
		var widget = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

		widget = widget.TImageBrowser( {
			field: field,
			accept_filter: accept_filter,
			default_width: width,
			default_height: height
		} );

		return widget;
	},

	getAComboBox: function( apiClass, allow_multiple, layoutName, field, set_all, key ) {
		var a_combobox = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		if ( !key ) {
			key = 'id';
		}

		a_combobox.AComboBox( {
			key: key,
			api_class: apiClass,
			allow_multiple_selection: allow_multiple,
			layout_name: layoutName,
			show_search_inputs: true,
			set_empty: true,
			set_all: set_all,
			field: field
		} );

		return a_combobox;

	},

	onGridSelectRow: function( e ) {

	},

	setGrid: function( gridId, grid_div, allMultipleSelection ) {

		if ( !allMultipleSelection ) {
			allMultipleSelection = false;
		}

		var $this = this;

		this.content_div.append( grid_div );

		var grid = grid_div.find( '#' + gridId );

		this.getGridColumns( gridId, function( result ) {

			grid = grid.jqGrid( {
				altRows: true,
				onSelectRow: function( e ) {
					$this.onGridSelectRow( e );
				},
				data: [],
				datatype: 'local',
				sortable: false,
				height: 75,
				rowNum: 10000,
				colNames: [],
				colModel: result,
				viewrecords: true,
				multiselect: allMultipleSelection,
				multiboxonly: allMultipleSelection

			} );

			$this.stepsWidgetDic[$this.current_step][gridId] = grid;

			$this.setGridSize( grid );

			$this.setGridGroupColumns( gridId );

		} );

	},

	setGridGroupColumns: function( gridId ) {

	},

	setGridSize: function( grid ) {
		grid.setGridWidth( $( this.content_div.find( '.grid-div' ) ).width() - 6 );
	},

	getGridColumns: function( gridId, callBack ) {

	},

	getRibbonButtonBox: function() {
		var div = $( '<div class="menu ribbon-button-bar"></div>' );
		var ul = $( '<ul></ul>' );

		div.append( ul );

		return div;
	},

	getRibbonButton: function( id, icon, label ) {
		var button = $( '<li><div class="ribbon-sub-menu-icon" id="' + id + '"><img src="' + icon + '" >' + label + '</div></li>' );

		return button;
	}

} );

BaseWizardController.default_data = null;
BaseWizardController.callBack = null;

BaseWizardController.openWizard = function( viewId, templateName ) {

	if ( LocalCacheData.current_open_wizard_controller ) {
		LocalCacheData.current_open_wizard_controller.onCloseClick();
	}

	Global.loadViewSource( viewId, templateName, function( result ) {
		var args = { };
		var template = _.template( result, args );

		$( 'body' ).append( template );

	} );
};