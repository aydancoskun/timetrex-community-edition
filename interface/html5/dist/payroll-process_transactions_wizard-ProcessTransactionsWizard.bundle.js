(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["payroll-process_transactions_wizard-ProcessTransactionsWizard","wizard-Wizard"],{3207:(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";__webpack_require__.r(__webpack_exports__),__webpack_require__.d(__webpack_exports__,{"Wizard":()=>Wizard});var _views_TTBackboneView__WEBPACK_IMPORTED_MODULE_0__=__webpack_require__(6739);class Wizard extends _views_TTBackboneView__WEBPACK_IMPORTED_MODULE_0__.TTBackboneView{constructor(e={}){_.defaults(e,{current_step:!1,wizard_id:"generic_wizard",wizard_name:$.i18n._("Wizard"),step_history:{},step_objects:{},el:$(".wizard"),previous_wizard:null,_step_map:null,do_not_initialize_onload:!1,external_data:null,events:{"click .close-btn":"onCloseClick","click .close-icon":"onCloseClick","click .wizard-overlay.onclick-close":"onCloseClick","click .forward-btn":"onNextClick","click .back-btn":"onPrevClick","click .done-btn":"onDone"}}),super(e)}initialize(e){if(super.initialize(e),e&&e.external_data&&this.setExternalData(e.external_data),!this.do_not_initialize_onload){this.step_history={},this.step_objects={};var t=this;this.initStepObject(this.getCurrentStepName()?this.getCurrentStepName():"home",(function(e){t.init(),t.render(),t.enableButtons(),LocalCacheData.current_open_wizard_controller?t.previous_wizard=LocalCacheData.current_open_wizard_controller:t.previous_wizard=!1,LocalCacheData.current_open_wizard_controller=t}))}}init(){}setExternalData(e){this.external_data=e}getExternalData(){return this.external_data}onNextClick(e){if(1==this.button_click_procesing)return!1;if(0==$(e.target).hasClass("disable-image")){this.disableButtons();var t=this.getStepObject().getNextStepName(),a=this;this.initStepObject(t,(function(e){e.setPreviousStepName(a.getCurrentStepName()),a.setCurrentStepName(t)}))}}onPrevClick(e){if(1==this.button_click_procesing)return!1;if(!0===e||0==$(e.target).hasClass("disable-image")){this.disableButtons();var t=this.getStepObject().getPreviousStepName(),a=this;this.initStepObject(t,(function(e){a.setCurrentStepName(t)}))}}onCloseClick(e){if(!e||0==$(e.target).hasClass("disable-image")){var t=this;!1!==this.getStepObject().getPreviousStepName()&&!1!==this.getStepObject().getNextStepName()?TAlertManager.showConfirmAlert($.i18n._("Are you sure you wish to cancel without completing all steps for this event?"),null,(function(e){!0===e&&t.cleanUp()})):0==this.getStepObject().getNextStepName()?0==this.getStepObject().isRequiredButtonsClicked()?TAlertManager.showConfirmAlert($.i18n._("<strong>WARNING</strong>: You are about to cancel without performing all required actions on this step! <br><br><strong>This may result in payments or reports not being submitted to this agency.</strong> <br><br>Are you sure you wish to continue?<br><br>"),null,(function(e){!0===e&&t.cleanUp()})):TAlertManager.showConfirmAlert($.i18n._("Are you sure you wish to cancel without marking this event as completed?"),null,(function(e){!0===e&&t.cleanUp()})):t.cleanUp()}}onDone(e){if(!e||0==$(e.target).hasClass("disable-image")){var t=this;0==this.getStepObject().getNextStepName()&&0==this.getStepObject().isRequiredButtonsClicked()?TAlertManager.showConfirmAlert($.i18n._("<strong>WARNING</strong>: You are about to mark this event as completed without performing all required actions on this step! <br><br><strong>This may result in payments or reports not being submitted to this agency.</strong> <br><br>Are you sure you wish to continue?<br><br>"),null,(function(e){!0===e&&t.onDoneComplete()})):t.onDoneComplete()}}onDoneComplete(e){$this.cleanUp()}addStepObject(e,t){return this.step_objects[e]=t,this.step_objects[e]}getStepObject(e){return void 0===e&&(e=this.getCurrentStepName()),"object"==typeof this.step_objects[e]?this.step_objects[e]:this.step_objects.home}getCurrentStepName(){return this.current_step}setCurrentStepName(e){this.current_step=e}render(){}cleanUp(){for(var e in $(this.el).remove(),this.step_objects)this.step_objects[e]&&(this.step_objects[e].reload=!0);LocalCacheData.current_open_wizard_controller=null,$().TFeedback({source:this.wizard_id})}initStepObject(name,callback){if(this._step_map.hasOwnProperty(name)){if(null==this.step_objects[name]||"object"!=typeof this.step_objects[name]){var $this=this;return void Global.loadScript(this._step_map[name].script_path,(function(){$this.setCurrentStepName(name),$($this.el).find(".content").html("");var obj=eval("new "+$this._step_map[name].object_name+"( $this );");obj.reload=!1,$this.addStepObject(name,obj),"function"==typeof callback&&callback(obj)}))}if(this.setCurrentStepName(name),"function"==typeof callback){var obj=this.step_objects[name];$(this.el).find(".content").html("");var obj=eval("new "+this._step_map[name].object_name+"( this );");1==this.step_objects[name].reload&&(obj.clicked_buttons={},obj.reload=!1),this.addStepObject(name,obj),callback(obj)}}else;}disableButtons(){this.button_click_procesing=!0}enableButtons(){var e=this.getStepObject();"string"!=typeof e.getNextStepName()?($(this.el).find(".forward-btn").addClass("disable-image"),$(this.el).find(".done-btn").removeClass("disable-image")):($(this.el).find(".forward-btn").removeClass("disable-image"),$(this.el).find(".done-btn").addClass("disable-image")),"string"!=typeof e.getPreviousStepName()?$(this.el).find(".back-btn").addClass("disable-image"):$(this.el).find(".back-btn").removeClass("disable-image"),this._enableButtons(),this.button_click_procesing=!1}_enableButtons(){}minimize(){LocalCacheData.PayrollRemittanceAgencyEventWizard=this,Global.addViewTab(this.wizard_id,this.wizard_name,window.location.href),this.delegateEvents(),$(this.el).remove()}reload(){for(var e in this.step_objects)this.step_objects[e].reload=!0}disableForCommunity(e){Global.getProductEdition()<=10?TAlertManager.showAlert(Global.getUpgradeMessage(),$.i18n._("Denied")):"function"==typeof e&&e()}}},171:(e,t,a)=>{"use strict";a.r(t),a.d(t,{"ProcessTransactionsWizard":()=>i});var s=a(3207);class i extends s.Wizard{constructor(e={}){_.defaults(e,{el:$(".process_transactions_wizard"),current_step:!1,wizard_name:$.i18n._("Process Transactions"),selected_transaction_ids:[],wizard_id:"ProcessTransactionsWizard",_step_map:{"home":{script_path:"views/payroll/process_transactions_wizard/ProcessTransactionsWizardStepHome.js",object_name:"ProcessTransactionsWizardStepHome"}},api:null}),super(e)}setTransactionIds(e){this.selected_transaction_ids=e,this.selected_transaction_ids.length>0?$(".process_transactions_wizard .done-btn").removeClass("disable-image"):$(".process_transactions_wizard .done-btn").addClass("disable-image")}onDone(e){if(e&&0==$(e.target).hasClass("disable-image")){var t=LocalCacheData.current_open_wizard_controller,a={filter_data:{}},s=t.getExternalData();s&&(s.filter_data?a.filter_data=s.filter_data:a.filter_data=s),a&&a.filter_data||(a.filter_data={}),a.filter_data.remittance_source_account_id||(a.filter_data.remittance_source_account_id=[]),a.filter_data.setup_last_check_number||(a.setup_last_check_number={});var i=$("#process_transactions_wizard_source_account_table tr");if(i.length>0)for(var r=0;r<i.length;r++){var n=$(i[r]);n.find('[type="checkbox"]').is(":checked")&&(a.filter_data.remittance_source_account_id.push(n.find('[type="checkbox"]').val()),a.setup_last_check_number[n.find('[type="checkbox"]').val()]=n.find("input.last_transaction_number").val())}if(a.filter_data.remittance_source_account_id.length>0){$(e.target).addClass("disable-image");var o={0:a,1:!0,2:"export_transactions"},c=TTAPI.APIPayStub;Global.APIFileDownload(c.className,"getPayStub",o)}else Debug.Text("No source accounts selected","ProcessTransactionsWizard.js","ProcessTransactionsWizard","onDone",10);t.onCloseClick(!0)}}onCloseClick(e){(!0===e||e&&0==$(e.target).hasClass("disable-image"))&&($("#min_tab_ProcessPayrollWizard")[0]&&$($("#min_tab_ProcessPayrollWizard")[0]).click(),this.cleanUp())}}}}]);
//# sourceMappingURL=payroll-process_transactions_wizard-ProcessTransactionsWizard.bundle.js.map?v=ae25c4d5104950292a1e