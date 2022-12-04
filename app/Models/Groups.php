<?php

namespace CC\Api\Models;

    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Authorization;
    use CC\Api\Session;

class Groups {

    /** Routes */

    public static $routes = [];

	/** Model */

	public static $name = 'Groups';
	public static $nameSingle = 'Group';

	/** Database */

	public static $database = 'main';
	public static $table = 'groups';
    public static $key = 'id';
    public static $parent = 'groupOf';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ],
        'name' => [ 'column' => 'name', 'datatype' => 'string', 'required' => true ],
        'organization' => [ 'column' => 'organization', 'datatype' => 'hash', 'model' => 'organizations', 'required' => true ],
        'groupOf' => [ 'column' => 'groupOf', 'datatype' => 'hash', 'model' => 'groups' ] ];

    /** Contruct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/groups' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/groups/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order', 'tree' ] ] ); }],
            'POST/groups' => [ 'roles' => 'administrator', 'write' => function(){ return $this -> post(); }],
            'PUT/groups/:id' => [ 'roles' => 'administrator', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/groups/:id' => [ 'roles' => 'administrator', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

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

        /** Get from cache */

        if( $id && isset( $this -> cache[ $id ] ) ){

            return $this -> cache[ $id ]; }

        /** Create WHERE clause */

        $where = 'id' . ( $id ? ':' . $id : '!null' ) . ( isset( $options[ 'where' ] ) ? ' and ( ' . $options[ 'where' ] . ' )' : '' );
        $where .= Authorization::user( 'administrator' ) ? '' : ' and organization:' . Session::get( 'organization' );

        /** Get from database */

        $groups = ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'campaigns', $options[ 'select' ] ?? null )
            -> where( $where )
            -> order( $options[ 'order' ] ?? 'name' )
            -> execute( );

        /** Add to cache */

        foreach( $groups as &$group ){

            /** Add to cache */

            $this -> cache[ $group[ 'id' ] ] = $group; }

        /** Return */

        return $id ? ( $groups[ 0 ] ?? null ) : $groups; }

    /**
     * Format data model before change
     *
     * @param array $data
     * @return void
     */

    public function formatData( array $data ): array {

        $data = array_change_key_case( $data, CASE_LOWER );

        /** Set required data */

        $data[ 'organization' ] = Authorization::user( 'administrator' ) ? $data[ 'organization' ] ?? Session::get( 'organization' ) : Session::get( 'organization' );

        /** Return */

        return $data; }

    /**
     * Create
     *
     * @param array $data
     * @return void
     */

    public function post( $data = null ){

        /** Format data */

        $data = array_change_key_case( $data ?? Request::data(), CASE_LOWER );

        /** Check if user is accessible */

        $data[ 'organization' ] = Authorization::user( 'administrator' ) ? ( $data[ 'organization' ] ?? Session::get( 'organization' ) ) : Session::get( 'organization' );

        /** Continue */

        return ( new Sql( 'main' ) )

            -> insert( 'groups', $data )
            -> execute( ); }

    /**
     * Update
     *
     * @param string $id
     * @param array $data
     * @return void
     */

    public function put( string $id, ?array $data = null ){

        /** Check if allowed to write user */

        if( ! $this -> get( $id, [ 'sql' => false ] ) ){

            return new Error( 403 ); }

        /** Format data */
    
        $data = $this -> formatData( $data ? $data : Request::data() );

        /** Write user */

        return ( new Sql( 'main' ) )

            -> update( 'groups', $data )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Delete
     *
     * @param string $id
     * @return void
     */

    public function delete( string $id ){

        /** Check if allowed to write user */

        if( ! $this -> get( $id, [ 'sql' => false ] ) ){

            return new Error( 403 ); }

        /** Write user */

        return ( new Sql( 'main' ) )

            -> delete( 'groups', Request::data() )
            -> where( 'id:' . $id )
            -> execute( ); }

    
    /**
     * Get chain of children groups
     *
     * @param integer $id
     * @param array $parent
     * @param array $loopProtection
     * @return void
     */
    public function getGroupChain( int $id, $parent = [], $loopProtection = [] ){

        if( $id && ! is_string( $id ) || in_array( $id, $loopProtection ) ){

            return $parent; }

        /** Get group */

        $loopProtection[] = $id; $group = ( new Sql( 'main') )

            -> select( 'groups' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? false;

        if( ! $group || $id === $group[ 'groupOf' ] ){

            return $parent; }

        /** Append to parent */

        $group[ 'groupOf' ] = $group[ 'groupOf' ] ? $this -> getGroupChain( $group[ 'groupOf' ], $parent, $loopProtection ) : null;

        /** Return parent or fetch parent */

    return $group; }
    
    /**
     * Get flat chain of groups
     * @param  int $id         Group ID
     * @param  array  $groups Cache of parents
     * @return array             Chain
     */
    
    public function getFlatGroupChain( int $id, $groups = [], $ids = [] ){

        /** Get group */

        $group = ( new Sql( 'main') )

            -> select( 'groups' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? false;

        if( ! $group ){

            return $groups; }

        /** Append to parent */

        if( ! in_array( $id, $ids ) ){

            $groups[] = $group; } $ids[] = $id;

        /** Get parent */

        if( $group[ 'groupOf' ] ){

            return $this -> getFlatGroupChain( $group[ 'groupOf' ], $groups, $ids ); }

        /** Return parent or fetch parent */

    return $groups; }

}
