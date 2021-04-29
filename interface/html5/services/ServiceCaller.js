import { ResponseObject } from '@/model/ResponseObject';
import { TTUUID } from '@/global/TTUUID';
import { APIReturnHandler } from '@/model/APIReturnHandler';

export class ServiceCaller extends Backbone.Model {

	constructor() {
		$.xhrPool = [];
		super();
	}

	getIsIdempotent() {
		if ( this.is_idempotent ) {
			return this.is_idempotent
		}

		return false;
	}

	setIsIdempotent( value ) {
		this.is_idempotent = value;

		return true;
	}

	argumentsHandler() {
		var className = arguments[0];
		var function_name = arguments[1];
		var apiArgsAndResponseObject = arguments[2];
		var lastApiArgsAndResponseObject = arguments[2][( apiArgsAndResponseObject.length - 1 )];
		var apiArgs = {};
		var responseObject;
		var len;

		if ( Global.isSet( lastApiArgsAndResponseObject.onResult ) || Global.isSet( lastApiArgsAndResponseObject.async ) ) {
			len = ( apiArgsAndResponseObject.length - 1 );

			responseObject = new ResponseObject( lastApiArgsAndResponseObject );

		} else {
			len = apiArgsAndResponseObject.length;
			responseObject = null;
		}

		for ( var i = 0; i < len; i++ ) {
			apiArgs[i] = apiArgsAndResponseObject[i];

			if ( i === 0 && len === 1 &&
				Global.isSet( apiArgs[i] ) &&
				Global.isSet( apiArgs[i].second_parameter ) ) {
				apiArgs[1] = apiArgs[i].second_parameter;
			}

		}

		return this.call( className, function_name, responseObject, apiArgs );

	}

	getOptionsCacheKey( api_args, key ) {

		$.each( api_args, function( index, value ) {

			if ( $.type( value ) === 'object' ) {
				key = key + '_' + JSON.stringify( value );
			} else {
				key = key + '_' + value;
			}

		} );

		return key;

	}

	uploadFile( form_data, paramaters, responseObj ) {
		var message_id = TTUUID.generateUUID();
		ProgressBar.showProgressBar( message_id );

		//On IE 9
		if ( typeof FormData == "undefined" ) {
			form_data.attr( 'method', 'POST' );
			form_data.attr( 'action', ServiceCaller.getURLByObjectType( 'upload' ) + '?' + paramaters + '&' + Global.getSessionIDKey() + '=' + LocalCacheData.getSessionID() );
			form_data.attr( 'enctype', 'multipart/form-data' );

			ProgressBar.changeProgressBarMessage( 'File Uploading' );
			form_data.ajaxForm().ajaxSubmit( {
				success: function( result ) {
					if ( result && result.toString().toLocaleLowerCase() !== 'true' ) {
						TAlertManager.showAlert( result );
					}
					ProgressBar.removeProgressBar();
					if ( responseObj.onResult ) {
						responseObj.onResult( result );
					}

				}
			} );
			return;
		}

		ProgressBar.changeProgressBarMessage( 'File Uploading' );
		$.ajax( {
			url: ServiceCaller.getURLByObjectType( 'upload' ) + '?' + paramaters + '&' + Global.getSessionIDKey() + '=' + LocalCacheData.getSessionID(), //Server script to process data
			headers: {
				//Handle CSRF tokens and related headers here.
				'X-Client-ID': 'Browser-TimeTrex',
				'X-CSRF-Token': getCookie( 'CSRF-Token' ),
			},
			type: 'POST',

//			xhr: function() {     // Custom XMLHttpRequest
//				var myXhr = $.ajaxSettings.xhr();
//				if ( myXhr.upload ) { // Check if upload property exists
//					myXhr.upload.addEventListener( 'progress', progressHandlingFunction, false ); // For handling the progress of the upload
//				}
//
//				function progressHandlingFunction() {
//				}
//
//				return myXhr;
//
//			},

			success: function( result ) {
				if ( result && result.toString().toLocaleLowerCase() !== 'true' ) {
					TAlertManager.showAlert( result );
				}

				if ( responseObj.onResult ) {
					responseObj.onResult( result );
				}

				ProgressBar.removeProgressBar();
			},
			// Form data
			data: form_data,
			cache: false,
			contentType: false,
			processData: false
		} );

	}

