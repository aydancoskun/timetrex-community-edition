(function( $ ) {

	$.fn.TTimePicker = function( options ) {
		var opts = $.extend( {}, $.fn.TTimePicker.defaults, options );
		//Global.addCss( 'global/widgets/timepicker/TTimePicker.css' );
		var $this = this;
		var field;
		var validation_field;
		var time_picker_input;
		var icon;
		var error_string = '';
		var error_tip_box;
		var mass_edit_mode = false;
		var check_box = null;
		var enabled = true;
		var is_open = false;
		var focus_out_timer;
		var can_open = false; //default when the calender can be open, we only open it when click on the icon
		var is_static_width = false;
		var stepMinute = 15;

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				time_picker_input.addClass( 't-time-picker-readonly' );
				icon.css( 'display', 'none' );
				time_picker_input.attr( 'readonly', 'readonly' );
			} else {
				time_picker_input.removeClass( 't-time-picker-readonly' );
				icon.css( 'display', 'inline' );
				time_picker_input.removeAttr( 'readonly' );
			}
		};

		this.setCheckBox = function( val ) {
			check_box.children().eq( 0 )[0].checked = val;
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
				check_box = $( ' <div class="mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox" />' +
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
				time_picker_input.addClass( 'warning-tip' );
			} else {
				time_picker_input.addClass( 'error-tip' );
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
			if ( time_picker_input.hasClass( 'warning-tip' ) ) {
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

		// Error: TypeError: time_picker_input is undefined in /interface/html5/global/widgets/datepicker/TTimePicker.js?v=8.0.3-20150313-161037 line 122
		this.clearErrorStyle = function() {
			if ( !time_picker_input ) {
				return;
			}
			time_picker_input.removeClass( 'error-tip' );
			time_picker_input.removeClass( 'warning-tip' );
			this.hideErrorTip();
			error_string = '';
		};

		this.getField = function() {
			return field;
		};

		this.getValidationField = function() {
			return validation_field;
		};

		this.getDefaultFormatValue = function() {
			var val = time_picker_input.val();

			val = Global.strToDate( val ).format( 'YYYYMMDD' );

			return val;
		};

		this.setPlaceHolder = function( val ) {
			time_picker_input.attr( 'placeholder', val );
		};

		this.getValue = function() {
			return time_picker_input.val();
		};

		this.setValue = function( val ) {
			//Error: Uncaught TypeError: Cannot read property 'val' of undefined in /interface/html5/global/widgets/datepicker/TTimePicker.js?v=8.0.0-20141230-130626 line 144
			if ( !time_picker_input ) {
				return;
			}
			if ( !val ) {
				val = '';
			}
			time_picker_input.val( val );
			this.autoResize();
		};

		this.setDefaultWidgetValue = function() {
			if ( $( this ).attr( 'widget-value' ) ) {
				this.setValue( $( this ).attr( 'widget-value' ) );
			}
		};

		this.autoResize = function() {
			var content_width, example_width;
			if ( !is_static_width ) {
				example_width = Global.calculateTextWidth( LocalCacheData.getLoginUserPreference().time_format_display );
				content_width = Global.calculateTextWidth( time_picker_input.val(), { min_width: example_width, max_width: (example_width + 100), padding: 28 } );
				$this.width( content_width + 'px' );
			}
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			var time_format = 'h:mm TT';
			if ( LocalCacheData.getLoginUserPreference() ) {
				time_format = LocalCacheData.getLoginUserPreference().time_format_1;
			}
			field = o.field;
			if ( o.validation_field ) {
				validation_field = o.validation_field;
			}
			icon = $( this ).find( '.t-time-picker-icon' );
			time_picker_input = $( this ).find( '.t-time-picker' );
			icon.attr( 'src', Global.getRealImagePath( 'images/time.png' ) );
			icon.bind( 'mouseup', function() {
				if ( !enabled ) {
					return;
				}
				if ( !is_open ) {
					time_picker_input.timepicker( 'show' );
					is_open = true;
				} else {
					is_open = false;
					if ( focus_out_timer ) {
						clearTimeout( focus_out_timer );
						focus_out_timer = null;
					}
				}
			} );

			if ( o.stepMinute ) {
				stepMinute = o.stepMinute;
			}

			var close_text = $.i18n._( 'Close' );
			time_picker_input = time_picker_input.timepicker( {
				showMillisec: false,
				showMicrosec: false,
				showTimezone: false,
				showHeader: false,
				timeFormat: time_format,
				showOn: '',
				stepMinute: stepMinute,
				closeText: close_text,
				showAnim: '',
				beforeShow: function() {
					if ( o.beforeShow ) {
						o.beforeShow();
					}
				},
				onClose: function() {
					focus_out_timer = setTimeout( function() {
						is_open = false;
						$this.autoResize();
						if ( o.onClose ) {
							o.onClose();
						}
					}, 100 );
				}
			} );
			$this.setPlaceHolder( LocalCacheData.loginUserPreference.time_format_display );

			//hack to stop automatic rounding of typed dates to stepMinute increment
			time_picker_input.off( 'keyup' ).off( 'keydown' ).off( 'keypress' );

			time_picker_input.off( 'change' ).on( 'change', function( e ) {
				if ( check_box ) {
					$this.setCheckBox( true );
				}
				$this.trigger( 'formItemChange', [$this] );
				$this.autoResize();
			} );
			time_picker_input.mouseover( function() {
				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}
			} );
			time_picker_input.mouseout( function() {
				if ( !$( $this ).is( ':focus' ) ) {
					$this.hideErrorTip();
				}
			} );

			time_picker_input.focusin( function( e ) {
				if ( !enabled ) {
					if ( !check_box ) {
						if ( LocalCacheData.current_open_sub_controller &&
								LocalCacheData.current_open_sub_controller.edit_view &&
								LocalCacheData.current_open_sub_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						} else if ( LocalCacheData.current_open_primary_controller &&
								LocalCacheData.current_open_primary_controller.edit_view &&
								LocalCacheData.current_open_primary_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						}
					}
				} else {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}
			} );

			time_picker_input.focusout( function() {
				$this.hideErrorTip();

			} );
			if ( o.width > 0 ) {
				$this.width( o.width );
				is_static_width = true;
			} else {
				$this.autoResize();
				is_static_width = false;
			}
			$this.setDefaultWidgetValue();
		} );
		return this;
	};

	$.fn.TTimePicker.defaults = {};

})( jQuery );