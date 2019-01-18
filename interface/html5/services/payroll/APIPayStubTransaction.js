var APIPayStubTransaction = ServiceCaller.extend( {

	key_name: 'PayStubTransaction',
	className: 'APIPayStubTransaction',

	getPayStubTransactionDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubTransactionDefaultData', arguments );

	},

	getPayStubTransaction: function() {

		return this.argumentsHandler( this.className, 'getPayStubTransaction', arguments );

	},

	getCommonPayStubTransactionData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubTransactionData', arguments );

	},

	validatePayStubTransaction: function() {

		return this.argumentsHandler( this.className, 'validatePayStubTransaction', arguments );

	},

	setPayStubTransaction: function() {

		return this.argumentsHandler( this.className, 'setPayStubTransaction', arguments );

	},

	deletePayStubTransaction: function() {

		return this.argumentsHandler( this.className, 'deletePayStubTransaction', arguments );

	},

	getPayPeriodTransactionSummary: function() {

		return this.argumentsHandler( this.className, 'getPayPeriodTransactionSummary', arguments );

	}

} );