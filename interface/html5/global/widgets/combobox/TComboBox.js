( function( $ ) {

	$.fn.TComboBox = function( options ) {
		var opts = $.extend( {}, $.fn.TComboBox.defaults, options );
		var $this = this;
		var field;
		var source_data = null;

		var select_value = null;

		var set_empty = false;

		var set_any = false;

		var set_select_item_when_set_source_data = false;

		var error_string = '';

		var error_tip_box;

		var mass_edit_mode = false;

		var check_box = null;

		var enabled = true;

		var valueKey = 'value';

		var labelKey = 'label';

		var customFirstItemLabel = '';

		this.setValueKey = function( val ) {
			valueKey = val;
		};

		this.setLabelKey = function( val ) {
			labelKey = val;
		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;

			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
				$this.addClass( 't-select-readonly' );
			} else {
				$this.removeAttr( 'disabled' );
				$this.removeClass( 't-select-readonly' );
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

		this.setField = function( val ) {
			field = val;
		};

		this.getField = function() {
			return field;
		};

		this.getLabel = function() {

			if ( !source_data || ( set_empty && source_data.length === 1 ) || ( set_any && source_data.length === 1 ) ) {
				return select_value;
			}
			//if value is number convert to number type
			var value = $( this ).children( 'option:selected' ).text();

			return value;
		};

		this.getValue = function() {

			if ( !source_data || ( set_empty && source_data.length === 1 ) || ( set_any && source_data.length === 1 ) ) {
				return select_value;
			}

			//if value is number convert to number type
			var value = $( this ).children( 'option:selected' ).attr( 'value' );

			//#2624 - Avoid parsing float value if we have a value larger than zero with a leading zero so that numeric string (eg province) ISO codes are not converted from '01' to 1
			if ( $.isNumeric( value ) && ( value === '0' || value.toString()[0] !== '0' ) ) {
				value = parseFloat( value );
			}

			if ( value === -1 || value === '-1' ) {
				value = -1;
			}
			return value;
		};

		this.getSelectedIndex = function() {
			return this[0].selectedIndex;
		};

		this.setSelectedIndex = function( set_index ) {
			if ( set_index < 0 ) {
				set_index = 0;
			}
			if ( set_index >= this[0].length ) {
				set_index = this[0].length - 1;
			}
			this[0].selectedIndex = set_index;
			this.setValue( this[0].value );
		}
		;
		this.getLabel = function() {
			//if value is number convert to number type
			var value = $( this ).children( 'option:selected' ).text();
			return value;
		};

		this.setValue = function( val ) {
			select_value = val;

			if ( !source_data || source_data.length < 1 || ( set_empty && source_data.length === 1 ) || ( set_any && source_data.length === 1 ) ) {
				set_select_item_when_set_source_data = true;
				return;
			}

			//When no value == undefined or null or default false
			if ( !Global.isSet( val ) || val === false ) {
				if ( set_empty ) {
					val = TTUUID.zero_id;
				} else if ( set_any ) {
					val = TTUUID.not_exist_id;
				} else {
					//If no empty value, default to select first item
					if ( source_data && source_data.length > 0 ) {
						this.setValue( source_data[0][valueKey] );
						return;
					}

				}
			}

			$( $( this ).find( 'option' ) ).removeAttr( 'selected' );

			$( $( this ).find( 'option' ) ).filter( function() {
				if ( val === null || val === undefined ) {
					return false;
				}
				return $( this ).attr( 'value' ) === val.toString();
			} ).prop( 'selected', true ).attr( 'selected', true );
		};

		/* jshint ignore:start */
		this.setSourceData = function( val ) {

			$( this ).empty();

			if ( !Global.isSet( val ) || val.length < 1 ) {
				if ( set_empty ) {
					val = Global.addFirstItemToArray( val, 'empty', customFirstItemLabel );
				} else if ( set_any ) {
					val = Global.addFirstItemToArray( val, 'any', customFirstItemLabel );
				}
			} else {
				if ( set_empty ) {
					if ( val && val.length > 0 && ( val[0].value != TTUUID.zero_id && val[0].value != 0 ) ) {
						val = Global.addFirstItemToArray( val, 'empty', customFirstItemLabel );
					}

				} else if ( set_any ) {
					if ( val && val.length > 0 && ( val[0].value != TTUUID.not_exist_id && val[0].value != -1 ) ) {
						val = Global.addFirstItemToArray( val, 'any', customFirstItemLabel );
					}

				}
			}

			source_data = val;
			var option_array = [];

			if ( $.isArray( val ) ) {
				var len = val.length;
				for ( var i = 0; i < len; i++ ) {
					option_array.push( '<option value="' + val[i][valueKey] + '">' + val[i][labelKey] + '</option>' );
					// $( this ).append( '<option value="' + item[valueKey] + '">' + item[labelKey] + '</option>' );
				}
			} else {
				for ( var j in val ) {
					// $( this ).append( '<option value="' + j + '">' + val[j] + '</option>' );
					option_array.push( '<option value="' + j + '">' + val[j] + '</option>' );
				}
			}

			if ( $( this )[0] ) {
				$( this )[0].innerHTML = option_array.join( '' );
			}

			if ( set_select_item_when_set_source_data ) {
				this.setValue( select_value );
			}

		};
		/* jshint ignore:end */

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.set_empty ) {
				set_empty = o.set_empty;
			}

			if ( o.customFirstItemLabel ) {
				customFirstItemLabel = o.customFirstItemLabel;
			}

			if ( o.set_any ) {
				set_any = o.set_any;
			}

			if ( o.mass_edit_mode ) {
				mass_edit_mode = o.mass_edit_mode;
			}

			field = o.field;

			$( this ).change( function() {

				if ( !enabled ) {
					return;
				}

				if ( check_box ) {
					$this.setCheckBox( true );
				}

				$this.trigger( 'formItemChange', [$this] );
			} );

			$( this ).click( function() {
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
				}
			} );

			$( this ).mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			$( this ).mouseout( function() {
				$this.hideErrorTip();
			} );

		} );

		return this;

	};

	$.fn.TComboBox.defaults = {};

} )( jQuery );