	prettyPrintAPIArguments( apiArgs ) {
		if ( apiArgs && apiArgs.json ) {
			var retval = [];
			var args = JSON.parse( apiArgs.json );
			for ( var property_name in args ) {
				var arg = args[property_name];
				retval.push( JSON.stringify( arg, null, 2 ) ); //Pretty print JSON
			}

			return retval.join( ', ' );
		}

		return null;
	}

	call( className, function_name, responseObject, apiArgs ) {
		var $this = this;
		var message_id;
		var base_url = ServiceCaller.getAPIURL( 'Class=' + className + '&Method=' + function_name + '&v=2' );
		var url = base_url;
		if ( LocalCacheData.getAllURLArgs() ) {
			if ( LocalCacheData.getAllURLArgs().hasOwnProperty( 'user_id' ) ) {
				url = url + '&user_id=' + LocalCacheData.getAllURLArgs().user_id;
			}
			if ( LocalCacheData.getAllURLArgs().hasOwnProperty( 'company_id' ) ) {
				url = url + '&company_id=' + LocalCacheData.getAllURLArgs().company_id;
			}
		}
		if ( Global.getStationID() ) {
			url = url + '&StationID=' + Global.getStationID();
		}

		var apiReturnHandler;
		var async;

		if ( responseObject && responseObject.get( 'async' ) === false ) {
			async = responseObject.get( 'async' );
		} else {
			async = true;
		}
		var cache_key;
		switch ( function_name ) {
			case 'getOptions':
			case 'getOtherField':
			case 'isBranchAndDepartmentAndJobAndJobItemEnabled':
			case 'getHierarchyControlOptions':
			case 'getUserGroup':
			case 'getJobGroup':
			case 'getJobItemGroup':
			case 'getProductGroup':
			case 'getDocumentGroup':
			case 'getQualificationGroup':
			case 'getKPIGroup':

				if ( function_name === 'getUserGroup' ) {
					cache_key = className + '.' + 'userGroup';
				} else if ( function_name === 'getJobGroup' ) {
					cache_key = className + '.' + 'jobGroup';
				} else if ( function_name === 'getJobItemGroup' ) {
					cache_key = className + '.' + 'jobItemGroup';
				} else if ( function_name === 'getProductGroup' ) {
					cache_key = className + '.' + 'productGroup';
				} else if ( function_name === 'getDocumentGroup' ) {
					cache_key = className + '.' + 'documentGroup';
				} else if ( function_name === 'getQualificationGroup' ) {
					cache_key = className + '.' + 'qualificationGroup';
				} else if ( function_name === 'getKPIGroup' ) {
					cache_key = className + '.' + 'kPIGroup';
				} else if ( function_name === 'getHierarchyControlOptions' ) {
					cache_key = 'getHierarchyControlOptions';
				} else {
					cache_key = this.getOptionsCacheKey( apiArgs, className + '.' + function_name );
				}
				if ( responseObject.get( 'noCache' ) === true ) {
					LocalCacheData.result_cache[cache_key] = false;
				}

				if ( cache_key && LocalCacheData.result_cache[cache_key] ) {
					var result = LocalCacheData.result_cache[cache_key];
					//Debug.Arr(result, 'Response from cached result. Key: '+cache_key, 'ServiceCaller.js', 'ServiceCaller', 'call', 10);

					apiReturnHandler = new APIReturnHandler();

					apiReturnHandler.set( 'result_data', result );
					apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
					apiReturnHandler.set( 'function_name', function_name );
					apiReturnHandler.set( 'args', apiArgs );

					if ( responseObject.get( 'onResult' ) ) {
						responseObject.get( 'onResult' )( apiReturnHandler );
					}

					return apiReturnHandler;

				}
				break;
			case 'setUserGroup':
			case 'deleteUserGroup':
				cache_key = className + '.' + 'userGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setJobGroup':
			case 'deleteJobGroup':
				cache_key = className + '.' + 'jobGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setJobItemGroup':
			case 'deleteJobItemGroup':
				cache_key = className + '.' + 'jobItemGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setProductGroup':
			case 'deleteProductGroup':
				cache_key = className + '.' + 'productGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setDocumentGroup':
			case 'deleteDocumentGroup':
				cache_key = className + '.' + 'documentGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setQualificationGroup':
			case 'deleteQualificationGroup':
				cache_key = className + '.' + 'qualificationGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
			case 'setKPIGroup':
			case 'deleteKPIGroup':
				cache_key = className + '.' + 'kPIGroup';
				LocalCacheData.result_cache[cache_key] = false;
				break;
		}

		message_id = TTUUID.generateUUID();

		TTPromise.add( 'ServiceCaller', message_id );

		if ( className !== 'APIProgressBar' && function_name !== 'Logout' ) {
			url = url + '&MessageID=' + message_id;
		}

		if ( this.getIsIdempotent() == true ) {
			url = url + '&idempotent=1';
		}

		if ( ServiceCaller.extra_url ) {
			url = url + ServiceCaller.extra_url;
		}

		if ( !apiArgs ) {
			apiArgs = {};

		}

		apiArgs = { json: JSON.stringify( apiArgs ) };

		//Try to get a stack trace for each function call so if an error occurs we know exactly what triggered the call.
		var stack_trace_str = null;
		if ( typeof Error !== 'undefined' ) {
			var stack_trace = ( new Error() );
			if ( typeof stack_trace === 'object' && stack_trace.stack && typeof stack_trace.stack === 'string' ) {
				stack_trace_str = stack_trace.stack.split( '\n' ); //This is eventually JSONified so convert it to an array for better formatting.
			} else {
				stack_trace_str = null;
			}
			stack_trace = null; // Previously null was 'delete' but not valid in JS strict mode.
		}

		var api_called_date = new Date();
		var api_stack = {
			api: className + '.' + function_name,
			args: apiArgs.json,
			api_called_date: api_called_date.toISOString(),
			stack_trace: stack_trace_str
		};
		stack_trace_str = null; // Previously null was 'delete' but not valid in JS strict mode.

		if ( LocalCacheData.api_stack.length === 16 ) {
			LocalCacheData.api_stack.pop();
		}

		if ( function_name !== 'sendErrorReport' ) {
			LocalCacheData.api_stack.unshift( api_stack );
		}

		if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
			ProgressBar.showProgressBar( message_id );
		}

