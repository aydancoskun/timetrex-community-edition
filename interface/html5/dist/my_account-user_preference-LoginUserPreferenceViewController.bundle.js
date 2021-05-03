(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["my_account-user_preference-LoginUserPreferenceViewController"],{30:(e,t,a)=>{"use strict";a.r(t),a.d(t,{"LoginUserPreferenceViewController":()=>i});class i extends BaseViewController{constructor(e={}){_.defaults(e,{language_array:null,date_format_array:null,time_format_array:null,time_unit_format_array:null,distance_format_array:null,time_zone_array:null,start_week_day_array:null,schedule_icalendar_type_array:null,saved_schedules:null,selected_schedule_uri_fragment:null,date_api:null,currentUser_api:null,user_preference_api:null}),super(e)}init(e){this.permission_id="user_preference",this.viewId="LoginUserPreference",this.script_name="LoginUserPreferenceView",this.table_name_key="user_preference",this.context_menu_name=$.i18n._("Preferences"),this.api=TTAPI.APIUserPreference,this.date_api=TTAPI.APITTDate,this.currentUser_api=TTAPI.APIAuthentication,this.user_preference_api=TTAPI.APIUserPreference,this.generic_user_data_api=TTAPI.APIUserGenericData,this.saved_schedules=[],this.selected_schedule_uri_fragment="",this.render(),this.initData()}render(){super.render()}initOptions(e){var t=[{option_name:"language",field_name:null,api:this.api},{option_name:"date_format",field_name:null,api:this.api},{option_name:"time_format",field_name:null,api:this.api},{option_name:"time_unit_format",field_name:null,api:this.api},{option_name:"distance_format",field_name:null,api:this.api},{option_name:"time_zone",field_name:null,api:this.api},{option_name:"start_week_day",field_name:null,api:this.api},{option_name:"schedule_icalendar_type",field_name:null,api:this.api},{option_name:"default_login_screen",field_name:null,api:this.api}];this.initDropDownOptions(t,(function(t){e&&e(t)}))}detectTimeZone(){if(Intl){var e=Intl.DateTimeFormat().resolvedOptions().timeZone;if(e&&e.length>0){Debug.Text("Detected TimeZone: "+e,"UserPreferenceViewController.js","UserPreferenceViewController","detectTimeZone",10),this.current_edit_record.time_zone=e;var t=this.edit_view_ui_dic.time_zone;t.setValue(this.current_edit_record.time_zone),t.trigger("formItemChange",[t]),TAlertManager.showAlert($.i18n._("Time Zone detected as")+" "+e)}else TAlertManager.showAlert($.i18n._("Unable to determine time zone"))}}getCustomContextMenuModel(){return{exclude:["default"],include:[ContextMenuIconName.save,ContextMenuIconName.cancel]}}getUserPreferenceData(e){var t=this,a={filter_data:{}};a.filter_data.user_id=LocalCacheData.loginUser.id,t.api["get"+t.api.key_name](a,{onResult:function(a){var i=a.getResult();Global.isArray(i)&&Global.isSet(i[0])?e(i[0]):t.api["get"+t.api.key_name+"DefaultData"]({onResult:function(t){var a=t.getResult();e(a)}})}})}getSavedScheduleSearchAndLayout(e){var t={filter_data:{script:"ScheduleView",deleted:!1}};this.generic_user_data_api.getUserGenericData(t,{onResult:function(t){for(var a=t.getResult(),i=a.length-1;i>=0;i--)a[i].name===BaseViewController.default_layout_name&&a.splice(i,1);e(a)}})}setCurrentEditRecordData(){for(var e in this.current_edit_record){var t=this.edit_view_ui_dic[e];if(Global.isSet(t))switch(e){case"full_name":t.setValue(this.current_edit_record.first_name+" "+this.current_edit_record.last_name);break;default:t.setValue(this.current_edit_record[e])}}this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}setEditViewDataDone(){super.setEditViewDataDone(),this.onStatusChange()}openEditView(){var e=this;e.edit_only_mode&&e.initOptions((function(t){e.getSavedScheduleSearchAndLayout((function(t){e.saved_schedules.push({value:0,label:$.i18n._("My Schedule (Default)")});for(var a=0;a<t.length;a++)e.saved_schedules.push({value:t[a].id,label:t[a].name});e.getUserPreferenceData((function(t){e.buildContextMenu(),e.edit_view||e.initEditViewUI("LoginUserPreference","LoginUserPreferenceEditView.html"),t.id||(t.first_name=LocalCacheData.loginUser.first_name,t.last_name=LocalCacheData.loginUser.last_name,t.user_id=LocalCacheData.loginUser.id),e.current_edit_record=t,e.initEditView()}))}))}))}onFormItemChange(e,t){this.setIsChanged(e),this.setMassEditingFieldsWhenFormChange(e);var a=e.getField(),i=e.getValue();this.current_edit_record[a]=i,"schedule_icalendar_type_id"===a?(this.onStatusChange(),this.setCalendarURL()):"schedule_saved_search"===a&&(this.onSavedScheduleChange(e.getValue(),e.getLabel()),this.setCalendarURL()),t||this.validate()}onStatusChange(){0==this.current_edit_record.schedule_icalendar_type_id?(this.detachElement("schedule_saved_search"),this.detachElement("calendar_url"),this.detachElement("schedule_icalendar_alarm1_working"),this.detachElement("schedule_icalendar_alarm2_working"),this.detachElement("schedule_icalendar_alarm1_absence"),this.detachElement("schedule_icalendar_alarm2_absence"),this.detachElement("schedule_icalendar_alarm1_modified"),this.detachElement("schedule_icalendar_alarm2_modified"),this.detachElement("shifts_scheduled_to_work"),this.detachElement("shifts_scheduled_absent"),this.detachElement("modified_shifts")):(this.setCalendarURL(),this.attachElement("schedule_saved_search"),this.attachElement("calendar_url"),this.attachElement("schedule_icalendar_alarm1_working"),this.attachElement("schedule_icalendar_alarm2_working"),this.attachElement("schedule_icalendar_alarm1_absence"),this.attachElement("schedule_icalendar_alarm2_absence"),this.attachElement("schedule_icalendar_alarm1_modified"),this.attachElement("schedule_icalendar_alarm2_modified"),this.attachElement("shifts_scheduled_to_work"),this.attachElement("shifts_scheduled_absent"),this.attachElement("modified_shifts")),this.editFieldResize()}onSavedScheduleChange(e,t){this.selected_schedule_uri_fragment=0===e?"":t}setCalendarURL(e){Global.isSet(e)||(e=this.edit_view_ui_dic.calendar_url),this.api.getScheduleIcalendarURL(this.current_edit_record.user_name,this.current_edit_record.schedule_icalendar_type_id,this.selected_schedule_uri_fragment,{onResult:function(t){var a=t.getResult();e.setValue(ServiceCaller.root_url+a),e.unbind("click"),e.click((function(){window.open(e.text())}))}})}initSubScheduleSynchronizationView(){Global.getProductEdition()>=15?(this.edit_view_tab.find("#tab_schedule_synchronization").find(".first-column").css("display","block"),this.edit_view.find(".permission-defined-div").css("display","none"),this.buildContextMenu(!0),this.setEditMenu()):(this.edit_view_tab.find("#tab_schedule_synchronization").find(".first-column").css("display","none"),this.edit_view.find(".permission-defined-div").css("display","block"),this.edit_view.find(".permission-message").html(Global.getUpgradeMessage()))}onSaveDone(e){return!!e.isValid()&&(Global.setLanguageCookie(this.current_edit_record.language),LocalCacheData.setI18nDic(null),Global.updateUserPreference((function(){window.location.reload(!0)}),$.i18n._("Updating preferences, reloading")+"..."),IndexViewController.setNotificationBar("preference"),!0)}setEditMenuSaveIcon(e,t){}setErrorMenu(){for(var e=this.context_menu_array.length,t=0;t<e;t++){var a=$(this.context_menu_array[t]),i=$(a.find(".ribbon-sub-menu-icon")).attr("id");switch(a.removeClass("disable-image"),i){case ContextMenuIconName.cancel:break;default:a.addClass("disable-image")}}}buildEditViewUI(){var e=this;super.buildEditViewUI();var t={"tab_preferences":{"label":$.i18n._("Preferences")},"tab_schedule_synchronization":{"label":$.i18n._("Schedule Synchronization"),"init_callback":"initSubScheduleSynchronizationView"}};this.setTabModel(t);var a,i,l=this.edit_view_tab.find("#tab_preferences"),d=l.find(".first-column"),n=l.find(".second-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(d),this.edit_view_tabs[0].push(n),(a=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"full_name"}),this.addEditFieldToColumn($.i18n._("Employee"),a,d,""),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"language"}),a.setSourceData(e.language_array),this.addEditFieldToColumn($.i18n._("Language"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"date_format"}),a.setSourceData(e.date_format_array),this.addEditFieldToColumn($.i18n._("Date Format"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"time_format"}),a.setSourceData(e.time_format_array),this.addEditFieldToColumn($.i18n._("Time Format"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"time_unit_format"}),a.setSourceData(e.time_unit_format_array),this.addEditFieldToColumn($.i18n._("Time Units"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"distance_format"}),a.setSourceData(e.distance_format_array),this.addEditFieldToColumn($.i18n._("Distance Units"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"time_zone",set_empty:!0}),a.setSourceData(e.time_zone_array),o=$("<div class='widget-h-box'></div>");var s=$("<input class='t-button' style='margin-left: 5px; height: 25px;' type='button' value='"+$.i18n._("Auto-Detect")+"'></input>");s.click((function(){e.detectTimeZone()})),o.append(a),o.append(s),this.addEditFieldToColumn($.i18n._("Time Zone"),a,d,"",o),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"start_week_day"}),a.setSourceData(e.start_week_day_array),this.addEditFieldToColumn($.i18n._("Calendar Starts On"),a,d),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"items_per_page",width:50}),this.addEditFieldToColumn($.i18n._("Rows per page"),a,d),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"default_login_screen"}),a.setSourceData(e.default_login_screen_array),this.addEditFieldToColumn($.i18n._("Default Screen"),a,d),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_save_timesheet_state"}),this.addEditFieldToColumn($.i18n._("Save TimeSheet State"),a,d),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_auto_context_menu"}),this.addEditFieldToColumn($.i18n._("Automatically Show Context Menu"),a,d),(a=Global.loadWidgetByName(FormItemType.SEPARATED_BOX)).SeparatedBox({label:$.i18n._("Email Notifications")}),this.addEditFieldToColumn(null,a,n),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_email_notification_exception"}),this.addEditFieldToColumn($.i18n._("Exceptions"),a,n),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_email_notification_message"}),this.addEditFieldToColumn($.i18n._("Messages"),a,n),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_email_notification_pay_stub"}),this.addEditFieldToColumn($.i18n._("Pay Stubs"),a,n),(a=Global.loadWidgetByName(FormItemType.CHECKBOX)).TCheckbox({field:"enable_email_notification_home"}),this.addEditFieldToColumn($.i18n._("Send Notifications to Home Email"),a,n,"");var r=this.edit_view_tab.find("#tab_schedule_synchronization").find(".first-column");this.edit_view_tabs[1]=[],this.edit_view_tabs[1].push(r),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"schedule_icalendar_type_id"}),a.setSourceData(e.schedule_icalendar_type_array),this.addEditFieldToColumn($.i18n._("Status"),a,r,""),(a=Global.loadWidgetByName(FormItemType.COMBO_BOX)).TComboBox({field:"schedule_saved_search"}),a.setSourceData(e.saved_schedules),this.addEditFieldToColumn($.i18n._("Schedule Saved Search"),a,r,"",null,!0,!1,"schedule_saved_search"),(a=Global.loadWidgetByName(FormItemType.TEXT)).TText({field:"calendar_url"}),a.addClass("link");var o=$("<div class='widget-h-box'></div>"),_=$(' <img style="margin-left: 5px; cursor: pointer; width: 20px; height: 20px;" src="'+Global.getRealImagePath("css/global/widgets/ribbon/icons/"+Icons.copy)+'">');_.click((function(){e.copyCalendarUrl()})),o.append(a),o.append(_),this.addEditFieldToColumn($.i18n._("Calendar URL"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.SEPARATED_BOX)).SeparatedBox({label:$.i18n._("Shifts Scheduled to Work")}),this.addEditFieldToColumn(null,a,r,"",null,!0,!1,"shifts_scheduled_to_work"),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm1_working",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 1"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm2_working",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 2"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.SEPARATED_BOX)).SeparatedBox({label:$.i18n._("Shifts Scheduled Absent")}),this.addEditFieldToColumn(null,a,r,"",null,!0,!1,"shifts_scheduled_absent"),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm1_absence",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 1"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm2_absence",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 2"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.SEPARATED_BOX)).SeparatedBox({label:$.i18n._("Modified Shifts")}),this.addEditFieldToColumn(null,a,r,"",null,!0,!1,"modified_shifts"),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm1_modified",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 1"),a,r,"",o,!0),(a=Global.loadWidgetByName(FormItemType.TEXT_INPUT)).TTextInput({field:"schedule_icalendar_alarm2_modified",width:90,need_parser_sec:!0}),o=$("<div class='widget-h-box'></div>"),i=$("<span class='widget-right-label'>( "+$.i18n._("before schedule start time")+" )</span>"),o.append(a),o.append(i),this.addEditFieldToColumn($.i18n._("Alarm 2"),a,r,"",o,!0)}copyCalendarUrl(){var e=this.edit_view_ui_dic.calendar_url[0],t=document.createElement("textarea");t.value=e.textContent,document.body.appendChild(t),t.select(),document.execCommand("Copy"),t.remove(),TAlertManager.showAlert($.i18n._("Calendar URL copied to clipboard."))}}}}]);
//# sourceMappingURL=my_account-user_preference-LoginUserPreferenceViewController.bundle.js.map?v=eb708c8ac0f4a35c1d23