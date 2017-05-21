<?php
/**
 * 
 * @author epointal
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Controller_Front
{
    private static $_instance = null;
    private static $_lfh_map_count =0;
    private static $_lfh_mapquest_count = 0;
    private static $_lfh_marker_count = 0;
    private static $_lfh_track_count = 0;
    private  $_view = null;
   
    private function __construct(){
       
        add_action( 'wp_enqueue_scripts', array(&$this, 'register_scripts') );
        add_shortcode('lfh-map', array(&$this, 'map_shortcode'));
        add_shortcode('lfh-marker', array(&$this, 'marker_shortcode'));
        add_shortcode('lfh-gpx', array(&$this, 'gpx_shortcode'));
        add_shortcode('lfh-kml', array(&$this, 'kml_shortcode'));
    }
    public static function get_instance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Lfh_Controller_Front();
        }
        return self::$_instance;
    }
    
    public  function get_view($controller_name = NULL){
        if(is_null($controller_name)){
            if(is_null($this->_view)){
                $this->_view = new Lfh_Tools_View('Front');
            }
            return $this->_view;
        }else{
            return new Lfh_Tools_View($controller_name);
        }
    }
    public  function register_scripts () {
    
        wp_register_style('leaflet_stylesheet', "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.css", Array(), null, false);
        wp_register_script('leaflet',"https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.js",Array(),null, true);
        wp_register_style('font_awesome', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css", Array(), null, false);
        
        
        if(WP_DEBUG){
            wp_register_style('awesome_marker_css', Lf_Hiker_Plugin::$url."lib/awesome-marker/leaflet.awesome-markers.css", Array('font_awesome'), null, false);
            wp_register_script('awesome_marker_js',Lf_Hiker_Plugin::$url. "lib/awesome-marker/leaflet.awesome-markers.js", Array('leaflet'), null, true);
            wp_register_script('leaflet_gpx_js', Lf_Hiker_Plugin::$url.'lib/leaflet-gpx.js', Array('leaflet'), null, true);

            wp_register_style('lfh_style', Lf_Hiker_Plugin::$url .'css/lfh-style.css', Array(), null, false);
            wp_register_script('lfh_plugin', Lf_Hiker_Plugin::$url . 'js/lfh-plugin.js', Array('leaflet','awesome_marker_js','leaflet_gpx_js'), null, true); 
        }else{
            $version = Lf_Hiker_Plugin::VERSION;
            wp_register_style('lfh_style', Lf_Hiker_Plugin::$url .'dist/lfh-style-min.'.$version.'.css', Array('font_awesome'), null, false);
            wp_register_script('lfh_front_min', Lf_Hiker_Plugin::$url . 'dist/lfh-front-min.'.$version.'.js', Array('leaflet'), null, true);
        }
    }
   
    public function map_shortcode($atts, $html =null){
        if(!is_array($atts)){
            $atts = array();
        }
        $options = Lfh_Model_Map::filter_map_data($atts);
        
        if(self::$_lfh_map_count == 0){
            //if the first map in the page/article add a div for the fade in bottom fullscreen
            // @todo (can do this with js)
            add_action( 'wp_footer', array(&$this,'add_div_fadable'), 100 );
            
            $css = Lfh_Model_Option::get_values('custom_css');
            self::enqueue_scripts( $css );
            // assign the url of images for the template and color path
            $this->get_view()->assign('images_url', Lf_Hiker_Plugin::$url .'/images/');
            $this->get_view()->assign('selected_color' , $css['lfh_selected_path']);
        }
        //mapquest case 
        if( self::$_lfh_mapquest_count == 0 && $options['tile']== 'mapquest'){
                self::enqueue_mapquest();
        }
        //initialize value for a new map
        self::$_lfh_map_count++;
        self::$_lfh_marker_count = 0;
        self::$_lfh_track_count = 0;
        // assign the value of map_count for the templates
        $this->get_view()->assign( 'map_count', self::$_lfh_map_count);
        $this->add_map_scripts( $options );
        return $this->get_view()->render('map', array(
                'options' => $options,
                'is_connected' => wp_get_current_user()->ID
                ));
    }
    
  
    public function gpx_shortcode($atts, $html=''){
        $options = Lfh_Model_Map::filter_gpx_data($atts);
        if(is_null($options)){
            return '';
        }
        $content = '';
        if(self::$_lfh_map_count==0){
            $content = self::map_shortcode(array());
        }
       
        self::$_lfh_track_count++;
        $this->add_gpx_script($options);
        $content .= $this->get_view()->render('track' ,
                        array(
                        'file_type'   => 'GPX',
                        'track_id'    => 'track-' . self::$_lfh_map_count .'-'.self::$_lfh_track_count,
                        'track_count' => self::$_lfh_track_count,
                        'options'     => $options,
                        'html'        => $html
                   ));
        return $content;
    }
    
    public function marker_shortcode ( $atts, $html = '') {
        $options = Lfh_Model_Map::filter_marker_data($atts);
        if(is_null($options)){
            return '';
        }
        $content = '';
        if(self::$_lfh_map_count == 0){
            $content = self::map_shortcode(array());
        }
        self::$_lfh_marker_count++;
        $this->add_marker_script($options);
        $content .= $this->get_view()->render('marker',
                array(
                        'marker_count'  => self::$_lfh_marker_count,
                        'marker_id'     => 'marker-' . self::$_lfh_map_count .'-'.self::$_lfh_marker_count,
                        'options'       => $options,
                        'html'          => $html
                ));
        return $content; 
    }
    public  function add_div_fadable(){
        echo '<div id="lfh-fade"></div>';
    }
    private function add_map_scripts($options){
        $map_count = self::$_lfh_map_count;
        $images_url = Lf_Hiker_Plugin::$url .'/images/';
        $css = Lfh_Model_Option::get_values('custom_css');
        $selected_color = $css['lfh_selected_path'];
        if($map_count == 1){
            $data = ' if( typeof lfh == "undefined"){
                        var lfh = {}
                  }
                  lfh.data = new Array();
                  lfh.ICON_URL = "' . $images_url .'";
                  lfh.tiles = '.json_encode(Lfh_Model_Map::$tiles, JSON_UNESCAPED_SLASHES).';
                  lfh.tiles["mapquest"] = { max_zoom:18 };
                  lfh.SELECTED_COLOR = "' . $selected_color .'";
                ';
        }
        $data .= '
        lfh.data['.$map_count.']= {
              map: '.json_encode($options, JSON_NUMERIC_CHECK ).',
              markers: new Array(),
              gpx: new Array()
        };
            ';
        wp_add_inline_script('leaflet', $data, 'before');
    }
    
    private function add_marker_script( $options ){
        $map_count = self::$_lfh_map_count;
        $marker_count = self::$_lfh_marker_count;
        $data = ' lfh.data[' . $map_count .'].markers['.$marker_count.'] = '.json_encode($options, JSON_NUMERIC_CHECK).';';
        wp_add_inline_script('leaflet', $data, 'before');
    }
    
    private function add_gpx_script($options ){
        $map_count = self::$_lfh_map_count;
        $track_count = self::$_lfh_track_count;
        $data = '  lfh.data['.$map_count .'].gpx['.$track_count .'] = '.json_encode($options, JSON_NUMERIC_CHECK).';';
        wp_add_inline_script('leaflet', $data, 'before');
    }
    private static function enqueue_scripts($css){
        //need load css and script for map
        wp_enqueue_style('leaflet_stylesheet');
        wp_enqueue_style('font_awesome');
        wp_enqueue_script('leaflet');
        wp_enqueue_style('lfh_style');
        self::add_css_inline($css);
        
        if(WP_DEBUG){
            wp_enqueue_style('awesome_marker_css');
            wp_enqueue_script('leaflet_gpx_js');
            wp_enqueue_script('awesome_marker_js');
            wp_enqueue_script('lfh_plugin');
        }else{
            
            wp_enqueue_script('lfh_front_min');
        }
    }
    private static function enqueue_mapquest(){
        wp_enqueue_script(
                'mapquest',
                'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key='.get_option('lfh_mapquest_key'),
                array('leaflet'),
                null,
                true);
        self::$_lfh_mapquest_count++;
        //deenqueue and reenqueue lfh-plugin
         
        if(WP_DEBUG){
            wp_deregister_script('lfh_plugin');
            wp_enqueue_script('lfh_plugin', Lf_Hiker_Plugin::$url . 'assets/js/lfh-plugin.js', Array('leaflet','awesome_marker_js','leaflet_gpx_js', 'mapquest'), null, true);
        }else{
            $version = Lf_Hiker_Plugin::VERSION;
            wp_deregister_script('lfh_front_min');
            wp_register_script('lfh_front_min', Lf_Hiker_Plugin::$url . 'dist/lfh-front-min.'.$version.'.js', Array('leaflet', 'mapquest'), null, true);
        }
        
    }
    private static function add_css_inline($css){
        $data = "#content .lfh-element h3:first-child,
                 #lfh-fade .lfh-element h3:first-child {
                     background-color:".$css['lfh_background'].";
                     color:".$css['lfh_color'].";
                 }
                 #content a.lfh-gpx-file,
                 #content .lfh-section .lfh-header,
                 #content .lfh-section .lfh-header h4,
                 #lfh-fade a.lfh-gpx-file,
                 #lfh-fade .lfh-section .lfh-header,
                 #lfh-fade .lfh-section .lfh-header h4{
                     background-color:".$css['lfh_background_sub'].";
                     color:".$css['lfh_color'].";
                 }
                 #content a.lfh-gpx-file,
                 #lfh-fade a.lfh-gpx-file{
                     border-color:".$css['lfh_color'].";
                 }
                ";
        wp_add_inline_style('lfh_style', $data );
        
    }
}
