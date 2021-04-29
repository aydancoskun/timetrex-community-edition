var APIAuthentication = ServiceCaller.extend( {

	key_name: 'Authentication',
	className: 'APIAuthentication',

	newSession: function() {
		return this.argumentsHandler( this.className, 'newSession', arguments );
	},

	sendErrorReport: function() {
		return this.argumentsHandler( this.className, 'sendErrorReport', arguments );
	},

	login: function() {
		return this.argumentsHandler( this.className, 'Login', arguments );
	},

	PunchLogin: function() {

		return this.argumentsHandler( this.className, 'PunchLogin', arguments );

	},

	getPreLoginData: function() {

		return this.argumentsHandler( this.className, 'getPreLoginData', arguments );

	},

	getCurrentUser: function() {
		return this.argumentsHandler( this.className, 'getCurrentUser', arguments );

	},

	getOrganizationName: function() {

		return this.argumentsHandler( this.className, 'getOrganizationName', arguments );

	},

	getApplicationName: function() {
		return this.argumentsHandler( this.className, 'getApplicationName', arguments );

	},

	getCurrentCompany: function() {
		return this.argumentsHandler( this.className, 'getCurrentCompany', arguments );

	},

	getOrganizationURL: function() {
		return this.argumentsHandler( this.className, 'getOrganizationURL', arguments );

	},

	passwordReset: function() {

		return this.argumentsHandler( this.className, 'passwordReset', arguments );

	},

	resetPassword: function() {

		return this.argumentsHandler( this.className, 'resetPassword', arguments );

	},

	changePassword: function() {

		return this.argumentsHandler( this.className, 'changePassword', arguments );

	},

	getLocale: function() {

		return this.argumentsHandler( this.className, 'getLocale', arguments );

	},

} );