String.prototype.stripslashes = function()
{return this.replace(/\\(.?)/g, function (s, n1){switch (n1){case '\\':return '\\';case '0':return '\u0000';case '':return '';default:return n1;}});};

(function($,options){
 
//For Adding button mode  on map control

var LfhControl = L.Control.extend({
    options: {
      position: 'topleft' 
    },
   
    onAdd: function (map) {
        var container = document.querySelector('#lfh-control');
        return container;
    },
   
});
L.Layer.prototype.onNameClick = function (e) {
    if (lfh.mode === 'lfh-edit-marker')
    this.fire('click');
}
var LfhControlLayer = L.Control.Layers.extend({
      updateName(layer, name) {
        var done = false;
        for (var i=0; i < this._layers.length && !done; i++) {
            if (this._layers[i].layer === layer) {
                this._layers[i].name = name;
                done = true;
            }
        }
        this._update()
      },
      _addItem: function (obj) {
        var label = document.createElement('span'),
            checked = this._map.hasLayer(obj.layer),
            input;

        if (obj.overlay) {
            input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'leaflet-control-layers-selector';
            input.defaultChecked = checked;
        } else {
            input = this._createRadioElement('leaflet-base-layers', checked);
        }

        this._layerControlInputs.push(input);
        input.layerId = L.Util.stamp(obj.layer);

        L.DomEvent.on(input, 'click', this._onInputClick, this);

        var name = document.createElement('a');
        name.innerHTML = ' ' + obj.name;
        name.setAttribute('data-index', input.layerId);
        L.DomEvent.on(name, 'click', obj.layer.onNameClick, obj.layer);
        // Helps from preventing layer control flicker when checkboxes are disabled
        // https://github.com/Leaflet/Leaflet/issues/2771
        var holder = document.createElement('div');

        label.appendChild(holder);
        holder.appendChild(input);
        holder.appendChild(name);

        var container = obj.overlay ? this._overlaysList : this._baseLayersList;
        container.appendChild(label);

        this._checkDisabledLayers();
        return label;
    }
});
 function bool(value) {
     if(value === 'false') {
         return false;
     } else {
         return Boolean(value);
     }
 }
var lfh = {
        mode: "lfh-edit-marker",
        tiles : options.tiles,
        map: null,
        center: [48.67777858405578, 2.166026472914382], //by default center = les ulis France
        zoom: 2,
        latlngbounds: [],
        current_layer: null, // the selected marker
        default_icon: null,
        move_marker: null, // seen when in mode add-marker
        markers: new Array(), // array of the added markers
        gpx: new Array(),
        tile: null, // the current L.tileLayer
        record: null, //record center and zoom when user want
        shortcode: {map:'[lfh-map]', gpx:'', markers: ''},
        map_options: {},
        controlLayer: null,
        POINT_ICON:  L.icon({
            iconUrl: options.ICON_URL + 'markers/pointS000063.png',
            iconSize:     [10, 10], 
            shadowSize:   [0, 0], 
            iconAnchor:   [5, 5], 
            shadowAnchor: [5, 5],  
            popupAnchor:  [5, 5]
        }),
        MINI_POINT_ICON: L.icon({
            iconUrl: options.ICON_URL + 'markers/pointS6.png',
            iconSize:     [6, 6], 
            shadowSize:   [0, 0], 
            iconAnchor:   [3, 3], 
            shadowAnchor: [3, 3],  
            popupAnchor:  [3, 3]
        }),
        init_map: function(){
            var first = function(element) { return !!element }    
            var map_data = options.data.find(first).map
            lfh.init_map_options(map_data)
            lfh.map = L.map('map').setView(lfh.center, lfh.zoom);
            lfh.set_tile();
            lfh.map.addControl(new LfhControl());
          //handler init event when control is added
            var nodes = document.querySelectorAll('.marker-control');
            [].forEach.call(nodes, function(button){
                L.DomEvent.addListener(button,'click', function(e){
                    lfh.set_mode(button);
                    e.stopPropagation();
                });
            });
            lfh.controlLayer = new LfhControlLayer();
            lfh.controlLayer.addTo(lfh.map);
            lfh.default_icon = L.AwesomeMarkers.icon({
                icon: 'circle',
                prefix: 'lfhicon',
                markerColor: 'red'});
            lfh.move_marker = L.marker(lfh.center, {interactive: false}).addTo(lfh.map);
            lfh.move_marker.setOpacity(0);
            //handler event
            lfh.map.on("mousemove", function(e){
                switch(lfh.mode){
                    case 'lfh-add-marker':
                        lfh.move_marker.setLatLng(e.latlng);
                        break;
                    default:
                }
            });
            lfh.map.on('click', function(e){
                switch(lfh.mode) {
                    case 'lfh-add-marker':
                    lfh.add_marker(e, true);
                    break;
                } 
            });
            lfh.init_data();
        },
        init_map_options: function (map_data) {
            lfh.init_listener_map_editor()
            // set value to form and map_options
            lfh.map_options = map_data;
            for(var key in map_data) {
                var node = document.querySelector('[name="lfh-form-map-' + key +'"]')
                if (node.type === 'checkbox') {
                    node.checked = bool(map_data[key])
                } else {
                    node.value = map_data[key]
                }
                if ( ['fullscreen', 'tile', 'open'].indexOf(key) >=0) {
                    document.querySelector('input[name="lfh-form-global-'+ key + '"]').checked = false
                    node.disabled = false
                }
            }
        },
        init_listener_map_editor: function () {
            var nodes = document.querySelectorAll('#window-edit-map [name^="lfh-form-map' + '"]');
            [].forEach.call(nodes, function (node) {
                L.DomEvent.addListener(node, 'change', function (e) {
                    var key = e.target.name.replace('lfh-form-map-', '');
                    if (node.type === 'checkbox') {
                        lfh.map_options[key] = e.target.checked;
                    } else {
                        lfh.map_options[key] = e.target.value
                    }
                   
                    switch(key) {
                        case 'autocenter':
                            document.querySelector('#center-map').style.visibility = e.target.checked ? 'collapse':'visible';
                            lfh.get_position_info();
                        break;
                        case 'tile':
                            lfh.set_tile();
                        break;
                    }
                    lfh.shortcode()
                    console.log(lfh.map_options)
                })
                
            })
            // var node = document.querySelector('#window-edit-map input[name="lfh-form-map-autocenter"]');
            // L.DomEvent.addListener(node, 'click', function(e){
            //     document.querySelector('#center-map').style.visibility = e.target.checked ? 'collapse':'visible';
            //     lfh.get_position_info();
            // });
            var node = document.querySelector('input[name="save-center"]');
            L.DomEvent.addListener(node, 'click', function(e){
                lfh.get_position_info();
            });
            // var node = document.querySelector('#window-edit-map select[name="lfh-form-map-tile"]');
            // L.DomEvent.addListener(node, 'change', function(e){
            //     lfh.set_tile();
            // });
            var nodes = document.querySelectorAll('#window-edit-map input[name^="lfh-form-global-"]');
             [].forEach.call(nodes, function(input){
                 L.DomEvent.addListener(input, 'click', function(event) {
                    var node = this.parentNode.parentNode.querySelector('[name^="lfh-form-map"]');
                    node.disabled = this.checked
                    if (this.checked) {
                        // set default value
                        var key = node.name.replace('lfh-form-map-', '')
                         if (node.type === 'checkbox') {
                            node.checked = bool(options.default.map[key])
                        } else {
                            node.value = options.default.map[key]
                            lfh.set_tile()
                        }
                    }
                 })
             })
            var evt = document.createEvent('MouseEvents');
            evt.initEvent("click", true, true);
            document.querySelector('#window-edit-map input[type="reset"]').dispatchEvent(evt); 

            document.querySelector('#window-edit-map input[type="reset"]').onclick = function(){
                document.querySelector('#center-map').style.display = 'none';
                // disable fullscreen, tile, open
                ['fullscreen', 'tile', 'open'].forEach(function (key) {
                    document.querySelector('[name="lfh-form-map-'+ key + '"]').disabled = true
                })
                setTimeout(function(){
                    if(lfh.tiles != null)
                        lfh.set_tile();
                }, 500);
            }
        },
        init_data: function() {
            var count = 0
            options.data.forEach(function (map) {
                if(map.gpx) {
                    map.gpx.forEach(function (gpx_options) {
                        lfh.add_gpx(gpx_options, false);
                    })
                }
                if (map.markers) {
                    map.markers.forEach(function(marker_options) {
                        console.log('add marker')
                        marker_options.latlng = L.latLng(marker_options.lat, marker_options.lng)
                        marker_options.iconAwesome = L.AwesomeMarkers.icon({
                            icon: marker_options.icon,
                            prefix: 'lfhicon',
                            markerColor: marker_options.color
                        })
                        lfh.add_marker(marker_options, false)
                    })
                }
                // autocenter + no gpx
                var auto_center = document.querySelector('input[name="lfh-form-map-autocenter"]').checked;
                if(auto_center && map.gpx.length == 0 && lfh.latlngbounds.length > 1){
                    lfh.map.fitBounds(lfh.latlngbounds);
                }
            })
        },
        set_tile: function(){
            var tilename = document.querySelector('select[name="lfh-form-map-tile"]').value;
            var tileinfo = lfh.tiles[ tilename ];
           
            if(lfh.tile != null ){
                lfh.map.removeLayer(lfh.tile);
            }
            lfh.tile = L.tileLayer(tileinfo.url, {
                attribution: tileinfo.attribution
            });
            lfh.tile.addTo(lfh.map);
           // lfh.map.options.minZoom = tileinfo.min_zoom;
           // lfh.map.options.minZoom = tileinfo.max_zoom;
        },
        add_marker: function(e, neo){
            var marker = L.marker(
                    e.latlng,
                    {
                        icon: e.iconAwesome? e.iconAwesome:lfh.default_icon,
                        draggable: true,
                        interactive: true,
                        bubblingMouseEvents: false,
                        index: lfh.markers.length,
                        title: e.title ? e.title : '',
                        popup: e.popup ? e.popup: '',
                        description: e.description? true:false,
                        content: e.description ? e.description.replace('/"/g','&quot;') : '',
                        visibility: e.visibility ? e.visibility : 'always'
                    });
                
                marker.on('click', function(){
                    switch(lfh.mode){
                        case 'lfh-edit-marker':
                            lfh.current_layer = this;
                            lfh.init_window();
                            break;
                        case 'lfh-delete-marker':
                            lfh.current_layer = null;
                            lfh.delete_marker(this);
                            break;
                        default:
                    }
                });
                marker.on('moveend', function() {
                    lfh.shortcode();
                });
                marker.addTo(lfh.map);
                lfh.markers.push(marker);
                lfh.shortcode();
                lfh.controlLayer.addOverlay(marker, e.title ? e.title : 'unknown');
                var auto_center = document.querySelector('input[name="lfh-form-map-autocenter"]').checked;
                if(auto_center){
                    lfh.latlngbounds.push(e.latlng);
                }
                //switch to edit mode
                if (neo) {
                    lfh.current_layer = marker;
                    lfh.set_mode(document.querySelector('#lfh-edit-marker'));
                }
                 // - for edit marker
   
              //  return marker;
        },
        add_marker_from_form: function(){
            var lat = document.querySelector('#window-add-marker input[name="lfh-lat"]').value;
            var lng = document.querySelector('#window-add-marker input[name="lfh-lng"]').value;
            var latlng = {lat: lat, lng: lng}
            lfh.add_marker({latlng: latlng}, true);
        },
        delete_marker: function(marker){
            var answer = confirm( lfh.confirm);
            if(!answer){
                return;
            }
            var index = marker.options.index; 
            for(var i=index;i<lfh.markers.length-1;i++){
                lfh.markers[i] = lfh.markers[i+1];
                lfh.markers[i].options.index = i;
            }
            lfh.map.removeLayer(marker);
            lfh.markers.pop();
        },
        //change mode from node button
        set_mode: function(node){
            //unactive the active
            if(lfh.mode !== 'lfh-edit-marker'){
                 $('.marker-control.active').removeClass('active');
                 // active.className = active.className.replace(' active', '');
            }
            //close window edit
            var nodes = document.querySelectorAll('.lfh-form-edit');
            [].forEach.call(nodes, function(node){
                node.style.display = 'none';
            });
            if(node.id === lfh.mode){
                lfh.mode = 'lfh-edit-marker';
            }else{
                //active the mode
                lfh.mode = node.id;
                node.className = node.className + " active";
                //case  add see marker window
                if (lfh.mode === 'lfh-edit-map') {
                 lfh.init_window();
                }
            }
            console.log(lfh.mode);
            if(lfh.mode.indexOf('lfh-add') >=0) {
                 $('#lfh-add-element').addClass('active');
            }
            if(lfh.mode == 'lfh-add-marker'){
                //show move_marker
                lfh.move_marker.setOpacity(1);
               
                //show manual marker
                document.querySelector('#window-add-marker').style.display = 'block';
            }else{
                //hide move_marker
                lfh.move_marker.setOpacity(0);
            }
        },
        add_gpx: function(post, neo) {
            console.log(post)
            var src = post.src? post.src : post.url
            var color = post.color ? post.color: options.default.gpx.color
            var width = post.width ? post.width: options.default.gpx.width
            
            var gpx = new L.GPX(
                 src, 
                {
                    async: true,
                    isLoaded: false,
                    title: post.title,
                    description: post.description,
                    polyline_options: {
                      color: color,
                      weight: width
                    },
                    marker_options: {
                        startIcon: width> 2 ? lfh.POINT_ICON:lfh.MINI_POINT_ICON,
                        endIcon: width> 2 ? lfh.POINT_ICON:lfh.MINI_POINT_ICON,
                        //shadowUrl: 'images/pin-shadow.png'
                      }
                 }).on('loaded', function(e) {
                      e.target.options.isLoaded = true;
                      var auto_center = document.querySelector('input[name="lfh-form-map-autocenter"]').checked;
                      if(auto_center){
                         var bounds = e.target.getBounds();
                         lfh.latlngbounds.push([bounds.getNorth(),bounds.getEast()]);
                         lfh.latlngbounds.push([bounds.getSouth(),bounds.getWest()]);
                      }
                      if (!neo) {
                          console.log('neo');
                      }
                      lfh.after_load();
                 }).on('click', function(e) {
                    console.log('click on gpx');
                 }).addTo(lfh.map);
           lfh.gpx.push(gpx);
           lfh.controlLayer.addOverlay(gpx, post.title ? post.title : 'unknown');
           lfh.shortcode();
            if (neo) {
                    lfh.current_layer = gpx;
                  // lfh.set_mode(document.querySelector('#lfh-edit-marker'));
            }
        },
        after_load: function() {
             var auto_center = document.querySelector('input[name="lfh-form-map-autocenter"]').checked;
             if(!auto_center){
                 return;
             }
            // count if all gpx is loaded
            var count = 0;
            lfh.gpx.forEach(function (gpx) {
                if (gpx.options.isLoaded) {
                    count++;
                }
            })
            if (count === lfh.gpx.length) {
                lfh.map.fitBounds(lfh.latlngbounds);
            }
        },
        //get position center and zoom of the map, record and write it
        get_position_info: function(){
            lfh.record = {
                    center: lfh.map.getCenter(),
                    zoom: lfh.map.getZoom()
            };
           lfh.write_position();
        },
     
        write_position: function(){
            var content = '<b> lat</b> = ' + lfh.record.center.lat + '<br />';
            content += '<b> lng</b> = ' + lfh.record.center.lng + '<br />';
            content += '<b> zoom</b> = ' + lfh.record.zoom;
            document.querySelector('#map-position').innerHTML = content;
            lfh.map_options.lat = lfh.record.center.lat;
            lfh.map_options.lng = lfh.record.center.lng;
            lfh.map_options.zoom = lfh.record.zoom;
        },
        //open window with good information
        init_window: function(){
            lfh.shortcode();
            switch(lfh.mode){
            case 'lfh-edit-map':
                document.querySelector('#window-edit-map').style.display = 'block';
                break;
            case 'lfh-edit-marker':
                 if(lfh.current_layer == null ) return;
                //init the window "edit marker" with information of current_layer
                //from current marker to edit window
                var options = lfh.current_layer.options;
                var color = options.icon.options.markerColor;
                var icon = options.icon.options.icon;
                document.querySelector('#window-edit-marker input[name="title"]').value = options.title.stripslashes();
                document.querySelector('#window-edit-marker textarea[name="popup"]').value = options.popup.stripslashes();
                document.querySelector('#window-edit-marker input[name="description"]').checked = options.description;
                document.querySelector('#marker-description').value = options.content.replace(/\&quot\;/g,'"');
                document.querySelector('#window-edit-marker select[name="visibility"]').selectedIndex = (options.visibility == 'always')? 0:1;
                var evt = document.createEvent('MouseEvents');
                evt.initEvent("click", true, true);
                document.querySelector('#color-marker div.awesome-marker-icon-'+color).dispatchEvent(evt);
                document.querySelector('#icon-marker div.lfhicon-'+icon).dispatchEvent(evt); 
                document.querySelector('#window-edit-marker').style.display = 'block';
                break;
            }
          
            
           
        },
        // close the widow "edit marker"
        close_window: function(id){
            switch(id){
            case 'window-edit-marker':
                lfh.close_color();
                lfh.close_icon();
                document.querySelector('#'+id).style.display = 'none';
                lfh.shortcode();
                break;
            case 'window-edit-map':
                var evt = document.createEvent('MouseEvents');
                evt.initEvent("click", true, true);
                document.querySelector('#lfh-edit-map').dispatchEvent(evt);
                break;
            case 'window-add-marker':
                document.querySelector('#' + id).style.display = 'none';
                break;
            default:
                break;
            }
        },

        close_color: function(){
            var label = document.querySelector('#window-edit-marker .to-extend label ');
            var div = label.parentNode.querySelector("div");
            if(label.textContent == '-'){
                label.textContent = '+';
                div.style.display = 'none';
            }
        },
        close_icon: function(){
            var label = $('#window-edit-marker .to-extend label').eq(1);
            var div = label.parent().find('> div');
            if(label.textContent == '-'){
                label.textContent = '+';
                div.style.display = 'none';
            }
        },
        write_checked: function(name){
            var str = ' ' + name + '='+document.querySelector('input[name="' + lfh.map_var[name] +'"]').checked;
        },
        shortcode_map:function(){
            var shortcode = '';
            shortcode += '[lfh-map ';
            for( var key in lfh.map_options) {
                if (key === "class") {
                    shortcode += key + '="' + lfh.map_options[key] + '" ';
                } else {
                    shortcode +=  key + '=' + lfh.map_options[key] + ' ';
                }
            }
            shortcode += ']'; 
            console.log(shortcode);
            return shortcode;
            // if(!document.querySelector('input[name="lfh-form-map-autocenter"]').checked){
            //     shortcode += ' lat=' + lfh.record.center.lat ;
            //     shortcode += ' lng=' + lfh.record.center.lng ;
            //     shortcode += ' zoom=' + lfh.record.zoom ;
            // }
            // if(document.querySelector('input[name="lfh-form-map-class"]').value != ''){
            //     shortcode += ' class="' + document.querySelector('input[name="lfh-form-map-class"]').value.replaceAll('-','\\-') +'"';
            // }
            // ['autocenter', 'fullscreen', 'reset', 'list', 'mousewheel', 'open', 'undermap'].forEach(function(key){
            //     shortcode += ' '+ key + '=' + document.querySelector('input[name="lfh-form-map-' + key + '"]').checked;
            // });
            // ['width', 'height'].forEach( function(key){
            //     shortcode += ' '+ key + '=' + document.querySelector('input[name="lfh-form-map-' + key + '"]').value;
            // });
            // shortcode += ' tile=' + document.querySelector('select[name="lfh-form-map-tile"]').value;
            // shortcode += ' ] <br /> ';
            // return shortcode;
        },
        shortcode_marker: function(marker) {
            var latlng = marker.getLatLng();
            var options = marker.options;
            var color = options.icon.options.markerColor;
            var icon = options.icon.options.icon;
            var shortcode = '[lfh-marker lat='+latlng.lat +' lng=' + latlng.lng;
            shortcode += ' color=' + color + ' icon=' + icon;
            shortcode += ' title="' + options.title +'"';
            shortcode += ' popup="' + options.popup +'"';
            console.log(options.visibility);
            shortcode += ' visibility=' + options.visibility;
            shortcode += ' ]' ;
            if(options.description){
                shortcode +=  options.content.replace(/\&quot\;/g,'"');
            }
            shortcode += '[/lfh-marker]';
            return shortcode
        },
        shortcode_gpx: function (gpx) {
               var shortcode = '[lfh-gpx src=' + gpx._gpx;
               shortcode += ' title="' + gpx.options.title + '"';
               shortcode += ' color=' + gpx.options.polyline_options.color;
               shortcode += ' width=' + gpx.options.polyline_options.weight;
               shortcode += ']'+ gpx.options.description + '[/lfh-gpx]';
               return shortcode;
        },
        shortcode: function(){
            var shortcode = '';
            shortcode += lfh.shortcode_map();
            lfh.markers.forEach( function(marker) {
                shortcode += lfh.shortcode_marker(marker);
            })
            console.log(lfh.gpx);
            console.log(lfh.gpx.length);
            lfh.gpx.forEach(function(gpx){
               shortcode += lfh.shortcode_gpx(gpx);
            })
            document.querySelector('textarea[name="content"]').value = shortcode;
            return shortcode;
        }
}

//handler add when buttons control are placed on map
/*var nodes = document.querySelectorAll('.marker-control');
[].forEach.call(nodes, function(button){
    L.DomEvent.addListener(button,'click', function(e){
        lfh.set_mode(button);
        e.stopPropagation();
    });
});*/
//no submit form
L.DomEvent.addListener(
        document.querySelector('form'),
        'submit',
        function(e){
            e.preventDefault();
        });
        
// draggable edit marker window
var hdrg = {
        selected : null, // Object of the element to be moved
        x_pos : 0,
        y_pos : 0, // Stores x & y coordinates of the mouse pointer
        x_elem : 0, 
        y_elem : 0, // Stores top, left values (edge) of the element

    // Will be called when user starts dragging an element
     init: function(elem) {
        // Store the object of the element which needs to be moved
        hdrg.selected = elem;
        hdrg.x_elem = hdrg.x_pos - hdrg.selected.offsetLeft;
        hdrg.y_elem = hdrg.y_pos - hdrg.selected.offsetTop;
    },

    // Will be called when user dragging an element
    move: function(e) {
        hdrg.x_pos = document.all ? window.event.clientX : e.pageX;
        hdrg.y_pos = document.all ? window.event.clientY : e.pageY;
        if (hdrg.selected !== null) {
            var left = Math.min( Math.max(-200, hdrg.x_pos - hdrg.x_elem), window.innerWidth-100);
            hdrg.selected.style.left = left + 'px';
            
            var top = Math.min(Math.max(0 , hdrg.y_pos - hdrg.y_elem), window.innerHeight -40);
            hdrg.selected.style.top = top + 'px';
        }
    },

    // Destroy the object when we are done
    destroy:function() {
        hdrg.selected = null;
    }
}
// Bind the functions...
document.onmousemove = hdrg.move;
document.onmouseup = hdrg.destroy;

//add drag listener
 var headers = document.querySelectorAll('.lfh-form-edit .header');
 [].forEach.call(headers, function(header){
        L.DomEvent.addListener(header,'mousedown', function(e){
            hdrg.init(header.parentNode);
        });
 });
//- end drag
// event on elements of window edit marker and edit map
   var nodes = document.querySelectorAll('.lfhicon-close');
   [].forEach.call(nodes, function(node){
       L.DomEvent.addListener(node, 'click', function(e){
           var id = node.parentNode.parentNode.id;
           lfh.close_window(id);
       });
   });
   // show/hide extend part of form
   var nodes = document.querySelectorAll(".to-extend label");
   [].forEach.call(nodes, function(label){
       L.DomEvent.addListener(label,'click', function(e){
           var label = e.target;
           var div = label.parentNode.querySelector("div");
           if(label.textContent == "+"){
               label.textContent = "-";
               div.style.display = "inline-block";
           }else{
               label.textContent = "+";
               div.style.display = "none";
           }
       });
   });
   // - for add manual marker
   // ------------------------
   var node = document.querySelector('#window-add-marker input[name="placeMarker"]');
   L.DomEvent.addListener(node, 'click', function(e){lfh.add_marker_from_form();})
  

   // - for edit marker
    var node = document.querySelector('#window-edit-marker input[name="title"]');
    L.DomEvent.addListener(node,'change', function(e){
        lfh.current_layer.options.title = e.target.value.addslashes();
        lfh.controlLayer.updateName(lfh.current_layer, e.target.value);
    });
    
  
    var node = document.querySelector('#window-edit-marker textarea[name="popup"]');
    L.DomEvent.addListener(node,'change', function(e){
        var content = e.target.value;
        lfh.current_layer.options.popup = e.target.value.addslashes();
        lfh.current_layer.bindPopup(content).openPopup();
        
    });
    var node = document.querySelector("#window-edit-marker input[name='description']");
    L.DomEvent.addListener(node,'click', function(e){
        lfh.current_layer.options.description = e.target.checked;
        
    });
    var node = document.querySelector("#window-edit-marker select[name='visibility']");
    L.DomEvent.addListener(node,'change', function(e){
        lfh.current_layer.options.visibility = e.target.value;
        lfh.shortcode();
        
    });
    var node = document.querySelector('#marker-description');
    L.DomEvent.addListener(node, 'change keypress', function(e) {
        lfh.current_layer.options.content = node.value.replace('/"/g', '&quot;');
        lfh.shortcode();
    });
    var nodes = document.querySelectorAll("#selected-icon , #selected-color");
    [].forEach.call(nodes, function(div){
        L.DomEvent.addListener(div,'focus', function(e){
            var node = div.parentNode.nextSibling;
            while(node.nodeType !=1){
                node = node.nextSibling;
            }
            node.querySelector("label").textContent = "-";
            node.querySelector("div").style.display = "inline-block";
            if(div.id == 'selected-icon'){
                lfh.close_color();
            }
        });
    });
    var nodes = document.querySelectorAll('#color-marker div.awesome-marker');
    [].forEach.call(nodes, function(div){
        L.DomEvent.addListener(div,'click', function(e){
            var div = this;
            var node =  document.querySelector('#color-marker div.selected');
            var classname = node.className.replace(' selected', '');
            node.className = classname;
            classname = div.className;
            div.className = classname + " selected";
            var color = div.dataset.value;
            var node = document.querySelector('#selected-color')
            node.className = classname;
            node.dataset.value = color;
            //change the marker
            var icon = document.querySelector('#selected-icon').dataset.value;

            lfh.current_layer.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'lfhicon',
                markerColor: color
              }));
            
        });
    });
    var nodes = document.querySelectorAll('#icon-marker div.lfhicon');
    [].forEach.call(nodes, function(div){
        L.DomEvent.addListener(div,'click', function(e){
            var div = this;
            var node =  document.querySelector('#icon-marker div.selected');
            
            var classname = node.className.replace(' selected', '');
            node.className = classname;
            
            classname = div.className;
            div.className = classname + " selected";
            var icon = div.dataset.value;
            var node = document.querySelector('#selected-icon')
            node.className = classname;
            node.dataset.value = icon;
            //change the marker
            var color = document.querySelector('#selected-color').dataset.value;

            lfh.current_layer.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'lfhicon',
                markerColor: color
              }));
            
        });
    });
    var frame = null;
    var frame2 = null;
    var post = null
    document.querySelector('#lfh-add-gpx').addEventListener('click', function(e) {
      post = null
      if (!frame) {
          frame = wp.media({
                title: 'Insert a gpx',
                library: {type: 'application/gpx+xml'},
                multiple: true,
                button: {text: 'Insert'}
            });
          frame.on('select', function() {
              var select = frame.state().get('selection')
              select.forEach(function(el){
                  lfh.add_gpx(el.toJSON(), true);
              })
          })
      }

      frame.open()
    })
     document.querySelector('#insert-media-button').addEventListener('click', function(e) {
      if (!frame2) {
          frame2 = wp.media({
                title: 'Insert a media',
                library: {type: 'image'},
                multiple: true,
                button: {text: 'Insert'}
            });
          frame2.open()
      }
    })
    lfh.init_map();
      return lfh;
})(jQuery, lfh);
String.prototype.addslashes = function()
{return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');};

String.prototype.stripslashes = function()
{return this.replace(/\\(.?)/g, function (s, n1){switch (n1){case '\\':return '\\';case '0':return '\u0000';case '':return '';default:return n1;}});};

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};