<?php

namespace CC\Api;

class Response {

    /**
     * Cache
     * @var array
     */
    
    public static $httpStatusCode = 200;
    public static $metadata = [];
    public static $log = [];
    public static $errors = [];
    
    /**
     * HTTP response headers
     * @var array
     */
    
    public static $headers = [

        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, X-Content-Type, X-Content-Language, X-Content-Timezone',
        'Access-Control-Allow-Methods' => 'GET, PUT, POST, DELETE, OPTIONS',
        'Content-Type' => 'application/json',
        'Charset' => 'utf-8',
        'Content-Language' => 'nl',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
        'X-Powered-By' => 'CC',
        
        /** Security */

        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Referrer-Policy' => 'no-referrer',
        'Content-Security-Policy' => 'default-src \'self\'; font-src *;img-src * data:; script-src * \'unsafe-inline\'; style-src * \'unsafe-inline\'',
        'Permissions-Policy' => 'accelerometer=(self), ambient-light-sensor=(self), autoplay=(self), battery=(self), camera=(self), cross-origin-isolated=(self), display-capture=(self), document-domain=*, encrypted-media=(self), execution-while-not-rendered=*, execution-while-out-of-viewport=*, fullscreen=(self), geolocation=(self), gyroscope=(self), magnetometer=(self), microphone=(self), midi=(self), navigation-override=(self), payment=(self), picture-in-picture=*, publickey-credentials-get=(self), screen-wake-lock=(self), sync-xhr=*, usb=(self), web-share=(self), xr-spatial-tracking=(self)' ];

    /**
     * HTTP status code list
     * @var code
     */
    
    public static $httpStatusCodes = [

        200 => [ 'title' => 'OK' ],
        201 => [ 'title' => 'Created', 'description' => 'The request has been fulfilled and resulted in a new resource being created.' ],
        206 => [ 'title' => 'Partial Content', 'description' => 'The request has been fulfilled and resulted in a new resource being created.' ],
        400 => [ 'title' => 'Bad Request', 'description' => 'You have malformed syntax in your request.' ],
        401 => [ 'title' => 'Unauthorized', 'description' => 'You are not authorized to access this resource.' ],
        403 => [ 'title' => 'Forbidden', 'description' => 'You are not authorized to access this resource.' ],
        404 => [ 'title' => 'Not Found', 'description' => 'This resource is not found.' ],
        405 => [ 'title' => 'Method Not Allowed', 'description' => 'This method is not allowed.' ],
        415 => [ 'title' => 'Unsupported Media Type', 'description' => 'You have requested an unsupported media type.' ],
        416 => [ 'title' => 'Requested Range Not Satisfiable', 'description' => 'You have requested a portion of the file, but the server cannot supply that portion.' ],
        422 => [ 'title' => 'Unprocessable Entity', 'description' => 'The request was well-formed but was unable to be followed due to semantic errors.' ],
        500 => [ 'title' => 'Internal Server Error', 'description' => 'OOPS...' ] ];

    /**
     * Set HTTP status code response
     * @param integer
     */
    
    public static function setHttpStatusCode( $httpStatusCode = 500 ){
        
        http_response_code( isset( self::$httpStatusCodes[ $httpStatusCode ] ) ? $httpStatusCode : 500 ); return self::getHttpStatusCode(); }
    
    /**
     * Get HTTP status code
     */
    
    public static function getHttpStatusCode(){
        
        return array_merge([ 'code' => http_response_code() ], self::$httpStatusCodes[ http_response_code() ]); }
    
    /**
     * Set HTTP response headers
     * @param array key => value
     */
    
    public static function setHeaders( $headers = [] ){
        
        foreach( $headers as $key => $value ){ self::$headers[ $key ] = $value; } ksort( self::$headers ); }
    
    /**
     * Get HTTP response headers
     * @param null|string key (optional)
     */
    
    public static function getHeaders( $key = null ){
        
        return $key ? ( isset( self::$headers[ $key ] ) ? self::$headers[ $key ] : null ) : self::$headers; }
    
    /**
     * Apply HTTP response headers to document
     */
    
    public static function applyHeaders(){
        
        foreach( self::$headers as $key => $value ){ header( $key . ': ' . $value ); } }
    
    /**
     * Set metadata response
     * @param array key => value
     */
    
    public static function setMetadata( $metadata = [] ){
        
        foreach( $metadata as $key => $value ){ self::$metadata[ $key ] = $value; } ksort( self::$metadata ); }
    
    /**
     * Get metadata response
     * @param null|string key (optional)
     */
    
    public static function getMetadata( $key = null ){
        
        return $key ?  array_change_key_case( self::$metadata, CASE_LOWER )[ strtolower( $key ) ] ?? null : self::$metadata; }
    
    /**
     * Get metadata response
     * @param null|string key (optional)
     */
    
