(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["hr-qualification-UserSkillViewController"],{1634:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"UserSkillViewController":()=>l});class l extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#user_skill_view_container",proficiency_array:null,document_object_type_id:null,qualification_group_api:null,qualification_api:null,qualification_group_array:null,source_type_array:null,qualification_array:null,sub_view_grid_autosize:!0}),super(e)}init(e){this.edit_view_tpl="UserSkillEditView.html",this.permission_id="user_skill",this.viewId="UserSkill",this.script_name="UserSkillView",this.table_name_key="user_skill",this.context_menu_name=$.i18n._("Skills"),this.navigation_label=$.i18n._("Skill")+":",this.api=TTAPI.APIUserSkill,this.qualification_api=TTAPI.APIQualification,this.qualification_group_api=TTAPI.APIQualificationGroup,this.document_object_type_id=125,this.render(),this.sub_view_mode||(this.buildContextMenu(),this.initData(),this.setSelectRibbonMenuIfNecessary("UserSkill"))}showNoResultCover(e){super.showNoResultCover(!!this.sub_view_mode)}onGridSelectRow(){this.sub_view_mode?(this.buildContextMenu(!0),this.cancelOtherSubViewSelectedStatus()):this.buildContextMenu(),this.setDefaultMenu()}onGridSelectAll(){this.sub_view_mode&&(this.buildContextMenu(!0),this.cancelOtherSubViewSelectedStatus()),this.setDefaultMenu()}cancelOtherSubViewSelectedStatus(){switch(!0){case void 0!==this.parent_view_controller.sub_user_education_view_controller:this.parent_view_controller.sub_user_education_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_user_license_view_controller:this.parent_view_controller.sub_user_license_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_user_membership_view_controller:this.parent_view_controller.sub_user_membership_view_controller.unSelectAll();case void 0!==this.parent_view_controller.sub_user_language_view_controller:this.parent_view_controller.sub_user_language_view_controller.unSelectAll()}}onAddClick(){this.sub_view_mode&&this.buildContextMenu(!0),super.onAddClick()}initOptions(){var e=this;this.initDropDownOption("proficiency"),this.initDropDownOption("source_type"),this.qualification_group_api.getQualificationGroup("",!1,!1,{onResult:function(i){i=i.getResult(),i=Global.buildTreeRecord(i),e.qualification_group_array=i,!e.sub_view_mode&&e.basic_search_field_ui_dic.group_id&&(e.basic_search_field_ui_dic.group_id.setSourceData(i),e.adv_search_field_ui_dic.group_id.setSourceData(i))}});var i={},t={type_id:[10]};i.filter_data=t,this.qualification_api.getQualification(i,{onResult:function(i){i=i.getResult(),e.qualification_array=i,!e.sub_view_mode&&e.basic_search_field_ui_dic.qualification_id&&(e.basic_search_field_ui_dic.qualification_id.setSourceData(i),e.adv_search_field_ui_dic.qualification_id.setSourceData(i))}})}onMassEditClick(){var e=this;e.is_add=!1,e.is_viewing=!1,e.is_mass_editing=!0,LocalCacheData.current_doing_context_action="mass_edit",e.openEditView();var i={},t=this.getGridSelectIdArray();t.length;this.mass_edit_record_ids=[],$.each(t,(function(i,t){e.mass_edit_record_ids.push(t)})),i.filter_data={},i.filter_data.id=this.mass_edit_record_ids,this.api["getCommon"+this.api.key_name+"Data"](i,{onResult:function(i){var t=i.getResult();e.unique_columns={},e.linked_columns={},t||(t=[]),e.sub_view_mode&&e.parent_key&&(t[e.parent_key]=e.parent_value),e.current_edit_record=t,e.initEditView()}})}buildEditViewUI(){super.buildEditViewUI();var e={"tab_skill":{"label":$.i18n._("Skill")},"tab_attachment":!0,"tab_audit":!0};this.setTabModel(e),this.navigation.AComboBox({api_class:TTAPI.APIUserSkill,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_user_skill",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i,t,l,a=this.edit_view_tab.find("#tab_skill").find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(a),(i=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!1,layout_name:"global_user",field:"user_id",set_empty:!0,show_search_inputs:!0});var _={permission_section:"user_skill"};i.setDefaultArgs(_),this.addEditFieldToColumn($.i18n._("Employee"),i,a,"");var s={},r={type_id:[10]};s.filter_data=r,(i=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIQualification,allow_multiple_selection:!1,layout_name:"global_qualification",show_search_inputs:!0,set_empty:!0,field:"qualification_id"}),i.setDefaultArgs(s),this.addEditFieldToColumn($.i18n._("Skill"),i,a),(i=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"proficiency_id",set_empty:!0}),i.setSourceData(this.proficiency_array),this.addEditFieldToColumn($.i18n._("Proficiency"),i,a),(i=Global.loadWidgetByName(FormItemType.DATE_PICKER)).TDatePicker({field:"first_used_date"}),this.addEditFieldToColumn($.i18n._("First Used Date"),i,a),(i=Global.loadWidgetByName(FormItemType.DATE_PICKER)).TDatePicker({field:"last_used_date"}),this.addEditFieldToColumn($.i18n._("Last Used Date"),i,a);var o=[];(i=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"experience",width:50}),o.push(i),t=$('<div class="widget-h-box"></div>'),l=$("<span class='widget-right-label'> "+$.i18n._("Automatic")+"</span>"),t.append(i),t.append(l),(i=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_calc_experience"}),o.push(i),t.append(i),this.addEditFieldToColumn($.i18n._("Years Experience"),o,a,"",t),(i=Global.loadWidgetByName(FormItemType.DATE_PICKER)).TDatePicker({field:"expiry_date"}),this.addEditFieldToColumn($.i18n._("Expiry Date"),i,a,"",null),(i=Global.loadWidgetByName(FormItemType.TEXT_AREA)).TTextArea({field:"description",width:"100%"}),this.addEditFieldToColumn($.i18n._("Description"),i,a,"",null,null,!0),i.parent().width("45%"),(i=Global.loadWidgetByName(FormItemType.TAG_INPUT)).TTagInput({field:"tag",object_type_id:251}),this.addEditFieldToColumn($.i18n._("Tags"),i,a,"",null,null,!0)}buildSearchFields(){super.buildSearchFields();var e={permission_section:"user_skill"};this.search_fields=[new SearchField({label:$.i18n._("Employee"),in_column:1,field:"user_id",default_args:e,layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Proficiency"),in_column:1,field:"proficiency_id",multiple:!0,basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Skill"),in_column:1,field:"qualification_id",layout_name:"global_qualification",api_class:TTAPI.APIQualification,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Group"),in_column:2,multiple:!0,field:"group_id",layout_name:"global_tree_column",tree_mode:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Source"),in_column:2,multiple:!0,field:"source_type_id",basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Expiry Date"),in_column:1,field:"expiry_date",tree_mode:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.DATE_PICKER}),new SearchField({label:$.i18n._("Tags"),field:"tag",basic_search:!0,adv_search:!0,in_column:1,object_type_id:251,form_item_type:FormItemType.TAG_INPUT}),new SearchField({label:$.i18n._("First Used Date"),in_column:2,field:"first_used_date",tree_mode:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.DATE_PICKER}),new SearchField({label:$.i18n._("Last Used Date"),in_column:2,field:"last_used_date",tree_mode:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.DATE_PICKER}),new SearchField({label:$.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX})]}setEditViewDataDone(){super.setEditViewDataDone(),this.current_edit_record.enable_calc_experience?this.edit_view_ui_dic.experience.setEnabled(!1):this.edit_view_ui_dic.experience.setEnabled(!0)}onFormItemChange(e,i){this.setIsChanged(e),this.setMassEditingFieldsWhenFormChange(e);var t=e.getField(),l=e.getValue();switch(t){case"enable_calc_experience":l?this.calcExperience():this.edit_view_ui_dic.experience.setEnabled(!0);break;case"last_used_date":case"first_used_date":this.current_edit_record.enable_calc_experience&&this.calcExperience()}this.current_edit_record[t]=l,i||this.validate()}calcExperience(){this.edit_view_ui_dic.experience.setEnabled(!1);var e=this.edit_view_ui_dic.first_used_date.getValue(),i=this.edit_view_ui_dic.last_used_date.getValue();if(i=i||(new Date).format(),""!==e&&""!==i){var t=this.api.calcExperience(e,i,{async:!1});if(t){var l=t.getResult();this.edit_view_ui_dic.experience.setValue(l)}}else this.edit_view_ui_dic.experience.setValue(0)}searchDone(){super.searchDone(),TTPromise.resolve("Employee_Qualifications_Tab","UserSkillViewController")}}l.loadSubView=function(e,i,t){Global.loadViewSource("UserSkill","SubUserSkillView.html",(function(l){var a=_.template(l);Global.isSet(i)&&i(),Global.isSet(e)&&(e.html(a({})),Global.isSet(t)&&t(sub_user_skill_view_controller))}))}}}]);
//# sourceMappingURL=hr-qualification-UserSkillViewController.bundle.js.map?v=2ae0fb245df4aa8478f4