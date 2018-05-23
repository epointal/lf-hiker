<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Model_Map{
    public static $default = array(
            'stroke_color' => 'blue',
            'stroke_width' => 5,
    );
 
    public static $colors_marker = array('red', 'blue', 'green', 'purple', 'orange', 'darkred', 'lightred',  
        'darkblue', 'darkgreen', 'cadetblue', 'darkpurple',   'lightblue', 'lightgreen',
        'gray', 'black', 'lightgray');
    
    public static $icons_marker =  array('circle', 'square', 'dot-circle-o', 'circle-o', 'record', 'asterisk', 'star', 'first-order', 'bolt', 'sun-o', 'sun-inv', 'cube', 'bullseye', 'eye', 'heart','flag','leaf',
        'paw', 'key','music', 'cloud', 'close', 'tree', 'smile-o', 'snowflake-o', 'diamond', 'binoculars', 'shopping-basket', 'plane', 'cab',
        'bicycle', 'train', 'bed', 'shower', 'cutlery', 'coffee', 'beer', 'glass', 'spoon', 'bell',
        'home', 'wheelchair', 'school', 'child', 'female', 'male',   'bank', 'industry');
    
    public static $colors_path = array('red', 'blue', 'purple', 'orange', 'darkred', 'darkblue', 'darkgreen', 'black');
    
    public static $distance_units = array( 'km', 'miles');
    
    public static $class_map = array('simple-border', 'thick-border',
                                    'simple-border-round', 'thick-border-round',
                                    'left', 'right', 'center'
    );
    
    public static $tiles = array(
            'osm'         => array( 
                        'label'         => 'OpenStreetMap',
                        'url'           => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'attribution'   => ' &copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
                        'min_zoom'      => 1, 
                        'max_zoom'      => 18,
                        'need_key'      => false),
            'osm_fr'      => array(
                        'label'         => 'OpenStreetMap FR',
                        'url'           => '//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
                        'attribution'   => 'donn&eacute;es &copy; <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                        'min_zoom'      => 1,
                        'max_zoom'      => 20,
                        'need_key'      => false),
            'arcgis_topo' => array(
                        'label'         => 'Arcgis topo',
                        'url'           => '//server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
                        'attribution'   => 'Tiles © <a href="https://services.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer">ArcGIS</a>',
                        'max_zoom'      => 18,
                        'need_key'      => false),
            'stamen_water'=> array(
                        'label'         => 'Stamen Water',
                       // 'url'           =>  'https://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg',
                        'url'           => '//stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg',
                        'attribution'   => 'by <a href="http://stamen.com">Stamen Design</a> Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> Contributors | <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>',
                        'max_zoom'      => 13,
                        'need_key'      => false),
            'stamen_terrain'=> array(
                        'label'         => 'Stamen Terrain',
                        'url'           => '//stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg',
                        'attribution'   => 'by <a href="http://stamen.com">Stamen Design</a> Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> Contributors | <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>',
                        'min_zoom'      => 1,
                        'max_zoom'      => 14,
                        'need_key'      => false),
            'opentopomap'      => array(
                    'label'         => 'OpenTopoMap',
                    'url'           => '//{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                    'attribution'   => 'Kartendaten: © <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>-Mitwirkende, <a href="http://viewfinderpanoramas.org">SRTM</a> | Kartendarstellung: © <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)',
                    'min_zoom'      => 1,
                    'max_zoom'      => 20,
                    'need_key'      => false),
    );
    
    public static function distance_units(){
        return array(
            'km' => array(
                'label' => __('kilometer', 'lfh'),
                'code'  => __('km', 'lfh')
                ),
            'mi' => array(
                'label' => __('milles', 'lfh'),
                'code'  => __('mi', 'lfh')
            ));
    }
    public static function height_units(){
        return array(
                'm' => array(
                        'label' => __('meter', 'lfh'),
                        'code'  => __('m', 'lfh')
                ),
                'ft' => array(
                        'label' => __('foot', 'lfh'),
                        'code'  => __('ft', 'lfh')
                ));
    }
   public static  function map_parameters()
   {
     return   array(
  
            'autocenter' => array(
                'type'    => 'checkbox',
                'label'   => __('Position auto', 'lfh'),
                'default' => true,
                'filter'    => FILTER_CALLBACK,
                'options'   => 'Lfh_Model_Map::validate_boolean'),
            'lat'   => array(
                'type'      => 'custom',
                'label'     => 'Lat',
                'filter'    => FILTER_VALIDATE_FLOAT,
                'options' => array(
                       'default'   => 48.866667
                ),
                'dependency'=> array('autocenter')),
            'lng'   => array(
                'type'      => 'hidden',
                'label'     => 'Lng',
                'filter'    => FILTER_VALIDATE_FLOAT,
                'options'   => array(
                        'default'   => 2.333333 )),
            'zoom'  => array(
                'type'      => 'hidden',
                'label'     => 'Zoom',
                'filter'    =>  FILTER_VALIDATE_INT,
                'options'   => array(
                       'default'   => 13,
                       'min_range' => 1,
                       'max_range' => 20 )),
            'width' => array(
                'type'      => 'number',
                'label'     => __('Width', 'lfh'),
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array(
                       'default'   => 100,
                       'min_range' => 30,
                       'max_range' => 100,
                       'step_range'=> 5),
                'after'    => ' %'),
            'height' => array(
                'type'      => 'number',
                'label'     => __('Height', 'lfh'),
                'filter'    => FILTER_VALIDATE_INT,
                'options'   => array(
                       'default'   => 500,
                       'min_range' => 100,
                       'max_range' => 1000 ,
                        'step_range'=> 50),
                'after'   => ' px'),
            'class' => array(
                 'type'      => 'datalist',
                 'label'     => 'classname',
                 'list'      => self::$class_map,
                 'default'   => '',
                 'filter'    => FILTER_CALLBACK,
                 'options'   => 'Lfh_Model_Map::valid_class'),

            'fullscreen' => array(
                'type'      => 'checkbox',
                'label'     => __('Fullscreen', 'lfh'),
                'default'   => Lfh_Model_Option::get_option('lfh_button_fullscreen')?true:false,
                'filter'   => FILTER_VALIDATE_BOOLEAN,
                'options'   => 'FILTER_NULL_ON_FAILURE'
                ),
            'reset' => array(
                'type'      => 'checkbox',
                'label'     => ucfirst(__('reset', 'lfh')),
                'default'   => true,
                'filter'    => FILTER_CALLBACK,
                'options'   => 'Lfh_Model_Map::validate_boolean'
                ),
            'list' => array(
                'type'      => 'checkbox',
                'label'     => ucfirst(__('list' , 'lfh')),
                'default'   => true,
                'filter'    => FILTER_CALLBACK,
                'options'   => 'Lfh_Model_Map::validate_boolean'
                ),
            'mousewheel' => array(
                'type'      => 'checkbox',
                'label'     => __('Zoom on wheel', 'lfh'),
                'default'   => false,
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_NULL_ON_FAILURE),
            'tile' => array(
                 'type'      => 'select',
                 'label'     => __('Tiles', 'lfh'),
                 'default'   => Lfh_Model_Option::get_option('lfh_default_tiles'),
                 'list'      => self::get_valide_tiles(),
                 'filter'    => FILTER_CALLBACK,
                 'options'   => 'Lfh_Model_Map::valid_tile'
                 ),
             'open' => array( 
                 'type'      => 'checkbox',
                 'label'     => ucfirst(__('Open profile automaticaly' , 'lfh')),
                 'default'   => boolval( Lfh_Model_Option::get_option('lfh_open_profile')),
                 'filter'    => FILTER_VALIDATE_BOOLEAN,
                 'flags'   => FILTER_NULL_ON_FAILURE
             )
        );
   }
  
  
    public static function to_string($lat)
    {
        $num = abs($lat);
        $size = 2- intval( log10( $num )); //useless align right do the work
        $str = str_repeat(' ',$size) . ($lat<0 ? '-':' ').intval($num).'° ';
        $num = ($num - intval($num))*60;
        $size = 1- intval( log10 ( $num));
        $str .= str_repeat('0',$size).intval($num).'\' ';
        $num = ($num - intval($num))*60;$size = 1- intval( log10 ( $num));
        $str .= str_repeat('0',$size). intval($num).'\"';
        return stripslashes($str);
        
    }
    
    public static function filter_map_data($atts)
    {
      
        $args = self::map_parameters();
       //add key if not exists
        $first = filter_var_array($atts, $args, true);
        //put default value where null, use this solution
        //because trouble with default true when null for boolean
        array_walk($first, 'Lfh_Model_Map::set_default', $args);
        return $first;
    }
    
    public static function filter_gpx_data($atts)
    {
        if ($atts) {
            extract($atts);
        }
        if(!isset($src) || empty($src)){
            return null;
        }

        $options = array(
                'src'        => $src,
                'title'      => isset($title) && !empty($title) ? $title:  strtoupper(__('no named gpx', 'lfh')),
                'color'      => isset($color) ? $color: self::$default['stroke_color'],
                'width'      => isset($width) ? $width: self::$default['stroke_width'],
                'unit'       => isset($unit)  ? $unit:  Lfh_Model_Map::is_distance_unit(),
                'unit_h'     => isset($unit_h)  ? $unit_h:  Lfh_Model_Map::is_height_unit(),
                'step_min'   => isset($step_min)? $step_min: Lfh_Model_Option::get_option('lfh_step_min'),
                'button'     => isset($button)  ? self::to_bool($button): boolval(Lfh_Model_Option::get_option('lfh_download_gpx'))
        );
  
        $args = array(
                'src'   => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_url'
                ),
                'title' => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::clean_string'
                ),
                'color' => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_path_color'),
               
                'width' => array(
                        'filter'    => FILTER_VALIDATE_INT,
                        'options'   => array(
                                'default'   => self::$default['stroke_width'],
                                'min_range' => 1,
                                'max_range' => 10 )
                ),
                'unit' => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_distance_unit'
                
                ),
                'unit_h' => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_height_unit'
                ),
                'step_min' => array(
                        'filter'    => FILTER_VALIDATE_INT,
                        'options'   => array(
                                'default'   => Lfh_Model_Option::get_option('lfh_step_min'),
                                'min_range' => 10,
                                'max_range' => 500 )
                ),
                'button' => array(
                        'filter'    => FILTER_VALIDATE_BOOLEAN
                )
        );
      
        $return = filter_var_array($options,$args);

        if( $return['src'] === false){
            return null;
        }
        return $return;
      
        
    }
    public static function to_bool( $var ){
        if( strtolower( $var) === "true" || $var === '1' || $var === 1 || $var===true){
            return true;
        }else{
            return false;
        }
        
    }
    public static function filter_marker_data($atts)
    {
        if (!empty($atts)) extract($atts);
        
        //without position return null
        if(!isset($lat) || !isset($lng)){
            return null;
        }

        $options = array(
                'lat'   => $lat,
                'lng'   => $lng,
                'title' => (isset($title) && !empty( $title )) ? $title : strtoupper( __('no named marker', 'lfh')),
                'visibility' => isset($visibility) ? $visibility : 'always',
                'color' => isset($color) ? $color : 'red',
                'icon'  => isset($icon) ? $icon : 'circle',
                'popup' => isset($popup) ? $popup : ""
        );
        
        $args = array(
                'lat'   => FILTER_VALIDATE_FLOAT,
                'lng'   => FILTER_VALIDATE_FLOAT,
                'title' => array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::clean_string'
                ),
                'visibility' => array(
                        'filter'    =>  FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_visibility'
                ),
                'color' => array(
                        'filter'    =>  FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_color_marker'
                ),
                'icon'  => array(
                        'filter'    =>  FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::is_icon_marker'
                ),
                'popup' =>  array(
                        'filter'    => FILTER_CALLBACK,
                        'options'   => 'Lfh_Model_Map::clean_string'
                )
        );
        
        $arr = filter_var_array($options, $args);
        return $arr;
    }
    public static function is_path_color($var)
    {
        if(in_array(strtolower($var), self::$colors_path)
                || preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $var)){
            return strtolower($var);
        }else{
            return self::$default['stroke_color'];
        }
    }
    
    public static function is_distance_unit($var = null){
        $units = array_keys( self::distance_units());
        if(in_array($var, $units)){
            return $var;
        }else{
            return  get_option('lfh_distance_unit', $units[0]);
        }
    }
    public static function is_height_unit($var = null){
        $units = array_keys( self::height_units());
        if(in_array($var, $units)){
            return $var;
        }else{
            return  get_option('lfh_height_unit', $units[0]);
        }
    }
    public static function valid_tile($var)
    {
        if(in_array(strtolower($var), self::get_valide_tiles())){
            return strtolower($var);
        }else{
            return 'osm';
        }
    }
    private static function is_button_download( $var ){
       // if( )
       return true;
    }
    private static function is_url( $url ){
       if( (strpos( $url, '.gpx') === false && strpos($url, '.GPX') === false)){
           return false;
       }
       //$url = utf8_encode($url);
       
       //pour les changements de http à https et inversement
       $url = str_replace( "http:", "", $url);
       $url = str_replace( "https:", "", $url);

        return $url;
    }
 
    private static function validate_boolean( $bool){
        if( is_null($bool)){
            return true;
        }else if( strtolower( $bool) == "false"){
            return false;
        }else{
            return boolval($bool);
        }
    }
   //Specific filters for map data
    private static function is_color_marker($var)
    {
        if(in_array(strtolower($var), self::$colors_marker)){
            return strtolower($var);
        }else{
            return 'red';
        }
    }
    
    private static function is_icon_marker($var)
    {
        if(in_array(strtolower($var), self::$icons_marker)){
            return strtolower($var);
        }else{
            return 'circle';
        }
    }
    
    private static function clean_string($var)
    {
        return addslashes(stripslashes($var));
    }
    
    private static function valid_class($var)
    {
        return stripslashes($var);
    }
    private static function get_valide_tiles()
    {
        $tiles = array_keys(self::$tiles);
        //unactive mapquest
       // if( !empty(get_option('lfh_mapquest_key'))){
        //    $tiles[] = 'mapquest';
       // }
        return $tiles;
    }
  
    private static function is_visibility($var)
    {
        if(strtolower($var) === 'zoom'){
            return 'zoom';
        }else{
            return 'always';
        }
    }
    

    private static function set_default(&$value, $key ,&$args)
    {
        if(is_null($value)){
            if(isset($args[$key]['default'])){
                $value = $args[$key]['default'];
            }elseif(isset($args[$key]['options']['default'])){
                $value = $args[$key]['options']['default'];
            }
        }
    }
   
}