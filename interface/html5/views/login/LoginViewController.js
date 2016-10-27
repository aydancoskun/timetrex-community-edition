LoginViewController = BaseViewController.extend( {

	el: '#loginViewContainer', //Must set el here and can only set string, so events can work
	authentication_api: null,
	currentUser_api: null,
	currency_api: null,
	user_preference_api: null,
	is_login: true,
	date_api: null,
	permission_api: null,

	doing_login: false,

	lan_selector: null,

	initialize: function() {
		this._super( 'initialize' );
		var $this = this;
		this.authentication_api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
		this.currentUser_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
		this.currency_api = new (APIFactory.getAPIClass( 'APICurrency' ))();
		this.user_preference_api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.permission_api = new (APIFactory.getAPIClass( 'APIPermission' ))();
		this.viewId = 'LoginView';
		Global.topContainer().css( 'display', 'none' );
		Global.bottomContainer().css( 'display', 'none' );
		Global.contentContainer().removeClass( 'content-container-after-login' );
		Global.topContainer().removeClass( 'top-container-after-login' );

		var login_data = LocalCacheData.getLoginData();

		//Clean cache that saved in some views
		this.cleanNecessaryCache();
		if ( $.cookie( 'SessionID' ) && $.cookie( 'SessionID' ).length > 0 && LocalCacheData.getLoginData().is_logged_in ) {
			var timeout_count = 0;
			$( this.el ).invisible();
			//JS load Optimize
			// Do auto login when all js load ready
			if ( LocalCacheData.loadViewRequiredJSReady ) {
				$this.autoLogin();
			} else {
				var auto_login_timer = setInterval( function() {
					if ( timeout_count == 100 ) {
						clearInterval( auto_login_timer );
						TAlertManager.showAlert( $.i18n._( 'Resource load error!' ) );
						return;
					}
					timeout_count = timeout_count + 1;
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						$this.autoLogin();
						clearInterval( auto_login_timer );
					}
				}, 600 );
			}
		} else {
			$( this.el ).visible();
			this.render();
		}

		if ( LocalCacheData.notification_bar ) {
			LocalCacheData.notification_bar.remove();
		}

		Global.setAnalyticDimensions();
	},

	default: {
		is_login: false
	},

	events: {
		'click #quick_punch': 'onQuickPunchClick',
		'click #login_btn': 'onLoginBtnClick',
		'click #forgot_password': 'forgotPasswordClick',
		'click #appTypeLogo': 'onAppTypeClick',
		'click #companyLogo': 'onAppTypeClick',
		'click #powered_by': 'onAppTypeClick'
	},

	onAppTypeClick: function() {
		window.open( "http://" + LocalCacheData.loginData.organization_url );
	},

	onQuickPunchClick: function() {
		window.open( ServiceCaller.rootURL + LocalCacheData.loginData.base_url + 'quick_punch/QuickPunchLogin.php' );
	},

	onLoginBtnClick: function() {
		var user_name = $( '#user_name' ).val();
		var password = $( '#password' ).val();
		var $this = this;

		if ( !this.doing_login ) {
			this.doing_login = true;
		} else {
			return;
		}
		// Don't check copy right for now
		//var copy_right_text = $( '#login_copy_right_info' ).text();
		//var xhr = $.ajax( {
		//	type: "HEAD",
		//	url: ServiceCaller.login_page_powered_by_logo,
		//	success: function() {
		//		var _0xee93 = ["\x6F\x6E\x6C\x6F\x61\x64", "\x74\x6F\x74\x61\x6C", "\x43\x6F\x70\x79\x72\x69\x67\x68\x74\x20", "\x69\x6E\x64\x65\x78\x4F\x66", "\x6F\x72\x67\x61\x6E\x69\x7A\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x6C\x6F\x67\x69\x6E\x44\x61\x74\x61", "\x41\x6C\x6C\x20\x52\x69\x67\x68\x74\x73\x20\x52\x65\x73\x65\x72\x76\x65\x64", "\x45\x52\x52\x4F\x52\x3A\x20\x54\x68\x69\x73\x20\x69\x6E\x73\x74\x61\x6C\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20", "\x61\x70\x70\x6C\x69\x63\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x20\x69\x73\x20\x69\x6E\x20\x76\x69\x6F\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20\x74\x68\x65\x20\x6C\x69\x63\x65\x6E\x73\x65\x20\x61\x67\x72\x65\x65\x6D\x65\x6E\x74\x21", "\x73\x68\x6F\x77\x41\x6C\x65\x72\x74", "\x67\x65\x74\x52\x65\x73\x70\x6f\x6e\x73\x65\x48\x65\x61\x64\x65\x72", "\x43\x6f\x6e\x74\x65\x6e\x74\x2d\x4c\x65\x6e\x67\x74\x68"];
		//		var s = xhr[_0xee93[11]]( _0xee93[12] );
		//		if ( (s && parseInt( s ) !== 7793) || copy_right_text[_0xee93[3]]( _0xee93[2] ) !== 2 || copy_right_text[_0xee93[3]]( LocalCacheData[_0xee93[5]][_0xee93[4]] ) !== 19 ) { Global.sendErrorReport( (_0xee93[7] + LocalCacheData[_0xee93[5]][_0xee93[8]] + _0xee93[9] + 'c: ' + s + ' ' + copy_right_text[_0xee93[3]]( _0xee93[2] ) + ' ' + copy_right_text[_0xee93[3]]( LocalCacheData[_0xee93[5]][_0xee93[4]] ) ), ServiceCaller.rootURL, '', '', '' );}
		//	}
		//} );
		this.authentication_api.login( user_name, password, {
			onResult: function( e ) {
				if ( LocalCacheData.loadViewRequiredJSReady ) {
					$this.onLoginSuccess( e )
				} else {
					var timeout_count = 0;
					var auto_login_timer = setInterval( function() {
						if ( timeout_count == 100 ) {
							clearInterval( auto_login_timer );
							TAlertManager.showAlert( $.i18n._( 'Resource load error!' ) );
							return;
						}
						timeout_count = timeout_count + 1;
						if ( LocalCacheData.loadViewRequiredJSReady ) {
							$this.onLoginSuccess( e );
							clearInterval( auto_login_timer );
						}
					}, 600 );
				}
			}, delegate: this
		} );

	},

	cleanNecessaryCache: function() {
		LocalCacheData.last_timesheet_selected_user = null;
		LocalCacheData.last_timesheet_selected_date = null;
		//JS load Optimize
		if ( LocalCacheData.loadViewRequiredJSReady ) {
			ALayoutCache.layout_dic = {};
		}
		LocalCacheData.view_layout_cache = {};
		LocalCacheData.result_cache = {};
		if ( LocalCacheData.current_open_wizard_controller ) {
			LocalCacheData.current_open_wizard_controller.onCloseClick();
			LocalCacheData.current_open_wizard_controller = null;
		}
		Global.cleanViewTab();
	},

	onLoginSuccess: function( e, session_id ) {

		var result;
		var $this = this;

		if ( !session_id ) {
			result = e.getResult();
		} else {
			result = session_id;
		}

		var user_name = $( '#user_name' );
		var password = $( '#password' );

		if ( e && !e.isValid() ) {
			LocalCacheData.setSessionID( '' );
			$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );

			if ( e.getDetails()[0].hasOwnProperty( 'password' ) ) {
				IndexViewController.openWizard( 'ResetPasswordWizard', {
					user_name: user_name.val(),
					message: e.getDetailsAsString()
				}, function() {
					TAlertManager.showAlert( $.i18n._( 'Password has been changed successfully, you may now login' ), '', function() {
						password.focus();
					} );
				} );
			} else {
				TAlertManager.showAlert( e.getDetailsAsString(), 'Error', function() {
					password.focus();
				} );
			}

			$this.doing_login = false;
		} else {

			ServiceCaller.cancelAllError = false;
			LocalCacheData.setSessionID( result );
			$.cookie( 'SessionID', result, {expires: 30, path: LocalCacheData.cookie_path} );

//			$this.authentication_api.isApplicationBranded( {onResult: $this.onIsApplicationBranded, delegate: $this} );
			$this.authentication_api.getApplicationName( {onResult: $this.onGetApplicationName, delegate: $this} );
			$this.currentUser_api.getCurrentUser( {onResult: $this.onGetCurrentUser, delegate: $this} ); //Get more in result handler
		}

	},

	onGetApplicationName: function( e ) {
		LocalCacheData.setApplicationName( e.getResult() );
	},

