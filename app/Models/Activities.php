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

class Activities {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Activities';
	public static $nameSingle = 'Activity';

	/** ORM */

	public static $database = 'local';
	public static $table = 'activities';
    public static $key = 'id';
    public static $parent = 'activityOf';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'integer', 'readonly' => true ],
        'name' => [ 'column' => 'name', 'datatype' => 'string', 'required' => true ],
        'startAt' => [ 'column' => 'startAt', 'datatype' => 'datetime' ],
        'leadTime' => [ 'column' => 'leadTime', 'datatype' => 'float:2' ],
        'activityOf' => [ 'column' => 'activityOf', 'datatype' => 'int', 'model' => 'activities' ],
        'owners' => [ 'column' => 'owners', 'datatype' => 'array' ],
        'groups' => [ 'column' => 'groups', 'datatype' => 'array' ],
        'type' => [ 'column' => 'type', 'datatype' => 'switch', 'switch' => [

            'task' => [ 'value' => 'Taak', 'default' => true ],
            'form' => [ 'value' => 'Formulier' ] ] ],

        'status' => [ 'column' => 'status', 'datatype' => 'switch', 'switch' => [

            0 => [ 'value' => 'New', 'default' => true ],
            10 => [ 'value' => 'Open' ],
            20 => [ 'value' => 'On hold' ],
            30 => [ 'value' => 'In progress' ],
            100 => [ 'value' => 'Closed' ] ] ]

        ];

    /** Contruct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/activities' => [ 'roles' => 'user', 'write' => function(){ return $this -> get( null, [ 'allow' => true ]); }],
            'GET/activities/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order' ] ] ); }],
            'POST/activities' => [ 'roles' => 'w:administrator', 'write' => function(){ return $this -> post(); }],
            'PUT/activities/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/activities/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

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

        $activities = $id && isset( $this -> cache[ $id ] ) ?
        
            [ $this -> cache[ $id ] ] :

        /** OR: get activities from database */

        ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'activities', $options[ 'select' ] ?? null )
            -> where( $options[ 'where' ] ?? 'id' . ( $id ? ':' . $id : '!null' ) )
            -> order( $options[ 'order' ] ?? 'startsAt desc' )
            -> execute( );

        /** Eumerate */

        foreach( $activities as &$activity ){

            /** Add activity to cache */

            $this -> cache[ $activity[ 'id' ] ] = $activity; }

        /** Return */

        return $id ? ( $activities[ 0 ] ?? null ) : $activities; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function put( string $id ){

        /** Get data */

        $data = array_change_key_case( Request::data(), CASE_LOWER );

        /** Set children if activity = closed */

        if( array_key_exists( 'status', $data ) && intval( $data[ 'status' ] ) === 100 ){

            /** Get children */

            $children = []; foreach( Models::getChildren( $id, [

                'model' => self::$name,
                'parent' => self::$parent,
                'multiDimensional' => false

            ]) as $child ){

                $children[] = $child[ 'id' ]; }

            /** Update children */

            if( count( $children ) ){

                ( new Sql( 'main' ) )

                    -> update( 'activities', [ 'status' => 100 ])
                    -> where( 'status!100 and ( id:' . implode( ' or id:', $children ) . ' )' )
                    -> execute( ); } }

        /** Update activity */

        return ( new Sql( 'main' ) )

            -> update( 'activities', $data )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function post(){

        return ( new Sql( 'main' ) )

            -> insert( 'activities', Request::data() )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function delete( string $id ){

        return ( new Sql( 'main' ) )

            -> update( 'activities', Request::data() )
            -> where( 'id:' . $id )
            -> execute( ); }

}

?>