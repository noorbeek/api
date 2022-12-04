<?php

/**
 * 
 */

namespace CC\Api;

class Options {

    /**
     * Option cache
     * @var array
     */
    
    public static $options = [

        /** General */

        'api.name' => 'API',
        'api.color' => '#44f589',

        /** Development */

        'api.debug' => false,
        'api.log' => false,

        /** Localization */

        'api.timezone' => 'Europe/Amsterdam',
        'api.locale' => 'en_GB',
        'api.datetimeFormat' => 'c',
        'api.gettext' => true,

        /** Session */

        'session.lifetime' => 3600,

        /** Models */

        'models.namespaces' => [],

        /** Tasks */

        'tasks.directories' => [],

        /** SQL/database */

        'sql.migrations.directories' => [],
        'sql.limit.default' => 50,
        'sql.limit.max' => 1000000,

        /** Filesystem */

        'filesystem.maxUploadFileSize' => 1024 * 1024 * 1024 ];
    
    /**
     * Set/Get global Api options
     * @param string|null $key option name
     * @param string $value Set option value (optional)
     */
    
    public static function set( $key = false, $value = null ){

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