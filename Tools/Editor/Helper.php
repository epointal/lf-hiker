<?php
/**

 * 
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Tools_Editor_Helper{
    private $_unactive = false;
    
    public function __construct($unactive) {
        $this->_unactive = $unactive;
        if (wp_doing_ajax()){
            if(!$this->_unactive){
                //return the map for edit marker
                add_action( 'wp_ajax_add_marker_action', array( &$this, 'add_marker_action' ));
            }
        }else{
            //only if lfh_mode_user doesn't desactive
            if(!$this->_unactive){
                //for marker editing
                //---------------
                //Button and helper for add marker
                //register button marker for tinymce editor
                add_action( 'admin_head', array( &$this, 'custom_tinymce' ));
                
            }
        }
    }
    public  function get_view($controller_name = NULL)
    {
        if(is_null($controller_name)){
            if(is_null($this->_view)){
                $this->_view = new Lfh_Tools_View('back/editor');
            }
            return $this->_view;
        }else{
            return new Lfh_Tools_View($controller_name);
        }
    }
    // scripts for helper helper.phtml
    public function load_helper_scripts() {
//         $cdn = Lfh_Model_Option::get_option("lfh_use_cdn");
//         if( $cdn){
//             wp_enqueue_style( 'leaflet_css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/'. Lf_Hiker_Plugin::LEAFLET_VERSION .'/leaflet.css',  null, null );
//             wp_enqueue_script('leaflet','https://cdnjs.cloudflare.com/ajax/libs/leaflet/' .Lf_Hiker_Plugin::LEAFLET_VERSION. '/leaflet.js',Array(),null, true);
//         }else{
//             wp_enqueue_style( 'leaflet_css', Lf_Hiker_Plugin::$url.'lib/leaflet/'. Lf_Hiker_Plugin::LEAFLET_VERSION .'/leaflet.css',  null, null );
//             wp_enqueue_script('leaflet', Lf_Hiker_Plugin::$url.'lib/leaflet/' .Lf_Hiker_Plugin::LEAFLET_VERSION. '/leaflet.js',Array(),null, true);
//         }
        Lfh_Tools_Registrer::register_leaflet();
        Lfh_Tools_Registrer::enqueue_leaflet();
        if(WP_DEBUG){
            wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."css/lfh-map-editor.css",Array( 'leaflet_css'), null);
            
        }else{
            wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."dist/lfh-map-editor.".Lf_Hiker_Plugin::VERSION.".css", Array('leaflet_css'), null, null);
        }
        wp_enqueue_script('awesome_marker_js',Lf_Hiker_Plugin::$url. "lib/awesome-marker/leaflet.awesome-markers.min.js", Array('leaflet'), null, true);
 
        $depends = array( 'leaflet', 'awesome_marker_js');

        if(WP_DEBUG ){
            wp_enqueue_script('helper_js',Lf_Hiker_Plugin::$url. "js/lfh-helper.js", $depends, null, true);
        }else{
            wp_enqueue_script('helper_js',Lf_Hiker_Plugin::$url. "dist/". $helper. "-min.".Lf_Hiker_Plugin::VERSION.".js", $depends, null, true);
        }
        $this->add_inline_script_helper();
    }
    // data js for helper helper.phtml
    public function add_inline_script_helper(){
        $data= 'data_helper = {
                confirm : "'.__('Delete marker' , 'lfh').'",
                add_description : "'.__('Add here your formated description', 'lfh').'",
                tiles : '.json_encode(Lfh_Model_Map::$tiles).'
                }';
        wp_add_inline_script('helper_js', $data, 'before');
        
    }
    
    //ajax for load the page helper in editor
    public  function add_marker_action()
    {
        // Commment for the moment cache for helper :
        // if( $this->get_cache()->exist('add-marker-'. get_locale().'.html')){
        //     echo $this->get_cache()->read('add-marker-'. get_locale().'.html');
        //  }else{
        $this->load_helper_scripts(false);
        load_plugin_textdomain( 'lfh', false, realpath(Lf_Hiker_Plugin::$path . '/languages' ));
        $content = $this->get_view()->render('helper',
                array(  'plugin_url' => Lf_Hiker_Plugin::$url,
                        'colors'     => Lfh_Model_Map::$colors_marker,
                        'icons'      => Lfh_Model_Map::$icons_marker,
                        'tiles'      => Lfh_Model_Map::$tiles,
                        'default'    => Lfh_Model_Map::$default,
                        'mapquest_key'=> get_option('lfh_mapquest_key'),
                        'options_map' => Lfh_Model_Map::map_parameters()
                ));
        //        $this->get_cache()->write('add-marker-'. get_locale().'.html', $content);
        echo $content;
        // }
        wp_die(); // this is required to terminate immediately and return a proper response
    }
    //register and add button and plugin in tinyMCE editor
    public  function custom_tinymce()
    {
        global $typenow;
        
        // Only on Post Type: post,  event (events manager)  and page
        if( ! in_array( $typenow, array( 'post', 'page',  'event', 'tribe_events', 'ai1ec_event', 'lfh-map' ) ) )
        {
            return ;
        }
        add_filter( 'mce_buttons', array(&$this,'register_tinymce_button' ));
        add_filter( 'mce_external_plugins', array(&$this,'add_lfh_hiker_tinymce' ));
        add_filter( 'tiny_mce_before_init', array(&$this,'add_css_tinymce' ) );
    }
    // add button in list
    public   function register_tinymce_button( $buttons )
    {
        array_push( $buttons, "button_marker" );
        return $buttons;
    }
    // add javascript file which manage the button
    public  function add_lfh_hiker_tinymce( $plugin_array )
    {
        if(WP_DEBUG){
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . 'js/lfh-tinymce-helper.js' ;
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . 'dist/lfh-tinymce-helper-min'.$version.'.js' ;
        }
        return $plugin_array;
    }
    //add css
    public function  add_css_tinymce( $init_array )
    {
        if(WP_DEBUG){
            $init_array['content_css'] = Lf_Hiker_Plugin::$url . 'css/lfh-post-editor.css';
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            $init_array['content_css'] = Lf_Hiker_Plugin::$url . 'dist/lfh-post-editor'.$version.'.css';
        }
        return $init_array;
    }
    
}