var APIRemittanceDestinationAccount = ServiceCaller.extend( {

	key_name: 'RemittanceDestinationAccount',
	className: 'APIRemittanceDestinationAccount',

	getRemittanceDestinationAccount: function() {

		return this.argumentsHandler( this.className, 'getRemittanceDestinationAccount', arguments );

	},

	getRemittanceDestinationAccountDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRemittanceDestinationAccountDefaultData', arguments );

	},

	getCommonRemittanceDestinationAccountData: function() {

		return this.argumentsHandler( this.className, 'getCommonRemittanceDestinationAccountData', arguments );

	},

	validateRemittanceDestinationAccount: function() {

		return this.argumentsHandler( this.className, 'validateRemittanceDestinationAccount', arguments );

	},

	setRemittanceDestinationAccount: function() {

		return this.argumentsHandler( this.className, 'setRemittanceDestinationAccount', arguments );

	},

	deleteRemittanceDestinationAccount: function() {

		return this.argumentsHandler( this.className, 'deleteRemittanceDestinationAccount', arguments );

	},

	copyRemittanceDestinationAccount: function() {

		return this.argumentsHandler( this.className, 'copyRemittanceDestinationAccount', arguments );

	}

} );