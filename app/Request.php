<?php

/**
 * 
 */

namespace CC\Api;



class Request {

    /** Request properties */

    public static $ip = null;
    public static $port = 0;
    public static $url = '';
    public static $host = '';
    public static $query = '';
    public static $route = '';
    public static $path = [];
    public static $protocol = '';
    public static $method = '';
    public static $time = null;
    public static $contentType = 'application/json';
    public static $contentLanguage = null;
    public static $contentTimezone = null;
    public static $dataType = 'application/x-www-form-urlencoded';
    public static $parameters = [];
    public static $options = [];
    public static $join = [];
    public static $headers = [];
    public static $authorization = null;
    public static $nonce = null;

    /**
     * Constructor
     */
    
    function __construct(){

        /** Return if already executed */

        if( self::$protocol ){

            return $this; }

        /** Get URL properties */

        self::$ip = $_SERVER[ 'REMOTE_ADDR' ] ?? null;
        self::$port = Options::get( 'http.port' ) ?? $_SERVER[ 'REMOTE_PORT' ] ?? 80;
        self::$host = $_SERVER[ 'SERVER_NAME' ] ?? $_SERVER[ 'HTTP_HOST' ] ?? 'cli';
        self::$query = $_SERVER[ 'QUERY_STRING' ] ?? '';
        self::$method = strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ?? 'CLI' );
        self::$route = preg_replace( '/[\/]{2,}/', '/', preg_replace( '/\?.*$/', '', $_SERVER[ 'REQUEST_URI' ] ?? '' ) );
        self::$path = array_values( array_filter( explode( '/', strtolower( self::$route ) ) ) );
        self::$protocol = 
        
            isset( $_SERVER[ 'HTTPS' ] ) && filter_var( $_SERVER[ 'HTTPS' ], FILTER_VALIDATE_BOOLEAN ) ||
            isset( $_SERVER[ 'SERVER_PORT_SECURE' ] ) && filter_var( $_SERVER[ 'SERVER_PORT_SECURE' ], FILTER_VALIDATE_BOOLEAN ) ||
            isset( $_SERVER[ 'SERVER_PORT' ] ) && $_SERVER[ 'SERVER_PORT' ] === 443 ? 'https' : 'http';

        self::$url = self::$protocol . '://' . self::$host . ':' . self::$port . '/';
        self::$time = $_SERVER[ 'REQUEST_TIME_FLOAT' ] ?? microtime();
        self::$nonce = uniqid();

        /** 
         * 
         * HEADERS
         * 
         */

        if( function_exists( 'apache_request_headers' ) ){ foreach( array_change_key_case( apache_request_headers(), CASE_LOWER ) as $key => $value ){
            
            /** Parse value as UTF-8 and apply special processing */

            $value = mb_convert_encoding( $value, 'UTF-8' ); switch( $key ){

                case 'content-type':

                    self::$dataType = strtolower( $value ); break;

                case 'x-content-type':

                    self::$contentType = strtolower( $value ); break;

                case 'x-content-language':

                    self::$contentLanguage = $value && @setlocale( LC_MESSAGES, $value . '.utf8' ) ? $value : self::$contentLanguage; break;

                case 'x-content-timezone':

                    self::$contentTimezone = preg_match( '/^[a-z\/]+$/i', $value ) ? $value : self::$contentTimezone; break;

                case 'authorization':

                    self::$authorization = $value; break; }

            /** Set parameter */

            self::$headers[ $key ] = $value; } }

        /** 
         * 
         * SESSION
         * 
         */

        if( ! self::$authorization ){

            /** Start session */

            if( !headers_sent() ){

                if( Options::get( 'session.name' ) || self::$protocol === 'https' ){

                    session_name( Options::get( 'session.name' ) ?? '__Secure-PHPSESSID' ); }
                    
                session_set_cookie_params([

                    'lifetime' => Options::get( 'session.lifetime' ) ?? time() + 3600,
                    'path' => Options::get( 'session.path' ) ?? '/',
                    'domain' => Options::get( 'session.domain' ) ?? self::$host,
                    'secure' => Options::get( 'session.secure' ) ?? self::$protocol === 'https',
                    'httponly' => Options::get( 'session.httponly' ) ?? true,
                    'samesite' => Options::get( 'session.samesite' ) ?? 'Lax' ]);

                session_start(); }

            /** Enumerate properties */

            foreach( $_SESSION ?? [] as $key => $value ){

                /** Parse value as UTF-8 and apply special processing */

                $value = mb_convert_encoding( $value, 'UTF-8' ); switch( $key ){

                    case 'contenttype':

                        self::$contentType = strtolower( $value ); break;

                    case 'authorization':

                        self::$authorization = $value; break; }

                /** Set parameter */

                self::$headers[ $key ] = $value; } }

