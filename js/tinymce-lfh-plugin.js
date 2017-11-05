

(function() {
    /* Add replacment shortcode */
    
     /* Register the buttons */
    if(!lfh_plugin){
        lfh_plugin ={}
    }
    
     lfh_plugin.replaceMapShortcodes= function( content ) {
         console.log('passe dedans');
         return content.replace( /(\[lfh-map [^\]]*)\]/g, function( match ) {
             return lfh_plugin.html( 'lfh-map', match );
         });
     }
     lfh_plugin.html=function( cls, data ) {
        // data = window.encodeURIComponent( data );
         return '<div class="lfh-hiker lfhicon lfhicon-map ' + cls + '" >'+data+'</div>';
     }
     
     lfh_plugin.restoreShortcodes= function( nodes) {
         
             var result = new Array();
             [].forEach.call(nodes, function(el) {
                 if(el.className.indexOf('lfh-hiker')>=0){
                     result.push(el.innerHTML);
                 }else{
                     //search other
                     var divs = el.getElementsByClassName("lfh-hiker");
                     if(divs.length==0){
                         var div = document.createElement('div');
                         div.append(el);
                         result.push( div.innerHTML);
                     }else{
                         var str = '';
                         [].forEach.call(divs, function(node){
                             str += node.innerHTML;
                         });
                         result.push(str);
                     }
                 }
             });
            
            return result.join(' ');
     }
    
     tinymce.create('tinymce.plugins.Lfh_plugin', {
          init : function(ed, url) {
               /**
               * Inserts shortcode content
               */
              // tinymce.ScriptLoader.load('https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.js');
              // tinymce.ScriptLoader.load( lfh_plugin.url + 'lib/awesome-marker/leaflet.awesome-markers.min.js');

               ed.addButton( 'button_marker', {
                    title : lfh_plugin.langage.addMarker,
                    image : lfh_plugin.url + 'images/icons/markerblack.png',
                    onclick : function() {
                       /* var body = jQuery('<div></div>');
                        jQuery.get( lfh_plugin.ajax+ '?action=add_marker_action',
                             function(html){
                                 console.log(html);
                                 tinymce.activeEditor.windowManager.open({
                                     html:html,
                                     width: window.innerWidth-50,
                                     height: window.innerHeight-50,
                                     resizable:true,
                                     maximizable:true,
                                     scrollbars:true,
                                     
                                     title:lfh_plugin.langage.addMarker});
                             }
                        );*/

                        tinymce.activeEditor.windowManager.open({
                            url: lfh_plugin.ajax+ '?action=add_marker_action&ver=1.3.8',
                            width: window.innerWidth-50,
                            height: window.innerHeight-60,
                            resizable:true,
                            maximizable:true,
                            scrollbars:true,
                            
                            title:lfh_plugin.langage.addMarker,
                         },
                         {
                            custom_param: 1
                         }
                        );
               }
              
               });
              /* ed.on( 'BeforeSetContent', function( event ) {
                   // 'wpview' handles the gallery shortcode when present
                  // if ( ! editor.plugins.wpview || typeof wp === 'undefined' || ! wp.mce ) {
                   console.log('here');
                       event.content = lfh_plugin.replaceMapShortcodes( event.content );
                   //}
               });*/
            /*   ed.on( 'PostProcess', function( event ) {
                 //  console.log(event.node.children);
                   
                   if ( event.get ) {
                    
                      // event.content = lfh_plugin.restoreShortcodes(event.node.children);
                   }
               });*/
           
          },
          createControl : function(n, cm) {
               return null;
          },

    
     });
     /* Start the buttons */
   /*  tinymce.init({
         content_css:lfh_plugin.url + 'assets/css/lfh-post-editor.css',
       });*/

     tinymce.PluginManager.add( 'Lfh_plugin', tinymce.plugins.Lfh_plugin );
})();