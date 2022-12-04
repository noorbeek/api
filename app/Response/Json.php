<?php

/**
 * 
 */

namespace CC\Api\Response;

    use CC\Api\Response;
    use CC\Api\Error;

class Json {

    /** @var array Error Cache for json errors */

    public static $jsonErrors = [];

    /**
     * Parse array as CSV
     * @param array $response PHP array
     */
    
    public static function get( $response = [] ){
        
        /** Make array */
            
        if( ! $response || ! is_array( $response ) ){ $response = [ $response ]; }

        /** Prettify & parse */

        $jsonResponse = json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

        /** Detect error */

        if( json_last_error() ){

            $error = 'Unknown JSON parsing error.'; switch( json_last_error() ){

                case 1: $error = 'JSON_ERROR_DEPTH'; break;
                case 2: $error = 'JSON_ERROR_STATE_MISMATCH'; break;
                case 3: $error = 'JSON_ERROR_CTRL_CHAR'; break;
                case 4: $error = 'JSON_ERROR_SYNTAX'; break;
                case 5: $error = 'JSON_ERROR_UTF8'; break;
                case 6: $error = 'JSON_ERROR_RECURSION'; break;
                case 7: $error = 'JSON_ERROR_INF_OR_NAN'; break;
                case 8: $error = 'JSON_ERROR_UNSUPPORTED_TYPE'; break;

            } print_r( $response ); new Error([ 'description' => $error, 'errors' => self::jsonFindErrors( $response ) ], 422 ); }

        /** Return */

        return $jsonResponse; }

    /**
     * Find json parsing errors (recursive)
     * @param mixed $data  Array or String
     */
    
    public static function jsonFindErrors( $data ){

        /** Convert to UTF8 */

        if( is_array( $data ) ){ foreach( $data as $key => $value ){

            if( is_string( $value ) ){

                $Test = json_encode( $value ); if( json_last_error() ){ self::$jsonErrors[] = [ $key => utf8_encode( $value ) ]; }

            } $data[ $key ] = self::jsonFindErrors( $value ); }

        } else if( is_string( $data ) ){ return utf8_encode( $data ); }

        /** Return */

        return self::$jsonErrors; }

}

?>