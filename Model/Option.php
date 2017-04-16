<?php
/**
 * @author epointal
 */
Class Lfh_Model_Option
{
    public static function get_tabs(){
        return array(
                'custom_css'    => array(
                        'label'  => __('Custom css'),
                        'comment'=> ''),
                'config_tile'   => array(
                        'label'  => __('Tiles Layers', 'lfh'),
                        'comment'=> __('You can add tiles layers which need key here', 'lfh')),
                'config_lfh'    => array(
                        'label'  => __('General', 'lfh'),
                        'comment'=> ''));
    }
    public static function get_defaults( $tab){
        switch($tab){
            case 'custom_css':
            return array(
                'lfh_background' => array(
                    'type'    => 'color',
                    'label'   => __('Background color', 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'helper'  => esc_html__('Background for title in track\'s window on map' , 'lfh'),
                    'options' => array( 
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#808080')),
                'lfh_background_sub'=> array(
                    'type'    => 'color',
                    'label'   => '',
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'display' => 'none',
                    'options' => array( 
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#ddddee')),
                'lfh_color'      => array(
                    'type'    => 'color',
                    'label'   => __('Title color', 'lfh'),
                    'helper'  => esc_html__('Font color for title in track\'s window on map' , 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'options' => array( 
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#ffffff')),
                'lfh_selected_path'      => array(
                    'type'    => 'color',
                    'label'   => __('Selected path color', 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'options' => array(
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#00ff00')),
                );
            break;
            case 'config_tile':
            return array(
                'lfh_mapquest_key' => array(
                    'type'    => 'text',
                    'label'   => __('Mapquest key', 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'after'   => '*',
                    'style'   => 'width:300px;',
                    'helper'  => __('If you want MapQuest, you must provide an app key.', 'lfh'). 
                            '<a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank">'.
                            __('Sign up' , 'lfh').
                            '</a>, '. __('then', 'lfh').' <a href="https://developer.mapquest.com/user/me/apps" target="_blank">'.
                            __('Create a new app', 'lfh').'</a>'.
                            __('then supply the &laquo;Consumer Key&raquo; here.', 'lfh'),
                    'options' => array( 
                            'regexp'    => "/^[0-9a-z-A-Z]{9,}$/",
                            'default'   => '')
                ));
            break;
            case 'config_lfh':
            return array(
                'lfh_cache_parent' => array(
                    'type'    => 'text',
                    'label'   => __('Cache dir', 'lfh'),
                    'default' => realpath(WP_CONTENT_DIR),
                    'after'   => DIRECTORY_SEPARATOR.'cache',
                    'filter'  => FILTER_CALLBACK,
                    'style'   => 'width:500px;',
                    'options' => 'Lfh_Model_Option::try_cache_dir')
                );
            break;
            default:
                return array();
        }
        
    }
   public static function get_values($tab){

       $atts = self::get_defaults($tab);
       // search option value in 'db' or default value if not exists
       array_walk($atts, 'Lfh_Model_Option::set_only_value');
       return $atts;
   }
   public static function try_cache_dir($dir){
       //@todo
       //try to create cache dir
       $dir = Lfh_Tools_Cache::create_cache_dir($dir);
       //Success : delete old cache dir
       if($dir!==false && $dir !=  get_option('lfh_parent_cache')){
           Lfh_Tools_Cache::delete_cache_dir();
           return $dir;
       }else{
           return get_option('lfh_parent_cache');
       }
   }

   public static function save_data($tab , $data){
       //filter 
       $filter = self::get_defaults($tab);
       $data = filter_var_array($data, $filter);
       foreach ($data as $name=>$atts) {
           update_option($name, $data[$name] );
       }
       switch($tab){
           case 'config_tile':
           Lfh_Tools_Cache::clear_cache_dir();
           break;
           case 'custom_css':
               self::update_inherit_color();
       }
       
       return array('msg' => __('data updated', 'lfh'), 'error' => null);
   }
   public static function reset_data($tab){
       if($tab == 'config_lfh'){
           //delete old cache
           Lfh_Tools_Cache::delete_cache_dir();
       }
       $data = self::get_defaults($tab);
       foreach( $data as $name=>$atts ){
           $default = isset($atts['default'])? $atts['default']:$atts['options']['default'];
           update_option($name, $default );
       }
       if( $tab == 'config_tile'){
           Lfh_Tools_Cache::clear_cache_dir();
       }
       if($tab == 'config_lfh'){
           //create new cache
           Lfh_Tools_Cache::create_cache_dir();
       }
       
       return array('msg' => __('data reseted', 'lfh'), 'error' => null);
   }
   public static function get_option($key){
       //only for lfh_cache_parent
       if(get_option($key)=== false){ 
           return self::get_defaults('config_lfh')[$key]['default'];
       }else{
           return get_option($key);
       }
   }
   //return all the key and value and filter ... for a particular tab
   public static function get_options($tab){
       $atts = self::get_defaults($tab);
       // search option value in 'db' or default value if not exists
       array_walk($atts, 'Lfh_Model_Option::set_value');
       return $atts;
   }
   private static function set_value(&$field, $key){
       $default = isset($field['default'])? $field['default']:$field['options']['default'];
       $field['value'] = get_option($key, $default);
   }
   private static function set_only_value(&$field, $key){
       $default = isset($field['default'])? $field['default']:$field['options']['default'];
       $field = get_option($key, $default);
   }
   
   private static function update_inherit_color(){
       //lighter lfh_background
       update_option('lfh_background_sub', self::lighter_darker(get_option('lfh_background'), 25));
   }
   private static function lighter_darker($hex, $pct) {
       // Steps should be between -255 and 255. Negative = darker, positive = lighter
       preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $hex, $parts);
       $out = "#"; // Prepare to fill with the results
       for($i = 1; $i <= 3; $i++) {
           $parts[$i] = hexdec($parts[$i]);
           $parts[$i] = min(255 ,round($parts[$i] * (1 + $pct/100)));
           $out .= str_pad(dechex($parts[$i]), 2, '0', STR_PAD_LEFT);
       }
       return $out;
   }
   
}