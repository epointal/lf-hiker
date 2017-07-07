<?php
//@see http://www.easyrgb.com/en/math.php  for  color conversion 

if ( ! defined( 'ABSPATH' ) ) exit;

Class Lfh_Tools_Color{
    public static function lighter_darker($hex, $pct) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $parts = self::hex2rgb($hex);
        for($i = 0; $i < 3; $i++) {
            $parts[$i] = min(255 ,round($parts[$i] * (1 + $pct/100)));
           
        }
       return self::rgb2hex($parts);
    }
    
    /**
     * 
     * @param {string} $hex color hexadecimal
     * @param {integer} $pct percentage
     * @return {string} color saturate hexadecimal
     */
    public static function saturate($hex, $pct){
        $rgb = self::hex2rgb( $hex );
        $hsl = self::rgb2hsl( $rgb );
        // change saturation
        $hsl[1] = $hsl[1] * (1 + $pct/100);
        if( $hsl[1] > 1){
            $hsl[1] = 1;
        }
        $rgb = self::hsl2rgb( $hsl );
        return self::rgb2hex( $rgb );
    }
    
    public static function hex2rgb( $hex){
        preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $hex, $rgb);
        $rgb = array_slice( $rgb, 1, 3);
        for($i = 0; $i < 3; $i++) {
            $rgb[$i] = hexdec($rgb[$i]);
        }
        return $rgb;
    }
    
    public static function rgb2hex( $rgb){
        $hex = "#";
        for($i = 0; $i < 3; $i++) {
            $hex .= str_pad(dechex($rgb[$i]), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }
    public static function rgb2hsl( $rgb){
        if(!function_exists("unify")){
            function unify ($n){
                return $n/255;
            }
        }
       
        $rgb = array_map( "self::unify", $rgb);
        $min = min( $rgb );
        $max = max( $rgb );
        $delta_max = $max - $min;
        
        $l = ( $max + $min )/2;
        
        if( $delta_max === 0 ){
            $h = 0;
            $s = 0;
        }else{
            if ( $l < 0.5 )
            {
                $s = $delta_max / ( $max + $min );
            }else{
                $s = $delta_max / ( 2 - $max - $min );
            }
            $delta = array_map( "self::delta", $rgb, array_fill(0,3,$max), array_fill(0,3,$delta_max) );
            if( $rgb[0] == $max )
            {
                $h = $delta[2] - $delta[1];
            }
            elseif ( $rgb[1] == $max )
            {
                $h = ( 1 / 3 ) + $delta[0] - $delta[2];
            }
            elseif ( $rgb[2] == $max  )
            {
                $h = ( 2 / 3 ) + $delta[1] - $delta[0];
            }
            
            if ( $h < 0 ){
                $h += 1;
            }
            if ( $h > 1 ){
                $h -= 1;
            }
        }
        return array( $h, $s, $l);
    
    }
    
    public static function hsl2rgb( $hsl)
    {
        if ( $hsl[ 1 ] == 0 )
        {
            $rgb = array_fill( 0, 3 , $hsl[2] / 255);
        }
        else
        {
            if ( $hsl[2] < 0.5 )
            {
                $var_2 = $hsl[2] * ( 1 + $hsl[1] );
            }
            else
            {
                $var_2 = ( $hsl[1] + $hsl[2] ) - ( $hsl[1] * $hsl[2] );
            }
        
            $var_1 = 2 * $hsl[2] - $var_2;
        
            $r = 255 * self::hue2rgb( $var_1, $var_2, $hsl[0] + ( 1 / 3 ) );
            $g = 255 * self::hue2rgb( $var_1, $var_2, $hsl[0] );
            $b = 255 * self::hue2rgb( $var_1, $var_2, $hsl[0] - ( 1 / 3 ) );
            
            $rgb = array( $r, $g, $b);
        }
        return $rgb;
    }
   
    
    private static function delta( $col, $max, $delta_max )
    {
        return ( ( ( $max - $col ) / 6 ) + ( $delta_max / 2 ) ) / $delta_max;
    }
    private static function hue2rgb( $var_1, $var_2, $h)
    {
        if ( $h < 0 ){
            $h += 1;
        }
        if( $h > 1 ){
            $h -= 1;
        }
        if ( ( 6 * $h ) < 1 ){
            return ( $var_1 + ( $var_2 - $var_1 ) * 6 * $h );
        }
        if ( ( 2 * $h ) < 1 ){
            return $var_2;
        }
        if ( ( 3 * $h ) < 2 ){
            return ( $var_1 + ( $var_2 - $var_1 ) * ( ( 2 / 3 ) - $h ) * 6 );
        }
        return $var_1;
    }
    private static function unify( $n )
    {
        return $n/255;
    }
}