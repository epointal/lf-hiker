<?php
/**
 * Manage the plugin in admin: installation, editing options, 
 * and functions for edit post, and gpx file, 
 * @author epointal
 */
if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Controller_Back
{
    private static $_instance;
    private static $_view = null;
    private $_editor = null;
    private $_controller = null;
    private $_manage = null;
   
    private function __construct(){
        global $pagenow;
        add_action('admin_menu' , array(&$this , 'editor_menu'));
        
        // add configuration for helper markers
        add_action( 'show_user_profile', array( &$this, 'add_infos_user') );
        add_action( 'edit_user_profile', array( &$this, 'add_infos_user') );
        add_action( 'personal_options_update', array(&$this, 'update_helper_unactive' ));
        
        if(in_array( $pagenow, array('admin-ajax.php' , 'post.php', 'post-new.php',
                                  'media-new.php', 'async-upload.php', 'upload.php'))){
            $this->_editor = new Lfh_Tools_Editor( $this->get_helper_unactive());
        }
        
        if(current_user_can('administrator') ){
            $this->_manage = Lfh_Controller_Admin::get_instance();
        }
    }
    public static function get_instance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Lfh_Controller_Back();
        }
        return self::$_instance;
    }
    public static function get_view($controller_name = NULL){
        if(is_null($controller_name)){
            if(is_null(self::$_view)){
                self::$_view = new Lfh_Tools_View('Back');
            }
            return self::$_view;
        }else{
            return new Lfh_Tools_View($controller_name);
        }
    }
    public function editor_menu(){
        global $submenu;
        add_menu_page( 'GPX', 'GPX',
                'edit_posts' ,'lfh_info',array(&$this , 'about_page'), Lf_Hiker_Plugin::$url.'images/icons/marker.png' ,50);
        $data = array(
                'mode'              => 'list',
                'attachment-filter' => 'post_mime_type:application/gpx+xml',
                'filter_action'     => 'Filter',
                'paged'             => 1
        );
        $slug = 'upload.php?' . http_build_query($data);
       // $slug = 'upload.php?mode=list&attachment-filter=post_mime_type%3Aapplication%2Fgpx%2Bxml&m=0&filter_action=Filtrer&s&action=-1&paged=1';
        add_submenu_page('lfh_info',  'GPX' , __('All gpx files', 'lfh'), 'edit_posts', $slug ,null);
        $submenu['lfh_info'][0][0] = ucfirst(__( 'about','lfh' ));
    }
    
    public function about_page(){
        $msg = null;
        $mode = null;
        if( isset( $_GET[ 'tab' ] ) ) {
            $active_tab = $_GET[ 'tab' ];
        }else{
            $active_tab ="about_page";// end if
        }
        if(isset($_POST["lfh_change_helper"])  ){
            $user_id = get_current_user_id();
            $mode = self::update_helper_unactive($user_id);
            $msg = __('Your configuration has been updated', 'lfh');
        }else{
            $mode = $this->get_helper_unactive();
        }
      
      echo  self::get_view()->render('about-page', array(
                'active_tab'      => $active_tab,
                'helper_unactive' => $mode,
                'msg'             => $msg,
                'plugin_url'      => Lf_Hiker_Plugin::$url
        ));
    }
    //for all users
    public static function add_infos_user( $user )
    {
        $mode = empty(get_user_meta( $user->ID, 'lfh_helper_unactive', $user->ID, true))? false:true;
    	echo  self::get_view()->render('user', array(
    		    'title'           => 'Lf Hiker',
                'helper_unactive' => $mode
        ));
    }
    /**for all users */
    public static function update_helper_unactive($user_id){
        $mode = isset($_POST['lfh_helper_unactive'])? true : false;
        update_user_meta( $user_id, 'lfh_helper_unactive', $mode );
        return $mode;
    }
    /** for current user */
    public  function get_helper_unactive(){
        $mode = get_user_meta( get_current_user_id() , 'lfh_helper_unactive', true);
        if(empty( $mode)){
            return false;
        }else{
            return $mode; 
        }
    }
}