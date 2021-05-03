(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["company-hierarchy_control-HierarchyControlViewController"],{3433:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"HierarchyControlViewController":()=>r});class r extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#hierarchy_control_view_container",object_type_array:null,editor:null,hierarchy_level_api:null}),super(e)}init(e){this.edit_view_tpl="HierarchyControlEditView.html",this.permission_id="hierarchy",this.viewId="HierarchyControl",this.script_name="HierarchyControlView",this.table_name_key="hierarchy_control",this.context_menu_name=$.i18n._("Hierarchy"),this.navigation_label=$.i18n._("Hierarchy")+":",this.api=TTAPI.APIHierarchyControl,this.hierarchy_level_api=TTAPI.APIHierarchyLevel,this.render(),this.buildContextMenu(),this.initData(),this.setSelectRibbonMenuIfNecessary("HierarchyControl")}getCustomContextMenuModel(){return{exclude:[ContextMenuIconName.mass_edit],include:[]}}initOptions(){this.initDropDownOption("object_type","object_type")}buildEditViewUI(){super.buildEditViewUI();var e={"tab_hierarchy":{"label":$.i18n._("Hierarchy")},"tab_audit":!0};this.setTabModel(e),this.navigation.AComboBox({api_class:TTAPI.APIHierarchyControl,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_hierarchy",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i=this.edit_view_tab.find("#tab_hierarchy"),t=i.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(t);var r=Global.loadWidgetByName(FormItemType.TEXT_INPUT);r.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn($.i18n._("Name"),r,t,""),r.parent().width("45%"),(r=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"description",width:"100%"}),this.addEditFieldToColumn($.i18n._("Description"),r,t),r.parent().width("45%"),(r=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({allow_multiple_selection:!0,layout_name:"global_option_column",show_search_inputs:!1,set_empty:!0,key:"value",field:"object_type"}),r.setSourceData(this.object_type_array),this.addEditFieldToColumn($.i18n._("Objects"),r,t),(r=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!0,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"user"}),this.addEditFieldToColumn($.i18n._("Subordinates"),r,t,"");var a=i.find(".inside-editor-div"),o={level:$.i18n._("Level"),superiors:$.i18n._("Superiors")};this.editor=Global.loadWidgetByName(FormItemType.INSIDE_EDITOR),this.editor.InsideEditor({title:$.i18n._("NOTE: Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities."),addRow:this.insideEditorAddRow,getValue:this.insideEditorGetValue,setValue:this.insideEditorSetValue,removeRow:this.insideEditorRemoveRow,parent_controller:this,render:"views/company/hierarchy_control/HierarchyInsideEditorRender.html",render_args:o,api:this.hierarchy_level_api,row_render:"views/company/hierarchy_control/HierarchyInsideEditorRow.html"}),a.append(this.editor)}insideEditorSetValue(e){var i=e.length;if(this.removeAllRows(),i>0){for(var t=0;t<e.length;t++)if(Global.isSet(e[t])){var r=e[t];this.parent_controller.current_edit_record.id||(r.id=""),this.addRow(r)}}else this.getDefaultData()}setEditViewDataDone(){super.setEditViewDataDone(),this.initInsideEditorData()}initInsideEditorData(){var e=this,i={filter_data:{}};i.filter_data.hierarchy_control_id=this.current_edit_record.id?this.current_edit_record.id:this.copied_record_id?this.copied_record_id:"",this.current_edit_record&&this.current_edit_record.id||this.copied_record_id?this.hierarchy_level_api.getHierarchyLevel(i,!0,{onResult:function(i){if(e.edit_view){var t=i.getResult();e.editor.setValue(t)}}}):this.editor.addRow()}insideEditorRemoveRow(e){var i=e[0].rowIndex-1,t=this.rows_widgets_array[i].current_edit_item.id;TTUUID.isUUID(t)&&t!=TTUUID.zero_id&&t!=TTUUID.not_exist_id&&this.delete_ids.push(t),e.remove(),this.rows_widgets_array.splice(i,1),this.removeLastRowLine()}insideEditorAddRow(e,i){e||(e={});var t=this.getRowRender(),r=this.getRender(),a={},o=Global.loadWidgetByName(FormItemType.TEXT_INPUT);o.TTextInput({field:"level",width:50}),o.setValue(e.level?e.level:1),a[o.getField()]=o,t.children().eq(0).append(o),this.setWidgetEnableBaseOnParentController(o),(o=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIUser,width:132,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"user_id"}),a[o.getField()]=o,o.setValue(e.user_id?e.user_id:""),t.children().eq(1).append(o),void 0!==i?(t.insertAfter($(r).find("tr").eq(i)),this.rows_widgets_array.splice(i,0,a)):($(r).append(t),this.rows_widgets_array.push(a)),this.parent_controller.is_viewing&&t.find(".control-icon").hide(),this.setWidgetEnableBaseOnParentController(o),a.current_edit_item=e,this.parent_controller.current_edit_record.id||(a.current_edit_item.id=""),this.addIconsEvent(t),this.removeLastRowLine()}insideEditorGetValue(e){for(var i=this.rows_widgets_array.length,t=[],r=0;r<i;r++){var a=this.rows_widgets_array[r],o={level:a.level.getValue(),user_id:a.user_id.getValue()};o.hierarchy_control_id=e,o.id=a.current_edit_item.id?a.current_edit_item.id:"",t.push(o)}return t}onSaveResult(e){var i=this;if(e.isValid()){var t=e.getResult();!0===t?i.refresh_id=i.current_edit_record.id:TTUUID.isUUID(t)&&t!=TTUUID.zero_id&&t!=TTUUID.not_exist_id&&(i.refresh_id=t),i.saveInsideEditorData((function(){i.search(),i.onSaveDone(e),i.removeEditView()}))}else i.setErrorMenu(),i.setErrorTips(e)}onSaveAndCopyResult(e){var i=this;if(e.isValid()){var t=e.getResult();!0===t?i.refresh_id=i.current_edit_record.id:TTUUID.isUUID(t)&&t!=TTUUID.zero_id&&t!=TTUUID.not_exist_id&&(i.refresh_id=t),i.copied_record_id=i.refresh_id,i.saveInsideEditorData((function(){i.search(!1),i.onCopyAsNewClick()}))}else i.setErrorTips(e),i.setErrorMenu()}saveInsideEditorData(e){var i=this,t=this.editor.getValue(this.refresh_id),r=this.editor.delete_ids;r.length>0&&this.hierarchy_level_api.deleteHierarchyLevel(r,{onResult:function(e){i.editor.delete_ids=[]}}),this.hierarchy_level_api.ReMapHierarchyLevels(t,{onResult:function(t){var r=t.getResult();i.hierarchy_level_api.setHierarchyLevel(r,{onResult:function(i){e()}})}})}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:$.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("Description"),in_column:1,field:"description",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("Superior"),in_column:1,field:"superior_user_id",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Subordinate"),in_column:1,field:"user_id",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Object Type"),in_column:2,field:"object_type",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}_continueDoCopyAsNew(){if(this.setCurrentEditViewState("new"),LocalCacheData.current_doing_context_action="copy_as_new",Global.isSet(this.edit_view))for(var e=0;e<this.editor.rows_widgets_array.length;e++)this.editor.rows_widgets_array[e].current_edit_item.id="";super._continueDoCopyAsNew()}onCopyAsNewResult(e){var i=this,t=e.getResult();if(!t)return TAlertManager.showAlert($.i18n._("Record does not exist")),void i.onCancelClick();i.openEditView(),t=t[0],this.copied_record_id=t.id,t.id="",i.sub_view_mode&&i.parent_key&&(t[i.parent_key]=i.parent_value),i.current_edit_record=t,i.initEditView()}}}}]);
//# sourceMappingURL=company-hierarchy_control-HierarchyControlViewController.bundle.js.map?v=c15ba2f3f7ddc960c5d5