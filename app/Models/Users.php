<?php

/**
 * 
 */

namespace CC\Api\Models;

    use CC\Api\Models;
    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Response;
    use CC\Api\Cryptography;
    use CC\Api\Session;
    use CC\Api\Authorization;
    use CC\Api\Views;
    use CC\Api\Notifications\Mail;

class Users {

    /** Routes */

    public static $routes = [];

	/** Model */

	public static $name = 'Users';
	public static $nameSingle = 'User';

	/** Database */

	public static $database = 'main';
    public static $table = 'users';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ],
        'name' => [ 'column' => 'name', 'datatype' => 'string', 'required' => true ],
        'email' => [ 'column' => 'email', 'datatype' => 'string', 'required' => true ],
        'password' => [ 'column' => 'password', 'datatype' => 'password', 'roles' => 'r:root' ],
        'passwordReset' => [ 'column' => 'passwordReset', 'datatype' => 'string', 'roles' => 'r:root' ],
        'pin' => [ 'column' => 'pin', 'datatype' => 'crypt', 'roles' => 'r:root' ],
        'api' => [ 'column' => 'api', 'datatype' => 'crypt', 'roles' => 'r:root' ],
        'role' => [ 'column' => 'role', 'datatype' => 'integer', 'roles' => [ 'r:user', 'w:administrator' ] ],
        'organization' => [ 'column' => 'organization', 'datatype' => 'hash', 'model' => 'Organizations' ],
        'createdAt' => [ 'column' => 'createdAt', 'datatype' => 'datetime', 'readonly' => true ],
        'memberOf' => [ 'column' => 'memberOf', 'datatype' => 'array:hash', 'roles' => [ 'r:user', 'w:administrator' ] ],
        'ownerOf' => [ 'column' => 'ownerOf', 'datatype' => 'array:hash', 'roles' => [ 'r:user', 'w:administrator' ] ],
        'verified' => [ 'column' => 'verified', 'datatype' => 'boolean' ],
        'blocked' => [ 'column' => 'blocked', 'datatype' => 'boolean' ],
        'removed' => [ 'column' => 'removed', 'datatype' => 'boolean' ],
        'signature' => [ 'column' => 'signature', 'datatype' => 'html' ] ];

    /** Contruct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/users' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/users/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join' => true, 'order' => true ] ] ); }],
            'POST/users' => [ 'roles' => 'w:authenticated', 'write' => function(){ return $this -> post(); }],
            'PUT/users/:id' => [ 'roles' => 'w:authenticated', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/users/:id' => [ 'roles' => 'w:authenticated', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

        /** Return */

        return $this; }

    /** Cache */

    private $cache = [];
    private $accounts = [];

    /** Me cache */

    private static $myGroups = [];
    private static $myScopes = [];

    /**
     * Get all
     * @param  string   $id Group ID hash
     * @return array    Group(s)
     */
    
    public function get( ?string $id = null, ?array $options = [] ){

        /** Convert 'me' to authenticated user */

        $id = is_string( $id ) && strtolower( $id ) === 'me' ? Session::get( 'user' ) : $id;

        /** Convert 'id' to options */

        $options = is_array( $id ) ? $id : $options;
        $id = is_array( $id ) ? null : $id;

        /** Build WHERE clause */

        $where = 'id' . ( $id ? ':' . $id : '!null' );
        $where .= isset( $options[ 'where' ] ) ? ' and ( ' . $options[ 'where' ] . ' )' : '';
        $where .= Authorization::user( 'administrator' ) ? '' : ' and organization:' . Session::get( 'organization' );

        /** Get from cache or database */

        $users = $id && isset( $this -> cache[ $id ] ) ?

            [ $this -> cache[ $id ] ] :

        ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'users', $options[ 'select' ] ?? null )
            -> where( $where )
            -> order( $options[ 'order' ] ?? 'name' )
            -> execute( );

        /** Eumerate */

        foreach( $users as &$user ){

            /** Embed ownerOf */

            if( Models::requireJoin( 'ownerOf', $options ) ){

                foreach( $user[ 'ownerOf' ] as &$group ){

                    $group = ( new Groups ) -> get( $group ); } }

            /** Embed memberOf */

            if( Models::requireJoin( 'memberOf', $options ) ){

                foreach( $user[ 'memberOf' ] as &$group ){

                    $group = ( new Groups ) -> get( $group ); } }

            /** Embed groups */

            if( Models::requireJoin( 'groups', $options ) ){

                $user[ 'groups' ] = $this -> getUserGroups( $user[ 'id' ] ); }

            /** Embed scopes */

            if( Models::requireJoin( 'scopes', $options ) ){

                $user[ 'scopes' ] = $this -> getUserScopes( $user[ 'id' ] ); }

            /** Add user to cache */

            $this -> cache[ $user[ 'id' ] ] = $user;

        }

        /** Return */

        return $id ? ( $users[ 0 ] ?? null ) : $users; }

    /**
     * format User data before PUT/POST
     *
     * @param array $data
     * @return array
     */

    public function formatData( array $data, ?array $originalData = [] ){

        /** Make lowercase keys */

        $data = array_change_key_case( $data, CASE_LOWER );

        /** Organization */

        if( isset( $data[ 'organization' ] ) && ! Authorization::user( 'administrator' ) && Session::get( 'organization' ) !== $data[ 'organization' ] ){

            new Error( _( 'Invalid organization' ), 400 ); }

        /** Password */

        if( isset( $data[ 'password' ] ) && ( $data[ 'passwordrepeat' ] ?? null ) !== $data[ 'password' ] ){

            new Error( _( 'The passwords do not match (password, passwordRepeat)' ), 400 ); }

        /** Check regex pattern*/

        if( isset( $data[ 'password' ] ) ){ foreach( [ '@[A-Z]@', '@[a-z]@', '@[0-9]@', '@[^\w]@', '/^.{8,}$/' ] as $regex ){

            if( ! preg_match( $regex, $data[ 'password' ] ) ){

                new Error( _( 'The password must contain of at least 8 lowercase, uppercase, alfanumeric and special characters' ), 400 ); } } }

        /** Check if email exists */

        if( isset( $data[ 'email' ] ) && $data[ 'email' ] !== $originalData[ 'email' ] && count( ( new Sql( 'main' ) )
            
            -> select( 'users' )
            -> where( 'removed:false and email:' . $data[ 'email' ] )
            -> execute( ) ) ){
        
        return new Error( _( 'This email address is already in use' ), 400 ); }

        /** Role */

        if( isset( $data[ 'role' ] ) && ( int ) $data[ 'role' ] < Session::get( 'role' ) ){

            new Error( sprintf( _( 'You are not allowed to create a: "%s"' ), $data[ 'role' ] ), 400 ); }

        /** Return formatted model */

        return array_change_key_case( $data, CASE_LOWER ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function put( string $id, ?array $data = null, $unrestrict = null ){

        /** Convert 'me' to authenticated user */

        $id = is_string( $id ) && strtolower( $id ) === 'me' ? Session::get( 'user' ) : $id;

        /** Check if user is accessible */

        $user = $this -> get( $id ); if( ! $user || ( ! Authorization::user( 'manager' ) && $id !== Session::get( 'user' ) ) ){
        
            return new Error( _( 'The user does not exist or you are not authorized to view this user' ), 404 ); }

        /** Check if allowed to write user */

        if( ! $this -> get( $id, [ 'sql' => false ] ) ){

            return new Error( 403 ); }

        /** Parse user data */

        $data = $this -> formatData( $data ?? Request::data(), $user );

        /** Write user */

        $update = ( new Sql( 'main' ) )

            -> unrestrict( is_null( $unrestrict ) ? [ 'password', 'passwordReset', 'role' ] : $unrestrict )
            -> update( 'users', $data )
            -> where( 'id:' . $id )
            -> execute( );

        /** Send verify mail if mail changes */

        if( ( $data[ 'email' ] ?? false ) && $data[ 'email' ] !== $user[ 'email' ] ){

            $this -> sendAccountVerification( $data[ 'email' ] ?? '' ); }

        /** Write user */

        return $update; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function post( ?array $data = null, $unrestrict = null ){

        /** Parse user data */

        $data = $this -> formatData( $data ?? Request::data() );

        /** Set organization if no admin */

        if( ! isset( $data[ 'organization' ] ) && ! Authorization::user( 'administrator' ) ){
        
            $data[ 'organization' ] = Session::get( 'organization' ); }

        /** Continue */

        $user = ( new Sql( 'main' ) )

            -> unrestrict( is_null( $unrestrict ) ? [ 'password', 'passwordReset', 'role' ] : $unrestrict )
            -> insert( 'users', $data )
            -> execute( );

        /** Send verify mail */

        $this -> sendAccountVerification( $data[ 'email' ] ?? '', $data[ 'redirectUrl' ] ?? $data[ 'redirecturl' ] ?? false );

        /** Continue */

        return $user; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function delete( string $id ){

        /** Convert 'me' to authenticated user */

        $id = is_string( $id ) && strtolower( $id ) === 'me' ? Session::get( 'user' ) : $id;

        /** Check if allowed to write user */

        if( ! $this -> get( $id, [ 'sql' => false ] ) ){

            return new Error( 403 ); }

        /** Check if user is accessible */

        $user = $this -> get( $id ); if( ! $user || ( ! Authorization::user( 'manager' ) && $id !== Session::get( 'user' ) ) ){
        
            return new Error( _( 'The user does not exist or you are not authorized to view this user' ), 404 ); }

        /** Continue */

        return $id !== Session::get( 'user' ) ? ( new Sql( 'main' ) )

            -> delete( 'users' )
            -> where( 'id:' . $id )
            -> execute( ) :

        new Error( _( 'You cannot remove yourself ;)' ), 400 ); }

    /**
     * Authenticate username/password
     * @param  string $username 
     * @param  string $password 
     * @return array           User
     */
    
    public function authenticate( $username, $password, $softFail = false ){

        $error = false;

        /** Validate username & password */

        if( ! $username || ! filter_var( $username, FILTER_VALIDATE_EMAIL ) ){

            $error = [ 'error' => _( 'No valid username supplied (not an email address)' ), 'code' => 400 ];

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); } if( ! $password ){

            $error = [ 'error' => _( 'No password supplied' ), 'code' => 400 ];

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); }

        /** Fetch user */

        $user = ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> select( 'users' )
            -> where( 'removed:false and email:' . $username )
            -> execute( )[ 0 ] ?? false;

        if( ! $user ){

            $error = [ 'error' => _( 'Invalid username' ), 'code' => 401 ];

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); }

        /** Validate verified */

        if( $user[ 'verified' ] === false ){

            $error = [ 'error' => _( 'Your email address is not yet verified, check your inbox for the verification code' ), 'code' => 401 ];

            /** Send verify mail */

            $this -> sendAccountVerification( $username );

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); }

        /** Validate status */

        if( $user[ 'blocked' ] === true ){

            $error = [ 'error' => _( 'This account is blocked, contact your administrator' ), 'code' => 401 ];

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); }

        /** Validate password */

        if( ! Cryptography::password( $user[ 'password' ] ?? '', $password ) ){

            $error = [ 'error' => _( 'Invalid password' ), 'code' => 401 ];

        return $softFail ? $error : new Error( $error[ 'error' ], $error[ 'code' ] ); }

        /** Return */

        return $user; }

    /**
     * Get user account
     * @param  string $id User ID
     * @return object     User object
     */
    
    public function getAccount( $id = 'me' ){

        /** Convert 'me' to authenticated user */

        $id = is_string( $id ) && strtolower( $id ) === 'me' ? Session::get( 'user' ) : $id; if( !$id ){

            return null; }

        /** Get from cache or database */

        $account = isset( $this -> accounts[ $id ] ) ?

            $this -> accounts[ $id ]

        : ( new Sql( 'main' ) )
            
            -> unrestrict( true )
            -> select( 'users' )
            -> join( 'organizations', 'organization:organizations.id' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? null;

        $this -> cache[ $id ] = $account;

        /** Return */

        return $account; }

    /**
     * Get groups (recurring) of user
     * @param  string $id User ID
     * @return array     Groups
     */
    
    public function getUserGroups( $id = 'me' ){

        /** Return from cache */

        if( count( self::$myGroups ) ){

            return self::$myGroups; }

        /** Parse id */

        $id = is_string( $id ) && strtolower( $id ) === 'me' ? Session::get( 'user' ) : $id;

        /** Get & validate user */

        $user = ( new Sql( 'main' ) )

            -> select( 'users' )
            -> where( 'id' . ( $id ? ':' . $id : '!null' ) )
            -> order( 'name' )
            -> execute( )[ 0 ] ?? null;

        if( ! $user || ( ! count( $user[ 'ownerOf' ] ) && ! count( $user[ 'memberOf' ] ) ) ){

            return []; }

        /** Enumerate groups */

        $groups = []; foreach( array_merge( $user[ 'ownerOf' ], $user[ 'memberOf' ] ) as $group ){

            $groups = array_merge( $groups, ( new Groups ) -> getFlatGroupChain( $group ) ); }

        /** Get unique groups */

        $uniqueGroups = $ids = []; foreach( $groups as $group ){

            if( ! in_array( $group[ 'id' ], $ids ) ){

                $uniqueGroups[] = $group; $ids[] = $group[ 'id' ]; }}

        /** Set memberOf/ownerOf */

        $groups = $uniqueGroups; foreach( $groups as &$group ){

            $group[ 'ownerOf' ] = in_array( $group[ 'id' ], $user[ 'ownerOf' ] ?? [] ) ? true : false;
            $group[ 'memberOf' ] = true; }

        /** Return */

        return $groups; }

    /**
     * Get groups (recurring) of user
     * @param  string $id User ID
     * @return array     Groups
     */
    
    public function getUserScopes( $id = 'me' ){

        /** Return from cache */

        if( count( self::$myScopes ) ){

            return self::$myScopes; }

        /** Get & validate user */

        $groups = $this -> getUserGroups( $id ); if( ! count( $groups ) ){

            return []; }

        /** Enumerate groups */

        $groupIds = []; foreach( $groups as $group ){

            if( ! in_array( $group[ 'id' ], $groupIds ) ){

                $groupIds[] = $group[ 'id' ]; }}

        /** Set memberOf/ownerOf */

        return ( new Sql( 'main' ) )

            -> select( 'scopes' )
            -> where( 'group:' . implode( ' or group:', $groupIds ) )
            -> execute( ); }

    /**
     * Send password reset link to
     *
     * @param string $username
     * @return void
     */

    public function sendPasswordReset( string $username ) {

        /** Get user or 401 */

        $user = ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> select( 'users' )
            -> where( 'email:' . $username )
            -> execute( )[ 0 ] ?? new Error( _( 'Invalid username' ), 401 );

        /** Authenticate with that user */

        Session::start( $user );

        /** Generate reset token */

        $token = Cryptography::uuid(); ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> update( 'users', [ 'passwordReset' => $token ])
            -> where( 'id:' . $user[ 'id' ] )
            -> execute( );

        /** Send mail with new password */

        $mail = ( new Mail ) -> Send([

            'to' => $user[ 'email' ],
            'subject' => 'Wachtwoord reset',
            'description' => 'Wachtwoord reset link',
            'body' => '
              <p>Hallo ' . $user[ 'name' ] . '</p>
              <p>Dit is je wachtwoord reset link. Als je op deze link klikt ontvang je een nieuw wachtwoord.</p>
              <p><a href="' . Request::$url . 'me/password/' . $token . '">' . Request::$url . 'me/password/' . $token . '</a></p>
              <p>Je kunt deze mail negeren als je geen wachtwoord reset hebt aangevraagd.</p>',
            'action' => Request::$url . 'me/password/' . $token,
            'actionText' => 'Wachtwoord resetten' ]) ?? new Error( _( 'The mail could not be sent, please try again later.' ), 500 );

        /** Write new password */

        return $mail ? _( 'The password reset link has been sent.' ) : new Error( _( 'Failed to send the reset link.' ), 500 ); }

    /**
     * Send password
     *
     * @param string $token
     * @return void
     */

    public function passwordReset( string $token, array $user = null ): string {

        /** Get user or 401 */

        $user = $user ? $user : ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> select( 'users' )
            -> where( 'passwordReset:' . $token )
            -> execute( )[ 0 ] ?? null;

        /** Display status page */

        if( ! $user ){

            return ( new Views ) -> message([
                
                'title' => 'Link niet geldig',
                'message' => 'Deze wachtwoord reset link is niet langer geldig.',
                'status' => 'warning' ]); }

        /** Authenticate with that user */

        Session::start( $user );

        /** Reset password */

        $password = ''; foreach([ 'abcdefghijklmnopqrstuvwxyz' => 4, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' => 4, '!@#$%&*_-=+' => 2 ] as $characters => $amount ){

            $password = str_shuffle( $password . substr( str_shuffle( $characters ), 0, $amount ) ); }

        /** Send mail with new password */

        $mail = ( new Mail ) -> Send([

            'to' => $user[ 'email' ],
            'subject' => 'Je nieuwe wachtwoord',
            'description' => 'Je nieuwe wachtwoord',
            'body' => '
              <p>Hallo ' . $user[ 'name' ] . '</p>
              <p>Je nieuwe wachtwoord is <b>' . $password . '</b> en is hoofdlettergevoelig.</p>
              <p>Je kunt inloggen en bij je profiel een zelfgekozen wachtwoord instellen.</p>' ]) ?? false;

        /** Display status page */

        if( ! $mail ){

            return ( new Views ) -> message([
                
                'title' => 'Wachtwoord niet gereset',
                'message' => 'Er is een fout opgetreden bij het versturen van de wachtwoord reset link. Probeer het a.u.b. later opnieuw.',
                'status' => 'error' ]); }

        /** Write new password */

        ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> update( 'users', [ 'password' => $password ])
            -> where( 'id:' . $user[ 'id' ] )
            -> execute( );
            
        /** Display status page */

        return ( new Views ) -> message([
            
            'title' => 'Wachtwoord verstuurd!',
            'message' => 'Hallo ' . $user[ 'name' ] . ', het wachtwoord is verstuurd naar ' . $user[ 'email' ] . '.<br>Je kunt inloggen en bij je profiel een zelfgekozen wachtwoord instellen.',
            'status' => 'success' ]); }

    /**
     * Send password reset link to
     *
     * @param string $username
     * @return void
     */

    public function sendAccountVerification( string $username, $redirectUrl = false ) {

        /** Get user or 401 */

        $user = ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> select( 'users' )
            -> where( 'email:' . $username )
            -> execute( )[ 0 ] ?? new Error( _( 'Invalid username' ), 401 );

        /** Check if account is already verified */

        if( $user[ 'verified' ] ){

            new Error( _( 'Account is already verified.' ), 403 ); }

        /** Authenticate with that user */

        Session::start( $user );

        /** Generate reset token */

        $token = Cryptography::encrypt( json_encode([ 'user' => $user[ 'id' ], 'nonce' => uniqid(), 'redirectUrl' => $redirectUrl ], true ) );

        /** Send mail with new password */

        $mail = ( new Mail ) -> Send([

            'to' => $user[ 'email' ],
            'subject' => 'Accountvalidatie',
            'description' => 'Valideer uw account',
            'body' => '
              <p>Hallo ' . $user[ 'name' ] . '</p>
              <p>U kunt bijna gebruik maken van uw account. Om uw account en e-mailadres te valideren moet u op de onderstaande link klikken.</p>
              <p><a href="' . Request::$url . 'me/verify/' . $token . '">' . Request::$url . 'me/verify/' . $token . '</a></p>
              <p>Je kunt deze mail negeren als je geen wachtwoord reset hebt aangevraagd.</p>',
            'action' => Request::$url . 'me/verify/' . $token,
            'actionText' => 'Account valideren' ]) ?? new Error( _( 'The mail could not be sent, please try again later.' ), 500 );

        /** Write new password */

        return $mail ? _( 'The verification mail has been sent.' ) : new Error( _( 'Failed to send the verification mail.' ), 500 ); }

    /**
     * Send password
     *
     * @param string $token
     * @return void
     */

    public function accountVerification( string $token ){

        /** Read token or die */

        $token = json_decode( Cryptography::decrypt( $token ), true ) ?? false;
        $id = $token[ 'user' ] ?? false; if( ! $id ){ return ( new Views ) -> message([
                
            'title' => 'Token niet geldig',
            'message' => 'De token is niet geldig.',
            'status' => 'warning' ]); }

        /** Get user or die */

        $user = ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> select( 'users' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? false;
            
        if( ! $user ){ return ( new Views ) -> message([
                
            'title' => 'Account bestaat niet (meer)',
            'message' => 'Dit account bestaat niet (meer).',
            'status' => 'warning' ]); }

        /** Authenticate with that user */

        Session::start( $user );

        /** Update verification */

        ( new Sql( 'main' ) )

            -> unrestrict( true )
            -> update( 'users', [ 'verified' => true ])
            -> where( 'id:' . $user[ 'id' ] )
            -> execute( );

        /** Send password */

        if( ! $user[ 'password' ] ){

            $this -> passwordReset( false, $user ); }

        /** Redirect to login page */

        if( $token[ 'redirectUrl' ] ?? false ){

            header( 'Location: ' . $token[ 'redirectUrl' ] ); die(); }
            
        /** Display status page */

        return ( new Views ) -> message([
            
            'title' => 'Account geverifieerd!',
            'message' => 'Hallo ' . $user[ 'name' ] . ', uw account is geverifieerd en geactiveerd!',
            'status' => 'success' ]); }

}
