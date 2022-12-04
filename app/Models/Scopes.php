<?php

/**
 * 
 */

namespace CC\Api\Models;

    use Models;
    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Cryptography;
    use CC\Api\Session;

class Scopes {

    /** Routes */

    public static $routes = [];

	/** Model */

	public static $name = 'Scopes';
	public static $nameSingle = 'Scope';

	/** Database */

	public static $database = 'main';
	public static $table = 'scopes';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ],
        'group' => [ 'column' => 'group', 'datatype' => 'hash', 'model' => 'groups' ],
        'scope' => [ 'column' => 'scope', 'datatype' => 'string' ],
        'mode' => [ 'column' => 'mode', 'datatype' => 'switch', 'switch' => [

            'r' => [ 'value' => 'Read', 'default' => true ],
            'w' => [ 'value' => 'Write' ],
            'x' => [ 'value' => 'Administration' ]

        ]]];

    /** Contruct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/scopes' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/scopes/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order' ] ] ); }],
            'POST/scopes' => [ 'roles' => 'w:administrator', 'write' => function(){ return $this -> post(); }],
            'PUT/scopes/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/scopes/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

        /** Return */

        return $this; }

    /**
     * Get all
     * @param  string   $id Scope ID hash
     * @return array    Scope(s)
     */
    
    public function get( ?string $id = null, ?array $options = [] ){

        /** Get from database */

        $scopes = ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'campaigns', $options[ 'select' ] ?? null )
            -> where( $options[ 'where' ] ?? 'id' . ( $id ? ':' . $id : '!null' ) )
            -> order( $options[ 'order' ] ?? 'id' )
            -> execute( );

        /** Return */

        return $id ? ( $scopes[ 0 ] ?? null ) : $scopes; }

    /**
     * Create
     * @return array    Group(s)
     */
    
    public function post( ){

        return ( new Sql( 'main' ) )

            -> insert( 'scopes', Request::data() )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id Scope ID hash
     * @return array    Scope(s)
     */
    
    public function put( string $id ){

        return ( new Sql( 'main' ) )

            -> update( 'scopes', Request::data() )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id Scope ID hash
     * @return array    Scope(s)
     */
    
    public function delete( string $id ){

        return ( new Sql( 'main' ) )

            -> update( 'scopes', Request::data() )
            -> where( 'id:' . $id )
            -> execute( ); }

}

?>