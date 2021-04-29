import { TTBackboneView } from '@/views/TTBackboneView';

export class BaseWizardController extends TTBackboneView {
	constructor( options = {} ) {
		_.defaults( options, {
			// _required_files: null,
			steps: 0,
			title: 'Wizard',
			current_step: 1,

			content_div: null,

			next_btn: null,
			back_btn: null,
			done_btn: null,
			cancel_btn: null,
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

			edit_view_ui_dic: {},

			edit_view_form_item_dic: {},

			events: {
				'click .close-btn': 'onCloseClick',
				'click .close-icon': 'onCloseClick',
				'click .wizard-overlay.onclick-close': 'onCloseClick',
				'click .forward-btn': 'onNextClick',
				'click .back-btn': 'onBackClick',
				'click .done-btn': 'onDoneClick'
			}

		} );

		super( options );
	}

	/**
	 * When changing this function, you need to look for all occurences of this function because it was needed in several bases
	 * BaseViewController, HomeViewController, BaseWizardController, QuickPunchBaseViewControler
	 *
	 * @returns {Array}
	 */
	// filterRequiredFiles() {
	// 	var retval = [];
	//
	// 	if ( this._required_files && this._required_files[0] ) {
	// 		retval = this._required_files;
	// 	} else {
	// 		for ( var edition_id in this._required_files ) {
	// 			if ( Global.getProductEdition() >= edition_id ) {
	// 				retval = retval.concat( this._required_files[edition_id] );
	// 			}
	// 		}
	// 	}
	//
	// 	Debug.Arr( retval, 'RETVAL', 'BaseWizardController.js', 'BaseWizardController', 'filterRequiredFiles', 10 );
	// 	return retval;
	// }

	initialize( options ) {
		super.initialize( options );

		this.content_div = $( this.el ).find( '.content' );
		this.stepsWidgetDic = {};
		this.stepsDataDic = {};

		this.default_data = BaseWizardController.default_data;
		this.call_back = BaseWizardController.call_back;

		BaseWizardController.default_data = null;
		BaseWizardController.call_back = null;

		this.user_generic_data_api = TTAPI.APIUserGenericData;

		LocalCacheData.current_open_wizard_controller = this;

		if ( typeof this.init == 'function' ) {
			//FIXME: pull this out when all wizards are refactored to the new way #1187
			if ( typeof this.setDefaultDataToSteps == 'function' ) {
				this.setDefaultDataToSteps();
			}
			this.init( options );
			TTPromise.resolve( 'BaseViewController', 'initialize' );
		}
	}

	setDefaultDataToSteps() {
	}

	getDefaultData( key ) {

		if ( !this.default_data ) {
			return null;
		}

		return this.default_data[key];
	}

	render() {
		var title = $( this.el ).find( '.title' );
		var title_1 = $( this.el ).find( '.title-1' );
		this.progress = $( this.el ).find( '.progress' );
		this.progress_label = $( this.el ).find( '.steps' );

		this.progress.attr( 'max', 10 );
		this.progress.val( 0 );

		this.next_btn = $( this.el ).find( '.forward-btn' );
		this.back_btn = $( this.el ).find( '.back-btn' );
		this.done_btn = $( this.el ).find( '.done-btn' );
		this.close_btn = $( this.el ).find( '.close-btn' );

		Global.setWidgetEnabled( this.back_btn, false );
		Global.setWidgetEnabled( this.next_btn, false );
		Global.setWidgetEnabled( this.close_btn, false );
		Global.setWidgetEnabled( this.done_btn, false );

		title.text( this.title );
		title_1.text( this.title );
		TTPromise.resolve( 'init', 'init' );
	}

	setButtonsStatus() {

		Global.setWidgetEnabled( this.done_btn, false );
		Global.setWidgetEnabled( this.close_btn, true );

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
	}

	onNextClick() {
		this.saveCurrentStep();
		this.current_step = this.current_step + 1;
		this.initCurrentStep();
	}

	onBackClick() {
		this.saveCurrentStep();
		this.current_step = this.current_step - 1;
		this.initCurrentStep();
	}

	onDoneClick() {
	}

	cleanStepsData() {
		this.stepsDataDic = {};
		this.current_step = 1;
	}

	onCloseClick() {
		if ( this.script_name ) {
			this.saveCurrentStep();

			this.saveAllStepsToUserGenericData( function() {

			} );
		}
		LocalCacheData.current_open_wizard_controller = null;
		$( this.el ).remove();
	}

	saveCurrentStep( direction, callBack ) {
	}

	saveAllStepsToUserGenericData( callBack ) {

		// Function called stacks: TypeError: Unable to set property 'data' of undefined or null reference
		if ( this.script_name && this.saved_user_generic_data ) {
			this.saved_user_generic_data.data = this.stepsDataDic;
			this.saved_user_generic_data.data.current_step = this.current_step;

			this.user_generic_data_api.setUserGenericData( this.saved_user_generic_data, {
				onResult: function( result ) {
					callBack( result.getResult() );
				}
			} );
		} else {
			callBack( true );
		}
	}

