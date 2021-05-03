(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["my_account-user_contact-LoginUserContactViewController"],{5345:(e,t,i)=>{"use strict";i.r(t),i.d(t,{"LoginUserContactViewController":()=>d});class d extends BaseViewController{constructor(e={}){_.defaults(e,{sex_array:null,company_api:null}),super(e)}init(e){this.permission_id="user",this.viewId="LoginUserContact",this.script_name="LoginUserContactView",this.table_name_key="bank_account",this.context_menu_name=$.i18n._("Contact Information"),this.api=TTAPI.APIUser,this.company_api=TTAPI.APICompany,this.render(),this.buildContextMenu(),this.initData()}render(){super.render()}initOptions(e){var t=[{option_name:"sex",field_name:"sex_id",api:this.api}];this.initDropDownOptions(t,(function(t){e&&e(t)}))}getCustomContextMenuModel(){return{exclude:["default"],include:[ContextMenuIconName.save,ContextMenuIconName.cancel]}}getUserContactData(e){var t={filter_data:{}};t.filter_data.id=LocalCacheData.loginUser.id,this.api["get"+this.api.key_name](t,{onResult:function(t){var i=t.getResult();Global.isSet(i[0])&&e(i[0])}})}openEditView(){var e=this;e.edit_only_mode&&e.initOptions((function(t){e.edit_view||e.initEditViewUI("LoginUserContact","LoginUserContactEditView.html"),e.getUserContactData((function(t){e.current_edit_record=t,e.initEditView()}))}))}setCurrentEditRecordData(){for(var e in this.current_edit_record)if(this.current_edit_record.hasOwnProperty(e)){var t=this.edit_view_ui_dic[e];if(Global.isSet(t))switch(e){case"country":t.setValue(this.current_edit_record[e]);break;case"sin":this.current_edit_record[e]?t.setValue(this.current_edit_record[e]):t.setValue("N/A");break;default:t.setValue(this.current_edit_record[e])}}this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}onSaveClick(e){super.onSaveClick(true)}setErrorMenu(){for(var e=this.context_menu_array.length,t=0;t<e;t++){var i=$(this.context_menu_array[t]),d=$(i.find(".ribbon-sub-menu-icon")).attr("id");switch(i.removeClass("disable-image"),d){case ContextMenuIconName.cancel:break;default:i.addClass("disable-image")}}}buildEditViewUI(){super.buildEditViewUI();var e={"tab_contact_information":{"label":$.i18n._("Contact Information")}};this.setTabModel(e);var t=this.edit_view_tab.find("#tab_contact_information"),i=t.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(i),(d=Global.loadWidgetByName(FormItemType.PASSWORD_INPUT)).TPasswordInput({field:"current_password",width:200}),this.addEditFieldToColumn($.i18n._("Current Password"),d,i);var d=Global.loadWidgetByName(FormItemType.TEXT);d.TText({field:"first_name"}),this.addEditFieldToColumn($.i18n._("First Name"),d,i,""),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"middle_name"}),this.addEditFieldToColumn($.i18n._("Middle Name"),d,i),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"last_name"}),this.addEditFieldToColumn($.i18n._("Last Name"),d,i),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"address1",width:"100%"}),this.addEditFieldToColumn($.i18n._("Home Address(Line 1)"),d,i),d.parent().width("45%"),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"address2",width:"100%"}),this.addEditFieldToColumn($.i18n._("Home Address(Line 2)"),d,i),d.parent().width("45%"),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"city"}),this.addEditFieldToColumn($.i18n._("City"),d,i),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"country",set_empty:!0}),this.addEditFieldToColumn($.i18n._("Country"),d,i),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"province",set_empty:!0}),this.addEditFieldToColumn($.i18n._("Province/State"),d,i),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"postal_code",width:200}),this.addEditFieldToColumn($.i18n._("Postal/ZIP Code"),d,i,"");var o=t.find(".second-column");this.edit_view_tabs[0].push(o),(d=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"sex_id"}),d.setSourceData(this.sex_array),this.addEditFieldToColumn($.i18n._("Gender"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"work_phone",width:200}),this.addEditFieldToColumn($.i18n._("Work Phone"),d,o,""),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"work_phone_ext",width:100}),this.addEditFieldToColumn($.i18n._("Work Phone Ext"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"home_phone",width:200}),this.addEditFieldToColumn($.i18n._("Home Phone"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"mobile_phone",width:200}),this.addEditFieldToColumn($.i18n._("Mobile Phone"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"fax_phone",width:200}),this.addEditFieldToColumn($.i18n._("Fax"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"work_email",width:200}),this.addEditFieldToColumn($.i18n._("Work Email"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"home_email",width:200}),this.addEditFieldToColumn($.i18n._("Home Email"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"birth_date"}),this.addEditFieldToColumn($.i18n._("Birth Date"),d,o),(d=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"sin"}),this.addEditFieldToColumn($.i18n._("SIN/SSN"),d,o,"")}}}}]);
//# sourceMappingURL=my_account-user_contact-LoginUserContactViewController.bundle.js.map?v=df648f2f6ace8377aeea