( function( $ ) {

	$.fn.TTextInput = function( options ) {
		var opts = $.extend( {}, $.fn.TTextInput.defaults, options );
		var $this = this;
		var field;
		var validation_field;
		var error_string = '';
		var error_tip_box;

		var mass_edit_mode = false;
		var check_box = null;

		var enabled = true;

		var hasKeyEvent = null;

		//DONT USE THIS ANY MORE
		var need_parser_date = false;

		var need_parser_sec = false;

		var parsed_value = false; //work with need_parser_date

		var api_date = null;

		var validate_timer = null;

		var no_validate_timer = null;

		var no_validate_timer_sec = 0;

		var password_style = false;

		var disable_keyup_event = false; //set to not send change event when mouseup

		var mode;

		var is_static_width;

		var static_width;

		var display_na = true; // Display N/A when no value

		// var cancel_date_parse = false;

		var do_validate = true;

		// var parseDateAsync = function( callBack ) {
		// 	parsed_value = -1;
		// 	if ( !api_date ) {
		// 		api_date = new (APIFactory.getAPIClass( 'APIDate' ))();
		// 	}
		// 	api_date.parseTimeUnit( $this.val(), {
		// 		onResult: function( result ) {
		// 			if ( cancel_date_parse ) {
		// 				return;
		// 			}
		// 			parsed_value = result.getResult();
		// 			if ( callBack ) {
		// 				callBack();
		// 			}
		// 			ProgressBar.closeOverlay();
		// 		}
		// 	} );
		//
		// 	//parsed_value = Global.parseTimeUnit( $this.val() );
		// };

		this.setPlaceHolder = function( val ) {
			$this.attr( 'placeholder', val );
		};

		this.setNeedParsDate = function( val ) {
			need_parser_date = val;
		};

		this.setNeedParseSec = function( val ) {
			if ( val ) {
				//parsed_value = parseDateAsync();
				parsed_value = Global.parseTimeUnit( $this.val() );
			}
			need_parser_sec = val;

		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'readonly', 'true' );
				$this.addClass( 't-text-input-readonly' );
				if ( check_box ) {
					check_box.hide();
				}
				if ( !this.getValue() && display_na ) {
					this.val( $.i18n._( 'N/A' ) );
				}
			} else {
				$this.removeAttr( 'readonly' );
				$this.removeClass( 't-text-input-readonly' );
				if ( check_box ) {
					check_box.show();
				}
				if ( this.val() === $.i18n._( 'N/A' ) ) {
					this.val( '' );
				}
			}

		};

		this.setReadOnly = function( val ) {
			if ( val ) {
				$this.attr( 'disabled', 'true' );
				$this.addClass( 't-text-input-readonly-bg' );
			} else {
				$this.removeAttr( 'disabled' );
				$this.removeClass( 't-text-input-readonly-bg' );
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
				check_box = $( ' <div class="mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox" />' +
					'<label for="checkbox-input-1" class="input-helper input-helper--checkbox"></label></div>' );
				check_box.insertBefore( $( this ) );
				check_box.change( function() {
					if ( need_parser_date || need_parser_sec ) {
						parsed_value = Global.parseTimeUnit( $this.val() );
						// parseDateAsync( function() {
						// 	$this.trigger( 'formItemChange', [$this] );
						// } );
					}
					$this.trigger( 'formItemChange', [$this] );
				} );

				if ( is_static_width && static_width.toString().indexOf( '%' ) > 0 ) {
					$this.css( 'width', 'calc(' + static_width + ' - 25px)' );
				}
			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.setField = function( val ) {
			field = val;
		};

		this.getField = function() {
			return field;
		};

		this.getValidationField = function() {
			return validation_field;
		};

		this.getInputValue = function() {

			var val = $this.val();
			return val;

		};
		this.getValue = function() {
			var val = $this.val();
			if ( val === $.i18n._( 'N/A' ) ) {
				val = '';
			}
			if ( need_parser_sec || need_parser_date || parsed_value ) {
				if ( parsed_value === -1 ) {
					parsed_value = Global.parseTimeUnit( val );
					// cancel_date_parse = true; // cancel orginal date parse process
					// parsed_value = api_date.parseTimeUnit( val, { async: false } ).getResult();
				}
				return parsed_value;
			} else {
				return val;
			}

		};

		this.setValue = function( val ) {
			if ( !val && val !== 0 ) {
				val = '';
			}
			$this.val( val );
			if ( need_parser_date ) {
				//parseDateAsync();
				parsed_value = Global.parseTimeUnit( $this.val() );
			} else if ( need_parser_sec ) {
				parsed_value = val;
				$this.val( Global.getTimeUnit( val ) );
			}

			this.autoResize();
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

		this.setWidth = function( val ) {
			if ( val && ( val > 0 || val.indexOf( '%' ) > 0 ) ) {
				$this.width( val );
				static_width = val;
				is_static_width = true;
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

		this.autoResize = function() {
			var content_width, example_width;
			if ( !is_static_width ) {
				if ( mode === 'time' ) {
					example_width = Global.calculateTextWidth( LocalCacheData.getLoginUserPreference().time_format_display );
				} else if ( mode == 'time_unit' ) {
					example_width = Global.calculateTextWidth( LocalCacheData.getLoginUserPreference().time_unit_format_display );
				} else {
					example_width = 156;
				}
				content_width = Global.calculateTextWidth( $this.getValue(), {
					min_width: example_width,
					max_width: 200
				} );

			} else {
				if ( static_width.toString().indexOf( '%' ) > 0 ) {
					return;
				}
				example_width = static_width;
				content_width = Global.calculateTextWidth( $this.getValue(), {
					min_width: example_width,
					max_width: static_width > 200 ? static_width : 200
				} );
			}
			$this.width( content_width + 'px' );
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			field = o.field;

			if ( o.hasOwnProperty( 'do_validate' ) ) {
				do_validate = o.do_validate;
			}

			if ( o.validation_field ) {
				validation_field = o.validation_field;
			}
			if ( o.hasOwnProperty( 'display_na' ) ) {
				display_na = o.display_na;
			}
			if ( o.hasOwnProperty( 'no_validate_timer_sec' ) && o.no_validate_timer_sec > 0 ) {
				no_validate_timer_sec = o.no_validate_timer_sec;
			}
			hasKeyEvent = o.hasKeyEvent;
			need_parser_date = o.need_parser_date;
			need_parser_sec = o.need_parser_sec;

			if ( need_parser_date || need_parser_sec ) {
				api_date = new ( APIFactory.getAPIClass( 'APIDate' ) )();
			}

			if ( o.mode ) {
				mode = o.mode;
			}

			if ( o.width && ( o.width > 0 || o.width.indexOf( '%' ) > 0 ) ) {
				$this.width( o.width );
				static_width = o.width;
				is_static_width = true;
			}

			if ( o.disable_keyup_event ) {
				disable_keyup_event = o.disable_keyup_event;
			}

			if ( mode === 'time' ) {
				$this.setPlaceHolder( LocalCacheData.getLoginUserPreference().time_format_display );
			} else if ( mode === 'time_unit' ) {
				$this.setPlaceHolder( LocalCacheData.getLoginUserPreference().time_unit_format_display );
			}
			$this.autoResize();

			$( this ).keydown( function( e ) {
				// key is not TAB and ENTER
				if ( hasKeyEvent && e.keyCode !== 9 && e.keyCode !== 13 ) {
					$this.trigger( 'formItemKeyDown', [$this] );
				}

			} );

			$( this ).keyup( function( e ) {
				var validate_sec = 1000;
				if ( e.keyCode === 9 || e.keyCode === 13 ) {
					return;
				}
				var is_valid_input = Global.isValidInputCodes( e.keyCode );
				if ( !is_valid_input ) {
					return;
				}
				//don't clean event when click tab
				if ( validate_timer ) {
					clearTimeout( validate_timer );
					validate_timer = null;
				}

				if ( no_validate_timer ) {
					clearTimeout( no_validate_timer );
					no_validate_timer = null;
				}
				if ( hasKeyEvent ) {
					$this.trigger( 'formItemKeyUp', [$this] );
				}
				if ( error_string && error_string.length > 0 ) {
					validate_sec = 500;
				}
				if ( do_validate ) {
					validate_timer = setTimeout( function() {
						if ( check_box ) {
							$this.setCheckBox( true );
						}
						if ( need_parser_date || need_parser_sec ) {
							parsed_value = -1; // parse date when get value
							if ( !disable_keyup_event ) {
								$this.trigger( 'formItemChange', [$this, true] );
							}
						} else {
							if ( !disable_keyup_event ) {
								$this.trigger( 'formItemChange', [$this] );
							}
						}

					}, validate_sec );
				}

				// TO make sure the value is set to currentEditRecord when user typing it, but not trigger validate
				// Do this immediately instead wait for 300 ms.
				no_validate_timer = setTimeout( function() {

					if ( check_box ) {
						$this.setCheckBox( true );
					}
					if ( need_parser_date || need_parser_sec ) {
						parsed_value = -1; // parse date when get value
						if ( !disable_keyup_event ) {
							$this.trigger( 'formItemChange', [$this, true] );
						}
					} else {
						if ( !disable_keyup_event ) {
							$this.trigger( 'formItemChange', [$this, true] );
						}
					}
				}, no_validate_timer_sec );
			} );

			$( this ).mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			$( this ).mouseout( function() {
				if ( !$( $this ).is( ':focus' ) ) {
					$this.hideErrorTip();
				}
			} );

			$( this ).change( function() {
				$this.trigger( 'keyup', [$this] );
				//#2226 - When datetime or time unit fields specify need_parser_date or need_parser_sec it does not set disable_keyup_event == true. To validate those fields on change we need to check for those values.
				if ( disable_keyup_event || need_parser_date || need_parser_sec ) {
					$this.trigger( 'formItemChange', [$this] );
				}
				if ( !need_parser_date && !need_parser_sec ) {
					$this.autoResize();
				}
			} );

			$( this ).focusin( function() {

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

			$( this ).focusout( function() {
				$this.hideErrorTip();
			} );

		} );

		return this;

	};

	$.fn.TTextInput.defaults = {};

} )( jQuery );