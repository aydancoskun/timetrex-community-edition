RemittanceSourceAccountViewController = BaseViewController.extend( {
	el: '#remittance_source_account_view_container',

	_required_files: ['APIRemittanceSourceAccount', 'APILegalEntity', 'APICurrency', 'APILog'],

	status_array: null,
	type_array: null,
	country_array: null,
	data_format_array: null,
	company_api: null,

	init: function() {
		//this._super('initialize' );
		this.edit_view_tpl = 'RemittanceSourceAccountEditView.html';
		this.permission_id = 'remittance_source_account';
		this.viewId = 'RemittanceSourceAccount';
		this.script_name = 'RemittanceSourceAccountView';
		this.table_name_key = 'remittance_source_account';
		this.context_menu_name = $.i18n._( 'Remittance Source Accounts' );
		this.navigation_label = $.i18n._( 'Remittance Source Account' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRemittanceSourceAccount' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

		$('#tab_advanced_content_div .edit-view-form-item-div .edit-view-form-item-label-div').css('border-top-left-radius', '0px');
		$('#tab_advanced_content_div .edit-view-form-item-div:first .edit-view-form-item-label-div').css('border-top-left-radius', '5px');
	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'type' );
//		this.initDropDownOption( 'data_format' );
		this.initDropDownOption( 'country', 'country', this.company_api );
	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		this.setAdvancedTabVisible();

		if ( this.edit_view_tab_selected_index > 1 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {

				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

	},

	setAdvancedTabVisible: function () {
		var tabs = $('.edit-view-tab-bar .ui-tabs-nav li');

		var type_id = this.edit_view_ui_dic.type_id.getValue();
		var country = (this.edit_view_ui_dic.country.getValue() && this.edit_view_ui_dic.country.getValue() != TTUUID.zero_id)? this.edit_view_ui_dic.country.getValue() : this.current_edit_record.country; //sometimes it's false for no reason.

		if ( (type_id == 3000 && country == 'US') || (type_id == 3000 && country == 'CA') ) {
			$(tabs[1]).show();
		} else {
			$(tabs[1]).hide();
		}
	},

	buildContextMenuModels: function() {

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

		var copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.copy,
			group: editor_group,
			icon: Icons.copy_as_new,
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

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
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

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
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


		return [menu];

	},


	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'country':
			case 'type_id':
				this.onTypeChange();
				break;
			case 'value24':
				if ( c_value != false ) {
					this.attachElement('value25').text($.i18n._('Offset Description') + ':');
					this.attachElement('value27').text($.i18n._('Offset Routing') + ':');
					this.attachElement('value28').text($.i18n._('Offset Account') + ':');
					if ( this.edit_view_ui_dic.value25.getValue().length == 0 ) {
						this.edit_view_ui_dic.value25.setValue('OFFSET');
					}
				} else {
					this.detachElement('value25');
					this.detachElement('value27');
					this.detachElement('value28');
				}
				break;
		}
		for (var evud_key in this.edit_view_ui_dic ){
			this.current_edit_record[evud_key] =this.edit_view_ui_dic[evud_key].getValue();
		}

		this.current_edit_record[key] = c_value;


		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onSaveClick: function(){
		this._super('onSaveClick');
		Global.clearCache('getOptions');
	},

	attachElement: function( key ) {
		//Error: Uncaught TypeError: Cannot read property 'insertBefore' of undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150822-210544 line 6439
		if ( !this.edit_view_form_item_dic || !this.edit_view_form_item_dic[key] ) {
			return;
		}

		var place_holder = $( '.place_holder_' + key );
		this.edit_view_form_item_dic[key].insertBefore( place_holder );
		place_holder.remove();

		return $(this.edit_view_form_item_dic[key].find('.edit-view-form-item-label') );
	},


	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'type_id': //popular case
						widget.setValue( this.current_edit_record[key] );
						this.onTypeChange();
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},


	onTypeChange: function() {
		var type_id = this.edit_view_ui_dic.type_id.getValue();
		var country = (this.edit_view_ui_dic.country.getValue() && this.edit_view_ui_dic.country.getValue() != TTUUID.zero_id)? this.edit_view_ui_dic.country.getValue() : this.current_edit_record.country; //sometimes it's false for no reason.

		this.setAdvancedTabVisible();

		this.detachElement( 'last_transaction_number' );
		this.detachElement( 'data_format_id' );
		this.detachElement( 'value1' );
		this.detachElement( 'value2' );
		this.detachElement( 'value3' );
		this.detachElement( 'value4' );
		this.detachElement( 'value5' );
		this.detachElement( 'value6' );
		this.detachElement( 'value7' );
		this.detachElement( 'value8' );
		this.detachElement( 'value9' );
		this.detachElement( 'value10' );
		this.detachElement( 'value11' );
		this.detachElement( 'value12' );
		this.detachElement( 'value13' );
		this.detachElement( 'value14' );
		this.detachElement( 'value15' );
		this.detachElement( 'value16' );
		this.detachElement( 'value17' );
		this.detachElement( 'value18' );
		this.detachElement( 'value19' );
		this.detachElement( 'value20' );
		this.detachElement( 'value21' );
		this.detachElement( 'value22' );
		this.detachElement( 'value23' );
		this.detachElement( 'value24' );
		this.detachElement( 'value25' );
		this.detachElement( 'value26' );
		this.detachElement( 'value27' );
		this.detachElement( 'value28' );
		this.detachElement( 'value29' );
		this.detachElement( 'value30' );

		if ( country == false || type_id == false ) {
			return;
		}

		if ( type_id == 2000 && country == 'US' ) { //cheque US
			this.attachElement('last_transaction_number').text($.i18n._('Last Check Number') + ':');
			// this.attachElement('value2').text($.i18n._('Routing') + ':');
			// this.attachElement('value3').text($.i18n._('Account') + ':');
		}
		if ( type_id == 2000 && country == 'CA' ) { //cheque US
			this.attachElement( 'last_transaction_number' ).text( $.i18n._('Last Check Number') + ':' );
			 // this.attachElement( 'value1' ).text( $.i18n._('Institution Number') + ':' );
			 // this.attachElement( 'value2' ).text( $.i18n._('Routing') + ':' );
			 // this.attachElement( 'value3' ).text( $.i18n._('Account') + ':' );

		} else if ( type_id == 3000 && country == 'US' ) { //ACH (american eft)
			this.attachElement( 'last_transaction_number' ).text( $.i18n._('Last Batch Number') + ':' );
			this.attachElement( 'value2' ).text( $.i18n._('Routing') + ':' );
			this.attachElement( 'value3' ).text( $.i18n._('Account') + ':' );
			this.attachElement( 'value4' ).text( $.i18n._('Business Number') + ':' );
			this.attachElement( 'value5' ).text( $.i18n._('Immediate Origin') + ':' );
			this.attachElement( 'value6' ).text( $.i18n._('Immediate Origin Name') + ':' );
			this.attachElement( 'value7' ).text( $.i18n._('Immediate Dest.') + ':' );
			this.attachElement( 'value8' ).text( $.i18n._('Immediate Dest. Name') + ':' );
			this.attachElement( 'value9' ).text( $.i18n._('Trace Number') + ':' );

			this.attachElement( 'value24').text( $.i18n._('Offset Transaction') + ':' );
			if ( this.current_edit_record.value24 == 1 ) {
				this.current_edit_record.value24 = true;
				this.attachElement('value25').text($.i18n._('Offset Description') + ':');
				this.attachElement('value27').text($.i18n._('Offset Routing') + ':');
				this.attachElement('value28').text($.i18n._('Offset Account') + ':');
			}
			this.attachElement( 'value29').text( $.i18n._('File Header Line') + ':' );
			this.attachElement( 'value30').text( $.i18n._('File Trailer Line') + ':' );
			$('#tab_advanced_content_div .edit-view-form-item-div .edit-view-form-item-label-div').css('border-top-left-radius', '0px');
			$('#tab_advanced_content_div .edit-view-form-item-div:first .edit-view-form-item-label-div').css('border-top-left-radius', '5px');

		} else if ( type_id == 3000 && country == 'CA') { //canadian eft
			this.attachElement( 'last_transaction_number' ).text( $.i18n._('Last Batch Number') + ':' );
			this.attachElement( 'value1' ).text( $.i18n._('Institution') + ':' );
			this.attachElement( 'value2' ).text( $.i18n._('Bank Transit') + ':' );
			this.attachElement( 'value3' ).text( $.i18n._('Account') + ':' );

			this.attachElement( 'value5' ).text( $.i18n._('Originator ID') + ':' );
			this.attachElement( 'value6' ).text( $.i18n._('Originator Short Name') + ':' );
			this.attachElement( 'value7' ).text( $.i18n._('Data Center ID') + ':' );
			//this.attachElement( 'value7' ).text( $.i18n._('Data Center Name') + ':' );

			this.attachElement( 'value26').text( $.i18n._('Return Institution') + ':' );
			this.attachElement( 'value27').text( $.i18n._('Return Transit') + ':' );
			this.attachElement( 'value28').text( $.i18n._('Return Account') + ':' );
			this.attachElement( 'value29').text( $.i18n._('File Header Line') + ':' );
			this.attachElement( 'value30').text( $.i18n._('File Trailer Line') + ':' );
			$('#tab_advanced_content_div .edit-view-form-item-div .edit-view-form-item-label-div').css('border-top-left-radius', '0px');
			$('#tab_advanced_content_div .edit-view-form-item-div:first .edit-view-form-item-label-div').css('border-top-left-radius', '5px');

		}

		var $this = this;
		this.api.getOptions( 'data_format', {'type_id': type_id, 'country':country },  { async:false,
			onResult: function( res ) {
				$this.attachElement( 'data_format_id' );
				var result = res.getResult();

				$this.data_format_array = Global.buildRecordArray( result );

				if ( Global.isSet( $this.basic_search_field_ui_dic['data_format_id'] ) ) {
					$this.basic_search_field_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				}

				if ( Global.isSet( $this.adv_search_field_ui_dic['data_format_id'] ) ) {
					$this.adv_search_field_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				}

				$this.edit_view_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				if ( $this.current_edit_record['data_format_id'] && result[$this.current_edit_record['data_format_id'] ]  ) {
					$this.edit_view_ui_dic['data_format_id'].setValue( $this.current_edit_record['data_format_id'] );
				} else {
					$this.current_edit_record['data_format_id'] = $this.edit_view_ui_dic['data_format_id'].getValue();
				}
			}
		} );

		this.editFieldResize();
	},


	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );
		var $this = this;

		this.setTabLabels( {
			'tab_remittance_source_account': $.i18n._( 'Remittance Source Account' ),
			'tab_audit': $.i18n._( 'Audit' ),
			'tab_advanced': $.i18n._( 'Advanced' )
		} );

		var tab_0_label = this.edit_view.find( 'a[ref=tab_remittance_source_account]' );
		var tab_1_label = this.edit_view.find( 'a[ref=tab_audit]' );
		var tab_2_label = this.edit_view.find( 'a[ref=tab_advanced]' );
		tab_0_label.text( $.i18n._( 'Remittance Source Account' ) );
		tab_1_label.text( $.i18n._( 'Audit' ) );
		tab_2_label.text( $.i18n._( 'Advanced' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRemittanceSourceAccount' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REMITTANCE_SOURCE_ACCOUNT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_remittance_source_account = this.edit_view_tab.find( '#tab_remittance_source_account' );
		var tab_remittance_source_account_column1 = tab_remittance_source_account.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_remittance_source_account_column1 );

		//advanced tab
		var tab_advanced= this.edit_view_tab.find( '#tab_advanced' );
		var tab_advanced_column1 = tab_advanced.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_advanced_column1 );

		// Legal Entity
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APILegalEntity' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LEGAL_ENTITY,
			field: 'legal_entity_id',
			set_empty: true,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_remittance_source_account_column1, '' );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_remittance_source_account_column1, '' );

		// Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_remittance_source_account_column1 );
		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_remittance_source_account_column1 );
		form_item_input.parent().width( '45%' );

		//TYPE
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_remittance_source_account_column1, '' );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'country', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_remittance_source_account_column1 );

		// Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			field: 'currency_id',
			set_empty: true,
			show_search_inputs: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_remittance_source_account_column1 );

		// Data Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'data_format_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.data_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Format' ), form_item_input, tab_remittance_source_account_column1, '', null, true );


		// Last Transaction Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'last_transaction_number', width: '60'} );
		this.addEditFieldToColumn( $.i18n._( 'Last Transaction Number' ), form_item_input, tab_remittance_source_account_column1, '', null, true );

		//generate Value# fields 1-30
		//shorter and easier to read than 150 extra lines
		for ( var i = 1; i <= 30; i++ ) {
			if (i == 24){} else {}
			var width = '200';

			if ( i == 29 || i == 30 ) {
				width = '500';
			}
			var tab_for_values = tab_remittance_source_account_column1;
			if ( i > 3 ) {
				tab_for_values = tab_advanced_column1;
			}

			if ( i == 24 ) {
				form_item_input = Global.loadWidgetByName(FormItemType.CHECKBOX);
				form_item_input.TCheckbox({field: 'value' + i});
			} else {
				form_item_input = Global.loadWidgetByName(FormItemType.TEXT_INPUT);
				form_item_input.TTextInput({field: 'value' + i, width: width});
			}
			this.addEditFieldToColumn($.i18n._('Value' + i), form_item_input, tab_for_values, '', null, true);
		}

	},


	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: ALayoutIDs.LEGAL_ENTITY,
				api_class: (APIFactory.getAPIClass( 'APILegalEntity' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'LegalEntityView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
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
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 3,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

} );