HeaderViewController = Backbone.View.extend( {
	initialize: function() {
		var tpl = Global.loadWidget( 'views/quick_punch/header/HeaderView.html' );
		if ( tpl ) { //JS Exception: Uncaught TypeError: Cannot read property 'replace' of undefined
			this.tpl = _.template( tpl );
		}
		this.render();
	},
	render: function() {
		var url = ServiceCaller.mainCompanyLogo;
		if ( this.tpl ) {
			this.setElement( this.tpl( { company_logo: url } ) );
		}
	}

} );
