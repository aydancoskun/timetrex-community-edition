class QuickPunchViewController extends QuickPunchBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			type_array: null,
			status_array: null,
			branch_array: null,
			department_array: null,
			job_array: null,
			job_item_array: null,

			job_api: null,
			job_item_api: null,

			old_type_status: {},

			show_job_ui: false,
			show_job_item_ui: false,
			show_branch_ui: false,
			show_department_ui: false,
			show_good_quantity_ui: false,
			show_bad_quantity_ui: false,
			show_transfer_ui: false,
			show_node_ui: false,
			el: '#quick_punch_container',
			events: {
				'change input[type="text"]': 'onFormItemChange',
				'change input[type="checkbox"]': 'onFormItemChange',
				'change select.form-control': 'onFormItemChange',
				'change textarea.form-control': 'onFormItemChange'
			}
		} );

		super( options );
	}

	initialize( options ) {
		super.initialize( options );

		var $this = this;
		if ( !LocalCacheData.getPunchLoginUser() ) {
			IndexViewController.instance.router.navigate( 'QuickPunchLogin', true );
			return false;
		}
		this.current_edit_record = null;
		this.edit_view_error_ui_dic = {};
		this.permission_id = 'punch';
		this.other_fields = [];
		require( this.filterRequiredFiles(), function() {
			$this.api = TTAPI.APIPunch;
			$this.branch_api = TTAPI.APIBranch;
			$this.department_api = TTAPI.APIDepartment;
			$this.other_field_api = TTAPI.APIOtherField;
			if ( ( Global.getProductEdition() >= 20 ) ) {
				$this.job_api = TTAPI.APIJob;
				$this.job_item_api = TTAPI.APIJobItem;
			}
			$this.company_api = TTAPI.APICompany;
			$this.api_station = TTAPI.APIStation;
			$this.current_user_api = TTAPI.APIAuthentication;
			$this.initPermission();
			ProgressBar.showOverlay();

			TTPromise.add( 'QuickPunch', 'CurrentEditRecordComplete' );

			$this.initOptions();
			$this.getUserPunch();

			$this.getOtherFields( function() {
				TTPromise.wait( 'QuickPunch', 'CurrentEditRecordComplete', function() {
					$this.render();
				} );
			} );
		} );
	}

	initPermission() {
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

		if ( this.transferUIValidate() ) {
			this.show_transfer_ui = true;
		} else {
			this.show_transfer_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_node_ui = true;
		} else {
			this.show_node_ui = false;
		}

		// Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemEnabled is not a function on line 207
		if ( this.company_api && _.isFunction( this.company_api.isBranchAndDepartmentAndJobAndJobItemEnabled ) ) {
			var $this = this;
			this.company_api.isBranchAndDepartmentAndJobAndJobItemEnabled( {
				onResult: function( result ) {
					//tried to fix Unable to get property 'getResult' of undefined or null reference, added if(!result)
					if ( !result ) {
						$this.show_branch_ui = false;
						$this.show_department_ui = false;
						$this.show_job_ui = false;
						$this.show_job_item_ui = false;
					} else {
						result = result.getResult();
						if ( !result.branch ) {
							$this.show_branch_ui = false;
						}

						if ( !result.department ) {
							$this.show_department_ui = false;
						}

						if ( !result.job ) {
							$this.show_job_ui = false;
						}

						if ( !result.job_item ) {
							$this.show_job_item_ui = false;
						}
					}
				}
			} );
		}
	}

	jobUIValidate() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_job' ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	branchUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	goodQuantityUIValidate() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_quantity' ) ) {
			return true;
		}
		return false;
	}

	badQuantityUIValidate() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	}

	transferUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_transfer' ) || PermissionManager.validate( 'punch', 'default_transfer' ) ) {
			return true;
		}
		return false;
	}

	noteUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_note' ) ) {
			return true;
		}
		return false;
	}

	preRender( callBack ) {
		var $this = this;
		TTPromise.wait( 'QuickPunch_options', null, function() {
			$this.setSourceData( 'status_id', $this.status_array, true );
			$this.setSourceData( 'type_id', $this.type_array, true );
			$this.setSourceData( 'branch_id', $this.branch_array, true );
			$this.setSourceData( 'department_id', $this.department_array, true );
			$this.setSourceData( 'job_id', $this.job_array, true );
			$this.setSourceData( 'job_item_id', $this.job_item_array, true );

			Global.contentContainer().html( $this.$el );

			TTPromise.wait( null, null, function() {
				var field = $this.getFirstAvailableField();
				field.setAttribute( 'tab-start', '' );
				field.focus();
				var quick_punch_success_controller = new QuickPunchModalController( {
					el: $this.el,
					_timedRedirect: 10,
					is_modal: false,
					_delegateCallback: function() {
						$this.onSaveClick();
					}
				} );
			} );
		} );
	}

	initOptions() {
		var $this = this;
		var args = {};
		args.filter_columns = {
			id: true,
			name: true
		};
		TTPromise.add( 'QuickPunch_options', 'Status' );
		TTPromise.add( 'QuickPunch_options', 'Type' );
		TTPromise.add( 'QuickPunch_options', 'Branch' );
		TTPromise.add( 'QuickPunch_options', 'Department' );
		this.api.getOptions( 'status', {
			onResult: function( result ) {
				$this.status_array = result.getResult();
				TTPromise.resolve( 'QuickPunch_options', 'Status' );
			}
		} );
		this.api.getOptions( 'type', {
			onResult: function( result ) {
				$this.type_array = result.getResult();
				TTPromise.resolve( 'QuickPunch_options', 'Type' );
			}
		} );
		this.branch_api.getBranch( args, true, {
			onResult: function( result ) {
				$this.branch_array = result.getResult();

				TTPromise.resolve( 'QuickPunch_options', 'Branch' );
			}
		} );
		this.department_api.getDepartment( args, true, {
			onResult: function( result ) {
				$this.department_array = result.getResult();
				TTPromise.resolve( 'QuickPunch_options', 'Department' );
			}
		} );

		if ( ( Global.getProductEdition() >= 20 ) ) {
			TTPromise.add( 'QuickPunch_options', 'Job' );
			TTPromise.add( 'QuickPunch_options', 'JobItem' );

			var user = LocalCacheData.getPunchLoginUser();
			args.filter_data = { status_id: 10, user_id: user.id };
			args.filter_columns.manual_id = args.filter_columns.default_item_id = true;
			this.job_api.getJob( args, true, {
				onResult: function( result ) {
					$this.job_array = result.getResult();
					TTPromise.resolve( 'QuickPunch_options', 'Job' );
				}
			} );

			var $this = this;
			TTPromise.wait( 'QuickPunch', 'CurrentEditRecordComplete', function() {
				args.filter_data = { status_id: 10, job_id: $this.current_edit_record.job_id };
				args.filter_columns.default_item_id = false;
				$this.job_item_api.getJobItem( args, true, {
					onResult: function( result ) {
						$this.job_item_array = result.getResult();
						TTPromise.resolve( 'QuickPunch_options', 'JobItem' );
					}
				} );
			} );
		}
	}

	getFirstAvailableField() {
		return _.min( this.$( 'input[tabindex], select[tabindex], textarea[tabindex]' ), function( el ) {
			if ( $( el ).attr( 'tabindex' ) ) {
				return parseInt( $( el ).attr( 'tabindex' ) );
			}
		} );
	}

	getOtherFields( callBack ) {
		var $this = this;
		var filter = { filter_data: { type_id: 15 } };// punch type
		this.other_field_api.getOtherField( filter, true, {
			onResult: function( result ) {
				var res_data = result.getResult();
				if ( $.type( res_data ) === 'array' && res_data.length > 0 ) {
					var $res_data = res_data[0];
					TTPromise.wait( 'QuickPunch', 'CurrentEditRecordComplete', function() {
						for ( var i = 1; i < 10; i++ ) {
							if ( $res_data['other_id' + i] ) {
								var permission = PermissionManager.getPermissionData();
								if ( Global.isSet( permission[$this.permission_id]['edit_other_id' + i] ) ) {
									if ( PermissionManager.validate( $this.permission_id, 'edit_other_id' + i ) == false ) {
										continue;
									}
								}
								// field: 'other_id' + i,
								// value: res_data['other_id' + i]
								$this.other_fields.push( {
									field: 'other_id' + i,
									label: $res_data['other_id' + i],
									value: $this.current_edit_record['other_id' + i]
								} );
							}
						}
					} );
				}
				callBack();
			}
		} );
	}

	render() {
		var $this = this;
		var row = Global.loadWidget( 'views/quick_punch/punch/QuickPunchView.html' );

		//#2571 exception: null is not an object (evaluating 'this.current_edit_record['first_name']')
		//cancel when error case is hit
		if ( !this.current_edit_record ) {
			this.onCancelClick();
		}

		this.setElement( _.template( row )( {
			show_job_ui: this.show_job_ui,
			show_job_item_ui: this.show_job_item_ui,
			show_branch_ui: this.show_branch_ui,
			show_department_ui: this.show_department_ui,
			show_good_quantity_ui: this.show_good_quantity_ui,
			show_bad_quantity_ui: this.show_bad_quantity_ui,
			show_transfer_ui: this.show_transfer_ui,
			show_node_ui: this.show_node_ui,
			first_name: this.current_edit_record['first_name'],
			last_name: this.current_edit_record['last_name'],
			punch_time: this.current_edit_record['punch_time'],
			punch_date: this.current_edit_record['punch_date'],
			transfer: this.current_edit_record['transfer'],
			quantity: this.current_edit_record['quantity'],
			bad_quantity: this.current_edit_record['bad_quantity'],
			note: this.current_edit_record['note'],
			other_fields: this.other_fields
		} ) );
		this.save_btn = this.$( '#save_btn' );
		this.save_btn.on( 'click', function() {
			$this.onSaveClick();
		} );
		this.cancel_btn = this.$( '#cancel_btn' );
		this.cancel_btn.on( 'click', function() {
			$this.onCancelClick();
		} );

		this.preRender();

		//this.initOptions();
		$( document ).off( 'keydown' ).on( 'keydown', function( event ) {
			var attrs = event.target.attributes;
			if ( event.keyCode === 13 && $( event.target ).attr( 'id' ) != 'cancel_btn' ) {
				if ( $this.save_btn.attr( 'disabled' ) == 'disabled' ) {
					return;
				}
				if ( event.target.name == 'note' ) {
					return;
				}
				$this.onSaveClick();
				event.preventDefault();
				return;
			} else if ( ( event.keyCode === 13 || event.keyCode === 32 ) && $( event.target ).attr( 'id' ) == 'cancel_btn' ) {
				$this.onCancelClick();
				event.preventDefault();
				return;
			}

			if ( event.keyCode === 9 && event.shiftKey ) {
				//ensure the first editable item is selected.
				if ( $( event.target ).parents( '.form-group' ).index() == $( $( '.quick-punch-form-container select,input,textarea' )[0] ).parents( '.form-group' ).index() ) {

					$( '#cancel_btn' ).focus();
					event.preventDefault();
				}
			}
			if ( attrs['tab-end'] && event.shiftKey === false ) {
				var button = $this.$( 'input[tab-start]', $this.$el );
				if ( button.length > 0 ) { //Uncaught TypeError: Cannot read property 'focus' of undefined
					button[0].focus();
				}
				event.preventDefault();
			}
		} );
		return this;
	}

	getUserPunch() {
		var $this = this;

		var station_id = Global.getStationID();

		if ( station_id ) {
			this.api_station.getCurrentStation( station_id, '10', {
				onResult: function( result ) {
					doNext( result );
				}
			} );
		} else {
			this.api_station.getCurrentStation( '', '10', {
				onResult: function( result ) {
					doNext( result );
				}
			} );
		}

		function doNext( result ) {
			if ( !$this.api || typeof $this.api['getUserPunch'] !== 'function' ) {
				return;
			}
			var res_data = result.getResult();
			// setCookie( 'StationID', res_data );
			Global.setStationID( res_data );
		}

		$this.api.getUserPunch( {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( !result.isValid() ) {
					$this.showErrorAlert( result, function() {
						setTimeout( function() {
							$this.doLogout();
						}, 5000 );
					} );
					return;
				}
				if ( Global.isSet( result_data ) ) {
					$this.current_edit_record = result_data;
					$this.old_type_status.status_id = result_data.status_id;
					$this.old_type_status.type_id = result_data.type_id;
					TTPromise.resolve( 'QuickPunch', 'CurrentEditRecordComplete' );
				}
			}
		} );
	}

	showErrorAlert( result, callback ) {
		var error_list = result.getDetails() ? result.getDetails()[0] : {};
		if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
			error_list = error_list.error;
		}
		var container = $( '<div>' ).addClass( 'alert' ).addClass( 'alert-danger' ).attr( 'role', 'alert' );
		for ( var key in error_list ) {
			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}
			var error_obj = $( '<p>' ).addClass( 'text-center' );
			var error_string;
			if ( _.isArray( error_list[key] ) ) {
				error_string = error_list[key][0];
			} else {
				error_string = error_list[key];
			}
			error_obj.text( $.i18n._( error_string ) );
			container.append( error_obj );
		}
		if ( _.size( error_list ) > 0 ) {
			Global.contentContainer().html( container );
		}
		callback();
	}

	onFormItemChange( e, doNotValidate ) {
		var key = e.currentTarget.name;
		var c_value = $( e.currentTarget ).val();
		this.current_edit_record[key] = c_value;
		switch ( key ) {
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					var manual_id = $( $( e.currentTarget ).find( 'option[value=\'' + c_value + '\']' ) ).attr( 'manual_id' );
					this.$( 'input[name="job_quick_search"]' ).val( manual_id ? manual_id : '' );
					this.setJobItemValueWhenJobChanged( c_value );
				}
				break;
			case 'job_item_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					var manual_id = $( $( e.currentTarget ).find( 'option[value=\'' + c_value + '\']' ) ).attr( 'manual_id' );
					this.$( 'input[name="job_item_quick_search"]' ).val( manual_id ? manual_id : '' );
				}
				break;
			case 'transfer':
				var c_value;
				if ( $( e.currentTarget ).is( ':checked' ) ) {
					c_value = true;
				} else {
					c_value = false;
				}
				this.current_edit_record[key] = c_value;
				this.onTransferChanged( c_value );
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, c_value );
				}
				break;
		}
		if ( !doNotValidate ) {
			this.validate();
		}
	}

	validate() {
		var $this = this;
		this.api.setUserPunch( this.current_edit_record, true, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	}

	validateResult( result ) {
		var $this = this;
		if ( !result.isValid() ) {
			$this.setErrorTips( result );
		}
	}

	setErrorTips( result ) {
		this.clearErrorTips( true );
		var error_list = {};
		var no_obj_error_string = '';
		if ( result.getDetails() ) {
			error_list = result.getDetails()[0];
		} else {
			var error = result.getDescription();
			TAlertManager.showAlert( error, 'Error' );
			this.save_btn.removeAttr( 'disabled' );
			return;
		}
		if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
			error_list = error_list.error;
		}
		for ( var key in error_list ) {
			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}
			var field_obj;
			var error_string;
			switch ( key ) {
				// case 'time_stamp':
				//     field_obj = this.$('p[name="punch_time"]');
				//     break;
				// case 'lic_obj':
				//     field_obj = this.$('p[name="user_full_name"]');
				//     break;
				default:
					if ( this.$( 'input[name="' + key + '"]' )[0] ) {
						field_obj = this.$( 'input[name="' + key + '"]' );
					} else if ( this.$( 'select[name="' + key + '"]' )[0] ) {
						field_obj = this.$( 'select[name="' + key + '"]' );
					} else if ( this.$( 'textarea[name="' + key + '"]' )[0] ) {
						field_obj = this.$( 'textarea[name="' + key + '"]' );
					}
					break;
			}
			if ( _.isArray( error_list[key] ) ) {
				error_string = error_list[key][0];
			} else {
				error_string = error_list[key];
			}
			if ( field_obj ) {
				this.showErrorTips( field_obj, error_string );
				this.edit_view_error_ui_dic[key] = field_obj;
			} else {
				no_obj_error_string += error_string + '</br>';
			}
		}
		if ( _.size( this.edit_view_error_ui_dic ) > 0 ) {
			this.save_btn.removeAttr( 'disabled' );
			var focus_obj = _.min( this.edit_view_error_ui_dic, function( item ) {
				if ( item.attr( 'tabindex' ) ) {
					return parseInt( item.attr( 'tabindex' ) );
				}
			} );
			if ( typeof focus_obj.focus !== 'undefined' ) {
				focus_obj.focus();
			} else {
				var field = this.getFirstAvailableField();
				field.focus();
			}
		}
		if ( no_obj_error_string != '' ) {
			this.save_btn.removeAttr( 'disabled' );
			TAlertManager.showAlert( no_obj_error_string, 'Error' );
		}
	}

	showErrorTips( field_obj, error_msg ) {
		field_obj.addClass( 'error-tip' );
		field_obj.attr( 'data-original-title', error_msg );
		field_obj.tooltip( {
			// title: error_msg,
			container: 'body',
			trigger: 'hover focus'
			// placement: 'right'
			// delay: { "show": 0, "hide": 0 }
		} );
		field_obj.tooltip( 'show' );
		// field_obj.tooltip('show');
	}

	clearErrorTips( clear_all, destroy ) {
		for ( var key in this.edit_view_error_ui_dic ) {
			if ( this.edit_view_error_ui_dic[key].val() !== '' || clear_all ) {
				this.edit_view_error_ui_dic[key].removeClass( 'error-tip' );
				this.edit_view_error_ui_dic[key].attr( 'data-original-title', '' );
			}
			if ( destroy ) {
				this.edit_view_error_ui_dic[key].tooltip( 'dipose' );
			}
		}
		this.edit_view_error_ui_dic = {};
	}

	onJobQuickSearch( key, value ) {
		var args = {};
		var $this = this;
		args.filter_columns = {
			id: true,
			name: true,
			manual_id: true
		};
		if ( key === 'job_quick_search' ) {
			args.filter_data = { manual_id: value, user_id: this.current_edit_record.user_id, status_id: '10' };
			args.filter_columns.default_item_id = true;
			this.job_api.getJob( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.$( 'select[name="job_id"]' ).val( result_data[0].id );
						$this.current_edit_record.job_id = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0].id );
					} else {
						$this.$( 'select[name="job_id"]' ).val( '' );
						$this.current_edit_record.job_id = false;
						$this.setJobItemValueWhenJobChanged( null );
					}
					// $this.$( 'select[name="job_id"]' ).selectpicker('refresh');
				}
			} );
		} else if ( key === 'job_item_quick_search' ) {
			args.filter_data = { manual_id: value, job_id: this.current_edit_record.job_id, status_id: '10' };
			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.$( 'select[name="job_item_id"]' ).val( result_data[0].id );
						$this.current_edit_record.job_item_id = result_data[0].id;

					} else {
						$this.$( 'select[name="job_item_id"]' ).val( '' );
						$this.current_edit_record.job_item_id = false;
					}
					// $this.$( 'select[name="job_item_id"]' ).selectpicker('refresh');
				}
			} );
		}
	}

	onTransferChanged( value ) {
		if ( value ) {

			this.$( 'select[name="type_id"]' ).attr( 'disabled', true );
			this.$( 'select[name="status_id"]' ).attr( 'disabled', true );

			this.old_type_status.type_id = this.$( 'select[name="type_id"]' ).val();
			this.old_type_status.status_id = this.$( 'select[name="status_id"]' ).val();

			this.$( 'select[name="type_id"]' ).val( 10 );
			this.$( 'select[name="status_id"]' ).val( 10 );

			this.current_edit_record.type_id = 10;
			this.current_edit_record.status_id = 10;

		} else {
			this.$( 'select[name="type_id"]' ).removeAttr( 'disabled' );
			this.$( 'select[name="status_id"]' ).removeAttr( 'disabled' );

			if ( this.old_type_status.hasOwnProperty( 'type_id' ) ) {
				this.$( 'select[name="type_id"]' ).val( this.old_type_status.type_id );
				this.$( 'select[name="status_id"]' ).val( this.old_type_status.status_id );

				this.current_edit_record.type_id = this.old_type_status.type_id;
				this.current_edit_record.status_id = this.old_type_status.status_id;
			}
		}
	}

	setJobItemValueWhenJobChanged( job, job_item_id_col_name, filter_data ) {
		var $this = this;
		if ( job_item_id_col_name == undefined ) {
			job_item_id_col_name = 'job_item_id';
		}
		if ( filter_data == undefined ) {
			filter_data = { status_id: 10, job_id: job };
		}
		var args = {};
		args.filter_data = filter_data;
		args.filter_columns = {
			id: true,
			name: true,
			manual_id: true
		};
		var job_item_widget = $this.$( 'select[name="' + job_item_id_col_name + '"]' );
		var current_job_item_id = job_item_widget.val();

		if ( TTUUID.isUUID( current_job_item_id ) && current_job_item_id != TTUUID.zero_id && current_job_item_id != TTUUID.not_exist_id ) {
			var new_arg = Global.clone( args );
			new_arg.filter_data.job_id = job;
			$this.job_item_api.getJobItem( new_arg, {
				onResult: function( task_result ) {
					var data = task_result.getResult();
					if ( data.length > 0 ) {
						$this.current_edit_record[job_item_id_col_name] = current_job_item_id;
					} else {
						setDefaultData( job_item_id_col_name );
					}
					$this.setSourceData( job_item_id_col_name, data, true );
				}
			} );

		} else {
			setDefaultData( job_item_id_col_name );
			$this.job_item_api.getJobItem( args, true, {
				onResult: function( result ) {
					$this.setSourceData( job_item_id_col_name, result.getResult(), true );
				}
			} );
		}

		function setDefaultData( job_item_id_col_name ) {
			if ( job_item_id_col_name == undefined ) {
				job_item_id_col_name = 'job_item_id';
			}
			if ( $this.current_edit_record.job_id ) {
				// job_item_widget.val( $this.$('select[name="job_id"] option[value="'+ job +'"]').attr('default_item_id') );
				var default_item_id = $this.$( 'select[name="job_id"] option[value="' + job + '"]' ).attr( 'default_item_id' );
				$this.current_edit_record[job_item_id_col_name] = default_item_id;
				if ( default_item_id === false || default_item_id === 0 ) {
					$this.$( 'input[name="job_item_quick_search"]' ).val( '' );
				}
			} else {
				job_item_widget.val( '' );
				$this.current_edit_record[job_item_id_col_name] = false;
				$this.$( 'input[name="job_item_quick_search"]' ).val( '' );
			}
		}
	}

	setSourceData( field, source_data, set_empty ) {
		var $this = this;
		var field_selector = 'select[name="' + field + '"]';
		if ( $( this.$el ).find( field_selector ) && $( this.$el ).find( field_selector )[0] ) {
			$( this.$el ).find( field_selector ).empty();
		} else {
			return;
		}
		if ( _.size( source_data ) == 0 ) {
			set_empty = true;
		}
		if ( set_empty === true ) {
			var $field = $( $this.$el ).find( field_selector );
			switch ( field ) {
				case 'status_id':
				case 'type_id':
					break;
				default:
					$field.append( $( '<option></option>' ).prop( 'value', '0' ).text( '-- ' + $.i18n._( 'None' ) + ' --' ) ).attr( 'selected', 'selected' );
				// $($this.$el).find(field_selector).selectpicker();
			}
		}
		if ( _.size( source_data ) > 0 ) {
			if ( _.isArray( source_data ) ) {
				_.each( source_data, function( option ) {
					var optionEl = $( '<option></option>' ).prop( 'value', option.id ).text( option.name );
					if ( field == 'job_id' || field == 'job_item_id' ) {
						optionEl.attr( 'manual_id', option.manual_id );
					}
					if ( field == 'job_id' ) {
						optionEl.attr( 'default_item_id', option.default_item_id );
					}
					$( $this.$el ).find( field_selector ).append( optionEl );
					if ( $this.current_edit_record[field] == option.id ) {
						$( $this.$el ).find( field_selector ).val( option.id );
						if ( field == 'job_id' ) {
							$this.$( 'input[name="job_quick_search"]' ).val( option.manual_id );
						}
						if ( field == 'job_item_id' ) {
							$this.$( 'input[name="job_item_quick_search"]' ).val( option.manual_id );
						}
					}
				} );
			} else {
				$.each( source_data, function( value, label ) {
					$( $this.$el ).find( field_selector ).append( $( '<option></option>' ).prop( 'value', value ).text( label ) );
					if ( field == 'status_id' || field == 'type_id' ) {
						if ( $this.current_edit_record.transfer == true ) {
							$this.current_edit_record[field] = 10;
						}
					}
					if ( $this.current_edit_record[field] == value ) {
						$( $this.$el ).find( field_selector ).val( value );
					}
				} );
			}
			// $this.$($this.el).find(field_selector).selectpicker('refresh');
		} else {
			switch ( field ) {
				case 'branch_id':
				case 'department_id':
					$( $this.$el ).find( 'div.' + field ).hide();
					break;
				case 'job_id':
					$( $this.$el ).find( 'div.' + field ).hide();
					$( $this.$el ).find( 'div.job_item_id' ).hide();
					break;
			}
		}
	}

	onCancelClick() {
		window.history.back();
	}

	onSaveClick() {
		var $this = this;
		var record = this.current_edit_record;
		this.save_btn.attr( 'disabled', 'disabled' );
		this.clearErrorTips( true, true );
		this.api.setUserPunch( record, false, false, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					var quick_punch_success_controller = new QuickPunchModalController( {
						el: _.template( Global.loadWidget( 'views/quick_punch/punch/QuickPunchSuccessView.html' ) )( {
							user_full_name: $this.current_edit_record['first_name'] + ' ' + $this.current_edit_record['last_name'],
							label: $this.current_edit_record['status_id'] == 10 ? $.i18n._( 'In' ) : $.i18n._( 'Out' ),
							return_date: $this.current_edit_record['time_stamp'],
							is_mobile: ( _.isUndefined( window.is_mobile ) == false )
						} ),
						_timedRedirect: 5,
						is_modal: true,
						_delegateCallback: function() {
							$this.doLogout();
						}
					} );
				} else {
					$this.setErrorTips( result );
				}
			}
		} );
	}

	doLogout() {
		//Don't wait for result of logout in case of slow or disconnected internet. Just clear local cookies and move on.
		this.current_user_api.Logout( {
			onResult: function() {
			}
		} );
		Global.setAnalyticDimensions();
		if ( typeof ( ga ) != 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
			try {
				ga( 'send', 'pageview', { 'sessionControl': 'end' } );
			} catch ( e ) {
				throw e;
			}
		}
		//A bare "if" wrapped around lh_inst doesn't work here for some reason.
		if ( typeof ( lh_inst ) != 'undefined' ) {
			//stop the update loop for live chat with support
			clearTimeout( lh_inst.timeoutStatuscheck );
		}
		deleteCookie( 'SessionID-QP' );
		LocalCacheData.setPunchLoginUser( null );
		LocalCacheData.setCurrentCompany( null );
		sessionStorage.clear();
		if ( window.location.href === Global.getBaseURL() + '#!m=QuickPunchLogin' ) {
			IndexViewController.instance.router.reloadView( 'QuickPunchLogin' );
		} else {
			Global.setURLToBrowser( Global.getBaseURL() + '#!m=QuickPunchLogin' );
		}
	}

}

