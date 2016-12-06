(function( $ ) {

	$.fn.TColorPicker = function( options ) {
		var opts = $.extend( {}, $.fn.TColorPicker.defaults, options );
		var $this = this;
		var field;
		var error_string = '';
		var error_tip_box;
		var mass_edit_mode = false;
		var check_box = null;
		var enabled = true;
		var color_picker_instance;

		this.clearErrorStyle = function() {

		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
			} else {
				$this.removeAttr( 'disabled' );
			}

		};

		this.setCheckBox = function( val ) {
			if ( check_box ) {
				check_box.children().eq( 0 )[0].checked = val;
			}
		};

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.children().eq( 0 )[0].checked === true ) {
					return true;
				}
			}

			return false;
		};

		this.setMassEditMode = function( val ) {
			mass_edit_mode = val;

			if ( mass_edit_mode ) {
				check_box = $( ' <div class="mass-edit-checkbox-wrapper checkbox-mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox" />' +
				'<label for="checkbox-input-1" class="input-helper input-helper--checkbox"></label></div>' );
				check_box.insertBefore( $( this ) );
				check_box.change( function() {
					$this.trigger( 'formItemChange', [$this] );
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.setErrorStyle = function( errStr, show, isWarning ) {
			if ( isWarning ) {
				$( this ).addClass( 'warning-tip' );
			} else {
				$( this ).addClass( 'error-tip' );
			}
			error_string = errStr;

			if ( show ) {
				this.showErrorTip();
			}
		};

		this.showErrorTip = function( sec ) {
			if ( !Global.isSet( sec ) ) {
				sec = 2;
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox();
			}
			error_tip_box.cancelRemove();
			if ( $( this ).hasClass( 'warning-tip' ) ) {
				error_tip_box.show( this, error_string, sec, true );
			} else {
				error_tip_box.show( this, error_string, sec );
			}
		};

		this.hideErrorTip = function() {
			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}
		};

		this.clearErrorStyle = function() {
			$( this ).removeClass( 'error-tip' );
			$( this ).removeClass( 'warning-tip' );
			this.hideErrorTip();
			error_string = '';
		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {
			return color_picker_instance.val().substring( 1 );
		};

		this.setValue = function( val ) {
			val && color_picker_instance.val( '#' + val.toUpperCase() ) && color_picker_instance.css( "background", '#' + val );
			!val && color_picker_instance.val( '#0F820F' ) && color_picker_instance.css( "background", '#0F820F' );
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			field = o.field;
			//var is_open;
			if ( $( '.cp-color-picker' ).length > 0 ) {
				$().colorPicker.destroy();
			}
			// A workaround that the $this in renderCallback is not correct.
			$( this ).unbind( 'colorPickerClose' ).bind( 'colorPickerClose', function() {
				$this.trigger( 'formItemChange', [$this] );
			} );
			color_picker_instance = $( this ).colorPicker( {
				doRender: 'div div',
				renderCallback: function( $elm, toggled ) {

					var self = this;
					//var colors = this.color.colors,
					//	rgb = colors.RND.rgb;

					var colors = this.color.colors.RND,
						modes = {
							r: colors.rgb.r, g: colors.rgb.g, b: colors.rgb.b,
							h: colors.hsv.h, s: colors.hsv.s, v: colors.hsv.v,
							HEX: this.color.colors.HEX
						};

					$elm.each( function() {
						this.value = '#'+modes[this.className.substr(3)];
					} )

					$elm.unbind('focusout').bind('focusout', function(e) {
						self.toggle(false);
						if ( check_box ) {
							$this.setCheckBox( true );
						}
						$(this).trigger( 'colorPickerClose' );

					})

					//if ( toggled === true ) {
					//	is_open = true;
					//	return;
					//}

					//if ( is_open && toggled === false ) {
					//	is_open = false;
					//	if ( check_box ) {
					//		$this.setCheckBox( true );
					//	}
					//	$elm.trigger( 'colorPickerClose' );
					//
					//}


				}
			} );

		} );

		return this;

	};

	$.fn.TColorPicker.defaults = {};

})( jQuery );