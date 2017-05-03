<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Lfh_Tools_Notice
{
    public static function display( $text, $mode='success'){
        if(!empty($text)){
            switch($mode){
                case 'error':
                    $class = 'class="error notice notice-error is-dismissible"';
                    break;
                case 'important':
                   $class = 'class="notice notice-warning is-dismissible"';
                    break;
                case 'success':
                default:
                    $class = 'class="updated notice notice-success is-dismissible"';
                    break;
            }
            self::display_notice($text, $class);
        }
    }
    
    private static function display_notice($text , $class){
    ?>
        <div id="message" <?=$class?>>
            <p><?=$text?></p>
            <button type="button" class="notice-dismiss">
            <span class="screen-reader-text"><?=__('close')?></span>
            </button>
        </div>
    <?php    
    }
   
}