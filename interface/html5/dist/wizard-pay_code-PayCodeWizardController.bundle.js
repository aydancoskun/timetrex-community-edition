(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["wizard-pay_code-PayCodeWizardController"],{538:(e,t,s)=>{"use strict";s.r(t),s.d(t,{"PayCodeWizardController":()=>i});class i extends BaseWizardController{constructor(e={}){_.defaults(e,{el:".wizard-bg"}),super(e)}init(e){this.title=$.i18n._("Migrate Pay Codes"),this.steps=2,this.current_step=1,$(this.el).width(1010),this.render()}render(){super.render(),this.initCurrentStep()}buildCurrentStepUI(){switch(this.content_div.empty(),this.current_step){case 1:var e=this.getLabel();e.html($.i18n._("This wizard will migrate data associated with one pay code to another pay code without recalculating timesheets or otherwise affecting employees time or wages.")+'<br><br><span style="color: #ff0000; font-weight: bold;">'+$.i18n._("WARNING")+": </span>"+$.i18n._("This operation can not be reversed once complete.")),this.content_div.append(e);break;case 2:var t=$($(this.el).find(".wizard-content")).clone();this.stepsWidgetDic[this.current_step]={};var s=t.find(".first-hr");s.find(".wizard-item-label > span").text($.i18n._("Select Source Pay Codes")+": ");var i=this.getAComboBox(TTAPI.APIPayCode,!0,"global_pay_code","source_pay_code_ids");s.find(".wizard-item-widget").append(i),this.stepsWidgetDic[this.current_step][i.getField()]=i;var a=t.find(".second-hr");a.find(".wizard-item-label > span").text($.i18n._("Select Destination Pay Code")+": "),i=this.getAComboBox(TTAPI.APIPayCode,!1,"global_pay_code","dest_pay_code_id"),a.find(".wizard-item-widget").append(i),this.stepsWidgetDic[this.current_step][i.getField()]=i,t.appendTo(this.content_div)}}buildCurrentStepData(){}onDoneClick(){var e=this;super.onDoneClick(),this.saveCurrentStep();var t=this.stepsDataDic[2].source_pay_code_ids,s=this.stepsDataDic[2].dest_pay_code_id;TTAPI.APIPayCode.migratePayCode(t,s,{onResult:function(t){t.getResult()?(e.onCloseClick(),e.call_back&&e.call_back()):TAlertManager.showErrorAlert(t)}})}setCurrentStepValues(){if(this.stepsDataDic[this.current_step]){var e=this.stepsDataDic[this.current_step],t=this.stepsWidgetDic[this.current_step];switch(this.current_step){case 1:break;case 2:e.source_pay_code_ids&&t.source_pay_code_ids.setValue(e.source_pay_code_ids),e.dest_pay_code_id&&t.dest_pay_code_id.setValue(e.dest_pay_code_id)}}}saveCurrentStep(){this.stepsDataDic[this.current_step]={};var e=this.stepsDataDic[this.current_step],t=this.stepsWidgetDic[this.current_step];switch(this.current_step){case 1:break;case 2:e.source_pay_code_ids=t.source_pay_code_ids.getValue(),e.dest_pay_code_id=t.dest_pay_code_id.getValue()}}setDefaultDataToSteps(){if(!this.default_data)return null;this.stepsDataDic[2]={},this.getDefaultData("source_pay_code_ids")&&(this.stepsDataDic[2].source_pay_code_ids=this.getDefaultData("source_pay_code_ids")),this.getDefaultData("dest_pay_code_id")&&(this.stepsDataDic[2].dest_pay_code_id=this.getDefaultData("dest_pay_code_id"))}}}}]);
//# sourceMappingURL=wizard-pay_code-PayCodeWizardController.bundle.js.map?v=2aab751c59948dd780b8