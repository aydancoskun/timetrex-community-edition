import { TTAPI } from '@/services/TimeTrexClientAPI';
import Nanobar from 'nanobar'; // need to rely on the default export via CommonJS syntax, hence no named import.

export var ProgressBar = ( function() {

	var loading_box = null;

	var process_number = false;

	var can_close = false;

	var timer = null;

	var close_time = null;

	var circle = null;

	var doing_close = false;

	var message_id_dic = {};

	var updating_message_id = false;

	var current_process_id = false;

	var _progress_bar_api = false;

	var get_progress_timer = null;

	var first_start_get_progress_timer = false;

	var start_progress_timer = false;

	var auto_clear_message_id_dic = {}; //for which api calls don't have return; for example all report view calls

	var last_iteration = null;

	var temp_message_until_close = '';

	var second_timer;

	var time_offset;

	var nanoBar;

	var loading_bar_time;

	var no_progress_for_next_call = false;

	var showOverlay = function() {
		Global.overlay().addClass( 'overlay' );
		Global.setUINotready();
		TTPromise.add( 'ProgressBar', 'overlay_visible' );
	};

	var closeOverlay = function() {
		//this variable is set in BaseViewController::onContextMenuClick
		if ( window.clickProcessing == true ) {
			window.clickProcessing = false;
			window.clearTimeout( window.clickProcessingHandle );
		}
		Global.overlay().removeClass( 'overlay' );
		Global.setUIReady();
		TTPromise.resolve( 'ProgressBar', 'overlay_visible' );
	};

	var cancelProgressBar = function() {
		if ( get_progress_timer ) {
			clearInterval( get_progress_timer );
			get_progress_timer = false;
		}
		removeProgressBar( current_process_id );
		get_progress_timer = false;
		current_process_id = false;
		last_iteration = null;
		first_start_get_progress_timer = false;
	};

	var showProgressBar = function( message_id, auto_clear, instant ) {

		TTPromise.add( 'ProgressBar', 'MASTER' );
		if ( no_progress_for_next_call ) {
			no_progress_for_next_call = false;
			return;
		}
		if ( instant ) {
			if ( process_number > 0 && loading_box ) {
				loading_box.css( 'display', 'block' );
			}
		} else {
			if ( !timer ) {
//			clearTimeout( timer );
				//Display progress bar after 1 sec
				timer = setTimeout( function() {
					if ( process_number > 0 && loading_box ) {
						loading_box.css( 'display', 'block' );
					}

				}, 1000 );
			}
		}

		if ( message_id ) {
			message_id_dic[message_id] = true;

			if ( auto_clear ) {
				auto_clear_message_id_dic[message_id] = true;
			}

		}

		if ( message_id && start_progress_timer === false ) {
			start_progress_timer = setInterval( function() {
				getProgressBarProcess();
			}, 3000 );
			first_start_get_progress_timer = true;
		}

		if ( process_number > 0 ) { //has multi call or the last call is not removed yet
			process_number = process_number + 1;
			return;
		}

		process_number = 1;

		Global.addCss( 'global/widgets/loading_bar/LoadingBox.css' );

		clearTimeout( close_time );
		var message_label;
		if ( !loading_box ) {
			var loadingBoxWidget = Global.loadWidget( 'global/widgets/loading_bar/LoadingBox.html' );
			var loadngBox = $( loadingBoxWidget ).attr( 'id', 'progressBar' );

			var close_icon = loadngBox.find( '.close-icon' );

			close_icon.unbind( 'click' ).click( function() {
				cancelProgressBar();

			} );
			// css only spinner - and yes, it does unfortunately need all those divs. They are for each of the spokes of the circle.
			circle = $( '<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' );

			$( 'body' ).append( loadngBox );
			loading_box = $( '#progressBar' );
			message_label = loading_box.find( '.processing' );

			if ( circle ) {
				message_label.after( circle );
			}

			loading_box.css( 'display', 'none' );

		} else {
			message_label = loading_box.find( '.processing' );

			if ( circle ) {
				message_label.after( circle );
			}

		}

		resetToDefault();
	};

	var resetToDefault = function() {

		if ( temp_message_until_close ) {
			//Default process message, change this when update progress bar.
			loading_box.find( '.processing' ).text( temp_message_until_close );
		} else {
			//Default process message, change this when update progress bar.
			// Error: Unable to get property '_' of undefined or null reference in interface/html5/global/ProgressBarManager.js?v=9.0.6-20151231-155042 line 169
			loading_box.find( '.processing' ).text( $.i18n ? $.i18n._( 'Processing...' ) : 'Processing...' );
		}

		var complete_info = loading_box.find( '.complete-info' );
		var progress_bar = loading_box.find( '.progress-bar' );
		var time_remaining = loading_box.find( '.time-remaining' );

		complete_info.css( 'display', 'none' );
		progress_bar.css( 'display', 'none' );
		time_remaining.css( 'display', 'none' );

		complete_info.text( 0 + ' / ' + 0 + ' ' + 0 + '%' );
		progress_bar.val( 0 );

		last_iteration = null;

	};

	var changeProgressBarMessage = function( val ) {
		temp_message_until_close = val;
		if ( loading_box ) {
			loading_box.find( '.processing' ).text( val );
		}

	};

	var updateProgressbar = function( data ) {

		if ( !loading_box ) {
			return;
		}

		loading_box.find( '.processing' ).text( data.message );

		var percentage = data.current_iteration / data.total_iterations;

		var complete_info = loading_box.find( '.complete-info' );
		var progress_bar = loading_box.find( '.progress-bar' );
		var time_remaining = loading_box.find( '.time-remaining' );

		complete_info.css( 'display', 'block' );
		progress_bar.css( 'display', 'block' );
		time_remaining.css( 'display', 'block' );

		complete_info.text( data.current_iteration + ' / ' + data.total_iterations + ' ' + ( percentage * 100 ).toFixed( 0 ) + '%' );
		progress_bar.prop( 'value', ( percentage * 100 ) );

		if ( !last_iteration ) {
			time_remaining.text( 'Calculating remaining time...' );
		} else {

			if ( last_iteration !== data.current_iteration || !second_timer ) {

				time_offset = ( data.last_update_time - data.start_time ) * ( data.total_iterations / data.current_iteration );
				time_offset = time_offset - ( data.last_update_time - data.start_time );

				if ( isNaN( time_offset ) || time_offset <= 0 ) {
					time_offset = 0;
				}

				//Error: 'console' is undefined in /interface/html5/global/ProgressBarManager.js?v=8.0.0-20141117-153515 line 224
				time_remaining.text( Global.getTimeUnit( time_offset, '99' ) );
			}
			secondDown();

		}

		last_iteration = data.current_iteration;

	};

	var noProgressForNextCall = function() {
		no_progress_for_next_call = true;
	};

	var secondDown = function() {

		//calculate down time every one second
		if ( !second_timer ) {
			var time_remaining = loading_box.find( '.time-remaining' );
			second_timer = setInterval( function() {

				if ( isNaN( time_offset ) || time_offset <= 0 ) {
					time_offset = 0;
				}

				if ( time_offset > 0 ) {
					time_offset = ( time_offset - 1 );
				}

				if ( time_offset <= 0 ) {
					time_offset = 0;
				}

				time_remaining.text( Global.getTimeUnit( time_offset, '99' ) );
			}, 1000 );
		}

	};

	var getProgressBarProcess = function() {
		if ( !LocalCacheData.getLoginData() ) {
			return;
		}

		if ( !current_process_id ) {
			for ( var key in message_id_dic ) {
				current_process_id = key;

				delete message_id_dic[key];
				break;
			}
		}

		if ( current_process_id && $.type( 'current_process_id' ) === 'string' ) {
			TTAPI.APIProgressBar.getProgressBar( current_process_id, {
				onResult: function( result ) {
					var res_data = result.getResult();

					//Means error in progress bar
					if ( res_data.hasOwnProperty( 'status_id' ) && res_data.status_id === 9999 ) {
						stopProgress();
						TAlertManager.showAlert( res_data.message );
					} else {
						if ( res_data === true ||
							( $.type( res_data ) === 'array' && res_data.length === 0 ) || !res_data.total_iterations ||
							$.type( res_data.total_iterations ) !== 'number' ) {
							stopProgress();
							return;
						} else {
							updateProgressbar( res_data );
							if ( first_start_get_progress_timer ) {
								first_start_get_progress_timer = false;

								//prevent over-writing active handle. see bug #2196
								if ( start_progress_timer != false && get_progress_timer == false ) {
									//start interval needs to be reset to FALSE to trigger start code in showProgressBar
									clearInterval( start_progress_timer );
									start_progress_timer = false;

									get_progress_timer = setInterval( function() {
										getProgressBarProcess();
									}, 2000 );
								}

							}

						}
					}

				}
			} );
		}

		function stopProgress() {
			if ( start_progress_timer ) {
				clearInterval( start_progress_timer );
				start_progress_timer = false;
			}
			if ( get_progress_timer ) {
				clearInterval( get_progress_timer );
				get_progress_timer = false;
			}

			if ( auto_clear_message_id_dic[current_process_id] ) {
				removeProgressBar( current_process_id );
			}

			get_progress_timer = false;
			current_process_id = false;
			last_iteration = null;
			first_start_get_progress_timer = false;
		}

	};

	var removeProgressBar = function( message_id ) {
		if ( message_id ) {
			delete message_id_dic[message_id];
			delete auto_clear_message_id_dic[message_id];

			if ( current_process_id === message_id ) {
				current_process_id = false;
			}

		}

		if ( process_number > 0 ) {
			process_number = process_number - 1;

			if ( process_number === 0 ) {
				removeProgressBar();
			}

		} else {
			if ( loading_box ) {
				doing_close = true;
				clearTimeout( close_time );

				//shorten timeout in unit test mode.
				var close_time_timeout = Global.UNIT_TEST_MODE ? 10 : 500;
				close_time = setTimeout( function() {
					closeOverlay();

					if ( second_timer ) {
						clearInterval( second_timer );
						second_timer = null;
					}

					timer = null;
					temp_message_until_close = '';
					if ( loading_box ) {
						loading_box.css( 'display', 'none' );

						if ( get_progress_timer ) {
							clearInterval( get_progress_timer );
							get_progress_timer = false;
						}

						if ( start_progress_timer ) {
							clearInterval( start_progress_timer );
							start_progress_timer = false;
						}

					}

					TTPromise.resolve( 'ProgressBar', 'MASTER' );
				}, close_time_timeout );
			}
		}

	};

	var showNanobar = function() {
		if ( !nanoBar ) {
			var options = {
				bg: 'red',
				id: 'nano-bar'
			};
			nanoBar = new Nanobar( options );
		}
		var percentage = 0;
		loading_bar_time && clearInterval( loading_bar_time );
		loading_bar_time = setInterval( function() {
			if ( percentage < 80 ) {
				percentage = percentage + 20;
				nanoBar.go( percentage );
			} else if ( percentage === 80 ) {
				percentage = percentage + 10;
				nanoBar.go( percentage );
			}

		}, 1000 );
	};

	var removeNanobar = function() {
		loading_bar_time && clearInterval( loading_bar_time );
		nanoBar && nanoBar.go( 100 );
		closeOverlay();
	};

	return {
		showProgressBar: showProgressBar,
		removeProgressBar: removeProgressBar,
		showOverlay: showOverlay,
		closeOverlay: closeOverlay,
		message_id_dic: message_id_dic,
		changeProgressBarMessage: changeProgressBarMessage,
		cancelProgressBar: cancelProgressBar,
		showNanobar: showNanobar,
		removeNanobar: removeNanobar,
		noProgressForNextCall: noProgressForNextCall
	};

} )();