	addEditFieldToColumn( label, widgets, column, firstOrLastRecord, widgetContainer, saveFormItemDiv, setResizeEvent, saveFormItemDivKey, hasKeyEvent, customLabelWidget ) {
		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		var widget = widgets;

		if ( Global.isArray( widgets ) ) {
			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
//				widget.css( 'opacity', 0 );
			}
		} else {
//			widget.css( 'opacity', 0 );
		}

		if ( customLabelWidget ) {
			form_item_label.parent().append( customLabelWidget );
			form_item_label.remove();
		} else {
//			form_item_label.text( label + ': ' );
			form_item_label.html( label + ': ' );
		}

		if ( Global.isSet( widgetContainer ) ) {

			form_item_input_div.append( widgetContainer );

		} else {
			form_item_input_div.append( widget );
		}

		column.append( form_item );
		//column.append( "<div class='clear-both-div'></div>" );

		//set height to text area
//		if ( form_item.height() > 35 ) {
//			form_item_label_div.css( 'height', form_item.height() );
//		} else if ( widget.hasClass( 'a-dropdown' ) ) {
//			form_item_label_div.css( 'height', 240 );
//		}

		if ( setResizeEvent ) {

//			form_item.unbind( 'resize' ).bind( 'resize', function() {
//				if ( form_item_label_div.height() !== form_item.height() && form_item.height() !== 0 ) {
//					form_item_label_div.css( 'height', form_item.height() );
//				}
//
//			} );
//			widget.unbind( 'setSize' ).bind( 'setSize', function() {
//				form_item_label_div.css( 'height', widget.height() + 10 );
//			} );

//			form_item_input_div.unbind( 'resize' ).bind( 'resize', function() {
//				form_item_label_div.css( 'height', form_item_input_div.height() + 10 );
//			} );

		}

		if ( !label ) {
			form_item_input_div.remove();
			form_item_label_div.remove();

			form_item.append( widget );
//			widget.css( 'opacity', 1 );

			if ( saveFormItemDiv && saveFormItemDivKey ) {
				this.edit_view_form_item_dic[saveFormItemDivKey] = form_item;
			}

			return;
		}

		if ( saveFormItemDiv ) {

			if ( Global.isArray( widgets ) ) {
				this.edit_view_form_item_dic[widgets[0].getField()] = form_item;
			} else {
				this.edit_view_form_item_dic[widget.getField()] = form_item;
			}

		}
		if ( Global.isArray( widgets ) ) {

			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				this.stepsWidgetDic[this.current_step][widget.getField()] = widget;

				widget.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );

