QuickPunchViewController = Backbone.View.extend({
    type_array: null,
    status_array: null,
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
        'change textarea.form-control': 'onFormItemChange',
    },
    initialize: function () {
        var $this = this;
        if ( !LocalCacheData.getPunchLoginUser() ) {
            IndexViewController.instance.router.navigate('QuickPunchLogin', true);
            return false;
        }
        this.current_edit_record = null;
        this.edit_view_error_ui_dic = {};
        this.permission_id = 'punch';
        this.other_fields = [];
        this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
        this.branch_api = new (APIFactory.getAPIClass( 'APIBranch' ))();
        this.department_api = new (APIFactory.getAPIClass( 'APIDepartment' ))();
        this.other_field_api = new (APIFactory.getAPIClass( 'APIOtherField' ))();
        if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
            this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
            this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
        }
        this.initPermission();
        ProgressBar.showOverlay();
        this.getUserPunch( function( result ) {
            // $this.current_edit_record = result;
            $this.render();
        } );
    },

    initPermission: function() {
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

        var result = false;

        // Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemEnabled is not a function on line 207
        var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
        if ( company_api && _.isFunction( company_api.isBranchAndDepartmentAndJobAndJobItemEnabled ) ) {
            result = company_api.isBranchAndDepartmentAndJobAndJobItemEnabled( {async: false} );
        }

        //tried to fix Unable to get property 'getResult' of undefined or null reference, added if(!result)
        if ( !result ) {
            this.show_branch_ui = false;
            this.show_department_ui = false;
            this.show_job_ui = false;
            this.show_job_item_ui = false;
        } else {
            result = result.getResult();
            if ( !result.branch ) {
                this.show_branch_ui = false;
            }

            if ( !result.department ) {
                this.show_department_ui = false;
            }

            if ( !result.job ) {
                this.show_job_ui = false;
            }

            if ( !result.job_item ) {
                this.show_job_item_ui = false;
            }
        }

        // if ( !this.show_job_ui && !this.show_job_item_ui ) {
        //     this.show_bad_quantity_ui = false;
        //     this.show_good_quantity_ui = false;
        // }

    },

    jobUIValidate: function() {
        if ( PermissionManager.validate( "job", 'enabled' ) &&
            PermissionManager.validate( "punch", 'edit_job' ) ) {
            return true;
        }
        return false;
    },

    jobItemUIValidate: function() {
        if ( PermissionManager.validate( "job", 'enabled' ) &&
            PermissionManager.validate( "punch", 'edit_job_item' ) ) {
            return true;
        }
        return false;
    },

    branchUIValidate: function() {
        if ( PermissionManager.validate( "punch", 'edit_branch' ) ) {
            return true;
        }
        return false;
    },

    departmentUIValidate: function() {
        if ( PermissionManager.validate( "punch", 'edit_department' ) ) {
            return true;
        }
        return false;
    },

    goodQuantityUIValidate: function() {
        if ( PermissionManager.validate( "job", 'enabled' ) &&
            PermissionManager.validate( "punch", 'edit_quantity' ) ) {
            return true;
        }
        return false;
    },

    badQuantityUIValidate: function() {
        if ( PermissionManager.validate( "job", 'enabled' ) &&
            PermissionManager.validate( "punch", 'edit_bad_quantity' ) ) {
            return true;
        }
        return false;
    },

    transferUIValidate: function() {
        if ( PermissionManager.validate( "punch", 'edit_transfer' ) || PermissionManager.validate( "punch", 'default_transfer' ) ) {
            return true;
        }
        return false;
    },

    noteUIValidate: function() {
        if ( PermissionManager.validate( "punch", 'edit_note' ) ) {
            return true;
        }
        return false;
    },

    initOptions: function() {
        var $this = this;
        var args = {};
        args.filter_columns = {
            id: true,
            name: true
        };
        TTPromise.add('QuickPunch', 'init');
        TTPromise.wait( null, null, function() {
            var field = $this.getFirstValiableField();
            field.setAttribute('tab-start', '');
            field.focus();
            var quick_punch_success_controller = new QuickPunchModalController({
                el: $this.el,
                _timedRedirect: 10,
                is_modal: false,
                _delegateCallback: function () {
                    $this.onSaveClick();
                }
            });
        });
        TTPromise.add('QuickPunch', 'Status');
        this.api.getOptions( 'status', {
            onResult: function( result ) {
                $this.status_array = result.getResult();
                $this.setSourceData('status_id', result.getResult(), true );
                TTPromise.resolve('QuickPunch', 'Status');
            }
        } );
        TTPromise.add('QuickPunch', 'Type');
        this.api.getOptions( 'type', {
            onResult: function( result ) {
                $this.type_array = result.getResult();
                $this.setSourceData('type_id', result.getResult(), true );
                TTPromise.resolve('QuickPunch', 'Type');
            }
        } );
        TTPromise.add('QuickPunch', 'Branch');
        this.branch_api.getBranch( args, true, {
            onResult: function( result ) {
                $this.setSourceData( 'branch_id', result.getResult(), true );
                TTPromise.resolve('QuickPunch', 'Branch');
            }
        } );
        TTPromise.add('QuickPunch', 'Department');
        this.department_api.getDepartment( args, true, {
            onResult: function( result ) {
                $this.setSourceData( 'department_id', result.getResult(), true );
                TTPromise.resolve('QuickPunch', 'Department');
            }
        } );
        if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
            args.filter_data = {status_id: 10, user_id: this.current_edit_record.user_id};
            TTPromise.add('QuickPunch', 'Job');
            args.filter_columns.manual_id = args.filter_columns.default_item_id = true;
            this.job_api.getJob( args, true, {
                onResult: function( result ) {
                    $this.setSourceData( 'job_id', result.getResult(), true );
                    TTPromise.resolve('QuickPunch', 'Job');
                }
            } );
        }
        if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
            args.filter_data = {status_id: 10, job_id: this.current_edit_record.job_id};
            TTPromise.add('QuickPunch', 'JobItem');
            args.filter_columns.default_item_id = false;
            this.job_item_api.getJobItem( args, true, {
                onResult: function( result ) {
                    $this.setSourceData( 'job_item_id', result.getResult(), true );
                    TTPromise.resolve('QuickPunch', 'JobItem');
                }
            } );
        }
        TTPromise.resolve('QuickPunch', 'init');
    },

    getFirstValiableField: function () {
        return _.min( this.$('input[tabindex], select[tabindex], textarea[tabindex]'), function ( el ) {
            if ( $(el).attr('tabindex') ) {
                return parseInt($(el).attr('tabindex'));
            }
        } );
    },

    getOtherFields: function ( callback ) {
        var $this = this;
        var filter = {filter_data: {type_id: 15}};// punch type
        this.other_field_api.getOtherField( filter, true, {
            onResult: function( result ) {
                var res_data = result.getResult();
                if ( $.type( res_data ) === 'array' && res_data.length > 0 ) {
                    res_data = res_data[0];

                    for ( var i = 1; i < 10; i++ ) {
                        if ( res_data['other_id' + i] ) {
                            var permission = PermissionManager.getPermissionData();
                            if ( Global.isSet( permission[$this.permission_id]['edit_other_id' + i] ) ) {
                                if ( PermissionManager.validate( $this.permission_id, 'edit_other_id' + i ) == false ) {
                                    continue;
                                }
                            }
                            // field: 'other_id' + i,
                            // value: res_data['other_id' + i]
                            $this.other_fields.push({
                                field: 'other_id' + i,
                                label: res_data['other_id' + i],
                                value: $this.current_edit_record['other_id' + i]
                            })
                        }
                    }
                }
                callback();
            }
        } );
    },

    render: function () {
        var $this = this;
        var row = Global.loadWidget( 'views/quick_punch/punch/QuickPunchView.html' );
        this.setElement( _.template(row)({
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
        }) );
        this.save_btn = this.$('.btn-block');
        this.save_btn.on('click', function () {
            $this.onSaveClick();
        });
        Global.contentContainer().html( this.$el );
        this.initOptions();
        $(document).off('keydown').on("keydown", function(event) {
            var attrs = event.target.attributes;
            if ( event.keyCode === 13 ) {
                if ( $this.save_btn.attr('disabled') == 'disabled' ) {
                    return;
                }
                if ( event.target.name == 'note' ) {
                    return;
                }
                $this.onSaveClick();
                event.preventDefault();
            }
            if ( event.keyCode === 9 && event.shiftKey ) {
                if (attrs['tab-start']) {
                    $this.$('button[tabindex=0]', $this.$el)[0].focus();
                    event.preventDefault();
                }
            }
            if ( attrs['tab-end'] && event.shiftKey === false ) {
                $this.$('input[tabindex=1]', $this.$el)[0].focus();
                event.preventDefault();
            }
        });
        return this;
    },

    getUserPunch: function( callBack ) {
        var $this = this;

        var station_id = Global.getStationID();
        var api_station = new (APIFactory.getAPIClass( 'APIStation' ))();

        if ( station_id ) {
            api_station.getCurrentStation( station_id, '10', {
                onResult: function( result ) {
                    doNext( result );
                }
            } );
        } else {
            api_station.getCurrentStation( '', '10', {
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
            // $.cookie( 'StationID', res_data, {expires: 10000, path: LocalCacheData.cookie_path} );
            Global.setStationID( res_data );
            $this.api.getUserPunch( {
                onResult: function( result ) {
                    var result_data = result.getResult();
                    if ( !result.isValid() ) {
                        $this.showErrorAlert( result, function () {
                            setTimeout(function () {
                                $this.doLogout();
                            }, 5000)
                        } );
                        return
                    }
                    if ( Global.isSet( result_data ) ) {
                        $this.current_edit_record = result_data;
                        $this.getOtherFields( callBack );
                    }
                }
            } );
        }
    },
    
    showErrorAlert: function ( result, callback ) {
        var error_list = result.getDetails() ? result.getDetails()[0] : {};
        if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
            error_list = error_list.error;
        }
        var container = $('<div>')
            .addClass('alert')
            .addClass('alert-danger')
            .attr('role', 'alert');
        for ( var key in error_list ) {
            if ( !error_list.hasOwnProperty( key ) ) {
                continue;
            }
            var error_obj = $('<p>')
                .addClass('text-center');
            var error_string;
            if ( _.isArray( error_list[key] ) ) {
                error_string = error_list[key][0];
            } else {
                error_string = error_list[key];
            }
            error_obj.text( $.i18n._( error_string ) );
            container.append( error_obj );
        }
        if ( _.size( error_list )> 0 ) {
            Global.contentContainer().html( container );
        }
        callback();
    },

    onFormItemChange: function( e, doNotValidate ) {
        var key = e.currentTarget.name;
        var c_value = $(e.currentTarget).val();
        this.current_edit_record[key] = c_value;
        switch ( key ) {
            case 'job_id':
                if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
                    var manual_id = $($(e.currentTarget).find("option[value='"+c_value+"']")).attr('manual_id');
                    this.$('input[name="job_quick_search"]').val( manual_id  ? manual_id : '' );
                    this.setJobItemValueWhenJobChanged( c_value );
                }
                break;
            case 'job_item_id':
                if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
                    var manual_id = $($(e.currentTarget).find("option[value='"+c_value+"']")).attr('manual_id');
                    this.$('input[name="job_item_quick_search"]').val( manual_id  ? manual_id : '' );
                }
                break;
            case 'transfer':
                var c_value;
                if ( $(e.currentTarget).is(':checked' ) ) {
                    c_value = true;
                } else {
                    c_value = false;
                }
                this.current_edit_record[key] = c_value;
                this.onTransferChanged( c_value );
                break;
            case 'job_quick_search':
            case 'job_item_quick_search':
                if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
                    this.onJobQuickSearch( key, c_value );
                }
                break;
        }
        if ( !doNotValidate ) {
            this.validate();
        }
    },

    validate: function() {
        var $this = this;
        this.api.setUserPunch( this.current_edit_record, true, {
            onResult: function( result ) {
                $this.validateResult( result );
            }
        } );
    },

    validateResult: function( result ) {
        var $this = this;
        if ( !result.isValid() ) {
            $this.setErrorTips( result );
        }
    },

    setErrorTips: function(result) {
        this.clearErrorTips( true );
        var error_list = {};
        var no_obj_error_string = '';
        if ( result.getDetails() ) {
	        error_list = result.getDetails()[0];
        } else {
        	var error = result.getDescription();
	        TAlertManager.showAlert( error, 'Error' );
	        this.save_btn.removeAttr('disabled');
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
                    if ( this.$('input[name="' + key + '"]')[0] ) {
                        field_obj = this.$('input[name="' + key + '"]');
                    } else if ( this.$('select[name="' + key + '"]')[0] ) {
                        field_obj = this.$('select[name="' + key + '"]');
                    } else if ( this.$('textarea[name="' + key + '"]')[0] ) {
                        field_obj = this.$('textarea[name="' + key + '"]');
                    }
                    break;
            }
            if ( _.isArray( error_list[key] ) ) {
                error_string = error_list[key][0]
            } else {
                error_string = error_list[key]
            }
            if ( field_obj ) {
                this.showErrorTips( field_obj, error_string );
                this.edit_view_error_ui_dic[key] = field_obj;
            } else {
	            no_obj_error_string += error_string + '</br>';
            }
        }
        if ( _.size( this.edit_view_error_ui_dic ) > 0 ) {
            this.save_btn.removeAttr('disabled');
            var focus_obj = _.min( this.edit_view_error_ui_dic, function ( item ) {
                if ( item.attr('tabindex') ) {
                    return parseInt(item.attr('tabindex'));
                }
            } );
            if ( typeof focus_obj.focus !== 'undefined' ) {
                focus_obj.focus();
            } else {
                var field = this.getFirstValiableField();
                field.focus();
            }
        }
        if ( no_obj_error_string != '' ) {
	        this.save_btn.removeAttr('disabled');
        	TAlertManager.showAlert( no_obj_error_string, 'Error' );
        }
    },

    showErrorTips: function ( field_obj, error_msg ) {
        field_obj.addClass('error-tip');
        field_obj.attr('data-original-title', error_msg);
        field_obj.tooltip({
            // title: error_msg,
            container: 'body',
            trigger: 'hover focus',
            // placement: 'right'
            // delay: { "show": 0, "hide": 0 }
        })
        field_obj.tooltip('show');
        // field_obj.tooltip('show');
    },

    clearErrorTips: function( clear_all, destroy ){
        for ( var key in this.edit_view_error_ui_dic ) {
            if ( this.edit_view_error_ui_dic[key].val() !== '' || clear_all ) {
                this.edit_view_error_ui_dic[key].removeClass('error-tip');
                this.edit_view_error_ui_dic[key].attr('data-original-title', '');
            }
            if ( destroy ) {
	            this.edit_view_error_ui_dic[key].tooltip('destroy');
            }
        }
        this.edit_view_error_ui_dic = {};
    },

    onJobQuickSearch: function( key, value ) {
        var args = {};
        var $this = this;
        args.filter_columns = {
            id: true,
            name: true,
            manual_id: true,
        }
        if ( key === 'job_quick_search' ) {
            args.filter_data = {manual_id: value, user_id: this.current_edit_record.user_id, status_id: "10"};
            args.filter_columns.default_item_id = true;
            this.job_api.getJob( args, {
                onResult: function( result ) {
                    var result_data = result.getResult();
                    if ( result_data.length > 0 ) {
                        $this.$('select[name="job_id"]').val( result_data[0].id );
                        $this.current_edit_record.job_id = result_data[0].id;
                        $this.setJobItemValueWhenJobChanged( result_data[0].id );
                    } else {
                        $this.$('select[name="job_id"]').val( '' );
                        $this.current_edit_record.job_id = false;
                        $this.setJobItemValueWhenJobChanged( null );
                    }
                    // $this.$( 'select[name="job_id"]' ).selectpicker('refresh');
                }
            } );
        } else if ( key === 'job_item_quick_search' ) {
            args.filter_data = {manual_id: value, job_id: this.current_edit_record.job_id, status_id: "10"};
            this.job_item_api.getJobItem( args, {
                onResult: function( result ) {
                    var result_data = result.getResult();
                    if ( result_data.length > 0 ) {
                        $this.$('select[name="job_item_id"]').val( result_data[0].id );
                        $this.current_edit_record.job_item_id = result_data[0].id;

                    } else {
                        $this.$('select[name="job_item_id"]').val( '' );
                        $this.current_edit_record.job_item_id = false;
                    }
                    // $this.$( 'select[name="job_item_id"]' ).selectpicker('refresh');
                }
            } );
        }

    },

    onTransferChanged: function( value ) {
        if ( value ) {

            this.$('select[name="type_id"]').attr( 'disabled', true );
            this.$('select[name="status_id"]').attr( 'disabled', true );

            this.old_type_status.type_id = this.$('select[name="type_id"]').val();
            this.old_type_status.status_id = this.$('select[name="status_id"]').val();

            this.$('select[name="type_id"]').val( 10 );
            this.$('select[name="status_id"]').val( 10 );

            this.current_edit_record.type_id = 10;
            this.current_edit_record.status_id = 10;

        } else {
            this.$('select[name="type_id"]').removeAttr( 'disabled' );
            this.$('select[name="status_id"]').removeAttr( 'disabled' );

            if ( this.old_type_status.hasOwnProperty( 'type_id' ) ) {
                this.$('select[name="type_id"]').val( this.old_type_status.type_id );
                this.$('select[name="status_id"]').val( this.old_type_status.status_id );

                this.current_edit_record.type_id = this.old_type_status.type_id;
                this.current_edit_record.status_id = this.old_type_status.status_id;
            }
        }
    },

    setJobItemValueWhenJobChanged: function( job, job_item_id_col_name, filter_data ) {
        var $this = this;
        if ( job_item_id_col_name == undefined ) {
            job_item_id_col_name = 'job_item_id';
        }
        if ( filter_data == undefined ) {
            filter_data = {status_id: 10, job_id: job}
        }
        var args = {};
        args.filter_data = filter_data;
        args.filter_columns = {
            id: true,
            name: true,
            manual_id: true
        };
        var job_item_widget = $this.$('select[name="' + job_item_id_col_name + '"]');
        var current_job_item_id = job_item_widget.val();
        if ( parseInt( current_job_item_id ) > 0 ) {
            var new_arg = Global.clone( args );
            new_arg.filter_data.job_id = job;
            $this.job_item_api.getJobItem( new_arg, {
                onResult: function( task_result ) {
                    var data = task_result.getResult();
                    if ( data.length > 0 ) {
                        $this.current_edit_record[job_item_id_col_name] = current_job_item_id;
                    } else {
                        setDefaultData(job_item_id_col_name);
                    }
                    $this.setSourceData( job_item_id_col_name, data, true );
                }
            } )

        } else {
            setDefaultData(job_item_id_col_name);
            $this.job_item_api.getJobItem( args, true, {
                onResult: function( result ) {
                    $this.setSourceData( job_item_id_col_name, result.getResult(), true );
                }
            } );
        }

        function setDefaultData(job_item_id_col_name) {
            if ( job_item_id_col_name == undefined ) {
                job_item_id_col_name = 'job_item_id';
            }
            if ( $this.current_edit_record.job_id ) {
                // job_item_widget.val( $this.$('select[name="job_id"] option[value="'+ job +'"]').attr('default_item_id') );
                var default_item_id = $this.$('select[name="job_id"] option[value="'+ job +'"]').attr('default_item_id');
                $this.current_edit_record[job_item_id_col_name] = default_item_id;
                if ( default_item_id === false || default_item_id === 0 ) {
                    $this.$('input[name="job_item_quick_search"]').val( '' );
                }
            } else {
                job_item_widget.val( '' );
                $this.current_edit_record[job_item_id_col_name] = false;
                $this.$('input[name="job_item_quick_search"]').val( '' );
            }
        }
    },

    setSourceData: function( field, source_data, set_empty ) {
        var $this = this;
        var field_selector = 'select[name="' + field + '"]';
        if ( this.$( field_selector ) && this.$( field_selector )[0] ) {
            this.$( field_selector ).empty();
        } else {
            return;
        }
        if ( _.size( source_data ) == 0 ) {
            set_empty = true;
        }
        if ( set_empty === true ) {
            this.$( field_selector )
                .append($("<option></option>")
                    .attr("value", '0')
                    .text( '-- ' + $.i18n._('None') + ' --' ))
                .attr('selected', 'selected');
            // this.$( field_selector ).selectpicker();
        }
        if ( _.size( source_data ) > 0 ) {
            if ( _.isArray( source_data ) ) {
                _.each( source_data, function ( option ) {
                    var optionEl = $("<option></option>")
                        .attr("value", option.id)
                        .text( option.name );
                    if ( field == 'job_id' || field == 'job_item_id' ) {
                        optionEl.attr('manual_id', option.manual_id);
                    }
                    if ( field == 'job_id' ) {
                        optionEl.attr('default_item_id', option.default_item_id);
                    }
                    $this.$( field_selector ).append( optionEl );
                    if ( $this.current_edit_record[field] == option.id ) {
                        $this.$( field_selector ).val( option.id );
                        if ( field == 'job_id' ) {
                            $this.$('input[name="job_quick_search"]').val( option.manual_id );
                        }
                        if ( field == 'job_item_id' ) {
                            $this.$('input[name="job_item_quick_search"]').val( option.manual_id );
                        }
                    }
                });
            } else {
                $.each( source_data, function ( value, label ) {
                    $this.$( field_selector )
                        .append($("<option></option>")
                            .attr("value", value)
                            .text( label ));
                    if (  field == 'status_id' || field == 'type_id'  ) {
                    	if ( $this.current_edit_record.transfer == true ) {
		                    $this.current_edit_record[field] = 10;
	                    }
                    }
                    if ( $this.current_edit_record[field] == value ) {
                        $this.$( field_selector ).val( value );
                    }
                });
            }
            // $this.$( field_selector ).selectpicker('refresh');
        } else {
            switch ( field ) {
                case 'branch_id':
                case 'department_id':
                    $this.$('div.'+field).hide();
                    break;
                case 'job_id':
                    $this.$('div.'+field).hide();
                    $this.$('div.job_item_id').hide();
                    break;
            }
        }
    },

    onSaveClick: function() {
        var $this = this;
        var record = this.current_edit_record;
        this.save_btn.attr('disabled', 'disabled');
        this.clearErrorTips(true, true);
        this.api.setUserPunch( record, false, false, {
            onResult: function( result ) {
                if ( result.isValid() ) {
                    var result_data = result.getResult();
                    var quick_punch_success_controller = new QuickPunchModalController({
                        el: _.template(Global.loadWidget( 'views/quick_punch/punch/QuickPunchSuccessView.html' ))({
                            user_full_name: $this.current_edit_record['first_name'] + ' ' + $this.current_edit_record['last_name'],
                            label: $this.current_edit_record['status_id'] == 10 ? $.i18n._('In') : $.i18n._('Out'),
                            return_date: $this.current_edit_record['time_stamp'],
                            is_mobile: (_.isUndefined(window.is_mobile) == false)
                        }),
                        _timedRedirect: 5,
                        is_modal: true,
                        _delegateCallback: function () {
                            $this.doLogout();
                        }
                    });
                } else {
                    $this.setErrorTips( result );
                }
            }
        } );
    },

    doLogout: function() {
        //Don't wait for result of logout in case of slow or disconnected internet. Just clear local cookies and move on.
        var current_user_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
        current_user_api.Logout({onResult:function(){}})
        Global.setAnalyticDimensions();
        if ( typeof(ga) != "undefined" && APIGlobal.pre_login_data.analytics_enabled === true ) {
            ga('send', 'pageview', {'sessionControl': 'end'});
        }
        //A bare "if" wrapped around lh_inst doesn't work here for some reason.
        if ( typeof(lh_inst) != "undefined" ) {
            //stop the update loop for live chat with support
            clearTimeout(lh_inst.timeoutStatuscheck);
        }
        $.cookie( 'SessionID-QP', null, {expires: 30, path: LocalCacheData.cookie_path} );
        LocalCacheData.setPunchLoginUser(null);
        LocalCacheData.setCurrentCompany(null);
        sessionStorage.clear();
        if ( window.location.href === Global.getBaseURL() + '#!m=QuickPunchLogin' ) {
            IndexViewController.instance.router.reloadView( 'QuickPunchLogin' );
        } else {
            window.location = Global.getBaseURL() + '#!m=QuickPunchLogin';
        }
    }

});

