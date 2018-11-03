<?php
/**
 * Static functions to register scripts
 * @author elisabeth
 *
 */
Class Lfh_Tools_Registrer{
    public static function register_leaflet() {
        $cdn = Lfh_Model_Option::get_option("lfh_use_cdn");
        if( $cdn){
            wp_register_style( 'leaflet_css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/'. Lf_Hiker_Plugin::LEAFLET_VERSION .'/leaflet.css',  null, null );
            wp_register_script('leaflet','https://cdnjs.cloudflare.com/ajax/libs/leaflet/' .Lf_Hiker_Plugin::LEAFLET_VERSION. '/leaflet.js',Array(),null, true);
        }else{
            wp_register_style( 'leaflet_css', Lf_Hiker_Plugin::$url.'lib/leaflet/'. Lf_Hiker_Plugin::LEAFLET_VERSION .'/leaflet.css',  null, null );
            wp_register_script('leaflet', Lf_Hiker_Plugin::$url.'lib/leaflet/' .Lf_Hiker_Plugin::LEAFLET_VERSION. '/leaflet.js',Array(),null, true);
        }
        wp_register_script('awesome_marker_js',Lf_Hiker_Plugin::$url. "lib/awesome-marker/leaflet.awesome-markers.min.js", Array('leaflet'), null, true);
        return array( 'leaflet', 'awesome_marker_js');
    }
    public static function enqueue_leaflet() {
        wp_enqueue_style('leaflet_css');
        wp_enqueue_script('leaflet');
        wp_enqueue_script('awewome_marker_js');
        return array( 'leaflet', 'awesome_marker_js');
    }
}