PayrollRemittanceAgencyEventWizardStepHome = WizardStep.extend( {

	name: 'home',

	prae_grid_source_data: null,
	grid: null,

	el: $( '.wizard.process_transactions_wizard' ),


	init: function() {
		filter_data = {
			filter_data: {
				'status_id': [ 10, 15 ], //10=Enabled (Self Service) 15=Enabled (Full Service)
				'payroll_remittance_agency_status_id': 10, //Enabled
				'start_date': ( ( new Date() / 1000 ) + ( 86400 * 14 ) ) //Move start date into the future by 14 days so per Pay Period frequencies will still appear well in advance.
			},
			filter_columns: {
				'id': true,
				'payroll_remittance_agency_id': true,
				'legal_entity_legal_name': true,
				'payroll_remittance_agency_name': true,
				'type': true,
				'type_id': true,
				'start_date': true,
				'end_date': true,
				'due_date': true,
				'in_time_period': true
			},
			'filter_sort': {
				'status_id': 'desc',
				'due_date': 'asc',
				'legal_entity_id': 'asc',
				'payroll_remittance_agency_id': 'asc',
				'type_id': 'asc'
			}
		};

		var $this = this;

		var api_payroll_remittance_agency_event = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();

		api_payroll_remittance_agency_event.getPayrollRemittanceAgencyEvent( filter_data, {
			onResult: function( result ) {
				$this.prae_grid_source_data = result.getResult();
				$this.render();
			}
		} );
	},

	getNextStepName: function() {
		//Must have a selected row in home step grid to enable the next button.
		if ( TTUUID.isUUID( this.getWizardObject().selected_remittance_agency_event_id ) ) {
			return 'review';
		} else {
			return false;
		}
	},

	_render: function() {
		this.setTitle( this.getWizardObject().wizard_name );
		if ( this.prae_grid_source_data.length > 0 ) {
			var $this = this;
			this.setInstructions( $.i18n._( 'Select one of the event(s) below to process' ) + ': ', function() {
				var grid_id = 'payroll_remittance_agency_events';
				var grid_div = $( '<div class=\'grid-div wizard-grid-div\'></div>' );
				var grid_table = $( '<table id=\'' + grid_id + '\'></table>' );
				grid_div.append( grid_table );

				if ( !$this.grid ) {
					$this.grid = $this.setGrid( grid_id, grid_div );
					$this.grid.setData( $this.prae_grid_source_data );
				}
				$this.colorGrid();

				$this.setGridAutoHeight( $this.grid, $this.prae_grid_source_data.length );

				if ( TTUUID.isUUID( $this.getWizardObject().selected_remittance_agency_event_id ) ) {
					$this.grid.grid.setSelection( $this.getWizardObject().selected_remittance_agency_event_id );
				} else {
					//select the first row on load.
					$this.grid.grid.setSelection( $this.grid.grid.find( 'tbody:first-child tr:nth-child(2)' ).attr( 'id' ) );
					$this.getWizardObject().selected_remittance_agency_event_id = $this.grid.grid.find( 'tbody:first-child tr:nth-child(2)' ).attr( 'id' );
				}

				$this.addButton( 'PayrollRemittanceAgency',
						Icons.view_detail,
						$.i18n._( 'Edit Remittance Agency' ),
						$.i18n._( 'In the event of incorrect dates, edit the selected remittance agency and its events to make corrections.' )
				);
			} );

		} else {
			var message = $( '<div/>' );
			message.html( $.i18n._( 'There are no outstanding tax events at this time.' ) );
			this.append( message );
		}

		//If the wizard is closed, it reopens to the home step and must be told what the current step is.
		this.getWizardObject().setCurrentStepName( 'home' );

	},

	colorGrid: function() {
		var data = this.grid.getData();
		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.in_time_period == true ) {
				$( '#'+this.grid.ui_id ).find( 'tr[id=\'' + item.id + '\']' ).css( 'color', '#ccc' );
			}

		}
	},

	_onNavigationClick: function( icon ) {
		switch ( icon ) {
			case 'PayrollRemittanceAgency':
				this.getWizardObject().minimize();

				var grid_data = this.grid.getData();
				var grid_indecies = this.grid.grid.jqGrid( 'getGridParam', '_index' );
				var remittance_agency_event = grid_data[grid_indecies [this.getWizardObject().selected_remittance_agency_event_id]];

				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'PayrollRemittanceAgency', remittance_agency_event.payroll_remittance_agency_id );

				break;
		}
	},

	getGridColumns: function( gridId, callBack ) {
		var column_info_array = [
			{
				name: 'legal_entity_legal_name',
				index: 'legal_entity_legal_name',
				label: 'Legal Entity',
				width: 90,
				sortable: true,
				title: false
			},
			{
				name: 'payroll_remittance_agency_name',
				index: 'payroll_remittance_agency_name',
				label: 'Agency',
				width: 200,
				sortable: true,
				title: false
			},
			{
				name: 'type',
				index: 'type',
				label: 'Event',
				width: 100,
				sortable: true,
				title: false
			},
			{
				name: 'start_date',
				index: 'start_date',
				label: 'Start Date',
				width: 60,
				sortable: true,
				title: false
			},
			{
				name: 'end_date',
				index: 'end_date',
				label: 'End Date',
				width: 60,
				sortable: true,
				title: false
			},
			{
				name: 'due_date',
				index: 'due_date',
				label: 'Due Date',
				width: 60,
				sortable: true,
				title: false
			}
		];

		return column_info_array;
	},

	onGridSelectRow: function( selected_id ) {
		if ( this.getWizardObject().selected_remittance_agency_event_id != selected_id ) {
			this.getWizardObject().selected_remittance_agency_event_id = selected_id;

			this.getWizardObject().reload();
			this.getWizardObject().payroll_remittance_agency_event_block = null;

		}
		this.getWizardObject().enableButtons();
	}
} );