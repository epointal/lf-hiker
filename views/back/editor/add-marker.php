<?php
/**
 * @author epointal
 * Complete page
 * Tools for add place marker, no dependance with wordpress, it can be used like this
 * This page is called in post editor for create the shortcode, only need a directory with markers
 */


//for dev (http://rancs.com/blog/wp-content/plugins/lf-hiker/views/back/editor/add-marker.phtml)
if(!isset($plugin_url)){
    $position = strpos($_SERVER["SERVER_PROTOCOL"],"/");
    $uri = explode('/', $_SERVER['REQUEST_URI']);
    array_pop($uri);//add-marker
    array_pop($uri);//editor
    array_pop($uri);//back
    array_pop($uri);//views
    $plugin_url = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0,$position).'://'.$_SERVER['SERVER_NAME'].implode('/', $uri).'/';
   
    $dir = __DIR__;

    $dirs = explode(DIRECTORY_SEPARATOR, __DIR__);
    array_pop($dirs);
    array_pop($dirs);
    array_pop($dirs);
    
    $plugin_dir = implode( DIRECTORY_SEPARATOR, $dirs);
   
    require_once  $plugin_dir.'/Tools/Map.php';
   /* require_once  $plugin_dir.'/Tools/Form.php';
    require_once  $plugin_dir.'/Tools/Form/Checkbox.php';
    require_once  $plugin_dir.'/Tools/Form/Number.php';*/
    $colors = Lfh_Model_Map::$colors_marker;
    $icons  = Lfh_Model_Map::$icons_marker;
    $class = Lfh_Model_Map::$class_map;
    $default = Lfh_Model_Map::$default;
    $tiles = Lfh_Model_Map::$tiles;
    $mapquest_key = "7zIDDCdk1pXCTgR5cQxmZCeDcaLuxX34";
}
if(!function_exists('_e')){
        function _e($text, $domaine){
            return stripslashes($text);
        }
}
//-- end for dev

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
     <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.css" />
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="<?=$plugin_url?>lib/awesome-marker/leaflet.awesome-markers.css">        
    <style>
    html, body{
       width:100%;
       height:100%;
       font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
       font-size:16px;
    }
    #banner{
        position:fixed;
        z-index:1001;
        bottom:0px;
        right:20px;
        height:auto;
        text-align:right;
        display:inline-block;
    }
    input[type="button"],
    input[type="reset"]{
        margin: 0px 7px 3px 0;
        padding: 6px 18px;
        height: 50px;
        vertical-align:middle;
        max-width:150px;
        font-size: 14px;
        line-height: 1.4285714;
        white-space: normal;
        background: #0085ba;
        border-width:1px;
        border-style: solid;
        border-radius:3px;
        border-color: #0073aa #006799 #006799;
        color: #fff;
        text-decoration: none;
        text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799;
        vertical-align: top;
        display: inline-block;
        cursor: pointer;
        box-sizing: border-box;
        text-align:center;
         box-shadow: 0 1px 5px rgba(0,0,0,0.65);
    }
    input[type="button"]:disabled{
        background-color: lightgrey;
    }
    input[type="button"]:disabled:hover{
        background-color: lightgrey;
    }
    input[type="button"]:hover{
        background-color: #0089bc;
    }
    form input[type="button"],
    form input[type="reset"]{
        margin: 7px 7px 0px 0;
        padding: 3px 9px;
        height: auto;
        font-size: 12px;
        line-height: 1.2;
    }
    form input[type="number"]{
        width:50px;
        direction: rtl;
        padding-right: 3px;
    }
    .lfh-form-edit{
       padding:0px;
       width:350px;
       position:fixed;

       z-index:1000;
       background-color:white;
       height:auto;
       -webkit-user-drag: element;
       box-shadow: 0 1px 5px rgba(0,0,0,0.65);
       border-radius: 4px;
    }
    #window-edit-marker{
       top:5px;
       right:5px;
    }
    #window-edit-map{
        top:5px;
        left:60px;
    }
    .lfh-form-edit .header{
        background-color: #23282d;
        color:white;
        padding-left:10px;
        margin:0px;
        cursor: move;
    }
    .lfh-form-edit .header h3{
        margin:;
        display:inline;
        width:180px;
        vertical-align:middle;
    }
    .header div.fa{
        float:right;
        display:inline-block;
        vertical-align:top;
        cursor:pointer;
        padding : 3px;
    }
    form{
        padding-top:10px;
        overflow-y:auto;
        height:auto;
        max-height:395px;
        padding-bottom:10px;
    }
    label{
        text-align:right;
        width:150px;
        padding-right:5px;
        color: #124964;
    }
    input,
    select,
    textarea{
        font-size: 12px;
        border-width: 1px;
        border-style: solid;
        border-color: #ddd;
    }
    input[type=text],
    textarea{
        width: 165px;
        max-width:165px;
        line-height:1.4;
    }
    label, input{
        display:inline-block;
        vertical-align:top;
    }
    input:focus,
    select:focus,
    textarea:focus{
        border-color: #5b9dd9;
    }
    textarea{
        height:33px;
    }
    #selected-icon,
    #selected-color{
        cursor:pointer;
    }
    #selected-icon{
        margin-left:10px;
    }
    .to-extend{
        margin-top: -5px;
    }
    .to-extend > div{
       width:165px; 
       margin:2px;
       text-align:center;
       background-color:#f3f3f3;
       overflow:hidden;
    }
    #center-map,
    #selected-color,
    #selected-icon,
    .to-extend div{
        display:inline-block;
        position:static;
        -webkit-box-shadow: inset 0 0 0 1px rgba(0,0,0,.1);
        box-shadow: inset 0 0 0 1px rgba(0,0,0,.1);
    }
    #center-map{
        background-color:#f3f3f3;
        margin:0 6px 6px 6px;
        padding:0 5px 5px 5px;
        text-align: right;
       
    }
    .to-extend label{
        cursor:pointer;
    }
    .to-extend label:hover{
        font-size:18px;
    }
    
    .to-extend > div > div{
        margin:0;
        padding:0px;
        cursor:pointer;
    }    
    .to-extend > div > div:hover{
        background-color: #fff;
        padding:1px;
    }
    #icon-marker div{
        min-width:19px;
        text-align:center;
    }
    .to-extend > div > div.selected{
        background-color:#b5d0d0;
        -webkit-box-shadow: inset 0 0 0 1px rgba(91,157,217,.9);
        box-shadow: inset 0 0 0 1px rgba(91,157,217,.9);
    }
    *::-moz-placeholder {
        color: #72777c;
        opacity: 1;
    }
    .leaflet-control .fa{
        width:22px;
        font-size:18px;
        text-align:center;
        margin:0;
        padding:2px;
        border-radius:2px;
     
        cursor:pointer;
    }
    .leaflet-control .active{
        background-color:rgba(240, 214, 215, 1);
        -webkit-box-shadow: inset 0 0 0 2px rgba(231, 107, 111, 0.6);
        box-shadow: inset 0 0 0 2px rgba(231, 107, 111, 0.6);
    }
    #map-position, #text-pan{
        display:block;
        text-align:left;
        padding-left:9px;
        font-size:12px;
    }
    #text-pan{
        padding-left:3px;
        font-size:14px;
        color:#d12a2f;
    }
    </style>
