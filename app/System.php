<?php

/**
 * 
 */

namespace CC\Api;

class System {
    
    /**
     * Set/Get global Api options
     * @param string|null $key option name
     * @param string $value Set option value (optional)
     */
    
    public static function update( $key = false, $value = null ){

        /** Set multiple options */

        if( is_array( $key ) ){

            foreach( $key as $option => $value ){ self :: set( $option, $value ); } }

        /** Set single option */

        else {
            
            if( $value !== null ){ self :: $options[ strtolower( $key ) ] = $value; }
        
        /** Return option(s) */

        return is_string( $key ) ? self :: get( $key ) : null; }
        
    }
    
    /**
     * Get option value
     * @param  string $key option key
     */
    
    public static function get( string $key ){
        
        /** Return option(s) */

        return array_change_key_case( self :: $options, CASE_LOWER )[ strtolower( $key ) ] ?? null; }

}

?>