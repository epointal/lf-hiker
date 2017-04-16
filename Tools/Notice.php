<?php
class Lfh_Tools_Notice
{
    public static function display( $text, $mode='success'){
        if(!empty($text)){
            switch($mode){
                case 'error':
                    $class = 'class="error notice is-dismissible"';
                    break;
                case 'important':
                   $class = 'class="update_nag notice is-dismissible"';
                    break;
                case 'success':
                default:
                    $class = 'class="updated notice is-dismissible"';
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