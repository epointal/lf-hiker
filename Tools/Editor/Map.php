<?php
/**
 * Class to edit map 
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Tools_Editor_Map{
    private $_view = null;
    private static $_lfh_map_count =0;
    private static $_lfh_mapquest_count = 0;
    private static $_lfh_marker_count = 0;
    private static $_lfh_track_count = 0;
    
    public function __construct() {
        add_shortcode('lfh-map', array(&$this, 'map_shortcode'));
        add_shortcode('lfh-marker', array(&$this, 'marker_shortcode'));
        add_shortcode('lfh-gpx', array(&$this, 'gpx_shortcode'));
        add_action('edit_form_after_title', array(&$this, 'add_media_button_to_map_editor'));
        add_action('edit_form_after_title', array(&$this, 'do_shortcode_in_editor'));
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
    public  function map_shortcode($atts, $html =null){
        global $post;
        $options = Lfh_Model_Map::filter_map_data($atts);
        
        if(self::$_lfh_map_count == 0){
            //if the first map in the page/article add a div for the fade in bottom fullscreen
            
//             $css = Lfh_Model_Option::get_values('custom_css');
//             self::enqueue_scripts( $css );
            
        }
        self::$_lfh_map_count = $post->ID;
        self::$_lfh_marker_count = 0;
        self::$_lfh_track_count = 0;
        
        $this->add_map_scripts( $options );
        return '';
    }
    
    
    public function gpx_shortcode($atts, $html=''){
        $options = Lfh_Model_Map::filter_gpx_data($atts);
        if(is_null($options)){
            return '';
        }
        if( self::$_lfh_map_count==0){
            $content = $this->map_shortcode(array());
        }
        self::$_lfh_track_count++;;
        $this->add_gpx_script($options, $html);
        return '';
    }
    
    public  function marker_shortcode ( $atts, $html = '') {
        $options = Lfh_Model_Map::filter_marker_data($atts);
        if(is_null($options)){
            return '';
        }
        if(self::$_lfh_map_count == 0){
            $content = $this->map_shortcode(array());
        }
        self::$_lfh_marker_count++;
        $this->add_marker_script($options, $html);
        
        return '';
    }
    // scripts only for edit post page
    public function add_media_button_to_map_editor() {
        if (get_post_type() === 'lfh-map') {
            echo $this->get_view()->render('map-editor-buttons', array());
        }
    }
    public function do_shortcode_in_editor($post) {
        if (get_post_type() === 'lfh-map') {
            $this->enqueue_scripts();
            load_plugin_textdomain( 'lfh', false, realpath(Lf_Hiker_Plugin::$path . '/languages' ));
            do_shortcode($post->post_content);
            $content = $this->get_view()->render('helper-body',
                    array(  'plugin_url' => Lf_Hiker_Plugin::$url,
                            'is_map_type' => true,
                            'colors'     => Lfh_Model_Map::$colors_marker,
                            'icons'      => Lfh_Model_Map::$icons_marker,
                            'tiles'      => Lfh_Model_Map::$tiles,
                            'default'    => Lfh_Model_Map::$default,
                            'mapquest_key'=> get_option('lfh_mapquest_key'),
                            'options_map' => Lfh_Model_Map::map_parameters(),
                            'colors_path' => Lfh_Model_Map::$colors_path,
                            'post'       => $post
                    ));
            //        $this->get_cache()->write('add-marker-'. get_locale().'.html', $content);
            echo $content;
        }
    }
    public function enqueue_scripts(){

       wp_enqueue_media();
       $depends = Lfh_Tools_Registrer::register_leaflet();
       array_push($depends, 'jquery');
       Lfh_Tools_Registrer::enqueue_leaflet();
        if(WP_DEBUG){
            wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."css/lfh-map-editor.css",Array( 'leaflet_css'), null);
            
        }else{
            wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."dist/lfh-map-editor.".Lf_Hiker_Plugin::VERSION.".css", Array('leaflet_css'), null, null);
        }
        if(WP_DEBUG ){
            wp_enqueue_script('lfh_map_editor', Lf_Hiker_Plugin::$url. "js/lfh-map-editor.js", $depends, null, true);
        }else{
            wp_enqueue_script('lfh_map_editor', Lf_Hiker_Plugin::$url. "dist/lfh-map-editor-min.".Lf_Hiker_Plugin::VERSION.".js", $depends, null, true);
        }
        $this->add_inline_script();
    }
    // data js for helper helper.phtml
    public function add_inline_script(){
        $data = '';
        $data .= 'data_helper = {
                confirm : "'.__('Delete marker' , 'lfh').'",
                add_description : "'.__('Add here your formated description', 'lfh').'",
                tiles : '.json_encode(Lfh_Model_Map::$tiles).'
                }';
        $data .= '';
        wp_add_inline_script('lfh_map_editor', $data, 'before');
        
    }
    private function add_map_scripts($options){
        $map_count = self::$_lfh_map_count;
        $images_url = Lf_Hiker_Plugin::$url .'/images/';
        $css = Lfh_Model_Option::get_values('custom_css');
        $selected_color = $css['lfh_selected_path'];
        $data = '';
       // if($map_count == 1){
            $data .= 'if( typeof lfh === "undefined"){
                        var lfh = {}
                  };
                  lfh.data = new Array();
                  lfh.ICON_URL = "' . $images_url .'";
                  lfh.tiles = '.json_encode(Lfh_Model_Map::$tiles, JSON_UNESCAPED_SLASHES).';
                  lfh.tiles["mapquest"] = { max_zoom:18 };
                  lfh.SELECTED_COLOR = "' . $selected_color .'";
                  lfh.NUMBER_GPX_FOR_CHECK = '. Lfh_Model_Option::get_option('lfh_number_gpx_for_checkbox').';
                  lfh.DISTANCE_UNIT = ' .json_encode(Lfh_Model_Map::distance_units()). ';
                  lfh.HEIGHT_UNIT = ' .json_encode(Lfh_Model_Map::height_units());';
                ';
       // }
        $data .= '
        lfh.data['.$map_count.']= {
              map: '.json_encode($options, JSON_NUMERIC_CHECK ).',
              markers: new Array(),
              gpx: new Array()
        };';
        $data .= '';
        wp_add_inline_script('lfh_map_editor', $data, 'before');
    }
    private  function add_marker_script( $options ){
        $map_count = self::$_lfh_map_count;
        $marker_count = self::$_lfh_marker_count;
        $data = ' lfh.data[' . $map_count .'].markers['.$marker_count.'] = '.json_encode($options, JSON_NUMERIC_CHECK).';';
        wp_add_inline_script('lfh_map_editor', $data, 'before');
    }
    
    private  function add_gpx_script($options ){
        $map_count = self::$_lfh_map_count;
        $track_count = self::$_lfh_track_count;
        $data = '  lfh.data['.$map_count .'].gpx['.$track_count .'] = '.json_encode($options, JSON_NUMERIC_CHECK).';';
        wp_add_inline_script('lfh_map_editor', $data, 'before');
    }
    private static function enqueue_scripts_old($css){
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