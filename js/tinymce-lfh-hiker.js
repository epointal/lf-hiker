
(function() {
    /* Add replacment shortcode */
    tinymce.PluginManager.add('lfh_kiker', function( editor ) {

        function replaceMapShortcodes( content ) {
            console.log('passe dedans');
            return content.replace( /\[lfh-map([^\]]*)\]/g, function( match ) {
                return html( 'lfh-map', match );
            });
        }

        function html( cls, data ) {
            data = window.encodeURIComponent( data );
            return '<img src="' + tinymce.Env.transparentSrc + '" class="wp-media mceItem ' + cls + '" ' +
                'data-wp-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" alt="" />';
        }

        function restoreMediaShortcodes( content ) {
            function getAttr( str, name ) {
                name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
                return name ? window.decodeURIComponent( name[1] ) : '';
            }

            return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
                var data = getAttr( image, 'data-wp-media' );

                if ( data ) {
                    return '<p>' + data + '</p>';
                }

                return match;
            });
        }

      function editMedia( node ) {
            var gallery, frame, data;

            if ( node.nodeName !== 'lfh-map' ) {
                return;
            }

            // Check if the `wp.media` API exists.
            if ( typeof wp === 'undefined' || ! wp.media ) {
                return;
            }

            data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-wp-media' ) );

            // Make sure we've selected a gallery node.
            if ( editor.dom.hasClass( node, 'wp-gallery' ) && wp.media.gallery ) {
                gallery = wp.media.gallery;
                frame = gallery.edit( data );

                frame.state('gallery-edit').on( 'update', function( selection ) {
                    var shortcode = gallery.shortcode( selection ).string();
                    editor.dom.setAttrib( node, 'data-wp-media', window.encodeURIComponent( shortcode ) );
                    frame.detach();
                });
            }
        }

        // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
        editor.addCommand( 'Lfh_map', function() {
            editMedia( editor.selection.getNode() );
        });

        editor.on( 'mouseup', function( event ) {
            var dom = editor.dom,
                node = event.target;

            function unselect() {
                dom.removeClass( dom.select( 'img.wp-media-selected' ), 'wp-media-selected' );
            }

            if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-wp-media' ) ) {
                // Don't trigger on right-click
                if ( event.button !== 2 ) {
                    if ( dom.hasClass( node, 'wp-media-selected' ) ) {
                        editMedia( node );
                    } else {
                        unselect();
                        dom.addClass( node, 'wp-media-selected' );
                    }
                }
            } else {
                unselect();
            }
        });

        // Display gallery, audio or video instead of img in the element path
        editor.on( 'ResolveName', function( event ) {
            var dom = editor.dom,
                node = event.target;

            if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-wp-media' ) ) {
                if ( dom.hasClass( node, 'wp-gallery' ) ) {
                    event.name = 'gallery';
                }
            }
        });

        editor.on( 'BeforeSetContent', function( event ) {
            // 'wpview' handles the gallery shortcode when present
           // if ( ! editor.plugins.wpview || typeof wp === 'undefined' || ! wp.mce ) {
            console.log('here');
                event.content = replaceMapShortcodes( event.content );
            //}
        });

        editor.on( 'PostProcess', function( event ) {
            if ( event.get ) {
                event.content = restoreMapShortcodes( event.content );
            }
        });
    });
     /* Register the buttons */
    if(!lfh_plugin){
        lfh_plugin ={}
    }
     lfh_plugin.replaceMapShortcodes= function( content ) {
         console.log('passe dedans');
         return content.replace( /\[lfh-map([^\]]*)\]/g, function( match ) {
             return lfh_plugin.html( 'lfh-map', match );
         });
     }
     lfh_plugin.html=function( cls, data ) {
         data = window.encodeURIComponent( data );
         return '<img id="lfh-hiker" src="' + tinymce.Env.transparentSrc + '" class="lfh-hiker mceItem ' + cls + '" ' +
             'data-lfh-hiker="' + data + '" data-mce-resize="false" data-mce-placeholder="1" alt="'+data+'" title="'+data+'"/>';
     }
     tinymce.create('tinymce.plugins.Lfh_buttons', {
          init : function(ed, url) {
               /**
               * Inserts shortcode content
               */
               ed.addButton( 'button_marker', {
                    title : lfh_plugin.langage.addMarker,
                    image : lfh_plugin.url + 'assets/images/icons/markerblack.png',
                    onclick : function() {
                        tinymce.activeEditor.windowManager.open({
                            url: lfh_plugin.ajax+ '?action=add_marker_action',
                            width: window.innerWidth-50,
                            height: window.innerHeight-50,
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
               ed.on( 'BeforeSetContent', function( event ) {
                   // 'wpview' handles the gallery shortcode when present
                  // if ( ! editor.plugins.wpview || typeof wp === 'undefined' || ! wp.mce ) {
                   console.log('here');
                       event.content = lfh_plugin.replaceMapShortcodes( event.content );
                   //}
               });
               ed.on( 'mouseup', function( event ) {
                   var dom = ed.dom,
                       node = event.target;

                   function unselect() {
                       dom.removeClass( dom.select( 'img.wp-media-selected' ), 'wp-media-selected' );
                   }

                   if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-wp-media' ) ) {
                       // Don't trigger on right-click
                       if ( event.button !== 2 ) {
                           if ( dom.hasClass( node, 'wp-media-selected' ) ) {
                               lfh_plugin.editMap( node );
                           } else {
                               unselect();
                               dom.addClass( node, 'wp-media-selected' );
                           }
                       }
                   } else {
                       unselect();
                   }
               });
           
          },
          createControl : function(n, cm) {
               return null;
          },
         //content_css:lfh_plugin.url + 'assets/css/lfh-post-editor.css',

    
     });
     /* Start the buttons */
   /*  tinymce.init({
         content_css:lfh_plugin.url + 'assets/css/lfh-post-editor.css',
       });*/

     tinymce.PluginManager.add( 'Lf_hiker', tinymce.plugins.Lfh_buttons );
})();