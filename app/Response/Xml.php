<?php

/**
 * 
 */

namespace CC\Api\Response;

    use CC\Api\Response;

class Xml {

    /**
     * Parse array as CSV
     * @param array $response PHP array
     */
    
    public static function get( $response = [] ){
        
        /** Properties */

        $doc = new \DOMDocument( '1.0' );
        $doc -> preserveWhiteSpace = false;
        $doc -> formatOutput = true;

        /** Parse XML */

        $doc -> loadXML( self::xmlBuild( $response, new \SimpleXMLElement( '<Root/>' ) ) -> asXML() );

        /** Return */

        return $doc -> saveXML(); }

    /**
     * Recursive XML builder
     * @param array
     * @param SimpleXMLElement
     */
    
    public static function xmlBuild( $response = [], \SimpleXMLElement $xml ){

        /** Parse children as XML */
            
        foreach( is_array( $response ) ? $response : [ $response ] as $key => $value ){

            $key = is_numeric( $key ) ? 'Item' : $key; ( is_array( $value ) ) ?

                self::xmlBuild( $value, $xml -> addChild( $key ) ) : $xml -> addChild( $key, htmlentities( $value ) ); }

        /** Return */

        return $xml; }

}

?>