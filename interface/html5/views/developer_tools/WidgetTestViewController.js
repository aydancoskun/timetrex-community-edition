import '@/global/widgets/filebrowser/TImage';
import '@/global/widgets/filebrowser/TImageAdvBrowser';
import '@/global/widgets/color-picker/TColorPicker';
import { SwitchButtonIcon } from '@/global/widgets/switch_button/SwitchButton';

export class WidgetTestViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#awesomebox_test_view_container',

			// _required_files: [
			// 	'TColorPicker',
			// 	'TImage',
			// 	'TImageAdvBrowser'
			// ],

			user_api: null,
			user_group_api: null,
			company_api: null,
			user_id_array: null
		} );

		super( options );
	}

	init( options ) {
		this.edit_view_tpl = 'WidgetTestEditView.html';
		this.permission_id = 'user';
		this.viewId = 'WidgetTest';
		this.script_name = 'WidgetTest';
		this.table_name_key = 'awesomebox_test';
		this.context_menu_name = $.i18n._( 'Widget Test' );
		this.navigation_label = $.i18n._( 'Widget Test' ) + ':';
		this.api = TTAPI.APIUser;
		this.select_company_id = LocalCacheData.getCurrentCompany().id;
		this.user_group_api = TTAPI.APIUserGroup;
		this.company_api = TTAPI.APICompany;
		this.user_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
	}

	setCurrentEditRecordData() {
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	clearEditViewData() {
		return false;
	}

	buildEditViewUI() {
		var $this = this;

		var tab_model = {
			'tab_employee': { 'label': $.i18n._( 'Employee' ) },
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_employee = this.edit_view_tab.find( '#tab_employee' );

		var tab_employee_column1 = tab_employee.find( '.first-column' );
		var tab_employee_column2 = tab_employee.find( '.second-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_employee_column1 );

		var form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Separated_box: if you see this, the test is passing.' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_employee_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'checkbox' } );
		this.addEditFieldToColumn( $.i18n._( 'TCheckbox' ), form_item_input, tab_employee_column1, '' );

		form_item_input = Global.loadWidgetByName( FormItemType.COLOR_PICKER );
		form_item_input.TColorPicker( { field: 'color_picker' } );
		this.addEditFieldToColumn( $.i18n._( 'TColorPicker' ), form_item_input, tab_employee_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'datepicker' } );
		this.addEditFieldToColumn( $.i18n._( 'TDatePicker' ), form_item_input, tab_employee_column1, '', null );

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TRangePicker( { field: 'daterange', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'TDateRange' ), form_item_input, tab_employee_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'timepicker' } );
		this.addEditFieldToColumn( $.i18n._( 'TTimePicker' ), form_item_input, tab_employee_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );
		this.file_browser = form_item_input.TImageAdvBrowser( {
			field: 'imagebrowser',
			default_width: 128,
			default_height: 128,
			enable_delete: true,
			callBack: function( form_data ) {
				new ServiceCaller().uploadFile( form_data, 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( ServiceCaller.getURLByObjectType( 'user_photo' ) + '&object_id=' + $this.current_edit_record.id );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			},
			deleteImageHandler: function( e ) {
				$this.onDeleteImage();
			}
		} );
		this.addEditFieldToColumn( $.i18n._( 'TImageAdvBrowser' ), this.file_browser, tab_employee_column1, '', null, false, true );

		Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js', function() {
			// Dynamic Field 11
			form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
			form_item_input.FormulaBuilder( {
				field: 'formula', width: '100%', onFormulaBtnClick: function() {

					var custom_column_api = TTAPI.APIReportCustomColumn;

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
			$this.detachElement( 'df_11' );
			$this.addEditFieldToColumn( 'FormulaBuilder', form_item_input, tab_employee_column1, '', null, true );
			form_item_input.parent().width( '45%' );
		} );

		//NO INSTANCES OF TList

		//Create action switch buttons
		var action_chooser_div = $( '.control-bar .action-chooser-div' );
		this.all_employee_btn = action_chooser_div.find( '#all_employee' );
		this.daily_totals_btn = action_chooser_div.find( '#daily_totals' );
		this.weekly_totals_btn = action_chooser_div.find( '#weekly_totals' );
		this.strict_range_btn = action_chooser_div.find( '#strict_range' );
		this.all_employee_btn = this.all_employee_btn.SwitchButton( {
			icon: SwitchButtonIcon.all_employee,
			tooltip: $.i18n._( 'Show Unscheduled Employees' )
		} );
		this.daily_totals_btn = this.daily_totals_btn.SwitchButton( {
			icon: SwitchButtonIcon.daily_total,
			tooltip: $.i18n._( 'Daily Totals' )
		} );
		this.weekly_totals_btn = this.weekly_totals_btn.SwitchButton( {
			icon: SwitchButtonIcon.weekly_total,
			tooltip: $.i18n._( 'Weekly Totals' )
		} );
		this.strict_range_btn = this.strict_range_btn.SwitchButton( {
			icon: SwitchButtonIcon.strict_range,
			tooltip: $.i18n._( 'Strict Range' )
		} );
		this.all_employee_btn.click( function() {
			// $this.onShowEmployeeClick();
		} );
		this.daily_totals_btn.click( function() {
			// $this.onDailyTotalsClick();
		} );
		this.weekly_totals_btn.click( function() {
			// $this.onWeeklyTotalClick();
		} );
		this.strict_range_btn.setValue( true );
		this.strict_range_btn.click( function() {
			// $this.onStrictRangeClick();
		} );
		this.toggle_button = $( this.el ).find( '.toggle-button-div' );
		var data_provider = [
			{ label: $.i18n._( 'Day' ), value: 'day' },
			{ label: $.i18n._( 'Week' ), value: 'week' },
			{ label: $.i18n._( 'Month' ), value: 'month' },
			{ label: $.i18n._( 'Year' ), value: 'year' }
		];

		this.toggle_button = this.toggle_button.TToggleButton( { data_provider: data_provider } );
		this.toggle_button.bind( 'change', function( e, result ) {
			$this.scroll_position = 0;
			$this.select_all_shifts_array = [];
			$this.select_shifts_array = [];
			$this.select_recurring_shifts_array = [];
		} );

		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );
		form_item_input.TTagInput( { field: 'tags', object_type_id: 930 } );
		this.addEditFieldToColumn( $.i18n._( 'TTagInput' ), form_item_input, tab_employee_column2, '', null, null, true );

		//requires width manipulation or intermittently won't show.
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'text', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'TText' ), form_item_input, tab_employee_column2 );
		form_item_input.parent().width( '45%' );

		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'password', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'TPasswordInput' ), form_item_input, tab_employee_column2 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'textarea' } );
		this.addEditFieldToColumn( $.i18n._( 'TTextArea' ), form_item_input, tab_employee_column2, '', null, null, true );
	}

	buildSearchFields() {
		super.buildSearchFields();
		var $this = this;
		this.search_fields = [];
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.edit,
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	}

	//override that forces same data to grid at all times.
	search() {
		var $this = this;
		this.api.getUser( {}, true, {
			onResult: function( r ) {
				var result_data = r.getResult();
				$this.user_id_array = result_data;
				result_data = $this.processResultData( result_data );
				$this.grid.setData( result_data );
				$this.grid.setGridColumnsWidth();
				$this.onEditClick( result_data[0].id );
			}
		} );
	}

	setEditViewDataDone() {
		var $this = this;
		setTimeout( function() {
			TAlertManager.showConfirmAlert( $.i18n._( 'Run tests?' ), null, function( result ) {
				if ( result == true ) {
					$this.runTests();
				}
			} );
			$this.setTabOVisibility( true );
			$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
			TTPromise.resolve( 'init', 'init' );
		}, 2500 );

		super.setEditViewDataDone();
	}

	runTests() {
		var $this = this;
		if ( $( '#qunit_script' ).length == 0 ) {
			$( '<script id=\'qunit_script\' src=\'framework/qunit/qunit.js\'></script>' ).appendTo( 'head' );
			$( '<link rel=\'stylesheet\' type=\'text/css\' href=\'framework/qunit/qunit.css\'>' ).appendTo( 'head' );
		}
		;

		QUnit.config.autostart = false;
		$( '#qunit_container' ).css( 'width', '100%' );
		$( '#qunit_container' ).css( 'height', 'auto' );
		$( '#qunit_container' ).css( 'overflow-y', 'scroll' );
		$( '#qunit_container' ).css( 'top', '0px' );
		$( '#qunit_container' ).css( 'left', '0px' );
		$( '#qunit_container' ).css( 'z-index', '100' );
		$( '#qunit_container' ).css( 'background', '#fff' );
		$( '#qunit_container' ).show();

		$( '#tt_debug_console' ).remove();
		if ( !window.qunit_initiated ) {
			window.qunit_initiated = true;
			QUnit.start();
		}

		//select an item by clicking a td in the last row
		QUnit.module( 'Widgets' );
		this.testCheckbox();
		this.testColorPicker();

		TTPromise.add( 'datetests', 'picker' );
		TTPromise.add( 'datetests', 'range' );
		this.testDatePicker();
		this.testDateRange();
		//as timepicker is a date picker with alt options enabled, it must be triggered after the date tests are done or the selectors will collide and possibly provide false positives
		TTPromise.wait( 'datetests', null, function() {
			$this.testTimePicker();
		} );

		TTPromise.add( 'wizardtests', 'imagebrowser' );
		this.testImageBrowser();
		TTPromise.wait( 'wizardtests', 'imagebrowser', function() {
			$this.testFormulaBuilder();
		} );

		TTPromise.wait( 'null', 'null', function() {
			$this.testTags();
		} );

		this.testButtons();
		this.testText();
		this.testPassword();
		this.testTextarea();

		//Extra tests that should be added to test non-form widgets
		//todo: TAlert
		//todo: inside_editor
		//todo: error tips
		//todo: test feedback widget
		//todo: live_chat
		//todo: paging
		//todo: search_panel
		//todo: top_notification
		//todo: view_min_tab
	}

	testCheckbox() {
		var $this = this;
		QUnit.test( 'Checkbox tests', function( assert ) {
			var cb = $this.edit_view_ui_dic.checkbox;
			assert.ok( cb.getValue() == false, 'checkbox not checked' );
			cb.setValue( true );
			assert.ok( cb.getValue(), 'checkbox checked via code' );
			cb.setValue( false );
			assert.ok( cb.getValue() == false, 'checkbox unchecked via code' );
			$( cb ).click();
			assert.ok( cb.getValue(), 'checkbox checked via click' );
			$( cb ).click();
			assert.ok( cb.getValue() == false, 'checkbox unchecked via click' );
		} );
	}

	testColorPicker() {
		var $this = this;
		QUnit.test( 'ColorPicker tests', function( assert ) {
			var done_testing = assert.async();
			var widget = $this.edit_view_ui_dic.color_picker;
			assert.ok( widget.getValue() == '', 'widget empty' );
			$( widget ).click();

			setTimeout( function() {
				$( '.cp-color-picker' ).hide();
				assert.ok( widget.getValue() == 'FFFFFF', 'widget white after no select: ' + widget.getValue() );
				widget.setValue( 'FF0000' );
				assert.ok( widget.getValue() == 'FF0000', 'widget red after set value programatically' + widget.getValue() );

				$( widget ).click();
				//there's no color selection by click because it's based on mouseup and mousedown events which can't be simulated at specific coordinates

				$( '.cp-color-picker' ).hide();
				done_testing();
			}, 1000 );
		} );
	}

	testDatePicker() {
		var $this = this;
		QUnit.test( 'DatePicker tests', function( assert ) {
			var done_testing = assert.async();
			var widget = $this.edit_view_ui_dic.datepicker;
			assert.ok( widget.getValue() == '', 'widget empty' );
			setTimeout( function() {
				widget.find( '#tDatePickerIcon' ).trigger( 'mouseup' );
				assert.ok( ( $( '#ui-datepicker-div:visible' ).length > 0 ), 'calendar visible after click' );
				$( $( '#ui-datepicker-div .ui-datepicker-calendar td' )[15] ).click();
				setTimeout( function() {
					assert.ok( ( $( '#ui-datepicker-div:visible' ).length == 0 ), 'calendar invisible after date selection' );
					assert.ok( widget.getValue() != '', 'value is not blank' );
					TTPromise.resolve( 'datetests', 'picker' );

					done_testing();
				}, 1000 );
			}, 1000 );
		} );
	}

	testDateRange() {
		var $this = this;
		QUnit.test( 'DateRange tests', function( assert ) {
			var done_testing = assert.async();
			var widget = $this.edit_view_ui_dic.daterange;
			assert.ok( typeof widget.getValue() == 'undefined', 'widget empty ' + widget.getValue() );
			widget.find( '#tDatePickerIcon' ).trigger( 'mouseup' );
			setTimeout( function() {
				assert.ok( ( $( '.t-range-picker-div:visible' ).length > 0 ), 'calendar visible after click' );
				$( $( '.t-range-picker-div .end-picker td' )[15] ).click();
				$( '.t-range-picker-div .close-icon' ).click();
				setTimeout( function() {
					assert.ok( ( $( '.t-range-picker-div:visible' ).length == 0 ), 'calendar invisible after closing' );
					assert.ok( widget.getValue() != '', 'value is not blank' );
					done_testing();
					TTPromise.resolve( 'datetests', 'range' );
				}, 1000 );
			}, 1000 );
		} );
	}

	testTimePicker() {
		var $this = this;
		QUnit.test( 'TimePicker tests', function( assert ) {
			var widget = $this.edit_view_ui_dic.timepicker;
			assert.ok( widget.getValue() == '', 'widget empty: ' + widget.getValue() );
			widget.find( '#tTimePickerIcon' ).trigger( 'mouseup' );
			assert.ok( ( $( '#ui-datepicker-div:visible' ).length > 0 ), 'timepicker widget visible after click' );
			$( '#ui-datepicker-div:visible button[data-handler="today"]' ).click();
			assert.ok( ( $( '#ui-datepicker-div:visible' ).length > 0 ), 'timepicker widget visible after now click' );
			$( '#ui-datepicker-div:visible button[data-handler="hide"]' ).click();
			assert.ok( ( $( '#ui-datepicker-div:visible' ).length == 0 ), 'timepicker widget gone clicking close button' );
			assert.ok( widget.getValue() != '', 'value is not blank' );
		} );
	}

	testImageBrowser() {
		var $this = this;
		TTPromise.add( '' );
		QUnit.test( 'ImageBrowser tests', function( assert ) {
			var done_testing = assert.async();
			var widget = $this.edit_view_ui_dic.imagebrowser;
			assert.ok( typeof widget.getValue() == 'undefined', 'widget empty: ' + widget.getValue() );
			widget.find( '#upload_image' ).click();
			setTimeout( function() {
				assert.ok( $( '.wizard:visible' ).length > 0, 'wizard visible' );
				$( '.wizard:visible .close-btn' ).click();
				assert.ok( $( '.wizard:visible' ).length == 0, 'wizard invisible' );
				done_testing();
				TTPromise.resolve( 'wizardtests', 'imagebrowser' );
			}, 1000 );
		} );
	}

	testFormulaBuilder() {
		var $this = this;
		QUnit.test( 'FormulaBuilder tests', function( assert ) {
			var done_testing = assert.async();
			var widget = $this.edit_view_ui_dic.formula;
			assert.ok( widget.getValue() == '', 'widget empty: ' + widget.getValue() );
			widget.find( '.t-button.formula-btn' ).click();
			setTimeout( function() {
				assert.ok( $( '.wizard:visible' ).length > 0, 'wizard visible' );
				$( '.wizard:visible .close-btn' ).click();
				assert.ok( $( '.wizard:visible' ).length == 0, 'wizard invisible' );
				done_testing();
			}, 1000 );
		} );
	}

	testButtons() {
		var $this = this;
		QUnit.test( 'TotalButtons tests', function( assert ) {
			var done_testing = assert.async();

			var all_employee_btn = $( '.control-bar .action-chooser-div #all_employee .all-employee' );
			var daily_totals_btn = $( '.control-bar .action-chooser-div #daily_totals .daily' );
			var weekly_totals_btn = $( '.control-bar .action-chooser-div #weekly_totals .weekly' );
			var strict_range_btn = $( '.control-bar .action-chooser-div #strict_range .strict-range' );

			assert.ok( strict_range_btn.hasClass( 'selected' ), 'strict range starts selected' );
			assert.ok( daily_totals_btn.hasClass( 'selected' ) == false, 'daily totals starts unselected' );
			assert.ok( weekly_totals_btn.hasClass( 'selected' ) == false, 'week total starts unselected' );
			assert.ok( all_employee_btn.hasClass( 'selected' ) == false, 'all employees starts unselected' );

			strict_range_btn.click();
			setTimeout( function() {
				assert.ok( strict_range_btn.hasClass( 'selected' ) == false, 'strict range unselects on click' );
				daily_totals_btn.click();
				setTimeout( function() {
					assert.ok( daily_totals_btn.hasClass( 'selected' ), 'daily_totals_btn selects on click' );
					weekly_totals_btn.click();
					setTimeout( function() {
						assert.ok( weekly_totals_btn.hasClass( 'selected' ), 'weekly_totals_btn selects on click' );
						all_employee_btn.click();
						setTimeout( function() {
							assert.ok( all_employee_btn.hasClass( 'selected' ), 'all_employee_btn selects on click' );
							strict_range_btn.click();
							daily_totals_btn.click();
							weekly_totals_btn.click();
							all_employee_btn.click();
							setTimeout( function() {

								//back to initial state
								assert.ok( strict_range_btn.hasClass( 'selected' ), 'strict range starts selected' );
								assert.ok( daily_totals_btn.hasClass( 'selected' ) == false, 'daily totals starts unselected' );
								assert.ok( weekly_totals_btn.hasClass( 'selected' ) == false, 'week total starts unselected' );
								assert.ok( all_employee_btn.hasClass( 'selected' ) == false, 'all employees starts unselected' );
								done_testing();
							}, 1500 );
						}, 1500 );
					}, 1500 );
				}, 1500 );
			}, 1500 );
		} );
	}

	testTags() {
		var $this = this;
		QUnit.test( 'Tags tests', function( assert ) {
			var done_testing = assert.async();
			var el = $this.edit_view_ui_dic.tags.find( 'input.add-tag-input' );
			var val = $this.edit_view_ui_dic.tags.getValue();
			assert.ok( val == '', 'starts empty ' + val );

			el.focus();
			$( el ).val( 'test1' );
			$( el ).trigger( { type: 'keydown', which: 32, keyCode: 32 } );
			$( el ).val( 'test2' );
			$( el ).trigger( { type: 'keydown', which: 32, keyCode: 32 } );

			setTimeout( function() {
				var val = $this.edit_view_ui_dic.tags.getValue();
				assert.ok( val == 'test1,test2', '2 tags entered and return in value: ' + val );
				setTimeout( function() {
					//click the x's to remove them
					$( '.tag-span-div .close-btn' ).click();
					setTimeout( function() {
						var val = $this.edit_view_ui_dic.tags.getValue();
						assert.ok( val == '-test1,-test2', '2 tags entered and return (subtracted) in value: ' + val );
						done_testing();
					}, 1000 );
				}, 1000 );
			}, 1000 );
		} );
	}

	testText() {
		var $this = this;
		QUnit.test( 'Text test', function( assert ) {
			//var done_testing = assert.async();
			var textbox = $this.edit_view_ui_dic.text;
			textbox.setValue( 'testing textbox' );
			assert.ok( textbox.getValue() == 'testing textbox', 'accepts text and returns value' );
			textbox.setValue( '' );
			assert.ok( textbox.getValue() == '', 'returned value updates when text is updated' );
		} );
	}

	testPassword() {
		var $this = this;
		QUnit.test( 'Password box test', function( assert ) {
			//var done_testing = assert.async();
			var textbox = $this.edit_view_ui_dic.password;
			textbox.setValue( 'testing textbox' );
			assert.ok( textbox.getValue() == 'testing textbox', 'accepts text and returns value' );
			textbox.setValue( '' );
			assert.ok( textbox.getValue() == '', 'returned value updates when text is updated' );
		} );
	}

	testTextarea() {
		var $this = this;
		QUnit.test( 'Password box test', function( assert ) {
			//var done_testing = assert.async();
			var textbox = $this.edit_view_ui_dic.textarea;
			textbox.setValue( 'testing textbox' );
			assert.ok( textbox.getValue() == 'testing textbox', 'accepts text and returns value' );
			textbox.setValue( '' );
			assert.ok( textbox.getValue() == '', 'returned value updates when text is updated' );
		} );
	}

}