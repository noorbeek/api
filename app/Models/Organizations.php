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

class Organizations {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Organizations';
	public static $nameSingle = 'Organization';

	/** ORM */

	public static $database = 'local';
	public static $table = 'organizations';
    public static $key = 'id';
    public static $parent = 'organizationOf';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ],
        'name' => [ 'column' => 'name', 'datatype' => 'string', 'required' => true ],
        'description' => [ 'column' => 'description', 'datatype' => 'string' ],
        'organizationOf' => [ 'column' => 'organizationOf', 'datatype' => 'hash', 'model' => 'organizations' ],
        'customerOf' => [ 'column' => 'customerOf', 'datatype' => 'hash', 'model' => 'organizations' ],
        'accountManager' => [ 'column' => 'accountManager', 'datatype' => 'hash', 'model' => 'users' ],
        'supportManager' => [ 'column' => 'supportManager', 'datatype' => 'hash', 'model' => 'users' ],
        'technicalManager' => [ 'column' => 'technicalManager', 'datatype' => 'hash', 'model' => 'users' ],
        'projectManager' => [ 'column' => 'projectManager', 'datatype' => 'hash', 'model' => 'users' ],
        'financialAccount' => [ 'column' => 'financialAccount', 'datatype' => 'string' ],
        'street' => [ 'column' => 'street', 'datatype' => 'string' ],
        'houseNumber' => [ 'column' => 'houseNumber', 'datatype' => 'string' ],
        'houseNumberExtension' => [ 'column' => 'houseNumberExtension', 'datatype' => 'string' ],
        'zipCode' => [ 'column' => 'zipCode', 'datatype' => 'string' ],
        'city' => [ 'column' => 'city', 'datatype' => 'string' ],
        'website' => [ 'column' => 'website', 'datatype' => 'string' ],
        'phoneNumber' => [ 'column' => 'phoneNumber', 'datatype' => 'string' ],
        'logo' => [ 'column' => 'logo', 'datatype' => 'string' ],
        'color' => [ 'column' => 'color', 'datatype' => 'string' ] ];

    /** Contruct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/organizations' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/organizations/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order', 'tree' ] ] ); }],
            'POST/organizations' => [ 'roles' => 'w:administrator', 'write' => function(){ return $this -> post(); }],
            'PUT/organizations/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/organizations/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

        /** Return */

        return $this; }

    /** Cache */

    private $cache = [];

    /**
     * Get all
     * @param  string   $id Group ID hash
     * @return array    Group(s)
     */
    
    public function get( ?string $id = null, ?array $options = [] ){

        /** Get from cache if ID exists in self::$cache */

        $organizations = $id && isset( $this -> cache[ $id ] ) ?
        
            [ $this -> cache[ $id ] ] :

        /** OR: get campaigns from database */

        ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'organizations', $options[ 'select' ] ?? null )
            -> where( $options[ 'where' ] ?? 'id' . ( $id ? ':' . $id : '!null' ) )
            -> order( $options[ 'order' ] ?? 'name' )
            -> execute( );

        /** Eumerate */

        foreach( $organizations as &$organization ){

            /** Add organization to cache */

            $this -> cache[ $organization[ 'id' ] ] = $organization; }

        /** Return */

        return $id ? ( $organizations[ 0 ] ?? null ) : $organizations; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function put( string $id ){

        return ( new Sql( 'main' ) )

            -> update( 'organizations', Request::data() )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function post(){

        return ( new Sql( 'main' ) )

            -> insert( 'organizations', Request::data() )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function delete( string $id ){

        return ( new Sql( 'main' ) )

            -> delete( 'organizations' )
            -> where( 'id:' . $id )
            -> execute( ); }

}
