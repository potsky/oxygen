/*! jquery-tagsinput */
(function(d){var b=new Array();var c=new Array();var a=true;d.fn.doAutosize=function(j){var g=d(this).data("minwidth"),q=d(this).data("maxwidth"),l="",p=d(this),k=d("#"+d(this).data("tester_id"));if(l===(l=p.val())){return}var f=l.replace(/&/g,"&amp;").replace(/\s/g," ").replace(/</g,"&lt;").replace(/>/g,"&gt;");k.html(f);var n=k.width(),m=(n+j.comfortZone)>=g?n+j.comfortZone:g,h=p.width(),e=(m<h&&m>=g)||(m>g&&m<q);if(e){p.width(m)}};d.fn.resetAutosize=function(g){var j=d(this).data("minwidth")||g.minInputWidth||d(this).width(),k=d(this).data("maxwidth")||g.maxInputWidth||(d(this).closest(".tagsinput").width()-g.inputPadding),l="",f=d(this),h=d("<tester/>").css({position:"absolute",top:-9999,left:-9999,width:"auto",fontSize:f.css("fontSize"),fontFamily:f.css("fontFamily"),fontWeight:f.css("fontWeight"),letterSpacing:f.css("letterSpacing"),whiteSpace:"nowrap"}),e=d(this).attr("id")+"_autosize_tester";if(!d("#"+e).length>0){h.attr("id",e);h.appendTo("body")}f.data("minwidth",j);f.data("maxwidth",k);f.data("tester_id",e);f.css("width",j)};d.fn.addTag=function(f,e){e=jQuery.extend({focus:false,callback:true},e);this.each(function(){var l=d(this).attr("id");var g=d(this).val().split(b[l]);if(g[0]==""){g=new Array()}f=jQuery.trim(f);if(e.unique){var h=d(this).tagExist(f);if(h==true){d("#"+l+"_tag").addClass("not_valid")}}else{var h=false}if(f!=""&&h!=true){d("<span>").addClass("tag").append(d("<span>").text(f).append("&nbsp;&nbsp;"),a?d("<a>",{href:"javascript:",title:"",text:"×"}).click(function(){return d("#"+l).removeTag(escape(f))}):d("")).insertBefore("#"+l+"_addTag");g.push(f);d("#"+l+"_tag").val("");if(e.focus){d("#"+l+"_tag").focus()}else{d("#"+l+"_tag").blur()}d.fn.tagsInput.updateTagsField(this,g);if(e.callback&&c[l]&&c[l]["onAddTag"]){var k=c[l]["onAddTag"];k.call(this,f)}if(c[l]&&c[l]["onChange"]){var j=g.length;var k=c[l]["onChange"];k.call(this,d(this),g[j-1])}}});return false};d.fn.removeTag=function(e){e=unescape(e);this.each(function(){var j=d(this).attr("id");var g=d(this).val().split(b[j]);d("#"+j+"_tagsinput .tag").remove();str="";for(i=0;i<g.length;i++){if(g[i]!=e){str=str+b[j]+g[i]}}d.fn.tagsInput.importTags(this,str);if(c[j]&&c[j]["onRemoveTag"]){var h=c[j]["onRemoveTag"];h.call(this,e)}});return false};d.fn.tagExist=function(f){var g=d(this).attr("id");var e=d(this).val().split(b[g]);return(jQuery.inArray(f,e)>=0)};d.fn.importTags=function(e){id=d(this).attr("id");d("#"+id+"_tagsinput .tag").remove();d.fn.tagsInput.importTags(this,e)};d.fn.tagsInput=function(e){var f=jQuery.extend({interactive:true,defaultText:"add a tag",minChars:0,width:"300px",height:"100px",autocomplete:{selectFirst:false},hide:true,delimiter:",",unique:true,removeWithBackspace:true,placeholderColor:"#666666",autosize:true,comfortZone:20,inputPadding:6*2,cssClass:""},e);this.each(function(){if(f.hide){d(this).hide()}var j=d(this).attr("id");if(!j||b[d(this).attr("id")]){j=d(this).attr("id","tags"+new Date().getTime()).attr("id")}var h=jQuery.extend({pid:j,real_input:"#"+j,holder:"#"+j+"_tagsinput",input_wrapper:"#"+j+"_addTag",fake_input:"#"+j+"_tag"},f);b[j]=h.delimiter;a=h.interactive;if(f.onAddTag||f.onRemoveTag||f.onChange){c[j]=new Array();c[j]["onAddTag"]=f.onAddTag;c[j]["onRemoveTag"]=f.onRemoveTag;c[j]["onChange"]=f.onChange}var g='<div id="'+j+'_tagsinput" class="tagsinput '+f.cssClass+'"><div id="'+j+'_addTag">';if(f.interactive){g=g+'<input id="'+j+'_tag" value="" data-default="'+f.defaultText+'" class="'+f.cssClass+'" />'}g=g+'</div><div class="tags_clear"></div></div>';d(g).insertAfter(this);d(h.holder).css("width",f.width);d(h.holder).css("min-height",f.height);if(d(h.real_input).val()!=""){d.fn.tagsInput.importTags(d(h.real_input),d(h.real_input).val())}if(f.interactive){d(h.fake_input).val(d(h.fake_input).attr("data-default"));d(h.fake_input).css("color",f.placeholderColor);d(h.fake_input).resetAutosize(f);d(h.holder).bind("click",h,function(k){d(k.data.fake_input).focus()});d(h.fake_input).bind("focus",h,function(k){if(d(k.data.fake_input).val()==d(k.data.fake_input).attr("data-default")){d(k.data.fake_input).val("")}d(k.data.fake_input).css("color","#000000")});if(f.autocomplete_url!=undefined){autocomplete_options={source:f.autocomplete_url};for(attrname in f.autocomplete){autocomplete_options[attrname]=f.autocomplete[attrname]}if(jQuery.Autocompleter!==undefined){d(h.fake_input).autocomplete(f.autocomplete_url,f.autocomplete);d(h.fake_input).bind("result",h,function(k,m,l){if(m){d("#"+j).addTag(m[0]+"",{focus:true,unique:(f.unique)})}})}else{if(jQuery.ui.autocomplete!==undefined){d(h.fake_input).autocomplete(autocomplete_options);d(h.fake_input).bind("autocompleteselect",h,function(k,l){d(k.data.real_input).addTag(l.item.value,{focus:true,unique:(f.unique)});return false})}}}else{d(h.fake_input).bind("blur",h,function(k){var l=d(this).attr("data-default");if(d(k.data.fake_input).val()!=""&&d(k.data.fake_input).val()!=l){if((k.data.minChars<=d(k.data.fake_input).val().length)&&(!k.data.maxChars||(k.data.maxChars>=d(k.data.fake_input).val().length))){d(k.data.real_input).addTag(d(k.data.fake_input).val(),{focus:true,unique:(f.unique)})}}else{d(k.data.fake_input).val(d(k.data.fake_input).attr("data-default"));d(k.data.fake_input).css("color",f.placeholderColor)}return false})}d(h.fake_input).bind("keypress",h,function(k){if(k.which==k.data.delimiter.charCodeAt(0)||k.which==13){k.preventDefault();if((k.data.minChars<=d(k.data.fake_input).val().length)&&(!k.data.maxChars||(k.data.maxChars>=d(k.data.fake_input).val().length))){d(k.data.real_input).addTag(d(k.data.fake_input).val(),{focus:true,unique:(f.unique)})}d(k.data.fake_input).resetAutosize(f);return false}else{if(k.data.autosize){d(k.data.fake_input).doAutosize(f)}}});h.removeWithBackspace&&d(h.fake_input).bind("keydown",function(l){if(l.keyCode==8&&d(this).val()==""){l.preventDefault();var k=d(this).closest(".tagsinput").find(".tag:last").text();var m=d(this).attr("id").replace(/_tag$/,"");k=k.replace(/[\s]+x$/,"");d("#"+m).removeTag(escape(k));d(this).trigger("focus")}});d(h.fake_input).blur();if(h.unique){d(h.fake_input).keydown(function(k){if(k.keyCode==8||String.fromCharCode(k.which).match(/\w+|[áéíóúÁÉÍÓÚñÑ,/]+/)){d(this).removeClass("not_valid")}})}}});return this};d.fn.tagsInput.updateTagsField=function(f,e){var g=d(f).attr("id");d(f).val(e.join(b[g]))};d.fn.tagsInput.importTags=function(h,j){d(h).val("");var k=d(h).attr("id");var e=j.split(b[k]);for(i=0;i<e.length;i++){d(h).addTag(e[i],{focus:false,callback:false})}if(c[k]&&c[k]["onChange"]){var g=c[k]["onChange"];g.call(h,h,e[i])}}})(jQuery);