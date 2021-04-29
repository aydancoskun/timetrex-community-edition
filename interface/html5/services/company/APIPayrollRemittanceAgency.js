var APIPayrollRemittanceAgency = ServiceCaller.extend( {

	key_name: 'PayrollRemittanceAgency',
	className: 'APIPayrollRemittanceAgency',

	getPayrollRemittanceAgency: function() {

		return this.argumentsHandler( this.className, 'getPayrollRemittanceAgency', arguments );

	},

	getPayrollRemittanceAgencyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayrollRemittanceAgencyDefaultData', arguments );

	},

	getCommonPayrollRemittanceAgencyData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayrollRemittanceAgencyData', arguments );

	},

	validatePayrollRemittanceAgency: function() {

		return this.argumentsHandler( this.className, 'validatePayrollRemittanceAgency', arguments );

	},

	setPayrollRemittanceAgency: function() {

		return this.argumentsHandler( this.className, 'setPayrollRemittanceAgency', arguments );

	},

	deletePayrollRemittanceAgency: function() {

		return this.argumentsHandler( this.className, 'deletePayrollRemittanceAgency', arguments );

	},

	copyPayrollRemittanceAgency: function() {

		return this.argumentsHandler( this.className, 'copyPayrollRemittanceAgency', arguments );

	},

	getProvinceOptions: function() {
		return this.argumentsHandler( this.className, 'getProvinceOptions', arguments );
	},

	getDistrictOptions: function() {
		return this.argumentsHandler( this.className, 'getDistrictOptions', arguments );
	}

} );