    public static function log( $entry ){
        
        array_unshift( self::$log, $entry ); }

    /**
     * Write response to document
     * @param array items
     * @param null|string $Custom response (i.e. javascript|jpg|png|pdf)
     */
    
    public static function write( $response = [], $contentType = null ){
        
        /** Clear output */

        if( ! Options::get( 'api.debug' ) && ob_get_contents() ){

            ob_end_clean(); }

        /** Set content properties */

        $charset = self::getHeaders( 'Charset' ) ? self::getHeaders( 'Charset' ) : 'utf-8';
        $contentType = strtolower( $contentType ? $contentType : Request::get( 'contentType' ) );
        $contentDisposition = 'inline;';
        $contentName = implode( '-', array_filter( Request::$path ) ) . '-' . time();

        /** Switch on content types */

        switch( $contentType ){

            case 'application/xml':

                $parse = function( $response ){ return Response\Xml::get( $response ); };
                $contentDisposition = 'inline; filename="' . $contentName . '.xml"'; break;

            case 'application/json':

                $parse = function( $response ){ return Response\Json::get( $response ); }; break;

            case 'application/pdf':

                $parse = function( $response ){ return Response\Pdf::get( $response ); };
                $contentDisposition = 'attachment; filename="' . $contentName . '.pdf"'; break;

            case 'text/html':

                $parse = function( $response ){ return Response\Html::get( $response ); }; break;

            case 'text/csv':

                $parse = function( $response ){ return Response\Csv::get( $response ); };
                $contentDisposition = 'attachment; filename="' . $contentName . '.csv"'; break;

            default: new Error( 415 ); break; }

        /** Prepare metadata */

        self::setMetadata([ 'datetime' => ( new \DateTime ) -> format( 'c' ) ]);
        self::setMetadata([ 'duration' => round( microtime( true ) - Request::get( 'time' ), 4 ) ]);
        self::setMetadata([ 'resource' => Request::get( 'route' ) ]);

        /** Log model */

        if( Router::$Model && isset( $_GET[ 'model' ] ) ? true : false ){

            $modelMetadata = [ 'name' => Router::$Model::$name ]; if( isset( Router::$Model::$fields ) ){

                $modelMetadata[ 'fields' ] = []; foreach( Router::$Model::$fields as $key => $field ){

                    /** Log field */

                    $modelMetadata[ 'fields' ][ $key ] = $field[ 'datatype' ] ?? 'mixed';

                    /** Required */

                    if( $field[ 'required' ] ?? false ){

                        $modelMetadata[ 'fields' ][ $key ] .= '*'; }

                    /** Model */

                    if( isset( $field[ 'model' ] ) ){

                        $modelMetadata[ 'fields' ][ $key ] = 'int: model:' . $field[ 'model' ]; }

                    /** Datatypes */

                    if( ( $field[ 'datatype' ] ?? 'mixed' ) === 'switch' ){

                        $modelMetadata[ 'fields' ][ $key ] .= ': ' . implode( ', ', array_keys( $field[ 'switch' ] ?? [] ) ); }

                }}

        self::setMetadata([ 'model' => $modelMetadata ]); }

        /** Prepare response */

        if( $contentType === 'application/json' || $contentType === 'text/html' ){ $response = [

            'metadata' => self::$metadata,
            'response' => count( self::$errors ) ? false : $response ];

            /** errors */

            if( count( self::$errors ) ){

                $response[ 'errors' ] = self::$errors; }}

        /** log SQL */

        if( Options::get( 'api.log' ) ){

             $response[ 'log' ] = self::$log; }

        /** debugging */

        if( Options::get( 'api.debug' ) && count( self::$errors ) ){

             $response[ 'parameters' ] = Request::parameters();
             $response[ 'backtrace' ] = debug_backtrace();
             $response[ 'log' ] = self::$log; }

        /** Redirect */
        
        if( self::getHttpStatusCode()[ 'code' ] === 401 && $contentType === 'text/html' ){

            Session::stop();

        header( 'Location: ' . Request::get( 'url' ) . 'login?redirect=' . Request::get( 'route' ) ); die(); }

        /** Set 201 if POST is successfull and no error */

        if( self::getHttpStatusCode()[ 'code' ] === 200 && Request::get( 'method' ) === 'POST' && ( $response[ 'response' ][ 'id' ] ?? null ) ){

            self::setHttpStatusCode( 201 ); }

        /** Apply headers */

        self::setHeaders([ 'Content-Type' => $contentType . '; charset=' . $charset ]);
        self::setHeaders([ 'Content-Disposition' => $contentDisposition ]);

        /** If CLI */

        if( php_sapi_name() === 'cli' ){

            Log::error( $response ); die(); }

        /** Return data */

        $response = $parse( $response ); if( $response ){

            self::applyHeaders(); echo $response; echo ob_get_clean(); die(); }

        /** Throw error */

        else {

            new Error( 500 ); die(); }}

