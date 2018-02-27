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
 * @const lfh.WIDTH_LIMIT interger the map width limit in pixels to change displaying
 * @const lfh.NUMBER_GPX_FOR_CHECK max of gpx for adding checkbox hide/show
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

lfh.WIDTH_LIMIT = 620;

// height of window under the map (without the title)
lfh.HEIGHT = 170;
// width of part of description under the map (without margin)
lfh.WIDTH = 280;

// max of gpx for adding checkbox hide/show
if(! lfh.NUMBER_GPX_FOR_CHECK) lfh.NUMBER_GPX_FOR_CHECK = 3;

/** Build all on the differents maps*/
lfh.initialize_map = function(i){
    if(typeof lfh.data[i] != 'function'){
        lfh.all[i] = new lfh.Map(i);
    }
}
/** Recursivily initialize the map, if there are a lot of map*/
lfh.all = new Array();
lfh.initialize = function( i ){
    if(i < lfh.data.length){
        lfh.initialize_map( i );
        var next = function(){ lfh.initialize( i+1);};
        setTimeout( next,0);
     }
}

/**
 * @todo clean and optimize this 3 methods, put it out of object lfh.Link
 * count and create div node for description
 * resize image, cut paragraphe if lengthy
 * @param {DomNode} div lfh-element information about marker or track
 * @return {integer} number of div in description and modify the node
 */
lfh.count_step = function( dom )
 {
     var _dom_childs = lfh.treatment_description( dom );
     var description = dom.querySelector(".lfh-description");
    
     if( description ){
         description.innerHTML ="";
         [].forEach.call( _dom_childs , function( node) {
            
             description.appendChild(node);
         });
         return _dom_childs.length; // count number of div and add div in description 
     }else{
         return 0;
     }
 }
lfh.treatment_description = function( dom )
{
    // change dom add div 
    //first resize image
    var imgs = dom.querySelectorAll("img");
    [].forEach.call(imgs, function( img ) {
        if( img.parentNode.className.indexOf("wp-caption")>=0)
        {
            img.style.maxWidth = (lfh.WIDTH -10) + "px";
            img.style.maxHeight = (lfh.HEIGHT - 60) +"px";
            img.setAttribute("srcset","");
            img.parentNode.style.maxWidth = lfh.WIDTH + "px";
            img.parentNode.style.maxHeight = lfh.HEIGHT + "px";

            img.parentNode.style.width = lfh.WIDTH + "px";
            img.parentNode.style.height = lfh.HEIGHT + "px";
        }
        if( img.parentNode.tagName.toLowerCase() === "p"){
            img.parentNode.parentNode.insertBefore(img, img.parentNode)
        }
    });
   // second cut with good size
    var description = dom.querySelector(".lfh-description");
    if( description === null){
        return [];
    }
   return lfh.treatment_node( description);
}

/** function recursive, for all child nodes cut it and organize
 * @parameter {DOMNode} description
 * @return array of DOMNOde
 */