		$.ajax(
			{
				dataType: 'JSON',
				data: apiArgs,
				headers: {
					//#1568  -  Add "fragment" to POST variables in API calls so the server can get it...
					//Encoding is a must, otherwise HTTP requests will be corrupted on some web browsers (ie: Mobile Safari)
					//This caused the corrupted requests for things like: "POST_/api/json/api_php?Class"
					//Also it must use dashes instead of underscores for separators.
					'Request-Uri-Fragment': encodeURIComponent( LocalCacheData.fullUrlParameterStr ),

					//Handle CSRF tokens and related headers here.
					'X-Client-ID': 'Browser-TimeTrex',
					'X-CSRF-Token': getCookie( 'CSRF-Token' ),
				},
				type: 'POST',
				async: async,
				url: url,
				beforeSend: function( jqXHR ) {
					this.jqXHR = jqXHR;
					$.xhrPool.push( this ); //Track all pending AJAX requests so we can cancel them if needed.
				},
				complete: function( jqXHR ) {
					var index = $.xhrPool.indexOf( this );
					if ( index > -1 ) {
						$.xhrPool.splice( index, 1 ); //Remove completed AJAX request from pool.
					}
				},
				success: function( result ) {
					//Debug.Arr(result, 'Response from API. message_id: '+ message_id, 'ServiceCaller.js', 'ServiceCaller', null, 10);

					if ( Global.enable_api_tracing == true ) {
						var api_trace_label = '%cAPI Request:%c ' + className + '->' + function_name + '(...) [Expand for Details]';
						console.groupCollapsed( api_trace_label, 'font-weight: bold', 'font-weight: normal' );
						console.log( '%c' + className + '->' + function_name + '%c(' + $this.prettyPrintAPIArguments( apiArgs ) + ')', 'font-weight: bold', 'font-weight: normal' );

						var api_trace_raw_request_label = '%cRaw Request:%c [Expand for Details]';
						console.groupCollapsed( api_trace_raw_request_label, 'font-weight: bold', 'font-weight: normal' );
						console.log( '%cURL:%c ' + url, 'font-weight: bold', 'font-weight: normal' );
						console.log( '%cRaw POST Body (non-URLEncoded):%c json=' + apiArgs.json + '', 'font-weight: bold', 'font-weight: normal' );
						console.log( '%ccURL Command:%c curl -k --location --request POST --cookie "' + Global.getSessionIDKey() + '=<SessionID>" --form \'json=' + apiArgs.json + '\' "' + base_url + '"', 'font-weight: bold', 'font-weight: normal' );
						console.groupEnd( api_trace_raw_request_label );

						var api_trace_response_label = '%cResponse:%c [Expand for Details]';
						console.groupCollapsed( api_trace_response_label, 'font-weight: bold', 'font-weight: normal' );
						console.log( JSON.stringify( result, null, 2 ) );
						console.groupEnd( api_trace_response_label );

						console.groupEnd( api_trace_label );
						api_trace_raw_request_label = null; // Previously null was 'delete' but not valid in JS strict mode.
						api_trace_response_label = null; // Previously null was 'delete' but not valid in JS strict mode.
						api_trace_label = null; // Previously null was 'delete' but not valid in JS strict mode.
					}

					if ( !Global.isSet( result ) ) {
						result = true;
					}
					if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
						ProgressBar.removeProgressBar( message_id );
					}

					apiReturnHandler = new APIReturnHandler();
					apiReturnHandler.set( 'result_data', result );
					apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
					apiReturnHandler.set( 'function_name', function_name );
					apiReturnHandler.set( 'args', apiArgs );

					if ( !apiReturnHandler.isValid() && ( apiReturnHandler.getCode() === 'EXCEPTION' || apiReturnHandler.getCode() === 'EXCEPTION_CSRF' ) ) {
						Debug.Text( 'api-exception: Code: ' + apiReturnHandler.getCode() + ' Error: ' + apiReturnHandler.getDescription() +' Message ID: '+ message_id, 'ServiceCaller.js', 'ServiceCaller', null, 10);
						if ( apiReturnHandler.getCode() === 'EXCEPTION_CSRF' ) { //Don't bother recording CSRF exceptions.
							Global.sendAnalyticsEvent( 'service-caller', 'error:api-exception', 'api-exception: Code: ' + apiReturnHandler.getCode() + ' Error: ' + apiReturnHandler.getDescription() );
							TAlertManager.showAlert( apiReturnHandler.getDescription(), 'Error', function() {
								window.location.reload();
							} );
						} else {
							Global.sendErrorReport( 'api-exception: Code: ' + apiReturnHandler.getCode() + ' Error: ' + apiReturnHandler.getDescription(), 'ServiceCaller.js' );
							TAlertManager.showAlert( $.i18n._( 'API Exception' ) + ': ' + apiReturnHandler.getDescription(), 'Error' );
						}

						//Error: Uncaught ReferenceError: promise_key is not defined
						if ( typeof promise_key != 'undefined' ) {
							TTPromise.reject( 'ServiceCaller', message_id );
						} else {
							Debug.Text( 'ERROR: Unable to release promise because key is NULL.', 'ServiceCaller.js', 'ServiceCaller', null, 10 );
						}
						return;
					} else if ( !apiReturnHandler.isValid() && apiReturnHandler.getCode() === 'SESSION' ) {
						//Debug.Text('API returned session expired: '+ message_id, 'ServiceCaller.js', 'ServiceCaller', null, 10);
						LocalCacheData.cleanNecessaryCache(); //make sure the cache is cleared when session is expired
						ServiceCaller.cancel_all_error = true;
						LocalCacheData.login_error_string = $.i18n._( 'Session expired, please login again.' );
						Global.clearSessionCookie(); //This helps skip other API calls or prevent the UI from thinking we are still logged in.
						if ( window.location.href == Global.getBaseURL() + '#!m=' + 'Login' ) {
							// Prevent a partially loaded login screen when SessionID cookie is set but not valid on server.
							//   However if the session is expired on the server, and the user tries to navigate to some other page,
							//   there could be multiple API calls queued up, which causes this reload() to be triggered many times,
							//   and network requests to be aborted, which triggers error messages. Disable the reload for now as in theory it shouldn't be needed.
							//   This reload also gets rid of the "Session expired, please login again" error message, which is not ideal.
							//window.location.reload();
						} else {
							var paths = Global.getBaseURL().replace( ServiceCaller.root_url, '' ).split( '/' );
							if ( paths.indexOf( 'quick_punch' ) > 0 ) {
								Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + 'QuickPunchLogin' );
							} else if ( paths.indexOf( 'portal' ) > 0 ) {
								if ( LocalCacheData.getAllURLArgs().company_id ) {
									LocalCacheData.setPortalLoginUser( null );
									Global.setURLToBrowser( Global.getBaseURL() + '#!m=PortalJobVacancy&company_id=' + LocalCacheData.getAllURLArgs().company_id );
								}
							} else {
								if ( !LocalCacheData.getAllURLArgs().company_id ) {
									Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + 'Login' );
								}
							}
						}
						TTPromise.resolve( 'ServiceCaller', message_id );
						return;
					} else if ( !apiReturnHandler.isValid() && apiReturnHandler.getCode() === 'DOWN_FOR_MAINTENANCE' ) {
						Global.sendAnalyticsEvent( 'service-caller', 'error:down-for-maintenance', 'error:down-for-maintenance: Code: ' + apiReturnHandler.getCode() + ' Error: ' + apiReturnHandler.getDescription() );

						//Before the location.replace because after that point we can't be sure of execution.
						TTPromise.resolve( 'ServiceCaller', message_id );
						//replace instead of assignment to ensure that the DOWN_FOR_MAINTENANCE page does not end up in the back button history.
						window.location.replace( ServiceCaller.root_url + LocalCacheData.loginData.base_url + 'html5/DownForMaintenance.php?exception=DOWN_FOR_MAINTENANCE' );
						return;
					} else {
						//Debug.Text('API returned result: '+ message_id, 'ServiceCaller.js', 'ServiceCaller', null, 10);

						//only cache data when api return is successful and can be trusted (ie not logged out or session expired.)
						switch ( function_name ) {
							case 'getOptions':
							case 'getOtherField':
							case 'isBranchAndDepartmentAndJobAndJobItemEnabled':
							case 'getUserGroup':
							case 'getJobGroup':
							case 'getJobItemGroup':
							case 'getProductGroup':
							case 'getDocumentGroup':
							case 'getQualificationGroup':
							case 'getKPIGroup':
							case 'getHierarchyControlOptions':
								LocalCacheData.result_cache[cache_key] = result;
								break;
						}

						//Error: Function expected in /interface/html5/services/ServiceCaller.js?v=9.0.0-20150822-090205 line 269
						if ( responseObject.get( 'onResult' ) && typeof ( responseObject.get( 'onResult' ) ) == 'function' ) {
							responseObject.get( 'onResult' )( apiReturnHandler );
						}
					}

