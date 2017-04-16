<?php
/**
 * Manage the plugin in admin: installation, editing options, 
 * and functions for edit post, and gpx file, 
 * @author epointal
 */

Class Lfh_Controller_Admin
{
    private static $_instance;
    private $_view = null;
    
    private function __construct(){

        add_action('admin_menu' , array(&$this , 'admin_menu'));
        add_action( 'edit_user_profile_update', array('Lfh_Controller_Back', 'update_helper_unactive'));
    }
    
    private static function set_xframe_options(){
        $content = array();
        $content[] = '<IfModule mod_headers.c>';
        $content[] = 'Header set X-Frame-Options SAMEORIGIN';
        $content[] = '</IfModule>';
        $success = insert_with_markers(get_home_path().'.htaccess', 'Lf-hiker plugin',$content);
        if($success){
            return array('msg' => __('Add directive in .htaccess with success', 'lfh'));
        }else{
            return array('error' => __(' Failure. Can not write in .htaccess', 'lfh'));
        }
    }
    private static function remove_xframe_options(){
        $success = insert_with_markers(get_home_path().'.htaccess', 'Lf-hiker plugin','');
        if($success){
            return array('msg' => __('Remove directive in .htaccess with success', 'lfh'));
        }else{
            return array('error' => __(' Failure. Can not write in .htaccess', 'lfh'));
        }
    }
    private  function get_view($controller_name = NULL){
        if(is_null($controller_name)){
            if(is_null($this->_view)){
                $this->_view = new Lfh_Tools_View('Admin');
            }
            return $this->_view;
        }else{
            return new Lfh_Tools_View($controller_name);
        }
    }
    public static function get_instance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Lfh_Controller_Admin();
        }
        return self::$_instance;
    }
  
    public function admin_menu () {
        global $submenu;
        add_options_page( 'Lf Hiker Options', 'Lf Hiker', 'manage_options', 'lfh_options_page', array(&$this , 'admin_page') ); 
       // add_submenu_page('lfh_info',  __('Options', 'lfh'), __('Configuration', 'lfh'),
       //         'manage_options', 'lfh_options_page', array(&$this , 'admin_page'));
    }
    
    public function admin_page ( ) {
        $msg = null;
        $error = null;
        if( isset( $_GET[ 'tab' ] ) ) {
            $active_tab = $_GET[ 'tab' ];
        }else{
            $active_tab ="custom_css";// end if
        }
        if (isset($_POST['lfh_config_submit'])) {
            $success = Lfh_Model_Option::save_data($active_tab, $_POST);
            extract($success);
        } elseif (isset($_POST['lfh_config_reset'])) {
            $success = Lfh_Model_Option::reset_data($active_tab);
            extract($success);
        }elseif (isset($_POST['lfh_xframe_options'])){
            //add in htaccess
            $success = self::set_xframe_options();
            extract($success);
        }elseif(isset($_POST['lfh_xframe_options_remove'])){
            $success = self::remove_xframe_options();
            extract($success);
        }elseif(isset( $_POST['lfh_clear_cache'])){
            $success = Lfh_Tools_Cache::clear_cache_dir();
            extract($success);
        }
        
        Lfh_Tools_Notice::display($msg, 'success');
        Lfh_Tools_Notice::display($error, 'error');
        
        $options = Lfh_Model_Option::get_options($active_tab);

       echo $this->get_view()->render('settings', array(
                'tabs'      => Lfh_Model_Option::get_tabs(),
                'active_tab'=> $active_tab,
                'error'     => $error,
                'msg'       => $msg,
                'options'   => $options
        ));
    }
    
    public  static function activate () {
       //add default value in db if not exists
       $tabs = Lfh_Model_Option::get_tabs();
        foreach($tabs as $tab => $label){
            $defaults = Lfh_Model_Option::get_options($tab);
            foreach($defaults as $name=>$atts) {
                update_option( $name, $atts['value'] );
            }
        }
        //create cache dir if not exists
        $success = Lfh_Tools_Cache::create_cache_dir(Lfh_Model_Option::get_option('lfh_cache_parent'));
        
        if(!$success){
            Lfh_Tools_Notice::display(__('Failed to create Cache directory', 'lfh'), 'important');
        }
        //set x-frame options in htaccess
        $success = self::set_xframe_options();
        if(!$success){
            Lfh_Tools_Notice::display(__('Failed to write directive in .htaccess. It can blocked the helper editor for markers', 'lfh'), 'important');
        }
    }
    public static function deactivate(){
        Lfh_Tools_Cache::delete_cache_dir();
        insert_with_markers(get_home_path().'.htaccess', 'Lf-hiker plugin','');
        // keep option in db if admin want reactive the plugin
        /* $tabs = Lfh_Model_Option::get_tabs();
         foreach($tabs as $tab => $label){
         $defaults = Lfh_Model_Option::get_defaults($tab);
         foreach($defaults as $name=>$atts) {
         delete_option( $name );
         }
         }*/
    }
    public  static function uninstall () {
        //delete all lf-hiker options
        $tabs = Lfh_Model_Option::get_tabs();
        foreach($tabs as $tab => $label){
            $defaults = Lfh_Model_Option::get_defaults($tab);
            foreach($defaults as $name=>$atts) {
                delete_option( $name );
            }
        }
        // remove cache dir lf-hiker : done when deactivate
        //Lfh_Tools_Cache::delete_cache_dir();
        // remove x-frame options in htaccess: done when deactivate
        //self::remove_xframe_options();
    }
   
}