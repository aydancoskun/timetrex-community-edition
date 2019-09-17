( function ( $ ) {

	$.fn.TFeedback = function ( _options ) {
		var options = $.extend( {
			source: '',
			force_source: false, // does not prepend viewId to source value
			delay: 0,
			manual_trigger: false,
			prompt_for_feedback: false, // Default is false so that only manual trigger, custom functions, or server input will make the feedback popup appear.
			review_link: 'https://www.timetrex.com/r?id=review&product_edition_id='+ Global.getProductEdition()
		}, _options );

		// DOM references for containers and pages
		var feedback_container = Global.loadWidgetByName( FormItemType.FEEDBACK_BOX ); // Note: There is a .feedback-overlay div at the root of feedback_container controlled by CSS.
		var page_title = feedback_container.find( '.top-bar-title' );
		var all_pages = feedback_container.find( '.feedback-page' );
		var default_page = feedback_container.find( '.feedback-page.default' );
		var positive_page = feedback_container.find( '.feedback-page.positive' );
		var negative_page = feedback_container.find( '.feedback-page.negative' );

		var api = new ( APIFactory.getAPIClass( 'APIUser' ) )();

		var feedback = {
			POSITIVE: 'postitive',
			NEUTRAL: 'neutral',
			NEGATIVE: 'negative'
		};

		function init() {

			// check the feedback prompt status set by API
			if ( LocalCacheData.getLoginUser() && LocalCacheData.getLoginUser().prompt_for_feedback == true ) {
				options.prompt_for_feedback = true;
				LocalCacheData.getLoginUser().prompt_for_feedback = false;
				// TODO: API Call to save the new value
			}

			if ( options.prompt_for_feedback ) {
				// Append current view id to the source
				if ( !options.force_source ) {
					options.source = LocalCacheData.current_open_view_id + '@' + options.source;
				}

				// Initialise the default page (not visible until showFeedbackContainer() is triggered.
				showPage( 'default' );

				// Display feedback dialog either immediately or with a delay
				if( options.delay && options.delay > 0 ) {
					delayShowFeedbackContainer( options.delay );
				} else {
					showFeedbackContainer();
				}
			}
		}

		function getFeedbackType() {
			if ( options.manual_trigger ) {
				return 'click';
			} else {
				return 'popup';
			}
		}

		/**
		 *
		 * @param {number} duration - number of milliseconds to delay the feedback popup from showing.
		 */
		function delayShowFeedbackContainer( duration ) {
			duration = duration || 0;
			Debug.Text( 'Setting feedback display delay to ' + duration, 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
			setTimeout(function() {
				Debug.Text( 'Triggering delayed feedback display', 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
				showFeedbackContainer();
			}, duration );
		}

		function showFeedbackContainer() {
			if ( $( '.feedback-container' ).length == 0 ) {
				$( 'body' ).append( feedback_container );
			} else {
				Debug.Text( 'ERROR: Feedback container already exists, halting to prevent duplicate popups.', 'TFeedback.js', 'TFeedback', 'initDefaultPage', 1 );
			}
		}

		function removeFeedbackContainer() {
			if ( Global.isSet( feedback_container ) ) {
				feedback_container.remove();
			}
		}

		function initDefaultPage() {
			page_title.html( $.i18n._( 'Feedback' ) );
			default_page.find( '.page-text' ).text( $.i18n._( 'Tell us what you think about TimeTrex?' ) );

			default_page.find( '.positive-button' )
				.html( $.i18n._( 'It\'s great!' ) )
				.bind( 'click', function () {
					showPage( 'positive' );
					Debug.Text( 'Feedback Analytics: Category: feedback, Action: ' + getFeedbackType() + ', Label: ' + getFeedbackType() + ':feedback:' + options.source + ':' + feedback.POSITIVE, 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
					Global.sendAnalyticsEvent( 'feedback', getFeedbackType(), getFeedbackType() + ':feedback:' + options.source + ':' + feedback.POSITIVE );
					sendDataToFeedbackAPI( feedback.POSITIVE, '' );
				} );

			default_page.find( '.negative-button' )
				.html( $.i18n._( 'Not so great' ) )
				.bind( 'click', function () {
					showPage( 'negative' );
					Debug.Text( 'Feedback Analytics: Category: feedback, Action: ' + getFeedbackType() + ', Label: ' + getFeedbackType() + ':feedback:' + options.source + ':' + feedback.NEGATIVE, 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
					Global.sendAnalyticsEvent( 'feedback', getFeedbackType(), getFeedbackType() + ':feedback:' + options.source + ':' + feedback.NEGATIVE );
					sendDataToFeedbackAPI( feedback.NEGATIVE, '' );
				} );

			var cancel_text;
			if ( options.manual_trigger ) {
				cancel_text = $.i18n._( 'Close' );
			} else {
				var cancel_text = $.i18n._( 'Ask me later' );
			}

			default_page.find( '.cancel-button' )
				.html( cancel_text )
				.click( function () {
					removeFeedbackContainer();
					Debug.Text( 'Feedback Analytics: Category: feedback, Action: ' + getFeedbackType() + ', Label: ' + getFeedbackType() + ':feedback:' + options.source + ':' + feedback.NEUTRAL, 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
					Global.sendAnalyticsEvent( 'feedback', getFeedbackType(), getFeedbackType() + ':feedback:' + options.source + ':' + feedback.NEUTRAL );
					sendDataToFeedbackAPI( feedback.NEUTRAL, '' );
				} );

			Debug.Text( 'Feedback Analytics: Category: feedback, Action: ' + getFeedbackType() + ', Label: ' + getFeedbackType() + ':feedback:' + options.source, 'TFeedback.js', 'TFeedback', 'initDefaultPage', 10 );
			Global.sendAnalyticsEvent( 'feedback', getFeedbackType(), getFeedbackType() + ':feedback:' + options.source );
		}

		function initPositivePage() {
			var feedback_rating = feedback.POSITIVE;
			page_title.html( $.i18n._( 'Feedback' ) );
			positive_page.find( '.page-text.block1' ).text( $.i18n._( 'It thrills us to hear you think TimeTrex is great! ' ) );
			positive_page.find( '.page-text.block2' ).text( $.i18n._( 'Share your experience and WIN a tasty lunch for your team!' ) );
			positive_page.find( '.page-text.block3' ).text( $.i18n._( 'We’ll select one winner each month. ' ) );
			positive_page.find( '.openReviewPageButton' )
				.html( $.i18n._( 'Share experience' ) )
				.bind( 'click', function () {
					Debug.Text( 'Feedback Analytics: Category: feedback, Action: ' + getFeedbackType() + '-Link, Label: submit:feedback:' + options.source + ':' + feedback_rating, 'TFeedback.js', 'TFeedback', 'initPositivePage', 10 );
					Global.sendAnalyticsEvent( 'feedback', getFeedbackType() + '-link', 'submit:' + getFeedbackType() + ':feedback:' + options.source + ':' + feedback_rating );
					sendDataToFeedbackAPI( feedback_rating, '', false );
					triggerFeedbackReviewAPI( 1, true );
					window.open( options.review_link, '_blank', 'review_link' );
				} );
			positive_page.find( '.cancel-button' )
				.html( $.i18n._( 'I\'m not hungry' ) ) //Skip the Lunch
				.click( function () {
					triggerFeedbackReviewAPI( 0, true );
					Debug.Text( 'Feedback: Category: feedback, Action: cancel, Label: cancel:feedback:' + options.source + ':' + feedback_rating, 'TFeedback.js', 'TFeedback', 'cancelButtonClick', 10 );
					Global.sendAnalyticsEvent( 'feedback', 'cancel', 'cancel:feedback:' + options.source + ':' + feedback_rating );
				} );
		}

		function initNegativePage() {
			var feedback_rating = feedback.NEGATIVE;
			var user_contact = getUserContactDetails();
			var form_messagebox = negative_page.find( '.feedback-messagebox' );
			var form_email = negative_page.find( '.feedback-email' );
			var form_phone = negative_page.find( '.feedback-phone' );

			page_title.html( $.i18n._( 'Feedback' ) );
			negative_page.find( '.page-text' ).html( $.i18n._( 'We’re all ears!<br>What improvements do you think we should make?' ) );
			negative_page.find( '.contact-notice-text' ).html( $.i18n._( 'What is the best way to contact you?' ) );
			negative_page.find( '.email-label-text' ).html( $.i18n._( 'Email' ) );
			negative_page.find( '.phone-label-text' ).html( $.i18n._( 'Phone' ) );
			form_email.val( user_contact.user_email );
			form_phone.val( user_contact.user_phone );

			negative_page.find( '.sendButton' )
				.html( $.i18n._( 'Send' ) )
				.click( _sendForm );
			negative_page.find( '.cancel-button' )
				.html( $.i18n._( 'Back' ) )
				.click( function () {
					showPage( 'default' );
					Debug.Text( 'Feedback: Category: feedback, Action: cancel, Label: cancel:feedback:' + options.source + ':' + feedback_rating, 'TFeedback.js', 'TFeedback', 'cancelButtonClick', 10 );
					Global.sendAnalyticsEvent( 'feedback', 'cancel', 'cancel:feedback:' + options.source + ':' + feedback_rating );
				} );

			function _sendForm() {
				var message = '';
				if ( form_messagebox.val().length > 0 ) {
					message = form_messagebox.val() + '\nEmail: ' + form_email.val() + '\nPhone: ' + form_phone.val();
				}
				Debug.Text( 'Feedback Analytics: Category: feedback, Action: submit, Label: submit:feedback:' + options.source + ':' + feedback_rating, 'TFeedback.js', 'TFeedback', 'initNegativePage._sendForm', 10 );
				Global.sendAnalyticsEvent( 'feedback', 'submit', 'submit:feedback:' + options.source + ':' + feedback_rating );

				sendDataToFeedbackAPI( feedback_rating, message, true );
			}
		}

		function showPage( page ) {
			// Set i18n translation text and pre-populate any data fields for requested page, then load page.
			switch ( page ) {
				case 'positive':
					initPositivePage();
					all_pages.hide();
					positive_page.show();
					break;
				case 'negative':
					initNegativePage();
					all_pages.hide();
					negative_page.show();
					break;
				default:
					initDefaultPage();
					all_pages.hide();
					default_page.show();
			}
		}

		function getUserContactDetails() {
			var current_user_api = new ( APIFactory.getAPIClass( 'APICurrentUser' ) )();
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

			return {
				user_email: user_email,
				user_phone: user_phone
			};
		}

		function sendDataToFeedbackAPI( feedback_rating, message, close_window ) {
			if ( options.source ) {
				message += '\n\nFeedback source: ' + options.source;
			}

			api.setUserFeedbackRating( feedback_rating, message, {
				onResult: function ( res ) {
					if ( res.isValid() ) {
						if ( close_window ) {
							removeFeedbackContainer();
						}
					}
				}
			} );
		}

		function triggerFeedbackReviewAPI( review_state, close_window ) {
			api.setUserFeedbackReview( review_state, {
				onResult: function ( res ) {
					if ( res.isValid() ) {
						if ( close_window ) {
							removeFeedbackContainer();
						}
					}
				}
			} );
		}

		// this.each is typical jQuery format to apply the actions to all elements in the jQuery selector. In this case, not needed as we attach manually to the body tag, and won't work on empty selectors.
		// this.each( function() {
		// 	init();
		// } );

		init();

		return this;
	};

} )( jQuery );
