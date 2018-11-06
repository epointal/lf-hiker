<?php
/**
 * Class to edit gpx file
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Tools_Editor_Gpx{
    private $_view = null;
    public function __construct() {
        // Add gpx and xml as a supported upload type to Media Gallery
        add_filter( 'upload_mimes', array( &$this, 'xml_upload_mimes') );
        
        //Add fields color and stroke wifth for gpx file in media gallery
        //need when ajax in upload file and edit gpx in post media edit
        add_filter( 'attachment_fields_to_edit', array( &$this, 'gpx_attachment_field'), 10, 2 );
        add_filter( 'attachment_fields_to_save', array( &$this, 'gpx_attachment_save_field' ), 10, 2 );
        
        // Add gpx filter  in Media Gallery
        add_filter( 'post_mime_types', array( &$this, 'xml_post_mime_types') );
        // Embed  gpx shortcode instead of link in  old classic post editor
        add_filter( 'media_send_to_editor', array( &$this, 'xml_media_send_to_editor' ), 20, 3 );
        //  //save value in custom field of gpx file in upload window
        if (wp_doing_ajax()){
           add_action('wp_ajax_save-attachment-compat', array( &$this, 'fields_to_save_on_upload' ), -1, 1);
        }
        // script for edit fields of gpx file
        add_action( 'admin_print_scripts-post-new.php', array(&$this, 'load_editor_scripts'), 11 );
        add_action( 'admin_print_scripts-post.php', array(&$this, 'load_editor_scripts'), 11 );
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
            $color = empty($color)? Lfh_Model_Map::$default['stroke_color'] :  $color;
            $width = get_post_meta($id, 'lfh_stroke_width', true);
            $width = empty($width)? Lfh_Model_Map::$default['stroke_width'] : $width;
            $value = get_post_meta($id, 'lfh_download_gpx', true);
            $button = empty($value) ? 'false': 'true';
            $filter = '';
            $filter .= '[lfh-gpx src=' . $attachment['url'] .' title="' . $attachment['post_title'] .'"';
            $filter .= ' button='. $button .' color='. $color . ' width=' .$width . ' ]';
            if($attachment['post_content']){
                $filter .= $attachment['post_content'];
            }else{
                $filter .=  __('Add here your formated description','lfh');
            }
            $filter .= '[/lfh-gpx]<br /><br />';
            $attachment['lfh_stroke_color'] = $color;
            return apply_filters('gpx_override_send_to_editor',  $filter , $html, $id, $attachment);
        }else{
            return $html;
        }
    }
    // Add custom field for gpx file
    public function gpx_attachment_field( $form_fields, $post )
    {
        
        if($post->post_mime_type == 'application/gpx+xml' || $post->post_mime_type == 'application/xml'){
            
            $value = get_post_meta( $post->ID, 'lfh_download_gpx', true );
            if($value==''){
                $value = Lfh_Model_Option::get_option('lfh_download_gpx');
            }
            $form_fields['lfh_download_gpx'] = array(
                    'label' => __('Display download button' , 'lfh'),
                    'input' => 'html',
                    'value' => get_post_meta( $post->ID, 'lfh_', true ),
                    'html'  => $this->create_field_download_gpx( $post->ID , $value)
            );
            //stroke color
            $form_fields['lfh_stroke_color'] = array(
                    'label' => __('Color path' , 'lfh'),
                    'input' => 'html',
                    'value' => get_post_meta( $post->ID, 'lfh_stroke_color', true ),
                    'html'  => $this->get_view()->render('select-color-path', array(
                            'post_id'      => $post->ID,
                            'field'        => 'lfh_stroke_color',
                            'value'        => get_post_meta( $post->ID, 'lfh_stroke_color', true ),
                            'colors_path'  => Lfh_Model_Map::$colors_path,
                            'default'      => Lfh_Model_Map::$default['stroke_color']
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
            if( isset($attachment['lfh_download_gpx']) ){
                update_post_meta($post['ID'], 'lfh_download_gpx', true);
            }else{
                update_post_meta($post['ID'], 'lfh_download_gpx', 0);
            }
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
        if(isset( $_POST['attachments'][$post_id ]['lfh_download_gpx']) ){
            update_post_meta($post_id, 'lfh_download_gpx', true);
        }else{
            update_post_meta($post_id, 'lfh_download_gpx', 0);
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
    public function load_editor_scripts()
    {
        if(WP_DEBUG){
            wp_register_style('lfh_gpx_editor_css', Lf_Hiker_Plugin::$url .'/css/lfh-gpx-editor.css', Array(), null, false);
            wp_register_script('lfh_gpx_editor_js', Lf_Hiker_Plugin::$url . '/js/lfh-gpx-editor.js', Array('jquery'), null, false);
            
        }else{
            $version = '.'.Lf_Hiker_Plugin::VERSION;
            wp_register_style('lfh_gpx_editor_css', Lf_Hiker_Plugin::$url .'/dist/lfh-gpx-editor'.$version.'.css', Array(), null, false);
            wp_register_script('lfh_gpx_editor_js', Lf_Hiker_Plugin::$url . '/dist/lfh-gpx-editor-min'.$version.'.js', Array('jquery'), null, false);
        }
        wp_enqueue_style('lfh_gpx_editor_css');
        wp_enqueue_script('lfh_gpx_editor_js');
        $this->script_for_tinyMCE();
        
    }
    public function script_for_tinyMCE()
    {
        $data = ' var lfh_plugin = {
            url : "'.Lf_Hiker_Plugin::$url.'",
            ajax: "'. admin_url('admin-ajax.php').'",
            langage: {
                addMarker : "'. __('Add marker', 'lfh').'"
            }
        }';
        
        wp_add_inline_script('lfh_gpx_editor_js', $data, 'before');
        return;
        
    }
    /**
     * Create the checkbox for gpx field download button in post edition page
     * @param integer $post_id
     * @param boolean|integer $value
     * @return string
     */
    private function create_field_download_gpx( $post_id, $value ){
        
        $checked = $value ? 'checked="checked"': '';
        
        $html = '<input type="checkbox" name="attachments['. $post_id .'][lfh_download_gpx]"';
        $html .= ' id="attachments['. $post_id .'][lfh_download_gpx]" ';
        $html .= ' value="' .$value .'" ';
        $html .= $checked . '  />';
        
        return $html;
    }
}