(self.webpackChunktimetrex=self.webpackChunktimetrex||[]).push([["color-picker-TColorPicker"],{3161:(r,t,o)=>{"use strict";o.r(t);const e=function(r,t){var o={rgb:{r:[0,255],g:[0,255],b:[0,255]},hsv:{h:[0,360],s:[0,100],v:[0,100]},hsl:{h:[0,360],s:[0,100],l:[0,100]},alpha:{alpha:[0,1]},HEX:{HEX:[0,16777215]}},e=r.Math,i=e.round,n={},s={},c={r:.298954,g:.586434,b:.114612},a={r:.2126,g:.7152,b:.0722},l=function(r){this.colors={RND:{}},this.options={color:"rgba(0,0,0,0)",grey:c,luminance:a,valueRanges:o},h(this,r||{})},h=function(r,o){var e,i=r.options;for(var n in u(r),o)o[n]!==t&&(i[n]=o[n]);e=i.customBG,i.customBG="string"==typeof e?d.txt2color(e).rgb:e,s=g(r.colors,i.color,t,!0)},u=function(r){n!==r&&(n=r,s=r.colors)};function g(r,e,i,n,c){if("string"==typeof e)i=(e=d.txt2color(e)).type,s[i]=e[i],c=c!==t?c:e.alpha;else if(e)for(var a in e)r[i][a]=m(e[a]/o[i][a][1],0,1);return c!==t&&(r.alpha=m(+c,0,1)),p(i,n?r:t)}function p(r,t){var c,a,l,h=t||s,u=d,g=n.options,p=o,m=h.RND,x="",k="",C={hsl:"hsv",rgb:r},w=m.rgb;if("alpha"!==r){for(var y in p)if(!p[y][y])for(x in r!==y&&(k=C[y]||"rgb",h[y]=u[k+"2"+y](h[k])),m[y]||(m[y]={}),c=h[y])m[y][x]=i(c[x]*p[y][x][1]);w=m.rgb,h.HEX=u.RGB2HEX(w),h.equivalentGrey=g.grey.r*h.rgb.r+g.grey.g*h.rgb.g+g.grey.b*h.rgb.b,h.webSave=a=f(w,51),h.webSmart=l=f(w,17),h.saveColor=w.r===a.r&&w.g===a.g&&w.b===a.b?"web save":w.r===l.r&&w.g===l.g&&w.b===l.b?"web smart":"",h.hueRGB=d.hue2RGB(h.hsv.h),t&&(h.background=function(r,t,o){var e=n.options.grey,s={};return s.RGB={r:r.r,g:r.g,b:r.b},s.rgb={r:t.r,g:t.g,b:t.b},s.alpha=o,s.equivalentGrey=i(e.r*r.r+e.g*r.g+e.b*r.b),s.rgbaMixBlack=v(t,{r:0,g:0,b:0},o,1),s.rgbaMixWhite=v(t,{r:1,g:1,b:1},o,1),s.rgbaMixBlack.luminance=b(s.rgbaMixBlack,!0),s.rgbaMixWhite.luminance=b(s.rgbaMixWhite,!0),n.options.customBG&&(s.rgbaMixCustom=v(t,n.options.customBG,o,1),s.rgbaMixCustom.luminance=b(s.rgbaMixCustom,!0),n.options.customBG.luminance=b(n.options.customBG,!0)),s}(w,h.rgb,h.alpha))}var _,B,E,R,G,M,F=h.rgb,P=h.alpha,H=h.background;return(_=v(F,{r:0,g:0,b:0},P,1)).luminance=b(_,!0),h.rgbaMixBlack=_,(B=v(F,{r:1,g:1,b:1},P,1)).luminance=b(B,!0),h.rgbaMixWhite=B,g.customBG&&((E=v(F,H.rgbaMixCustom,P,1)).luminance=b(E,!0),E.WCAG2Ratio=function(r,t){var o=1;o=r>=t?(r+.05)/(t+.05):(t+.05)/(r+.05);return i(100*o)/100}(E.luminance,H.rgbaMixCustom.luminance),h.rgbaMixBGMixCustom=E,E.luminanceDelta=e.abs(E.luminance-H.rgbaMixCustom.luminance),E.hueDelta=(R=H.rgbaMixCustom,G=E,M=!0,(e.max(R.r-G.r,G.r-R.r)+e.max(R.g-G.g,G.g-R.g)+e.max(R.b-G.b,G.b-R.b))*(M?255:1)/765)),h.RGBLuminance=b(w),h.HUELuminance=b(h.hueRGB),g.convertCallback&&g.convertCallback(h,r),h}l.prototype.setColor=function(r,o,e){return u(this),r?g(this.colors,r,o,t,e):(e!==t&&(this.colors.alpha=m(e,0,1)),p(o))},l.prototype.setCustomBackground=function(r){return u(this),this.options.customBG="string"==typeof r?d.txt2color(r).rgb:r,g(this.colors,t,"rgb")},l.prototype.saveAsBackground=function(){return u(this),g(this.colors,t,"rgb",!0)},l.prototype.toString=function(r,t){return d.color2text((r||"rgb").toLowerCase(),this.colors,t)};var d={txt2color:function(r){var t={},e=r.replace(/(?:#|\)|%)/g,"").split("("),i=(e[1]||"").split(/,\s*/),n=e[1]?e[0].substr(0,3):"rgb",s="";if(t.type=n,t[n]={},e[1])for(var c=3;c--;)s=n[c]||n.charAt(c),t[n][s]=+i[c]/o[n][s][1];else t.rgb=d.HEX2rgb(e[0]);return t.alpha=i[3]?+i[3]:1,t},color2text:function(r,t,o){var e=!1!==o&&i(100*t.alpha)/100,n="number"==typeof e&&!1!==o&&(o||1!==e),s=t.RND.rgb,c=t.RND.hsl,a="hex"===r&&n,l="hex"===r&&!a,h="rgb"===r||a?s.r+", "+s.g+", "+s.b:l?"#"+t.HEX:c.h+", "+c.s+"%, "+c.l+"%";return l?h:(a?"rgb":r)+(n?"a":"")+"("+h+(n?", "+e:"")+")"},RGB2HEX:function(r){return((r.r<16?"0":"")+r.r.toString(16)+(r.g<16?"0":"")+r.g.toString(16)+(r.b<16?"0":"")+r.b.toString(16)).toUpperCase()},HEX2rgb:function(r){return{r:+("0x"+(r=r.split(""))[0]+r[r[3]?1:0])/255,g:+("0x"+r[r[3]?2:1]+(r[3]||r[1]))/255,b:+("0x"+(r[4]||r[2])+(r[5]||r[2]))/255}},hue2RGB:function(r){var t=6*r,o=~~t%6,e=6===t?0:t-o;return{r:i(255*[1,1-e,0,0,e,1][o]),g:i(255*[e,1,1,1-e,0,0][o]),b:i(255*[0,0,e,1,1,1-e][o])}},rgb2hsv:function(r){var t,o,i=r.r,n=r.g,c=r.b,a=0;return n<c&&(n=c+(c=n,0),a=-1),o=c,i<n&&(i=n+(n=i,0),a=-2/6-a,o=e.min(n,c)),t=i-o,{h:(i?t/i:0)<1e-15?s&&s.hsl&&s.hsl.h||0:t?e.abs(a+(n-c)/(6*t)):0,s:i?t/i:s&&s.hsv&&s.hsv.s||0,v:i}},hsv2rgb:function(r){var t=6*r.h,o=r.s,e=r.v,i=~~t,n=t-i,s=e*(1-o),c=e*(1-n*o),a=e*(1-(1-n)*o),l=i%6;return{r:[e,c,s,s,a,e][l],g:[a,e,e,c,s,s][l],b:[s,s,a,e,e,c][l]}},hsv2hsl:function(r){var t=(2-r.s)*r.v,o=r.s*r.v;return o=r.s?t<1?t?o/t:0:o/(2-t):0,{h:r.h,s:r.v||o?o:s&&s.hsl&&s.hsl.s||0,l:t/2}},rgb2hsl:function(r,t){var o=d.rgb2hsv(r);return d.hsv2hsl(t?o:s.hsv=o)},hsl2rgb:function(r){var t=6*r.h,o=r.s,e=r.l,i=e<.5?e*(1+o):e+o-o*e,n=e+e-i,s=~~t,c=i*(i?(i-n)/i:0)*(t-s),a=n+c,l=i-c,h=s%6;return{r:[i,l,n,n,a,i][h],g:[a,i,i,l,n,n][h],b:[n,n,a,i,i,l][h]}}};function f(r,t){var o={},e=0,i=t/2;for(var n in r)e=r[n]%t,o[n]=r[n]+(e>i?t-e:-e);return o}function b(r,t){for(var o=t?1:255,i=[r.r/o,r.g/o,r.b/o],s=n.options.luminance,c=i.length;c--;)i[c]=i[c]<=.03928?i[c]/12.92:e.pow((i[c]+.055)/1.055,2.4);return s.r*i[0]+s.g*i[1]+s.b*i[2]}function v(r,o,e,i){var n={},s=e!==t?e:1,c=i!==t?i:1,a=s+c*(1-s);for(var l in r)n[l]=(r[l]*s+o[l]*c*(1-s))/a;return n.a=a,n}function m(r,t,o){return r>o?o:r<t?t:r}return l}(window,void 0);!function(r,t,o,e){var i,n,s,c,a,l,h,u,g,p,d,f=t(document),b=t(),v="touchstart.tcp mousedown.tcp pointerdown.tcp",m=!1,x=r.requestAnimationFrame||r.webkitRequestAnimationFrame||function(r){r()},k='<div class="cp-color-picker"><div class="cp-z-slider"><div class="cp-z-cursor"></div></div><div class="cp-xy-slider"><div class="cp-white"></div><div class="cp-xy-cursor"></div></div><div class="cp-alpha"><div class="cp-alpha-cursor"></div></div></div>',C=".cp-color-picker{position:absolute;overflow:hidden;padding:6px 6px 0;background-color:#444;color:#bbb;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:400;cursor:default;border-radius:5px}.cp-color-picker>div{position:relative;overflow:hidden}.cp-xy-slider{float:left;height:128px;width:128px;margin-bottom:6px;background:linear-gradient(to right,#FFF,rgba(255,255,255,0))}.cp-white{height:100%;width:100%;background:linear-gradient(rgba(0,0,0,0),#000)}.cp-xy-cursor{position:absolute;top:0;width:10px;height:10px;margin:-5px;border:1px solid #fff;border-radius:100%;box-sizing:border-box}.cp-z-slider{float:right;margin-left:6px;height:128px;width:20px;background:linear-gradient(red 0,#f0f 17%,#00f 33%,#0ff 50%,#0f0 67%,#ff0 83%,red 100%)}.cp-z-cursor{position:absolute;margin-top:-4px;width:100%;border:4px solid #fff;border-color:transparent #fff;box-sizing:border-box}.cp-alpha{clear:both;width:100%;height:16px;margin:6px 0;background:linear-gradient(to right,#444,rgba(0,0,0,0))}.cp-alpha-cursor{position:absolute;margin-left:-4px;height:100%;border:4px solid #fff;border-color:#fff transparent;box-sizing:border-box}",w=function(r){n=this.color=new o(r),s=n.options,i=this};function y(r){return r.value||r.getAttribute("value")||t(r).css("background-color")||"#FFF"}function _(r){return(r=r.originalEvent&&r.originalEvent.touches?r.originalEvent.touches[0]:r).originalEvent?r.originalEvent:r}function B(r){return t(r.find(s.doRender)[0]||r[0])}function E(o){var f=t(this),b=f.offset(),x=t(r),w=s.gap;o?((c=B(f))._colorMode=c.data("colorMode"),i.$trigger=f,(a||(t("head")[s.cssPrepend?"prepend":"append"]('<style type="text/css" id="tinyColorPickerStyles">'+(s.css||C)+(s.cssAddon||"")+"</style>"),t(k).css({"margin":s.margin}).appendTo("body").show(0,(function(){i.$UI=a=t(this),m=s.GPU&&a.css("perspective")!==e,l=t(".cp-z-slider",this),h=t(".cp-xy-slider",this),u=t(".cp-xy-cursor",this),g=t(".cp-z-cursor",this),p=t(".cp-alpha",this),d=t(".cp-alpha-cursor",this),s.buildCallback.call(i,a),a.prepend("<div>").children().eq(0).css("width",a.children().eq(0).width()),a._width=this.offsetWidth,a._height=this.offsetHeight})).hide())).css(s.positionCallback.call(i,f)||{"left":(a._left=b.left)-((a._left+=a._width-(x.scrollLeft()+x.width()))+w>0?a._left+w:0),"top":(a._top=b.top+f.outerHeight())-((a._top+=a._height-(x.scrollTop()+x.height()))+w>0?a._top+w:0)}).show(s.animationSpeed,(function(){!0!==o&&(p.toggle(!!s.opacity)._width=p.width(),h._width=h.width(),h._height=h.height(),l._height=l.height(),n.setColor(y(c[0])),P(!0))})).off(".tcp").on(v,".cp-xy-slider,.cp-z-slider,.cp-alpha",R)):i.$trigger&&t(a).hide(s.animationSpeed,(function(){P(!1),i.$trigger=null})).off(".tcp")}function R(r){var o=this.className.replace(/cp-(.*?)(?:\s*|$)/,"$1").replace("-","_");(r.button||r.which)>1||(r.preventDefault&&r.preventDefault(),r.returnValue=!1,c._offset=t(this).offset(),(o="xy_slider"===o?G:"z_slider"===o?M:F)(r),P(),f.on("touchend.tcp mouseup.tcp pointerup.tcp",(function(r){f.off(".tcp")})).on("touchmove.tcp mousemove.tcp pointermove.tcp",(function(r){o(r),P()})))}function G(r){var t=_(r),o=t.pageX-c._offset.left,e=t.pageY-c._offset.top;n.setColor({s:o/h._width*100,v:100-e/h._height*100},"hsv")}function M(r){var t=_(r).pageY-c._offset.top;n.setColor({h:360-t/l._height*360},"hsv")}function F(r){var t=(_(r).pageX-c._offset.left)/p._width;n.setColor({},"rgb",t)}function P(r){var t=n.colors,o=t.hueRGB,i=(t.RND.rgb,t.RND.hsl,s.dark),a=s.light,f=n.toString(c._colorMode,s.forceAlpha),b=t.HUELuminance>.22?i:a,v=t.rgbaMixBlack.luminance>.22?i:a,k=(1-t.hsv.h)*l._height,C=t.hsv.s*h._width,w=(1-t.hsv.v)*h._height,y=t.alpha*p._width,_=m?"translate3d":"",B=c[0].value,E=c[0].hasAttribute("value")&&""===B&&r!==e;h._css={backgroundColor:"rgb("+o.r+","+o.g+","+o.b+")"},u._css={transform:_+"("+C+"px, "+w+"px, 0)",left:m?"":C,top:m?"":w,borderColor:t.RGBLuminance>.22?i:a},g._css={transform:_+"(0, "+k+"px, 0)",top:m?"":k,borderColor:"transparent "+b},p._css={backgroundColor:"#"+t.HEX},d._css={transform:_+"("+y+"px, 0, 0)",left:m?"":y,borderColor:v+" transparent"},c._css={backgroundColor:E?"":f,color:E?"":t.rgbaMixBGMixCustom.luminance>.22?i:a},c.text=E?"":B!==f?f:"",r!==e?H(r):x(H)}function H(r){h.css(h._css),u.css(u._css),g.css(g._css),p.css(p._css),d.css(d._css),s.doRender&&c.css(c._css),c.text&&c.val(c.text),s.renderCallback.call(i,c,"boolean"==typeof r?r:e)}w.prototype={render:P,toggle:E},t.fn.colorPicker=function(o){var e=this,c=function(){};return o=t.extend({animationSpeed:150,GPU:!0,doRender:!0,customBG:"#FFF",opacity:!0,renderCallback:c,buildCallback:c,positionCallback:c,body:document.body,scrollResize:!0,gap:4,dark:"#222",light:"#DDD"},o),!i&&o.scrollResize&&t(r).on("resize.tcp scroll.tcp",(function(){i.$trigger&&i.toggle.call(i.$trigger[0],!0)})),b=b.add(this),this.colorPicker=i||new w(o),this.options=o,t(o.body).off(".tcp").on(v,(function(r){-1===b.add(a).add(t(a).find(r.target)).index(r.target)&&E()})),this.on("focusin.tcp click.tcp",(function(r){i.color.options=t.extend(i.color.options,s=e.options),E.call(this,r)})).on("change.tcp",(function(){n.setColor(this.value||"#FFF"),e.colorPicker.render(!0)})).each((function(){var r=y(this),e=r.split("("),i=B(t(this));i.data("colorMode",e[1]?e[0].substr(0,3):"HEX").attr("readonly",s.preventFocus),o.doRender&&i.css({"background-color":r,"color":function(){return n.setColor(r).rgbaMixBGMixCustom.luminance>.22?o.dark:o.light}})}))},t.fn.colorPicker.destroy=function(){t("*").off(".tcp"),i.toggle(!1),b=t()}}(window,$,e,void 0),function(r){r.fn.TColorPicker=function(t){var o,e,i,n=r.extend({},r.fn.TColorPicker.defaults,t),s=this,c="",a=null,l=!0;return this.clearErrorStyle=function(){},this.getEnabled=function(){return l},this.setEnabled=function(r){l=r,!1===r||""===r?s.attr("disabled","true"):s.removeAttr("disabled")},this.setCheckBox=function(r){a&&(a.children().eq(0)[0].checked=r)},this.isChecked=function(){return!(!a||!0!==a.children().eq(0)[0].checked)},this.setMassEditMode=function(t){t?((a=r(' <div class="mass-edit-checkbox-wrapper checkbox-mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox"></input><label for="checkbox-input-1" class="input-helper input-helper--checkbox"></label></div>')).insertBefore(r(this)),a.change((function(){s.trigger("formItemChange",[s])}))):a&&(a.remove(),a=null)},this.setErrorStyle=function(t,o,e){e?r(this).addClass("warning-tip"):r(this).addClass("error-tip"),c=t,o&&this.showErrorTip()},this.showErrorTip=function(t){Global.isSet(t)||(t=2),e||(e=(e=Global.loadWidgetByName(WidgetNamesDic.ERROR_TOOLTIP)).ErrorTipBox()),e.cancelRemove(),r(this).hasClass("warning-tip")?e.show(this,c,t,!0):e.show(this,c,t)},this.hideErrorTip=function(){Global.isSet(e)&&e.remove()},this.clearErrorStyle=function(){r(this).removeClass("error-tip"),r(this).removeClass("warning-tip"),this.hideErrorTip(),c=""},this.getField=function(){return o},this.getValue=function(){return i.val().substring(1)},this.setValue=function(r){r&&i.val("#"+r.toUpperCase())&&i.css("background","#"+r),!r&&i.val("#0F820F")&&i.css("background","#0F820F")},this.each((function(){var t=r.meta?r.extend({},n,r(this).data()):n;o=t.field,r(".cp-color-picker").length>0&&r().colorPicker("destroy"),r(this).unbind("colorPickerClose").bind("colorPickerClose",(function(){s.trigger("formItemChange",[s])})),i=r(this).colorPicker({doRender:"div div",renderCallback:function(t,o){var e=this,i=this.color.colors.RND,n={r:i.rgb.r,g:i.rgb.g,b:i.rgb.b,h:i.hsv.h,s:i.hsv.s,v:i.hsv.v,HEX:this.color.colors.HEX};t.each((function(){this.value="#"+n[this.className.substr(3)]})),t.unbind("focusout").bind("focusout",(function(t){e.$UI.toggle(!1),a&&s.setCheckBox(!0),r(this).trigger("colorPickerClose")}))}})})),this},r.fn.TColorPicker.defaults={}}(jQuery)}}]);
//# sourceMappingURL=color-picker-TColorPicker.bundle.js.map?v=25a224375b933865d9a1