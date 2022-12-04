<?php

namespace CC\Api;

class Views {
 
    /**
     * Set/Get global Api options
     * @param string|null $string Log string
     * @param string $type Log type for coloring
     */
    
    public static function message( $options = [] ){

        /** Get status code*/

        $statusCode = Response::$httpStatusCode;
        $statusCodeProperties = Response::$httpStatusCodes[ Response::$httpStatusCode ];

        /** Set options */

        $options = array_merge([

            'title' => $statusCode . ': ' . $statusCodeProperties[ 'title' ],
            'message' => $statusCodeProperties[ 'description' ],
            'status' => $statusCode < 400 ? 'success' : ( $statusCode < 500 ? 'warning' : 'error' ) ], $options );

        /** Determine color */

        switch( $options[ 'status' ] ){

            case 'success': $color = '#43a047'; break;
            case 'warning': $color = '#f9a825'; break;
            case 'error': $color = '#b71c1c'; break;
        
        default: $color = Options::get( 'api.color' ); break; }

        /** Return */

        require __DIR__ . '/Views/Message.php'; die(); }

}