class QuickPunchModalController extends Backbone.View { //Must extend Backbone.View rather than TTBackbone otherwise Quick Punch success modal fails.
	constructor( options = {} ) {
		_.defaults( options, {
			timed_redirect_time: 0
		} );

		super( options );
	}

	initialize( options ) {
		//super.initialize( options );
		var $this = this;
		this._delegateCallback = options._delegateCallback || null;
		this._timedRedirect = options._timedRedirect;
		this.is_modal = options.is_modal;
		this.render();

		//Back button or Forward button
		window.onpopstate = function() {
			clearTimeout( $this.timer );
		};
	}

	render() {
		var $this = this;
		$( document ).off( 'keyup' ).on( 'keyup', function() {
			$this.stopTimedRedirect();
		} );

		//must use core Javascript as the jquery function did not catch the event properly and did not stop the timer when opening selects.
		// $(document).on('mouseup', function () {
		// 	$this.stopTimedRedirect();
		// });
		$( document ).off( 'mousedown' ).on( 'mousedown', function() {
			$this.stopTimedRedirect();
		} );
		if ( this.is_modal ) {
			$( this.el ).on( 'hidden.bs.modal', function() {
				$this.remove();
				$this.delegateCallback();
			} );
			$( this.el ).on( 'shown.bs.modal', function() {
				$this.timedRedirect( $this._timedRedirect );
			} );
			$( 'body' ).append( $( this.el ).modal( {
				backdrop: 'static',
				show: true
			} ) );
		} else {
			$this.timedRedirect( $this._timedRedirect );
		}
		return this;
	}

	delegateCallback() {
		if ( this._delegateCallback ) {
			this._delegateCallback();
		}
	}

	stopTimedRedirect() {
		if ( this.is_modal === false ) {
			this._delegateCallback = null;
		}
		this.removeTimedRedirect();
	}

	updateTimedRedirect( time ) {
		if ( time != 'undefined' && time > 0 ) {
			this.timed_redirect_time = time;
		}
	}

	removeTimedRedirect() {
		clearTimeout( this.timer );
		if ( this.is_modal ) {
			this.$el.modal( 'hide' );
		} else {
			this.$( '#timedRedirect' ).remove();
			this.delegateCallback();
		}
	}

	timedRedirect( time ) {
		var $this = this;
		this.updateTimedRedirect( time );
		if ( this.timed_redirect_time > 0 ) {
			this.$( '#timedRedirect' ).html( '( ' + this.timed_redirect_time + ' )' );
			this.timed_redirect_time--;
			this.timer = setTimeout( function() {
				$this.timedRedirect( null );
			}, 1000 );
		} else if ( this.timed_redirect_time == 0 ) {
			this.removeTimedRedirect();
		}
	}
}
