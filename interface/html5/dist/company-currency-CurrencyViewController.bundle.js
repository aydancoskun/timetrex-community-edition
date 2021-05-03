(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["company-currency-CurrencyViewController"],{1297:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"CurrencyViewController":()=>r});class r extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#currency_view_container",status_array:null,iso_codes_array:null,round_decimal_places_array:null,sub_currency_rate_view_controller:null}),super(e)}init(e){this.edit_view_tpl="CurrencyEditView.html",this.permission_id="currency",this.viewId="Currency",this.script_name="CurrencyView",this.table_name_key="currency",this.context_menu_name=$.i18n._("Currencies"),this.navigation_label=$.i18n._("Currency")+":",this.api=TTAPI.APICurrency,this.render(),this.buildContextMenu(),this.initData(),this.setSelectRibbonMenuIfNecessary("Currency")}initOptions(){var e=this;this.initDropDownOption("status"),this.initDropDownOption("round_decimal_places","round_decimal_places"),this.api.getISOCodesArray("",!1,!1,{onResult:function(i){i=i.getResult(),i=Global.buildRecordArray(i),e.basic_search_field_ui_dic.iso_code.setSourceData(i),e.iso_codes_array=i}})}buildEditViewUI(){super.buildEditViewUI();var e=this,i={"tab_currency":{"label":$.i18n._("Currency")},"tab_rates":{"label":$.i18n._("Rates"),"init_callback":"initSubCurrencyRateView","display_on_mass_edit":!1},"tab_audit":!0};this.setTabModel(i),this.navigation.AComboBox({api_class:TTAPI.APICurrency,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_currency",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var t,r,a=this.edit_view_tab.find("#tab_currency").find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(a),(t=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"status_id"}),t.setSourceData(e.status_array),this.addEditFieldToColumn($.i18n._("Status"),t,a,""),(t=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"iso_code"}),t.setSourceData(e.iso_codes_array),this.addEditFieldToColumn($.i18n._("ISO Currency"),t,a),(t=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn($.i18n._("Name"),t,a),t.parent().width("45%"),(t=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"is_base"}),this.addEditFieldToColumn($.i18n._("Base Currency"),t,a),(t=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"conversion_rate",width:114}),r=$("<div class=''></div>");var n=$("<span id='conversion_rate_clarification_box'></span>");r.append(t),r.append(n),this.addEditFieldToColumn($.i18n._("Conversion Rate"),[t],a,"",r,!1,!0),(t=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"is_default"}),this.addEditFieldToColumn($.i18n._("Default Currency"),t,a),(t=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"auto_update"}),this.addEditFieldToColumn($.i18n._("Auto Update"),t,a),(t=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"round_decimal_places"}),t.setSourceData(e.round_decimal_places_array),this.addEditFieldToColumn($.i18n._("Decimal Places"),t,a,"")}removeEditView(){super.removeEditView(),this.sub_currency_rate_view_controller=null}initSubCurrencyRateView(){var e=this;if(this.current_edit_record.id){if(this.sub_currency_rate_view_controller)return this.sub_currency_rate_view_controller.buildContextMenu(!0),this.sub_currency_rate_view_controller.setDefaultMenu(),e.sub_currency_rate_view_controller.parent_key="currency_id",e.sub_currency_rate_view_controller.parent_value=e.current_edit_record.id,e.sub_currency_rate_view_controller.parent_edit_record=e.current_edit_record,void e.sub_currency_rate_view_controller.initData();Global.loadScript("views/company/currency/CurrencyRateViewController.js",(function(){var r=e.edit_view_tab.find("#tab_rates").find(".first-column-sub-view");Global.trackView("SubCurrencyRateView",LocalCacheData.current_doing_context_action),CurrencyRateViewController.loadSubView(r,i,t)}))}else TTPromise.resolve("BaseViewController","onTabShow");function i(){}function t(i){e.sub_currency_rate_view_controller=i,e.sub_currency_rate_view_controller.parent_key="currency_id",e.sub_currency_rate_view_controller.parent_value=e.current_edit_record.id,e.sub_currency_rate_view_controller.parent_edit_record=e.current_edit_record,e.sub_currency_rate_view_controller.parent_view_controller=e,e.sub_currency_rate_view_controller.initData()}}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:$.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("ISO Currency"),in_column:1,field:"iso_code",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}onFormItemChange(e,i){"conversion_rate"==e.getField()&&this.setConversionRateExampleText(e.getValue(),this.edit_view_ui_dic.iso_code.getValue()),super.onFormItemChange(e,i)}initEditView(e,i){super.initEditView(),this.setConversionRateExampleText(this.edit_view_ui_dic.conversion_rate.getValue(),this.edit_view_ui_dic.iso_code.getValue())}}}}]);
//# sourceMappingURL=company-currency-CurrencyViewController.bundle.js.map?v=f0c3b4f7d08374812c6e