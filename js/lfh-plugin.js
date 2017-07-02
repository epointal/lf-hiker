/**
 * @author epointal
 * 
 * 
 * Create maps interactives with  markers and gpx from data in lfh.data
 * ( written with shortcode execution )
 * totaly depend of DOM call class 'lfh-element', 'lfh-element-content',
 * call the map by id, container and svg...etc
 * 
 * @use L leaflet
 * @use L.AwesomeMarkers.Icon
 * @use L.GPX
 */

(function(){
  
// Method usefull on strings

String.prototype.addslashes = function()
 {return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');};

String.prototype.stripslashes = function()
 {return this.replace(/\\(.?)/g, function (s, n1){switch (n1){case '\\':return '\\';case '0':return '\u0000';case '':return '';default:return n1;}});};

String.prototype.replaceAll = function(search, replacement) {
     var target = this;
     return target.replace(new RegExp(search, 'g'), replacement);
 };

 /**
  * @namespace lfh is declared some where else when shorcode executed
  * @param lfh.data array of object{ map, markers, gpx } data for build map
  *    declared some in DOM when shortcode executed 
  */
 /**
  * @namespace lfh.Util functions usefull and independant
  */
 if( typeof lfh.Util == 'undefined' ){
     lfh.Util = {}
 }

 /** 
  *  round with its own order of magnitude 
  *  @param float delta
  *  @return int
  *  @example
  * 3       -> 3
  * 9       -> 10
  * 33      -> 50
  * 68      -> 100
  * 333     -> 500
  * 820     -> 1000
  * 8625    -> 10000*/
 lfh.Util.step_round = function(delta)
 {
 /**  */
   var precision = Math.round( Math.log(delta)/Math.log(10) );
   var p = Math.pow (10, precision);
   var max = Math.ceil(delta / p) * p;
   return max/2> delta ? max/2:max; 
 }

/**
 * @const lfh.ZOOM_LIMIT the zoom limit for which some markers are displayed
 * @const lfh.ICON_MOVE {L.Icon} icon for marker moving on path
 * @const lfh.POINT_ICON {L.Icon} icon for start and end points of path
 * @const lfh.SELECTED_COLOR string html color for the selected path 
 **/
lfh.ZOOM_LIMIT = 11;

lfh.ICON_MOVE = L.icon({
        iconUrl: lfh.ICON_URL +'/markers/move.png',
        iconSize:     [15, 15], 
        shadowSize:   [0, 0], 
        iconAnchor:   [7, 7],
        shadowAnchor: [7, 7], 
        popupAnchor:  [7, 7]

    });
lfh.POINT_ICON = L.icon({
        iconUrl: lfh.ICON_URL + '/markers/pointS000063.png',
        iconSize:     [10, 10], 
        shadowSize:   [0, 0], 
        iconAnchor:   [5, 5], 
        shadowAnchor: [5, 5],  
        popupAnchor:  [5, 5]
    });




/** Build all on the differents maps*/
lfh.initialize = function(){
    for(var i in lfh.data){
        if(typeof lfh.data[i] != 'function') // instagram conflict
        {
            var my_map = new lfh.Map(i);
        }
     }
}

/**
 *  Add two buttons on top right : fullscreen and list of layer
 * @constructor
 * @extend {L.Control}
 */
lfh.TopControl = L.Control.extend({
   
    options: {
      position: 'topright' 
    }, 
    _fullscreen: true,
    _list: true,
    _selected: null,
    initialize: function(d, selected){
        this._fullscreen = d.fullscreen;
        this._list = d.list;
        this._index = d.i;
        this._selected = selected;
    },
    onAdd: function (map) {
        var container = L.DomUtil.create('div', 'lfh-container-fullscreen');
        if(this._fullscreen){
            var div1 =  L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-fullscreen');
            container.appendChild(div1);
            div1.onclick = function(){
                
                var id = map._container.getAttribute('id');
                var fade = L.DomUtil.get('lfh-fade');
                var container = L.DomUtil.get(id + '-fadable')
                  if( this.className.indexOf('actived') >= 0 ){
                      //reduce map
                      
                      L.DomUtil.get(id + '-skin').appendChild( container);
                      this.className = this.className.replace(' actived','');
                      fade.className = fade.className.replace(' actived','');
                      map._container.style.height= map._container.h0;
                      if(! map.options.mousewheel){
                          map.scrollWheelZoom.disable();
                      }
                  }else{
                      //fullscreen
                      fade.appendChild( container);
                      this.className += ' actived';
                      fade.className = fade.className + ' actived';
                      map.scrollWheelZoom.enable();
                      map._container.h0 = map._container.style.height;

                      map._container.style.height = "100%";
                  }
                  map.invalidateSize();
                  
                  lfh.resize(map._container);
            }
        }
        if(this._list){
            var div2 =  L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-list');
            container.appendChild(div2);
            //append list window to the map
            var link = new lfh.Link( map, div2, 'list-' + this._index , this._selected, null, null,null);
          
           /* div2.onclick = function(){
                console.log('show/hide list layer');
            }*/
        }
        return container;
    },
   
  });

/**
 *  Add button reset on top left of the map under zoomin zoomout
 *  for recenter map to initial position
 * @constructor
 * @extend {L.Control} 
 */ 
lfh.ResetControl = L.Control.extend({
    options: {
        position: 'topleft',
    },
    _center: null,
    _zoom: 13,
    initialize: function(center,zoom){
        this._center = center;
        this._zoom = zoom;
    },
    onAdd : function(map){
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-refresh');
        var center = this._center;
        var zoom = this._zoom;
        container.onclick = function(){
            map.setView(center, zoom);
        }
        return container;
    }
})
/** Resize the DOM div lfh-element for their container
 * @param {DomNode} container the map container*/
lfh.resize = function(container){
    // compute the size of the description fonction of the parent
    var node = document.querySelector('#'+ container.id + '-data');
    var height = node.offsetHeight ;
    if(container.parentNode.parentNode.className.indexOf('lfh-min')<0){
        height -= 70;
    }else{
        height = 220;
        //delete all section hidden
        var nodes = node.querySelectorAll('.lfh-section.hidden');
        [].forEach.call(nodes, function(div) {
                var classname = div.className;
                if(classname.indexOf('disabled')>=0){
                    return;
                }
                if(classname.indexOf('hidden')>=0){
                     div.className = classname.replaceAll(' hidden', '');
                }
            
          });
        return;
    }
    var elements = node.getElementsByClassName('lfh-element');//.forEach(funtion(e){
    //var elements = node.querySelectorAll('div:not(.lfh-min) .lfh-element');
    console.log(elements);
    for(var i=0; i<elements.length;i++){
        elements.item(i).style.maxHeight = (height) +'px';
        elements.item(i).querySelector('.lfh-element-content').style.maxHeight = (height-40)+'px';
    }
}

/**
 * Build the map indexed i, with its markers, its gpx path, its controls...
 * @constructor
 * @param {int} i the index of data in array lfh.data
 */
lfh.Map = function(i){
        // Public only map
        var map = null;
        
        // all Private
        var _map_id = 'lfh-'+i;
        var _index = i;
        var _data = lfh.data[i];
        
        var _center = [48.866667,2.333333];//default value Paris if not in data
        var _zoom = 13;                    // default value if not in data
        var _zoom_limit = lfh.ZOOM_LIMIT;              // zoom from which the markers are visible
        var _auto_center = true;           // compute center and zoom from the elements added on map
        var _list = false;                 // see button list
        //remarquables layers
        var _gpx = new Array();            // layers from file gpx
        var _move_marker = null;           // marker which move on polyline according to profile
        var _layer_zoom = null;            // layer of elements which are displayed according to zoom
        var _latlngbounds = new Array();   // markers used for compute bounds of map if _auto_center
        
        // the layer selected, whose description is displayed
        var _selected_element ={
                id: null,
                layer:null,
                dom: null,
                title: document.querySelector("#"+ _map_id + "-data div.lfh-nav .lfh-trackname").textContent,
                close: function(){
                    if(this.id != null ){
                        //close fenetre
                        var classname = this.dom.className;
                        this.dom.className = classname + ' hidden';
                        if( this.layer instanceof L.GPX){
                            var options = this.layer.get_options();
                            this.layer.setStyle({color:options.realColor});
                            map.removeLayer(_move_marker);
                        }
                    }
                },
                set: function(obj){
                    for(var key in obj){
                        this[key] = obj[key];
                    }
                }
        }

        function _initialize( i ){
            _data = lfh.data[i];
            var d = lfh.data[i].map;
            d.i = i;
            _auto_center = d.autocenter;
            _list = d.list;
            _center = [d.lat, d.lng];
            _zoom = Math.min(d.zoom,lfh.tiles[d.tile].max_zoom);
            map = L.map(_map_id).setView( _center, _zoom);
            _set_tile(d.tile);
 
            map.options.mousewheel = d.mousewheel;
            if(!d.mousewheel){
                map.scrollWheelZoom.disable();
            }
           
            // Add layers
            _add_move_marker(_center);
            _add_markers( );
            _add_gpx_polylines( );
            
            //Add event listener
            _add_map_event();
            _add_loaded_listener(d.reset);
            _add_nav_event();
            // Add control button
            _add_controls(d);
            
        }
        function _set_tile(tile){
            if(tile == 'mapquest'){
                MQ.mapLayer().addTo(map);
            }else{
                L.tileLayer(lfh.tiles[tile].url, {
                    attribution: lfh.tiles[tile].attribution,
                    minZoom: 1,
                    maxZoom: lfh.tiles[tile].max_zoom
                }).addTo(map);
            }
        }
        function _add_controls(d){
            map.addControl(new lfh.TopControl(d, _selected_element));
           
            
            if(!_auto_center && d.reset){ 
                //not have to wait all elements loaded
                map.addControl(new lfh.ResetControl(_center, _zoom));
            }
        }
        function _add_markers(  ){
            //Create layer for markers "according to zoom"
            _layer_zoom = L.layerGroup();
            if(_zoom >=_zoom_limit){
                _layer_zoom.addTo(map);
            }
            // Add the markers
            for(var i in _data.markers){
                if( typeof _data.markers[i] != 'function')// conflict instagram
                {
                    _add_marker(i);
                }
            }
            
        }
        
        function _add_gpx_polylines( ){
            for(var j in _data.gpx){
                if( typeof _data.gpx[i] != 'function') //conflict instagram plugin
                {
                    _add_gpx(j);
                }
             }
        }
        function _add_map_event(){
            // hide/show markers according to zoom
            map.on('zoomend' , function(){
               
                if(map.getZoom()<_zoom_limit){
                    if(this.hasLayer(_layer_zoom))
                    this.removeLayer(_layer_zoom);
                }else{
                    if(!this.hasLayer(_layer_zoom))
                    _layer_zoom.addTo(this);
                }
            });
            map.on('resize' , function(){
                lfh.resize(this.getContainer());
            });
        }
        function _add_nav_event()
        {
            // Close button window for min screen
            var node = document.querySelector('#'+ _map_id + "-nav .lfh-close");
            L.DomEvent.addListener( node , 'click', function(e){
                _selected_element.layer.fire('click');
            });
            
            var back = document.querySelector('#'+ _map_id + "-nav .lfh-back");
            L.DomEvent.addListener( back , 'click', function(e){
               console.log( _selected_element.dom.step);
            });
            var next = document.querySelector('#'+ _map_id + "-nav .lfh-next");
            L.DomEvent.addListener( next , 'click', function(e){
               console.log( _selected_element.dom.step);
            });
            
        }
        function _add_move_marker(latlng){
            _move_marker = L.marker(latlng ,{icon: lfh.ICON_MOVE});
        }
        function _add_marker(i){
            var info = _data.markers[i];
            
            var marker_id = 'marker-' + _index +'-' +i;
           
            var marker = L.marker(
                    [info.lat, info.lng],
                    {
                        elem_id: marker_id,
                        icon:  L.AwesomeMarkers.icon({
                            icon: info.icon,
                            prefix: 'fa',
                            markerColor: info.color
                        }),
                        title: info.title.stripslashes(),
                        visibility: 'zoom'
                    });
            info.popup = info.popup + "";
            
            if(info.popup.length>0){
                marker.bindPopup(info.popup.stripslashes());

            }
            if(info.visibility == 'zoom'){
                _layer_zoom.addLayer(marker);
            }else{
                marker.addTo(map);
            }
            _latlngbounds.push([info.lat, info.lng]);
            
            //add the marker in the list of markers
            _add_marker_to_list(marker);
          
            var link = new lfh.Link( map, marker, marker_id, _selected_element, null);
            return link;
        }
        function _add_marker_to_list( marker){
            if(_list){
                var list = document.querySelector('#list-'+ _index +' ul.lfh-list-markers');
                var node = document.createElement('li');
                node.textContent = marker.options.title;
                list.appendChild(node);
                
                L.DomEvent.addListener( node , 'click', function(e){
                    marker.fire('click');
                    e.stopPropagation();
                });
            }
        }
        
        function _add_gpx_to_list( gpx ){
            if(_list){
                var list = document.querySelector('#list-'+ _index +' ul.lfh-list-gpx');
                var node = document.createElement('li');
                node.textContent = document.querySelector('#'+gpx.options.elem_id + ' span.lfh-trackname').textContent;
                list.appendChild(node);
                L.DomEvent.addListener( node , 'click', function(e){
                    gpx.fire('click');
                });
            }
        }
        function _add_loaded_listener(buttonreset){
           // turn while all files gpx aren't loaded
           var isLoaded = 1;
           for(var j in _data.gpx){
               if( typeof _data.gpx[j]!= 'function') //instagram conflict
               {
                   isLoaded *= (typeof _gpx[j]!= 'undefined' && _gpx[j].options.isLoaded);
               }
           }
           if(!isLoaded){
               setTimeout(function(){_add_loaded_listener( buttonreset );}, 500);
           }else{
               if(_latlngbounds.length>0){
                   map.fitBounds(_latlngbounds);
               }
               _center = map.getCenter();
               _zoom = map.getZoom();
               
               if(lfh.tiles[_data.map.tile].max_zoom < _zoom){
                   _zoom = lfh.tiles[_data.map.tile].max_zoom ;
                   map.setZoom( _zoom );
               }
               if(buttonreset){
                   map.addControl(new lfh.ResetControl(_center, _zoom));
               }
               lfh.resize(map.getContainer());
               _auto_center = true;
           }
       }
      
       function _add_gpx(j){
            var track_id = 'track-'+_index+'-'+j;
            _gpx[j] = new L.GPX(
                 _data.gpx[j].src, 
                {
                    async: true,
                    isLoaded: false,
                    elem_id: track_id,
                    marker_options: {
                        startIcon: lfh.POINT_ICON,
                        endIcon: lfh.POINT_ICON,
                        //shadowUrl: 'images/pin-shadow.png'
                      }
                 }).on('loaded', function(e) {
                      e.target.options.isLoaded = true;
                      e.target.setStyle({
                          color: _data.gpx[j].color,
                          weight: _data.gpx[j].width});
                      if(_auto_center){
                          var bounds = e.target.getBounds();
                          _latlngbounds.push([bounds.getNorth(),bounds.getEast()]);
                          _latlngbounds.push([bounds.getSouth(),bounds.getWest()]);
                      }

                       _add_gpx_to_list(e.target);

                      var link = new lfh.Link(
                              map,
                              e.target,
                              track_id,
                              _selected_element,
                              _move_marker,
                              _data.gpx[j].unit,
                              _data.gpx[j].unit_h);
                 }).on('failed', function(){
                      e.target.options.isLoaded = true;
                      console.log("failed");
                 })
                 .addTo(map);
        }
        _initialize( i );
        return map;
}

/**
 * The link for synchronize layer and info div node
 * show/hide the Dom div when click on the layer
 * @constructor
 * @param {L.Map} map,  the map concerned
 * @param {L.Marker | L.GPX} layer marker or gpx
 * @param {string} elem_id the identifiant of div in DOM (ex: marker-1-2 or track-2-1)
 * @param {Object<id, dom, layer>} selected the object _selected_element on the map
 * @param {L.Marker} move the object _move_marker on the map (important only for gpx)
 * @param {string} unit km or milles (important only for gpx)
 * @param {string} unit_h m or ft (important only for gpx)
 * @return {object <dom, layer, id >}
 */
lfh.Link = function( map, layer, elem_id, selected, move, unit, unit_h){
    
    var _id = elem_id;
    var _layer = layer;
    var _dom = L.DomUtil.get( elem_id);
    var _map = map;
    var _selected_element = selected;
    var _move_marker = move;
    var _unit = unit;
    var _unit_h = unit_h;
    
    function _initialize(){
        if( typeof _layer.options == 'undefined'){
            //case not really layer but button list
            _layer.options = {};
        }
        _layer.options.elem_id = elem_id;
        if(_dom != null )
        {
            _dom.step = 0;
            _dom.step_max = 5; // count number of div and add div in description 
        }
        if(_dom != null){
            // add dom node to map
           // _map.getContainer().appendChild(_dom);
            //console.log(_map.getContainer().id);
            //var i= 0;
            var data = document.querySelector("#"+_map.getContainer().id + "-data");
            var last_child = data.querySelector(".lfh-nav");
            data.insertBefore(_dom,last_child);
            _add_event();
            if( _layer instanceof L.GPX ){
                var profile = new lfh.Profile(
                        _map, 
                        _layer , 
                        _dom, 
                        _move_marker,
                        _unit,
                        _unit_h);
            }
        }
    }
    
     function _tooggle(){
        _selected_element.close();
        
        if(_selected_element.id == null || _selected_element.id != _id){
                _dom.className = _dom.className.replaceAll(' hidden', '');
                if(_layer instanceof L.GPX){
                    var options = _layer.get_options();
                    _layer.setStyle({ 
                            realColor: options.color,
                            color: lfh.SELECTED_COLOR
                     });
                    
                    _move_marker.addTo(map); 
                }
                if(_layer instanceof L.Marker ){
                    _layer.openPopup();
                }
                _selected_element.set({
                        id :    _id,
                        layer:  _layer,
                        dom:    _dom});
                _add_title_nav();
        }else{
                _selected_element.set({
                        id : null,
                        layer: null,
                        dom: null});
                if(_layer instanceof L.Marker ){
                    _layer.closePopup();
                }
                _reset_title_nav();
        }
        
    }
    function _add_title_nav(){
        // replace title nav by title of selected element
        var nav = document.querySelector("#"+_map.getContainer().id + "-data div.lfh-nav");
        var name = nav.querySelector(".lfh-trackname");
        name.textContent = _selected_element.dom.querySelector(".lfh-trackname").textContent;
        
        //remove gpx if exists
        var link_gpx = nav.querySelector(".lfh-gpx-file");
        if( link_gpx != null ){
            link_gpx.parentNode.removeChild( link_gpx );
        }
        if(_selected_element.layer instanceof L.GPX){
            // add link to gpx file
            var link_gpx = _selected_element.dom.querySelector(".lfh-gpx-file").cloneNode(true);
            nav.querySelector(".lfh-title").appendChild( link_gpx );
        }
        
    }
    function _reset_title_nav()
    {
        var nav = document.querySelector("#"+_map.getContainer().id + "-data div.lfh-nav"); 
        nav.querySelector(".lfh-trackname").textContent = _selected_element.title;
        
        // remove link to gpx file
        var link_gpx = nav.querySelector(".lfh-gpx-file");
        if( link_gpx != null ){
            link_gpx.parentNode.removeChild( link_gpx );
        }
        // link_gpx.remove();
    }
    function _add_event( ){
       // hide show section 
       var nodes = _dom.querySelectorAll('.lfh-element .lfh-header');
       [].forEach.call(nodes, function(div) {
           L.DomEvent.addListener(div ,'click', function(e){
               e.preventDefault();
               var parent = div.parentNode;
               var classname = parent.className;
               if(classname.indexOf('disabled')>=0){
                   return;
               }
               if(classname.indexOf('hidden')>=0){
                    parent.className = classname.replaceAll(' hidden', '');
               }else{
                    parent.className += ' hidden';
               }
               e.stopPropagation();
             });
         });
       
       
       L.DomEvent.addListener( _dom, 'mousemove', function(e){
           e.stopPropagation();
       });
       L.DomEvent.addListener( _dom, 'mousewheel', function(e){
           e.stopPropagation();
           e.preventDefault();
       });
       // close button
       var nodeClose = _dom.querySelector('.lfh-close');
      
    
      if( _layer instanceof L.Layer){
          _layer.on('click', function(e){
              _tooggle( );
          });
          L.DomEvent.addListener(nodeClose ,'click', function(e){
              _layer.fire('click');
          });
      }else{
        
          L.DomEvent.addListener( _layer, 'click', function(e){
              _tooggle();
          });
          L.DomEvent.addListener(nodeClose ,'click', function(e){
              _tooggle();
          });
      }
      
   }
   function translate( delta ){
       
   }
   _initialize();
   return {dom: _dom, layer: _layer, id: _id};
}

/**
 * Build the Profile for gpx path
 * @constructor
 * @param {L.Map} map 
 * @param {L.GPX} layer the gpx path
 * @param {DomNode} dom the node link to the track 
 * @param {L.Marker} move, the move_marker on polyline path 
 */
lfh.Profile = function( map, layer, dom, move, unit, unit_h){
     var _unit = unit;
     var _unit_h = unit_h;
     if( _unit == "km"){
        var  _coeff = 1;
     }else{
        var _coeff = 1.60934;
     }
     if(_unit_h == "m"){
        var _coeff_elevation = 1;
     }else{
        var _coeff_elevation = 0.3048;
     }
     var _move_marker = move;
     var _gpx = layer;
     var _track = dom;
     var _coords = _gpx.getLayers()[0].getLayers()[0].getLatLngs();
     var _data = _gpx.get_elevation_data();
     //@todo quand pas de données d'élévation on a tout de même la distance totale
     if(_data[0][1]==null){
         return null;
      }

     var _max = _gpx.get_elevation_max() / _coeff_elevation;
     var _min = _gpx.get_elevation_min() / _coeff_elevation;
     var _max_km = _data[ _data.length-1 ][0] / _coeff;
     var _step_h = lfh.Util.step_round((_max - _min)/3.5);
     var _max_h = Math.ceil( _max/_step_h)*(_step_h);
     var _min_h = _max_h - 5 * _step_h;
   
     var _step_x = lfh.Util.step_round((_max_km)/4);

     function _x(km){
         return km * 220 / (_max_km * _coeff);
     }
     function _h(h){
         return (200 - (h/_coeff_elevation - _min_h)*40/_step_h);
     }
     function _compute(){
         var d= 'M ';
         for(var i=0; i < _data.length ; i++){
             d += Math.round(_x(_data[i][0])) + ','+Math.round(_h(_data[i][1]));
             if(i!=_data.length-1){
                 d += ' L ';
             }
         }
         return d;
     }
     
     function draw(){
         // draw the curve
         var _d = _compute();
         _track.querySelector('.lfh-profile-line').setAttribute( 'd', _d);
         
         // write the value for elevation line
         for(var i=1;i<5; i++){
             _track.querySelector('.h'+i).textContent = _min_h+i * _step_h;
         }
         // move the vertical line and write the value
         for(var i=1; i<4;i++){
             var node = _track.querySelector( '.v'+i );
             var tr_x = Math.round( i * _step_x * 220 / _max_km );
             node.setAttribute( 'transform', 'translate(' + tr_x + ', 0)');
             node.querySelector('text').textContent = i * _step_x;
         }

         _track.querySelector('.lfh-gpx-name').textContent = _gpx.get_name();
         _track.querySelector('.lfh-gpx-distance').textContent = (Math.round(_gpx.get_distance()/(100*_coeff))/10).toString().replace('.' , ',')  + ' ' + lfh.DISTANCE_UNIT[_unit].code;
         _track.querySelector('.lfh-gpx-elevation-gain').textContent = Math.round(_gpx.get_elevation_gain()/_coeff_elevation) + ' ' + lfh.HEIGHT_UNIT[_unit_h].code;
         _track.querySelector('.lfh-gpx-elevation-loss').textContent =  Math.round(_gpx.get_elevation_loss()/_coeff_elevation) + ' ' + lfh.HEIGHT_UNIT[_unit_h].code;
         //ajout d'un ecouteur sur le svg
         _track.querySelector('svg').addEventListener('mousemove', function(e){
            var position = this.getBoundingClientRect();
            // var x = e.layerX - 40;
            var x = e.pageX - position.left - 50 ;
            if(x<0){
                 x = 0;
            }
            if(x>220){
                 x = 220;
            }
             
            _track.querySelector('.lfh-move-line').setAttribute('transform','translate(' + x + ',0)');
            
            var km = x * _max_km/220;
            var position = _find_position(km);
            _move_marker.setLatLng(_coords[ position]);
         },false);
         
     }
     function _find_position(km){
         var km = km * _coeff;
         var find = false;  
         var id = 0; 
         var iend = _data.length-1 

         while(!find && ((iend - id) > 1)){
           var im = Math.ceil((id + iend)/2);  
           find = (_data[im] == km);  
           if(_data[im][0] > km){
               iend = im;  
           }else{
               id = im; 
           }
         }
         return(id);  
     }
     draw();
     return {draw:draw};
 }

lfh.initialize();
})();
    