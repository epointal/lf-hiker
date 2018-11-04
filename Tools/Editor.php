<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Lfh_Tools_Editor
{
    public function __construct( $unactive)
    {
       $editorMap = new Lfh_Tools_Editor_Map();
       $editorGpx = new Lfh_Tools_Editor_Gpx();
       if(version_compare(get_bloginfo('version'),'5.0', '<') ) {
         // helper for old version
         $editorHelper = new Lfh_Tools_Editor_Helper($unactive);
       }
    }
}