var APILegalEntity = ServiceCaller.extend( {

	key_name: 'LegalEntity',
	className: 'APILegalEntity',

	getLegalEntity: function() {

		return this.argumentsHandler( this.className, 'getLegalEntity', arguments );

	},

	getLegalEntityDefaultData: function() {

		return this.argumentsHandler( this.className, 'getLegalEntityDefaultData', arguments );

	},

	getCommonLegalEntityData: function() {

		return this.argumentsHandler( this.className, 'getCommonLegalEntityData', arguments );

	},

	validateLegalEntity: function() {

		return this.argumentsHandler( this.className, 'validateLegalEntity', arguments );

	},

	setLegalEntity: function() {

		return this.argumentsHandler( this.className, 'setLegalEntity', arguments );

	},

	deleteLegalEntity: function() {

		return this.argumentsHandler( this.className, 'deleteLegalEntity', arguments );

	},

	copyLegalEntity: function() {

		return this.argumentsHandler( this.className, 'copyLegalEntity', arguments );

	}



} );