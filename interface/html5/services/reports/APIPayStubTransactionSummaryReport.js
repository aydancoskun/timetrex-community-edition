/*
 * $License$
 */

var APIPayStubTransactionSummaryReport = ServiceCaller.extend( {

	key_name: 'PayStubTransactionSummaryReport',
	className: 'APIPayStubTransactionSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonPayStubTransactionSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubTransactionSummaryReportData', arguments );

	},

	getPayStubTransactionSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getPayStubTransactionSummaryReport', arguments );

	},

	setPayStubTransactionSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setPayStubTransactionSummaryReport', arguments );

	},

	getPayStubTransactionSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubTransactionSummaryReportDefaultData', arguments );

	},

	deletePayStubTransactionSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deletePayStubTransactionSummaryReport', arguments );

	},

	validatePayStubTransactionSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validatePayStubTransactionSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}

} );