				if ( hasKeyEvent ) {
					widget.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
						$this.onFormItemKeyUp( target );
					} );

					widget.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
						$this.onFormItemKeyDown( target );
					} );
				}
			}
		} else {
			this.stepsWidgetDic[this.current_step][widget.getField()] = widget;

			widget.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );

			if ( hasKeyEvent ) {
				widget.bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				widget.bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
			}
		}

		return form_item;
	}

	initUserGenericData() {
		var $this = this;
		var args = {};

		if ( this.script_name ) {
			args.filter_data = { script: this.script_name, deleted: false };
			this.user_generic_data_api.getUserGenericData( args, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( $.type( result_data ) === 'array' ) {
						$this.saved_user_generic_data = result_data[0];
						$this.stepsDataDic = $this.saved_user_generic_data.data;
					} else {
						$this.saved_user_generic_data = {};
						$this.saved_user_generic_data.script = $this.script_name;
						$this.saved_user_generic_data.name = $this.script_name;
						$this.saved_user_generic_data.is_default = false;
						$this.saved_user_generic_data.data = { current_step: 1 };

					}

					$this.current_step = $this.saved_user_generic_data.data.current_step;

					if ( $this.current_step > $this.steps || $this.current_step < 1 ) { //Make sure current_step isn't outside the range of the wizard.
						$this.current_step = 1;
					}

					$this.initCurrentStep();

				}
			} );
		} else {
			$this.initCurrentStep();
		}
	}

	initCurrentStep() {

		var $this = this;
		$this.progress_label.text( 'Step ' + $this.current_step + ' of ' + $this.steps );
		$this.progress.attr( 'max', $this.steps );
		$this.progress.val( $this.current_step );

		$this.buildCurrentStepUI();
		$this.buildCurrentStepData();
		$this.setCurrentStepValues();
		$this.setButtonsStatus(); // set button enabled or disabled
	}

	buildCurrentStepUI() {
	}

	buildCurrentStepData() {
	}

	//Don't use this any more. Use BuildCurrentStepData to set values too
	setCurrentStepValues() {
	}

	getLabel() {
		var label = $( '<span class=\'wizard-label clear-both-div\'></span>' );
		return label;
	}

	getCheckBox( field ) {
		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( { field: field } );

		return check_box;
	}

	getDatePicker( field ) {
		var widget = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		widget.TDatePicker( { field: field } );

		return widget;
	}

	getPasswordInput( field ) {
		var widget = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

		widget = widget.TPasswordInput( {
			field: field
		} );

		return widget;
	}

	getTextInput( field, width ) {
		var widget = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		if ( width ) {
			widget = widget.TPasswordInput( {
				field: field,
				width: width
			} );
		} else {
			widget = widget.TPasswordInput( {
				field: field
			} );
		}
		return widget;
	}

	getText() {
		var widget = Global.loadWidgetByName( FormItemType.TEXT );

		widget = widget.TText( {} );

		return widget;
	}

	getTextArea( field, width, height ) {

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
	}

	getComboBox( field, set_empty ) {
		var widget = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		widget = widget.TComboBox( {
			field: field,
			set_empty: set_empty
		} );

		return widget;
	}

	getImageCutArea( field ) {
		var widget = Global.loadWidgetByName( FormItemType.IMAGE_CUT );

		widget = widget.TImageCutArea( {
			field: field
		} );

		return widget;
	}

	getCameraBrowser( field ) {
		var widget = Global.loadWidgetByName( FormItemType.CAMERA_BROWSER );

		widget = widget.CameraBrowser( {
			field: field
		} );

		return widget;
	}

	getFileBrowser( field, accept_filter, width, height ) {
		var widget = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

		widget = widget.TImageBrowser( {
			field: field,
			accept_filter: accept_filter,
			default_width: width,
			default_height: height
		} );

		return widget;
	}

	getAComboBox( apiClass, allow_multiple, layoutName, field, set_all, key ) {
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
	}

	getSimpleTComboBox( field, allowMultiple ) {

		if ( !Global.isSet( allowMultiple ) ) {
			allowMultiple = true;
		}

		var widget = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		widget = widget.AComboBox( {
			field: field,
			set_empty: true,
			allow_multiple_selection: allowMultiple,
			layout_name: 'global_option_column',
			key: 'value'
		} );

		return widget;
	}

	onGridSelectRow( e ) {
	}

	onGridDblClickRow( e ) {
	}

	setGrid( gridId, grid_div, allMultipleSelection ) {

		if ( !allMultipleSelection ) {
			allMultipleSelection = false;
		}

		var $this = this;

		this.content_div.append( grid_div );

		var grid = grid_div.find( '#' + gridId );

		this.getGridColumns( gridId, function( column_model ) {

			$this.stepsWidgetDic[$this.current_step][gridId] = new TTGrid( gridId, {
				container_selector: '.wizard',
				altRows: true,
				onSelectRow: function( e ) {
					$this.onGridSelectRow( e );
				},
				onSelectAll: function( e ) {
					for ( var n in e ) {
						$this.onGridSelectRow( e[n] );
					}
				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				sortable: false,
				height: 75,
				multiselect: allMultipleSelection,
				multiboxonly: allMultipleSelection

			}, column_model );

			$this.setGridSize( $this.stepsWidgetDic[$this.current_step][gridId] );

			$this.setGridGroupColumns( gridId );

		} );
		return grid; //allowing chaining off this method.
	}

	setGridGroupColumns( gridId ) {
	}

	setGridSize( grid ) {
		grid.grid.setGridWidth( $( this.content_div.find( '.grid-div' ) ).width() - 11 );
		grid.grid.setGridHeight( this.content_div.height() - 150 ); //During merge, this wasn't in MASTER branch.
	}

	getGridColumns( gridId, callBack ) {
	}

	getRibbonButtonBox() {
		var div = $( '<div class="menu ribbon-button-bar"></div>' );
		var ul = $( '<ul></ul>' );

		div.append( ul );

		return div;
	}

	getRibbonButton( id, icon, label ) {
		var button = $( '<li><div class="ribbon-sub-menu-icon" id="' + id + '"><img src="' + icon + '" >' + label + '</div></li>' );

		return button;
	}

	showNoResultCover( grid_div ) {
		if ( grid_div && grid_div instanceof jQuery ) {
			this.removeNoResultCover( grid_div );
			var no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
			no_result_box.NoResultBox( { related_view_controller: this, is_new: false } );
			no_result_box.attr( 'class', 'no-result-div' );

			grid_div.append( no_result_box );
		}
	}

	removeNoResultCover( grid_div ) {
		if ( grid_div && grid_div instanceof jQuery ) {
			grid_div.find( '.no-result-div' ).remove();
		}
	}

}

BaseWizardController.default_data = null;
BaseWizardController.callBack = null;

BaseWizardController.openWizard = function( viewId, templateName ) {
	if ( viewId != 'ReportViewWizard' && LocalCacheData.current_open_wizard_controller ) {
		LocalCacheData.current_open_wizard_controller.onCloseClick();
	}
	Global.loadViewSource( viewId, templateName, function( result ) {
		var args = {};
		var template = _.template( result );
		$( 'body' ).append( template( args ) );
		Global.setUIInitComplete();
	} );

};