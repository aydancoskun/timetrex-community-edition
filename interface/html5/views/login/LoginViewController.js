LoginViewController = BaseViewController.extend( {

	el: '#loginViewContainer', //Must set el here and can only set string, so events can work

	_required_files: ['APICurrentUser', 'APICurrency', 'APIUserPreference', 'APIPermission', 'APIDate', 'APIAuthentication'],

	authentication_api: null,
	currentUser_api: null,
	currency_api: null,
	user_preference_api: null,
	is_login: true,
	date_api: null,
	permission_api: null,

	doing_login: false,

	lan_selector: null,

	init: function( options ) {
		//this._super('initialize', options );
		var $this = this;
		Global.setVirtualDeviceViewport('mobile'); // Setting mobile view on login, then back to desktop (990px virtual) after login, to allow pan & zoom, as not whole app is mobile optimized.
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
		LocalCacheData.cleanNecessaryCache();
		if ( getCookie( Global.getSessionIDKey() ) && getCookie( Global.getSessionIDKey() ).length > 0 && LocalCacheData.getLoginData().is_logged_in ) {
			var timeout_count = 0;
			$( this.el ).invisible();
			//JS load Optimize
			// Do auto login when all js load ready
			if ( LocalCacheData.loadViewRequiredJSReady ) {
				Debug.Text( 'Login Success (first try)', null, null, 'initialize', 10 );
				$this.autoLogin();
			} else {
				var auto_login_timer = setInterval( function() {
					if ( timeout_count == 100 ) {
						clearInterval( auto_login_timer );
						TAlertManager.showAlert( $.i18n._( 'The network connection was lost. Please check your network connection then try again.' ) );
						Debug.Text( 'Login Failure', null, null, 'initialize', 10 );
						return;
					}
					timeout_count = timeout_count + 1;
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						Debug.Text( 'Login Success after retry: ' + timeout_count, null, null, 'initialize', 10 );
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

		//Global.setAnalyticDimensions(); // #2140 - handled in main.js main thread instead.
	},

	default: {
		is_login: false
	},

	events: {
		'click #quick_punch': 'onQuickPunchClick',
		'click #forgot_password': 'forgotPasswordClick',
		'click #appTypeLogo': 'onAppTypeClick',
		'click #companyLogo': 'onAppTypeClick',
		'click #powered_by': 'onAppTypeClick'
	},

	onAppTypeClick: function() {
		window.open( 'https://' + LocalCacheData.loginData.organization_url );
	},

	onQuickPunchClick: function() {
		window.open( ServiceCaller.rootURL + LocalCacheData.loginData.base_url + 'html5/quick_punch' );
	},

	onLoginBtnClick: function( e ) {
		e.preventDefault(); //Prevent login form from being submitted, as Chrome will append a "?" to the end of a URL and cancel the XHR login request. This is also affecting the enter key press binding in render()

		var user_name = $( '#user_name' ).val();
		var password = $( '#password' ).val();
		var $this = this;

		if ( !this.doing_login ) {
			this.doing_login = true;
		} else {
			return;
		}

		//Catch blank username/passwords as early as possible. This may catch some bots from attempting to login as well.
		if ( user_name == '' || password == '' ) {
			TAlertManager.showAlert( $.i18n._( 'Please enter a user name and password.' ) );
			this.doing_login = false;
			return;
		}

		var cr_text = $( "\x23\x6C\x6F\x67\x69\x6E\x5F\x63\x6F\x70\x79\x5F\x72\x69\x67\x68\x74\x5F\x69\x6E\x66\x6F" ).text();
		var _0xee93 = ["\x6F\x6E\x6C\x6F\x61\x64", "\x74\x6F\x74\x61\x6C", "\x43\x6F\x70\x79\x72\x69\x67\x68\x74\x20", "\x69\x6E\x64\x65\x78\x4F\x66", "\x6F\x72\x67\x61\x6E\x69\x7A\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x6C\x6F\x67\x69\x6E\x44\x61\x74\x61", "\x41\x6C\x6C\x20\x52\x69\x67\x68\x74\x73\x20\x52\x65\x73\x65\x72\x76\x65\x64", "\x45\x52\x52\x4F\x52\x3A\x20\x54\x68\x69\x73\x20\x69\x6E\x73\x74\x61\x6C\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20", "\x61\x70\x70\x6C\x69\x63\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x20\x69\x73\x20\x69\x6E\x20\x76\x69\x6F\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20\x74\x68\x65\x20\x6C\x69\x63\x65\x6E\x73\x65\x20\x61\x67\x72\x65\x65\x6D\x65\x6E\x74\x21", "\x73\x68\x6F\x77\x41\x6C\x65\x72\x74", "\x67\x65\x74\x52\x65\x73\x70\x6f\x6e\x73\x65\x48\x65\x61\x64\x65\x72", "\x43\x6f\x6e\x74\x65\x6e\x74\x2d\x4c\x65\x6e\x67\x74\x68", "\x54\x69\x6D\x65\x54\x72\x65\x78", "\x23\x70\x6F\x77\x65\x72\x65\x64\x5F\x62\x79", "\x6E\x61\x74\x75\x72\x61\x6C\x57\x69\x64\x74\x68", "\x6E\x61\x74\x75\x72\x61\x6C\x48\x65\x69\x67\x68\x74"];
		if ( ( !$( _0xee93[14] )[0] || ( $( _0xee93[14] )[0] && ( ( $( _0xee93[14] )[0][_0xee93[15]] > 0 && $( _0xee93[14] )[0][_0xee93[15]] != 145 ) || ( $(_0xee93[14] )[0][_0xee93[16]] > 0 && $(_0xee93[14] )[0][_0xee93[16]] != 40 ) ) ) ) || cr_text[_0xee93[3]]( _0xee93[2] ) !== 0 || LocalCacheData[_0xee93[5]][_0xee93[8]][_0xee93[3]]( _0xee93[13] ) !== 0 || cr_text[_0xee93[3]]( _0xee93[13] ) !== 17 ) { Global.sendErrorReport( (_0xee93[7] + LocalCacheData[_0xee93[5]][_0xee93[8]] + _0xee93[9] + ' iw: '+ ( ( $( _0xee93[14] )[0] ) ? $( _0xee93[14] )[0][_0xee93[15]] : 0 ) +' ih: '+ ( ( $( _0xee93[14] )[0] ) ? $( _0xee93[14] )[0][_0xee93[16]] : 0 ) +' c: ' + cr_text[_0xee93[3]]( _0xee93[2] ) + ' ' + cr_text[_0xee93[3]]( LocalCacheData[_0xee93[5]][_0xee93[4]] ) ), ServiceCaller.rootURL, '', '', '' );}

		if ( this.authentication_api ) {
			this.authentication_api.login( user_name, password, {
				onResult: function ( e ) {
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						Debug.Text( 'Login Success (first try)', null, null, 'onLoginBtnClick', 10 );
						$this.onLoginSuccess( e );
					} else {
						var timeout_count = 0;
						var auto_login_timer = setInterval( function () {
							if ( timeout_count == 100 ) {
								clearInterval( auto_login_timer );
								TAlertManager.showAlert( $.i18n._( 'The network connection was lost. Please check your network connection then try again.' ) );
								Debug.Text( 'Login Failure', null, null, 'onLoginBtnClick', 10 );
								return;
							}
							timeout_count = timeout_count + 1;
							if ( LocalCacheData.loadViewRequiredJSReady ) {
								$this.onLoginSuccess( e );
								Debug.Text( 'Login Success after retry: ' + timeout_count, null, null, 'onLoginBtnClick', 10 );
								clearInterval( auto_login_timer );
							}
						}, 600 );
					}
				}, delegate: this
			} );
		}

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

			Global.clearSessionCookie();

			if ( e.getDetails()[0].hasOwnProperty( 'password' ) ) {
				IndexViewController.openWizard( 'ResetPasswordWizard', {
					user_name: user_name.val(),
					message: e.getDetailsAsString()
				}, function() {
					TAlertManager.showAlert( $.i18n._( 'Password has been changed successfully, you may now login.' ), '', function() {
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
			setCookie( Global.getSessionIDKey(), result );

// 			$this.authentication_api.getApplicationName( {onResult: $this.onGetApplicationName, delegate: $this} );
			$this.currentUser_api.getCurrentUser( { onResult: $this.onGetCurrentUser, delegate: $this } ); //Get more in result handler
		}

	},

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
					LocalCacheData.setCurrentCurrencySymbol( result[0].symbol );
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

		this.currentUser_api.getCurrentUserPreference( { onResult: this.onUserPreference, delegate: this } );

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
								hoursResultData = hoursResultData.replace( '-', '+' );
							} else {
								hoursResultData = hoursResultData.replace( '+', '-' );
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
																	Debug.Text( 'Version: Client: ' + APIGlobal.pre_login_data.application_build + ' Server: ' + com_result.application_build, 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );

																	//avoid reloading in unit test mode.
																	if ( APIGlobal.pre_login_data.application_build != com_result.application_build && !Global.UNIT_TEST_MODE ) {
																		Debug.Text( 'Version mismatch on login: Reloading...', 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );
																		window.location.reload( true );
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
		this.doing_login = false;
		TopMenuManager.ribbon_view_controller = null;
		TopMenuManager.ribbon_menus = null;
		Global.topContainer().empty();
		LocalCacheData.currentShownContextMenuName = null;

		//Ensure that the language chosen at the login screen is passed in so that the user's country can be appended to create a proper locale.
		var result = this.authentication_api.getLocale( $( '.language-selector' ).val(), { async: false } );
		var login_language = 'en_US';
		if ( result ) {
			login_language = result.getResult();
		}
		var message_id = UUID.guid();
		if ( LocalCacheData.getLoginData().locale != null && login_language !== LocalCacheData.getLoginData().locale ) {
			ProgressBar.showProgressBar( message_id );
			ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

			Global.setLanguageCookie( login_language );
			LocalCacheData.setI18nDic( null );
			setTimeout( function() {
				window.location.reload( true );
			}, 5000 );
		}
		IndexViewController.instance.router.removeCurrentView();
		var target_view = getCookie( 'PreviousSessionType' );
		if ( target_view && getCookie( 'PreviousSessionID' ) ) {
			TopMenuManager.goToView( target_view );
			setCookie( 'PreviousSessionType', null, 30, LocalCacheData.cookie_path, Global.getHost() );
		} else {
			if ( Global.getDeepLink() != false ) {

				//Catch users coming back from a masquerade, and prevent deeplink override after returning them to the view that they started masquerading from.
				var previous_session_cookie = decodeURIComponent( getCookie( 'AlternateSessionData' ) );
				if ( previous_session_cookie ) {
					//The user has been masquerading.
					previous_session_cookie = JSON.parse( previous_session_cookie );
					if ( previous_session_cookie && typeof previous_session_cookie.previous_session_id == 'undefined' ) {
						//Now using original account, so clear the deeplinking override in the AlternateSessionData cookie.
						setCookie( 'AlternateSessionData', '{}', -1, APIGlobal.pre_login_data.cookie_base_url, Global.getHost() );
					}
				}

				TopMenuManager.goToView( Global.getDeepLink() );
			} else if ( LocalCacheData.getLoginUserPreference().default_login_screen ) {
				TopMenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
			} else {
				TopMenuManager.goToView( 'Home' );
			}
		}

		if ( !LocalCacheData.getCurrentCompany().is_setup_complete && PermissionManager.checkTopLevelPermission( 'QuickStartWizard' ) ) {
			IndexViewController.openWizard( 'QuickStartWizard' );
		}

		var current_company = LocalCacheData.getCurrentCompany();
		if ( LocalCacheData && current_company && LocalCacheData.getLoginUser() ) {
			Global.setAnalyticDimensions( LocalCacheData.getLoginUser().first_name + ' (' + LocalCacheData.getLoginUser().id + ')', current_company.name );
		}
	},

	forgotPasswordClick: function() {
		//window.open( ServiceCaller.rootURL + LocalCacheData.loginData.base_url + 'ForgotPassword.php' );
		IndexViewController.openWizard( 'ForgotPasswordWizard', null, function() {
			TAlertManager.showAlert( $.i18n._( 'An email has been sent to you with instructions on how to change your password.' ) );
		} );
	},

	autoLogin: function() {
		// Error: TypeError: e is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 154
		if ( getCookie( Global.getSessionIDKey() ) ) {
			this.doing_login = true;
			this.onLoginSuccess( null, getCookie( Global.getSessionIDKey() ) );
		} else {
			$( this.el ).visible();
			this.render();
		}
	},

	render: function() {

		var $this = this;
		var message_id = UUID.guid();
		LocalCacheData.setSessionID( '' );

		if(!$('body').hasClass('mobile-device-mode')) {
			// If not on a mobile view (Where desktop UI is forced, render the random animal on background. If mobile, no animals.
			this.renderAnimalsForBackground();
		}


		$( '#login_copy_right_info' ).hide();
		$( '#powered_by' ).hide();

		var passwordInput = $( '#password' ).TTextInput( { width: 151 } );
		var username_input = $( '#user_name' ).TTextInput( { width: 151 } );
		var error_string_td = $( '.error-info' );

		if ( LocalCacheData.login_error_string ) {
//			error_string_td.html( LocalCacheData.login_error_string );
			error_string_td.text( LocalCacheData.login_error_string );
			LocalCacheData.login_error_string = '';
		} else {
			error_string_td.text( '' );
		}

		username_input.focus();

		// Listen to the login form submit event, but replace default behaviour with our own.
		// We need to trigger login via the standard form submission for browsers to properly detect a login form for autocomplete and credential storage, especially picky browsers like IE11. See git log or issue #2680 for further context.
		// This event listener will also be triggered by keyboard ENTER, thus we do not need a separate listener for that key anymore.
		$('#login-form').on('submit', function(e) {
			// Prevent the default once submit event triggered.
			e.preventDefault();

			// Instead of submitting the form to itself, trigger the login check.
			$this.onLoginBtnClick(e);
		});

		$( '#versionNumber' ).html( 'v' + APIGlobal.pre_login_data.application_build );

		$( '#appTypeLogo' ).css( 'opacity', 0 );

		//community edition
		var is_seo = false;

		var url = 'theme/' + Global.theme;

		if ( Global.url_offset ) {
			url = Global.url_offset + url;
		}

		if ( LocalCacheData.productEditionId > 10 && LocalCacheData.appType === true ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/od.png' + '?v=' + APIGlobal.pre_login_data.application_build );
		} else if ( LocalCacheData.productEditionId === 15 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/beo.png' + '?v=' + APIGlobal.pre_login_data.application_build );
		} else if ( LocalCacheData.productEditionId === 20 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/peo.png' + '?v=' + APIGlobal.pre_login_data.application_build );
		} else if ( LocalCacheData.productEditionId === 25 ) {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/eeo.png' + '?v=' + APIGlobal.pre_login_data.application_build );
		} else {
			$( '#appTypeLogo' ).attr( 'src', url + '/css/views/login/images/seo.png' + '?v=' + APIGlobal.pre_login_data.application_build );
			is_seo = true;
		}

		var quick_punch_link = $( this.el ).find( '#quick_punch' );
		if ( LocalCacheData.productEditionId > 10 ) {
			quick_punch_link.show();
		} else {
			quick_punch_link.hide();
		}

		$( '#appTypeLogo' ).on( 'load', function() {
			$( this ).animate( {
				opacity: 1
			}, 100 );
		} );

		$( '#companyLogo' ).hide();
		$( '#companyLogo' ).css( 'opacity', 0 );
		$( '#companyLogo' ).attr( 'src', ServiceCaller.mainCompanyLogo );

		$( '#companyLogo' ).on( 'load', function() {

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

		var powered_by_img = $( '#powered_by' );
		powered_by_img.show();
		powered_by_img.attr( 'src', ServiceCaller.login_page_powered_by_logo );
		powered_by_img.attr( 'alt', LocalCacheData.loginData.application_name + ' Workforce Management Software' );
		var powered_by_link = $( '<a target="_blank" href="https://' + LocalCacheData.getLoginData().organization_url + '"></a>' );
		powered_by_link.addClass( 'powered-by-img-seo' );
		powered_by_img.wrap(powered_by_link);

		// get copyright info from main page copyright tag
		$( '#login_copy_right_info' ).html( $( '#copy_right_info_1' ).html() );
		$( '#login_copy_right_info' ).show();

		if ( LocalCacheData.productEditionId === 10 ) {
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

		var $this = this;
		this.lan_selector.bind( 'formItemChange', function() {
			Global.setLanguageCookie( $this.lan_selector.getValue() );
			LocalCacheData.setI18nDic( null );

			ProgressBar.showProgressBar( message_id );
			ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

			setTimeout( function() {
				window.location.reload( true );
			}, 2000 );

		} );

		if ( LocalCacheData.all_url_args.user_name ) {
			username_input.val( LocalCacheData.all_url_args.user_name );
		}

		if ( LocalCacheData.all_url_args.password ) {
			passwordInput.val( LocalCacheData.all_url_args.password );
		}

		Global.moveCookiesToNewPath();
		Global.setUIInitComplete();

	},

	cleanWhenUnloadView: function( callBack ) {
		$( '#loginViewContainer' ).remove();
		Global.setVirtualDeviceViewport('desktop'); // Setting mobile view on login, then back to desktop (990px virtual) after login, to allow pan & zoom, as not whole app is mobile optimized.
		this._super( 'cleanWhenUnloadView', callBack );
	},

	renderAnimalsForBackground: function() {
		var station_id = Global.getStationID();
		//week of year and numeric digits of station_id.
		if ( station_id.length > 0 ) {
			station_id = station_id.substr( 0, 5 ).match( /\d+/g ).map( function ( n ) {
				return parseInt( n );
			} );
		}
		var seed = parseInt( moment().week() + '' + station_id );
		var random_image_number = Math.floor( ( Math.abs( Math.sin( seed ) ) * seed ) % 2 ) + 1; // seeded random
		$( '#login-bg_animal' ).css( 'background-image', 'url(\'theme/default/images/login_animals_' + random_image_number + '.png\')' );
	}

} );
