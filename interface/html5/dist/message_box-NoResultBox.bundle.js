(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["message_box-NoResultBox"],{4938:()=>{var e;(e=jQuery).fn.NoResultBox=function(s){Global.addCss("global/widgets/message_box/NoResultBox.css");var t,i=e.extend({},e.fn.NoResultBox.defaults,s),n=Global.no_result_message,a="";return this.each((function(){var s=e.meta?e.extend({},i,e(this).data()):i;s.related_view_controller&&(t=s.related_view_controller),s.message&&(n=s.message),a=s.iconLabel?s.iconLabel:e.i18n._("New");var l=e(this).find(".ribbon-button"),o=e(this).find(".add-div"),d=e(this).find(".label"),c=e(this).find(".icon"),b=e(this).find(".message");o.css("display","block"),s.is_new?(c.attr("src",Global.getRealImagePath("css/global/widgets/ribbon/icons/"+Icons.new_add)),d.text(a),l.bind("click",(function(){t.onAddClick()}))):s.is_edit?(c.attr("src",Global.getRealImagePath("css/global/widgets/ribbon/icons/"+Icons.edit)),d.text(e.i18n._("Edit")),l.bind("click",(function(){t.onEditClick()}))):o.css("display","none"),b.text(n)})),this},e.fn.NoResultBox.defaults={}}}]);
//# sourceMappingURL=message_box-NoResultBox.bundle.js.map?v=0c4190c08d2a0f9cd2e7