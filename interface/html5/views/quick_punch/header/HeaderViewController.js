/*
 * $License$
 */
HeaderViewController = Backbone.View.extend({
	initialize: function() {
		var tpl = Global.loadWidget( 'views/quick_punch/header/HeaderView.html' )
		this.tpl = _.template( tpl );
		this.render();
	},
	render: function () {
		var url = ServiceCaller.mainCompanyLogo;
		this.setElement( this.tpl( {company_logo: url} ) );
	}

});
