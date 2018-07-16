<?php
/**
 * @author epointal
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Model_Option
{
    public static function get_tabs(){
        return array(
                'custom_css'    => array(
                        'label'  => __('Custom css'),
                        'comment'=> ''),
                'config_tile'   => array(
                        'label'  => __('Config units, gpx, tiles', 'lfh'),
                        'comment'=> ''), //__('You can add tiles layers which need key here, or choose unit for distance', 'lfh')),
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
                    'helper'  => esc_html__('Background color for track title' , 'lfh'),
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
                        'default'   => '#707070')),
                'lfh_color'      => array(
                    'type'    => 'color',
                    'label'   => __('Title color', 'lfh'),
                    'helper'  => esc_html__('Font color for track title' , 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'options' => array( 
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#ffffff')),
                'lfh_button_color'      => array(
                        'type'    => 'color',
                        'label'   => __('Button color', 'lfh'),
                        'helper'  => esc_html__('Button color for elements list' , 'lfh'),
                        'filter'  => FILTER_VALIDATE_REGEXP,
                        'options' => array(
                                'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                                'default'   => '#e95325')),
                'lfh_selected_path'      => array(
                    'type'    => 'color',
                    'label'   => __('Selected path color', 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'options' => array(
                        'regexp'    => "/^#[0-9a-f-A-F]{6}$/",
                        'default'   => '#00ff00')),
                'lfh_position_under'      => array(
                    'type'    => 'checkbox',
                    'default' => false,
                    'label'   => __('Display information window always under the map', 'lfh'),
                    'filter'  => FILTER_VALIDATE_BOOLEAN,
                    'helper'  => esc_html__('You can change it for only on map in its shortcode by using property <code>undermap</code>', 'lfh'))
            );
            break;
            case 'config_tile':
                $distance_units = array_keys( Lfh_Model_Map::distance_units());
                $height_units = array_keys(Lfh_Model_Map::height_units());
                $map_tiles = Lfh_Model_Map::$tiles;
            return array(
                 'lfh_download_gpx' => array(
                    'type'    => 'checkbox',
                    'default' => true,
                    'label'   => __('Display button download gpx', 'lfh'),
                    'filter'  => FILTER_VALIDATE_BOOLEAN,
                    'helper'  => esc_html__('You can change it for one gpx in shortcode by using property <code>button</code>', 'lfh')
                 ),
                'lfh_open_profile' => array(
                     'type'   => 'checkbox',
                     'default'=> false,
                     'label'  => __('Open profile window automatically', 'lfh'),
                     'filter' => FILTER_VALIDATE_BOOLEAN,
                     'helper'  => esc_html__('You can change it for one map in shortcode by using property', 'lfh'). ' <code>open</code>'
                        
                ),
                'lfh_number_gpx_for_checkbox'=> array(
                    'type'   => 'select',
                    'select_options' => Lfh_Model_Option::generate_range(0,20,1),
                    'label'  => __('Number of gpx to display a checkbox show/hide', 'lfh'),
                    'default'=> '10',
                    'filter' => FILTER_VALIDATE_INT,
                    'options' => array(
                        'min_range' => 0,
                        'max_range' => 20),
                   // 'helper'  => esc_html__('You can change it for one map in shortcode by using property', 'lfh'). '<code>checkbox</code>'
                 ),
                'lfh_button_fullscreen' => array(
                        'type'   => 'checkbox',
                        'default'=> true,
                        'label'  => __('Display button fullscreen', 'lfh'),
                        'filter' => FILTER_VALIDATE_BOOLEAN,
                        'helper' => esc_html__('You can change it for one map in shortcode by using property', 'lfh'). '<code>fullscreen</code>'
                ),
                'lfh_default_tiles' => array(
                        'type'   => 'select',
                        'select_options' => $map_tiles,
                        'default'=> 'osm',
                        'label'  => __('Default map tiles', 'lfh'),
                        'filter' => FILTER_CALLBACK,
                        'options' => 'Lfh_Model_Map::valid_tile',
                        'helper'  => esc_html__('You can change it for one map in shortcode by using property', 'lfh'). '<code>tile</code>'
                ),
                'lfh_distance_unit'=> array(
                    'type'   => 'select',
                    'select_options' => Lfh_Model_Map::distance_units(),
                    'label'  => __('Default distance unit', 'lfh'),
                    'default'=> $distance_units[0],
                    'filter' => FILTER_CALLBACK,
                    'options'=> 'Lfh_Model_Map::is_distance_unit'
                ),
                'lfh_height_unit'=> array(
                        'type'   => 'select',
                        'select_options' => Lfh_Model_Map::height_units(),
                        'label'  => __('Default height unit', 'lfh'),
                        'default'=> $height_units[0],
                        'filter' => FILTER_CALLBACK,
                        'options'=> 'Lfh_Model_Map::is_height_unit'
                ),
                'lfh_step_min' => array(
                        'type'   => 'select',
                        'select_options' => Lfh_Model_Option::generate_range(10,500,10),
                        'label'  => __('Minimum step on elevation axis in meters ( four steps in general)', 'lfh'),
                        'default'=> 50,
                        'filter' => FILTER_VALIDATE_INT,
                        'options' => array(
                                'min_range' => 10,
                                'max_range' => 500),
                        'helper'  => esc_html__('You can change it for one gpx in shortcode by using property', 'lfh'). ' <code>step_min</code>'
                        
                )
               /* 'lfh_mapquest_key' => array(
                    'type'    => 'text',
                    'label'   => __('Mapquest key', 'lfh'),
                    'filter'  => FILTER_VALIDATE_REGEXP,
                    'after'   => '*',
                    'style'   => 'width:300px;',
                    'attributes' =>'disabled',
                    'helper'  => __('This option is deactived (mapquest using deprecated method).','lfh'). ' '.__('If you want MapQuest, you must provide an app key.', 'lfh'). 
                            '<a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank">'.
                            __('Sign up' , 'lfh').
                            '</a>, '. __('then', 'lfh').' <a href="https://developer.mapquest.com/user/me/apps" target="_blank">'.
                            __('Create a new app', 'lfh').'</a>'.
                            __('then supply the &laquo;Consumer Key&raquo; here.', 'lfh'),
                    'options' => array( 
                            'regexp'    => "/^[0-9a-z-A-Z]{9,}$/",
                            'default'   => '')
                )*/
            );
            break;
            case 'config_lfh':
            return array(
                'lfh_use_cdn' => array(
                    'type'   => 'checkbox',
                    'default'=> true,
                    'label'  => __('Use cdn host for leaflet', 'lfh'),
                    'filter' => FILTER_VALIDATE_INT,
                    'helper' => esc_html__('If unchecked, use local leaflet version', 'lfh')
                    )
                /*'lfh_cache_parent' => array(
                    'type'    => 'text',
                    'label'   => __('Cache dir', 'lfh'),
                    'default' => realpath(WP_CONTENT_DIR),
                    'after'   => DIRECTORY_SEPARATOR.'cache',
                    'filter'  => FILTER_CALLBACK,
                    'style'   => 'width:500px;',
                    'attributes' => 'disabled',
                    'options' => 'Lfh_Model_Option::try_cache_dir')*/
                );
            break;
            
            default:
                return array();
        }
        
    }
    private static function generate_range( $min=0, $max=20, $step=1){
        $result = array();
        for( $i=$min; $i<$max+1; $i +=$step){
            $result[$i] = array('label' => strval($i));
        }
        return $result;
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

       if($dir!=false && $dir !=  get_option('lfh_parent_cache')){
           Lfh_Tools_Cache::delete_cache_dir();
           return $dir;
       }else{
           Lfh_Tools_Notice::display(__('Failed to create a new cache', 'lfh'), 'important');
           return get_option('lfh_parent_cache')? get_otion('lfh_parent_cache'): self::get_defaults('config_lfh')['lfh_cache_parent']['default'];
       }
   }

   public static function save_data($tab , $data){
       //filter 
       if( $tab == 'config_tile'){
           if(  isset( $data["lfh_download_gpx"])){
               $data["lfh_download_gpx"] = true;
           }else{
               $data["lfh_download_gpx"] = false;
           }
           if(  isset( $data["lfh_open_profile"])){
               $data["lfh_open_profile"] = true;
           }else{
               $data["lfh_open_profile"] = false;
           }
           if(  isset( $data["lfh_button_fullscreen"])){
               $data["lfh_button_fullscreen"] = true;
           }else{
               $data["lfh_button_fullscreen"] = false;
           }
       }
       if( $tab == 'config_lfh'){
           if(  isset( $data["lfh_use_cdn"])){
               $data["lfh_use_cdn"] = true;
           }else{
               $data["lfh_use_cdn"] = false;
           }
       }
       if( $tab == 'custom_css'){
           if(  isset( $data["lfh_position_under"])){
               $data["lfh_position_under"] = true;
           }else{
               $data["lfh_position_under"] = false;
           }
       }
    
       $filter = self::get_defaults($tab);
       $data = filter_var_array($data, $filter);
 
       foreach ($data as $name=>$atts) {
           update_option($name, $data[$name] );
       }
       switch($tab){
           case 'config_tile':
          // Lfh_Tools_Cache::clear_cache_dir();
           break;
           case 'custom_css':
               self::update_inherit_color();
           break;
       }
       
       return array('msg' => __('data updated', 'lfh'), 'error' => null);
   }
   public static function reset_data($tab){
       if($tab == 'config_lfh'){
           //delete old cache
          // Lfh_Tools_Cache::delete_cache_dir();
       }
       $data = self::get_defaults($tab);
       foreach( $data as $name=>$atts ){
           $default = isset($atts['default'])? $atts['default']:$atts['options']['default'];
           update_option($name, $default );
       }
       if( $tab == 'config_tile'){
          // Lfh_Tools_Cache::clear_cache_dir();
       }
       if($tab == 'config_lfh'){
           //create new cache
           //Lfh_Tools_Cache::create_cache_dir();
       }
       
       return array('msg' => __('data reseted', 'lfh'), 'error' => null);
   }
   public static function get_option($key){
       //only for lfh_cache_parent
       if(get_option($key)=== false){ 
           if(isset(self::get_defaults('config_tile')[$key]['default'])){
               return self::get_defaults('config_tile')[$key]['default'];
           }
           if(isset(self::get_defaults('config_lfh')[$key])){
               return self::get_defaults('config_lfh')[$key]['default'];
           }
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
       update_option('lfh_background_sub', Lfh_Tools_Color::lighter_darker(get_option('lfh_background'), 10));
   }
  
  
   
}