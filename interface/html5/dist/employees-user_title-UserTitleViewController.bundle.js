(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["employees-user_title-UserTitleViewController"],{2321:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"UserTitleViewController":()=>l});class l extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#user_title_view_container"}),super(e)}init(e){this.edit_view_tpl="UserTitleEditView.html",this.permission_id="user",this.viewId="UserTitle",this.script_name="UserTitleView",this.table_name_key="user_title",this.context_menu_name=$.i18n._("Job Titles"),this.navigation_label=$.i18n._("Job Title")+":",this.api=TTAPI.APIUserTitle,this.render(),this.buildContextMenu(),this.initData(),this.setSelectRibbonMenuIfNecessary("UserTitle")}getCustomContextMenuModel(){return{exclude:[ContextMenuIconName.mass_edit],include:[]}}buildEditViewUI(){super.buildEditViewUI();var e={"tab_job_title":{"label":$.i18n._("Job Title")},"tab_audit":!0};this.setTabModel(e),this.navigation.AComboBox({api_class:TTAPI.APIUserTitle,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_user_title",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i=this.edit_view_tab.find("#tab_job_title").find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(i);var t=Global.loadWidgetByName(FormItemType.TEXT_INPUT);t.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn($.i18n._("Name"),t,i,"first_last"),t.parent().width("45%")}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:$.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}}}}]);
//# sourceMappingURL=employees-user_title-UserTitleViewController.bundle.js.map?v=1715f7f49e8ca44cbcf9