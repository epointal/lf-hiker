<?php
/**
 * 
 * @author epointal
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$lfh_shortcode_done = array();

Class Lfh_Controller_Front
{
   
    private static $_instance = null;
  //  private static $_templates = array();
    private static $_lfh_map_count =0;
    private static $_lfh_mapquest_count = 0;
    private static $_lfh_marker_count = 0;
    private static $_lfh_track_count = 0;
    private  $_view = null;
   
    private function __construct(){
        add_action( 'wp_enqueue_scripts', array(&$this, 'register_scripts') );
      
      //  add_filter ( 'single_template', array(&$this, 'register_map_single_template'));
        add_shortcode('lfh-map', array(&$this, 'map_shortcode'));
        add_shortcode('lfh-marker', array(&$this, 'marker_shortcode'));
        add_shortcode('lfh-gpx', array(&$this, 'gpx_shortcode'));
        // add_shortcode('lfh-kml', array(&$this, 'kml_shortcode'));
        if(!function_exists('shortcode_empty_paragraph_fix')){
            add_filter( 'the_content', array(&$this,'shortcode_empty_paragraph_fix' ));
        }
    }
    public static function get_instance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Lfh_Controller_Front();
        }
        return self::$_instance;
    }
    
    public function get_view($controller_name = NULL){
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
        global $post;
        $cdn = Lfh_Model_Option::get_option('lfh_use_cdn');
        if( $cdn ){
            wp_register_style('leaflet_stylesheet', "https://cdnjs.cloudflare.com/ajax/libs/leaflet/" . Lf_Hiker_Plugin::LEAFLET_VERSION . '/leaflet.css', Array(), null, false);
            wp_register_script('leaflet',"https://cdnjs.cloudflare.com/ajax/libs/leaflet/" . Lf_Hiker_Plugin::LEAFLET_VERSION . "/leaflet.js",Array(),null, true);
     //   wp_register_style('font_awesome', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css", Array(), null, false);
        }else{
            wp_register_style('leaflet_stylesheet', Lf_Hiker_Plugin::$url.'lib/leaflet/'.Lf_Hiker_Plugin::LEAFLET_VERSION.'/leaflet.css', Array(), null, false);
            wp_register_script('leaflet', Lf_Hiker_Plugin::$url.'lib/leaflet/'.Lf_Hiker_Plugin::LEAFLET_VERSION.'/leaflet.js',Array(),null, true);
            
        }
        
        if(WP_DEBUG){
          //  wp_register_style('awesome_marker_css', Lf_Hiker_Plugin::$url."lib/awesome-marker/leaflet.awesome-markers.css", Array('font_awesome'), null, false);
            wp_register_script('awesome_marker_js',Lf_Hiker_Plugin::$url. "lib/awesome-marker/leaflet.awesome-markers.js", Array('leaflet'), null, true);
            wp_register_script('leaflet_gpx_js', Lf_Hiker_Plugin::$url.'lib/leaflet-gpx.js', Array('leaflet'), null, true);

            wp_register_style('lfh_style', Lf_Hiker_Plugin::$url .'css/lfh-style.css', Array(), null, false);
            wp_register_script('lfh_plugin', Lf_Hiker_Plugin::$url . 'js/lfh-plugin.js', Array('leaflet','awesome_marker_js','leaflet_gpx_js'), null, true); 
        }else{
            $version = Lf_Hiker_Plugin::VERSION;
            wp_register_style('lfh_style', Lf_Hiker_Plugin::$url .'dist/lfh-style-min.'.$version.'.css', Array('leaflet_stylesheet'), null, false);
            wp_register_script('lfh_front_min', Lf_Hiker_Plugin::$url . 'dist/lfh-front-min.'.$version.'.js', Array('leaflet'), null, true);
        }
        if (get_post_type() === "attachment" && get_post_mime_type() === "application/gpx+xml") {
            $atts = self::attsFromPost($post);
            $content = '[lfh-gpx ' . $atts . ']';
            $content .= $post->post_content . '[/lfh-gpx]';
            $post->post_content = $content;
        }
    }
    /**
     * Create the shortcode attributes for gpx post type
     * @param WP_Post $post
     * @return string
     */
    public static function attsFromPost($post) {
        $id = $post->ID;
        $color = get_post_meta($id, 'lfh_stroke_color', true);
        $color = empty($color)? Lfh_Model_Map::$default['stroke_color'] :  $color;
        $width = get_post_meta($id, 'lfh_stroke_width', true);
        $width = empty($width)? Lfh_Model_Map::$default['stroke_width'] : $width;
        $value = get_post_meta($id, 'lfh_download_gpx', true);
        $button = empty($value) ? 'false': 'true';
        $atts = 'title="'.htmlspecialchars($post->post_title).'" src=' . $post->guid . ' color=' . $color . ' width=' . $width .' button=' .$button;
        return $atts;
    }

    public  function map_shortcode($atts, $html =null){
        if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ){
            return "";
        }
        if( $this->is_divi_get_thumbnail()){
            return "";
        }
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
        //if( self::$_lfh_mapquest_count == 0 && $options['tile']== 'mapquest'){
         //       self::enqueue_mapquest();
        //}
        //initialize value for a new map
        self::$_lfh_map_count++;
        self::$_lfh_marker_count = 0;
        self::$_lfh_track_count = 0;
        // assign the value of map_count for the templates
        $this->get_view()->assign( 'map_count', self::$_lfh_map_count);
 
        $this->add_map_scripts( $options );
        
        $map = $this->get_view()->render('map', array(
                'options' => $options,
                'is_connected' => wp_get_current_user()->ID
                ));
       
        return $map;
    }
    
  
    public function gpx_shortcode($atts, $html=''){
        if( $this->is_divi_get_thumbnail()){
            return "";
        }
        $options = Lfh_Model_Map::filter_gpx_data($atts);
        if(is_null($options)){
            return '';
        }
        $content = '';
        if( self::$_lfh_map_count==0){
            $content = $this->map_shortcode(array());
        }
        self::$_lfh_track_count++;;
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
    
    public  function marker_shortcode ( $atts, $html = '') {
        if($this->is_divi_get_thumbnail()){
            return "";
        }
        $options = Lfh_Model_Map::filter_marker_data($atts);
        if(is_null($options)){
            return '';
        }
        if( empty($options['popup'])){
            $options['popup'] = $options['title'];
        }
        $content = '';
        if(self::$_lfh_map_count == 0){
            $content = $this->map_shortcode(array());
        }
        self::$_lfh_marker_count++;
        $this->add_marker_script($options);
        $content .= self::get_view()->render('marker',
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
    private  function is_divi_get_thumbnail(){
        global $shortname;
        if( $shortname != "divi"){
            return false;
        }
        $functions = debug_backtrace();
        $find = false;
        $i = 5;
        while( $i < count( $functions) && !$find){
            if( $functions[$i]["function"] === "get_thumbnail"){
                $find = true;
            }
            $i++;
        }
        return $find;
    }
    private function add_map_scripts($options){
        $map_count = self::$_lfh_map_count;
        $images_url = Lf_Hiker_Plugin::$url .'/images/';
        $css = Lfh_Model_Option::get_values('custom_css');
        $selected_color = $css['lfh_selected_path'];
        $data = '';
        if($map_count == 1){
            $data .= 'if( typeof lfh == "undefined"){
                        var lfh = {}
                  }
                  lfh.data = new Array();
                  lfh.ICON_URL = "' . $images_url .'";
                  lfh.tiles = '.json_encode(Lfh_Model_Map::$tiles, JSON_UNESCAPED_SLASHES).';
                  lfh.tiles["mapquest"] = { max_zoom:18 };
                  lfh.SELECTED_COLOR = "' . $selected_color .'";
                  lfh.NUMBER_GPX_FOR_CHECK = '. Lfh_Model_Option::get_option('lfh_number_gpx_for_checkbox').';
                  lfh.DISTANCE_UNIT = ' .json_encode(Lfh_Model_Map::distance_units()). ';
                  lfh.HEIGHT_UNIT = ' .json_encode(Lfh_Model_Map::height_units());';
                ';
        }
        $data .= '
        lfh.data['.$map_count.']= {
              map: '.json_encode($options, JSON_NUMERIC_CHECK ).',
              markers: new Array(),
              gpx: new Array()
        };';
        
        wp_add_inline_script('leaflet', $data, 'before');
    }
    
    /**
     * this function come from the plugin
     * https://wordpress.org/plugins/shortcode-empty-paragraph-fix/
     * it delete the <p> add arround shortcode
     * I load it but add only for page with lfh shortcode
     * and only if the plugin is not active
     * @param {string} $content the post content
     * @return {string} the post content filtered
     */
    public function shortcode_empty_paragraph_fix( $content ) {
    
        $array = array (
                '<p>[' => '[',
                ']</p>' => ']',
                //  ']<br />' => ']',
        );
    
        $content = strtr( $content, $array );
    
        return $content;
    }
    private  function add_marker_script( $options ){
        $map_count = self::$_lfh_map_count;
        $marker_count = self::$_lfh_marker_count;
        $data = ' lfh.data[' . $map_count .'].markers['.$marker_count.'] = '.json_encode($options, JSON_NUMERIC_CHECK).';';
        wp_add_inline_script('leaflet', $data, 'before');
    }
    
    private  function add_gpx_script($options ){
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
   
        $data = 'div.lfh-min div.lfh-nav div.lfh-title,
                 div.undermap div.lfh-nav div.lfh-title,
                 #content .lfh-element h3:first-child,
                 #lfh-fade .lfh-element h3:first-child,
                 .lfh-element h3:first-child {
                     background-color:'.$css['lfh_background'].';
                     color:'.$css['lfh_color'].';
                 }
                 a.lfh-gpx-file,
                 .lfh-section .lfh-header,
                 #content .lfh-section .lfh-header,
                 #content .lfh-section .lfh-header h4,
                 #lfh-fade a.lfh-gpx-file,
                 #lfh-fade .lfh-section .lfh-header,
                 #lfh-fade .lfh-section .lfh-header h4{
                     background-color:'.$css['lfh_background_sub'].';
                     color:'.$css['lfh_color'].';
                 }
                 a.lfh-gpx-file{
                     border-color:'.$css['lfh_color'].';
                 }
                 div.lfh-min a.lfh-gpx-file,
                 div.undermap a.lfh-gpx-file{
                     background:none;
                     color:inherit;
                     border:none;
                 }
                 .lfh-button,
                 #content .lfh-button,
                 .main .lfh-button,
                 .lfh-button + input[type="button"],
                input.lfh-button,
                #content input.lfh-button,
                .main input.lfh-button,
                input.lfh-button + input[type="button"]{
                       font-family:"lfhiker";
                       background-color:' .$css['lfh_button_color'].';
                       border-color: ' . Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], 10) . ' ';
        $data .= Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -10) .' ';
        $data .= Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -10) .';
                 text-shadow: 0 -1px 1px ' . Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -20) .', ';
        $data .= '1px 0 1px '. Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -10).', ';
        $data .= '0 1px 1px '. Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -10).', ';
        $data .= '-1px 0 1px '. Lfh_Tools_Color::lighter_darker( $css['lfh_button_color'], -20).';
   
                 }
                 .lfh-button:hover,
                 #content .lfh-button:hover,
                 .main .lfh-button:hover,
                input.lfh-button:hover,
                #content input.lfh-button:hover,
                .main input.lfh-button:hover{
                       background-color:' . Lfh_Tools_Color::saturate( $css['lfh_button_color'], 20) .';
                 }
            ';
        wp_add_inline_style('lfh_style', $data );
    }
   
    
   
}
