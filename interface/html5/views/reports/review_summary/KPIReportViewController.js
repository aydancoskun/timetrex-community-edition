export class KPIReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'KPIReport';
		this.viewId = 'KPIReport';
		this.context_menu_name = $.i18n._( 'Review Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'KPIReportView.html';
		this.api = TTAPI.APIKPIReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				review: {
					label: $.i18n._( 'Review' ),
					id: this.viewId + 'Review'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Print' ),
					id: 'pdf_review_print',
					group: 'review',
					icon: Icons.print,
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'pdf_review_print': //All report view
				//this.onNavigationClick( id );
				this.onViewClick( id );
				break;
		}
	}
}