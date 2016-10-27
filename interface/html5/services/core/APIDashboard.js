var APIDashboard = ServiceCaller.extend( {

	key_name: 'Dashboard',
	className: 'APIDashboard',

	getDashletData: function() {
		return this.argumentsHandler( this.className, 'getDashletData', arguments );
	},

	getDefaultDashlets: function() {
		return this.argumentsHandler( this.className, 'getDefaultDashlets', arguments );
	},

	removeAllDashlets: function() {
		return this.argumentsHandler( this.className, 'removeAllDashlets', arguments );
	}

} );
