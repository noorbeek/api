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
use CC\Api\Datatypes;
use CC\Api\Session;

class Registry {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Registry';
	public static $nameSingle = 'Registry';

	/** ORM */

	public static $database = 'local';
	public static $table = 'registry';
    public static $property = 'property';
	public static $fields = [

        'property' => [ 'column' => 'property', 'datatype' => 'string', 'required' => true ],
        'value' => [ 'column' => 'value', 'datatype' => 'string', 'required' => true ],
        'datatype' => [ 'column' => 'datatype', 'datatype' => 'string' ] ];

    /** Contruct */

    public function __construct(){

        /** Return */

        return $this; }

    /** Cache */

    private $cache = [];

    /**
     * Get all
     * @param  string   $property Group ID hash
     * @return array    Group(s)
     */
    
    public function get( $property = null ){

        /** Get from cache or database */

        $registry = $property && isset( $this -> cache[ $property ] ) ? [ $this -> cache[ $property ] ] : ( new Sql( 'main' ) )

            -> select( 'registry' )
            -> where( 'property' . ( $property ? ':' . $property : '!null' ) )
            -> order( 'property' )
            -> execute( );

        /** Eumerate */

        $map = []; foreach( $registry as &$registryItem ){

            /** Convert value to correct datatype */

            switch( $registryItem['datatype'] ){
                case 'array':
                    $value = json_decode($registryItem[ 'value' ]); break;
                case 'json':
                    $value = json_decode($registryItem[ 'value' ], true); break;
                default:
                    $value = Datatypes::convert( $registryItem[ 'value' ], $registryItem[ 'datatype' ] ); }

            /** Add registryItem to cache */

            $map[] = $this -> cache[ $registryItem[ 'property' ] ] = [ 'property' => $property, 'value' => $value, 'datatype' => $registryItem[ 'datatype' ] ]; }

        /** Return */

        return $property ? ( $map[ 0 ] ?? null ) : $map; }

    /**
     * Get all
     * @param  string   $property User ID hash
     * @return array    User(s)
     */
    
    public function set( string $property, $value, $datatype = null ){

        /** Check if registry item exists */

        $registryItem = $this -> get( $property );
        $datatype = $datatype ? $datatype : ( $registryItem[ 'datatype' ] ?? 'string' );
        $data = [ 'property' => $property, 'value' => $value, 'datatype' => $datatype ];

        /** Update of create */

        return array_merge( $registryItem ? ( new Sql( 'main' ) )

            -> update( 'registry', $data)
            -> where( 'property:' . $property )
            -> execute( ) : 
            
        ( new Sql( 'main' ) )

            -> insert( 'registry', $data)
            -> execute( ), $this->get( $property ) ); }

    /**
     * Get all
     * @param  string   $property User ID hash
     * @return array    User(s)
     */
    
    public function delete( string $property ){

        return ( new Sql( 'main' ) )

            -> delete( 'registry' )
            -> where( 'property:' . $property )
            -> execute( ); }

    /**
     *
     * Special methods
     * 
     */

}
