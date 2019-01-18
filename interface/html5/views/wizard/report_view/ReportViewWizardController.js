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
				this.content_div.children().eq( 0 )[0].contentDocument.open();
				this.content_div.children().eq( 0 )[0].contentDocument.writeln( this.default_data );
				this.content_div.children().eq( 0 )[0].contentDocument.close();
				break;
		}
	},

	onCloseClick: function() {
		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;
	},

	onDoneClick: function() {
		this.onCloseClick();
	}

} );