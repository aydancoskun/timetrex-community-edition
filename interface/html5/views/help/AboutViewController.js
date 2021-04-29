import '@/global/widgets/filebrowser/TImageBrowser';
import '@/global/widgets/filebrowser/TImageAdvBrowser';

export class AboutViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			date_api: null,
			employeeActive: []
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.viewId = 'About';
		this.script_name = 'AboutView';
		this.context_menu_name = $.i18n._( 'About' );
		this.api = TTAPI.APIAbout;
		this.date_api = TTAPI.APITTDate;

		this.render();

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'Check For<br>Updates' ),
					id: ContextMenuIconName.check_updates,
					group: 'editor',
					icon: Icons.check_updates
				},
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.check_updates:
				this.onCheckClick();
				break;
		}
	}

	onCheckClick() {
		var $this = this;
		this.api['isNewVersionAvailable']( {
			onResult: function( result ) {
				$this.current_edit_record = result.getResult();

				$this.initEditView();
			}
		} );
	}

	getAboutData( callBack ) {
		var $this = this;
		$this.api['getAboutData']( {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( Global.isSet( result_data ) ) {
					callBack( result_data );
				}

			}
		} );
	}

	openEditView() {
		var $this = this;

		if ( $this.edit_only_mode ) {

			this.buildContextMenu();
			if ( !$this.edit_view ) {
				$this.initEditViewUI( 'About', 'AboutEditView.html' );
			}

			$this.getAboutData( function( result ) {
				// Waiting for the TTAPI.API returns data to set the current edit record.
				$this.current_edit_record = result;

				$this.initEditView();

			} );

		}
	}

	setUIWidgetFieldsToCurrentEditRecord() {
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		if ( !Global.isSet( this.current_edit_record['license_data'] ) ) {
			this.current_edit_record['license_data'] = {};
		}

		for ( var i in this.edit_view_form_item_dic ) {
			this.detachElement( i );
		}

		for ( var key in this.current_edit_record ) {
			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];

			switch ( key ) {
				case 'new_version':
					if ( this.current_edit_record[key] === true ) {

						this.attachElement( 'notice' );

						var html = '<br><b>' + $.i18n._( 'NOTICE' ) + ':' + '</b> ' + $.i18n._( 'There is a new version of' ) + ' ';
						html += '<b>' + $.i18n._( this.current_edit_record['application_name'] ) + '</b> ' + $.i18n._( 'available' ) + '.';
						html += '<br>' + $.i18n._( 'This version may contain tax table updates necessary for accurate payroll calculation, we recommend that you upgrade as soon as possible.' ) + '<br>';
						html += '' + $.i18n._( 'The latest version can be downloaded from' ) + ':' + ' <a href=\'https://' + this.current_edit_record['organization_url'] + '/?upgrade=1\' target=\'_blank\'>';
						html += '<b>' + this.current_edit_record['organization_url'] + '</b></a><br><br>';

						$( this.edit_view_form_item_dic['notice'].find( '.tblDataWarning' ) ).html( html );

					}
					break;
				case 'registration_key':
					if ( Global.isSet( widget ) ) {
						if ( this.current_edit_record[key] === '' || Global.isFalseOrNull( this.current_edit_record[key] ) ) {
							widget.setValue( $.i18n._( 'N/A' ) );
						} else {
							widget.setValue( this.current_edit_record[key] );
						}
					}
					break;
				case 'hardware_id':
					if ( Global.isSet( widget ) ) {
						if ( this.current_edit_record[key] === '' || Global.isFalseOrNull( this.current_edit_record[key] ) ) {
							widget.setValue( $.i18n._( 'N/A' ) );
						} else {
							widget.setValue( this.current_edit_record[key] );
						}
					}
					break;
				case 'cron': //popular case
					if ( Global.isSet( widget ) ) {
						if ( this.current_edit_record[key]['last_run_date'] !== '' ) {
							widget.setValue( this.current_edit_record[key]['last_run_date'] );
						} else {
							widget.setValue( $.i18n._( 'Never' ) );
						}
					}
					break;
				case 'license_data':
					if ( Global.isSet( this.current_edit_record[key] ) ) {

						this.attachElement( 'license_info' );
						this.attachElement( 'license_browser' );

						var separated_box = $( this.edit_view_form_item_dic['license_info'].find( '.separated-box' ) );

						if ( this.current_edit_record[key]['message'] ) {
							separated_box.css( {
								'font-weight': 'bold',
								'background-color': 'red',
								'height': 'auto',
								'color': '#000000'
							} );
							separated_box.html( $.i18n._( 'License Information' ) + '<br>' + $.i18n._( 'WARNING' ) + ': ' + this.current_edit_record[key]['message'] );
							$( separated_box.find( 'span' ) ).removeClass( 'label' ).css( {
								'font-size': 'normal',
								'font-weight': 'bold'
							} );
						} else {
							separated_box.html( '<span class="label">' + $.i18n._( 'License Information' ) + '</span>' );
						}

						if ( this.current_edit_record[key]['organization_name'] ) {
							for ( var k in this.current_edit_record[key] ) {
								switch ( k ) {
									case 'major_version':
									case 'minor_version':
										this.attachElement( '_version' );
										this.edit_view_ui_dic['_version'].setValue( this.current_edit_record[key]['major_version'] + '.' + this.current_edit_record[key]['minor_version'] + '.X' );
										break;
									default:
										if ( Global.isSet( this.edit_view_ui_dic[k] ) && Global.isSet( this.edit_view_form_item_dic[k] ) ) {
											this.attachElement( k );
											this.edit_view_ui_dic[k].setValue( this.current_edit_record[key][k] );
										}
										break;
								}
							}
						}
					}
					break;
				case 'user_counts':
					if ( this.current_edit_record[key].length > 0 ) {
						this.attachElement( 'user_active_inactive' );
					}
					break;
				case 'schema_version_group_A':
				case 'schema_version_group_B':
				case 'schema_version_group_C':
				case 'schema_version_group_D':
					if ( Global.isSet( widget ) && this.current_edit_record[key] ) {
						widget.setValue( this.current_edit_record[key] );
					}
					break;
				default:
					if ( Global.isSet( widget ) ) {
						widget.setValue( this.current_edit_record[key] );
					}
					break;
			}

			if ( Global.isSet( widget ) && this.current_edit_record[key] == false ) {
				widget.parents( '.edit-view-form-item-div' ).detach();
			}

		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.setActiveEmployees();
	}

	setActiveEmployees() {

		if ( this.employeeActive.length > 0 ) {
			for ( var i in this.employeeActive ) {
				var field = this.employeeActive[i].getField();
				if ( Global.isSet( this.edit_view_form_item_dic[field] ) ) {
					this.edit_view_form_item_dic[field].remove();
				}
			}

			this.employeeActive = [];

		}

		if ( Global.isSet( this.current_edit_record['user_counts'] ) && this.current_edit_record['user_counts'].length > 0 ) {
			var tab_about = this.edit_view_tab.find( '#tab_about' );
			var tab_about_column1 = tab_about.find( '.first-column' );

			for ( var key in this.current_edit_record['user_counts'] ) {

				var item = this.current_edit_record['user_counts'][key];

				var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'active_' + key } );
				form_item_input.setValue( item['max_active_users'] + ' / ' + item['max_inactive_users'] );

				this.addEditFieldToColumn( $.i18n._( item['label'] ), form_item_input, tab_about_column1, '', null, true );

				this.employeeActive.push( form_item_input );

				this.edit_view_ui_dic['active_' + key].css( 'opacity', 1 );
			}

			this.editFieldResize( 0 );
		}
	}

	buildEditViewUI() {
		var $this = this;
		super.buildEditViewUI();

		var tab_model = {
			'tab_about': { 'label': $.i18n._( 'About' ) },
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_about = this.edit_view_tab.find( '#tab_about' );

		var tab_about_column1 = tab_about.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_about_column1 );

		var form_item_input = $( '<div class=\'tblDataWarning\'></div>' );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'notice' );

		// separate box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'System Information' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1 );

		// Product Edition
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'product_edition' } );
		this.addEditFieldToColumn( $.i18n._( 'Product Edition' ), form_item_input, tab_about_column1 );

		// Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'system_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Version' ), form_item_input, tab_about_column1 );

		// Operating System
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'operating_system'

		} );
		this.addEditFieldToColumn( $.i18n._( 'Operating System' ), form_item_input, tab_about_column1 );

		// PHP Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'php_version'
		} );
		this.addEditFieldToColumn( $.i18n._( 'PHP Version' ), form_item_input, tab_about_column1 );

		// Tax Engine Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'tax_engine_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Tax Engine Version' ), form_item_input, tab_about_column1 );

		// Tax Data Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'tax_data_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Tax Data Version' ), form_item_input, tab_about_column1 );

		// Registration Key
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'registration_key' } );
		this.addEditFieldToColumn( $.i18n._( 'Registration Key' ), form_item_input, tab_about_column1 );

		// Hardware ID
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'hardware_id' } );
		this.addEditFieldToColumn( $.i18n._( 'Hardware ID' ), form_item_input, tab_about_column1 );

		// Maintenance Jobs Last Ran
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'cron' } );
		this.addEditFieldToColumn( $.i18n._( 'Maintenance Jobs Last Ran' ), form_item_input, tab_about_column1 );

		// separate box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'License Information' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'license_info' );

		if ( LocalCacheData.productEditionId > 10 && APIGlobal.pre_login_data.primary_company_id == LocalCacheData.getCurrentCompany().id ) {
			// Upload License
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );
			this.file_browser = form_item_input.TImageBrowser( {
				field: 'license_browser',
				id: 'license_browser',
				name: 'filedata',
				accept_filter: '*',
				changeHandler: function( a ) {
					$this.uploadLicense( this );
				}
			} );
		}
		this.addEditFieldToColumn( $.i18n._( 'Upload License' ), form_item_input, tab_about_column1, '', null, true );

		// Product
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'product_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Product' ), form_item_input, tab_about_column1, '', null, true );

		// Company
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'organization_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_about_column1, '', null, true );

		// Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: '_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Version' ), form_item_input, tab_about_column1, '', null, true );

		// Active Employee Licenses
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'active_employee_licenses' } );
		this.addEditFieldToColumn( $.i18n._( 'Active Employee Licenses' ), form_item_input, tab_about_column1, '', null, true );

		// Issue Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'issue_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Issue Date' ), form_item_input, tab_about_column1, '', null, true );

		// Expire Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'expire_date_display' } );
		this.addEditFieldToColumn( $.i18n._( 'Expire Date' ), form_item_input, tab_about_column1, '', null, true );

		// Schema Version
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Schema Version' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1 );

		// Group A
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'schema_version_group_A' } );
		this.addEditFieldToColumn( $.i18n._( 'Group A' ), form_item_input, tab_about_column1 );

		// Group B
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'schema_version_group_B' } );
		this.addEditFieldToColumn( $.i18n._( 'Group B' ), form_item_input, tab_about_column1 );

		// Group C
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'schema_version_group_C' } );
		this.addEditFieldToColumn( $.i18n._( 'Group C' ), form_item_input, tab_about_column1 );

		// Group D
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'schema_version_group_D' } );
		this.addEditFieldToColumn( $.i18n._( 'Group D' ), form_item_input, tab_about_column1 );

		// Separated Box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Employees (Active / InActive)' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'user_active_inactive' );
	}

	uploadLicense( obj ) {
		var $this = this;
		var file = this.edit_view_ui_dic['license_browser'].getValue();
		$this.api.uploadFile( file, 'object_type=license&object_id=', {
			onResult: function( res ) {
				//file upload returns a "TRUE" string on success
				if ( res == 'TRUE' ) {
					//$this.openEditView();

					ProgressBar.showProgressBar();
					IndexViewController.setNotificationBar( 'login' );
					window.setTimeout( function() {
						window.location.reload();
					}, 3000 );
				} else {
					//TAlertManager.showAlert( $.i18n._( 'Invalid license file' ) )
					TAlertManager.showAlert( res );
					$( '#file_browser' ).val( '' ); //Clear the file name from beside the "Choose File" button.
				}

			}
		} );
	}

}
