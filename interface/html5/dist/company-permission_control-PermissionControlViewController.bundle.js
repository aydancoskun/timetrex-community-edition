(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["company-permission_control-PermissionControlViewController"],{5448:(e,i,t)=>{"use strict";t.r(i),t.d(i,{"PermissionControlViewController":()=>r});class r extends BaseViewController{constructor(e={}){_.defaults(e,{el:"#permission_control_view_container",level_array:null,user_api:null,permission_array:null,quick_search_dic:{},quick_search_typed_keys:"",quick_search_timer:null}),super(e)}init(e){this.edit_view_tpl="PermissionControlEditView.html",this.permission_id="permission",this.viewId="PermissionControl",this.script_name="PermissionControlView",this.table_name_key="permission_control",this.context_menu_name=$.i18n._("Permission Group"),this.navigation_label=$.i18n._("Permission Group")+":",this.api=TTAPI.APIPermissionControl,this.user_api=TTAPI.APIUser,this.render(),this.buildContextMenu(),this.initData(),this.setSelectRibbonMenuIfNecessary()}onKeyDown(e){var i=$(":focus"),t=this;if(0===this.edit_view_tab_selected_index&&!LocalCacheData.openAwesomeBox&&(i.length<1||"input"!==i[0].localName)){var r=this.edit_view_ui_dic.permission;if(39===e.keyCode)e.preventDefault(),r.getAllowMultipleSelection()&&r.onUnSelectGridDoubleClick();else if(37===e.keyCode)e.preventDefault(),r.getAllowMultipleSelection()&&r.onSelectGridDoubleClick();else{if(16===e.keyCode||17===e.keyCode||91===e.keyCode)return;var s,o,n;if(this.quick_search_timer&&clearTimeout(this.quick_search_timer),this.quick_search_timer=setTimeout((function(){t.quick_search_typed_keys=""}),200),e.preventDefault(),this.quick_search_typed_keys=this.quick_search_typed_keys+Global.KEYCODES[e.which],r.getAllowMultipleSelection()||r.getTreeMode()){if(this.quick_search_typed_keys){s=r.getFocusInSeletGrid()?r.getSelectGrid():r.getUnSelectGrid();var a,_=this.quick_search_dic[this.quick_search_typed_keys]?this.quick_search_dic[this.quick_search_typed_keys]:0,l=$(s.grid.find("tr").find("td:eq(1)").filter((function(){return 0==$.text([this]).toLowerCase().indexOf(t.quick_search_typed_keys)})));_>0&&_<l.length||(_=0),a=$(l[_]),r.unSelectAll(s,!0),o=a.parent().index()-1,n=s.grid.jqGrid("getGridParam","data")[o],r.setSelectItem(n,s),this.quick_search_dic={},this.quick_search_dic[this.quick_search_typed_keys]=_+1}}else this.quick_search_typed_keys&&(_=this.quick_search_dic[this.quick_search_typed_keys]?this.quick_search_dic[this.quick_search_typed_keys]:0,l=$(r.getUnSelectGrid().find("tr").find("td:first").filter((function(){return 0==$.text([this]).toLowerCase().indexOf(t.quick_search_typed_keys)}))),_>0&&_<l.length||(_=0),o=(a=$(l[_])).parent().index()-1,n=this.getItemByIndex(o),r.setSelectItem(n),this.quick_search_dic={},this.quick_search_dic[this.quick_search_typed_keys]=_+1)}}}setSubLogViewFilter(){return!!this.sub_log_view_controller&&(this.sub_log_view_controller.getSubViewFilter=function(e){return e.table_name_object_id={"permission_user":[this.parent_edit_record.id],"permission":[this.parent_edit_record.id],"permission_control":[this.parent_edit_record.id]},e},!0)}getCustomContextMenuModel(){return{exclude:[ContextMenuIconName.mass_edit],include:[{label:$.i18n._("Permission<br>Wizard"),id:ContextMenuIconName.permission_wizard,group:"other",icon:Icons.permission_wizard}]}}setEditViewTabHeight(){super.setEditViewTabHeight(),this.edit_view_ui_dic.permission.setHeight(this.edit_view_tab.height()-325)}onCustomContextClick(e){var i=this;switch(i.current_edit_record.permission=this.buildAPIFormPermissionResult(),"array"!==$.type(i.current_edit_record.permission)&&(i.current_edit_record.permission=[]),e){case ContextMenuIconName.permission_wizard:IndexViewController.openWizard("PermissionWizard",null,(function(e,t){if(e)switch(t){case"allow":var r=i.convertPermissionData(e);i.current_edit_record.permission=i.current_edit_record.permission.concat(r),i.removeDuplicatePermission(),i.edit_view_ui_dic.permission.setValue(i.current_edit_record.permission),i.edit_view_ui_dic.permission.setSelectGridHighlight(r);break;case"highlight":r=i.convertPermissionData(e),i.edit_view_ui_dic.permission.setSelectGridHighlight(r),i.edit_view_ui_dic.permission.setUnSelectGridHighlight(r);break;case"deny":r=i.convertPermissionData(e),i.edit_view_ui_dic.permission.setSelectGridHighlight(r),i.edit_view_ui_dic.permission.moveItems(!1,r),i.edit_view_ui_dic.permission.setUnSelectGridHighlight(r),i.current_edit_record.permission=i.edit_view_ui_dic.permission.getValue()}}))}}removeDuplicatePermission(){var e=[];$.each(this.current_edit_record.permission,(function(i,t){-1===$.inArray(t,e)&&e.push(t)})),this.current_edit_record.permission=e}buildPermissionArray(e,i){var t=[],r=[];for(var s in e){var o=[];for(var n in e[s]){var a=e[s],_={};_.value=s+"->"+n,r.push(_.value),_.sortKey=s,_.label=a[n],_.id=_.value,o.push(_)}t=t.concat(o)}return t.sort((function(e,i){return Global.compare(e,i,"label")})),i?r:t}initOptions(){var e=this;this.initDropDownOption("level","level"),this.api.getPermissionOptions({onResult:function(i){i=i.getResult(),i=e.buildPermissionArray(i),e.permission_array=i}})}setCurrentEditRecordData(){for(var e in this.current_edit_record){var i=this.edit_view_ui_dic[e];if(Global.isSet(i))switch(e){case"permission":this.current_edit_record.permission&&(this.current_edit_record.permission=this.convertPermissionData(this.current_edit_record.permission),i.setValue(this.current_edit_record.permission));break;default:i.setValue(this.current_edit_record[e])}}this.collectUIDataToCurrentEditRecord(),this.edit_view_ui_dic.permission.setGridColumnsWidths(),this.setEditViewDataDone()}convertPermissionData(e){var i=[];for(var t in e){var r=[];for(var s in e[t])!0===e[t][s]&&r.push(t+"->"+s);i=i.concat(r)}return i}buildSelectItems(){var e=[],i=this.permission_array.length;for(var t in this.current_edit_record.permission)for(var r=this.current_edit_record.permission[t],s=0;s<i;s++){var o=this.permission_array[s];if(r===o.value){e.push(o);break}}return e}buildEditViewUI(){super.buildEditViewUI();this.edit_view.children().eq(0).css("min-width",1170);var e={"tab_permission_group":{"label":$.i18n._("Permission Group")},"tab_audit":!0};this.setTabModel(e),this.navigation.AComboBox({api_class:TTAPI.APIPermissionControl,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_permission_control",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i=this.edit_view_tab.find("#tab_permission_group").find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(i);var t=Global.loadWidgetByName(FormItemType.TEXT_INPUT);t.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn($.i18n._("Name"),t,i,""),t.parent().width("45%"),(t=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"description",width:"100%"}),this.addEditFieldToColumn($.i18n._("Description"),t,i),t.parent().width("45%"),(t=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"level"}),t.setSourceData(this.level_array),this.addEditFieldToColumn($.i18n._("Level"),t,i),(t=Global.loadWidgetByName(FormItemType.AWESOME_BOX)).AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!0,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"user"}),this.addEditFieldToColumn($.i18n._("Employees"),t,i,""),t=Global.loadWidgetByName(FormItemType.AWESOME_DROPDOWN);var r=ALayoutCache.getDefaultColumn("global_option_column");r=Global.convertColumnsTojGridFormat(r,"global_option_column"),t.ADropDown({field:"permission",display_show_all:!1,id:"permission_dropdown",key:"value",allow_drag_to_order:!1,display_close_btn:!1,auto_sort:!0,display_column_settings:!1,default_height:this.edit_view_tab.height()-325}),t.addClass("splayed-adropdown"),this.addEditFieldToColumn($.i18n._("Permissions"),t,i,"",null,!0,!0),t.setColumns(r),t.setUnselectedGridData(this.permission_array)}uniformVariable(e){return e.permission=this.buildAPIFormPermissionResult(),e}buildAPIFormPermissionResult(){for(var e=this.edit_view_ui_dic.permission.getValue(),i={},t="",r=0;r<e.length;r++){var s=e[r].value;i[t=s.split("->")[0]]||(i[t]={}),i[t][s.split("->")[1]]=!0}return i}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:$.i18n._("Name"),in_column:1,field:"name",basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("Description"),in_column:1,field:"description",basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:$.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:$.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}}}}]);
//# sourceMappingURL=company-permission_control-PermissionControlViewController.bundle.js.map?v=5212c3e28a89cd648ce5