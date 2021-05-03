(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["hr-recruitment-JobApplicantLanguageViewController"],{2048:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"JobApplicantLanguageViewController":()=>a});class a extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#job_applicant_language_view_container",fluency_array:null,competency_array:null,qualification_array:null,qualification_api:null,sub_view_grid_autosize:!0}),super(e)}init(e){this.edit_view_tpl="JobApplicantLanguageEditView.html",this.permission_id="job_applicant",this.viewId="JobApplicantLanguage",this.script_name="JobApplicantLanguageView",this.table_name_key="job_applicant_language",this.context_menu_name=$.i18n._("Languages"),this.navigation_label=$.i18n._("Language")+":",this.api=TTAPI.APIJobApplicantLanguage,this.qualification_api=TTAPI.APIQualification,this.render(),this.sub_view_mode||this.initData()}showNoResultCover(e){super.showNoResultCover(!!this.sub_view_mode)}onGridSelectRow(){this.sub_view_mode?(this.buildContextMenu(!0),this.cancelOtherSubViewSelectedStatus()):this.buildContextMenu(),this.setDefaultMenu()}onGridSelectAll(){this.sub_view_mode&&(this.buildContextMenu(!0),this.cancelOtherSubViewSelectedStatus()),this.setDefaultMenu()}cancelOtherSubViewSelectedStatus(){switch(!0){case void 0!==this.parent_view_controller.sub_job_applicant_education_view_controller:this.parent_view_controller.sub_job_applicant_education_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_job_applicant_membership_view_controller:this.parent_view_controller.sub_job_applicant_membership_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_job_applicant_skill_view_controller:this.parent_view_controller.sub_job_applicant_skill_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_job_applicant_license_view_controller:this.parent_view_controller.sub_job_applicant_license_view_controller.unSelectAll()}}onMassEditClick(){var e=this;e.is_add=!1,e.is_viewing=!1,e.is_mass_editing=!0,LocalCacheData.current_doing_context_action="mass_edit",e.openEditView();var i={},t=this.getGridSelectIdArray();t.length;this.mass_edit_record_ids=[],$.each(t,(function(i,t){e.mass_edit_record_ids.push(t)})),i.filter_data={},i.filter_data.id=this.mass_edit_record_ids,this.api["getCommon"+this.api.key_name+"Data"](i,{onResult:function(i){var t=i.getResult();e.unique_columns={},e.linked_columns={},t||(t=[]),e.sub_view_mode&&e.parent_key&&(t[e.parent_key]=e.parent_value),e.current_edit_record=t,e.initEditView()}})}onAddClick(){this.sub_view_mode&&this.buildContextMenu(!0),super.onAddClick()}initOptions(){var e=this;this.initDropDownOption("fluency"),this.initDropDownOption("competency");var i={},t={type_id:[40]};i.filter_data=t,this.qualification_api.getQualification(i,{onResult:function(i){i=i.getResult(),e.qualification_array=i}})}buildEditViewUI(){super.buildEditViewUI();var e={"tab_language":{"label":$.i18n._("Language")},"tab_audit":!0};this.setTabModel(e),this.navigation.AComboBox({api_class:TTAPI.APIJobApplicantLanguage,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_job_applicant_language",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i,t=this.edit_view_tab.find("#tab_language").find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(t);var a={},l={type_id:[40]};a.filter_data=l,(i=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIQualification,allow_multiple_selection:!1,layout_name:"global_qualification",show_search_inputs:!0,set_empty:!0,field:"qualification_id"}),i.setDefaultArgs(a),this.addEditFieldToColumn($.i18n._("Language"),i,t,""),(i=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"fluency_id"}),i.setSourceData(this.fluency_array),this.addEditFieldToColumn($.i18n._("Fluency"),i,t),(i=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"competency_id"}),i.setSourceData(this.competency_array),this.addEditFieldToColumn($.i18n._("Competency"),i,t),(i=Global.loadWidgetByName(FormItemType.TEXT_AREA)).TTextArea({field:"description",width:"100%"}),this.addEditFieldToColumn($.i18n._("Description"),i,t,"",null,null,!0),i.parent().width("45%"),(i=Global.loadWidgetByName(FormItemType.TAG_INPUT)).TTagInput({field:"tag",object_type_id:393}),this.addEditFieldToColumn($.i18n._("Tags"),i,t,"",null,null,!0)}searchDone(){super.searchDone(),TTPromise.resolve("JobApplicant_Qualifications_Tab","JobApplicantLanguageViewController")}}a.loadSubView=function(e,i,t){Global.loadViewSource("JobApplicantLanguage","SubJobApplicantLanguageView.html",(function(a){var l=_.template(a);Global.isSet(i)&&i(),Global.isSet(e)&&(e.html(l({})),Global.isSet(t)&&t(sub_job_applicant_language_view_controller))}))}}}]);
//# sourceMappingURL=hr-recruitment-JobApplicantLanguageViewController.bundle.js.map?v=8d6b7b2f765d1f24fde8