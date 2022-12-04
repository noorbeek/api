<?php

/**
 * 
 */

namespace CC\Api\Response;

    use CC\Api\Response;

class Csv {

    /**
     * Parse array as CSV
     * @param array $response PHP array
     */
    
    public static function get( $response = [] ){
        
        /** Defaults */

        $charset = Response::getHeaders( 'Charset' ) ? Response::getHeaders( 'Charset' ) : 'utf-8';
        $csv = [];
        $head = [];
        $export = "";
        $count = 0;

        /** Convert to flat array */

        foreach( is_array( $response ) ? $response : [ $response ] as $item ){

            $csv[] = self::flattenRow( $item ); }

        /** Find deepest array to create head */

        foreach( $csv as $item ){ if( $item[ 'count' ] > $count ){

            $count = $item[ 'count' ]; $head = array_keys( $item[ 'items' ] ); } }

        /** Create head */

        foreach( $head as $item ){

            $export .= self::parseField( $item ) . ';'; } $export .= "\r\n";

        /** Create rows */

        foreach( $csv as $item ){ foreach( $head as $key ){

            $export .= ( isset( $item[ 'items' ][ $key ] ) ? self::parseField( $item[ 'items' ][ $key ] ) : '""' ) . ";"; } $export .= "\r\n"; }

        return ( strtolower( $charset ) === 'utf-8' ? "\xEF\xBB\xBF" : "" ) . $export; }

    /**
     * Parse field for CSV
     * @param  string $field Raw Field
     * @return stinrg        Parsed field
     */
    
    public static function parseField( $field ){
        
        return json_encode( is_string( $field ) ? strip_tags( $field ) : $field, JSON_UNESCAPED_UNICODE ); }

    /**
     * Flatten deep array
     * @param  mixed $response Response data
     * @param  array  $cache    Sub array
     * @param  string $prefix   Optional table prefix
     * @return array            Response
     */
    
    public static function flattenRow( $response, $cache = [], $prefix = null ){

        foreach( is_array( $response ) ? $response : [ $response ] as $key => $value ){

            if( is_array( $value ) && array_values( $value ) !== $value ){

                $cache = array_merge( $cache, self::flattenRow( $value, [], ( $prefix ? $prefix : '' ) . $key )[ 'items' ] );

            } else { $cache[ ( $prefix ? $prefix : '' ) . $key ] = $value; } }

        return [ 'items' => $cache, 'count' => count( $cache ) ]; }

}

?>