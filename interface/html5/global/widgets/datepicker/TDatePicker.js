(function( $ ) {

	$.fn.TDatePicker = function( options ) {
		var opts = $.extend( {}, $.fn.TDatePicker.defaults, options );
		//Global.addCss( 'global/widgets/datepicker/TDatePicker.css' );

		var $this = this;
		var field;
		var validation_field;
		var date_picker_input;
		var icon;
		var error_string = '';
		var error_tip_box;
		var mode = 'date';
		var multiple; // This is used to test Punches -> Edit view Date
		var mass_edit_mode = false;
		var check_box = null;
		var enabled = true;
		var is_open = false;
		var focus_out_timer;
		var can_open = false; //default when the calender can be open, we only open it when click on the icon
		var is_static_width = false;

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				//$this.attr( 'disabled', 'true' );
				date_picker_input.addClass( 't-date-picker-readonly' );
				icon.css( 'display', 'none' );
				date_picker_input.attr( 'readonly', 'readonly' );
				if ( check_box ) {
					check_box.hide();
				}
			} else {
				//$this.removeAttr( 'disabled' );
				date_picker_input.removeClass( 't-date-picker-readonly' );
				icon.css( 'display', 'inline' );
				date_picker_input.removeAttr( 'readonly' );
				if ( check_box ) {
					check_box.show();
				}
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
				date_picker_input.addClass( 'warning-tip' );
			} else {
				date_picker_input.addClass( 'error-tip' );
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
			if ( date_picker_input.hasClass( 'warning-tip' ) ) {
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

		// Error: TypeError: date_picker_input is undefined in /interface/html5/global/widgets/datepicker/TDatePicker.js?v=8.0.3-20150313-161037 line 122
		this.clearErrorStyle = function() {
			if ( !date_picker_input ) {
				return;
			}
			date_picker_input.removeClass( 'error-tip' );
			date_picker_input.removeClass( 'warning-tip' );
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
			// Error: Uncaught TypeError: Cannot read property 'val' of undefined in interface/html5/global/widgets/datepicker/TDatePicker.js?v=9.0.5-20151222-162114 line 145
			var val = date_picker_input ? date_picker_input.val() : null;
			//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/global/widgets/datepicker/TDatePicker.js?v=9.0.0-20150909-213207 line 140
			val = Global.strToDate( val ) && Global.strToDate( val ).format( 'YYYYMMDD' );

			return val;
		};

		this.setPlaceHolder = function( val ) {
			date_picker_input.attr( 'placeholder', val );
		};

		this.getValue = function() {
			// This is used to test Punches -> Edit view Date
			if ( multiple ) {
				return [date_picker_input.val()];
			}

			return date_picker_input.val();
		};

		this.setValue = function( val ) {
			//Error: Uncaught TypeError: Cannot read property 'val' of undefined in /interface/html5/global/widgets/datepicker/TDatePicker.js?v=8.0.0-20141230-130626 line 144
			if ( !date_picker_input ) {
				return;
			}
			if ( !val ) {
				val = '';
			}
			date_picker_input.val( val );
			this.autoResize();
		};

		this.setDefaultWidgetValue = function() {
			if ( $( this ).attr( 'widget-value' ) ) {
				this.setValue( $( this ).attr( 'widget-value' ) );
			}
		};

		this.autoResize = function() {
			var content_width, example_width, example_display;
			if ( LocalCacheData.getLoginUserPreference() ) {
				example_display = LocalCacheData.getLoginUserPreference().date_format_display;
			} else {
				example_display = 'dd-mmm-yy';
			}
			if ( !is_static_width ) {
				if ( mode === 'date' ) {
					example_width = Global.calculateTextWidth( example_display );
				} else if ( mode === 'date_time' ) {
					example_width = Global.calculateTextWidth( example_display + ' ' + LocalCacheData.getLoginUserPreference().time_format_display );
				}
				content_width = Global.calculateTextWidth( date_picker_input.val(), { min_width: example_width, max_width: (example_width + 100), padding: 28 } );
				$this.width( content_width + 'px' );
			}
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			field = o.field;
			if ( o.validation_field ) {
				validation_field = o.validation_field;
			}
			multiple = o.multiple; // This is used to test Punches -> Edit view Date
			if ( Global.isSet( o.mode ) ) {
				mode = o.mode;
			}
			icon = $( this ).find( '.t-date-picker-icon' );
			date_picker_input = $( this ).find( '.t-date-picker' );
			icon.attr( 'src', Global.getRealImagePath( 'images/cal.png' ) );

			icon.bind( 'mousedown', function( e ) {
				e.stopPropagation(); //Stop jquery-ui datepickers own mousedown event from firing, which cause the date picker to close, then the below 'mouseup' event opens it again.
			} );

			icon.bind( 'mouseup', function() { //Need to use 'mouseup' event as main.js binds 'mousedown' for closing when clicked off.
				if ( !enabled ) {
					return;
				}

				if ( !is_open ) {
					date_picker_input.datepicker( 'show' );
					is_open = true;
				} else {
					date_picker_input.datepicker( 'hide');
					is_open = false;
				}
			} );

			var time_format = 'h:mm TT';
			var format = 'dd-M-y';
			// When portal mode, no user preference.
			if ( LocalCacheData.getLoginUserPreference() ) {
				format = LocalCacheData.getLoginUserPreference().date_format_1;
				time_format = LocalCacheData.getLoginUserPreference().time_format_1;
			}
			var day_name_min = [
				$.i18n._( 'Sun' ), $.i18n._( 'Mon' ), $.i18n._( 'Tue' ),
				$.i18n._( 'Wed' ), $.i18n._( 'Thu' ), $.i18n._( 'Fri' ), $.i18n._( 'Sat' )
			];
			var month_name_short = [
				$.i18n._( 'Jan' ), $.i18n._( 'Feb' ),
				$.i18n._( 'Mar' ), $.i18n._( 'Apr' ), $.i18n._( 'May' ),
				$.i18n._( 'Jun' ), $.i18n._( 'Jul' ), $.i18n._( 'Aug' ),
				$.i18n._( 'Sep' ), $.i18n._( 'Oct' ), $.i18n._( 'Nov' ),
				$.i18n._( 'Dec' )
			];
			var current_text = $.i18n._( 'Today' );
			var close_text = $.i18n._( 'Close' );

			$.datepicker._gotoToday = function( id ) {
				//This fixes JS exception when using the "TODAY" button in the date pickers: Uncaught TypeError: Cannot read property 'selectedYear' of undefined
				$( id ).datepicker( 'setDate', new Date() ).datepicker( 'hide' ).change();

				// var target = $( id );
				// var inst = this._getInst( target[0] );
				// if ( this._get( inst, 'gotoCurrent' ) && inst.currentDay ) {
				// 	inst.selectedDay = inst.currentDay;
				// 	inst.drawMonth = inst.selectedMonth = inst.currentMonth;
				// 	inst.drawYear = inst.selectedYear = inst.currentYear;
				// }
				// else {
				// 	var date = new Date();
				// 	inst.selectedDay = date.getDate();
				// 	inst.drawMonth = inst.selectedMonth = date.getMonth();
				// 	inst.drawYear = inst.selectedYear = date.getFullYear();
				// 	// the below two lines are new
				// 	this._setDateDatepicker( target, date );
				// 	this._selectDate( id, this._getDateDatepicker( target ) );
				// 	$( target ).datepicker( 'setDate', date );
				// }
				// this._notifyChange( inst );
				// this._adjustDate( target );
			};

			if ( mode === 'date' ) {
				date_picker_input = date_picker_input.datepicker( {
					showOtherMonths: true,
					showTime: false,
					dateFormat: format,
					showHour: false,
					showMinute: false,
					changeMonth: true,
					changeYear: true,
					showButtonPanel: true,
					duration: '',
					showAnim: '',
					yearRange: '-100:+10',
					showOn: '',
					dayNamesMin: day_name_min,
					currentText: current_text,
					monthNamesShort: month_name_short,
					closeText: close_text,
					beforeShow: function() {
						if ( o.beforeShow ) {
							o.beforeShow();
						}
					},

					onClose: function() {
						is_open = false;
						$this.autoResize();
						if ( o.onClose ) {
							o.onClose();
						}
					},

					//#2353 - removed because it breaks the month navigation buttons
					// onChangeMonthYear: function( year, month, args ) {
					// 	var day = $.datepicker.formatDate( 'dd', $( this ).datepicker( 'getDate' ) );
					// 	$( this ).val( $.datepicker.formatDate( format, new Date( year, month - 1, day ) ) );
					// }

				} );

				// Portal mode, no user preference.
				if ( LocalCacheData.getLoginUserPreference() ) {
					$this.setPlaceHolder( LocalCacheData.getLoginUserPreference().date_format_display );
				} else {
					$this.setPlaceHolder( 'dd-mmm-yy' );
				}

			} else if ( mode === 'date_time' ) {
				date_picker_input = date_picker_input.datetimepicker( {
					showOtherMonths: true,
					dateFormat: format,
					timeFormat: time_format,
					showTime: true,
					showHour: true,
					showMinute: true,
					changeMonth: true,
					changeYear: true,
					showButtonPanel: true,
					duration: '',
					showAnim: '',
					showOn: '',
					yearRange: '-100:+10',
					closeText: close_text,
					dayNamesMin: day_name_min,
					monthNamesShort: month_name_short,
					currentText: current_text,
					onClose: function() {
						is_open = false;
						$this.autoResize();
						if ( o.onClose ) {
							o.onClose();
						}
					}
				} );
				$this.setPlaceHolder( LocalCacheData.loginUserPreference.date_format_display + ' ' + LocalCacheData.loginUserPreference.time_format_display );
			}

			date_picker_input.change( function() {
				if ( check_box ) {
					$this.setCheckBox( true );
				}

				$this.trigger( 'formItemChange', [$this] );
				$this.autoResize();
			} );

			date_picker_input.mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			date_picker_input.mouseout( function() {
				if ( !$( $this ).is( ':focus' ) ) {
					$this.hideErrorTip();
				}
			} );

			date_picker_input.focusin( function( e ) {
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

			date_picker_input.focusout( function() {
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


	$.fn.TDatePicker.defaults = {};

})( jQuery );
