(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["wizard-forgot_password-ForgotPasswordWizardController"],{1002:(t,e,i)=>{"use strict";i.r(e),i.d(e,{"ForgotPasswordWizardController":()=>r});class r extends BaseWizardController{constructor(t={}){_.defaults(t,{el:".wizard-bg"}),super(t)}init(){this.title=$.i18n._("Password Reset"),this.steps=1,this.current_step=1,this.default_data&&void 0!==this.default_data.api_class?this.api=this.default_data.api_class:this.api=TTAPI.APIAuthentication,this.render()}render(){super.render(),$(window).resize((function(){})),this.initCurrentStep()}buildCurrentStepUI(){var t=this;switch(this.content_div.empty(),this.stepsWidgetDic[this.current_step]={},this.current_step){case 1:var e=$(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),i=e.find(".form-item-label"),r=e.find(".form-item-input-div"),s=this.getTextInput("email");i.text($.i18n._("Email Address")+":"),r.unbind("keydown").bind("keydown",(function(e){13===e.keyCode&&t.onDoneClick()})),r.append(s),this.content_div.append(e),this.stepsWidgetDic[this.current_step][s.getField()]=s,this.stepsWidgetDic[this.current_step][s.getField()].focus()}}saveCurrentStep(){this.stepsDataDic[this.current_step]={};var t=this.stepsDataDic[this.current_step],e=this.stepsWidgetDic[this.current_step];for(var i in this.current_step,e)e.hasOwnProperty(i)&&(t[i]=e[i].getValue())}buildCurrentStepData(){}onCloseClick(){$(this.el).remove(),LocalCacheData.current_open_wizard_controller=null,LocalCacheData.extra_filter_for_next_open_view=null}onDoneClick(){var t=this;super.onDoneClick(),this.saveCurrentStep();var e=this.stepsDataDic[1].email;this.stepsWidgetDic[1].email.clearErrorStyle(),e?this.api.resetPassword(e,{onResult:function(e){e.isValid()?(t.onCloseClick(),t.call_back&&t.call_back(e)):TAlertManager.showErrorAlert(e)}}):this.stepsWidgetDic[1].email.setErrorStyle($.i18n._("Email must be specified"),!0)}showErrorAlert(t){var e=t.getDetails();e||(e=t.getDescription());var i="";Global.isArray(e)||"object"==typeof e?$.each(e,(function(t,e){for(var r in e.hasOwnProperty("error")&&(e=e.error),e)i=i+e[r]+"<br>"})):i=e,IndexViewController.instance.router.showTipModal(i)}}}}]);
//# sourceMappingURL=wizard-forgot_password-ForgotPasswordWizardController.bundle.js.map?v=44d1c9ac8cf5e9c7c5cd