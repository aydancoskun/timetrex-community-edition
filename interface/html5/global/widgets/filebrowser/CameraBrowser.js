( function( $ ) {

	$.fn.CameraBrowser = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.CameraBrowser.defaults, options );

		var $this = this;
		var field;

		var enabled = true;
		var video = null;
		var canvas = null;

		var local_stream = null;

		this.stopCamera = function() {

			if ( local_stream ) {
				if ( local_stream.stop ) {
					// This is the legacy method to stop video.
					local_stream.stop();
				} else if ( local_stream.getTracks ) {
					// This is the modern approach for stopping the video. https://developer.mozilla.org/en-US/docs/Web/API/MediaStreamTrack/stop
					local_stream.getTracks().forEach( track => track.stop() );
				}
			}
		};

		this.showCamera = function() {

			// check for getUserMedia support
			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;
			if ( navigator.mediaDevices && navigator.mediaDevices.getUserMedia ) {
				// Most up to date as of May 2020 (Aside from using async and await) https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia

				// get webcam feed if available
				navigator.mediaDevices.getUserMedia( { video: true } )
					.then(function(stream) {
						if ('srcObject' in video) {
							video.srcObject = stream;
						} else {
							// Fallback for older browsers. https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/srcObject#Supporting_fallback_to_the_src_property
							video.src = URL.createObjectURL( stream );
						}
						video.play();
						local_stream = stream;
					})
					.catch(function(err) {
						errorBack();
					});
			} else if ( navigator.getUserMedia ) {
				// Semi-deprecated, legacy, but still works. https://developer.mozilla.org/en-US/docs/Web/API/Navigator/getUserMedia

				// get webcam feed if available
				navigator.getUserMedia( { video: true }, function( stream ) {
					if ('srcObject' in video) {
						video.srcObject = stream;
					} else {
						// Fallback for older browsers. https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/srcObject#Supporting_fallback_to_the_src_property
						video.src = URL.createObjectURL( stream );
					}
					video.play();
					local_stream = stream;
				}, errorBack );
			} else if ( navigator.webkitGetUserMedia ) { // WebKit-prefixed
				navigator.webkitGetUserMedia( { video: true }, function( stream ) {
					video.src = window.webkitURL.createObjectURL( stream );
					video.play();
					local_stream = stream;
				}, errorBack );
			} else if ( navigator.mozGetUserMedia ) { // Firefox-prefixed
				navigator.mozGetUserMedia( { video: true }, function( stream ) {
					video.src = window.URL.createObjectURL( stream );
					video.play();
					local_stream = stream;
				}, errorBack );
			} else {
				errorBack();
			}

			function errorBack() {
				TAlertManager.showAlert( $.i18n._( 'Unable to access Camera.<br><br>Please check your camera connections, permissions, and ensure you are using HTTPS. Alternatively, use the File upload method instead.' ) );
			}
		};

		this.setEnable = function( val ) {
			enabled = val;

			var btn = this.children().eq( 1 );

			if ( !val ) {
				btn.attr( 'disabled', true );
				btn.removeClass( 'disable-element' ).addClass( 'disable-element' );
			} else {
				btn.removeAttr( 'disabled' );
				btn.removeClass( 'disable-element' );
			}

		};

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {
			return false;
		};

		this.getFileName = function() {
			return 'camera_stream.png';
		};

		this.getImageSrc = function() {
			return canvas[0].toDataURL();
		};

		this.setImage = function( val ) {
			var image = $this.children().eq( 0 );

			if ( !val ) {
				image.attr( 'src', '' );
				image.hide();
				return;
			}

			var d = new Date();
			image.hide();
			image.attr( 'src', val + '&t=' + d.getTime() );
			image.css( 'height', 'auto' );
			image.css( 'width', 'auto' );

		};

		this.onImageLoad = function( image ) {

//			var image_height = $( image ).height() > 0 ? $( image ).height() : image.naturalHeight;
//			var image_width = $( image ).width() > 0 ? $( image ).width() : image.naturalWidth;
//
//			if ( image_height > default_height ) {
//				$( image ).css( 'height', default_height );
//
//			}
//
//			if ( image_width > default_width ) {
//				$( image ).css( 'width', default_width );
//
//				$( image ).css( 'height', 'auto' );
//			}
//
//			$this.trigger( 'setSize' );

			$( image ).show();

		};

		this.setValue = function( val ) {

			if ( !val ) {
				val = '';
			}

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			var $$this = this;

			video = $( this ).children().eq( 0 ).children().eq( 0 )[0];
			canvas = $( this ).children().eq( 0 ).children().eq( 1 );

			var take_picture = $( this ).children().eq( 1 ).children().eq( 0 );
			var try_again = $( this ).children().eq( 1 ).children().eq( 1 );

			// Set initial states of the buttons.
			take_picture.prop( 'disabled', false );
			try_again.prop( 'disabled', true );

			take_picture.bind( 'click', function() {
				take_picture.prop( 'disabled', true );
				try_again.prop( 'disabled', false );
				// Global.glowAnimation.start(); // not needed here as its triggered in UserPhotoWizardController.buildCurrentStepUI()

				// flash the photo area to indicate a picture has been taken.
				canvas.parent().addClass( 'flash' );

				setTimeout( function(){
					canvas.parent().removeClass( 'flash' );
				}, 1000);	// Timeout must be the same length as the CSS3 transition or longer (or you'll mess up the transition)

				// handle picture taking
				var ctx = canvas[0].getContext( '2d' );
				ctx.drawImage( video, 0, 0, 400, 300 );
				canvas.css( 'z-index', 51 );

				$this.trigger( 'change', [$this] );
			} );

			try_again.bind( 'click', function() {
				take_picture.prop( 'disabled', false );
				try_again.prop( 'disabled', true );
				Global.glowAnimation.stop();

				canvas.css( 'z-index', -1 );

				$this.trigger( 'NoImageChange', [$this] );
			} );

		} );

		return this;

	};

	$.fn.CameraBrowser.defaults = {};

} )( jQuery );