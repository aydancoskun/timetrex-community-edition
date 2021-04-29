import { WizardStep } from '@/global/widgets/wizard/WizardStep';

export class ProcessTransactionsWizardStepHome extends WizardStep {
	constructor( options = {} ) {
		_.defaults( options, {
			name: 'home',

			source_accounts: [],
			pay_periods: [],
			types: [],

			filter_data: null
		} );

		super( options );
	}

	init() {
		var $this = this;
		var external_data = $this.getWizardObject().getExternalData();
		if ( external_data.transaction_source_data ) {
			$this.source_accounts = [];
			$this.normalizeSourceAccounts( external_data.transaction_source_data );
			delete external_data.transaction_source_data;
			$this.render();
			return;
		}

		var api = TTAPI.APIPayStubTransaction;
		var filter_data = {};
		if ( external_data ) {
			var temp_filter_data = external_data.filter_data;

			// Ignore ugliness from report setup data ( We only want to send the filter forward )
			var ignore_fields = ['chart', 'columns', 'sort', 'sort_', 'order', 'other', 'template', 'sub_total'];
			for ( var n in temp_filter_data ) {
				if ( ignore_fields.indexOf( n ) == -1 ) {
					filter_data[n] = temp_filter_data[n];
				}
			}
		}

		api.getPayPeriodTransactionSummary( filter_data, {
			onResult: function( result ) {
				var transactions = result.getResult();
				$this.normalizeSourceAccounts( transactions );
				$this.render();
			}
		} );
	}

	normalizeSourceAccounts( data ) {
		this.source_accounts = [];

		var external_data = this.getWizardObject().getExternalData();
		if ( !external_data.filter_data.pay_period_id ) {
			external_data.filter_data.pay_period_id = [];
		}

		for ( var m in data ) {
			var item = data[m];
			var new_record = {
				id: item.remittance_source_account_id,
				name: item.remittance_source_account,
				type: item.remittance_source_account_type,
				last_transaction_number: item.remittance_source_account_last_transaction_number,
				total_amount: item.total_amount,
				total_transactions: item.total_transactions
			};
			this.source_accounts.push( new_record );

			if ( item.pay_period_id && external_data.filter_data.pay_period_id.indexOf( item.pay_period_id ) == -1 && item.pay_period_id != TTUUID.zero_id ) {
				external_data.filter_data.pay_period_id.push( item.pay_period_id );
			}
		}
		this.getWizardObject().setExternalData( external_data );
	}

	initCardsBlock() {
		$( this.wizard_obj.el ).find( '.download_warning' ).html( 'Click the <button class="done-btn"></button> icon to download the transaction file. Be sure to save it to your computer rather than open it' );
	}

	getNextStepName() {
		//This is a single-step wizard. Always return false;
		return false;
	}

	_render() {
		this.setTitle( this.getWizardObject().wizard_name );

		//If the wizard is closed, it reopens to the home step and must be told what the current step is.
		this.getWizardObject().setCurrentStepName( 'home' );

		TTPromise.add( 'processTransactionsWizard', 'render' );
		var $this = this;
		TTPromise.wait( 'processTransactionsWizard', 'render', function() {
			$( $this.el ).show();
			( $( '.process_transactions_wizard input' )[1] ).focus(); //Select first input field by default.
		} );
		this.buildForm();
	}