</head>

<body>
 <!--  <div id="debug"  style="position:absolute;top:20px;left:400px;display:block;z-index:90000;"></div>-->
<!-- control for marker on map -->
<div id="lfh-control" class="leaflet-bar leaflet-control">
  <a  id="lfh-edit-map" class="leaflet-buttons-control-button marker-control" title="<?=_e('Edit map', 'lfh')?>">
        <div class="fa fa-map"></div>
    </a>
    <a id="lfh-add-marker" class="leaflet-buttons-control-button marker-control " title="<?=_e('Add marker', 'lfh')?>">
        <div class="fa fa-map-marker"></div>
    </a>
    <a id="lfh-edit-marker" class="leaflet-buttons-control-button marker-control" title="<?=_e('Edit marker', 'lfh')?>">
        <div class="fa fa-edit"></div>
    </a>
    <a  id="lfh-delete-marker" class="leaflet-buttons-control-button marker-control" title="<?=_e('Delete marker', 'lfh')?>">
        <div class="fa fa-trash"></div>
    </a>
   
    
</div>
<!-- banner buttons bottom right -->
     <div id="banner" >
     	<input name="lfh-cancel" type="button" value="<?=_e('Cancel', 'lfh')?>" />
     	<input name="lfh-insert-markers" type="button" value="<?=_e('Insert shortcode Markers', 'lfh')?>" />
     	<input name="lfh-insert" type="button" value="<?=_e('Insert Markers & Map', 'lfh')?>" />
     </div>
