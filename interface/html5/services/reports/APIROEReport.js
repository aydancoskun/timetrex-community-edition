var APIROEReport = ServiceCaller.extend( {
	key_name: 'ROEReport',
	className: 'APIROEReport',

	getTemplate: function () {
		return this.argumentsHandler( this.className, 'getTemplate', arguments );
	},

	getCommonROEReportData: function () {
		return this.argumentsHandler( this.className, 'getCommonROEReportData', arguments );
	},

	getROEReport: function () {
		return this.argumentsHandler( this.className, 'getROEReport', arguments );
	},

	setROEReport: function () {
		return this.argumentsHandler( this.className, 'setROEReport', arguments );
	},

	getROEReportDefaultData: function () {
		return this.argumentsHandler( this.className, 'getROEReportDefaultData', arguments );
	},

	deleteROEReport: function () {
		return this.argumentsHandler( this.className, 'deleteROEReport', arguments );
	},

	validateROEReport: function () {
		return this.argumentsHandler( this.className, 'validateROEReport', arguments );
	},

	validateReport: function () {
		return this.argumentsHandler( this.className, 'validateReport', arguments );
	},

	setCompanyFormConfig: function () {
		return this.argumentsHandler( this.className, 'setCompanyFormConfig', arguments );
	},

	getCompanyFormConfig: function () {
		return this.argumentsHandler( this.className, 'getCompanyFormConfig', arguments );
	}
} );