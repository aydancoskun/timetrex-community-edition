class ImportCSVWizardController extends BaseWizardController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.wizard-bg',

			api_import: null,

			parse_hint_source: null,

			field_source: null,

			select_grid_last_row: null,

			last_id: 0, // Last grid id

			saved_layout_array: null,

			column_map_data: null, //Used to build grid data

			_required_files: ['TImageBrowser']
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );

		this.title = $.i18n._( 'Import Wizard' );
		this.steps = 6;
		this.current_step = 1;
		this.wizard_id = 'ProcessPayrollWizard';
		this.api_import = TTAPI.APIImport;

		this.render();
	}

	render() {
		super.render();

		this.initCurrentStep();
	}

	//Create each page UI
	buildCurrentStepUI() {

		var $this = this;
		this.content_div.empty();
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Select the type of objects that you wish to import' ) + ':' );

				var combo_box = this.getComboBox( 'import_class' );

				var example_label = this.getLabel();
				example_label.text( $.i18n._( 'Download example CSV file' ) );
				example_label.css( 'text-decoration', 'underline' );
				example_label.css( 'cursor', 'pointer' );
				example_label.css( 'margin-top', '25px' );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][combo_box.getField()] = combo_box;

				example_label.unbind( 'click' ).bind( 'click', function() {

					var current_step_ui = $this.stepsWidgetDic[$this.current_step];
					var current_value = current_step_ui.import_class.getValue().toLowerCase();
					var url = ServiceCaller.import_csv_emample + 'import_' + current_value + '_example.csv';
					window.open( url, '_blank' );
				} );

				this.stepsWidgetDic[this.current_step]['example_label'] = example_label;

				combo_box.unbind( 'change' ).bind( 'change', function( e ) {
					example_label.text( $.i18n._( 'Download example' ) + ' ' + combo_box.getLabel() + ' ' + $.i18n._( 'CSV file' ) );
				} );

				this.content_div.append( label );
				this.content_div.append( combo_box );
				this.content_div.append( example_label );

				break;
			case 2:
				label = this.getLabel();
				label.text( $.i18n._( 'Upload Comma Separated Value (CSV) text file' ) );

				var file_browser = this.getFileBrowser( 'file_uploader', '.csv' );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][file_browser.getField()] = file_browser;

				this.content_div.append( label );
				this.content_div.append( file_browser );

				file_browser.unbind( 'imageChange' ).bind( 'imageChange', function() {
					if ( file_browser.getValue() ) {
						Global.setWidgetEnabled( $this.back_btn, true );
						Global.setWidgetEnabled( $this.next_btn, true );
					}
				} );

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Map columns from the uploaded file' ) + ':' );

				this.stepsWidgetDic[this.current_step] = {};

				//Saved layout

				var saved_layout_div = $( '<div></div>' );

				var form_item_label = $( '<span></span>' );

				saved_layout_div.append( form_item_label );

				form_item_label.text( $.i18n._( 'Save Mapping As' ) + ':' );

				var save_mapping_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
				save_mapping_input = $( save_mapping_input ).TTextInput();

				var save_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Save' ) + '\'></input>' );

				saved_layout_div.append( save_mapping_input );
				saved_layout_div.append( save_btn );

				form_item_label = $( '<span style=\'margin-left: 5px\' >' + $.i18n._( 'Saved Mapping' ) + ':</span>' );

				var previous_saved_layout_selector = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				previous_saved_layout_selector = previous_saved_layout_selector.TComboBox();
				previous_saved_layout_selector.setValueKey( 'id' );
				previous_saved_layout_selector.setLabelKey( 'name' );

				var update_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Update' ) + '\'></input>' );
				var del_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Delete' ) + '\'></input>' );

				save_btn.unbind( 'click' ).bind( 'click', function() {
					var name = save_mapping_input.getValue();
					if ( !name ) {
						TAlertManager.showAlert( $.i18n._( 'Mapping Name is blank' ) );
						return;
					}
					$this.saveNewMapping( save_mapping_input.getValue() );
				} );

				update_btn.unbind( 'click' ).bind( 'click', function() {
					$this.updateSelectMapping( previous_saved_layout_selector.getValue() );
				} );

				del_btn.unbind( 'click' ).bind( 'click', function() {
					$this.deleteSelectMapping( previous_saved_layout_selector.getValue() );
				} );

				saved_layout_div.append( form_item_label );
				saved_layout_div.append( previous_saved_layout_selector );
				saved_layout_div.append( update_btn );
				saved_layout_div.append( del_btn );

				this.stepsWidgetDic[this.current_step]['saved_mapping'] = previous_saved_layout_selector;

				previous_saved_layout_selector.bind( 'formItemChange', function( e, target ) {
					$this.onSavedLayoutChange( target.getValue() );
				} );

				//add minus buttons
				var action_button_div = $( '<div style="margin-left: 15px;text-align: left; margin-bottom: 5px;"></div>' );
				var add_icon = $( '<button class="plus-icon" style="margin-right: 5px;"></button>' );
				var minus_icon = $( '<button class="minus-icon"></button>' );

				action_button_div.append( add_icon );
				action_button_div.append( minus_icon );

				this.content_div.append( label );
				this.content_div.append( saved_layout_div );
				this.content_div.append( action_button_div );

				var grid_id = 'import_data';
				var grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setImportGrid( grid_id, grid_div );

				add_icon.bind( 'click', function() {
					$this.addRow();
				} );

				minus_icon.bind( 'click', function() {
					$this.minusRow();
				} );

				break;
			case 4:
				label = this.getLabel();
				label.text( $.i18n._( 'Select import settings' ) );

				this.content_div.append( label );

				this.stepsWidgetDic[this.current_step] = {};

				break;
			case 5:

		}
	}

	onSavedLayoutChange( value ) {
		var grid = this.stepsWidgetDic[this.current_step].import_data;

		var len = this.saved_layout_array.length;

		var id = 1;

		var select_data = {};

		for ( var i = 0; i < len; i++ ) {
			var layout = this.saved_layout_array[i];

			if ( layout.id === value ) {
				select_data = layout.data;
				break;
			}
		}

		for ( var i = 0; i < select_data.length; i++ ) {
			var item = select_data[i];
			item.id = 'csv' + id;
			item.field = item.field ? item.field : TTUUID.zero_id; //Make sure any blank fields are converted to zero_ids to prevent "undefined" from appearing in place of the dropdown box.
			id = id + 1;
		}

		this.last_id = id;

		//Clone this array because its currently a reference to the raw grid data itself and grid.setData() clears out the grid before rendering it again, which results in a blank grid when switching from a saved mapping, then back to the original.
		select_data = this.setSampleRowBaseOnImportFile( select_data ).slice( 0 );

		grid.setData( select_data );

		this.bindGridRenderEvents( grid );
	}

	setSampleRowBaseOnImportFile( grid_data ) {
		if ( !this.import_data || !grid_data ) {
			return;
		}

		for ( var i = 0; i < grid_data.length; i++ ) {
			var item = grid_data[i];
			item.row_1 = '';
			for ( var j = 0; j < this.import_data.length; j++ ) {
				var import_data = this.import_data[j];

				//#2132 - match based on map_column_name
				if ( item && item.map_column_name && import_data && import_data.map_column_name ) {
					if ( item.map_column_name.trim() === import_data.map_column_name.trim() ) {
						item.row_1 = import_data.row_1;
						break;
					}
				}
			}
		}

		return grid_data;
	}

	getSavedMapping( select_layout_id ) {
		var $this = this;
		var args = {};
		var filter_data = {};
		filter_data.script = 'import_wizard' + this.stepsDataDic[1].import_class;
		filter_data.deleted = false;
		args.filter_data = filter_data;
		TTAPI.APIUserGenericData.getUserGenericData( args, {
			onResult: function( result ) {
				var res_data = result.getResult();
				if ( $.type( res_data ) !== 'array' ) {
					$this.saveNewMapping( BaseViewController.default_layout_name, true );
				} else {
					//Force sorting by name so -- DEFAULT -- record appears at the top.
					res_data.sort( function( a, b ) {
							return Global.compare( a, b, 'name' );
						}
					);

					$this.saved_layout_array = res_data;

					//If not set select layout, default to first one and update it to current upload columns
					if ( !select_layout_id ) {
						select_layout_id = res_data[0].id;

						//Only update the first saved import data record if it is infact the DEFAULT one, otherwise it could overwrite some other random saved mapping, especially if they happen to the delete the default one.
						//  It has to update the DEFAULT saved mapping, otherwise if the user changes to a different mapping, then changes back, all the settings will be lost.
						if ( res_data[0].is_default == true || res_data[0].name == BaseViewController.default_layout_name ) {
							$this.updateSelectMapping( select_layout_id );
						} else {
							$this.saveNewMapping( BaseViewController.default_layout_name, true );
						}
					}

					$this.setSavedMappingOptions( res_data, select_layout_id );

				}

			}
		} );
	}

	getLayoutById( select_id ) {
		var len = this.saved_layout_array.length;

		for ( var i = 0; i < len; i++ ) {
			var layout = this.saved_layout_array[i];

			if ( layout.id === select_id ) {
				return layout;
			}
		}
	}

	deleteSelectMapping( select_id ) {
		var $this = this;
		var select_layout = this.getLayoutById( select_id );

		if ( select_layout.is_default == true || select_layout.name === BaseViewController.default_layout_name ) {
			TAlertManager.showAlert( $.i18n._( 'Can\'t delete default layout' ) );
			return;
		}

		TAlertManager.showConfirmAlert( $.i18n._( 'Are you sure you wish to continue?' ), null, function( flag ) {
			if ( flag ) {
				TTAPI.APIUserGenericData.deleteUserGenericData( select_id, {
					onResult: function( result ) {
						$this.onSavedLayoutChange( $this.saved_layout_array[0].id );
						$this.getSavedMapping();
					}
				} );
			}
		} );
	}

	updateSelectMapping( select_id ) {
		var $this = this;

		this.saveCurrentStep();
		var select_layout = this.getLayoutById( select_id );

		select_layout.data = this.stepsDataDic[this.current_step].import_data_for_layout;

		TTAPI.APIUserGenericData.setUserGenericData( select_layout, {
			onResult: function( result ) {
				//Refresh saved mapping data once it has been saved on the server.
				// This makes it so if the user clicks the UPDATE button, then switches to another saved mapping, then switches back, they will see their most recent settings.
				$this.getSavedMapping( select_id );
			}
		} );
	}

	saveNewMapping( name, is_default ) {
		this.saveCurrentStep();

		var $this = this;
		var args = {};
		args.script = 'import_wizard' + this.stepsDataDic[1].import_class;
		args.name = name;
		args.is_default = ( is_default && is_default == true ? true : false );
		args.data = this.stepsDataDic[this.current_step].import_data_for_layout;

		TTAPI.APIUserGenericData.setUserGenericData( args, {
			onResult: function( result ) {
				if ( !result.isValid() ) {
					TAlertManager.showErrorAlert( result );
				} else {
					$this.getSavedMapping( result.getResult() );
				}

			}
		} );
	}

	setSavedMappingOptions( array, select_layout_id ) {
		var $this = this;

		if ( Global.isSet( $this.stepsWidgetDic[$this.current_step]['saved_mapping'] ) == true ) {
			var selector = $this.stepsWidgetDic[$this.current_step]['saved_mapping'];

			selector.setSourceData( array );

			if ( select_layout_id ) {
				selector.setValue( select_layout_id );
			}
		}
		$this.saved_layout_array = array;
	}

	addRow() {
		var grid = this.stepsWidgetDic[this.current_step].import_data;
		var all_data = grid.getData();

		var data = {};
		data.id = 'csv' + this.last_id;
		data.field = TTUUID.zero_id;
		data.default_value = '';
		data.parse_hint = '';
		data.map_column_name = $.i18n._( 'New Field Column' );
		data.row_1 = '';

		this.last_id = this.last_id + 1;

		all_data.push( data );

		grid.setData( all_data, false );

		grid.grid.jqGrid( 'setSelection', data.id );

		this.bindGridRenderEvents( grid );
	}

	minusRow() {

		var grid = this.stepsWidgetDic[this.current_step].import_data;
		var sel_id = grid.getGridParam( 'selrow' );

		if ( !sel_id ) {
			return;
		}

		var all_data = grid.getGridParam( 'data' );

		for ( var i = all_data.length - 1; i >= 0; i-- ) {
			var data = all_data[i];

			if ( data.id === sel_id ) {
				all_data.splice( i, 1 );
			}
		}

		grid.setData( all_data, false );

		grid.grid.jqGrid( 'setSelection', all_data[all_data.length - 1].id );
	}

	getGridColumns( gridId, callBack ) {
		var column_info_array = [];
		var $this = this;

		switch ( gridId ) {
			case 'import_data':

				var column_info = {
					name: 'map_column_name',
					index: 'map_column_name',
					label: $.i18n._( 'File Column' ),
					width: 100,
					sortable: false,
					title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onTextInputRender( cell_value, related_data, row );
					}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'field',
					index: 'field',
					label: $.i18n._( 'Field' ),
					width: 100,
					sortable: false,
					title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onFieldRender( cell_value, related_data, row );
					}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'default_value',
					index: 'default_value',
					label: $.i18n._( 'Default Value' ),
					width: 100,
					sortable: false,
					title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onTextInputRender( cell_value, related_data, row );
					}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'parse_hint',
					index: 'parse_hint',
					label: $.i18n._( 'Parse Hint' ),
					width: 100,
					sortable: false,
					title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onParseHintRender( cell_value, related_data, row );
					}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'row_1',
					index: 'row_1',
					label: $.i18n._( 'Sample Row' ),
					width: 100,
					sortable: false,
					title: false
				};
				column_info_array.push( column_info );

				break;

		}

		callBack( column_info_array );
	}

	onParseHintRender( cell_value, related_data, row ) {
		var widget;
		var col_model = related_data.colModel;
		var row_id = related_data.rowId;

		if ( this.parse_hint_source[row.field] ) {
			widget = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			widget = widget.TComboBox( { set_empty: false } );

			widget.attr( 'custom_cell', 'true' );
			widget.attr( 'render_type', 'combobox' );
			widget.attr( 'id', row_id + '_' + col_model.name );
			widget.width( '97%' );

			var source = Global.buildRecordArray( this.parse_hint_source[row.field] );
			widget.setSourceData( source );

			if ( cell_value ) {
				widget.setValue( cell_value );
			} else {
				widget.setValue( source[0].value );
				row.parse_hint = source[0].value;
			}

		} else {
			widget = $( '<input custom_cell="true" ' +
				'render_type="text" ' +
				'id="' + row_id + '_' + col_model.name + '" ' +
				'type="text" ' +
				'class="t-text-input" ' +
				'style="width: 97%">' );

			widget.text( cell_value );
		}

		return widget.get( 0 ).outerHTML;
	}

	onFieldRender( cell_value, related_data, row ) {
		if ( !cell_value ) {
			return;
		}

		var col_model = related_data.colModel;
		var row_id = related_data.rowId;

		var acombobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		acombobox = acombobox.TComboBox( { set_empty: true } );

		acombobox.attr( 'custom_cell', 'true' );
		acombobox.attr( 'render_type', 'combobox' );
		acombobox.attr( 'id', row_id + '_' + col_model.name );
		acombobox.width( '97%' );

		acombobox.setSourceData( this.field_source );

		$( acombobox[0] ).find( '[value=' + cell_value + ']' ).attr( 'selected', true );

		return acombobox.get( 0 ).outerHTML;
	}

	onTextInputRender( cell_value, related_data, row ) {

		var col_model = related_data.colModel;
		var row_id = related_data.rowId;

		return '<input custom_cell="true" render_type="text" id="' + row_id + '_' + col_model.name + '" value="' + cell_value + '" type="text" class="t-text-input" style="width: 97%">';
	}

	buildCurrentStepData() {

		var grid;
		var $this = this;

		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 1:
				this.api_import.getImportObjects( {
					onResult: function( result ) {
						var combo_box = current_step_ui['import_class'];
						var array = Global.buildRecordArray( result.getResult() );
						combo_box.setSourceData( array );

						if ( current_step_data ) {
							combo_box.setValue( current_step_data.import_class );
						} else if ( $this.default_data ) {
							combo_box.setValue( $this.default_data );

						}

						var example_label = current_step_ui.example_label;
						example_label.text( $.i18n._( 'Download example' ) + ' ' + combo_box.getLabel() + ' ' + $.i18n._( 'CSV file' ) );
						$this.setButtonsStatus(); // set button enabled or disabled
					}
				} );
				break;
			case 3:

				grid = current_step_ui.import_data;
				if ( current_step_data && current_step_data.import_data_for_layout ) {

					current_step_data.import_data_for_layout = this.setSampleRowBaseOnImportFile( current_step_data.import_data_for_layout );

					grid.setData( current_step_data.import_data_for_layout );

					$this.bindGridRenderEvents( grid );

					$this.setSavedMappingOptions( $this.saved_layout_array, current_step_data.saved_mapping );

					$this.setButtonsStatus(); // set button enabled or disabled
					return;
				}

				this.api_import.getOptions( 'parse_hint', {
					onResult: function( result ) {
						$this.parse_hint_source = result.getResult();

					}
				} );

				this.api_import.getOptions( 'columns', {
					onResult: function( result ) {
						$this.field_source = Global.buildRecordArray( result.getResult() );
						$this.api_import.getRawData( 1, {
							onResult: function( getRawDataRes ) {

								var raw_data = getRawDataRes.getResult();
								raw_data = $this.buildMappingGridDataArray( raw_data[0] );

								$this.api_import.generateColumnMap( {
									onResult: function( generateColumnMapRes ) {
										$this.column_map_data = generateColumnMapRes.getResult();

										var len = raw_data.length;
										for ( var key in $this.column_map_data ) {
											for ( var i = 0; i < len; i++ ) {
												var raw_data_item = raw_data[i];
												var col_map_data_item = $this.column_map_data[key];
												if ( raw_data_item.map_column_name == col_map_data_item.map_column_name ) {
													raw_data_item.field = key;
													raw_data_item.default_value = $this.column_map_data[key].default_value ? $this.column_map_data[key].default_value : '';
													raw_data_item.parse_hint = $this.column_map_data[key].parse_hint ? $this.column_map_data[key].parse_hint : '';
												}
											}
										}

										raw_data.sort( function( a, b ) {
												return Global.compare( a, b, 'map_column_name' );
											}
										);

										// use to set Sample row to same layout
										$this.import_data = raw_data;

										grid.setData( raw_data );

										$this.bindGridRenderEvents( grid );

										$this.getSavedMapping();
										$this.setButtonsStatus(); // set button enabled or disabled

									}
								} );
							}
						} );
					}
				} );

				break;
			case 4:
				this.api_import.getOptions( 'import_options', {
					onResult: function( result ) {
						var result_data = Global.buildRecordArray( result.getResult() );
						var div = $( '<div style="text-align: left;margin-left: 15px;"></div>' );

						for ( var i = 0; i < result_data.length; i++ ) {
							var item = result_data[i];
							var check_box = $this.getCheckBox( item.value );

							if ( current_step_data && current_step_data[item.value] ) {
								check_box.setValue( current_step_data[item.value] );
							}

							var label = $( '<label class="wizard-checkbox-label" style="display: block;">' + item.label + '</label>' );
							label.prepend( check_box );
							$this.stepsWidgetDic[$this.current_step][item.value] = check_box;

							div.append( label );
						}

						$this.content_div.append( div );
						$this.setButtonsStatus(); // set button enabled or disabled

					}
				} );
				break;
			case 5:
				var import_data = this.stepsDataDic[3].import_data;
				var import_options = this.stepsDataDic[4];

				this.api_import.import( import_data, import_options, true, {
					onResult: function( result ) {

						if ( $this.current_step != 5 ) {
							return;
						}
						if ( result.isValid() ) {
							var label = $this.getLabel();
							label.text( $.i18n._( 'Data verification successful' ) );

							$this.content_div.append( label );

						} else {
							var data_grid_error_source = $this.createErrorSource( result.getDetails() );
							$this.showErrorGrid( $.i18n._( 'Verification failed due to the following reasons' ) + ': ',
								data_grid_error_source,
								$.i18n._( 'Continue to the next step to skip importing invalid records.' ),
								result.getRecordDetails() );

						}

						$this.setButtonsStatus(); // set button enabled or disabled

					}
				} );
				break;
			case 6:
				import_data = this.stepsDataDic[3].import_data;
				import_options = this.stepsDataDic[4];

				this.api_import.import( import_data, import_options, false, {
					onResult: function( result ) {

						if ( $this.current_step != 6 ) {
							return;
						}
						if ( result.isValid() ) {
							var label = $this.getLabel();
							label.text( $.i18n._( 'Import successful' ) );

							$this.content_div.append( label );

						} else {
							var data_grid_error_source = $this.createErrorSource( result.getDetails() );

							$this.showErrorGrid( $.i18n._( 'Import failed due to the following reasons' ) + ':',
								data_grid_error_source,
								$.i18n._( 'Invalid records have been skipped, all other records have been imported successfully.' ),
								result.getRecordDetails() );

						}

						$this.setButtonsStatus(); // set button enabled or disabled
					}
				} );
				break;
			default:
				$this.setButtonsStatus(); // set button enabled or disabled
				break;

		}
	}

	showErrorGrid( top_des, data_grid_error_source, bottom_des, records_details ) {
		var label = $( '<span class=\'top-des clear-both-div\'></span>' );
		label.text( top_des );

		this.content_div.append( label );

		var grid = $( '<table id="error_grid"></table>' );

		var columns = [];

		var column_info = {
			name: 'rowIndex',
			index: 'rowIndex',
			label: $.i18n._( 'Row' ),
			width: 60,
			sortable: false,
			title: false,
			fixed: true
		};
		columns.push( column_info );

		column_info = {
			name: 'row',
			index: 'row',
			label: $.i18n._( 'File Column' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column_info );

		column_info = {
			name: 'column',
			index: 'column',
			label: $.i18n._( 'Field' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column_info );

		column_info = {
			name: 'message',
			index: 'message',
			label: $.i18n._( 'Message' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column_info );

		this.content_div.append( grid );

		label = $( '<span class=\'total-des clear-both-div\'></span>' );
		label.text( $.i18n._( 'Records' ) + ':' + $.i18n._( 'Total' ) + ': ' + records_details.total + ' ' + $.i18n._( 'Valid' ) + ': ' + records_details.valid + ' ' + $.i18n._( 'Invalid' ) + ': ' + records_details.invalid );

		this.content_div.append( label );

		label = $( '<span class=\'bottom-des clear-both-div\'></span>' );
		label.text( bottom_des );

		this.content_div.append( label );

		grid = new TTGrid( 'error_grid', {
			sortable: false,
			width: ( this.content_div.width() - 2 )
		}, columns );
		grid.setData( data_grid_error_source );
		grid.setGridColumnsWidth( null, { max_grid_width: ( this.content_div.width() - 2 ) } );
	}

	createErrorSource( error_array ) {

		//Error: Uncaught TypeError: Cannot read property 'import_data' of undefined in /interface/html5/#!m=TimeSheet&date=00070609&user_id=14372 line 773
		if ( !this.stepsDataDic || !this.stepsDataDic[3] ) {
			return;
		}

		var import_data = this.stepsDataDic[3].import_data;
		var result = [];
		var error_row = {};

		for ( var key in error_array ) {
			var error_info = error_array[key]['error'];
			for ( var error_key in error_info ) {
				if ( !error_info.hasOwnProperty( error_key ) ) {
					continue;
				}

				error_row = {};
				// #2345 - we always want the row and column name to show in the error report.
				error_row.rowIndex = parseInt( key ) + 2;
				error_row.row = $.i18n._( 'Unknown' );
				error_row.column = error_key;
				error_row.message = error_info[error_key][0];

				// Try to get more specific error info.
				for ( var import_key in import_data ) {
					if ( import_key == error_key ) {  // #2345 - This won't match in cases where the csv columns do not match the object properties being validated. For example 'branch' != 'branch_id'
						error_row.row = import_data[import_key].map_column_name;
						error_row.column = import_data[import_key].field_name;
						break;
					}
				}

				result.push( error_row );
			}
		}

		return result;
	}

	bindGridRenderEvents( grid ) {
		var $this = this;
		var inputs = grid.grid.find( 'input[custom_cell="true"]' );
		var select = grid.grid.find( 'select[custom_cell="true"]' );

		inputs.unbind( 'change' ).bind( 'change', function( e ) {
			$this.onCellInputChange( e );
		} );

		select.unbind( 'change' ).bind( 'change', function( e ) {
			$this.onCellInputChange( e );
		} );

		inputs.unbind( 'focusin' ).bind( 'focusin', function( e ) {
			$this.onCellFocusIn( e );
		} );

		select.unbind( 'focusin' ).bind( 'focusin', function( e ) {
			$this.onCellFocusIn( e );
		} );
	}

	onCellFocusIn( e ) {
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid = current_step_ui['import_data'];
		var target = $( e.target );
		var target_id = target.attr( 'id' );
		var row_id = target_id.split( '_' )[0];

		grid.grid.jqGrid( 'setSelection', row_id );
	}

	onCellInputChange( e ) {
		var $this = this;
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid = current_step_ui['import_data'];
		var target = $( e.target );
		var target_id = target.attr( 'id' );
		var row_id = target_id.split( '_' )[0];
		var field = target_id.substring( target_id.indexOf( '_' ) + 1, target_id.length );
		var data = grid.getData();
		var target_val = target.val();

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var row_data = data[i];
			if ( row_data.id === row_id ) {
				row_data[field] = target_val;
				break;
			}
		}

		if ( field === 'field' ) {
			updateParseHintWidget();
		}

		function updateParseHintWidget() {
			var widget;
			var parse_hint_widget = target.parent().parent().find( '#' + row_id + '_parse_hint' );
			var render_type = parse_hint_widget.attr( 'render_type' );

			row_data['parse_hint'] = '';

			if ( $this.parse_hint_source[target_val] ) {

				widget = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				widget = widget.TComboBox( { set_empty: false } );

				widget.attr( 'custom_cell', 'true' );
				widget.attr( 'render_type', 'combobox' );
				widget.attr( 'id', row_id + '_parse_hint' );
				widget.width( '97%' );

				var source = Global.buildRecordArray( $this.parse_hint_source[target_val] );
				widget.setSourceData( Global.buildRecordArray( $this.parse_hint_source[target_val] ) );
				widget.setValue( source[0].value );
				row_data['parse_hint'] = source[0].value;

				parse_hint_widget.parent().append( widget );
				parse_hint_widget.remove();

			} else {

				widget = $( '<input custom_cell="true" ' +
					'render_type="text" ' +
					'id="' + row_id + '_parse_hint" ' +
					'value="" ' +
					'type="text" ' +
					'class="t-text-input" ' +
					'style="width: 97%">' );

				parse_hint_widget.parent().append( widget );
				parse_hint_widget.remove();

			}

			widget.bind( 'change', function( e ) {
				$this.onCellInputChange( e );
			} );

		}
	}

	setImportGrid( gridId, grid_div, allMultipleSelection ) {

		if ( !allMultipleSelection ) {
			allMultipleSelection = false;
		}

		var $this = this;

		this.content_div.append( grid_div );

		this.getGridColumns( gridId, function( column_model ) {

			$this.stepsWidgetDic[$this.current_step][gridId] = new TTGrid( gridId, {
				sortable: false,
				height: 300,
				multiselect: allMultipleSelection,
				multiboxonly: allMultipleSelection,
				editurl: 'clientArray'
			}, column_model );

			$this.setGridSize( $this.stepsWidgetDic[$this.current_step][gridId] );

			$this.setGridGroupColumns( gridId );

		} );
	}

	buildMappingGridDataArray( mappingData ) {

		var result = [];
		var id = 1;
		for ( var key in mappingData ) {
			if ( !mappingData.hasOwnProperty( key ) ) {
				continue;
			}

			var item = mappingData[key];
			var data = {};
			data.id = 'csv' + id;
			data.field = item.field ? item.field : TTUUID.zero_id;
			data.default_value = item.default_value ? item.default_value : '';
			data.parse_hint = item.parse_hint ? item.parse_hint : '';
			data.map_column_name = item.map_column_name ? item.map_column_name : key;
			data.row_1 = item.row_1 ? item.row_1 : item;
			result.push( data );

			id = id + 1;

		}

		this.last_id = id;

		return result;
	}

	onDoneClick() {
		this.cleanStepsData();
		LocalCacheData.current_open_wizard_controller = null;
		this.saveAllStepsToUserGenericData( function() {

		} );

		if ( this.call_back ) {
			this.call_back();
		}

		$( this.el ).remove();
	}

	initCurrentStep() {

		var $this = this;
		$this.progress_label.text( 'Step ' + $this.current_step + ' of ' + $this.steps );
		$this.progress.attr( 'max', $this.steps );
		$this.progress.val( $this.current_step );

		$this.buildCurrentStepUI();
		$this.buildCurrentStepData();
		$this.setCurrentStepValues();
	}

	onNextClick() {
		var $this = this;
		this.saveCurrentStep();
		var current_step_data = this.stepsDataDic[this.current_step];
		Global.setWidgetEnabled( this.back_btn, false );
		Global.setWidgetEnabled( this.next_btn, false );
		if ( this.current_step === 2 ) {

			if ( !current_step_data.file_uploader ) {
				TAlertManager.showAlert( $.i18n._( 'Please choose a CSV file first' ) );
				return;
			}

			$this.api_import.uploadFile( current_step_data.file_uploader, 'object_type=import&object_id=' + this.api_import.className, {
				onResult: function( upload_file_result ) {
					if ( upload_file_result.toLowerCase() !== 'true' ) {
						$this.setButtonsStatus(); // set button enabled or disabled
						return;
					}

					$this.current_step = $this.current_step + 1;

					$this.stepsDataDic[$this.current_step] = null;
					$this.initCurrentStep();

				}
			} );

		} else {

			this.current_step = this.current_step + 1;
			this.initCurrentStep();
		}
	}

	buildImportMapping( array ) {

		var result = {};
		var content;

		var len = array.length;

		for ( var i = 0; i < len; i++ ) {
			var item = array[i];
			if ( item.field ) {
				content = {};
				content.field = item.field;
				content.map_column_name = item.map_column_name;
				content.default_value = item.default_value;
				content.parse_hint = item.parse_hint;
				result[item.field] = content;
			}
		}

		return result;
	}

	/* jshint ignore:start */
	saveCurrentStep() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				current_step_data.import_class = current_step_ui.import_class.getValue();

				var formatted_import_class = current_step_data.import_class.charAt( 0 ).toUpperCase() + current_step_data.import_class.slice( 1 );

				this.api_import.className = 'APIImport' + formatted_import_class;
				this.api_import.key_name = 'Import' + formatted_import_class;

				break;
			case 2:
				current_step_data.file_uploader = current_step_ui.file_uploader.getValue();
				break;
			case 3:
				var grid = current_step_ui.import_data;
				current_step_data.import_data = this.buildImportMapping( grid.getData() );
				current_step_data.import_data_for_layout = grid.getData();
				current_step_data.saved_mapping = current_step_ui.saved_mapping.getValue();
				break;
			case 4:
				for ( var key in current_step_ui ) {

					if ( !current_step_ui.hasOwnProperty( key ) ) {
						continue;
					}
					current_step_data[key] = current_step_ui[key].getValue();
				}
				break;
		}
	}

	/* jshint ignore:end */
	setDefaultDataToSteps() {

		if ( !this.default_data ) {
			return null;
		}
	}

}
