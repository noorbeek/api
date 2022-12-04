<?php

/**
 * 
 */

namespace CC\Api;

class Session {

    /**
     * Option cache
     * @var array
     */
    
    public static $properties = [

        /** User */

        'id' => null,
        'user' => null,
        'role' => 9999,
        'organization' => 0,
        'type' => 'default',
        'requests' => 0 ];
    
    /**
     * Set/Get global Api properties
     * @param string|null $key property name
     * @param string $value Set property value (propertyal)
     */
    
    public static function set( $key, $value = null ){

        /** Set multiple properties */

        if( is_array( $key ) ){

            foreach( $key as $property => $value ){ self::set( $property, $value ); } }

        /** Set single property */

        else if( $value !== null ){ self::$properties[ strtolower( $key ) ] = $value; }
        
        /** Return property(s) */

        return is_string( $key ) ? self::get( $key ) : self::$properties;
        
    }
    
    /**
     * Get property value
     * @param  string $key property key
     */
    
    public static function get( $key = null ){
        
        /** Return property(s) */

        return $key ? ( array_change_key_case( self::$properties, CASE_LOWER )[ strtolower( $key ) ] ?? null ) : self::$properties; }

    /**
     * Start session
     * @param  mixed $user       User object or uid
     * @param  array  $properties Optional overrides
     */
    
    public static function start( array $user, $properties = [] ){

        /** Return if already active */

        if( self::get( 'role' ) < 9999 ){

            return self::get(); }

        /** Properties */

        $properties = array_merge([

            'user' => $user[ 'id' ],
            'role' => $user[ 'role' ],
            'organization' => $user[ 'organization' ][ 'id' ] ?? $user[ 'organization' ],
            'ip' => Request::get( 'ip' ),
            'startAt' => ( new \DateTime ) -> format( 'c' ),
            'validity' => Options::get( 'session.lifetime' ) ?? 60,
            'endAt' => ( new \DateTime ) -> add( new \DateInterval( 'PT' . ( Options::get( 'session.lifetime' ) ?? 60 ) . 'M' ) ) -> format( 'c' ),
            'type' => 'default' ], $properties );

        /** Check for existing session */

        $session = ( new Sql( 'main' ) )

            -> select( 'sessions' )
            -> where( 'user:' . $user[ 'id' ] . ' and ip:' . Request::get( 'ip' ) . ' and type:' . $properties[ 'type' ] . ' and endAt*' . ( new \DateTime ) -> format( 'c' ) )
            -> execute( )[ 0 ] ?? null;

        /** Update existing session */

        $properties[ 'requests' ] = ( $session[ 'requests' ] ?? 0 ) + 1;

        /** Update session record */

        $session ? ( new Sql( 'main' ) )

            -> update( 'sessions', [ 'requests' => $properties[ 'requests' ], 'endAt' => $properties[ 'endAt' ] ] )
            -> where( 'id:' . $session[ 'id' ] )
            -> execute( ) : true; 

        /** Set global session */

        $sessionId = $session[ 'id' ] ?? ( ( new Sql( 'main' ) )

            -> insert( 'sessions', $properties )
            -> execute( )[ 'id' ] ??

        new Error( _( 'Session could not be created' ), 500 ) );

        /** Set global session properties */

        self::set( $properties );

        /** Return session object */

        return [

            'organization' => $properties[ 'organization' ],
            'startAt' => $properties[ 'startAt' ],
            'validity' => $properties[ 'validity' ],
            'endAt' => $properties[ 'endAt' ],
            'authorization' => 'Bearer '. Cryptography::encrypt( implode( ':', [ $sessionId, $user[ 'id' ] ]) ) ]; }

    /**
     * Stop current session
     * @return boolean success?
     */
    
    public static function stop(){

        /** Unset authorization */

        Request::$authorization = null;

        /** Stop session */

        if( session_status() === PHP_SESSION_ACTIVE ){

            session_unset();
            session_regenerate_id( true );
            session_unset();
            session_destroy();
            session_write_close();
            setcookie( session_name(), '', 0, '/' ); }

        /** Stop session */

        self::get( 'id' ) ? ( new Sql( 'main' ) )

            -> update( 'sessions', [ 'endAt' => ( new \DateTime ) -> format( 'c' ) ] )
            -> where( 'id:' . self::get( 'id' ) )
            -> execute( ) : true;

        /** Reset session */

        Session::set([

            'id' => null,
            'user' => null,
            'organization' => 0,
            'role' => 9999,
            'type' => 'default',
            'requests' => 0 ]);

        /** Return */

        return true; }

    /**
     * Resume existing session
     * @param  string $authorization Token
     * @return [type]                [description]
     */
    
    public static function resume( string $authorization ){

        /** Return if already active */

        if( self::get( 'role' ) < 9999 ){

            return self::get(); }

        /** Validate token */

        if( ! preg_match( '/^(Basic|Bearer)\s([a-z0-9\=\_\-]+)$/i', $authorization, $token ) ){

            new Error( _( 'Invalid authorization token' ), 401 ); }

        /** Switch authentication mechanisms */

        switch( strtoupper( $token[ 1 ] ) ){

            case 'BASIC':

                /** Parse Basic token */

                $basic = explode( ':', base64_decode( $token[ 2 ] ) ); if( count( $basic ) !== 2 ){

                    self::stop(); new Error( _( 'Invalid authorization token (basic)' ), 401 ); }

                /** Authenticate */

                self::start( ( new Models\Users ) -> authenticate( $basic[ 0 ], $basic[ 1 ] ) ); break;
 
            case 'BEARER':

                /** Parse Bearer token */

                $bearer = explode( ':', Cryptography::decrypt( $token[ 2 ] ) ?? '' ); if( count( $bearer ) !== 2 ){

                    self::stop(); new Error( _( 'Invalid authorization token (bearer)' ), 401 ); }

                /** Fetch session */

                $session = ( new Sql( 'main' ) )

                    -> unrestrict( true )
                    -> select( 'sessions' )
                    -> where( 'id:' . $bearer[ 0 ] . ' and user:' . $bearer[ 1 ] . ' and endAt*' . ( new \DateTime ) -> format( 'c' ) )
                    -> execute( )[ 0 ] ??

                ( self::stop() && new Error( _( 'Invalid session' ), 401 ) );

                /** Fetch user */

                $user = ( new Sql( 'main' ) )

                    -> unrestrict( true )
                    -> select( 'users', 'id,role' )
                    -> where( 'id:' . $bearer[ 1 ] . ' and removed:false and blocked:false and verified:true' )
                    -> execute( )[ 0 ] ??

                ( self::stop() && new Error( _( 'Invalid user' ), 401 ) );

                /** Update values */

                $session[ 'role' ] = $user[ 'role' ];
                $session[ 'requests' ] = ++$session[ 'requests' ];
                $session[ 'endAt' ] = ( new \DateTime ) -> add( new \DateInterval( 'PT' . $session[ 'validity' ] . 'M' ) ) -> format( 'c' );

                /** Update session record */

                ( new Sql( 'main' ) )

                    -> unrestrict( true )
                    -> update( 'sessions', [ 'requests' => $session[ 'requests' ], 'endAt' => $session[ 'endAt' ] ] )
                    -> where( 'id:' . $session[ 'id' ] )
                    -> execute( ); 

                /** Set session */

                self::set( $session ); break; }

        /** Return */

        return self::get(); }



}

?>