var APIPayrollRemittanceAgencyEvent = ServiceCaller.extend( {

	key_name: 'PayrollRemittanceAgencyEvent',
	className: 'APIPayrollRemittanceAgencyEvent',

	getPayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'getPayrollRemittanceAgencyEvent', arguments );
	},

	getPayrollRemittanceAgencyEventDefaultData: function() {
		return this.argumentsHandler( this.className, 'getPayrollRemittanceAgencyEventDefaultData', arguments );
	},

	getCommonPayrollRemittanceAgencyEventData: function() {
		return this.argumentsHandler( this.className, 'getCommonPayrollRemittanceAgencyEventData', arguments );
	},

	validatePayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'validatePayrollRemittanceAgencyEvent', arguments );
	},

	setPayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'setPayrollRemittanceAgencyEvent', arguments );
	},

	deletePayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'deletePayrollRemittanceAgencyEvent', arguments );
	},

	copyPayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'copyPayrollRemittanceAgencyEvent', arguments );
	},

	exportPayrollRemittanceAgencyEvent: function() {
		return this.argumentsHandler( this.className, 'exportPayrollRemittanceAgencyEvent', arguments );
	},

	calculateNextRunDate: function() {
		return this.argumentsHandler( this.className, 'calculateNextRunDate', arguments );
	},

	getReportData: function() {
		return this.argumentsHandler( this.className, 'getReportData', arguments );
	},
	getURL: function() {
		return this.argumentsHandler( this.className, 'getURL', arguments );
	},
} );