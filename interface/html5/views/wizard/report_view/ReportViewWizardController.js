ReportViewWizardController = BaseWizardController.extend( {

	el: '.wizard-bg',

	init: function( options ) {
		//this._super('initialize', options );
		this.title = $.i18n._( 'Report View' );
		this.steps = 1;
		this.current_step = 1;
		this.render();
	},

	render: function() {
		this._super( 'render' );
		this.initCurrentStep();
	},

	buildCurrentStepUI: function() {
		this.stepsWidgetDic[this.current_step] = {};
		switch ( this.current_step ) {
			case 1:
				this.content_div.children().eq( 0 )[0].contentWindow.document.open();
				this.content_div.children().eq( 0 )[0].contentWindow.document.writeln( this.default_data );
				this.content_div.children().eq( 0 )[0].contentWindow.document.close();
				break;
		}
	},

	onCloseClick: function() {
		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;

		var source = 'View'; // Backup value in case the url sm does not exist.
		if( LocalCacheData.all_url_args && LocalCacheData.all_url_args.sm ) {
			source = LocalCacheData.all_url_args.sm + '@View';
		}
		$().TFeedback({
			source: source,
			force_source: true
		});
	},

	onDoneClick: function() {
		this.onCloseClick();
	}

} );