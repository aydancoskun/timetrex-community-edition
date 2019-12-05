PayStubSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIPayStubSummaryReport', 'APIPayStub', 'APIPayrollRemittanceAgency', 'APICurrency'],

	initReport: function( options ) {
		this.script_name = 'PayStubSummaryReport';
		this.viewId = 'PayStubSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PayStubSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubSummaryReport' ))();
	},

	onReportMenuClick: function( id ) {
		this.processTransactions( id );
	},

	getCustomContextMenuModel: function () {
		var context_menu_model = {
			groups: {
				pay_stub: {
					label: $.i18n._( 'Pay Stub' ),
					id: this.script_name + 'PayStub'
				},
				export: {
					label: $.i18n._( 'Export' ),
					id: this.viewId + 'Export'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Employee<br>Pay Stubs' ),
					id: ContextMenuIconName.employee_pay_stubs,
					group: 'pay_stub',
					icon: Icons.pay_stubs
				},
				{
					label: $.i18n._( 'Employer<br>Pay Stubs' ),
					id: ContextMenuIconName.employer_pay_stubs,
					group: 'pay_stub',
					icon: Icons.pay_stubs
				},
				{
					label: $.i18n._( 'Process<br>Transactions' ),
					id: ContextMenuIconName.direct_deposit,
					group: 'export',
					icon: 'direct_deposit-35x35.png',
					items: []
				}
			]
		};

		return context_menu_model;
	},

	// Overriding empty ReportBaseViewController.processFilterField() called from base.openEditView to provide view specific logic.
	processFilterField: function() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'status_id' ) {
				item.value = 'filter';
			}
		}
	},

	onFormItemChangeProcessFilterField: function( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.status_id );
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.employee_pay_stubs: //All report view
				this.onViewClick( 'pdf_employee_pay_stub' );
				break;
			case ContextMenuIconName.employer_pay_stubs: //All report view
				this.onViewClick( 'pdf_employer_pay_stub' );
				break;
			case ContextMenuIconName.direct_deposit:
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
} );