        /** 
         * 
         * PARAMETERS
         * 
         */

        $parameters = array_merge( [], $_GET ?? [], $_POST ?? [] ); $input = file_get_contents( 'php://input' ) ?? null; if( $input ){

            /** Parse JSON */

            if( self::$dataType === 'application/json' ){

                $parameters = array_merge( $parameters, json_decode( $input ?? '', true ) ?? [] ); }

            /** Parse other */

            else {

                parse_str( $input, $_INPUT ); $parameters = array_merge( $parameters, is_array( $_INPUT ) ? $_INPUT : json_decode( $_INPUT ?? '', true ) ); } }

        /** Parse parameters */

        foreach( array_change_key_case( $parameters, CASE_LOWER ) as $key => $value ){

            /** Parse value as UTF-8 and apply special processing */

            $value = mb_convert_encoding( $value, 'UTF-8' ); switch( $key ){

                case 'join': foreach( explode( ',', $value ) as $join ){

                    self::$join[ $join ] = true; } break;

                case 'options': foreach( explode( ',', $value ) as $options ){

                    self::$options[ $options ] = true; } break;

                case 'contenttype':

                    self::$contentType = strtolower( $value ); break;

                case 'authorization':

                    self::$authorization = $value; break; }

            /** Set parameter */

            self::$parameters[ $key ] = $value; }

        /**
         * Get/set DEV options
         */

        if( Options::get( 'api.dev' ) ){

            foreach( self::get( 'options' ) as $option => $value ){

                Options::set( 'api.' . $option, true ); } }

        /** Validate content type */

        if( ! preg_match( '/^(application\/(xml|json|pdf)|text\/(csv|html))$/', self::$contentType ) ) {

            self::$contentType = 'application/json'; new Error( 415 ); }

        /** --- */

        return $this;

    }

    /**
     * 
     * Parent methods
     * 
     */
    
    /**
     * Get request properties
     * @param  string $key Property key
     * @return object      Properties or single property
     */
    
    public static function get( $key = null ){

        /** Return if set */

        if( ! self::$host ){

            new Request(); }

        /** Return */

        $request = [

            'time' => self::$time,
            'ip' => self::$ip,
            'port' => self::$port,
            'url' => self::$url,
            'host' => self::$host,
            'query' => self::$query,
            'route' => self::$route,
            'path' => self::$path,
            'protocol' => self::$protocol,
            'method' => self::$method,
            'contentType' => self::$contentType,
            'contentLanguage' => self::$contentLanguage,
            'contentTimezone' => self::$contentTimezone,
            'dataType' => self::$dataType,
            'parameters' => self::$parameters,
            'options' => self::$options,
            'join' => self::$join,
            'headers' => self::$headers,
            'authorization' => self::$authorization ];

        /** Return */

        return $key ? array_change_key_case( $request, CASE_LOWER )[ strtolower( $key ) ] ?? null : $request; }
    
        /**
         * Get request parameters
         * @param  string $key Parameter key
         * @return object      Parameters or single parameter
         */
        
        public static function parameters( $key = null ){
     
            /** Return if set */
    
            $request = self::get(); return $key ? ( array_change_key_case( $request[ 'parameters' ], CASE_LOWER )[ strtolower( $key ) ] ?? null ) : $request[ 'parameters' ]; }
    
    /**
     * Get request join
     * @param  string $key Parameter key
     * @return object      Parameters or single parameter
     */
    
    public static function join( $key = null ){
 
        /** Return if set */

        $request = self::get(); return $key ? ( array_change_key_case( $request[ 'join' ], CASE_LOWER )[ strtolower( $key ) ] ?? null ) : $request[ 'join' ]; }
    
    /**
     * Parameters shorthand alias
     * @param  string $key Parameter key
     * @return object      Parameters or single parameter
     */
    
    public static function data( $key = null ){
 
        /** Return if set */

        return self::parameters( $key ); }
    
    /**
     * Get request headers
     * @param  string $key Header key
     * @return object      Headers or single header
     */
    
    public static function headers( $key = null ){
 
        /** Return if set */

        $request = self::get(); return $key ? ( array_change_key_case( $request[ 'headers' ], CASE_LOWER )[ strtolower( $key ) ] ?? null ) : $request[ 'headers' ]; }

    /**
     * Get/set nonce
     *
     * @return string
     */

    public static function nonce(){

        /** Set nonce */

        self::$nonce = self::$nonce ? self::$nonce : uniqid();

        /** Return */

        return self::$nonce; }
}