    /**
     * Attach download
     * @param array $options See options
     */
    
    public static function attachment( $fileName, $options = [] ){
        
        /** Clear output */

        if( ! Options::get( 'api.debug' ) ){

            ob_end_clean(); }

        /** Set default options */

        $options = array_merge([

            'name' => 'Download',
            'stream' => null,
            'data' => null,
            'contentType' => null,
            'disposition' => Request::data( 'disposition' ) && preg_match( '/^(inline|attachment)$/i', Request::data( 'disposition' ) ) ? Request::data( 'disposition' ) : 'attachment'

        ], is_array( $fileName ) ? $fileName : $options );

        /** 
         * Parse manual file
         */

        if( is_string( $fileName ) && preg_match( '/\//', $fileName ) ){

            /** Check if file exists */

            $name = explode( '/', $fileName ); $name = end( $name ); if( ! file_exists( $fileName ) ){

                new Error( sprintf( _( 'File does not exist: "%s"' ), $name ), 404 ); }

            /** Override options */

            $options[ 'name' ] = $name; $options[ 'data' ] = file_get_contents( $fileName ); }

        /** 
         * Parse filesystem file
         */

        else if( is_string( $fileName ) ){

            /** Check if file exists */

            if( ! ( new Filesystem\Storage ) -> fileExists( $fileName ) ){

                new Error( sprintf( _( 'File does not exist: "%s"' ), $fileName ), 404 ); }

            /** Override options */

            $options[ 'name' ] = $fileName;
            $options[ 'data' ] = ( new Filesystem\Storage ) -> get( $fileName ); }

        /**
         * Set data
         */
        
        $data = $options[ 'data' ] ? $options[ 'data' ] : ( new Filesystem\Storage ) -> get( $options[ 'name' ] );

        /** 
         * 
         * Content-Range streaming
         * 
         */

        $contentRange = isset( $_SERVER[ 'HTTP_RANGE' ] ) ? $_SERVER[ 'HTTP_RANGE' ] : null;
        $rangeHeaders = $errorHeaders = [];

        if( $contentRange || $options[ 'stream' ] ){

            $size = ( new Filesystem\Storage ) -> fileSize( $options[ 'name' ] );
            $length = $size;
            $start = 0;
            $end = $size - 1;

            $rangeHeaders = $errorHeaders = [ 'Accept-Ranges' => '0-' . $length ];
            $errorHeaders[ 'Content-Range' ] = 'bytes */' . $size;

            if( $contentRange ){

                if( ! preg_match( '/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $contentRange, $matches ) ){

                    Response::setHeaders( $errorHeaders ); new Error( 416 ); }

                $start = ( int ) $matches[ 1 ];
                $end = isset( $matches[ 2 ] ) && ( int ) $matches[ 2 ] > 0 ? ( int ) $matches[ 2 ] : $size - 1; }

            if( $start > $end ){

                Response::setHeaders( $errorHeaders ); new Error( 416 ); }

            $rangeHeaders[ 'Content-Length' ] = ( $end - $start ) + 1;
            $rangeHeaders[ 'Content-Range' ] = 'bytes ' . $start . '-' . $end . '/' . $size;

            /** Set 206: partial content */

            self::setHttpStatusCode( 206 ); }

        /** 
         * 
         * Merge headers
         * 
         */

        Response::setHeaders( array_merge([

            'Content-Type' => $options[ 'contentType' ] ? $options[ 'contentType' ] : Filesystem\Mimetypes::get( $options[ 'name' ] ),
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Length' => $contentRange ? $size : mb_strlen( $data, '8bit' ),
            'Content-Disposition' => strtolower( $options[ 'disposition' ] ) . '; filename="' . $options[ 'name' ] . '"'

        ], isset( $options[ 'cache' ] ) && $options[ 'cache' ] ? [

            'Pragma' => 'public',
            'Cache-Control' => 'max-age=' . ( is_int( $options[ 'cache' ] ) ? $options[ 'cache' ] : 604800 ) . ', public',
            'Expires' => gmdate( 'D, d M Y H:i:s \G\M\T', time() + ( is_int( $options[ 'cache' ] ) ? $options[ 'cache' ] : 604800 ) )

        ] : [], $rangeHeaders ));

        /** Apply headers */

        self::applyHeaders();

        /** 
         * 
         * Write data
         * 
         */

        if( $contentRange ){

            /** Write range */

            fseek( $data, $start ); while( true ){

                if( ftell( $data ) >= $end ){ break; }

                echo fread( $data, 8192 ); flush(); ob_flush(); }
        
        } else {

            /** Write all */

            echo $data; echo ob_get_clean();

        } die();

    }
    
}
