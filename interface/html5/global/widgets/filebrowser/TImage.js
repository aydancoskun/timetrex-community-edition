(function( $ ) {

	$.fn.TImage = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImage.defaults, options );

		var $this = this;
		var field;

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {
			return null;
		};

		this.setValue = function( val ) {
			if ( !val ) {
				this.attr( 'src', '' );
				return;
			}
			var d = new Date();
			this.attr( 'src', val + '&t=' + d.getTime() );

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

		} );

		return this;

	};

	$.fn.TImage.defaults = {};

	$( document ).on( 'mouseover', '.file-browser img', function( e ) {
		var $this_image_widget = $( e.target ).parents( '.file-browser' );

		if ( !$( '.file_browser_overlay' )[0] && $( e.target ).attr( 'enable-delete' ) == 1 ) {
			var height = $( e.target ).height();
			var top = (height - 32) / 2;
			var left = top;

			var file_browser_overlay = $( '<div class="file_browser_overlay"><img src="theme/default/images/delete-512.png" style="position:absolute;width:32px;height:32px;top:' + top + 'px;left:' + left + 'px;"></div>' );
			file_browser_overlay.css( 'position', 'absolute' );
			file_browser_overlay.css( 'top', '0px' );
			file_browser_overlay.css( 'left', '0' );
			file_browser_overlay.css( 'cursor', 'pointer' );
			file_browser_overlay.css( 'height', height + 'px' );
			file_browser_overlay.css( 'width', '100%' );
			file_browser_overlay.css( 'background', 'rgba(255,255,255,0.85)' );

			$( e.target ).parents( '.file-browser' ).append( file_browser_overlay );

			$( document ).on( 'click', '.file_browser_overlay', function( e ) {
				var img_src = $( e.target ).parent().find( 'img' ).attr( 'src' );
				TAlertManager.showConfirmAlert( $.i18n._( 'This will permanently delete the image. Are you sure?' ), '', function( flag ) {
					if ( flag ) {
						var e = { type: 'deleteClick', message: 'Delete image clicked.', time: new Date() };
						$this_image_widget.trigger( e );
					}
				} );
			} );

			$( document ).on( 'mouseleave', '.file-browser', function() {
				$( document ).off( 'click', '.file_browser_overlay' );
				if ( $( '.file_browser_overlay' )[0] ) {
					var file_browser_overlay = $( this ).find( '.file_browser_overlay' );
					file_browser_overlay.off().remove();
				}
			} );
		}
	} );


})( jQuery );