	buildForm() {
		if ( this.source_accounts.length > 0 ) {
			var tab_index = 1050;
			var instruction_text = $.i18n._( 'Select source accounts to processs transactions for' );
			var $this = this;
			var container = $( '<div></div>' );
			var form = $( '<form id="process_transactions_wizard_select_accounts_form"></form>' ).appendTo( container );

			var table = $( '<table id="process_transactions_wizard_source_account_table"></table>' );
			var header_row = $( '<tr></tr>' );
			header_row.html( '<th colspan="7">' + instruction_text + '<br><br></th>' );
			var column_header_row = $( '<tr></tr>' );
			var th_chk = $( '<th></th>' ).appendTo( column_header_row );
			var th_name = $( '<th></th>' ).html( $.i18n._( 'Source Account' ) ).appendTo( column_header_row );
			var th_format = $( '<th></th>' ).html( $.i18n._( 'Type' ) ).appendTo( column_header_row );
			var th_last_check = $( '<th></th>' ).html( $.i18n._( 'Last #' ) ).appendTo( column_header_row );
			var th_next_check = $( '<th></th>' ).html( $.i18n._( 'Next #' ) ).appendTo( column_header_row );
			var th_amount = $( '<th></th>' ).html( $.i18n._( 'Amount' ) ).appendTo( column_header_row );
			var th_transactions = $( '<th></th>' ).html( $.i18n._( 'Transactions' ) ).appendTo( column_header_row );
			table.append( header_row );
			table.append( column_header_row );
			for ( var n in this.source_accounts ) {
				var item = this.source_accounts[n];
				var row = $( '<tr></tr>' );
				var chk = $( '<input type="checkbox" value="' + item.id + '" tabIndex="' + tab_index + '" checked></input>' );
				tab_index++;

				chk.on( 'change', function( e ) {
					e.preventDefault();
					$this.onCheck();
				} );

				var td_chk = $( '<td></td>' ).append( chk ).appendTo( row );
				var td_name = $( '<td></td>' ).html( item.name ).appendTo( row );
				var td_format = $( '<td></td>' ).html( $.i18n._( item.type ) ).appendTo( row );
				var last_transaction_input = $( '<input value="' + item.last_transaction_number + '" class="last_transaction_number" type="text" style="width:50px" tabIndex="' + tab_index + '">' );
				tab_index++;

				last_transaction_input.on( 'keydown', function( e ) {
					$this.onCheckNoKeyDown( e );
				} );

				last_transaction_input.on( 'keyup', function( e ) {
					var result_element = $( e.target ).parents( 'tr' ).find( '.next_transaction_number' );
					result_element.val( parseInt( $( e.target ).val() ) + 1 );
				} );

				var next_transaction_input = $( '<input value="' + ( parseInt( item.last_transaction_number ) + 1 ) + '" class="next_transaction_number" type="text" style="width:50px" tabIndex="' + tab_index + '">' );
				tab_index++;

				next_transaction_input.on( 'keydown', function( e ) {
					$this.onCheckNoKeyDown( e );
				} );

				next_transaction_input.on( 'keyup', function( e ) {
					var result_element = $( e.target ).parents( 'tr' ).find( '.last_transaction_number' );
					result_element.val( parseInt( $( e.target ).val() ) - 1 );
				} );

				var td_last_check = $( '<td></td>' ).append( last_transaction_input ).appendTo( row );
				var td_last_check = $( '<td></td>' ).append( next_transaction_input ).appendTo( row );
				var td_last_check = $( '<td></td>' ).html( item.total_amount ).appendTo( row );
				var td_last_check = $( '<td></td>' ).html( item.total_transactions ).appendTo( row );

				row.appendTo( table );
			}

			form.append( table );
			$( this.getWizardObject().el ).find( '.content' ).html( container );
			$( this.getWizardObject().el ).find( '.done-btn' ).removeClass( 'disable-image' );
			//reset preload data
			this.source_accounts = null;
			this.filter = null;
		} else {
			$( this.getWizardObject().el ).find( '.content' ).html( 'No transactions to process.' );
			$( this.getWizardObject().el ).find( '.done-btn' ).addClass( 'disable-image' );
		}

		this.onCheck(); //ensure that the done button is enabled by default
		TTPromise.resolve( 'processTransactionsWizard', 'render' );
	}

	onCheckNoKeyDown( e ) {
		//only allow digits, delete, backspace and arrows
		if ( isNaN( e.key ) && [9, 8, 46, 37, 39].indexOf( e.keyCode ) == -1 ) {
			e.preventDefault();
			return false;
		}
	}

	onCheck() {
		var checkboxes = $( this.getWizardObject().el ).find( '.content input[type="checkbox"]' ).filter( ':checked' );
		if ( checkboxes.length > 0 ) {
			var data = [];
			checkboxes.each( function() {
				data.push( $( this ).val() );
			} );
			this.getWizardObject().setTransactionIds( data );

			$( this.getWizardObject().el ).find( '.done-btn' ).removeClass( 'disable-image' );
		} else {
			this.getWizardObject().setTransactionIds( [] );
			$( this.getWizardObject().el ).find( '.done-btn' ).addClass( 'disable-image' );
		}
	}

	setFilterData( data ) {
		this.filter_data = data;
		TTPromise.resolve( 'ProcessTransactionsWizardStepHome', 'init_filter' );
	}

	getFilterData() {
		return this.filter_data;
	}

}