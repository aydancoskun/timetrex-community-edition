var TAlertManager = (function() {

	var view = null;

	var isShownNetworkAlert = false;

	var showNetworkErrorAlert = function( jqXHR, textStatus, errorThrown ) {
		if ( textStatus == "parsererror" ) {
			Global.sendErrorReport(textStatus + ': ' + errorThrown + " ~FROM TAlertManager::showNetworkErrorAlert()~", false, false, jqXHR);
			return;
		}

		if ( !isShownNetworkAlert ) {
			TAlertManager.showAlert( Global.network_lost_msg + "<br><br>" + "Error: " + textStatus + ': <br>"'+ errorThrown +'"' + '<br><hr>' + (jqXHR.responseText ? jqXHR.responseText : 'N/A') + " (" + jqXHR.status + ")", 'Error', function() {
				isShownNetworkAlert = false;
			} );
			isShownNetworkAlert = true;
		}
	}

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

			var host = Global.getHost();

			$.cookie( 'AlternateSessionData', null, {expires: 1, path: LocalCacheData.cookie_path, domain: host} );
		}

		function backToPreSession() {
			var host = Global.getHost();
			var alternate_session_data = JSON.parse( $.cookie( 'AlternateSessionData' ) );
			var url = alternate_session_data.previous_session_url;
			var previous_cookie_path = alternate_session_data.previous_cookie_path;

			alternate_session_data = {
				new_session_id: alternate_session_data.previous_session_id,
				previous_session_view: alternate_session_data.previous_session_view,
			};

			$.cookie( 'AlternateSessionData', JSON.stringify( alternate_session_data ), {expires: 1, path: previous_cookie_path, domain: host}  );

			window.location = url +'#!m=Login';
			Global.needReloadBrowser = true;

			result.remove();
			result = null;
		}
	};

	var closeBrowserBanner = function() {
		$( '.browser-banner' ).remove();
	};

	var showBrowserTopBanner = function() {
		var div = $( '<div class="browser-banner"><a href="https://www.timetrex.com/supported-web-browsers" target="_blank"><span class="label"></span></a></div>' );
		div.find( 'span' ).text( $.i18n._( LocalCacheData.getLoginData().application_name + ' requires a modern HTML5 standards compatible web browser, click here for more information.' ) );

		$( 'body' ).append( div );
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
		p.append( $.i18n._( 'Are you sure you wish to save this record without correcting the above warnings?' ) )
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

	};

	var remove = function() {

		if ( view ) {
			view.remove();
			view = null;
		}

	};

	return {
		showConfirmAlert: showConfirmAlert,
		showAlert: showAlert,
		showErrorAlert: showErrorAlert,
		showPreSessionAlert: showPreSessionAlert,
		showBrowserTopBanner: showBrowserTopBanner,
		closeBrowserBanner: closeBrowserBanner,
		showWarningAlert: showWarningAlert,
		showNetworkErrorAlert:showNetworkErrorAlert
	}

})();
