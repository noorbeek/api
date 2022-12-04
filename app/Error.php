<?php

namespace CC\Api;

class Error {
    
    /**
     * Suppress error
     * @var boolean
     */
    
    public static $catch = false;
    
    /**
     * Global Error class
     * @param null|int|bool|array null for backtrace, bool for init, int for HTTP status + die( ), array for custom error description
     * @param boolean HTTP status code (optional), die( )
     */
    
    function __construct( $errorObject, $httpStatusCode = false ){

        /** Default error object */
        
        $error = [ 'code' => 500, 'description' => _( 'Unknown error' ) ];

        /** Set HTTP status code */

        if( is_integer( $httpStatusCode ) || is_integer( $errorObject ) ){

            $error[ 'code' ] = is_integer( $httpStatusCode ) ? $httpStatusCode : $errorObject; }

        /** Set default error object from HTTP status code */

        $code = Response::setHttpStatusCode( $error[ 'code' ] );

            $error[ 'title' ] = $code[ 'title' ];
            $error[ 'description' ] = $code[ 'description' ];

        /** Override */

        if( is_string( $errorObject ) ){

            $error[ 'description' ] = $errorObject; }

        /** Override */

        if( is_array( $errorObject ) ){ 

            $error = array_merge( $error, $errorObject ); }

        /** Set description */

        Response :: $errors[] = $error;

        /** Throw error if catch mode is off */
        
        if( ! self :: $catch ){

            Response::setHttpStatusCode( $error[ 'code' ] );
            Response::write( Response :: $errors ); }

        /** Return */

        return true; }

    /** Default PHP error handler */

    public static function error( $code = null, $description = null, $file = null, $line = null ){
        
        /** Continue if error reporting = off*/

        if( ! error_reporting() || $code === null ){

            return true; }

        /** Throw error */

        return new Error([

            'code' => $code, 'description' => $description, 'Type' => 'ERROR', 'file' => $file, 'line' => $line ], 500 ); die( ); }

    /** Default PHP fatal error handler */

    public static function fatal(){

        /** Throw error */

        $error = error_get_last(); if( $error !== null ){
        
            return new Error([

                'description' => $error[ 'message' ],
                'Type' => 'FATAL', $error ], 500 ); } }
    
}

/**
 * Bypass standard exception
 */

class ExceptionBypass extends \Exception {

    function __construct( $description = null, $code = null ){

        /** Throw error */

        return new Error([

            'code' => ( $code ) ? $code : 0,
            'description' => ( $description ? $description : null ),
            'Type' => 'EXCEPTION' ], 500 ); }}
