<?php

/**
 * 
 */

namespace CC\Api;

class Router {

    /**
     * List of routes
     * @var array
     */

    public static $routes = [];

    /**
     * Cache model from route
     */
    
    public static $Model = null;

    /**
     * Execute route
     * @param  array  $route Route object
     * @return mixed        Route execution
     */
    
    public static function execute( array $route, $softFail = false ){

        /** Authorize route */

        $isAuthorized = Authorization::user( $route[ 'roles' ] ?? [], 'r' );
        $isAuthorized = $isAuthorized && ( ! isset( $route[ 'scopes' ] ) || Authorization::scope( $route[ 'scopes' ] ) );
        $isAuthorized = $isAuthorized && ( ! isset( $route[ 'groups' ] ) || Authorization::group( $route[ 'groups' ] ) );

        /** 
         * 
         * View
         * 
         */

        if( array_key_exists( 'view', $route ) && is_callable( $route[ 'view' ] ) ){

            /** Load view */

            if( $isAuthorized ){

                require call_user_func_array( $route[ 'view' ], $route[ 'parameters' ] ?? null ); }

            /** Not authorized */

            else if( Session::get( 'id' ) ){

                require 'Views/Error.php'; }

            /** Not authenticated */

            else {

                header( 'Location: ' . Request::get( 'url' ) . 'login?redirect=' . $route[ 'uri' ] ); die(); }}

        /** 
         * 
         * Default
         * 
         */

        if( ! $isAuthorized ){

            return $softFail ? false : ( Session::get( 'user' ) ? new Error( 401 ) : new Error( sprintf( _( 'You are not authorized to view route: "%s"' ), $route[ 'uri' ] ), 403 ) ); }

        /** Switch action */

        if( array_key_exists( 'run', $route ) && is_callable( $route[ 'run' ] ) ){

             return call_user_func_array( $route[ 'run' ], $route[ 'parameters' ] ?? null ); }

        if( array_key_exists( 'write', $route ) && is_callable( $route[ 'write' ] ) ){

             return Response::write( call_user_func_array( $route[ 'write' ], $route[ 'parameters' ] ?? null ) ); }

        if( array_key_exists( 'attachment', $route ) && is_callable( $route[ 'attachment' ] ) ){

             return Response::attachment( call_user_func_array( $route[ 'attachment' ], $route[ 'parameters' ] ?? null ) ); }

        /** Return */

        return false; }

    /**
     * Redirect to route
     * @param string $routes Routes
     */
    
    public static function redirect( string $route ){

        self::execute( self::find( $route ) ); }

    /**
     * Add route to routes
     * @param array $routes Routes
     */
    
    public static function add( array $routes ){

        foreach( $routes as $route => $properties ){

            self::$routes[ $route ] = $properties; } }
    
    /**
     * Parse route from string
     * @param  string $route Path
     * @return array        Path
     */
    
    public static function routeToPath( string $route ){

        return array_values( array_filter( explode( '/', $route ) ) ); }
    
    /**
     * Parse route from string
     * @param  string $route Path
     * @return array        Path
     */
    
    public static function find( string $route ){

        /** Prepend method */

        $route = preg_replace( '/\?.*$/', '', ! preg_match( '/^(GET|PUT|POST|DELETE|OPTIONS)\//i', $route ) ? Request::get( 'method' ) . ( $route[ 0 ] === '/' ? '' : '/' ) . $route : $route );

        /** Parse routes */

        $path = self::routeToPath( $route );
        $routes = self::$routes;
        $matches = [];

        /** Return empty path */

        if( ! count( $path ) ){

            return new Error( sprintf( _( 'Route not found: "%s"' ), $route ), 404 ); }

        /** Validate route

        if( ! preg_match( '/^[a-z0-9\-\/\_\.]+$/i', preg_replace( '/\?.*$/', '', $route ) ) ){

            return new Error( sprintf( _( 'Route invalid: "%s"' ), $route ), 404 ); } */

        /** Detect multilayered models */

        $modelPath = ''; foreach( $path as $index => $segment ){

            $modelPath .= $index ? $segment : '';

            /** Find & override model */

            if( $index && Models::exists( $modelPath ) ){

                $Model = self::$Model = Models::get( $modelPath );
                $routes = array_merge( $routes, $Model::$routes ?? $routes ); }

            /** Break if segment is not alfabetical */

            if( ! preg_match( '/^[a-z]+$/i', $segment ) ){

                break; } }

        /** Compare routes */

        $parameters = []; foreach( $routes as $uri => $routeObject ){

            $compare = self::routeToPath( $uri );

            /** Compare amount of paths */

            if( count( $path ) !== count( $compare ) ){

                continue; }

            /** Compare path items */

            $match = false; $exactMatch = true; foreach( $path as $index => $item ){

                $match = false; $compareItem = $compare[ $index ];

                /** Compare strings */

                if( strtolower( $item ) === strtolower( $compareItem ) ){
                    
                    $match = true; }

                /** Custom parameter by KEY */

                else if( preg_match( '/:[a-z]+/i', $compareItem ) ){
                        
                    $exactMatch = false; $match = true; $parameters[] = $item; }

                /** Custom parameter by REGEX */

                else if( preg_match( '/^' . $compareItem . '$/i', $item ) ){
                    
                    $exactMatch = false; $match = true; $parameters[] = $item; }

                /** Break if no match */

                if( ! $match ){

                    break; } }

            /** Return match */

            if( $match ){

                /** Write describe */

                if( isset( $Model ) && Options::get( 'api.describe' ) ){
    
                    Response::setMetadata([ 'describe' => Models::describe( $modelPath ) ]); }

                /** Return route object */

                $matches[] = array_merge([ 'uri' => $uri, 'exactMatch' => $exactMatch, 'parameters' => $parameters ], $routeObject ); }

        }

        /** Check for exact matches first */

        foreach($matches as $match){

            if($match['exactMatch']){

                return $match; }}

        /** Return */

    return $matches[ 0 ] ?? new Error( sprintf( _( 'Route not found: "%s"' ), $route ), 404 ); }


}