//	onGetCurrentCompany: function( e ) {
//
//	},
//
//	onGetOrganizationName: function( e ) {
//		var ozName = e.getResult();
//		var date = new Date();
//		e.get( 'delegate' ).authentication_api.getOrganizationURL( {onResult: function( e1 ) {
//		}, delegate: e.get( 'delegate' )} );
//	},

//	onIsApplicationBranded: function( e ) {
//
//		LocalCacheData.setIsApplicationBranded( e.getResult() );
//	},

	onGetCurrentUser: function( e ) {
		LocalCacheData.setLoginUser( e.getResult() );

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_id = LocalCacheData.loginUser.id;
		filter.filter_columns = {};
		filter.filter_columns.symbol = true;

		e.get( 'delegate' ).currency_api.getCurrency( filter, {
			onResult: function( e1 ) {

				var result = e1.getResult();

				if ( Global.isArrayAndHasItems( result ) && result[0].symbol ) {
					LocalCacheData.setCurrentCurrencySymbol( result[0].symbol )
				} else {
					LocalCacheData.setCurrentCurrencySymbol( '$' );
				}

			}, delegate: e.get( 'delegate' )
		} );

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

		TAlertManager.closeBrowserBanner();
		this.doing_login = false;

		TopMenuManager.ribbon_view_controller = null;
		TopMenuManager.ribbon_menus = null;
		Global.topContainer().empty();
		LocalCacheData.currentShownContextMenuName = null;

		var result = this.authentication_api.getLocale( {async: false} );
		var login_lan = 'en_US';
		if ( result ) {
			login_lan = result.getResult();
		}

		if ( login_lan !== LocalCacheData.getLoginData().locale ) {
			ProgressBar.showProgressBar();
			ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );
			$.cookie( 'language', login_lan, {expires: 10000, path: LocalCacheData.loginData.base_url} );
			LocalCacheData.setI18nDic( null );
			setTimeout( function() {
				window.location.reload( true );
			}, 5000 );
		}
		IndexViewController.instance.router.removeCurrentView();
		var target_view = $.cookie( 'PreviousSessionType' );
		if ( target_view && !$.cookie( 'PreviousSessionID' ) ) {
			TopMenuManager.goToView( target_view );
			$.cookie( 'PreviousSessionType', null, {
				expires: 30,
				path: LocalCacheData.cookie_path,
				domain: Global.getHost()
			} );
		} else {
			if ( LocalCacheData.getLoginUserPreference().default_login_screen ) {
				TopMenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
			} else {
				TopMenuManager.goToView( 'Home' );
			}
		}

		if ( !LocalCacheData.getCurrentCompany().is_setup_complete ) {
			IndexViewController.openWizard( 'QuickStartWizard' );
		}
		var current_company = LocalCacheData.getCurrentCompany();
		if ( LocalCacheData && current_company ) {
			Global.setAnalyticDimensions( LocalCacheData.getLoginUser().first_name + ' (' + LocalCacheData.getLoginUser().id + ')', current_company.name );
		}
	},

	forgotPasswordClick: function() {
		window.open( ServiceCaller.rootURL + LocalCacheData.loginData.base_url + 'ForgotPassword.php' );
	},

	autoLogin: function() {
		// Error: TypeError: e is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 154
		if ( $.cookie( 'SessionID' ) ) {
			this.doing_login = true;
			this.onLoginSuccess( null, $.cookie( 'SessionID' ) );
		} else {
			$( this.el ).visible();
			this.render();
		}
	},

	render: function() {

		var $this = this;

		LocalCacheData.setSessionID( '' );
		$( '#login_copy_right_info' ).hide();
		$( '#powered_by' ).hide();

		var passwordInput = $( '#password' ).TTextInput( {width: 151} );
		var username_input = $( '#user_name' ).TTextInput( {width: 151} );
		var error_string_td = $( '.error-info' );

		if ( LocalCacheData.login_error_string ) {
//			error_string_td.html( LocalCacheData.login_error_string );
			error_string_td.text( LocalCacheData.login_error_string );
			LocalCacheData.login_error_string = '';
		} else {
			error_string_td.text( '' );
		}

		username_input.focus();

		passwordInput.unbind( 'keydown' ).bind( 'keydown', function( e ) {

			if ( e.keyCode === 13 ) {
				$this.onLoginBtnClick();
			}
		} );

		$( "#versionNumber" ).html( "v" + APIGlobal.pre_login_data.application_build );

		$( '#appTypeLogo' ).css( 'opacity', 0 );

		//community edition
		var is_seo = false;

		var url = 'theme/' + Global.theme;

		if ( Global.url_offset ) {
			url = Global.url_offset + url;
		}

		if ( LocalCacheData.productEditionId > 10 && LocalCacheData.appType === true ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/od.png' );
		} else if ( LocalCacheData.productEditionId === 15 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/beo.png' );
		} else if ( LocalCacheData.productEditionId === 20 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/peo.png' );
		} else if ( LocalCacheData.productEditionId === 25 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/eeo.png' );
		} else {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/seo.png' );
			is_seo = true;
		}

		var quick_punch_link = $( this.el ).find( '#quick_punch' );
		if ( LocalCacheData.productEditionId > 10 ) {
			quick_punch_link.show();
		} else {
			quick_punch_link.hide();
		}

		$( '#appTypeLogo' ).load( function() {
			$( this ).animate( {
				opacity: 1
			}, 100 );
		} );

		$( '#companyLogo' ).hide();
		$( '#companyLogo' ).css( 'opacity', 0 );
		$( '#companyLogo' ).attr( 'src', ServiceCaller.mainCompanyLogo );

		$( '#companyLogo' ).load( function() {

			var ratio = 78 / $( this ).height();

			if ( $( this ).height() > 78 ) {
				$( this ).css( 'height', 78 );

				if ( $( this ).width > 286 ) {
					$( this ).css( 'width', 286 );
				}
			}

			if ( $( this ).width > 286 ) {
				$( this ).css( 'width', 286 );
			}
			$( '#companyLogo' ).show();

			$( this ).animate( {
				opacity: 1
			}, 100 );
		} );

		if ( LocalCacheData.loginData.powered_by_logo_enabled ) {
			$( '#powered_by' ).show();
			$( '#powered_by' ).attr( 'src', ServiceCaller.login_page_powered_by_logo );
			$( '#powered_by' ).attr( 'alt', LocalCacheData.loginData.application_name + ' Workforce Management' );

			var powered_by_link = $( '<a target="_blank" href="http://' + LocalCacheData.getLoginData().organization_url + '"></a>' );
			powered_by_link.append( $( '#powered_by' ) );
			powered_by_link.addClass( 'powered-by-img-seo' );
			$( '#login_copy_right_info' ).html( $( '#copy_right_info_1' ).html() );

			$( '#login_copy_right_info' ).show();
			powered_by_link.insertAfter( $( $this.el ) );
			$( '#login_copy_right_info' ).insertAfter( $( $this.el ) );
		}

		if ( LocalCacheData.productEditionId === 10 ) {
			$( '#social_div' ).insertAfter( $( $this.el ) );
			$( '#social_div' ).show();
			$( '#social_div' ).find( '.facebook-img' ).attr( 'src', 'theme/default/images/facebook_button.jpg' );
			$( '#social_div' ).find( '.twitter-img' ).attr( 'src', 'theme/default/images/twitter_button.jpg' );
		} else {
			$( '#social_div' ).hide();
		}

		var footer_right_html = LocalCacheData.getLoginData().footer_right_html;
		var footer_left_html = LocalCacheData.getLoginData().footer_left_html;

		if ( footer_right_html && $.type( footer_right_html ) === 'string' ) {
			footer_right_html = $( footer_right_html );
			footer_right_html.addClass( 'foot-right-html' );

			footer_right_html.insertAfter( $( $this.el ) );
		}

		if ( footer_left_html && $.type( footer_left_html ) === 'string' ) {
			footer_left_html = $( footer_left_html );
			footer_left_html.addClass( 'foot-left-html' );

			footer_left_html.insertAfter( $( $this.el ) );
		}

		this.lan_selector = $( '.language-selector' );
		this.lan_selector.TComboBox();
		var array = Global.buildRecordArray( (LocalCacheData.getLoginData().language_options) );

		this.lan_selector.setSourceData( array );

		this.lan_selector.setValue( LocalCacheData.getLoginData().language );

		this.lan_selector.bind( 'formItemChange', function() {
			$.cookie( 'language', $this.lan_selector.getValue(), {
				expires: 10000,
				path: LocalCacheData.loginData.base_url
			} );

			LocalCacheData.setI18nDic( null );

			ProgressBar.showProgressBar();
			ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

			setTimeout( function() {
				window.location.reload( true );
			}, 2000 );

		} );

		if ( LocalCacheData.all_url_args.user_name ) {
			username_input.val( LocalCacheData.all_url_args.user_name )
		}

		if ( LocalCacheData.all_url_args.password ) {
			passwordInput.val( LocalCacheData.all_url_args.password )
		}

		$( this.el ).attr( 'init_complete', true )

	},

	cleanWhenUnloadView: function( callBack ) {
		$( '#loginViewContainer' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );
	}

} );

LoginViewController.loadView = function() {


	//Load login.css on index.php file since it's the first view user see, we don't want any flash

	Global.loadViewSource( 'Login', 'LoginView.html', function( result ) {

		var args = {
			secure_login: $.i18n._( 'Secure Login' ),
			user_name: $.i18n._( 'User Name' ),
			password: $.i18n._( 'Password' ),
			forgot_your_password: $.i18n._( 'Forgot Your Password' ),
			quick_punch: $.i18n._( 'Quick Punch' ),
			login: $.i18n._( 'Login' ),
			language: $.i18n._( 'Language' )
		};
		var template = _.template( result, args );
		Global.contentContainer().html( template );

	} );

}
