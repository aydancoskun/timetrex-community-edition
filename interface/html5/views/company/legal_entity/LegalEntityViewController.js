import '@/global/widgets/filebrowser/TImage';
import '@/global/widgets/filebrowser/TImageAdvBrowser';

export class LegalEntityViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#legal_entity_view_container',

			// _required_files: ['TImage', 'TImageAdvBrowser'],

			status_array: null,
			type_array: null,
			classification_code_array: null,
			country_array: null,
			province_array: null,
			e_province_array: null,

			payment_services_status_array: null,

			company_api: null
		} );

		super( options );
	}

	init() {
		//this._super('initialize' );
		this.edit_view_tpl = 'LegalEntityEditView.html';
		this.permission_id = 'legal_entity';
		this.viewId = 'LegalEntity';
		this.script_name = 'LegalEntityView';
		this.table_name_key = 'legal_entity';
		this.context_menu_name = $.i18n._( 'Legal Entities' );
		this.navigation_label = $.i18n._( 'Legal Entity' ) + ':';
		this.api = TTAPI.APILegalEntity;
		this.company_api = TTAPI.APICompany;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'classification_code' );
		this.initDropDownOption( 'country', 'country', this.company_api );

		this.initDropDownOption( 'payment_services_status' );
	}

	onSetSearchFilterFinished() {
		var combo;
		var select_value;
		if ( this.search_panel.getSelectTabIndex() === 0 ) {
			combo = this.basic_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		} else if ( this.search_panel.getSelectTabIndex() === 1 ) {
			combo = this.adv_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		}
	}

	getLogoUrl() {
		var url = false;
		if ( this.current_edit_record.id ) {
			url = Global.getBaseURL() + '../send_file.php?api=1&object_type=legal_entity_logo&object_id=' + this.current_edit_record.id;
		}
		Debug.Text( url, 'LegalEntityViewController.js', 'LegalEntityViewController', 'getLogoUrl', 10 );
		return url;
	}

	setEditViewDataDone() {
		this.onPaymentServicesStatusChange();
		super.setEditViewDataDone();
		this.file_browser.setImage( this.getLogoUrl() );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.export_excel],
			include: [{
				label: $.i18n._( 'Payment Services<br>Statement' ),
				id: ContextMenuIconName.payment_services_statement,
				group: 'other',
				icon: Icons.payroll_export_report,
				// sort_order: 8000 // TODO: what is this for? Is it needed? From copy/paste
			}]
		};

		return context_menu_model;
	}

	setDefaultMenu( doNotSetFocus ) {

		super.setDefaultMenu( doNotSetFocus );

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.payment_services_statement:
					this.setDefaultMenuPaymentServicesStatementIcon( context_btn, grid_selected_length );
					break;
			}
		}
	}

	setDefaultMenuPaymentServicesStatementIcon( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.payment_services_statement:
				this.onPaymentServicesStatementClick();
				break;
		}
	}

	onPaymentServicesStatementClick() {
		var post_data = { 0: null, 1: null }; //Eventually we could pass start/end dates.
		this.doFormIFrameCall( post_data );
	}

	doFormIFrameCall( postData ) {
		Global.APIFileDownload( this.api.className, 'get' + 'PaymentServicesAccountStatementReport', postData );
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onBuildBasicUIFinished() {
		this.basic_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.basic_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.basic_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'country':
				if ( c_value.toString() === this.current_edit_record[key].toString() ) {
					break;
				}
				this.eSetProvince( c_value );
				break;
			case 'payment_services_status_id':
				this.current_edit_record[key] = c_value;
				this.onPaymentServicesStatusChange();
				break;
		}

		this.current_edit_record[key] = c_value;

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	openEditView( id ) {
		if ( id == false ) {
			var $this = this;
			this.initOptions();
			this.api.getLegalEntity( { filter_items_per_page: 1 }, {
				onResult: function( result ) {
					var record = result.getResult();
					if ( typeof record == 'object' ) {
						$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
						$this.current_edit_record = record[0];
						$this.initEditView();
					}
				}
			} );
		} else {
			super.openEditView();
		}
	}

	onPaymentServicesStatusChange() {
		if ( this.current_edit_record && this.current_edit_record['payment_services_status_id'] == 10 ) {
			this.attachElement( 'payment_services_user_name' );
			this.attachElement( 'payment_services_api_key' );
		} else {
			this.detachElement( 'payment_services_user_name' );
			this.detachElement( 'payment_services_api_key' );
		}

		this.editFieldResize();
	}

	buildEditViewUI() {
		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_legal_entity': { 'label': $.i18n._( 'Legal Entity' ) },
			'tab_payment_services': { 'label': $.i18n._( 'Payment Services' ), 'display_on_mass_edit': false },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		if ( this.navigation ) {
			this.navigation.AComboBox( {
				api_class: TTAPI.APILegalEntity,
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: 'global_legal_entity',
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start
		var tab_legal_entity = this.edit_view_tab.find( '#tab_legal_entity' );
		var tab_legal_entity_column1 = tab_legal_entity.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_legal_entity_column1 );

		//Payment Services Tab
		var tab_payment_services = this.edit_view_tab.find( '#tab_payment_services' );
		var tab_payment_services_column1 = tab_payment_services.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_payment_services_column1 );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_legal_entity_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_legal_entity_column1, '' );

		// Classification Code
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'classification_code' } );
		form_item_input.setSourceData( $this.classification_code_array );
		this.addEditFieldToColumn( $.i18n._( 'Classification Code' ), form_item_input, tab_legal_entity_column1, '' );

		// Legal Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'legal_name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Legal Name' ), form_item_input, tab_legal_entity_column1 );
		form_item_input.parent().width( '45%' );

		// Trade Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'trade_name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Trade Name' ), form_item_input, tab_legal_entity_column1 );
		form_item_input.parent().width( '45%' );

		// Short Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'short_name', width: '150' } );
		this.addEditFieldToColumn( $.i18n._( 'Short Name/Abbreviation' ), form_item_input, tab_legal_entity_column1 );

		// Address1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address1', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 1)' ), form_item_input, tab_legal_entity_column1 );
		form_item_input.parent().width( '45%' );

		// Address2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'address2', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 2)' ), form_item_input, tab_legal_entity_column1 );
		form_item_input.parent().width( '45%' );

		// city
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_legal_entity_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_legal_entity_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_legal_entity_column1 );

		//Postcode
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'postal_code', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_legal_entity_column1 );

		// Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Phone' ), form_item_input, tab_legal_entity_column1 );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'fax_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_legal_entity_column1 );

		//Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'start_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_legal_entity_column1 );

		//End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'end_date' } );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_legal_entity_column1 );

		if ( typeof FormData == 'undefined' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

			this.file_browser = form_item_input.TImageBrowser( { field: '', default_width: 128, default_height: 128 } );

			this.file_browser.bind( 'imageChange', function( e, target ) {
				new ServiceCaller().uploadFile( target.getValue(), 'object_type=legal_entity_logo&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( $this.getLogoUrl() );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			} );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );

			this.file_browser = form_item_input.TImageAdvBrowser( {
				field: '', callBack: function( form_data ) {
					new ServiceCaller().uploadFile( form_data, 'object_type=legal_entity_logo&object_id=' + $this.current_edit_record.id, {
						onResult: function( result ) {

							if ( result.toLowerCase() === 'true' ) {
								$this.file_browser.setImage( $this.getLogoUrl() );
							} else {
								TAlertManager.showAlert( result, 'Error' );
							}
						}
					} );

				}
			} );
		}

		if ( this.is_edit ) {
			this.file_browser.setEnableDelete( true );
			this.file_browser.bind( 'deleteClick', function( e, target ) {
				$this.api.deleteImage( $this.current_edit_record.id, {
					onResult: function( result ) {
						$this.onDeleteImage();
					}
				} );
			} );
		}

		this.addEditFieldToColumn( $.i18n._( 'Logo' ), this.file_browser, tab_legal_entity_column1, '', null, false, true );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'payment_services_status_id' } );
		form_item_input.setSourceData( $this.payment_services_status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_payment_services_column1, '', null, true );

		//User Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'payment_services_user_name', width: 250 } );
		this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, tab_payment_services_column1, '', null, true );

		//API Key
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'payment_services_api_key', width: 510 } );
		this.addEditFieldToColumn( $.i18n._( 'API Key' ), form_item_input, tab_payment_services_column1, '', null, true );
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
			this.basic_search_field_ui_dic['province'].setSourceData( [] );

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );
					$this.basic_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {
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
					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}
					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Legal Name' ),
				in_column: 1,
				field: 'legal_name',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Trade Name' ),
				in_column: 1,
				field: 'trade_name',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Phone' ),
				field: 'work_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				object_type_id: 110,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Fax' ),
				field: 'fax_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line1)' ),
				field: 'address1',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line2)' ),
				field: 'address2',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Postal/ZIP Code' ),
				field: 'postal_code',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: true,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 3,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

}