					TTPromise.resolve( 'ServiceCaller', message_id );
				},

				error: function( jqXHR, textStatus, errorThrown ) {
					TTPromise.reject( 'ServiceCaller', message_id );
					if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
						ProgressBar.removeProgressBar( message_id );
					}

					if ( ServiceCaller.cancel_all_error ) {
						return;
					}

					Debug.Text( 'AJAX Request Error: ' + errorThrown + ' Message: ' + textStatus + ' HTTP Code: ' + jqXHR.status, 'ServiceCaller.js', 'ServiceCaller', 'call', 10 );
					if ( jqXHR.responseText && jqXHR.responseText.indexOf( 'User not authenticated' ) >= 0 ) {
						ServiceCaller.cancel_all_error = true;

						LocalCacheData.login_error_string = $.i18n._( 'Session timed out, please login again.' );

						Global.clearSessionCookie();
						//$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + 'Login' );

						return;

					} else {
						if ( jqXHR.responseText && $.type( jqXHR.responseText ) === 'string' ) {
							TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
						}
					}

					if ( jqXHR.status === 200 && !jqXHR.responseText ) {
						apiReturnHandler = new APIReturnHandler();
						apiReturnHandler.set( 'result_data', true );
						apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
						apiReturnHandler.set( 'function_name', function_name );
						apiReturnHandler.set( 'args', apiArgs );

						if ( responseObject.get( 'onResult' ) ) {
							responseObject.get( 'onResult' )( apiReturnHandler );
						}
						return apiReturnHandler;
					} else {
						if ( jqXHR.status === 0 || ( jqXHR.status >= 400 && jqXHR.status <= 599 ) ) {
							//Status=0 (No response from server at all), 4xx/5xx is critical server failure.
							//Server can't respond properly due to 4xx/5xx error code, so display a message to the user. Can't redirect to down_for_maintenance page as that could be a 404 as well.
							TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
							ProgressBar.cancelProgressBar();
						}

						if ( responseObject.get( 'onError' ) && typeof ( responseObject.get( 'onError' ) ) == 'function' ) {
							responseObject.get( 'onError' )( apiReturnHandler );
						}

						return null;
					}
				}
			}
		);

		return apiReturnHandler;
	}
}

