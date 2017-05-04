<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Lfh_Tools_Editor
{
    private $_view = null;
    private $_cache = null;
    private $_unactive = false;
    
    public function __construct( $unactive)
    {
        // for manage gpx file
        global $pagenow;
        $this->_unactive = $unactive;
        // Add gpx and xml as a supported upload type to Media Gallery
        add_filter( 'upload_mimes', array( &$this, 'xml_upload_mimes') );
        
        //Add fields color and stroke wifth for gpx file in media gallery
        //need when ajax in upload file and edit gpx in post media edit
        add_filter( 'attachment_fields_to_edit', array( &$this, 'gpx_attachment_field'), 10, 2 );
        add_filter( 'attachment_fields_to_save', array( &$this, 'gpx_attachment_save_field' ), 10, 2 );
       
        // Filter for gpxs in Media Gallery
        add_filter( 'post_mime_types', array( &$this, 'xml_post_mime_types') );
        // Embed  gpx shortcode instead of link
        add_filter( 'media_send_to_editor', array( &$this, 'xml_media_send_to_editor' ), 20, 3 );
            
        if (wp_doing_ajax()){
            if(!$this->_unactive){
                //return the map for edit marker
                add_action( 'wp_ajax_add_marker_action', array( &$this, 'add_marker_action' ));
            }
            //save value in custom field of gpx file in upload window
            add_action('wp_ajax_save-attachment-compat', array( &$this, 'fields_to_save_on_upload' ), -1, 1);
        }else{
                //only if lfh_mode_user doesn't desactive
                if(!$this->_unactive){
                    //for marker editing
                    //---------------
                    //Button and helper for add marker
                    //register button marker for tinymce editor
                    add_action( 'admin_head', array( &$this, 'custom_tinymce' ));
           
                }
                // for Manage gpx file
                //-------------------
                // script for edit fields of gpx file
                add_action( 'admin_print_scripts-post-new.php', array(&$this, 'load_editor_scripts'), 11 );
                add_action( 'admin_print_scripts-post.php', array(&$this, 'load_editor_scripts'), 11 );
                
          
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
    public  function get_cache($controller_name = NULL)
    {
        if(is_null($controller_name)){
            if(is_null($this->_cache)){
                $this->_cache = new Lfh_Tools_Cache('Back');
            }
            return $this->_cache;
        }else{
            return new Lfh_Tools_Cache($controller_name);
        }
    }
    // scripts only for edit post page
    public function load_editor_scripts()
    {
        
        if(WP_DEBUG){
            wp_register_style('lfh_editor_css', Lf_Hiker_Plugin::$url .'/css/lfh-post-editor.css', Array(), null, false);
            wp_register_script('lfh_editor_js', Lf_Hiker_Plugin::$url . '/js/lfh-post-editor.js', Array('jquery'), null, false);
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            wp_register_style('lfh_editor_css', Lf_Hiker_Plugin::$url .'/dist/lfh-post-editor'.$version.'.css', Array(), null, false);
            wp_register_script('lfh_editor_js', Lf_Hiker_Plugin::$url . '/dist/lfh-post-editor-min'.$version.'.js', Array('jquery'), null, false);
        }
        wp_enqueue_style('lfh_editor_css');
        wp_enqueue_script('lfh_editor_js');
        $this->script_for_tinyMCE();
        
    }
    
    function script_for_field_color($post_id){
        $script = 'lfh.init_dom_color("lfh-select-color-' .$post_id. '");';
        wp_add_inline_script('lfh_editor_jd', $script );
    }
    // for markers
    //------------
    //Add script for transmit data to tinyMCE javascript editor
    public function script_for_tinyMCE()
    {
        $data = ' var lfh_plugin = {
            url : "'.Lf_Hiker_Plugin::$url.'",
            ajax: "'. admin_url('admin-ajax.php').'",
            langage: {
                addMarker : "'. __('Add marker', 'lfh').'"
            }
        }';
      
        wp_add_inline_script('lfh_editor_js', $data, 'before');
        return;
    
    }
    
    // data js for helper add-marker.phtml
    public function add_inline_script_helper(){
        $data= 'data_helper = {
                confirm : "'.__('Delete marker' , 'lfh').'",
                add_description : "'.__('Add here your formated description', 'lfh').'",
                tiles : '.json_encode(Lfh_Model_Map::$tiles).'
                }';
        wp_add_inline_script('helper_js', $data, 'before');
        
    }
    // scripts for helper add-marker.phtml
   public function load_scripts_helper(){
       wp_enqueue_style( 'leaflet_css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.css',  null, 'screen' );
       wp_enqueue_style('font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', null, 'screen');
       wp_enqueue_style('awesome_marker_css', Lf_Hiker_Plugin::$url."lib/awesome-marker/leaflet.awesome-markers.css", Array('font_awesome'), null, 'screen');
       
       if(WP_DEBUG){
           wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."css/helper.css", Array('awesome_marker_css', 'leaflet_css'), null, 'screen');
            
       }else{
           wp_enqueue_style('helper_css', Lf_Hiker_Plugin::$url."dist/helper.".Lf_Hiker_Plugin::VERSION.".css", Array('awesome_marker_css', 'leaflet_css'), null, 'screen');
       }
       wp_enqueue_script('leaflet',"https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.3/leaflet.js",Array(),null, true);
       wp_enqueue_script('awesome_marker_js',Lf_Hiker_Plugin::$url. "lib/awesome-marker/leaflet.awesome-markers.min.js", Array('leaflet'), null, true);
      

       $mapquest_key = get_option('lfh_mapquest_key');
       $depends = array( 'leaflet', 'awesome_marker_js');
       if(!is_null($mapquest_key)){
           wp_enqueue_script('mapquest', 'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key='.$mapquest_key, Array('leaflet'), null, true);
           $depends[] = 'mapquest';
       }
       if(WP_DEBUG ){
           wp_enqueue_script('helper_js',Lf_Hiker_Plugin::$url. "js/helper.js", $depends, null, true);
       }else{
           wp_enqueue_script('helper_js',Lf_Hiker_Plugin::$url. "dist/helper-min.".Lf_Hiker_Plugin::VERSION.".js", $depends, null, true);
       }
       $this->add_inline_script_helper();
   }
    //ajax for load the page add-marker in editor
    public  function add_marker_action()
    {
        if( $this->get_cache()->exist('add-marker-'. get_locale().'.html')){
            echo $this->get_cache()->read('add-marker-'. get_locale().'.html');
        }else{
            $this->load_scripts_helper();
            load_plugin_textdomain( 'lfh', false, realpath(Lf_Hiker_Plugin::$path . '/languages' ));
            $content = $this->get_view()->render('add-marker',
                    array(  'plugin_url' => Lf_Hiker_Plugin::$url,
                            'colors'     => Lfh_Model_Map::$colors_marker,
                            'icons'      => Lfh_Model_Map::$icons_marker,
                            'tiles'      => Lfh_Model_Map::$tiles,
                            'default'    => Lfh_Model_Map::$default,
                            'mapquest_key'=> get_option('lfh_mapquest_key'),
                            'options_map' => Lfh_Model_Map::map_parameters()
                    ));
                    $this->get_cache()->write('add-marker-'. get_locale().'.html', $content);
                    echo $content;
        }
        wp_die(); // this is required to terminate immediately and return a proper response
    }
    //register and add button and plugin in tinyMCE editor
    public  function custom_tinymce()
    {
        global $typenow;
        // Only on Post Type: post and page
        if( ! in_array( $typenow, array( 'post', 'page' ) ) )
            return ;
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
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . '/js/tinymce-lfh-plugin.js' ;
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . '/dist/tinymce-lfh-plugin-min'.$version.'.js' ;
        }
        return $plugin_array;
    }
    //add css 
    public function  add_css_tinymce( $init_array )
    { 
        if(WP_DEBUG){
            $init_array['content_css'] = Lf_Hiker_Plugin::$url . '/css/lfh-post-editor.css';
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
    	    $init_array['content_css'] = Lf_Hiker_Plugin::$url . '/dist/lfh-post-editor'.$version.'.css'; 
        }
	    return $init_array; 
	}
    // Manage gpx file : filter for gpx in media gallery...
    //-----------------------------------------
    // @todo often gpx file have mime type equal to 'application/xml'
    // Take over xml and gpx type in media gallery
    public  function xml_upload_mimes($existing_mimes = array())
    {
        if(!isset($existing_mimes['xml'])){
            $existing_mimes['xml'] = 'application/xml';
        }
        if(!isset($existing_mimes['gpx'])){
            $existing_mimes['gpx'] = 'application/gpx+xml';
        }
        return $existing_mimes;
    }
    //  filter for gpx in media gallery
    public  function xml_post_mime_types($post_mime_types)
    {
       $post_mime_types['application/gpx+xml'] = array( 'GPX', __( 'Manage gpx' , 'lfh'), 
                                                        _n_noop( 'PDF <span class="count">(%s)</span>',
                                                        'GPX <span class="count">(%s)</span>' ,
                                                        'leaflet') );
        return $post_mime_types;
    }
     
    // Embed  gpx shortcode instead of link
    public function xml_media_send_to_editor($html, $id, $attachment)
    {
        if (isset($attachment['url']) && preg_match( "/\.gpx$/i", $attachment['url'])) {
          
            $color = get_post_meta($id, 'lfh_stroke_color', true);
            $width = get_post_meta($id, 'lfh_stroke_width', true);
            $filter = '';
            $filter .= '[lfh-gpx src=' . $attachment['url'] .' title="' . $attachment['post_title'] .'"';
            $filter .= ' color='. $color . ' width=' .$width . ' ]';
            if($attachment['post_content']){
                $filter .= $attachment['post_content'];
            }else{
                $filter .=  __('Add here your formated description','lfh');
            }
            $filter .= '[/lfh-gpx]<br /><br />';
            return apply_filters('gpx_override_send_to_editor',  $filter , $html, $id, $attachment);
        }else{
            return $html;
        }
    }
    
     
    //Add custom field for gpx file
    public function gpx_attachment_field( $form_fields, $post )
    {
    
        if($post->post_mime_type == 'application/gpx+xml' || $post->post_mime_type == 'application/xml'){
            //stroke color
            $form_fields['lfh_stroke_color'] = array(
                    'label' => __('Color path' , 'lfh'),
                    'input' => 'html',
                    'value' => get_post_meta( $post->ID, 'lfh_stroke_color', true ),
                    'html'  => $this->get_view()->render('select-color-path', array(
                            'post_id'   => $post->ID,
                            'field'     => 'lfh_stroke_color',
                            'value'     => get_post_meta( $post->ID, 'lfh_stroke_color', true ),
                            'colors'    => Lfh_Model_Map::$colors_path,
                            'default'   => Lfh_Model_Map::$default['stroke_color']
                    ))
            );
            // stroke width
            $options = array();
            for($i = 1; $i < 9 ; $i++){
                $options[ strval($i) ] = $i . ' px';
            }
            $form_fields['lfh_stroke_width'] = array(
                    'label' => __('Stroke width', 'lfh'),
                    'input' => 'html',
                    'value' => get_post_meta( $post->ID, 'lfh_stroke_width', true),
                    'html'  => $this->get_view()->render('select-element', array(
                            'post_id'   => $post->ID,
                            'field' => 'lfh_stroke_width',
                            'options'   => $options,
                            'value'     => get_post_meta( $post->ID, 'lfh_stroke_width', true ),
                            'default'   => Lfh_Model_Map::$default['stroke_width']
                    ))
            );
        }
        return $form_fields;
    }
    // Save value in custom fields for gpx in edit page
    public function gpx_attachment_save_field( $post, $attachment)
    {
        if( isset($attachment['lfh_stroke_color']) ){
            update_post_meta($post['ID'], 'lfh_stroke_color', $attachment['lfh_stroke_color']);
        }
        if( isset($attachment['lfh_stroke_width']) ){
            update_post_meta($post['ID'], 'lfh_stroke_width', $attachment['lfh_stroke_width']);
        }
        return $post;
    }
    //add in ajax for save also custom fieds in upload page
   
    function fields_to_save_on_upload()
    {
        $post_id = intVal($_POST['id']);
        if(isset( $_POST['attachments'][$post_id ]['lfh_stroke_color']) ){
            $meta = Lfh_Model_Map::is_path_color($_POST['attachments'][$post_id ]['lfh_stroke_color']);
            update_post_meta($post_id , 'lfh_stroke_color', $meta);
        }
        if(isset(  $_POST['attachments'][$post_id ]['lfh_stroke_width'] )){
            $meta = filter_var($_POST['attachments'][$post_id ]['lfh_stroke_width'], FILTER_VALIDATE_INT , array(
                                'options'   => array(
                                                'default'   => Lfh_Model_Map::$default['stroke_width'],
                                                'min_range' => 1,
                                                'max_range' => 10)));
            update_post_meta($post_id , 'lfh_stroke_color', $meta);
        }
       
        clean_post_cache($post_id);
    }
}