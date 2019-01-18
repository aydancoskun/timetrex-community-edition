ResetForgotPasswordWizardController = BaseWizardController.extend( {

	el: '.wizard',

	init: function() {
		//this._super('initialize' );

		this.title = $.i18n._( 'Reset Password' );
		this.steps = 1;
		this.current_step = 1;
		if ( this.default_data && typeof this.default_data.api_class != 'undefined' ) {
			this.api = this.default_data.api_class;
		} else {
			this.api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
		}
		this.render();
	},

	render: function() {
		var $this = this;
		this._super( 'render' );
		// $( this.el ).css( {left:  ( Global.bodyWidth() - $(this.el ).width() )/2} );
        //
		// $( window ).resize( function() {
		// 	$( $this.el ).css( {left:  ( Global.bodyWidth() - $($this.el ).width() )/2} );
		// } );
		this.initCurrentStep();
	},

	buildCurrentStepUI: function() {

		var $this = this;
		this.content_div.empty();

		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Please specify your new password' ) + ':' );
				this.content_div.append( label );

				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );

				var new_password = this.getPasswordInput( 'new_password' );

				form_item_label.text( $.i18n._( 'New Password' ) + ':' );
				form_item_input_div.append( new_password );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var confirm_password = this.getPasswordInput( 'confirm_password' );
				form_item_label.text( $.i18n._( 'New Password (Confirm)' ) + ':' );
				form_item_input_div.unbind( 'keydown' ).bind( 'keydown', function( e ) {
					if ( e.keyCode === 13 ) {
						$this.onDoneClick();
					}
				} );
				form_item_input_div.append( confirm_password );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][new_password.getField()] = new_password;
				this.stepsWidgetDic[this.current_step][confirm_password.getField()] = confirm_password;
				break;
		}

	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			default:

				for ( var key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) continue;

					current_step_data[key] = current_step_ui[key].getValue();
				}
				break;
		}

	},

	buildCurrentStepData: function() {

	},

	onCloseClick: function() {
		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;
		LocalCacheData.extra_filter_for_next_open_view = null;
		var location = Global.getBaseURL().split( '#' )[0];
		location = location + '#!m=Login';
		if ( LocalCacheData.all_url_args ) {
			for ( var key in LocalCacheData.all_url_args ) {
				if ( key !== 'm' && key !== 'sm' && key !== 'key' ) {
					location = location + '&' + key + '=' + LocalCacheData.all_url_args[key];
				}
			}
		}
		window.location = location;
	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();

		var new_password = this.stepsDataDic[1].new_password;
		var confirm_password = this.stepsDataDic[1].confirm_password;

		this.stepsWidgetDic[1].new_password.clearErrorStyle();
		this.stepsWidgetDic[1].confirm_password.clearErrorStyle();

		if ( !new_password ) {
			this.stepsWidgetDic[1].new_password.setErrorStyle( $.i18n._( 'New password can\'t be empty' ), true );
		} else if ( new_password !== confirm_password ) {
			this.stepsWidgetDic[1].confirm_password.setErrorStyle( $.i18n._( 'New password does not match' ), true );
		} else {
			this.api.passwordReset( LocalCacheData.all_url_args.key,
				new_password,
				confirm_password
				, {
					onResult: function( result ) {

						if ( !result.isValid() ) {
							TAlertManager.showErrorAlert( result );
						} else {
							$this.onCloseClick();
							if ( $this.call_back ) {
								$this.call_back();
							}
						}

					}
				} )
		}

	}

} );

ResetForgotPasswordWizardController.type = '';