ServiceCaller.getAPIURL = function( rest_url ) {
	return ServiceCaller.base_url + ServiceCaller.base_api_url + '?' + rest_url;
};

ServiceCaller.getURLByObjectType = function( object_type ) {
	var append_csrf = false;
	var append_cache_buster = false;

	var retval = null;

	var base_url = ServiceCaller.base_url + 'interface/send_file.php?api=1';

	switch ( object_type.toLowerCase() ) {
		case 'upload':
			retval = ServiceCaller.base_url + 'interface/upload_file.php'
			append_csrf = false;
			break;
		case 'import_csv_example':
			retval = ServiceCaller.base_url + 'interface/html5/views/wizard/import_csv/'
			append_csrf = false;
			break;
		case 'file_download':
			retval = base_url; //Must allow for appending '&object_type=...' on the end.
			append_csrf = true;
			break;
		case 'company_logo':
			retval = base_url + '&object_type=company_logo';
			append_csrf = true;
			append_cache_buster = true;
			break;
		case 'invoice_config':
			retval = base_url + '&object_type=invoice_config';
			append_csrf = true;
			break;
		case 'user_photo':
			retval = base_url + '&object_type=user_photo';
			append_csrf = true;
			break;

		case 'primary_company_logo':
			retval = base_url + '&object_type=primary_company_logo';
			break;
		case 'smcopyright':
			retval = base_url + '&object_type=smcopyright';
			break;
		case 'copyright':
			retval = base_url + '&object_type=copyright';
			break;
		default:
			break;
	}

	//Append CSRF-Token.
	if ( append_csrf == true ) {
		retval += '&X-CSRF-Token=' + getCookie( 'CSRF-Token' );
	}

	if ( append_cache_buster == true ) {
		retval += '&t=' + new Date().getTime();
	}

	return retval;
};

//Abort in-flight AJAX calls on logout.
ServiceCaller.abortAll = function() {
	$.each( $.xhrPool, function( index, ajax_obj ) {
		if ( typeof ajax_obj == 'object' && ajax_obj.jqXHR && typeof ajax_obj.jqXHR == 'object' && typeof ajax_obj.jqXHR.abort === 'function' ) {
			if ( ajax_obj.url && ajax_obj.url.indexOf( 'Method=Logout' ) == -1 ) { //Don't abort the Logout call.
				Debug.Text( ' Aborting API call: ' + ajax_obj.url, 'ServiceCaller.js', 'ServiceCaller', 'abortAll', 10 );
				ajax_obj.jqXHR.abort();
			} else {
				Debug.Text( 'Not aborting Logout API call...', 'ServiceCaller.js', 'ServiceCaller', 'abortAll', 10 );
			}
		}
	} );
};

ServiceCaller.base_url = null;
ServiceCaller.base_api_url = null;
ServiceCaller.root_url = null;
ServiceCaller.cancel_all_error = false;
ServiceCaller.extra_url = false;
