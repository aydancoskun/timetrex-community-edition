var APIRemittanceSourceAccount = ServiceCaller.extend( {

	key_name: 'RemittanceSourceAccount',
	className: 'APIRemittanceSourceAccount',

	getRemittanceSourceAccount: function() {

		return this.argumentsHandler( this.className, 'getRemittanceSourceAccount', arguments );

	},

	getRemittanceSourceAccountDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRemittanceSourceAccountDefaultData', arguments );

	},

	getCommonRemittanceSourceAccountData: function() {

		return this.argumentsHandler( this.className, 'getCommonRemittanceSourceAccountData', arguments );

	},

	validateRemittanceSourceAccount: function() {

		return this.argumentsHandler( this.className, 'validateRemittanceSourceAccount', arguments );

	},

	setRemittanceSourceAccount: function() {

		return this.argumentsHandler( this.className, 'setRemittanceSourceAccount', arguments );

	},

	deleteRemittanceSourceAccount: function() {

		return this.argumentsHandler( this.className, 'deleteRemittanceSourceAccount', arguments );

	},

	copyRemittanceSourceAccount: function() {

		return this.argumentsHandler( this.className, 'copyRemittanceSourceAccount', arguments );

	}



} );