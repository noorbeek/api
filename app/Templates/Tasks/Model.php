<?php

namespace {{ namespace }};

    use CC\Api\Sql;
    use CC\Api\Request;
    use CC\Api\Error;
    use CC\Api\Session;

    use Moment\Moment;

class {{ modelName }} {

    /** Routes placeholder */

    public static $routes = [];

	/** Name of resource */

	public static $name = '{{ modelName }}';
	public static $nameSingle = '{{ modelNameSingle }}';

	/** Database mapping */

	public static $database = '{{ database }}';
	public static $table = '{{ modelName | lower }}';
    public static $key = '{{ key }}';
    public static $parent = {% if parent %}'{{ parent }}'{% else %}null{% endif %};
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ], // convert number to hash ID to mask record ID
        'someModel' => [ 'column' => 'someModel', 'datatype' => 'hash', 'model' => 'someModel', 'required' => true ],
        'someNumber' => [ 'column' => 'someNumber', 'datatype' => 'int' ],
        'someJson' => [ 'column' => 'someJson', 'datatype' => 'json' ],
        'someArray' => [ 'column' => 'someArray', 'datatype' => 'array' ],
        'someEmail' => [ 'column' => 'someEmail', 'datatype' => 'email' ],
        'somePhonenumber' => [ 'column' => 'somePhonenumber', 'datatype' => 'phone' ],
        'someDatetime' => [ 'column' => 'someDatetime', 'datatype' => 'datetime' ],
        'someDate' => [ 'column' => 'someDate', 'datatype' => 'date' ],
        'someTime' => [ 'column' => 'someTime', 'datatype' => 'time' ],
        'someFloat' => [ 'column' => 'someFloat', 'datatype' => 'float:2' ],
        'someBoolean' => [ 'column' => 'someBoolean', 'datatype' => 'boolean' ], // true/false
        'somePassword' => [ 'column' => 'somePassword', 'datatype' => 'password', 'roles' => [ 'x:root' ] ], // 1-way encryption
        'someCrypt' => [ 'column' => 'someCrypt', 'datatype' => 'crypt', 'roles' => 'manager' ], // 2-way en/decryption
        'type' => [ 'column' => 'type', 'datatype' => 'switch', 'switch' => [

            'A' => [ 'value' => 'Some value A', 'default' => true ],
            'B' => [ 'value' => 'Some value B' ],
            'C' => [ 'value' => 'Some value C' ]

        ]] ];

    /** 
     * 
     * Add CRUD routes to Router
     * 
     */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/{{ modelName | lower }}' => [ 'roles' => 'r:user', 'write' => function(){ return $this -> get( null, [ 'allow' => true ]); }],
            'GET/{{ modelName | lower }}/:id' => [ 'roles' => 'r:user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order' ] ] ); }],
            'POST/{{ modelName | lower }}' => [ 'roles' => 'w:manager', 'write' => function(){ return $this -> post(); }],
            'PUT/{{ modelName | lower }}/:id' => [ 'roles' => 'w:manager', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/{{ modelName | lower }}/:id' => [ 'roles' => 'w:manager', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

        /** Return */

        return $this; }

    /** Cache fetched {{ modelName | lower }} */

    private $cache = [];

    /**
     * Get all items or a single item
     * @param  string   $id
     * @return array    Item(s)
     */
    
    public function get( ?string $id = null, ?array $options = [] ): array {

        /** Get from cache if ID exists in self::$cache */

        ${{ modelName | lower }} = $id && isset( $this -> cache[ $id ] ) ?
        
            [ $this -> cache[ $id ] ] :

        /** OR: get {{ modelName | lower }} from database */
            
        ${{ modelName | lower }} = ( new Sql( '{{ database }}' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( '{{ modelName | lower }}', $options[ 'select' ] ?? null )
            -> join( 'someModel', 'someModel:someModel.id' )
            -> where( 'id' . ( $id ? ':' . $id : '!null' ) . ( isset( $options[ 'where' ] ) ? ' and ( ' . $options[ 'where' ] . ' )' : '' ) )
            -> order( $options[ 'order' ] ?? 'someDatetime desc' )
            -> execute( );

        /** Eumerate {{ modelName | lower }} */

        foreach( ${{ modelName | lower }} as &${{ modelNameSingle | lower }} ){

            /** Add/overwrite${{ modelNameSingle | lower }} in cache */

            $this -> cache[ ${{ modelNameSingle | lower }}[ 'id' ] ] = ${{ modelNameSingle | lower }}; }

        /** Return {{ modelName | lower }} */

        return $id ? ( ${{ modelName | lower }}[ 0 ] ?? null ) : ${{ modelName | lower }}; }
    
    /**
     * Format data from user input to parsed {{ modelName }} model
     *
     * @param array $data
     * @return void
     */

    private function formatData( array $data ): array {

        /** Make lowercase keys */

        $data = array_change_key_case( $data, CASE_LOWER );

        /** Default required fields */

        $data[ 'someModel' ] = Session::get( 'user' );
        $data[ 'someDatetime' ] = ( new Moment ) -> format();

        /** Return formatted model */

        return array_change_key_case( $data, CASE_LOWER ); }

    /**
     * Put {{ modelNameSingle }}
     * @param  string   $id {{ modelName }} ID hash
     * @return array    {{ modelName }}(s)
     */
    
    public function put( string $id, ?array $data = null ): array {

        /** Format data */

        $data = $this -> formatData( $data ? $data : Request::data() );

        /** Execute */

        return ( new Sql( '{{ database }}' ) )

            -> update( '{{ modelName | lower }}', $data )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Post {{ modelNameSingle }}
     * @param  string   $id {{ modelName }} ID hash
     * @return array    {{ modelName }}(s)
     */
    
    public function post( ?array $data = null ): array {

        /** Format data */

        $data = $this -> formatData( $data ? $data : Request::data() );

        /** Execute */

        return ( new Sql( '{{ database }}' ) )

            -> insert( '{{ modelName | lower }}', $data )
            -> execute( ); }

    /**
     * Delete {{ modelNameSingle }}
     * @param  string   $id {{ modelName }} ID hash
     * @return array    {{ modelName }}(s)
     */
    
    public function delete( string $id ): array {

        return ( new Sql( '{{ database }}' ) )

            -> delete( '{{ modelName | lower }}' )
            -> where( 'id:' . $id )
            -> execute( ); }   

}