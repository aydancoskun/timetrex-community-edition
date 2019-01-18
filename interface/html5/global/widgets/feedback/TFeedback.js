(function( $ ) {

	$.fn.TFeedback = function( options ) {
		var opts = $.extend( {}, $.fn.TFeedback.defaults, options );
		var message_container;
		var $this = this;
		var feedback_rating;
		var message_box;
		var message_email;
		var message_phone;
		this.api = null;
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.feedback_submitted = false;

		this.removeMessageContainer = function() {
			if ( Global.isSet( message_container ) ) {
				message_container.remove();
			}
		};

		this.saveIconSelection = function() {
			if ( this.feedback_submitted == false || this.feedback_submitted == undefined ) {
				this.feedback_submitted = true;
				var message = '';
				if ( message_box.val().length > 0 ) {
					message = message_box.val() + '\nEmail: ' + message_email.val() + '\nPhone: ' + message_phone.val();
				}
				$this.api['setUserFeedbackRating']( feedback_rating, message, {
					onResult: function( res ) {
						if ( res.isValid() ) {
							$this.parent().find( 'img' ).each( function() {
								$( this ).removeClass( 'current' ).attr( 'src', $( this ).attr( 'src' ).replace( /^(.*\/)[^\/]+$/, '$1' ) + $( this ).attr( 'alt' ) + '.png' );
							} );
							$this.addClass( 'current' ).attr( 'src', $this.attr( 'src' ).replace( /^(.*\/)[^\/]+$/, '$1' ) + $this.attr( 'alt' ) + '_light.png' );
							$this.removeMessageContainer();
							$this.user_api['getUser']( { filter_data: { id: LocalCacheData.getLoginUser().id } }, {
								onResult: function( res ) {
									if ( res.isValid() ) {
										LocalCacheData.setLoginUser( res.getResult()[0] );
									}
								}
							} );
						}
					}
				} );
			}
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			$this.api = new (APIFactory.getAPIClass( 'APIUser' ))();

			var current_user_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
			var user = current_user_api.getCurrentUser( { async: false } );
			user = user.getResult();
			var user_email;
			if ( user.work_email != false && user.work_email != '' ) {
				user_email = user.work_email;
			} else if ( user.home_email != false ) {
				user_email = user.home_email;
			}
			var user_phone;
			if ( user.work_phone != false && user.work_phone != '' ) {
				user_phone = user.work_phone;
			} else if ( user.home_phone != false ) {
				user_phone = user.home_phone;
			}
			message_container = Global.loadWidgetByName( FormItemType.FEEDBACK_BOX );

			message_box = message_container.find( '.feedback-messagebox' );
			message_email = message_container.find( '.feedback-email' );
			message_phone = message_container.find( '.feedback-phone' );

			message_email.val( user_email );
			message_phone.val( user_phone );

			feedback_rating = $this.attr( 'data-feedback' );

			if ( $( this ).attr( 'alt' ) == 'happy' ) {
				$( message_container.find( '.title' ) ).text( $.i18n._( 'Glad to hear that you are happy with your TimeTrex experience! But we don\'t want to rest on our laurels, so let us know what we are doing right, or what we can do to make further improvements, we will listen, promise.' ) );
			} else if ( $( this ).attr( 'alt' ) == 'neutral' ) {
				$( message_container.find( '.title' ) ).text( $.i18n._( 'Sorry to hear that you are not satisfied with your TimeTrex experience, please let us know how we can improve, we will listen, promise.' ) );
			} else if ( $( this ).attr( 'alt' ) == 'sad' ) {
				$( message_container.find( '.title' ) ).text( $.i18n._( 'Oh no! Sorry to hear that you are unhappy with your TimeTrex experience, please let us know how we can improve, we will listen, promise.' ) );
			}

			message_container.find( '.sendButton' ).html( $.i18n._( 'Send' ) );
			message_container.find( '.cancelButton' ).html( $.i18n._( 'Cancel' ) );
			message_container.find( '.top-bar-title' ).html( $.i18n._( 'Feedback' ) );
			message_container.find( '.email-label-text' ).html( $.i18n._( 'Email' ) );
			message_container.find( '.phone-label-text' ).html( $.i18n._( 'Phone' ) );
			message_container.find( '.contact-notice-text' ).html( $.i18n._( 'If you would like to be contacted regarding your feedback, please provide' ) + ':' );

			message_container.find( '.sendButton' ).bind( 'click', $this.saveIconSelection );

			message_container.find( '.cancelButton' ).bind( 'click', $this.removeMessageContainer );

			if ( $( 'body' ).children( '.message-container' ).length == 0 ) {
				$( 'body' ).append( message_container );
			}

		} );

		return this;

	};

	$.fn.TFeedback.defaults = {};

})( jQuery );