<!-- window for edit map -->
  <div id="window-edit-map"  class="lfh-form-edit" style="display:none;">
   <div class="header" >
        <h3><?=_e('Edit map', 'lfh')?></h3>
        <div id="window-close" class="fa fa-close"></div>
    </div>
    <form>
        <div>
            <label for="autocenter"><?=_e('Position auto', 'lfh')?></label>
            <input type="checkbox" name="autocenter" tabindex="2"  checked />
        </div>
        <div id="center-map"  style="display:none;">
        	<div id="text-pan"><?=_e('Pan and zoom until find the position, and when ready save the position', 'lfh')?></div>
        	<div id="map-position" >&nbsp;</div>
        	<input type="button" name="save-center" value="<?=_e('Save', 'lfh')?>"  />
        </div>
        <div>
            <label for="map-width"><?=_e('Width', 'lfh')?></label>
            <input type="number" name="map-width" min="10" max="100" step="10" tabindex="3" value="100"/> %
        </div>
        <div>
            <label for="map-height"><?=_e('Height', 'lfh')?></label>
            <input type="number" name="map-height" min="100" max="800" step="50" tabindex="4" value="500"/> px
        </div>
         <div>
            <label for="map-class"><?=_e('Class', 'lfh')?></label>
            <input type="list" list="classname" name="map-class" tabindex="5" placeholder="...">
            <datalist id="classname">
            <?php  for($i=0; $i<count($class);$i++): ?>
            	<option value="<?=$class[$i]?>" />
            <?php endfor;?>
            </datalist>
        </div>
         <div>
            <label for="fullscreen"><?=_e('Fullscreen Button', 'lfh')?></label>
   			 <input type="checkbox" name="fullscreen" tabindex="6" checked/>
        </div>
        <div>
            <label for="reset"><?=_e('Reset Button', 'lfh')?></label>
   			 <input type="checkbox" name="reset" tabindex="7" checked/>
        </div>
         <div>
            <label for="list"><?=_e('List Button', 'lfh')?></label>
   			 <input type="checkbox" name="list" tabindex="8" checked/>
        </div>
         <div>
            <label for="mousewheel"><?=_e('Zoom on mouse wheel', 'lfh')?></label>
   			 <input type="checkbox" name="mousewheel" tabindex="9"/>
        </div>
         <div>
            <label for="map-tile"><?=_e('Tile', 'lfh')?></label>
            <select name="map-tile" tabindex="10" >
            <?php foreach($tiles as $key=>$value):?>
            <?php if(($key == 'mapquest'&& !is_null($mapquest_key)) || $key!='mapquest'):?>
            	<option value="<?=$key?>"><?=strtoupper($key)?></option>
            <?php endif;?>
           <?php endforeach;?>
            </select>
        </div>
        	<label for="reset-map"></label>
        	<input type ="reset"  name="reset-map" value="<?=_e('Reset' , 'lfh')?>" />
        	
        <div>
        </div>
    </form>
  </div>
 
<!-- window for editing marker -->
    <div id="window-edit-marker"  class="lfh-form-edit" style="display:none;">
    <div class="header" >
        <h3><?=_e('Edit marker', 'lfh')?></h3>
        <div id="window-close" class="fa fa-close"></div>
    </div>
    <form>
        <div>
            <label for="title"><?=_e('Title', 'lfh')?></label>
            <input type="text" name="title" tabindex="1" value=""/>
        </div>
         <div>
            <label for="popup"><?=_e('Popup', 'lfh')?></label>
            <textarea name="popup" tabindex="2" rows="2" value=""></textarea>
        </div>
        <div>
            <label for="description"><?=_e('Description', 'lfh')?></label>
            <input type="checkbox" name="description"  tabindex="3"/>
        </div>
        <div>
            <label for="visibility"><?=_e('Visibility', 'lfh')?></label>
            <select  name="visibility" tabindex="4">
            <option value="always"><?=_e('Always', 'lfh')?></option>
            <option value="zoom" selected><?=_e('According to zoom', 'lfh')?></option>
            </select>
        </div>
        <div>
            <label for="icon-color"><?=_e('Icon color', 'lfh')?></label>
            <div id="selected-color" class="awesome-marker awesome-marker-icon-red" data-value="red" tabindex="5"></div>
            <input name="icon-color" type="hidden" value="red" />
        </div>
         <div class="to-extend">
             <label>+</label>
             <div id="color-marker" style="display:none;">
             <?php
             for($i=0; $i<count($colors);$i++): ?>
             <?php if($i==0):?>
             <div class="awesome-marker-icon-<?=$colors[$i]?> awesome-marker selected" data-value="<?=$colors[$i]?>"></div>
             <?php else:?>
             <div class="awesome-marker-icon-<?=$colors[$i]?> awesome-marker" data-value="<?=$colors[$i]?>"></div>
             <?php endif;?>
             <?php endfor;?>
             </div>
         </div>
         <div>
            <label for="selected-icon"><?=_e('Inside icon', 'lfh')?></label>
            <div id="selected-icon" class="fa fa-circle" data-value="circle" tabindex="6"></div>
            <input name="selected-icon" type="hidden" value="red" />
        </div>
         <div class="to-extend">
             <label>+</label>
             <div id="icon-marker" style="display:none;padding-bottom:10px">
        <?php for($i=0; $i<count($icons); $i++):?>
        <?php if($i==0):?>
             <div class="fa fa-<?=$icons[$i]?> selected"  data-value="<?=$icons[$i]?>"></div>
        <?php else:?>
            <div class="fa fa-<?=$icons[$i]?>" data-value="<?=$icons[$i]?>"></div>
        <?php endif;?>
        <?php endfor;?>
             </div>
         </div>
    </form>
    </div><!-- end edit marker -->
    

    <!-- the map -->
    <div id="map" style="height:100vh;width:100%;"></div>

