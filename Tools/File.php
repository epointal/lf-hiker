<?php
class Lfh_Tools_File
{
    public static function rrmdir($dirname){
        //@todo only remove lf-hiker plugin file and directory
        if (is_dir($dirname)) {
            $objects = scandir($dirname);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dirname .'/'. $object))
                        Lfh_Tools_File::rrmdir($dirname.'/'.$object);
                        else
                            unlink($dirname . '/' .$object);
                }
            }
            rmdir($dirname);
        }
     
    }
}