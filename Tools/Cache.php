<?php

Class Lfh_Tools_Cache{
  
    public $dirname;
    public $controller;
    
    public function __construct($controller){
       
        $this->controller = strtolower($controller);
        $dirname = get_option('lfh_cache_parent').DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'lf-hiker'.DIRECTORY_SEPARATOR.$this->controller;
        if(!is_dir($dirname)){
            $dir = self::create_cache_dir();
        }
        
        $this->dirname = $dirname;
        return $this;
       // $this->controller = $controller;
    }
    public static function create_cache_dir($dir=null){
       // var_dump(Lfh_Model_Option::get_defaults()['lfh_cache_parent']);
       if(is_null($dir)){
           $dir = Lfh_Model_Option::get_option('lfh_cache_parent');
       }
       $dir = realpath($dir);
       if(!$dir){
           //can't create a directory in a inexistant directory
           return false;
       }
       //$perms = decoct(fileperms($dir) & 0777);
       $success = chmod($dir, 0755);
       if(!$success){
           return false;
       }
       $cache_dir = $dir.  DIRECTORY_SEPARATOR .'cache'. DIRECTORY_SEPARATOR. 'lf-hiker';
        if(!is_dir( $cache_dir)){
            $success = mkdir( $cache_dir, 0755, true);
        }else{
            $success = chmod($cache_dir, 0755);
        }
        if(!$success){
            return false;
        }
        //create cache for the controllers
        $front = $cache_dir . DIRECTORY_SEPARATOR . 'front';
        if(!is_dir($front)){
            $success = mkdir( $front , 0755, true);
        }else{
            $success = chmod($front, 0755);
        }
        if(!$success){
            return false;
        }
        $back = $cache_dir . DIRECTORY_SEPARATOR . 'back';
        if(!is_dir($back)){
            $success = mkdir( $back , 0755, true);
        }else{
            $success = chmod( $back, 0755);
        }
        if(!$success){
            return false;
        }
        //chmod( $dir, $perms);
        return $dir;
    }
    
    public static function delete_cache_dir(){
        $hiker_cache = get_option('lfh_cache_parent'). DIRECTORY_SEPARATOR.'cache' . DIRECTORY_SEPARATOR. 'lf-hiker';
        Lfh_Tools_File::rrmdir( $hiker_cache);
    }
    public static function clear_cache_dir(){
        //delete and create
        self::delete_cache_dir();
       $dir = self::create_cache_dir();
       if( !is_null($dir)){
           return array( 'msg'      => __('Cache clear with success', 'lfh'),
                         'error'    => null
           );
       }else{
           return array( 'msg'      => null,
                         'error'    => __('Failed to clear cache', 'lfh')
           );
       }
    }
    public  function clear(){
        $files = glob($this->dirname.'/*');
        foreach($files as $file){
            unlink($file);
        }
    }
    public function exist($filename){
        return file_exists ($this->dirname.'/'.$filename );
    
    }
    public function write($filename, $content){
       return file_put_contents($this->dirname.DIRECTORY_SEPARATOR.$filename,$content);
    
    }
    public function read($filename){
       
        return file_get_contents($this->dirname.DIRECTORY_SEPARATOR.$filename);
    
    }
   
}