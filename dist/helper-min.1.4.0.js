!function(){var e=L.Control.extend({options:{position:"topleft"},onAdd:function(e){return document.querySelector("#lfh-control")}}),t={mode:"lfh-view",confirm:data_helper.confirm,add_description:data_helper.add_description,tiles:data_helper.tiles,map:null,center:[48.67777858405578,2.166026472914382],zoom:2,current_marker:null,default_icon:null,move_marker:null,markers:new Array,tile:null,record:null,init_map:function(){t.map=L.map("map").setView(t.center,t.zoom),t.set_tile(),t.map.addControl(new e);var r=document.querySelectorAll(".marker-control");[].forEach.call(r,function(e){L.DomEvent.addListener(e,"click",function(r){t.set_mode(e),r.stopPropagation()})}),t.default_icon=L.AwesomeMarkers.icon({icon:"circle",prefix:"lfhicon",markerColor:"red"}),t.move_marker=L.marker(t.center).addTo(t.map),t.move_marker.setOpacity(0),t.map.on("mousemove",function(e){switch(t.mode){case"lfh-add-marker":t.move_marker.setLatLng(e.latlng)}}),t.map.on("click",function(e){"lfh-add-marker"==t.mode&&t.add_marker(e)})},set_tile:function(){var e=document.querySelector('select[name="lfh-form-map-tile"]').value,r=t.tiles[e];null!=t.tile&&t.map.removeLayer(t.tile),t.tile="mapquest"!=e?L.tileLayer(r.url,{attribution:r.attribution}):MQ.mapLayer(),t.tile.addTo(t.map)},add_marker:function(e){return t.current_marker=L.marker(e.latlng,{icon:t.default_icon,draggable:!0,index:t.markers.length,title:"",popup:"",description:!1,visibility:"always"}).addTo(t.map),t.markers.push(t.current_marker),t.current_marker.on("click",function(e){switch(t.mode){case"lfh-edit-marker":t.current_marker=this,t.init_window();break;case"lfh-delete-marker":t.current_marker=null,t.delete_marker(this)}}),t.set_mode(document.querySelector("#lfh-edit-marker")),t.current_marker},delete_marker:function(e){if(confirm(t.confirm)){for(var r=e.options.index,n=r;n<t.markers.length-1;n++)t.markers[n]=t.markers[n+1],t.markers[n].options.index=n;t.map.removeLayer(e),t.markers.pop()}},set_mode:function(e){if("lfh-view"!=t.mode){var r=document.querySelector(".marker-control.active");r.className=r.className.replace(" active","")}var n=document.querySelectorAll(".lfh-form-edit");[].forEach.call(n,function(e){e.style.display="none"}),e.id==t.mode?t.mode="lfh-view":(t.mode=e.id,e.className=e.className+" active",t.init_window()),"lfh-add-marker"==t.mode?t.move_marker.setOpacity(1):t.move_marker.setOpacity(0)},get_position_info:function(){t.record={center:t.map.getCenter(),zoom:t.map.getZoom()},t.write_position()},write_position:function(){var e="<b> lat</b> = "+t.record.center.lat+"<br />";e+="<b> lng</b> = "+t.record.center.lng+"<br />",e+="<b> zoom</b> = "+t.record.zoom,document.querySelector("#map-position").innerHTML=e},init_window:function(){switch(t.mode){case"lfh-edit-map":document.querySelector("#window-edit-map").style.display="block";break;case"lfh-edit-marker":if(null==t.current_marker)return;var e=t.current_marker.options,r=e.icon.options.markerColor,n=e.icon.options.icon;document.querySelector('#window-edit-marker input[name="title"]').value=e.title.stripslashes(),document.querySelector('#window-edit-marker textarea[name="popup"]').value=e.popup.stripslashes(),document.querySelector('#window-edit-marker input[name="description"]').checked=e.description,document.querySelector('#window-edit-marker select[name="visibility"]').selectedIndex="always"==e.visibility?0:1;var o=document.createEvent("MouseEvents");o.initEvent("click",!0,!0),document.querySelector("#color-marker div.awesome-marker-icon-"+r).dispatchEvent(o),document.querySelector("#icon-marker div.lfhicon-"+n).dispatchEvent(o),document.querySelector("#window-edit-marker").style.display="block"}},close_window:function(e){switch(e){case"window-edit-marker":t.close_color(),t.close_icon(),document.querySelector("#"+e).style.display="none";break;case"window-edit-map":var r=document.createEvent("MouseEvents");r.initEvent("click",!0,!0),document.querySelector("#lfh-edit-map").dispatchEvent(r)}},close_color:function(){var e=document.querySelector("#window-edit-marker .to-extend label "),t=e.parentNode.querySelector("div");"-"==e.textContent&&(e.textContent="+",t.style.display="none")},close_icon:function(){var e=document.querySelector("#window-edit-marker .to-extend:last-child label"),t=e.parentNode.querySelector("div");"-"==e.textContent&&(e.textContent="+",t.style.display="none")},write_checked:function(e){document.querySelector('input[name="'+t.map_var[e]+'"]').checked},shortcode_map:function(){var e="";return e+="[lfh-map ",document.querySelector('input[name="lfh-form-map-autocenter"]').checked||(e+=" lat="+t.record.center.lat,e+=" lng="+t.record.center.lng,e+=" zoom="+t.record.zoom),""!=document.querySelector('input[name="lfh-form-map-class"]').value&&(e+=' class="'+document.querySelector('input[name="lfh-form-map-class"]').value.replaceAll("-","\\-")+'"'),["autocenter","fullscreen","reset","list","mousewheel"].forEach(function(t){e+=" "+t+"="+document.querySelector('input[name="lfh-form-map-'+t+'"]').checked}),["width","height"].forEach(function(t){e+=" "+t+"="+document.querySelector('input[name="lfh-form-map-'+t+'"]').value}),e+=" tile="+document.querySelector('select[name="lfh-form-map-tile"]').value,e+=" ] <br /> "},shortcode:function(e){var r="";switch(e){case"map":r+=t.shortcode_map();break;case"markers":for(var n=0;n<t.markers.length;n++){var o=t.markers[n].getLatLng(),c=t.markers[n].options,a=c.icon.options.markerColor,i=c.icon.options.icon;r+="[lfh-marker lat="+o.lat+" lng="+o.lng,r+=" color="+a+" icon="+i,r+=' title="'+c.title+'"',r+=' popup="'+c.popup+'"',r+=" visibility="+c.visibility,r+=" ]",c.description&&(r+="<br />"+t.add_description+"<br />"),r+="[/lfh-marker]<br />  "}}return r}};L.DomEvent.addListener(document.querySelector("form"),"submit",function(e){e.preventDefault()});var r={selected:null,x_pos:0,y_pos:0,x_elem:0,y_elem:0,init:function(e){r.selected=e,r.x_elem=r.x_pos-r.selected.offsetLeft,r.y_elem=r.y_pos-r.selected.offsetTop},move:function(e){if(r.x_pos=document.all?window.event.clientX:e.pageX,r.y_pos=document.all?window.event.clientY:e.pageY,null!==r.selected){var t=Math.min(Math.max(-200,r.x_pos-r.x_elem),window.innerWidth-100);r.selected.style.left=t+"px";var n=Math.min(Math.max(0,r.y_pos-r.y_elem),window.innerHeight-40);r.selected.style.top=n+"px"}},destroy:function(){r.selected=null}};document.onmousemove=r.move,document.onmouseup=r.destroy;var n=document.querySelectorAll(".lfh-form-edit .header");[].forEach.call(n,function(e){L.DomEvent.addListener(e,"mousedown",function(t){r.init(e.parentNode)})});var o=document.querySelectorAll(".lfhicon-close");[].forEach.call(o,function(e){L.DomEvent.addListener(e,"click",function(r){var n=e.parentNode.parentNode.id;t.close_window(n)})});var o=document.querySelectorAll(".to-extend label");[].forEach.call(o,function(e){L.DomEvent.addListener(e,"click",function(e){var t=e.target,r=t.parentNode.querySelector("div");"+"==t.textContent?(t.textContent="-",r.style.display="inline-block"):(t.textContent="+",r.style.display="none")})});var c=document.querySelector('#window-edit-map input[name="lfh-form-map-autocenter"]');L.DomEvent.addListener(c,"click",function(e){document.querySelector("#center-map").style.display=e.target.checked?"none":"block",t.get_position_info()});var c=document.querySelector('input[name="save-center"]');L.DomEvent.addListener(c,"click",function(e){t.get_position_info()});var c=document.querySelector('#window-edit-map select[name="lfh-form-map-tile"]');L.DomEvent.addListener(c,"change",function(e){t.set_tile()});var a=document.createEvent("MouseEvents");a.initEvent("click",!0,!0),document.querySelector('#window-edit-map input[type="reset"]').dispatchEvent(a),document.querySelector("#window-edit-map form").onreset=function(){document.querySelector("#center-map").style.display="none",setTimeout(function(){null!=t.tiles&&t.set_tile()},500)};var c=document.querySelector('#window-edit-marker input[name="title"]');L.DomEvent.addListener(c,"change",function(e){t.current_marker.options.title=this.value.addslashes()});var c=document.querySelector('#window-edit-marker textarea[name="popup"]');L.DomEvent.addListener(c,"change",function(e){var r=this.value;t.current_marker.options.popup=this.value.addslashes(),t.current_marker.bindPopup(r).openPopup()});var c=document.querySelector("#window-edit-marker input[name='description']");L.DomEvent.addListener(c,"click",function(e){t.current_marker.options.description=this.checked});var c=document.querySelector("#window-edit-marker select");L.DomEvent.addListener(c,"change",function(e){t.current_marker.options.visibility=this.value});var o=document.querySelectorAll("#selected-icon , #selected-color");[].forEach.call(o,function(e){L.DomEvent.addListener(e,"focus",function(r){for(var n=e.parentNode.nextSibling;1!=n.nodeType;)n=n.nextSibling;n.querySelector("label").textContent="-",n.querySelector("div").style.display="inline-block","selected-icon"==e.id&&t.close_color()})});var o=document.querySelectorAll("#color-marker div.awesome-marker");[].forEach.call(o,function(e){L.DomEvent.addListener(e,"click",function(e){var r=this,n=document.querySelector("#color-marker div.selected"),o=n.className.replace(" selected","");n.className=o,o=r.className,r.className=o+" selected";var c=r.dataset.value,n=document.querySelector("#selected-color");n.className=o,n.dataset.value=c;var a=document.querySelector("#selected-icon").dataset.value;t.current_marker.setIcon(L.AwesomeMarkers.icon({icon:a,prefix:"lfhicon",markerColor:c}))})});var o=document.querySelectorAll("#icon-marker div.lfhicon");[].forEach.call(o,function(e){L.DomEvent.addListener(e,"click",function(e){var r=this,n=document.querySelector("#icon-marker div.selected"),o=n.className.replace(" selected","");n.className=o,o=r.className,r.className=o+" selected";var c=r.dataset.value,n=document.querySelector("#selected-icon");n.className=o,n.dataset.value=c;var a=document.querySelector("#selected-color").dataset.value;t.current_marker.setIcon(L.AwesomeMarkers.icon({icon:c,prefix:"lfhicon",markerColor:a}))})});var c=document.querySelector('#banner input[name="lfh-cancel"]');L.DomEvent.addListener(c,"click",function(e){void 0!==window.parent&&void 0!==window.parent.tinymce&&window.parent.tinymce.activeEditor.windowManager.close()});var c=document.querySelector('#banner input[name="lfh-insert"]');L.DomEvent.addListener(c,"click",function(e){document.querySelector("#fade").className=""});var c=document.querySelector('#fade input[name="lfh-modal-cancel"]');L.DomEvent.addListener(c,"click",function(e){document.querySelector("#fade").className="hidden"});var c=document.querySelector('#fade input[name="lfh-modal-insert"]');L.DomEvent.addListener(c,"click",function(e){shortcode="",document.querySelector('#fade input[name="lfh-insert-map"]').checked&&(shortcode+=t.shortcode("map")),document.querySelector('#fade input[name="lfh-insert-markers"]').checked&&(shortcode+=t.shortcode("markers")),void 0!==window.parent&&void 0!==window.parent.tinymce&&(window.parent.tinymce.activeEditor.selection.setContent(shortcode),window.parent.tinymce.activeEditor.windowManager.close())}),window.onload=function(){if(window.dialogArguments)var arguments=window.dialogArguments;else var arguments=window.opener;null!=arguments&&void 0!==arguments.lat&&(t.center=[arguments.lat,arguments.lng],t.zoom=arguments.zoom),null==t.map&&t.init_map()},null==t.map&&t.init_map()}(),String.prototype.addslashes=function(){return this.replace(/[\\"']/g,"\\$&").replace(/\u0000/g,"\\0")},String.prototype.stripslashes=function(){return this.replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}})},String.prototype.replaceAll=function(e,t){return this.replace(new RegExp(e,"g"),t)};