lfh.treatment_node = function( description ){
   var childs = description.childNodes;
   var new_childs = new Array();
   var col = ("scelerisque massa pretium sed. Vivamus est ").length;
   var line = 13;
   var div = document.createElement('div');
   var row = 0;
   [].forEach.call( childs , function( node) {
       switch( node.nodeType){
       case Node.ELEMENT_NODE:
           switch( node.tagName.toLowerCase() ){
           case "h1":
           case "h2":
           case "h3":
           case "h4":
           case "h5":
               var h = document.createElement("h5");
               h.innerHTML = node.textContent;
               if(row + 3 > line){
                   new_childs.push(div);
                   div = document.createElement("div");
                   row = 0; 
               }
               div.appendChild(h);
               row += 2;
               break;
           case "a":
           case "span":
               if(node.textContent.trim().length > 0){
                   var h = (node.textContent.trim().length )/col;
                   if(row + lng > line){
                       new_childs.push(div);
                       div = document.createElement("div");
                       row = 0;
                   }
                   div.appendChild( node.cloneNode(true) );
                   row += lng;
               }
               break;
           case "p":
               
               if(node.textContent.trim().length > 0){
                   var lng = 4 +(node.textContent.trim().length )/col;
                   if(row + lng > line){
                       new_childs.push(div);
                       div = document.createElement("div");
                       row = 0;
                   }
                   div.appendChild( node.cloneNode(true) );
                   row += lng;
               }
               break;
           case "img":
               if(row > 0){
                   new_childs.push(div);
                   div = document.createElement("div");
                   row = 0;
               }
               var div2 = document.createElement("div");
               div2.appendChild(node.cloneNode(true));
               new_childs.push(div2);
               break;
           case "ul":
           case "ol":
               //count li
               var lng = 0;
               var lis = node.querySelectorAll("li");
               [].forEach.call( lis , function( li) {
                   lng += 1+li.textContent.trim().length/col;
               });
               if(row + lng > line && row>0){
                   new_childs.push(div);
                   div = document.createElement("div");
                   row = 0;
               }
               div.appendChild(node.cloneNode(true));
               row += lng;
               break;
           case "div":
              //if(node.className.indexOf('wp-caption')>=0 ){
                   if(row > 0){
                       new_childs.push(div);
                   }
                   var div2 = node.cloneNode(true);
                   new_childs.push(div2);
                   div = document.createElement("div");
                   row = 0;
              // }else{
               break;
           case "br":
               break;
          
           }
           break;
       case Node.TEXT_NODE:
           if(node.nodeValue.trim().length > 0){
               
               var lng = (node.nodeValue.trim().length )/col;
               
               if(row + lng > line){
                   new_childs.push(div);
                   div = document.createElement("div");
                   row = 0;
               }
               div.appendChild(node.cloneNode( true));
               row += lng;
           }
           break;
       }
      
   });
   if(row>0){
       new_childs.push(div);
   }
  
   return new_childs;
} 


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
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-refresh lfhicon lfhicon-reset');
        var center = this._center;
        var zoom = this._zoom;
        container.onclick = function(){
            map.setView(center, zoom);
        }
        return container;
    }
})
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
    initialize: function(d, selected ){
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
                var id = map._container.id;
                var fade = L.DomUtil.get('lfh-fade');
                var container = L.DomUtil.get(id + '-fadable')
                  if( this.className.indexOf('actived') >= 0 ){
                      
                      // reduce map
                      L.DomUtil.get(id + '-skin').appendChild( container);
                      this.className = this.className.replace(' actived','');
                      fade.className = fade.className.replace(' actived','');
                      map._container.style.height= map._container.h0;
                      if(! map.options.mousewheel){
                          map.scrollWheelZoom.disable();
                      }
                  }else{
                      // go to fullscreen
                      fade.appendChild( container);
                      this.className += ' actived';
                      fade.className = fade.className + ' actived';
                      map.scrollWheelZoom.enable();
                      map._container.h0 = map._container.style.height;
                      map._container.style.height = "100%";
                  }
                  
                  lfh.resize_all_map();
            }
        }
        
        if(this._list){
            var div2 =  L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-list');
        }else{
            var div2 =  L.DomUtil.create('div', 'leaflet-bar leaflet-control lfh-control-list lfh-hidden');
        }
        container.appendChild(div2);
        //append list window to the map
        var link = new lfh.Link( map, div2, 'lfh-list-' + this._index , this._selected, null, null,null);
       
        return container;
    },
  
   
  });

/** Resize the DOM div lfh-element for their container
 * @param {DomNode} container the map container*/
lfh.resize_content = function(container){
    // compute the size of the description fonction of the parent
    var node = document.querySelector('#'+ container.id + '-data');
    var height = node.offsetHeight ;
    if(container.parentNode.parentNode.className.indexOf('lfh-min')<0){
        height -= 70;
    }else{
        height = 220;
        //delete all section hidden
        var nodes = node.querySelectorAll('.lfh-section.lfh-hidden');
        [].forEach.call(nodes, function(div) {
                var classname = div.className;
                if(classname.indexOf('disabled')>=0){
                    return;
                }
                if(classname.indexOf('lfh-hidden')>=0){
                     div.className = classname.replaceAll(' lfh-hidden', '');
                }
            
          });
        return;
    }
    var elements = node.getElementsByClassName('lfh-element');//.forEach(funtion(e){
    //var elements = node.querySelectorAll('div:not(.lfh-min) .lfh-element');
  
    for(var i=0; i<elements.length;i++){
        elements.item(i).style.maxHeight = (height) +'px';
        elements.item(i).querySelector('.lfh-element-content').style.maxHeight = (height-40)+'px';
    }
}
lfh.map_resize = function(map){
    var width = map.getContainer().offsetWidth;
    var global_container = map.getContainer().parentNode.parentNode;
    if(width <= lfh.WIDTH_LIMIT ){
        
        global_container.className += ' lfh-min';
        _large = false;
    }else if( width > lfh.WIDTH_LIMIT ){
      
        var classname = global_container.className;
        global_container.className = classname.replaceAll(' lfh-min', '');
        _large = true;
    }
    
    if( map.getContainer().parentNode.parentNode.id == "lfh-fade"){
        if(_large){
            map.getContainer().style.height = "100%";
        }else{
            var nav = document.querySelector(".lfh-nav");
            map.getContainer().style.height = (global_container.offsetHeight - nav.offsetHeight+2) +"px";
        }
    }
    map.invalidateSize();
    return _large;
}
lfh.resize_all_map = function(){
    // do it for all maps on dom
    [].forEach.call(lfh.all , function( mapi ) {
     // resize according to container width
        lfh.map_resize( mapi );
      
    });
}
L.DomEvent.addListener( window, 'resize', function(e){
   lfh.resize_all_map();
});

