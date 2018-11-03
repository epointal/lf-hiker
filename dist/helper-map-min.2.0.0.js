!function(e){var t=L.Control.extend({options:{position:"topleft"},onAdd:function(e){return document.querySelector("#lfh-control")}});console.log(e);var r={mode:"lfh-view",confirm:data_helper.confirm,add_description:data_helper.add_description,tiles:data_helper.tiles,map:null,center:[48.67777858405578,2.166026472914382],zoom:2,current_marker:null,default_icon:null,move_marker:null,markers:new Array,gpx:new Array,tile:null,record:null,shortcode:"[lfh-map]",init_map:function(){r.map=L.map("map").setView(r.center,r.zoom),r.set_tile(),r.map.addControl(new t);var e=document.querySelectorAll(".marker-control");[].forEach.call(e,function(e){L.DomEvent.addListener(e,"click",function(t){r.set_mode(e),t.stopPropagation()})}),r.default_icon=L.AwesomeMarkers.icon({icon:"circle",prefix:"lfhicon",markerColor:"red"}),r.move_marker=L.marker(r.center).addTo(r.map),r.move_marker.setOpacity(0),r.map.on("mousemove",function(e){switch(r.mode){case"lfh-add-marker":r.move_marker.setLatLng(e.latlng)}}),r.map.on("click",function(e){"lfh-add-marker"==r.mode&&r.add_marker(e)})},set_tile:function(){var e=document.querySelector('select[name="lfh-form-map-tile"]').value,t=r.tiles[e];null!=r.tile&&r.map.removeLayer(r.tile),r.tile="mapquest"!=e?L.tileLayer(t.url,{attribution:t.attribution}):MQ.mapLayer(),r.tile.addTo(r.map)},add_marker:function(e){return r.current_marker=L.marker(e.latlng,{icon:r.default_icon,draggable:!0,index:r.markers.length,title:"",popup:"",description:!1,visibility:"always"}).addTo(r.map),r.markers.push(r.current_marker),r.current_marker.on("click",function(e){switch(r.mode){case"lfh-edit-marker":r.current_marker=this,r.init_window();break;case"lfh-delete-marker":r.current_marker=null,r.delete_marker(this)}}),r.set_mode(document.querySelector("#lfh-edit-marker")),r.current_marker},add_marker_from_form:function(){var e=document.querySelector('#window-add-marker input[name="lfh-lat"]').value,t=document.querySelector('#window-add-marker input[name="lfh-lng"]').value,o={lat:e,lng:t};r.add_marker({latlng:o})},delete_marker:function(e){if(confirm(r.confirm)){for(var t=e.options.index,o=t;o<r.markers.length-1;o++)r.markers[o]=r.markers[o+1],r.markers[o].options.index=o;r.map.removeLayer(e),r.markers.pop()}},set_mode:function(e){if("lfh-view"!=r.mode){var t=document.querySelector(".marker-control.active");t.className=t.className.replace(" active","")}var o=document.querySelectorAll(".lfh-form-edit");[].forEach.call(o,function(e){e.style.display="none"}),e.id==r.mode?r.mode="lfh-view":(r.mode=e.id,e.className=e.className+" active",r.init_window()),"lfh-add-marker"==r.mode?(r.move_marker.setOpacity(1),document.querySelector("#window-add-marker").style.display="block"):r.move_marker.setOpacity(0)},get_position_info:function(){r.record={center:r.map.getCenter(),zoom:r.map.getZoom()},r.write_position()},write_position:function(){var e="<b> lat</b> = "+r.record.center.lat+"<br />";e+="<b> lng</b> = "+r.record.center.lng+"<br />",e+="<b> zoom</b> = "+r.record.zoom,document.querySelector("#map-position").innerHTML=e},init_window:function(){switch(r.mode){case"lfh-edit-map":document.querySelector("#window-edit-map").style.display="block";break;case"lfh-edit-marker":if(null==r.current_marker)return;var e=r.current_marker.options,t=e.icon.options.markerColor,o=e.icon.options.icon;document.querySelector('#window-edit-marker input[name="title"]').value=e.title.stripslashes(),document.querySelector('#window-edit-marker textarea[name="popup"]').value=e.popup.stripslashes(),document.querySelector('#window-edit-marker input[name="description"]').checked=e.description,document.querySelector('#window-edit-marker select[name="visibility"]').selectedIndex="always"==e.visibility?0:1;var n=document.createEvent("MouseEvents");n.initEvent("click",!0,!0),document.querySelector("#color-marker div.awesome-marker-icon-"+t).dispatchEvent(n),document.querySelector("#icon-marker div.lfhicon-"+o).dispatchEvent(n),document.querySelector("#window-edit-marker").style.display="block"}},close_window:function(e){switch(e){case"window-edit-marker":r.close_color(),r.close_icon(),document.querySelector("#"+e).style.display="none";break;case"window-edit-map":var t=document.createEvent("MouseEvents");t.initEvent("click",!0,!0),document.querySelector("#lfh-edit-map").dispatchEvent(t);break;case"window-add-marker":document.querySelector("#"+e).style.display="none"}},close_color:function(){var e=document.querySelector("#window-edit-marker .to-extend label "),t=e.parentNode.querySelector("div");"-"==e.textContent&&(e.textContent="+",t.style.display="none")},close_icon:function(){var e=document.querySelector("#window-edit-marker .to-extend:last-child label"),t=e.parentNode.querySelector("div");"-"==e.textContent&&(e.textContent="+",t.style.display="none")},write_checked:function(e){document.querySelector('input[name="'+r.map_var[e]+'"]').checked},shortcode_map:function(){var e="";return e+="[lfh-map ",document.querySelector('input[name="lfh-form-map-autocenter"]').checked||(e+=" lat="+r.record.center.lat,e+=" lng="+r.record.center.lng,e+=" zoom="+r.record.zoom),""!=document.querySelector('input[name="lfh-form-map-class"]').value&&(e+=' class="'+document.querySelector('input[name="lfh-form-map-class"]').value.replaceAll("-","\\-")+'"'),["autocenter","fullscreen","reset","list","mousewheel","open","undermap"].forEach(function(t){e+=" "+t+"="+document.querySelector('input[name="lfh-form-map-'+t+'"]').checked}),["width","height"].forEach(function(t){e+=" "+t+"="+document.querySelector('input[name="lfh-form-map-'+t+'"]').value}),e+=" tile="+document.querySelector('select[name="lfh-form-map-tile"]').value,e+=" ] <br /> "},shortcode:function(e){var t="";switch(e){case"map":t+=r.shortcode_map();break;case"markers":for(var o=0;o<r.markers.length;o++){var n=r.markers[o].getLatLng(),a=r.markers[o].options,c=a.icon.options.markerColor,i=a.icon.options.icon;t+="[lfh-marker lat="+n.lat+" lng="+n.lng,t+=" color="+c+" icon="+i,t+=' title="'+a.title+'"',t+=' popup="'+a.popup+'"',t+=" visibility="+a.visibility,t+=" ]",a.description&&(t+="<br />"+r.add_description+"<br />"),t+="[/lfh-marker]<br />  "}}return t}};L.DomEvent.addListener(document.querySelector("form"),"submit",function(e){e.preventDefault()});var o={selected:null,x_pos:0,y_pos:0,x_elem:0,y_elem:0,init:function(e){o.selected=e,o.x_elem=o.x_pos-o.selected.offsetLeft,o.y_elem=o.y_pos-o.selected.offsetTop},move:function(e){if(o.x_pos=document.all?window.event.clientX:e.pageX,o.y_pos=document.all?window.event.clientY:e.pageY,null!==o.selected){var t=Math.min(Math.max(-200,o.x_pos-o.x_elem),window.innerWidth-100);o.selected.style.left=t+"px";var r=Math.min(Math.max(0,o.y_pos-o.y_elem),window.innerHeight-40);o.selected.style.top=r+"px"}},destroy:function(){o.selected=null}};document.onmousemove=o.move,document.onmouseup=o.destroy;var n=document.querySelectorAll(".lfh-form-edit .header");[].forEach.call(n,function(e){L.DomEvent.addListener(e,"mousedown",function(t){o.init(e.parentNode)})});var a=document.querySelectorAll(".lfhicon-close");[].forEach.call(a,function(e){L.DomEvent.addListener(e,"click",function(t){var o=e.parentNode.parentNode.id;r.close_window(o)})});var a=document.querySelectorAll(".to-extend label");[].forEach.call(a,function(e){L.DomEvent.addListener(e,"click",function(e){var t=e.target,r=t.parentNode.querySelector("div");"+"==t.textContent?(t.textContent="-",r.style.display="inline-block"):(t.textContent="+",r.style.display="none")})});var c=document.querySelector('#window-add-marker input[name="placeMarker"]');L.DomEvent.addListener(c,"click",function(e){r.add_marker_from_form()});var c=document.querySelector('#window-edit-map input[name="lfh-form-map-autocenter"]');L.DomEvent.addListener(c,"click",function(e){document.querySelector("#center-map").style.display=e.target.checked?"none":"block",r.get_position_info()});var c=document.querySelector('input[name="save-center"]');L.DomEvent.addListener(c,"click",function(e){r.get_position_info()});var c=document.querySelector('#window-edit-map select[name="lfh-form-map-tile"]');L.DomEvent.addListener(c,"change",function(e){r.set_tile()});var i=document.createEvent("MouseEvents");i.initEvent("click",!0,!0),document.querySelector('#window-edit-map input[type="reset"]').dispatchEvent(i);var c=document.querySelector('#window-edit-marker input[name="title"]');L.DomEvent.addListener(c,"change",function(e){r.current_marker.options.title=this.value.addslashes()});var c=document.querySelector('#window-edit-marker textarea[name="popup"]');L.DomEvent.addListener(c,"change",function(e){var t=this.value;r.current_marker.options.popup=this.value.addslashes(),r.current_marker.bindPopup(t).openPopup()});var c=document.querySelector("#window-edit-marker input[name='description']");L.DomEvent.addListener(c,"click",function(e){r.current_marker.options.description=this.checked});var c=document.querySelector("#window-edit-marker select");L.DomEvent.addListener(c,"change",function(e){r.current_marker.options.visibility=this.value});var a=document.querySelectorAll("#selected-icon , #selected-color");[].forEach.call(a,function(e){L.DomEvent.addListener(e,"focus",function(t){for(var o=e.parentNode.nextSibling;1!=o.nodeType;)o=o.nextSibling;o.querySelector("label").textContent="-",o.querySelector("div").style.display="inline-block","selected-icon"==e.id&&r.close_color()})});var a=document.querySelectorAll("#color-marker div.awesome-marker");[].forEach.call(a,function(e){L.DomEvent.addListener(e,"click",function(e){var t=this,o=document.querySelector("#color-marker div.selected"),n=o.className.replace(" selected","");o.className=n,n=t.className,t.className=n+" selected";var a=t.dataset.value,o=document.querySelector("#selected-color");o.className=n,o.dataset.value=a;var c=document.querySelector("#selected-icon").dataset.value;r.current_marker.setIcon(L.AwesomeMarkers.icon({icon:c,prefix:"lfhicon",markerColor:a}))})});var a=document.querySelectorAll("#icon-marker div.lfhicon");[].forEach.call(a,function(e){L.DomEvent.addListener(e,"click",function(e){var t=this,o=document.querySelector("#icon-marker div.selected"),n=o.className.replace(" selected","");o.className=n,n=t.className,t.className=n+" selected";var a=t.dataset.value,o=document.querySelector("#selected-icon");o.className=n,o.dataset.value=a;var c=document.querySelector("#selected-color").dataset.value;r.current_marker.setIcon(L.AwesomeMarkers.icon({icon:a,prefix:"lfhicon",markerColor:c}))})});var l=null,d=null;document.querySelector("#lfh-add-gpx").addEventListener("click",function(e){l||(l=wp.media({title:"Insert a gpx",library:{type:"application/gpx+xml"},multiple:!0,button:{text:"Insert"}}),l.open())}),document.querySelector("#insert-media-button").addEventListener("click",function(e){d||(d=wp.media({title:"Insert a media",library:{type:"image"},multiple:!0,button:{text:"Insert"}}),d.open())}),r.init_map()}(lfh),String.prototype.addslashes=function(){return this.replace(/[\\"']/g,"\\$&").replace(/\u0000/g,"\\0")},String.prototype.stripslashes=function(){return this.replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}})},String.prototype.replaceAll=function(e,t){return this.replace(new RegExp(e,"g"),t)};