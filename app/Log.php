<?php

namespace CC\Api;

use \Codedungeon\PHPCliColors\Color;

class Log {
 
    /**
     * Set/Get global Api options
     * @param string|null $string Log string
     * @param string $type Log type for coloring
     */
    
    public static function log( $string, $type = null, $header = false ){

        /** Set color */

        switch( $type ){

            case 'info': $color = Color::WHITE; break;
            case 'title': $color = Color::BLUE; break;
            case 'section': $color = Color::CYAN; break;
            case 'warning': $color = Color::YELLOW; break;
            case 'error': $color = Color::RED; break;
            case 'success': $color = Color::GREEN; break;
            case 'highlight': $color = Color::MAGENTA; break;
            default: $color = Color::RESET; break; }

        /** Typecast string */

        $string = ! is_string( $string ) ? json_encode( $string, JSON_PRETTY_PRINT ) : $string;

        /** Append lines if header */

        $originalString = $string; if( $header ){

            $string = PHP_EOL . $string . PHP_EOL; for( $i = 0; $i < strlen( $originalString ); $i++ ){ $string .= '-'; } }

        /** Return string */

        echo $color, $string, Color::RESET, PHP_EOL; }

    /**
     * 
     * Shortcut methods
     * 
     */

    public static function title( $string ){

        self::log( $string, 'title', true ); }

    public static function section( $string ){

        self::log( $string, 'section', true ); }

    public static function info( $string ){

        self::log( $string, 'info' ); }

    public static function warning( $string ){

        self::log( $string, 'warning' ); }

    public static function error( $string ){

        self::log( $string, 'error' ); }

    public static function success( $string ){

        self::log( $string, 'success' ); }

    public static function highlight( $string ){

        self::log( $string, 'highlight' ); }

}
