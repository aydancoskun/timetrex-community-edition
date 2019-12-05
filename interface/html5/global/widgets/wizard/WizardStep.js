WizardStep = Backbone.View.extend( {
	name: null,
	previous_step_name: null,
	next_step_name: null,
	buttons: null,
	reload: null,
	wizard_obj: null, //rename to wizard_obj

	clicked_buttons: {},
	reload: false,

	api: null,

	//override in children
	name: 'undefined',
	title: $.i18n._( 'Undefined Step' ),
	instructions: $.i18n._( 'Undefined step data' ),

	initialize: function( wizard_obj ) {
		this.buttons = {};
		this.reload = false;
		this.setWizardObject( wizard_obj );
		var $this = this;
		this.init();
	},

	//Children must always call render()
	init: function() {
		this.render();
	},

	initCardsBlock: function() {
		$( this.wizard_obj.el ).find( '#cards' ).html( '' );
	},

	setTitle: function( title ) {
		$( this.wizard_obj.el ).find( '.title-1' ).html( title );
	},

	setInstructions: function( instructions, callback ) {

		if ( $( this.el ).find( '.instructions' ).length == 0 ) {
			$( this.el ).find( '.progress-bar' ).append( '<p class="instructions"/>' );
		}

		$( this.el ).find( '.progress-bar .instructions' ).html( instructions );

		if ( typeof callback == 'function' ) {
			callback();
		}
	},


	setWizardObject: function( val ) {
		this.wizard_obj = val;
		this.el = this.wizard_obj.el;
	},
	getWizardObject: function() {
		return this.wizard_obj;
	},

	setNextStepName: function( val ) {
		this.next_step_name = val;
	},

	getNextStepName: function() {
		return false;
	},

	setPreviousStepName: function( val ) {
		this.previous_step_name = val;
	},

	getPreviousStepName: function() {
		return false;
	},

	render: function() {
		this.initCardsBlock();
		return this._render();
	},

	_render: function() {
		return;
		//always overrirde
	},

	append: function( content ) {
		$( this.wizard_obj.el ).find( '.content' ).append( content );
	},

	appendButton: function( button ) {
		$( this.wizard_obj.el ).find( '#cards' ).append( button );
	},

	setGrid: function( gridId, grid_div, allMultipleSelection ) {

		if ( !allMultipleSelection ) {
			allMultipleSelection = false;
		}

		$( '#' + gridId ).remove(); //Remove the grid to prevent JS Exception: Uncaught TypeError: Failed to execute 'replaceChild' on 'Node': parameter 2 is not of type 'Node'.

		this.append( grid_div );

		var grid = $( '#' + gridId );

		var grid_columns = this.getGridColumns( gridId );

		var $this = this;

		grid = new TTGrid( gridId, {
			onSelectRow: function( e ) {
				$this.onGridSelectRow( e );
			},
			onSelectAll: function( e ) {
				for ( var n in e ) {
					$this.onGridSelectRow( e[n] );
				}
			},
			ondblClickRow: function() {
				$this.onGridDblClickRow();
			},
			multiselect: false,
			winMultiSelect: false
		}, grid_columns );

		this.setGridSize( grid );
		this.setGridGroupColumns( gridId );

		return grid; //allowing chaining off this method.
	},

	getGridColumns: function( gridId, callBack ) {
		//override if step object needs a grid.
	},

	setGridAutoHeight: function( grid, length ) {
		if ( length > 0 && length < 10 ) {
			grid.grid.setGridHeight( length * 23 );
		} else if ( length > 10 ) {
			grid.grid.setGridHeight( 400 );
		}
	},


	setGridSize: function( grid ) {
		grid.grid.setGridWidth( $( this.wizard_obj.el ).find( '.content .grid-div' ).width() - 11 );
		grid.grid.setGridHeight( $( this.wizard_obj.el ).find( '.content' ).height() - 150 ); //During merge, this wasn't in MASTER branch.
	},

	getRibbonButtonBox: function() {
		var div = $( '<div class="menu ribbon-button-bar"></div>' );
		var ul = $( '<ul></ul>' );

		div.append( ul );

		return div;
	},

	/**
	 * to get old-style icons, don't provide desc
	 * to get card-style icons, provide desc
	 * to get card-style icons without a description, send a blank string ('') as desc
	 *
	 * @param id
	 * @param icon
	 * @param label
	 * @param desc
	 * @returns {*|jQuery|HTMLElement}
	 */
	getRibbonButton: function( id, icon, label, desc ) {
		//prelaod imgages to reduce the appearance of phantom flashing
		$( '<img/>' )[0].src = icon;

		if ( typeof desc == 'undefined' ) {
			var button = $( '<li><div class="ribbon-sub-menu-icon" id="' + id + '"><img src="' + icon + '" >' + label + '</div></li>' );
			return button;
		}

		var container = $( '<div class="wizard_icon_card" id="' + id + '"/>' );

		var img = $( '<img src="' + icon + '" />' );


		var right_container = $( '<div class="right_container"/>' );

		var title = $( '<h3 class="button_title"/>' );
		title.html( label ? label : '' );

		var description = $( '<div class="description"/>' );
		description.html( desc ? desc : '' );

		container.append( img );
		right_container.append( title );
		right_container.append( description );
		container.append( right_container );

		return container;
	},

	//
	//stubs that should be overrideen
	//

	onGridSelectRow: function( selected_id ) {
		//
	},

	onGridDblClickRow: function( selected_id ) {
		//
	},

	onNavigationClick: function( e, icon ) {
		if ( e ) {
			this.addButtonClick( e, icon );
		}

		//Prevent double clicking on tax wizard buttons.
		ProgressBar.showOverlay();

		//this flag is turned off in ProgressBarManager::closeOverlay, or 2s whichever happens first
		if ( window.clickProcessing == true ) {
			return;
		} else {
			window.clickProcessing = true;
			window.clickProcessingHandle = window.setTimeout( function() {
				if ( window.clickProcessing == true ) {
					window.clickProcessing = false;
					ProgressBar.closeOverlay();
				}
			}, 1000 );
		}

		this._onNavigationClick( icon );
	},

	//Overridden in each Wizard step.
	_onNavigationClick: function( icon ) {
	},

	addButtonClick: function( e, icon ) {
		// $(e.target).addClass('clicked_wizard_icon');
		// $(e.target).find('img').addClass('disable-image');
		var element = $( e.target );
		if ( !element.hasClass( 'wizard_icon_card' ) ) {
			element = $( e.target ).parents( '.wizard_icon_card' );
		}
		element.addClass( 'clicked_wizard_icon' );
		element.addClass( 'disable-image' );

		this.clicked_buttons[icon] = true;
	},

	isButtonClicked: function( icon ) {
		if ( this.clicked_buttons.hasOwnProperty( icon ) && typeof this.clicked_buttons[icon] != 'undefined' ) {
			return true;
		}
		return false;
	},

	addButton: function( context_name, icon_name, title, description, button_name ) {
		if ( typeof button_name == 'undefined' ) {
			button_name = context_name;
		}

		var button = this.getRibbonButton( context_name, Global.getRibbonIconRealPath( icon_name ), title, description );

		var $this = this;
		button.unbind( 'click' ).bind( 'click', function( e ) {
			$this.onNavigationClick( e, button_name );
		} );
		//ribbon_button_box.find('ul').append(button);

		if ( this.isButtonClicked( button_name ) ) {
			button.addClass( 'clicked_wizard_icon' );
			button.addClass( 'disable-image' );
		}

		this.buttons[icon_name] = button;
		this.appendButton( button );

		return button;
	},

	setGridGroupColumns: function( gridId ) {

	},

	urlClick: function( action_id ) {
		this.api.getMakePaymentData( this.getWizardObject().selected_remittance_agency_event_id, action_id, {
			onResult: function( result ) {
				var url = result.getResult();
				window.open( url );
			}
		} );
	},

	paymentServicesClick: function( action_id ) {
		this.api.getFileAndPayWithPaymentServicesData( this.getWizardObject().selected_remittance_agency_event_id, action_id, {
			onResult: function( result ) {
				var retval = result.getResult();

				if ( retval['user_message'] && retval['user_message'] != '' ) {
					TAlertManager.showAlert( retval['user_message'] );
				} else {
					if ( retval == false ) {
						TAlertManager.showAlert( $.i18n._( 'ERROR! Something went wrong, please contact customer service immediately!' ) );
					}
				}
			}
		} );
	}

} );