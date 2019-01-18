WizardStep = Backbone.View.extend({
	name: null,
	previous_step_name: null,
	next_step_name: null,
	buttons: null,
	reload:null,
	wizard_obj: null, //rename to wizard_obj

	clicked_buttons: {},
	reload: false,

	el: null,
	api: null,

	//override in children
	name: 'undefined',
	title:  $.i18n._('Undefined Step'),
	instructions:  $.i18n._('Undefined step data'),

	initialize: function(wizard_obj) {
		this.buttons = {};
		this.reload = false;
		this.setWizardObject(wizard_obj);
		this.el = $('.wizard:visible .content');
		this.el.css( 'opacity', 0 );
		var $this = this;
		TTPromise.add('Wizard', 'initialize');
		TTPromise.wait('Wizard', 'initialize', function() {
			$this.el.css( 'opacity', 1 );
		});

		this.init();
	},

	//Children must always call render()
	init: function() {
		this.render();
	},

	initCardsBlock: function(){
		$('.wizard:visible #cards').html('');
	},

	setTitle: function(title) {
		var el = $('.wizard:visible .title-1');
		el.html( title );
	},

	setInstructions: function( instructions, callback ) {
		// if( typeof instructions == 'undefined' ){
		// 	instructions = this.instructions;
		// }
		var el = $('<p class="instructions"/>');
		el.html( instructions );
		this.el.append(el);

		if ( typeof callback == 'function' ) {
			callback();
		}
	},


	setWizardObject: function(val){
		this.wizard_obj = val;
		this.el = this.wizard_obj.el;
	},
	getWizardObject: function(){
		return this.wizard_obj;
	},

	setNextStepName: function (val) {
		this.next_step_name = val;
	},

	getNextStepName: function () {
		return false;
	},

	setPreviousStepName: function (val) {
		this.previous_step_name = val;
	},

	getPreviousStepName: function () {
		return false;
	},

	render: function () {
		this.initCardsBlock();
		return this._render();
	},

	_render: function(){
		return;
		//always overrirde
	},

	append: function( content ) {
		$('.wizard:visible .content').append(content);
	},

	appendButton: function( button ) {
		$('.wizard:visible #cards').append(button);
	},

	setGrid: function( gridId, grid_div, allMultipleSelection ) {

		if ( !allMultipleSelection ) {
			allMultipleSelection = false;
		}

		this.append( grid_div );

		var grid = $( '#' + gridId );

		var grid_columns = this.getGridColumns(gridId);

		var $this = this;
		grid.jqGrid( {
			altRows: true,
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
			data: [],
			datatype: 'local',
			sortable: true,
			sorttype: 'text',
			height: 75,
			rowNum: 10000,
			colNames: [],
			colModel: grid_columns,
			viewrecords: true,
			multiselect: allMultipleSelection,
			multiboxonly: allMultipleSelection

		} );

		this.setGridSize( grid );
		this.setGridGroupColumns( gridId );

		return grid; //allowing chaining off this method.
	},

	getGridColumns: function( gridId, callBack ) {
		//override if step object needs a grid.
	},

	setGridAutoHeight: function( grid, length ) {
		if ( length > 0 && length < 10 ) {
			grid.setGridHeight( length * 23 );
		} else if ( length > 10 ) {
			grid.setGridHeight( 400 );
		}
	},


	setGridSize: function( grid ) {
		grid.setGridWidth( $('.wizard:visible .content .grid-div').width() - 11 );
		grid.setGridHeight(  $('.wizard:visible .content').height() - 150 ); //During merge, this wasn't in MASTER branch.
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
		$('<img/>')[0].src = icon;

		if ( typeof desc == 'undefined' ) {
			var button = $( '<li><div class="ribbon-sub-menu-icon" id="' + id + '"><img src="' + icon + '" >' + label + '</div></li>' );
			return button;
		}

		var container = $('<div class="wizard_icon_card" id="' + id + '"/>');

		var img = $('<img src="' + icon + '" />');


		var right_container = $('<div class="right_container"/>');

		var title = $('<h3 class="button_title"/>');
		title.html( label ? label : '' );

		var description = $('<div class="description"/>');
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

	onGridSelectRow: function( selected_id ){
		//
	},

	onGridDblClickRow: function( selected_id ){
		//
	},

	onNavigationClick: function(e, icon) {
		if ( e ) {
			this.addButtonClick(e, icon);
		}
		this._onNavigationClick(icon);
	},

	_onNavigationClick: function(icon) {
		//virtual
	},

	addButtonClick: function( e, icon ) {
		// $(e.target).addClass('clicked_wizard_icon');
		// $(e.target).find('img').addClass('disable-image');
		var element = $(e.target);
		if( !element.hasClass('wizard_icon_card') ) {
			element = $(e.target).parents('.wizard_icon_card');
		}
		element.addClass('clicked_wizard_icon');
		element.addClass('disable-image');

		this.clicked_buttons[icon] = true;
	},

	isButtonClicked: function( icon) {
		if ( this.clicked_buttons.hasOwnProperty( icon ) && typeof this.clicked_buttons[icon] != 'undefined' ) {
			return true;
		}
		return false;
	},

	addButton: function( context_name, icon_name, title, description, button_name ){
		if ( typeof button_name == 'undefined' ) {
			button_name = context_name;
		}

		var button = this.getRibbonButton(context_name, Global.getRibbonIconRealPath( icon_name ), title, description);

		var $this = this;
		button.unbind('click').bind('click', function (e) {
			$this.onNavigationClick(e, button_name);
		});
		//ribbon_button_box.find('ul').append(button);

		if ( this.isButtonClicked(button_name) ) {
			button.addClass('clicked_wizard_icon');
			button.addClass('disable-image');
		}

		this.buttons[icon_name] = button;
		this.appendButton(button);

		return button;
	},

	setGridGroupColumns: function( gridId ) {

	},

	urlClick: function( action_id ) {
		this.api.getURL(this.getWizardObject().selected_remittance_agency_event_id, action_id, {
			onResult: function( result ) {
				var url = result.getResult();
				window.open(url);
			},
		});
	},

});