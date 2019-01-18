QuickPunchLoginViewController = QuickPunchBaseViewController.extend( {
	events: {
		'change #language': 'onLanguageChange'
	},
	authentication_api: null,
	_required_files: [
		'APIPunch',
		'APIAuthentication',
		'APICurrentUser',
		//'APICurrency',
		'APIUserPreference',
		'APIDate',
		'APIPermission'
	],
	initialize: function() {
		var row = Global.loadWidget( 'views/quick_punch/login/QuickPunchLoginView.html' );
		this.template = _.template( row );
		this.setElement( this.template( {} ) );
		Global.contentContainer().html( this.$el );

		this.checkForWebkitTextSecuritySupport();

		var $this = this;
		require( this.filterRequiredFiles(), function() {
			$this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
			$this.authentication_api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
			$this.currentUser_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
			// this.currency_api = new (APIFactory.getAPIClass( 'APICurrency' ))();
			$this.user_preference_api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
			$this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
			$this.permission_api = new (APIFactory.getAPIClass( 'APIPermission' ))();
			$this.edit_view_error_ui_dic = {};
			LocalCacheData.all_url_args = {};
			$this.render();
		} );
	},
	render: function() {
		var $this = this;
		this.login_btn = this.$( 'button#punch_login' );
		this.login_btn.on( 'click', function( e ) {
			$this.onLogin( e );
		} );
		this.$( 'input[name="quick_punch_id"]' ).focus();
		this.setLanguageSourceData( 'language' );
		$( document ).off( 'keydown' ).on( 'keydown', function( event ) {
			var attrs = event.target.attributes;
			if ( event.keyCode === 13 ) {
				if ( $this.login_btn.attr( 'disabled' ) == 'disabled' ) {
					return;
				}
				$this.onLogin();
				event.preventDefault();
			}
			if ( event.keyCode === 9 && event.shiftKey ) {
				if ( attrs['tab-start'] ) {
					$this.$( 'button[tabindex=0]', $this.$el )[0].focus();
					event.preventDefault();
				}
			}
			if ( attrs['tab-end'] && event.shiftKey === false ) {
				$this.$( 'input[tabindex=1]', $this.$el )[0].focus();
				event.preventDefault();
			}
		} );
		return this;
	},

	// doLogin: function ( e ) {
	//     if ( e.keyCode === 13 ) {
	//         this.onLogin();
	//     }
	// },

	onLogin: function( e ) {
		var $this = this;
		var quick_punch_id = this.$( '#quick_punch_id' ).val();
		var quick_punch_password = this.$( '#quick_punch_password' ).val();
		this.login_btn.attr( 'disabled', 'disabled' );
		this.clearErrorTips( true, true );
		this.authentication_api.PunchLogin( quick_punch_id, quick_punch_password, {
			onResult: function( raw_result ) {
				if ( raw_result.isValid() ) {

					//set up promises to complete before view change
					TTPromise.add( 'QPLogin', 'Permissions' );
					TTPromise.add( 'QPLogin', 'CurrentStation' );
					TTPromise.add( 'QPLogin', 'CurrentUser' );
					TTPromise.add( 'QPLogin', 'CurrentCompany' );
					TTPromise.add( 'QPLogin', 'Locale' );

					//when all QPLogin promises complete, forward to punch view
					TTPromise.wait( 'QPLogin', null, function() {
						$this.goToView();
					} );

					var result = raw_result.getResult();
					$this.getCurrentStation();
					$this.getCurrentUser();
					// LocalCacheData.setSessionID( result.SessionID );
					setCookie( 'SessionID-QP', result.SessionID, { expires: 30, path: LocalCacheData.cookie_path } );
					$this.permission_api.getPermission( {
						onResult: function( permissionRes ) {
							LocalCacheData.setPermissionData( permissionRes.getResult() );
							TTPromise.resolve( 'QPLogin', 'Permissions' );
						}
					} );

					$this.authentication_api.getCurrentCompany( {
						onResult: function( current_company_result ) {
							var com_result = current_company_result.getResult();
							if ( com_result.is_setup_complete == 1 ) {
								com_result.is_setup_complete = true;
							} else {
								com_result.is_setup_complete = false;
							}
							LocalCacheData.setCurrentCompany( com_result );


							TTPromise.resolve( 'QPLogin', 'CurrentCompany' );
							Debug.Text( 'Version: Client: ' + APIGlobal.pre_login_data.application_build + ' Server: ' + com_result.application_build, 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );
							if ( APIGlobal.pre_login_data.application_build != com_result.application_build && APIGlobal.pre_login_data['PRODUCTION'] == true ) {
								Debug.Text( 'Version mismatch on login: Reloading...', 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );
								window.location.reload( true );
							}
						}
					} );
					$this.getLocale();
				} else {
					$this.setErrorTips( raw_result );
				}
			}
		} );
	},

	/* Checks if browser supports the use of -webkit-text-security, so we can obscure the password.
	 * We want type number so that mobile devices will trigger the numeric keypad rather than the alphabet keyboard.
	 * If not supported by browser (E.g. IE), replace element type number with type password.
	 * Note: type="number" is default in html, otherwise chrome on android does not change the keyboard to numeric keypad if other way around and converting to number from password. Seems to miss/ignore the type change.
	 */
	checkForWebkitTextSecuritySupport: function() {
		if(typeof CSS !== 'function' || !CSS.supports('-webkit-text-security', 'disc')) {
			var old_elem = $('input#quick_punch_password');
			var new_elem = old_elem.clone(true); // clone all, including bound events
			new_elem.attr('type', 'password');
			// Replace the element rather than simply changing the original, as IE does not support input type change well.
			old_elem.replaceWith(new_elem);
		}
	},

	getLocale: function() {
		var result = this.authentication_api.getLocale( $( 'select[name="language"]' ).val(), {
			onResult: function( result ) {
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
				TTPromise.resolve( 'QPLogin', 'Locale' );
			}
		} );
	},

	getCurrentUser: function() {
		this.currentUser_api.getCurrentUser( { onResult: this.onGetCurrentUser, delegate: this } );
	},

	getCurrentStation: function( callBack ) {
		var $this = this;

		var station_id = Global.getStationID();
		require( ['APIStation'], function() {
			var api_station = new (APIFactory.getAPIClass( 'APIStation' ))();

			if ( !station_id ) {
				station_id = '';
			}
			api_station.getCurrentStation( station_id, '10', {
				onResult: function( result ) {
					TTPromise.resolve( 'QPLogin', 'CurrentStation' );
					//doNext( result );
				}
			} );
		} );
	},

	onGetCurrentUser: function( e ) {
		LocalCacheData.setPunchLoginUser( e.getResult() );
		TTPromise.resolve( 'QPLogin', 'CurrentUser' );
	},

	goToView: function() {
		this.doing_login = false;
		// TopMenuManager.ribbon_view_controller = null;
		// TopMenuManager.ribbon_menus = null;
		// Global.topContainer().empty();
		LocalCacheData.currentShownContextMenuName = null;

		//Ensure that the language chosen at the login screen is passed in so that the user's country can be appended to create a proper locale.IndexViewController.instance.router.removeCurrentView();
		// var target_view = getCookie( 'PreviousSessionType' );
		// if ( target_view && !getCookie( 'PreviousSessionID' ) ) {
		//     TopMenuManager.goToView( target_view );
		//     setCookie( 'PreviousSessionType', null,  30,
		//  LocalCacheData.cookie_path,
		//  Global.getHost()
		//  );
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

	setErrorTips: function( result ) {
		this.clearErrorTips( true );
		var error_list = result.getDetails() ? result.getDetails()[0] : {};
		if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
			error_list = error_list.error;
		}
		for ( var key in error_list ) {
			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}
			var field_obj;
			if ( this.$( 'input[name="' + key + '"]' )[0] ) {
				field_obj = this.$( 'input[name="' + key + '"]' );
			} else if ( this.$( 'select[name="' + key + '"]' )[0] ) {
				field_obj = this.$( 'select[name="' + key + '"]' );
			}
			if ( field_obj ) {
				field_obj.addClass( 'error-tip' );
				var error_string;
				if ( _.isArray( error_list[key] ) ) {
					error_string = error_list[key][0];
				} else {
					error_string = error_list[key];
				}
				field_obj.attr( 'data-original-title', error_string );
				// field_obj.tooltip({
				//     'title': error_string,
				// })
				field_obj.tooltip( 'show' );
				this.edit_view_error_ui_dic[key] = field_obj;
			}
			// if ( field_obj ) {
			// 	$('html, body').animate({
			// 		scrollTop: field_obj.offset().top
			// 	}, 2000);
			// }
		}
		if ( _.size( this.edit_view_error_ui_dic ) > 0 ) {
			this.login_btn.removeAttr( 'disabled' );
			_.min( this.edit_view_error_ui_dic, function( item ) {
				if ( item.attr( 'tabindex' ) ) {
					return parseInt( item.attr( 'tabindex' ) );
				}
			} ).focus();
		}
	},

	clearErrorTips: function( clear_all, destroy ) {
		for ( var key in this.edit_view_error_ui_dic ) {
			if ( this.edit_view_error_ui_dic[key].val() !== '' || clear_all ) {
				this.edit_view_error_ui_dic[key].removeClass( 'error-tip' );
				this.edit_view_error_ui_dic[key].attr( 'data-original-title', '' );
			}
			if ( destroy ) {
				this.edit_view_error_ui_dic[key].tooltip( 'dispose' );
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
			source_data = Global.removeSortPrefixFromArray( (LocalCacheData.getLoginData().language_options) );
		}
		if ( _.size( source_data ) == 0 ) {
			set_empty = true;
		}
		if ( set_empty === true ) {
			this.$( field_selector ).append( $( '<option></option>' ).prop( 'value', '0' ).text( '-- ' + $.i18n._( 'None' ) + ' --' ) ).attr( 'selected', 'selected' );
		}
		if ( _.size( source_data ) > 0 ) {
			$.each( source_data, function( value, label ) {
				$this.$( field_selector ).append( $( '<option></option>' ).attr( 'value', value ).text( label ) );
				if ( LocalCacheData.getLoginData().language == value ) {
					$this.$( field_selector ).val( value );
				}
			} );
		}
		// $this.$( field_selector ).selectpicker();
	},

	onLanguageChange: function( e ) {
		Global.setLanguageCookie( $( e.target ).val() );
		LocalCacheData.setI18nDic( null );
		var message_id = UUID.guid();
		ProgressBar.showProgressBar( message_id );
		ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

		setTimeout( function() {
			window.location.reload( true );
		}, 2000 );
	}
} );


