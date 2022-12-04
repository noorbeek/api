<?php

/**
 * 
 */

namespace CC\Api;

class Authorization {

    /**
     * Option cache
     * @var array
     */
    
    public static $roles = [

        /** System */
        
        1 => 'system',
        99 => 'root',

        /** Authenticated */

        100 => 'administrator',
        200 => 'manager',
        300 => 'user',
        999 => 'any',

        /** Not authenticated */

        9998 => 'public',
        9999 => 'deny' ];

    /** Modes */

    public static $modes = [

        'r' => [ 'level' => 0, 'verb' => 'read' ],
        'w' => [ 'level' => 10, 'verb' => 'write' ],
        'x' => [ 'level' => 100, 'verb' => 'manage' ] ];
    
    /**
     * Set/Get global Api options
     * @param string|null $key option name
     * @param string $value Set option value (optional)
     */
    
    public static function check( $role, $roles, $mode = 'r', $softFail = false ){

        /** Start session */

        if( Session::get( 'role' ) === 9999 && Request::get( 'authorization' ) ){

            Session::resume( Request::get( 'authorization' ) ); }

        /** Get roles */

        $mode = strtolower( $mode );
        $role = self::getRole( $role === 'me' ? Session::get( 'role' ) : $role );
        $roles = $roles ? ( is_string( $roles ) ? [ $roles ] : $roles ) : [];

        /** Enumerate roles */

        $isAuthorized = count( $roles ) ? false : true; foreach( $roles as $item ){

            /** Validate authorization */

            if( ! preg_match( '/^(r|w|x):([a-z]+)/i', strtolower( $item ), $matches ) ){

                return $softFail ? false : new Error( sprintf( _( 'Invalid column role markup: "%s"' ), $item ), 500 ); }

            /** Validate group */

            if( $role[ 'level' ] <= self::getRole( $matches[ 2 ] )[ 'level' ] && self::modeIsHigherOrEqual( $matches[ 1 ], $mode ) ){

                $isAuthorized = true; break; } }
        
        /** Return option(s) */

        return $isAuthorized; }

    /**
     * Authorize by role name/number
     * @param  object $Model Model class
     * @return mixed        True or Error
     */
    
    public static function role( $role, $roles, $mode = 'r', $softFail = true ){

        /** Check */

        if( ! self::check( $role, $roles, $mode, $softFail ) ){

            return $softFail ? false: ( Session::get( 'user' ) ? new Error( 403 ) : new Error( 401 ) ); }

        /** Return */

        return true; }

    /**
     * Authorize by user role
     * @param  object $Model Model class
     * @return mixed        True or Error
     */
    
    public static function user( $roles, $mode = 'r', $softFail = true ){

        /** Parse roles */

        $parsedRoles = []; foreach( is_string( $roles ) ? [ $roles ] : ( is_array( $roles ) ? $roles : [] ) as &$role ){

            $parsedRoles[] = preg_match( '/^[a-z]+$/i', $role ) ? 'r:' . $role : $role; }

        /** Check */

        if( ! self::check( 'me', $parsedRoles, $mode, $softFail ) ){

            return $softFail ? false: ( Session::get( 'user' ) ? new Error( 403 ) : new Error( 401 ) ); }

        /** Return */

        return true; }

    /**
     * Authorize by user role
     * @param  object $Model Model class
     * @return mixed        True or Error
     */
    
    public static function scope( $scopes, $softFail = true ){

        /** Skip admins */

        if( self::user( 'r:administrator' ) ){

            return true; }

        /** Get scopes of user */

        $me = ( new Models\Users ) -> get( 'me' ) ?? new Error( 401 );
        $userScopes = ( new Models\Users ) -> getUserScopes();

        /** Enumerate scopes */

        foreach( is_string( $scopes ) ? [ $scopes ] : ( is_array( $scopes ) ? $scopes : [] ) as $scope ){

            /** Validate scope markup */

            if( ! preg_match( '/^(r|w|x):(.+)$/i', $scope, $scopeParts ) ){

                new Error( sprintf( _( 'Invalid column role markup: "%s"' ), $scope ), 500 ); }

            /** Match scope */

            foreach( $userScopes as $userScope ){

                if( preg_match( '/^' . $userScope[ 'scope' ] . '$/i', $scopeParts[ 2 ] ) && self::modeIsHigherOrEqual( $userScope[ 'mode' ], $scopeParts[ 1 ] ) ){

                    return true; } } }

        /** Return */

        return $softFail ? false : new Error( sprintf( _( 'You are not authorized to "%s" scope "%s"' ), self::$modes[ $scopeParts[ 1 ] ][ 'verb' ], $scopeParts[ 2 ] ), 403 ); }

    /**
     * Authorize by user group
     * @param  object $Model Model class
     * @return mixed        True or Error
     */
    
    public static function group( $groups, $softFail = false ){

        /** Get groups of user */

        $me = ( new Models\Users ) -> get( 'me' ) ?? new Error( 401 );
        $userGroups = ( new Models\Users ) -> getUserGroups();

        /** Enumerate scopes */

        foreach( is_string( $groups ) ? [ $groups ] : ( is_array( $groups ) ? $groups : [] ) as $group ){

            /** Skip admins */

            if( self::user( 'r:administrator' ) ){

                return true; }

            /** Parse group */

            if( ! preg_match( '/^(r|w|x):(.+)$/i', $group, $groupParts ) ){

                new Error( sprintf( _( 'Invalid column role markup: "%s"' ), $group ), 500 ); }

            /** Match group */

            foreach( $userGroups as $userGroup ){

                if( preg_match( '/^' . $userGroup[ 'id' ] . '$/i', $groupParts[ 2 ] ) && self::modeIsHigherOrEqual( $userGroup[ 'ownerOf' ] === true ? 'w' : 'r', $groupParts[ 1 ] ) ){

                    return true; } } }

        /** Return */

        return $softFail ? false : new Error( sprintf( _( 'You are not authorized to "%s" group "%s"' ), self::$modes[ $groupParts[ 1 ] ][ 'verb' ], $groupParts[ 2 ] ), 403 ); }

    /**
     * Commpare 2 modes
     * @param  string $mode         Mode to check
     * @param  string $requiredMode Mode that is required
     * @return boolean              Yes/no
     */
    
    public static function modeIsHigherOrEqual( string $mode, $requiredMode = 'r' ){

        /** Compare */

        return self::$modes[ strtolower( $mode ) ][ 'level' ] >= self::$modes[ strtolower( $requiredMode ) ][ 'level' ]; }
    
    /**
     * Get option value
     * @param  string $key option key
     */
    
    public static function getRole( $role ){

        /** Get role */

        if( is_integer( $role ) && isset( self::$roles[ $role ] ) ){

            return [ 'level' => $role, 'name' => self::$roles[ $role ] ]; }

        else if( is_string( $role ) && in_array( strtolower( $role ), self::$roles ) ){

            return [ 'level' => array_search( strtolower( $role ), self::$roles ), 'name' => strtolower( $role ) ]; }
        
        /** Return option(s) */

        return [ 'level' => 9999, 'name' => self::$roles[ 9999 ] ]; }

}
