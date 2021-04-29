( function( $ ) {

	$.fn.TText = function( options ) {
		var opts = $.extend( {}, $.fn.TText.defaults, options );

		var $this = this;
		var field;

		this.clearErrorStyle = function() {

		};

		this.setClassStyle = function( style ) {
			if ( style ) {
				this.css( style );
			}
		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {
//			return	$this.val();
			return $this.text();
		};

		this.setValue = function( val ) {
			if ( !val && val !== 0 ) {
				val = $.i18n._( 'N/A' );
			}

			val = Global.decodeCellValue( val );
			$this.html( ( val ) );

			this.setResizeEvent();
		};

		this.setResizeEvent = function() {
			$this.height( 'auto' );

			//Set label size if there is new lines in contents
			//if set value before add widget to UI, the height is 0, get the height so the event can be correct
			if ( $this.height() === 0 ) {
				var temp_span = $this.clone();
				$( 'body' ).append( temp_span );
				$this.height( temp_span.height() );
				temp_span.remove();

			}

			$this.trigger( 'setSize' );
		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			if ( o.selected_able ) {
				$( this ).addClass( 't-text-selected-able' );
			}

		} );

		return this;

	};

	$.fn.TText.defaults = {};

} )( jQuery );