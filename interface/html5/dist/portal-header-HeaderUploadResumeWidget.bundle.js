(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["portal-header-HeaderUploadResumeWidget"],{3295:(e,a,t)=>{"use strict";t.r(a),t.d(a,{"HeaderUploadResumeWidget":()=>l});var r=t(6739),o=t(7526),s=t(4936),i=t(12);class l extends r.TTBackboneView{constructor(e={}){_.defaults(e,{}),super(e)}initialize(e){super.initialize(e);var a=Global.loadWidget("views/portal/header/HeaderUploadResumeWidget.html");this.setElement(_.template(a)()),this.document_id=null,this.profileView=null,e.profileView&&(this.profileView=e.profileView)}render(){var e=this;this.uploader||(this.uploader=this.$(".register-resume").TImageBrowser({field:"file",name:"filedata",accept_filter:"*",changeHandler:function(a){var t=$(this).find(".browser")[0].files[0].name;e.$("#fileName").val(t)}})),IndexViewController.instance.router.showFormModal(this.$el,{title:$.i18n._("Upload Resume"),actions:[{label:"Close",isClose:!0},{label:$.i18n._("Upload Resume"),callBack:function(a){e.uploadResume()}}]})}uploadResume(){if(!this.uploader)return!1;var e=this.uploader,a=(e.attr("file_name"),e.getValue());if(a){this.document_api=o.y.APIDocumentPortal;var t="Resume - "+LocalCacheData.getPortalLoginUser().first_name+" "+LocalCacheData.getPortalLoginUser().last_name,r=this.document_api.addAttachment(t,"",{async:!1}).getResult();this.document_id=r.document_id,this.uploadFile(a,r.document_revision_id)}}uploadFile(e,a){var t=this,r=i.n.getAPIURL("Class="+this.document_api.className+"&Method=uploadAttachment&v=2");LocalCacheData.getAllURLArgs()&&LocalCacheData.getAllURLArgs().hasOwnProperty("company_id")&&(r=r+"&company_id="+LocalCacheData.getAllURLArgs().company_id),r=r+"&object_id="+a;var o=s.d.generateUUID();ProgressBar.showProgressBar(o),ProgressBar.changeProgressBarMessage($.i18n._("File Uploading")+"..."),$.ajax({url:r,headers:{"X-Client-ID":"Browser-TimeTrex","X-CSRF-Token":getCookie("CSRF-Token")},type:"POST",data:e,success:function(e){ProgressBar.removeProgressBar(),t.uploaded(),e.error?t.showUploadedMessage(e.error):(t.showUploadedMessage($.i18n._("Upload Successful!")),t.refreshSubDocument())},cache:!1,contentType:!1,processData:!1})}refreshSubDocument(){this.profileView&&this.profileView.addDocumentRow(this.document_id)}uploaded(){this.uploader=null,IndexViewController.instance.router.hideFormModal()}showUploadedMessage(e){IndexViewController.instance.router.showTipModal(e)}}}}]);
//# sourceMappingURL=portal-header-HeaderUploadResumeWidget.bundle.js.map?v=d1d7502538dd90d3c6b0