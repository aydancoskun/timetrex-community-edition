(function( $ ) {

	$.fn.TFeedback = function( options ) {
		var opts = $.extend( {}, $.fn.TFeedback.defaults, options );
		var message_container;
		var $this = this;
		var feedback_rating;
		var message_box;
		this.api = null;
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();

		this.removeMessageContainer = function() {
			if ( Global.isSet( message_container ) ) {
				message_container.remove();
			}
		};

		this.saveIconSelection = function(){
			var message = message_box.val();
			$this.api['setUserFeedbackRating']( feedback_rating, message, {onResult:function(res){
				if ( res.isValid() ) {
					$this.parent().find('img' ).each(function() {
						$(this ).removeClass('current' ).attr( 'src', $(this ).attr('src').replace(/^(.*\/)[^\/]+$/, '$1') + $(this ).attr('alt') + '.png' );
					});
					$this.addClass('current' ).attr( 'src', $this.attr('src').replace(/^(.*\/)[^\/]+$/, '$1') + $this.attr('alt') + '_light.png' );
					$this.removeMessageContainer();
					$this.user_api['getUser']( {filter_data: {id: LocalCacheData.getLoginUser().id}}, {onResult:function(res) {
						if ( res.isValid() ) {
							LocalCacheData.setLoginUser( res.getResult()[0] );
						}
					}})
				}
			}} );
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			$this.api = new (APIFactory.getAPIClass( 'APIUser' ))();
			message_container = Global.loadWidgetByName( FormItemType.FEEDBACK_BOX );

			message_box = message_container.find('.feedback-messagebox');
			feedback_rating = $this.attr('data-feedback');

			if ( $(this ).attr('alt') == 'happy' ) {
				$( message_container.find('.title' ) ).text( $.i18n._( 'Glad to hear that you are happy with your TimeTrex experience! But we don\'t want to rest on our laurels, so let us know what we are doing right, or what we can do to make further improvements, we will listen, promise.' ) )
				message_container.css('border-top-width', 70);
				$( message_container.find('.title' ) ).css('top', -60);
			} else if ( $(this ).attr('alt') == 'neutral' ) {
				$( message_container.find('.title' ) ).text( $.i18n._( 'Sorry to hear that you are not satisfied with your TimeTrex experience, please let us know how we can improve, we will listen, promise.' ) )
				message_container.css('border-top-width', 50);
				$( message_container.find('.title' ) ).css('top', -40);
			} else if ( $(this ).attr('alt') == 'sad' ) {
				message_container.css('border-top-width', 50);
				$( message_container.find('.title' ) ).css('top', -40);
				$( message_container.find('.title' ) ).text( $.i18n._( 'Oh no! Sorry to hear that you are unhappy with your TimeTrex experience, please let us know how we can improve, we will listen, promise.' ) )
			}

			message_container.find('.sendButton' ).bind('click', $this.saveIconSelection);

			message_container.find('.cancelButton' ).bind('click', $this.saveIconSelection);

			if ( $('body' ).children('.message-container' ).length == 0 ) {
				$('body' ).append( message_container );
			}


		} );

		return this;

	};

	$.fn.TFeedback.defaults = {

	};

})( jQuery );