<!-- all scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.js"></script>
<script src="<?=$plugin_url?>lib/awesome-marker/leaflet.awesome-markers.min.js"></script>
<?php if(!is_null($mapquest_key)):?>
<script src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=<?=$mapquest_key?>"></script>
<?php endif;?>
<script type="text/javascript">
console.log("before");
(function(){
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
  
var lfh = {
        mode: "lfh-view",
        confirm : "<?=_e('Delete marker' , 'lfh')?> ?",//when load 
        add_description : "<?=_e('Add here your formated description', 'lfh')?>",
        tiles : <?=json_encode($tiles,JSON_UNESCAPED_SLASHES)?>,
        map: null,
        center: [48.67777858405578, 2.166026472914382], //by default center = les ulis France
        zoom: 2,
        current_marker: null, // the selected marker
        default_icon: null,
        move_marker: null, // seen when in mode add-marker
        markers: new Array(), // array of the added markers
        tile: null, // the current L.tileLayer
        record: null, //record center and zoom when user want
        init_map: function(){
            lfh.map = L.map('map').setView(lfh.center, lfh.zoom);
            lfh.set_tile();
            lfh.map.addControl(new LfhControl());
            lfh.default_icon = L.AwesomeMarkers.icon({
                icon: 'circle',
                prefix: 'fa',
                markerColor: 'red'});
            lfh.move_marker = L.marker(lfh.center).addTo(lfh.map);
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
                if(lfh.mode == 'lfh-add-marker'){
                    lfh.add_marker(e);
                }
            });
            
        },
        set_tile: function(){
            var tilename = document.querySelector('select[name="map-tile"]').value;
            var tileinfo = lfh.tiles[ tilename ];
           
            if(lfh.tile != null ){
                lfh.map.removeLayer(lfh.tile);
            }
            if( tilename != 'mapquest'){
                lfh.tile = L.tileLayer(tileinfo.url, {
                        attribution: tileinfo.attribution
                    });
               
            }else{
                lfh.tile =  MQ.mapLayer();
            }
            lfh.tile.addTo(lfh.map);
           // lfh.map.options.minZoom = tileinfo.min_zoom;
           // lfh.map.options.minZoom = tileinfo.max_zoom;
        },
        add_marker: function(e){
            lfh.current_marker = L.marker(
                    e.latlng,
                    {
                        icon: lfh.default_icon,
                        draggable: true,
                        index: lfh.markers.length,
                        title: '',
                        popup: '',
                        description: false,
                        visibility: 'zoom'
                    }).addTo(lfh.map);
                lfh.markers.push(lfh.current_marker);
                lfh.current_marker.on('click', function(e){
                    switch(lfh.mode){
                    	case 'lfh-edit-marker':
                            lfh.current_marker = this;
                            lfh.init_window();
                            break;
                    	case 'lfh-delete-marker':
                        	lfh.current_marker = null;
                        	lfh.delete_marker(this);
                        	break;
                        default:
                    }
                });
                //switch to edit mode
                lfh.set_mode(document.querySelector('#lfh-edit-marker'));
                return lfh.current_marker;
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
            if(lfh.mode != 'lfh-view'){
             	var active = document.querySelector('.marker-control.active');
             	active.className = active.className.replace(' active', '');
            }
            //close window edit
            var nodes = document.querySelectorAll('.lfh-form-edit');
            [].forEach.call(nodes, function(node){
                node.style.display = 'none';
            });
            if(node.id == lfh.mode){
                lfh.mode = 'lfh-view';
            }else{
                //active the mode
                lfh.mode = node.id;
                node.className = node.className + " active";
                //case  add see marker window
                lfh.init_window();
            }
            if(lfh.mode == 'lfh-add-marker'){
                //show move_marker
                lfh.move_marker.setOpacity(1);
            }else{
                //hide move_marker
                lfh.move_marker.setOpacity(0);
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
        },
        //open window with good information
        init_window: function(){
            switch(lfh.mode){
            case 'lfh-edit-map':
                document.querySelector('#window-edit-map').style.display = 'block';
                break;
            case 'lfh-edit-marker':
                if(lfh.current_marker == null ) return;
                //init the window "edit marker" with information of current_marker
                //from current marker to edit window
                var options = lfh.current_marker.options;
                var color = options.icon.options.markerColor;
                var icon = options.icon.options.icon;
                document.querySelector('#window-edit-marker input[name="title"]').value = options.title.stripslashes();
                document.querySelector('#window-edit-marker textarea[name="popup"]').value = options.popup.stripslashes();
                document.querySelector('#window-edit-marker input[name="description"]').checked = options.description;
                document.querySelector('#window-edit-marker select[name="visibility"]').selectedIndex = (options.visibility == 'always')? 0:1;
                var evt = document.createEvent('MouseEvents');
                evt.initEvent("click", true, true);
                document.querySelector('#color-marker div.awesome-marker-icon-'+color).dispatchEvent(evt);
                document.querySelector('#icon-marker div.fa-'+icon).dispatchEvent(evt); 
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
                break;
            case 'window-edit-map':
                var evt = document.createEvent('MouseEvents');
                evt.initEvent("click", true, true);
                document.querySelector('#lfh-edit-map').dispatchEvent(evt);
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
            var label = document.querySelector('#window-edit-marker .to-extend:last-child label');
            var div = label.parentNode.querySelector("div");
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
            if(document.querySelector('input[name="new-map"]').checked){
                shortcode = '[lfh-map]<br />';
            }
            shortcode += '[lfh-prop-map ';
            shortcode += ' autoCenter=' + document.querySelector('input[name="auto-center"]').checked;
            if(!document.querySelector('input[name="auto-center"]').checked){
                shortcode += ' lat=' + lfh.record.center.lat ;
                shortcode += ' lng=' + lfh.record.center.lng ;
                shortcode += ' zoom=' + lfh.record.zoom ;
            }
            shortcode += ' width=' + document.querySelector('input[name="map-width"]').value;
            shortcode += ' height=' + document.querySelector('input[name="map-height"]').value;
            if(document.querySelector('input[name="map-class"]').value != ''){
                shortcode += ' class="' + document.querySelector('input[name="map-class"]').value.replaceAll('-','\\-') +'"';
            }
            shortcode += ' fullscreen=' + document.querySelector('input[name="fullscreen"]').checked;
            shortcode += ' reset=' + document.querySelector('input[name="reset"]').checked;
            shortcode += ' list=' + document.querySelector('input[name="list"]').checked;
            shortcode += ' mousewheel=' + document.querySelector('input[name="mousewheel"]').checked;
            shortcode += ' tile=' + document.querySelector('select[name="map-tile"]').value;
            shortcode += ' ]<br /><br />';
            return shortcode;
        },
        shortcode: function(name){
            var shortcode = '';
            switch(name){
            case 'map':
                	shortcode += lfh.shortcode_map();
            case 'markers':
                for(var i=0; i<lfh.markers.length; i++){
                    var latlng = lfh.markers[i].getLatLng();
                    var options = lfh.markers[i].options;
                    var color = options.icon.options.markerColor;
                    var icon = options.icon.options.icon;
                    shortcode += '[lfh-marker lat='+latlng.lat +' lng=' + latlng.lng;
                    shortcode += ' color=' + color + ' icon=' + icon;
                    shortcode += ' title="' + options.title +'"';
                    shortcode += ' popup="' + options.popup +'"';
                    shortcode += ' visibility=' + options.visibility;
                    shortcode += ' ]' ;
                    if(options.description){
                    	shortcode += '<br />' + lfh.add_description + '<br />';
                    }
                    shortcode += '[/lfh-marker]<br /><br />';
                }
            }
            return shortcode;
        }
}



//handler
var nodes = document.querySelectorAll('.marker-control');
[].forEach.call(nodes, function(button){
    L.DomEvent.addListener(button,'click', function(e){
    	lfh.set_mode(button);
    	e.stopPropagation();
    });
});
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
   var nodes = document.querySelectorAll('.fa-close');
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
   // - for edit map
   //-----------------
   var node = document.querySelector('#window-edit-map input[name="auto-center"]');
   L.DomEvent.addListener(node, 'click', function(e){
       document.querySelector('#center-map').style.display = e.target.checked ? 'none':'block';
       lfh.get_position_info();
   });
   var node = document.querySelector('input[name="save-center"]');
   L.DomEvent.addListener(node, 'click', function(e){
       lfh.get_position_info();
   });
   var node = document.querySelector('#window-edit-map select[name="map-tile"]');
   L.DomEvent.addListener(node, 'change', function(e){
       lfh.set_tile();
   });
   var evt = document.createEvent('MouseEvents');
   evt.initEvent("click", true, true);
   document.querySelector('#window-edit-map input[type="reset"]').dispatchEvent(evt);
   document.querySelector('#window-edit-map form').onreset = function(){
       document.querySelector('#center-map').style.display = 'none';
       setTimeout(function(){
           if(lfh.tiles != null)
               lfh.set_tile();
           }, 500);
   }
   // - for edit marker
	var node = document.querySelector('#window-edit-marker input[name="title"]');
    L.DomEvent.addListener(node,'change', function(e){
        lfh.current_marker.options.title = this.value.addslashes();
    });
    
  
    var node = document.querySelector('#window-edit-marker textarea[name="popup"]');
    L.DomEvent.addListener(node,'change', function(e){
        var content = this.value;
        lfh.current_marker.options.popup = this.value.addslashes();
        lfh.current_marker.bindPopup(content).openPopup();
        
    });
    var node = document.querySelector("#window-edit-marker input[name='description']");
    L.DomEvent.addListener(node,'click', function(e){
        lfh.current_marker.options.description = this.checked;
        
    });
    var node = document.querySelector("#window-edit-marker select");
    L.DomEvent.addListener(node,'change', function(e){
        lfh.current_marker.options.visibility = this.value;
        
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

            lfh.current_marker.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'fa',
                markerColor: color
              }));
            
        });
    });
    var nodes = document.querySelectorAll('#icon-marker div.fa');
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

            lfh.current_marker.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'fa',
                markerColor: color
              }));
            
        });
    });
   
   
    
   
 //buttons insert and cancel
 
  
    var node = document.querySelector('#banner input[name="lfh-cancel"]');
    L.DomEvent.addListener(node, 'click', function(e){
        // only close the window
        if(typeof window.parent != 'undefined'){
            window.parent.tinymce.activeEditor.windowManager.close();
        }
    });
    var node = document.querySelector('#banner input[name="lfh-insert"]');
    L.DomEvent.addListener(node, 'click', function(e){
        //document.querySelector('#debug').innerHTML = lfh.shortcode('map');
        // only close the window
        if(typeof window.parent != 'undefined' && typeof window.parent.tinymce != 'undefined'){
            window.parent.tinymce.activeEditor.selection.setContent(lfh.shortcode('map'));
            window.parent.tinymce.activeEditor.windowManager.close();
        }
    });
    var node = document.querySelector('#banner input[name="lfh-insert-markers"]');
    L.DomEvent.addListener(node, 'click', function(e){
        // only close the window
        if(typeof window.parent != 'undefined' && typeof window.parent.tinymce != 'undefined'){
            window.parent.tinymce.activeEditor.selection.setContent(lfh.shortcode('markers'));
            window.parent.tinymce.activeEditor.windowManager.close();
        }
    });

    window.onload = function() {
        if (window.dialogArguments) { // For IE
            var arguments = window.dialogArguments;
         }
         else { //For FF and Chrome
            var arguments = window.opener;
         } 
      
        if(arguments!= null && typeof arguments['lat'] != 'undefined'){
            lfh.center = [arguments['lat'], arguments['lng']];
            lfh.zoom = arguments['zoom'];
        }
        lfh.init_map();
      };
})();
String.prototype.addslashes = function()
{return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');};

String.prototype.stripslashes = function()
{return this.replace(/\\(.?)/g, function (s, n1){switch (n1){case '\\':return '\\';case '0':return '\u0000';case '':return '';default:return n1;}});};

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

</script>

    </body>
</html>