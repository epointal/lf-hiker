<?php
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
                    //add js data before tinyMCE
                    add_action('edit_form_top', array( &$this,'script_for_tinyMCE'));
                    // add action ajax  loading the helper page "add marker" ++
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
            wp_register_style('lfh_editor_css', Lf_Hiker_Plugin::$url .'/assets/css/lfh-post-editor.css', Array(), null, false);
            wp_register_script('lfh_editor_js', Lf_Hiker_Plugin::$url . '/assets/js/lfh-post-editor.js', Array('jquery'), null, false);
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            wp_register_style('lfh_editor_css', Lf_Hiker_Plugin::$url .'/dist/lfh-post-editor'.$version.'.css', Array(), null, false);
            wp_register_script('lfh_editor_js', Lf_Hiker_Plugin::$url . '/dist/lfh-post-editor'.$version.'.js', Array('jquery'), null, false);
        }
        wp_enqueue_style('lfh_editor_css');
        wp_enqueue_script('lfh_editor_js');
    }
    // for markers
    //------------
    //Add script for transmit data to tinyMCE javascript editor
    public function script_for_tinyMCE()
    {
        
        echo $this->get_view()->render('script-for-tinyMCE' , array(
                'plugin_url' => Lf_Hiker_Plugin::$url,
                'ajax_url'   => admin_url('admin-ajax.php')
        ));
        return;
    
    }
    //ajax for load the page add-marker in editor
    public  function add_marker_action()
    {
        if( $this->get_cache()->exist('add-marker-'. get_locale().'.html')){
            echo $this->get_cache()->read('add-marker-'. get_locale().'.html');
        }else{
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
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . '/assets/js/tinymce-lfh-plugin.js' ;
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
           // $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . '/dist/tinymce-lfh-plugin'.$version.'.js' ;
            $plugin_array['Lfh_plugin'] = Lf_Hiker_Plugin::$url . '/assets/js/tinymce-lfh-plugin.js' ;
        }
        return $plugin_array;
    }
    //add css 
    public function  add_css_tinymce( $init_array )
    { 
	    $init_array['content_css'] = Lf_Hiker_Plugin::$url . '/assets/css/lfh-post-editor.css'; 
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
       
        $post_id = $_POST['id'];
        if(isset( $_POST['attachments'][$post_id ]['lfh_stroke_color']) ){
            $meta = $_POST['attachments'][$post_id ]['lfh_stroke_color'];
            update_post_meta($post_id , 'lfh_stroke_color', $meta);
        }
        if(isset(  $_POST['attachments'][$post_id ]['lfh_stroke_width'] )){
            $meta = $_POST['attachments'][$post_id ]['lfh_stroke_width'];
            update_post_meta($post_id , 'lfh_stroke_color', $meta);
        }
       
        clean_post_cache($post_id);
    }
}