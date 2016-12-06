var APIAccrualBalance = ServiceCaller.extend( {

	key_name: 'AccrualBalance',
	className: 'APIAccrualBalance',

	getAccrualBalance: function() {

		return this.argumentsHandler( this.className, 'getAccrualBalance', arguments );

	},

	exportAccrualBalance: function() {
		return this.argumentsHandler( this.className, 'exportAccrualBalance', arguments );
	}
} );