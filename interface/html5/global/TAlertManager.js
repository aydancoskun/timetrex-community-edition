var TAlertManager = (function() {

	var view = null;

	var isShownNetworkAlert = false;

	var closeBrowserBanner = function() {
		$( '.browser-banner' ).remove();
	};

	var showBrowserTopBanner = function() {
		if ( ie && ie <= 11 ) {
			var div = $( '<div class="browser-banner"><a href="https://www.timetrex.com/supported-web-browsers" target="_blank"><span id="browser-top-banner" class="label"><strong>WARNING</strong>: ' + LocalCacheData.getLoginData().application_name + ' will no longer support <strong>Internet Explorer 11</strong> effective <strong>January 14th, 2020</strong>.<br><strong>Please upgrade to Microsoft Edge, Chrome or FireFox immediately to continue using TimeTrex.</strong></span></a></div>' );
			$( 'body' ).append( div );
		}
	};

	var showNetworkErrorAlert = function( jqXHR, textStatus, errorThrown ) {
		//#2514 - status 0 is caused by browser cancelling the request. There is no status because there was no request.
		if ( jqXHR.status == 0 ) {
			if ( APIGlobal.pre_login_data.production !== true ) {
				console.error( 'Browser cancelled request... jqXHR: Status=0' );
			}
			return;
		}

		if ( textStatus == 'parsererror' ) {
			Global.sendErrorReport(textStatus + ' ('+ jqXHR.status +'): "'+ errorThrown +'" FROM TAlertManager::showNetworkErrorAlert():\n\n'+ (jqXHR.responseText ? jqXHR.responseText : 'N/A'), false, false, jqXHR);
			return;
		}

		if ( !isShownNetworkAlert ) {
			TAlertManager.showAlert( Global.network_lost_msg + '<br><br>' + 'Error: ' + textStatus + ' ('+ jqXHR.status +'): <br>"'+ errorThrown +'"' + '<br><hr>' + (jqXHR.responseText ? jqXHR.responseText : 'N/A') + ' (' + jqXHR.status + ')', 'Error', function() {
				isShownNetworkAlert = false;
			} );
			isShownNetworkAlert = true;
			Global.sendAnalyticsEvent( 'alert-manager', 'error:network', 'network-error: jqXHR-status: ' + jqXHR.status + ' Error: ' + textStatus );
		}
	};

	var showPreSessionAlert = function() {
		var result = $( '<div class="session-alert"> ' +
				'<span class="close-icon">X</span>' +
				'<span class="content"/>' +
				'</div>' );
		setTimeout( function() {
			$( 'body' ).append( result );
			result.find( '.content' ).html( $.i18n._( 'Previous Session' ) );
			var button = result.find( '.close-icon' );
			button.bind( 'click', function() {
				removePreSession();
			} );
			result.bind( 'click', function() {
				backToPreSession();
			} );
		}, 100 );

		function removePreSession() {
			result.remove();
			result = null;

			deleteCookie( 'AlternateSessionData', LocalCacheData.cookie_path, Global.getHost() );
		}

		function backToPreSession() {
			var host = Global.getHost();
			try { //Prevent JS exception if we can't parse alternate_session_data for some reason.
				var alternate_session_data = JSON.parse( getCookie( 'AlternateSessionData' ) );
				if ( !alternate_session_data ) {
					Debug.Text( 'No alternate_session_data exists.', 'TAlertManager.js', 'TAlertManager', 'backToPreSession', 10 );
					return;
				}
			} catch ( e ) {
				Debug.Text( e.message, 'TAlertManager.js', 'showPreSessionAlert', 'backToPreSession', 10 );
				return;
			}

			var url = alternate_session_data.previous_session_url;
			var previous_cookie_path = alternate_session_data.previous_cookie_path;

			alternate_session_data = {
				new_session_id: alternate_session_data.previous_session_id,
				previous_session_view: alternate_session_data.previous_session_view
			};

			setCookie( 'AlternateSessionData', JSON.stringify( alternate_session_data ), 1, previous_cookie_path, host );

			Global.setURLToBrowser( url +'#!m=Login' );
			Global.needReloadBrowser = true;

			result.remove();
			result = null;
		}
	};

	var showErrorAlert = function( result ) {
		var details = result.getDetails();

		if ( details.hasOwnProperty( 'error' ) ) {

		}
		if ( !details ) {
			details = result.getDescription(); // If the details is empty, try to get description to show.
		}
		var error_string = '';

		if ( Global.isArray( details ) || typeof details === 'object' ) {
			error_string = Global.convertValidationErrorToString( details );
		} else {

			error_string = details;
		}

		showAlert( error_string, 'Error' );

	};

	var showWarningAlert = function( result, callBack ) {
		var details = result.getDetails();
		var ul_container = $( '<ol>' );
		if ( Global.isArray( details ) || typeof details === 'object' ) {
			$.each( details, function( index, val ) {
				if ( val.hasOwnProperty( 'warning' ) ) {
					val = val.warning;
				}
				for ( var key in val ) {
					var li = $( '<li>' );
					var child_val = val[key];
					var has_child = false;
					for ( var child_key in child_val ) {
						if ( child_val.hasOwnProperty( child_key ) ) {
							has_child = true;
							li = $( '<li>' );
							li.append( child_val[child_key] );
							ul_container.append( li );
						}
					}
					if ( !has_child ) {
						li.append( val[key] );
						ul_container.append( li );
					}
				}
			} );
		}
		var div = $( '<div>' );
		var p = $( '<p>' );
		p.append( $.i18n._( 'Are you sure you wish to save this record without correcting the above warnings?' ) );
		div.append( ul_container );
		div.append( p );
		showConfirmAlert( div[0], 'Warning', callBack, 'Save', 'Cancel' );
	};

	var showAlert = function( content, title, callBack ) {
		if ( !title ) {
			title = $.i18n._( 'Message' );
		}

		var result = $( '<div class="t-alert">' +
				'<div class="content-div"><span class="content"/></div>' +
				'<span class="title"/>' +
				'<div class="bottom-bar">' +
				'<button class="t-button">Close</button>' +
				'</div>' +
				'</div>' );
		setTimeout( function() {
			if ( view !== null ) {

				var cContent = view.find( '.content' ).text();

				if ( cContent === content ) {
					return;
				}

				remove();

			}
			view = result;
			$( 'body' ).append( result );
			result.find( '.title' ).text( title );
			result.find( '.content' ).html( content );
			var button = result.find( '.t-button' );
			button.bind( 'click', function() {
				remove();
				if ( callBack ) {
					callBack();
				}
			} );
			button.focus();
			button.bind( 'keydown', function( e ) {
				e.stopPropagation();
				if ( e.keyCode === 13 ) {
					remove();
					if ( callBack ) {
						callBack();
					}
				}
			} );

			Global.setUIInitComplete();
		}, 100 );

	};

	var showConfirmAlert = function( content, title, callBackFunction, yesLabel, noLabel ) {

		if ( !Global.isSet( title ) ) {
			title = $.i18n._( 'Message' );
		}

		if ( !Global.isSet( yesLabel ) ) {
			yesLabel = $.i18n._( 'Yes' );
		}

		if ( !Global.isSet( noLabel ) ) {
			noLabel = $.i18n._( 'No' );
		}

		if ( view !== null ) {

			var cContent = view.find( '.content' ).text();

			if ( cContent === content ) {
				return;
			}

			remove();
		}
		var result = $( '<div class="confirm-alert"> ' +
				'<div class="content-div"><span class="content"/></div>' +
				'<span class="title"></span>' +
				'<div class="bottom-bar">' +
				'<button id="yesBtn" class="t-button bottom-bar-yes-btn"></button>' +
				'<button id="noBtn" class="t-button"></button>' +
				'</div>' +
				'</div>' );
		view = result;
		$( 'body' ).append( result );

		result.find( '#yesBtn' ).text( yesLabel );
		result.find( '#noBtn' ).text( noLabel );
		result.find( '.title' ).text( title );

		result.find( '.content' ).html( content );

		result.find( '#yesBtn' ).bind( 'click', function() {
			remove();
			callBackFunction( true );

		} );

		result.find( '#noBtn' ).bind( 'click', function() {
			remove();
			callBackFunction( false );

		} );

		Global.setUIInitComplete();
	};

	var remove = function() {

		if ( view ) {
			view.remove();
			view = null;
		}

	};

	return {
		showBrowserTopBanner: showBrowserTopBanner,
		closeBrowserBanner: closeBrowserBanner,
		showConfirmAlert: showConfirmAlert,
		showAlert: showAlert,
		showErrorAlert: showErrorAlert,
		showPreSessionAlert: showPreSessionAlert,
		showWarningAlert: showWarningAlert,
		showNetworkErrorAlert: showNetworkErrorAlert
	};

})();
