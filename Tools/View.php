<?php
/**
 * 
 * @author epointal
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Tools_View{
    private $_controller;
    private $_dir_view;
    private $_fields = array();
    
    public function __construct($controller_name, $dir_view = null){
        $this->_controller = strtolower($controller_name);
        if(is_null($dir_view)){
            // Lf_Hiker_Plugin is necessary loaded
            $this->_dir_view = realpath(Lf_Hiker_Plugin::$path .'/views/');
        }else{
            $this->_dir_view = $dir_view;
        }
    }
    
    public function assign($key, $value){
        $this->_fields[ $key ] = $value;
    }
    
    public function clear_fields(){
        $this->_fields = array();
    }
 
    public function render($template_name, $fields) {
        $fields = array_merge($fields, $this->_fields);
        extract($fields);
        ob_start();
        include realpath($this->_dir_view .'/'.$this->_controller. '/'.$template_name.'.phtml');
        //return (new Lfh_Tools_Compress(ob_get_clean()))->toString();
        return ob_get_clean();
    }
}