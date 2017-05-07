QuickPunchLoginViewController = Backbone.View.extend({
    events: {
        'change #language': 'onLanguageChange',
    },
    authentication_api: null,
    initialize: function() {
        var row = Global.loadWidget( 'views/quick_punch/login/QuickPunchLoginView.html' );
        this.template = _.template(row);
        this.setElement( this.template({}) );
        Global.contentContainer().html( this.$el );
        this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
        this.authentication_api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
        this.currentUser_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
        // this.currency_api = new (APIFactory.getAPIClass( 'APICurrency' ))();
        this.user_preference_api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
        this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
        this.permission_api = new (APIFactory.getAPIClass( 'APIPermission' ))();
        this.edit_view_error_ui_dic = {};
        LocalCacheData.all_url_args = {};
        this.render();
    },
    render: function() {
        var $this = this;
        this.login_btn = this.$('button#punch_login');
        this.login_btn.on('click', function ( e ) {
            $this.onLogin( e );
        })
        this.$('input[name="user_name"]').focus();
        this.setLanguageSourceData('language');
        $(document).off('keydown').on("keydown", function(event) {
            var attrs = event.currentTarget.activeElement.attributes;
            if ( event.keyCode === 13 ) {
                if ( $this.login_btn.attr('disabled') == 'disabled'  ) {
                    return;
                }
                $this.onLogin();
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

    // doLogin: function ( e ) {
    //     if ( e.keyCode === 13 ) {
    //         this.onLogin();
    //     }
    // },

    onLogin: function ( e ) {
        var $this = this;
        var user_name = this.$( '#user_name' ).val();
        var password = this.$( '#password' ).val();
        this.login_btn.attr('disabled', 'disabled');
        this.clearErrorTips(true, true );
        this.authentication_api.PunchLogin( user_name, password, {
            onResult: function( result ) {
                if ( result.isValid() ) {
                    result = result.getResult();
                    // LocalCacheData.setSessionID( result.SessionID );
                    $.cookie( 'SessionID-QP', result.SessionID, {expires: 30, path: LocalCacheData.cookie_path} );
                    $this.getUserPunch(function () {
                        $this.getCurrentUser();
                    });
                } else {
                    $this.setErrorTips( result );
                }
            }
        });
    },

    getCurrentUser: function () {
        this.currentUser_api.getCurrentUser( {onResult: this.onGetCurrentUser, delegate: this} );
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
                        $this.setErrorTips( result );
                        return
                    }
                    if ( Global.isSet( result_data ) ) {
                        callBack( result_data );
                    }
                }
            } );
        }
    },

    onGetCurrentUser: function( e ) {
        LocalCacheData.setPunchLoginUser( e.getResult() );
        e.get( 'delegate' ).updateUserPreference();

    },

    updateUserPreference: function( is_login ) {
        if ( typeof is_login === 'undefined' ) {
            is_login = true;
        }

        this.is_login = is_login;

        //Error: TypeError: this.currentUser_api.getCurrentUserPreference is not a function in /interface/html5/framework/jquery.min.js?v=8.0.4-20150320-094021 line 2 > eval line 205
        if ( !this.currentUser_api || typeof this.currentUser_api['getCurrentUserPreference'] !== 'function' ) {
            return;
        }

        this.currentUser_api.getCurrentUserPreference( {onResult: this.onUserPreference, delegate: this} );

    },

    onUserPreference: function( e ) {

        var result = e.getResult();
        var login_view_this = e.get( 'delegate' );
        if ( result.date_format ) {
            next( result );
        } else {
            login_view_this.user_preference_api.getUserPreferenceDefaultData( {
                onResult: function( userPD ) {
                    next( userPD.getResult() );
                }
            } );
        }

        function next( nextResult ) {
            LocalCacheData.loginUserPreference = nextResult;

            login_view_this.date_api.getTimeZoneOffset( {
                onResult: function( timeZoneRes ) {
                    login_view_this.date_api.getHours( timeZoneRes.getResult(), {
                        onResult: function( hoursRes ) {
                            var hoursResultData = hoursRes.getResult();

                            //Flex way, Need this in js? Let's see
                            if ( hoursResultData.indexOf( '-' ) > -1 ) {
                                hoursResultData = hoursResultData.replace( '-', '+' )
                            } else {
                                hoursResultData = hoursResultData.replace( '+', '-' )
                            }

                            LocalCacheData.loginUserPreference.time_zone_offset = hoursResultData;

                            login_view_this.user_preference_api.getOptions( 'moment_date_format', {
                                onResult: function( jsDateFormatRes ) {

                                    var jsDateFormatResultData = jsDateFormatRes.getResult();

                                    //For moment date parser
                                    LocalCacheData.loginUserPreference.js_date_format = jsDateFormatResultData;

                                    var date_format = LocalCacheData.loginUserPreference.date_format;

                                    LocalCacheData.loginUserPreference.date_format = LocalCacheData.loginUserPreference.js_date_format[date_format];

                                    ////For date picker
                                    //LocalCacheData.loginUserPreference.js_date_format_1 = jsDateFormatResultData;
                                    LocalCacheData.loginUserPreference.date_format_1 = Global.convertTojQueryFormat( date_format );
                                    LocalCacheData.loginUserPreference.time_format_1 = Global.convertTojQueryFormat( LocalCacheData.loginUserPreference.time_format );

                                    login_view_this.user_preference_api.getOptions( 'moment_time_format', {
                                        onResult: function( jsTimeFormatRes ) {

                                            var jsTimeFormatResultData = jsTimeFormatRes.getResult();

                                            LocalCacheData.loginUserPreference.js_time_format = jsTimeFormatResultData;

                                            LocalCacheData.setLoginUserPreference( LocalCacheData.loginUserPreference );

                                            login_view_this.permission_api.getPermission( {
                                                onResult: function( permissionRes ) {
                                                    LocalCacheData.setPermissionData( permissionRes.getResult() );

                                                    login_view_this.permission_api.getUniqueCountry( {
                                                        onResult: function( country_result ) {
                                                            LocalCacheData.setUniqueCountryArray( country_result.getResult() );
                                                            login_view_this.authentication_api.getCurrentCompany( {
                                                                onResult: function( current_company_result ) {
                                                                    var com_result = current_company_result.getResult();
                                                                    if ( com_result.is_setup_complete === '1' || com_result.is_setup_complete === 1 ) {
                                                                        com_result.is_setup_complete = true;
                                                                    } else {
                                                                        com_result.is_setup_complete = false;
                                                                    }
                                                                    LocalCacheData.setCurrentCompany( com_result );
                                                                    login_view_this.goToView();
                                                                    Debug.Text('Version: Client: '+APIGlobal.pre_login_data.application_build + " Server: "+com_result.application_build, 'LoginViewController.js', 'LoginViewController','onUserPreference:next',10);
                                                                    if ( APIGlobal.pre_login_data.application_build != com_result.application_build) {
                                                                        Debug.Text("Version mismatch on login: Reloading...", 'LoginViewController.js', 'LoginViewController','onUserPreference:next',10);
                                                                        window.location.reload(true);
                                                                    }
                                                                }
                                                            } );

                                                        }
                                                    } );

                                                }
                                            } );

                                        }
                                    } );

                                }
                            } );

                        }
                    } );

                }
            } );

//			  var jsTimeFormatRes = e.get('delegate').user_preference_api.getOptions('js_time_format',{async:false});

        }

    },

    goToView: function() {
        // TAlertManager.closeBrowserBanner();
        this.doing_login = false;
        // TopMenuManager.ribbon_view_controller = null;
        // TopMenuManager.ribbon_menus = null;
        // Global.topContainer().empty();
        LocalCacheData.currentShownContextMenuName = null;

        //Ensure that the language chosen at the login screen is passed in so that the user's country can be appended to create a proper locale.
        var result = this.authentication_api.getLocale( $( 'select[name="language"]' ).val(), {async: false} );
        var login_language = 'en_US';
        if ( result ) {
            login_language = result.getResult();
        }
        var message_id = UUID.guid();
        if ( LocalCacheData.getLoginData().locale != null && login_language !== LocalCacheData.getLoginData().locale ) {
            ProgressBar.showProgressBar(message_id);
            ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

            Global.setLanguageCookie(login_language);
            LocalCacheData.setI18nDic( null );
            setTimeout( function() {
                window.location.reload( true );
            }, 5000 );
        }
        IndexViewController.instance.router.removeCurrentView();
        // var target_view = $.cookie( 'PreviousSessionType' );
        // if ( target_view && !$.cookie( 'PreviousSessionID' ) ) {
        //     TopMenuManager.goToView( target_view );
        //     $.cookie( 'PreviousSessionType', null, {
        //         expires: 30,
        //         path: LocalCacheData.cookie_path,
        //         domain: Global.getHost()
        //     } );
        // } else {
        //     if (Global.getDeepLink() != false){
        //         TopMenuManager.goToView(Global.getDeepLink());
        //     }else if ( LocalCacheData.getLoginUserPreference().default_login_screen ) {
        //         TopMenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
        //     } else {
        //         TopMenuManager.goToView( 'Home' );
        //     }
        // }

        // if ( !LocalCacheData.getCurrentCompany().is_setup_complete ) {
        //     IndexViewController.openWizard( 'QuickStartWizard' );
        // }
        Global.setURLToBrowser( Global.getBaseURL() + '#!m=QuickPunch' );
        var current_company = LocalCacheData.getCurrentCompany();
        if ( LocalCacheData && current_company ) {
            Global.setAnalyticDimensions( LocalCacheData.getPunchLoginUser().first_name + ' (' + LocalCacheData.getPunchLoginUser().id + ')', current_company.name );
        }
    },

    setErrorTips: function(result) {
        this.clearErrorTips(true );
        var error_list = result.getDetails() ? result.getDetails()[0] : {};
        if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
            error_list = error_list.error;
        }
        for ( var key in error_list ) {
            if ( !error_list.hasOwnProperty( key ) ) {
                continue;
            }
            var field_obj;
            if ( this.$('input[name="' + key + '"]')[0] ) {
                field_obj = this.$('input[name="' + key + '"]');
            } else if ( this.$('select[name="' + key + '"]')[0] ) {
                field_obj = this.$('select[name="' + key + '"]');
            }
            if ( field_obj ) {
                field_obj.addClass('error-tip');
                var error_string;
                if ( _.isArray( error_list[key] ) ) {
                    error_string = error_list[key][0];
                } else {
                    error_string = error_list[key];
                }
                field_obj.attr('data-original-title', error_string);
                // field_obj.tooltip({
                //     'title': error_string,
                // })
                field_obj.tooltip('show');
                this.edit_view_error_ui_dic[key] = field_obj;
            }
            // if ( field_obj ) {
            // 	$('html, body').animate({
            // 		scrollTop: field_obj.offset().top
            // 	}, 2000);
            // }
        }
        if ( _.size( this.edit_view_error_ui_dic ) > 0 ) {
            this.login_btn.removeAttr('disabled');
            _.min( this.edit_view_error_ui_dic, function ( item ) {
                if ( item.attr('tabindex') ) {
                    return parseInt(item.attr('tabindex'));
                }
            } ).focus();
        }
    },

    clearErrorTips: function( clear_all, destroy ) {
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

    setLanguageSourceData: function( field, source_data, set_empty ) {
        var $this = this;
        var field_selector = 'select[name="' + field + '"]';
        if ( this.$( field_selector ) && this.$( field_selector )[0] ) {
            this.$( field_selector ).empty();
        } else {
            return;
        }
        if ( !source_data ) {
            source_data = LocalCacheData.getLoginData().language_options;
            source_data = Global.removeSortPrefix( (LocalCacheData.getLoginData().language_options) )
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
        }
        if ( _.size( source_data ) > 0 ) {
            $.each( source_data, function ( value, label ) {
                $this.$( field_selector )
                    .append($("<option></option>")
                        .attr("value", value)
                        .text( label ));
                if ( LocalCacheData.getLoginData().language == value ) {
                    $this.$( field_selector ).val( value );
                }
            });
        }
        // $this.$( field_selector ).selectpicker();
    },

    onLanguageChange: function ( e ) {
        Global.setLanguageCookie( $(e.target).val() );
        LocalCacheData.setI18nDic( null );
        var message_id = UUID.guid();
        ProgressBar.showProgressBar( message_id );
        ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

        setTimeout( function() {
            window.location.reload( true );
        }, 2000 );
    }
});