lfh.toggle_next = function( node, delta, map_id){
    if( !node ){
        return;
    }
    var i = node.step;
    var next = i + delta;
    
    node.step = next;
    node.className = node.className.replace("step"+ i, "step" + next);

    if( node.step_max <= node.step +1 ){
        // hide next button
        document.querySelector('#'+ map_id + "-nav .lfh-next").style.display = "none";
    }else{
        // show next button
        document.querySelector('#'+ map_id + "-nav .lfh-next").style.display = "block";
    }
}

/** the layer selected, whose description is displayed
 * @constructor
 * @param {string} map_id  id of map container (lfh-1, lfh-2...)
 * @param {L.Map} map
 * @param [L.Marker} marker, the move marker on map
 * @return 
 */

lfh.Selected = function( map_id, map, marker){
        this.map_id = map_id;
        var _move_marker = marker; // move marker on map used for gpx layer
        var map = map; 
        this.id = null; 
        this.layer=null; // the layer
        this.dom = null; // the node where is layer descritption
        // title displayed in lfh-nav by default (window under map)
        this.title = document.querySelector("#"+ map_id + "-data div.lfh-nav .lfh-trackname").textContent;
        
        this.close = function(bool){
            // hide navigation button next
            document.querySelector('#'+ this.map_id + "-nav .lfh-next").style.display = "none";
            if(this.id != null ){
                //close fenetre
                var classname = this.dom.className;
                this.dom.className = classname + ' lfh-hidden';
                if( this.layer instanceof L.GPX){
                    // path with its original color
                    var options = this.layer.get_options();
                    this.layer.setStyle({color:options.realColor});
                    map.removeLayer(_move_marker);
                }
            }
            //display navigation for list
            var list = document.querySelector("#"+ map_id + "-fadable .lfh-list");
            lfh.toggle_next( list, 0, this.map_id);
        }
        this.set= function(obj){
            for(var key in obj){
                this[key] = obj[key];
            }
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
        var _OPEN_PROFILE = false;
        var _large = true; // "big screen"
        var _center = [0,0];//[48.866667,2.333333];//default value Paris if not in data
        var _zoom = 13;                    // default value if not in data
        var _zoom_limit = lfh.ZOOM_LIMIT;  // zoom from which the markers are visible
        var _auto_center = true;           // compute center and zoom from the elements added on map
        var _list = false;                 // see button list
        //remarquables layers
        var _gpx = new Array();            // layers from file gpx
        var _markers = new Array();
        var _move_marker = null;           // marker which move on polyline according to profile
        var _layer_zoom = null;            // layer of elements which are displayed according to zoom
        var _latlngbounds = new Array();   // markers used for compute bounds of map if _auto_center
        
        var _selected_element = null; 
       
        function _initialize( i ){
            _data = lfh.data[i];
            var d = lfh.data[i].map;
            _OPEN_PROFILE = d.open;
            d.i = i;
            _auto_center = d.autocenter;
            _list = d.list;
            _center = [d.lat, d.lng];
            _zoom = Math.min(d.zoom,lfh.tiles[d.tile].max_zoom);
            map = L.map(_map_id);
            if( !_auto_center ){
                map.setView( _center, _zoom);
            }
            _set_tile(d.tile);
       
            map.options.mousewheel = d.mousewheel;
            if(!d.mousewheel){
                map.scrollWheelZoom.disable();
            }
           // map.touchZoom.disable();
            // Add layers
            _add_move_marker(_center);
            //Create the selected element after the move marker
            _selected_element = new lfh.Selected(_map_id, map, _move_marker);
            _add_markers( );
            _add_gpx_polylines( );
            
            //Add event listener
            _add_map_event();
            _add_loaded_listener(d.reset);
            _add_nav_event();
           
            // Add control button
            _add_controls(d);
            lfh.map_resize( map);
            
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
                 var _large = lfh.map_resize( this );
                lfh.resize_content(this.getContainer());
                // close window if little map and list is displayes
                if(!_large &&
                    _selected_element.id &&
                    _selected_element.id.indexOf('lfh-list')>= 0 ){
                       
                        var button = _selected_element.layer;
                        var event = document.createEvent('MouseEvents');
                        event.initEvent("click", true, true);
                        // Dispatch/Trigger/Fire the event
                        button.dispatchEvent(event);
                }
              //display navigation for list
                if( _selected_element.dom == null && !_large){
                    var list = document.querySelector("#"+ _map_id + "-fadable .lfh-list");
                    lfh.toggle_next( list, 0,_map_id);
                }
            });
        }
        // add event on button in lfh-nav : close, next and back
        function _add_nav_event()
        {
            // Close button window for min screen
            var node = document.querySelector('#'+ _map_id + "-nav .lfh-close");
            L.DomEvent.addListener( node , 'click', function(e){
                _selected_element.layer.fire('click');
            });
            
            var back = document.querySelector('#'+ _map_id + "-nav .lfh-back");
            L.DomEvent.addListener( back , 'click', function(e){
                if(_selected_element.dom){
                    lfh.toggle_next(_selected_element.dom, -1, _map_id);
                }else{
                    var list = map.getContainer().parentNode.querySelector('.lfh-list');
                    lfh.toggle_next( list, -1, _map_id);
                }
            });
            var next = document.querySelector('#'+ _map_id + "-nav .lfh-next");
            L.DomEvent.addListener( next , 'click', function(e){
                if(_selected_element.dom){
                    lfh.toggle_next(_selected_element.dom, 1, _map_id);
                }else{
                    var list = map.getContainer().parentNode.querySelector('.lfh-list');
                    lfh.toggle_next( list, 1, _map_id);
                }
            });
        }
        
        function _add_move_marker(latlng){
            _move_marker = L.marker(latlng ,{icon: lfh.ICON_MOVE});
        }
        function _add_marker(i){
            var info = _data.markers[i];
            
            var marker_id = 'marker-' + _index +'-' +i;
           
            _markers[i] = L.marker(
                    [info.lat, info.lng],
                    {
                        elem_id: marker_id,
                        icon:  L.AwesomeMarkers.icon({
                            icon: info.icon,
                            prefix: 'lfhicon',
                            markerColor: info.color
                        }),
                        title: info.title.stripslashes(),
                        visibility: 'zoom'
                    });
            info.popup = info.popup + "";
            
            if(info.popup.length>0){
                _markers[i].bindPopup(info.popup.stripslashes());
            }
            if(info.visibility == 'zoom'){
                _layer_zoom.addLayer( _markers[i] );
            }else{
                _markers[i].addTo(map);
            }
            _latlngbounds.push([info.lat, info.lng]);
            
            var link = new lfh.Link( map, _markers[i], marker_id, _selected_element, null);
            return link;
        }
 
        function _add_marker_to_node(marker, container){
            var div = document.createElement("div");
            div.className = 'lfh-button lfhicon';
            var node = document.createElement("span");
           // node.setAttribute("type", "button");
            node.textContent = "\ue80f  "+ marker.options.title;
            div.appendChild( node );
            container.appendChild(div);
            
            L.DomEvent.addListener( node , 'click', function(e){
                marker.fire('click');
                e.stopPropagation();
            });
        }
        
        function _add_gpx_to_node( gpx, container, length )
        {
            var div = document.createElement("div");
           div.className = "lfh-button lfhicon";
       
            var node = document.createElement("span");
            if( length > lfh.NUMBER_GPX_FOR_CHECK){
                node.className = "lfh-short-button"
            }
            node.textContent = "\ue80e  " + document.querySelector('#'+gpx.options.elem_id + ' span.lfh-trackname').textContent;
            div.appendChild( node );
            if( length > lfh.NUMBER_GPX_FOR_CHECK ){
                
                var checkbox = document.createElement("input");
                checkbox.setAttribute("type", "checkbox");
                checkbox.checked = true;
                L.DomEvent.addListener( checkbox, 'change', function(e){
                    var markers = gpx.get_markers();
                    if( this.checked){
                        gpx.addTo(map);
                    }else{
                        map.removeLayer(gpx);
                    }
                    e.stopPropagation();
                })
                div.appendChild( checkbox);
               
            }
            container.insertBefore( div, container.firstChild);
            //appendChild(node);
            
            L.DomEvent.addListener( node , 'click', function(e){
                gpx.fire('click');
                e.stopPropagation();
            });
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
               lfh.resize_content(map.getContainer());
               _auto_center = true;
               
               //wait all is loaded
               setTimeout(_create_buttons, 0);
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
        function _create_buttons(){
            var count = 0;
            var count_div = 0;
            var list = document.querySelector('#lfh-list-' + _index );
            var nav = list.querySelector('div.lfh-description');
            var div = null;
           /* var div = document.createElement("div");
            nav.appendChild(div);
            count_div++;
            div.className = "lfh-content";*/
            //begin by gpx
            [].forEach.call(_gpx, function( one_gpx){
                if( count%3 == 0){
                    div = document.createElement("div");
                    div.className = "lfh-content";
                    nav.appendChild(div);
                    count_div++;
                }
                _add_gpx_to_node( one_gpx, div, _gpx.length );
                count++;
            });
          
            [].forEach.call(_markers, function( marker){
                if( count%3 == 0){
                    div = document.createElement("div");
                    div.className = "lfh-content";
                    nav.appendChild(div);
                    count_div++;
                }
                _add_marker_to_node( marker, div );
                count++;
               
            })
            
            list.step = 0;
            list.step_max = count_div;
            if(list.step_max == 1){
                list.className = list.className + " lfh-small-content";
            }
            lfh.toggle_next( list, 0,_map_id);
            if( _gpx.length > 0 && _OPEN_PROFILE){
                _gpx[1].fire('click');
            }
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
       
       
        if(_dom != null){
            // add dom node to map
            //insert before nav and after list
            _dom.step = 0;
            if(_dom.className.indexOf('lfh-list')<0){
                var data = document.querySelector("#"+_map.getContainer().id + "-data");
                var last_child = data.querySelector(".lfh-nav");
                data.insertBefore(_dom,last_child);
                // count length of description
                _dom.step_max = lfh.count_step( _dom );
            }else{
                _dom.step_max = 0;
            }
           
            
           
            if( _layer instanceof L.GPX){
                _dom.step_max += 2;
            }
            if( _dom.step_max == 1 && _dom.className.indexOf('lfh-list')<0){
                _dom.className = _dom.className + " lfh-small-content";
            }
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
   
     
  
     function _toggle( bool){
        _selected_element.close(bool);
        
        if(_selected_element.id == null || _selected_element.id != _id){
                _dom.className = _dom.className.replaceAll(' lfh-hidden', '');
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
                lfh.toggle_next(_selected_element.dom, 0, _map.getContainer().id );
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
            // add link to gpx file, if button download
            if( _selected_element.dom.querySelector(".lfh-gpx-file")){
                var link_gpx = _selected_element.dom.querySelector(".lfh-gpx-file").cloneNode(true);
                nav.querySelector(".lfh-title").appendChild( link_gpx );
            }
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
               if(classname.indexOf('lfh-hidden')>=0){
                    parent.className = classname.replaceAll(' lfh-hidden', '');
               }else{
                    parent.className += ' lfh-hidden';
               }
               e.stopPropagation();
             });
         });
       
       
       L.DomEvent.addListener( _dom, 'mousemove', function(e){
           e.stopPropagation();
       });
       L.DomEvent.addListener( _dom, 'mousewheel', function(e){
           e.stopPropagation();
          // e.preventDefault();
       });
       // close button
       var nodeClose = _dom.querySelector('.lfh-close');
      
    
      if( _layer instanceof L.Layer){
          _layer.on('click', function(e){
              _toggle(false );
          });
          L.DomEvent.addListener(nodeClose ,'click', function(e){
              _layer.fire('click');
          });
      }else{
        
          L.DomEvent.addListener( _layer, 'click', function(e){
              _toggle(false);
          });
          L.DomEvent.addListener(nodeClose ,'click', function(e){
              _toggle(true);
          });
      }
      
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
     //if(_data[0][1]==null){
      //   return null;
     // }
     if( _gpx.get_elevation_max()!= 0){
         
         var _has_elevation = true;
         var _max = _gpx.get_elevation_max() / _coeff_elevation;
         var _min = _gpx.get_elevation_min() / _coeff_elevation;
         var i0 = _data.length-1;
         while( i0> 0 && _data[i0][1] === null){
             i0--;
         }
         var _max_km = _data[ i0 ][0] / _coeff;
         var _step_h =  lfh.Util.step_round((_max - _min)/3.5);
         if( _step_h < 10 / _coeff_elevation ){
            
             _step_h = _coeff_elevation==1? 10 : 25 ;
             if(_min -5 < 0){
                 _max = _coeff_elevation==1? 40 : 100 ;
             }else{
                 _middle = lfh.Util.step_round((_max + _min)/2);
                 _max = _middle + 30;
             }
         }
        var _max_h = Math.ceil( _max/_step_h)*(_step_h);
        var _min_h = _max_h - 5 * _step_h;

         var _step_x = lfh.Util.step_round((_max_km)/4);
     }else{
         var _has_elevation = false;
     }
     function _x(km){
         return km * 220 / (_max_km * _coeff);
     }
     function _h(h){
         return (200 - (h/_coeff_elevation - _min_h)*40/_step_h);
     }
     function _compute(){
         var d= 'M ';
         var add = parseInt(_data.length /150)+1;
         var ln = _data.length;
         //find first point with elevation
         var i0 = 0;
         while( _data[i0][1] === null){
             i0++;
         }

         d += _x(_data[i0][0]) + ','+ _h(_data[i0][1]) + ' L ';
         for(var i=i0; i < ln -add ; i = i + add){
           
             var x = 0;
             var h = 0;
             for(var j=0;  j <add && i+j< ln ; j++){
                 var lg = 0;
                 if( _data[ i +j ][1] != null){
                     x += _x(_data[i + j][0]);
                     h += _h(_data[i + j][1]);
                     lg++;
                 }
             }
             if(lg != 0){
                 d += Math.round( x/add) + ','+ Math.round(h/add) + ' L ';
             }
            
         }
         // last point
         if( _data[ln-1][1] != null)
         d += _x(_data[ln-1][0]) + ','+ _h(_data[ln-1][1]);
         return d;
     }
     
     function draw(){
         if( _has_elevation){
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
                 if( tr_x > 220){
                     node.setAttribute('stroke-opacity', 0);
                     node.querySelector('text').textContent = "";
                 }else{
                     node.querySelector('text').textContent = i * _step_x;
                 }
             }
    
             //if()
             _track.querySelector('.lfh-gpx-elevation-gain').textContent = Math.round(_gpx.get_elevation_gain()/_coeff_elevation) + ' ' + lfh.HEIGHT_UNIT[_unit_h].code;
             _track.querySelector('.lfh-gpx-elevation-loss').textContent =  Math.round(_gpx.get_elevation_loss()/_coeff_elevation) + ' ' + lfh.HEIGHT_UNIT[_unit_h].code;
             
             //ajout d'un ecouteur sur le 
             L.DomEvent.addListener( _track.querySelector('svg') ,'click mousemove', function(e){
                _on_move(e );
             },false);
             _track.querySelector('svg').addEventListener('touchmove', function(e){
                 _on_move( e.touches[0]);
             })
         }else{
             // No data elevation : remove svg
             _track.querySelector('svg').parentNode.removeChild( _track.querySelector('svg'));
             var _duration = _gpx.get_total_time();
             if( _duration )
             {
                 _track.querySelector('.lfh-gpx-duration').textContent = _gpx.get_duration_string(_duration);
             }
         }
         _track.querySelector('.lfh-gpx-name').textContent = _gpx.get_name();
         _track.querySelector('.lfh-gpx-distance').textContent = (Math.round(_gpx.get_distance()/(100*_coeff))/10).toString().replace('.' , ',')  + ' ' + lfh.DISTANCE_UNIT[_unit].code;
        
     }
     function _on_move( e){
         
         var svg =  _track.querySelector('svg');
         var position = svg.getBoundingClientRect();
         // compute if svg is scaled
         var scale = 290 / position.width;
         var x = e.pageX - position.left - window.pageXOffset;
         x = x*scale - 50;
         if(x<0){
              x = 0;
         }
         if(x>220){
              x = 220;
         }
         x = parseInt(x );
         _track.querySelector('.lfh-move-line').setAttribute('transform','translate(' + x + ',0)');
         
         var km = x * _max_km/220;
         var position = _find_position(km);
         _move_marker.setLatLng(_coords[ position]);
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
 /**
  * treatment of dom description
  * cut and reorganize node for well display in length
  * @return array of node well form
  */
 function _count_div ( node ){
    return childs; 
 }
 lfh.initialize(1);
})();
    