QuickPunchModalController = Backbone.View.extend({
    timed_redirect_time: 0,
    initialize: function ( options ) {
        var $this = this;
        this._delegateCallback = options._delegateCallback || null;
        this._timedRedirect = options._timedRedirect;
        this.is_modal = options.is_modal;
        this.render();
    },

    render: function () {
        var $this = this;
        $(document).off('keyup').on('keyup', function () {
            $this.stopTimedRedirect();
        });
        $(document).off('mouseup').on('mouseup', function () {
            $this.stopTimedRedirect();
        });
        if ( this.is_modal ) {
            $(this.el).on('hidden.bs.modal', function() {
                $this.remove();
                $this.delegateCallback();
            });
            $(this.el).on('shown.bs.modal', function() {
                $this.timedRedirect( $this._timedRedirect );
            });
            $('body').append( $(this.el).modal({
                backdrop: 'static',
                show: true
            }) );
        } else {
            $this.timedRedirect( $this._timedRedirect );
        }
        return this;
    },

    delegateCallback: function () {
        if ( this._delegateCallback ) {
            this._delegateCallback();
        }
    },

    stopTimedRedirect: function () {
        if ( this.is_modal === false ) {
            this._delegateCallback = null;
        }
        this.removeTimedRedirect();
    },

    updateTimedRedirect: function( time ){
        if ( time != 'undefined' && time > 0 ) {
            this.timed_redirect_time = time;
        }
    },

    removeTimedRedirect: function () {
        clearTimeout(this.timer);
        if ( this.is_modal ) {
            this.$el.modal('hide');
        } else {
            this.$('#timedRedirect').remove();
            this.delegateCallback();
        }
    },

    timedRedirect: function( time ) {
        var $this = this;
        this.updateTimedRedirect( time );
        if ( this.timed_redirect_time > 0 ) {
            this.$('#timedRedirect').html( '( ' + this.timed_redirect_time + ' )' );
            this.timed_redirect_time--;
            this.timer = setTimeout( function () {
                $this.timedRedirect(null)
            } , 1000);
        } else if ( this.timed_redirect_time == 0 ) {
            this.removeTimedRedirect();
        }
    },
})
