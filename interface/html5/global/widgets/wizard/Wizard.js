/**
 * For an example of implementation see: interface/html5/views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizard.js
 *
 * CRITICAL: ALL WIZARDS MUST HAVE A HOME STEP SO THAT THEY HAVE SOMEWHERE TO START.
 **/

Wizard = Backbone.View.extend({

	current_step: false,
	wizard_id: 'generic_wizard',
	wizard_name: $.i18n._('Wizard'),
	step_history: {},
	step_objects: {},
	el: $('.wizard'),

	initialize: function () {
		this.el = $('.wizard:visible .content');
		var $this = this;

		this.initStepObject( ( this.getCurrentStepName() ? this.getCurrentStepName() : 'home' ), function(obj){
			$this.init();
			$this.render();
			$this.enableButtons();

			LocalCacheData.current_open_wizard_controller = $this;
		});
	},

	//always override
	init: function () {
		return;
	},

	events: {
		'click .close-btn': 'onCloseClick',
		'click .close-icon': 'onCloseClick',
		'click .wizard-overlay': 'onCloseClick',
		'click .forward-btn': 'onNextClick',
		'click .back-btn': 'onPrevClick',
		'click .done-btn': 'onDone'
	},

	onNextClick: function (e) {
		if ( e === true || $(e.target).hasClass('disable-image') == false ) {
			var name = this.getStepObject().getNextStepName();
			var $this = this;
			this.initStepObject(name, function (step_obj) {
				step_obj.setPreviousStepName($this.getCurrentStepName());
				$this.setCurrentStepName(name);
				$this.enableButtons();
			});
		}
	},

	onPrevClick: function (e) {
		if ( e === true || $(e.target).hasClass('disable-image') == false ) {
			var name = this.getStepObject().getPreviousStepName();
			var $this = this;

			//Needs to be initialized in the event that we came back from the min_tab.
			this.initStepObject(name, function (step_obj) {
				//step_obj.setPreviousStepName($this.getCurrentStepName());
				$this.setCurrentStepName(name);
				$this.enableButtons();
			});
		}
	},

	onCloseClick: function (e) {
		if ( !e || $(e.target).hasClass('disable-image') == false ) {
			this.cleanUp();
		}
	},

	onDone: function (e) {
		if ( !e || $(e.target).hasClass('disable-image') == false ) {
			this.cleanUp();
		}
	},

	addStepObject: function (name, obj) {
		//always override.
		this.step_objects[name] = obj;
		return this.step_objects[name]; //returned for chaining.
	},


	getStepObject: function (name) {
		if (typeof name == 'undefined') {
			name = this.getCurrentStepName();
		}

		if ( typeof this.step_objects[name] == 'object' ) {
			return this.step_objects[name];
		}
		return this.step_objects['home']
	},

	getCurrentStepName: function () {
		return this.current_step;
	},

	setCurrentStepName: function (val) {
		this.current_step = val;
	},

	//Stub to stop backbone from complaining that it's missing, Wizard really doesn't render itself as such, it just displays its template.
	render: function(){},

	/*
	* Clean up the markup.
	*/
	cleanUp: function () {
		$('.wizard').remove();
		for ( var n in  this.step_objects ) {
			if ( this.step_objects[n] ) {
				this.step_objects[n].reload = true;
			}
		}

		LocalCacheData.current_open_wizard_controller = null;
	},

	/**
	 * setup a step object
	 *
	 * @param name
	 * @param callback
	 */
	initStepObject: function (name, callback) {
		var $this = this
		if ( this._step_map.hasOwnProperty(name) ) {
			if (this.step_objects[name] == null || typeof this.step_objects[name] != 'object') {
				Global.loadScript(this._step_map[name].script_path, function () {
					$this.setCurrentStepName(name);
					$('.wizard:visible .content').html('');

					var obj = new window[$this._step_map[name].object_name]($this);
					obj.reload = false;
					$this.addStepObject(name, obj);


					if (typeof callback == 'function') {
						callback(obj);
					}
				});
				return;
			} else {
				//reopening a step
				$this.setCurrentStepName(name);
				if (typeof callback == 'function') {
					var obj = this.step_objects[name];

					$('.wizard:visible .content').html('');
					obj = new window[this._step_map[name].object_name](this);

					//reopening a step that has been opened in a previously closed wizard.
					if ( this.step_objects[name].reload == true ) {
						obj.clicked_buttons = {};
						obj.reload = false;
					}

					this.addStepObject(name, obj);

					callback( obj );
				}
				return;
			}
		}
	},


	/**
	 * Enables the next/prev buttons
	 * the step object for the first step should return false instead fo a previous step name to disable the previous button
	 * the step object for the last step should return false instead of a next step name to disable the next button and enable the done button.
	 */
	enableButtons: function() {
		var step = this.getStepObject();

		if ( typeof step.getNextStepName() != 'string' ) {
			$('.wizard .forward-btn').addClass('disable-image');
		} else {
			$('.wizard .forward-btn').removeClass('disable-image');
		}

		if ( typeof step.getPreviousStepName() != 'string' ) {
			$('.wizard .back-btn').addClass('disable-image');
		} else {
			$('.wizard .back-btn').removeClass('disable-image');
		}

		if ( typeof step.getPreviousStepName() == 'string' && typeof step.getNextStepName() != 'string' ) {
			$('.wizard .done-btn').removeClass('disable-image');
		} else {
			$('.wizard .done-btn').addClass('disable-image');
		}

		this._enableButtons();
	},

	//override me.
	_enableButtons: function(){
	},

	/**
	 * minimize the wiazrd to a min_tab
	 */
	minimize: function(){
		LocalCacheData.PayrollRemittanceAgencyEventWizard = this;
		Global.addViewTab( this.wizard_id, this.wizard_name, window.location.href );
		this.delegateEvents();
		$('.wizard').remove();
	},

	reload: function(){
		for( var i in this.step_objects ) {
			this.step_objects[i].reload = true;
		}
	},

	disableForCommunity: function( callback ){
		if ( LocalCacheData.getCurrentCompany().product_edition_id < 15 ) {
			TAlertManager.showAlert(Global.getUpgradeMessage(), $.i18n._('Denied'));
		} else {
			if ( typeof callback == 'function' ) {
				callback();
			}
		}

	},

});