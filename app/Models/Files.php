<?php

/**
 * 
 */

namespace CC\Api\Models;

    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Response;
    use CC\Api\Authorization;
    use CC\Api\Cryptography;
    use CC\Api\Session;
    use CC\Api\FileSystem;

class Files {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Files';
	public static $nameSingle = 'File';

	/** ORM */

	public static $database = 'local';
	public static $table = 'files';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'hash', 'readonly' => true ],
        'name' => [ 'column' => 'name', 'datatype' => 'string', 'required' => true ],
        'extension' => [ 'column' => 'extension', 'datatype' => 'string' ],
        'size' => [ 'column' => 'size', 'datatype' => 'int', 'required' => true ],
        'createdAt' => [ 'column' => 'createdAt', 'datatype' => 'datetime' ],
        'createdBy' => [ 'column' => 'createdBy', 'datatype' => 'hash', 'model' => 'users' ],
        'organization' => [ 'column' => 'organization', 'datatype' => 'hash', 'model' => 'organizations' ],
        'scope' => [ 'column' => 'scope', 'datatype' => 'string' ],
        'scopeId' => [ 'column' => 'scopeId', 'datatype' => 'hash' ],
        'token' => [ 'column' => 'token', 'datatype' => 'string' ] ];

    /** Construct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/files' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/files/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order' ] ] ); }],
            'POST/files' => [ 'roles' => 'user', 'write' => function(){ return $this -> post(); }],
            'POST/files/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/files/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

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

        /** Convert 'id' to options */

        $options = is_array( $id ) ? $id : $options;
        $id = is_array( $id ) ? null : $id;

        /** Create WHERE clause */

        $where = 'id' . ( $id ? ':' . $id : '!null' ) . ( isset( $options[ 'where' ] ) ? ' and ( ' . $options[ 'where' ] . ' )' : '' );
        $where .= Authorization::user( 'administrator' ) ? '' : ' and organization:' . Session::get( 'organization' );

        /** Get from cache or database */

        $files = $id && isset( $this -> cache[ $id ] ) ?

            [ $this -> cache[ $id ] ] :

        ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'files', $options[ 'select' ] ?? null )
            -> where( $where )
            -> order( $options[ 'order' ] ?? 'name' )
            -> execute( );

        /** Eumerate */

        foreach( $files as &$file ){

            /** Add model to cache */

            $this -> cache[ $file[ 'id' ] ] = $file; }

        /** Return */

        return $id ? ( $files[ 0 ] ?? null ) : $files; }

    /**
     * Download file
     * @param  string $token File token
     * @return data        Filedata
     */
    
    public function download( $token ){

        /** Get file */

        $file = ( new Sql( 'main' ) )

            -> select( 'files' )
            -> where( 'token:' . $token )
            -> execute( )[ 0 ] ?? new Error( 404 );

        /** Return attachment */

        return [ 'name' => $file[ 'id' ] . ( $file[ 'extension' ] ? '.' . $file[ 'extension' ] : '' ), 'disposition' => 'attachment' ]; }

    /**
     * format data before PUT/POST
     *
     * @param array $data
     * @return array
     */

    public function formatData( array $data ): array {

        /** Make lowercase keys */

        $data = array_change_key_case( $data, CASE_LOWER );
        $data[ 'createdBy' ] = Session::get( 'user' );

        /** Organization */

        $data[ 'organization' ] =  Authorization::user( 'administrator' ) ? ( $data[ 'organization' ] ?? Session::get( 'organization' ) ) : Session::get( 'organization' );

        /** Return formatted model */

        return array_change_key_case( $data, CASE_LOWER ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function put( $id, $data = null ){

        /** Parse data */

        $data = $this -> formatData( $data ?? Request::data() );

        /** Get file */

        $file = ( new Sql( 'main', false ) )

            -> select( 'files' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? new Error( 404 );

        /** Get uploads */

        $newFile = ( new FileSystem\Uploads ) -> get()[ 0 ] ?? null; if( $newFile ){

            /** Generate specific data */

            $data[ 'name' ] = $newFile[ 'name' ];
            $data[ 'extension' ] = preg_match( '/\.([a-z0-9]+)$/', $newFile[ 'name' ], $m ) ? strtolower( $m[ 1 ] ) : '';
            $data[ 'size' ] = $newFile[ 'size' ];

             /** Remove file from storage */

            ( new FileSystem\Storage ) -> delete(

                $file[ 'id' ] . ( $data[ 'extension' ] ? '.' . $data[ 'extension' ] : '' ) );

            /** Store new file */

            ( new FileSystem\Storage ) -> write(

                $file[ 'id' ] . ( $data[ 'extension' ] ? '.' . $data[ 'extension' ] : '' ),
                @file_get_contents( $newFile[ 'tmp_name' ] ), [], true ); }

        /** Return */

        return ( new Sql( 'main' ) )

            -> update( 'files', $data )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function post( $data = null, $uploads = null ){

        /** Parse data */

        $data = $this -> formatData( $data ?? Request::data() );

        /** Enumerate uploads */

        $files = []; foreach( $uploads ? $uploads : ( new FileSystem\Uploads ) -> get() as $file ){

            /** Generate specific data */

            $data[ 'name' ] = $file[ 'name' ];
            $data[ 'extension' ] = preg_match( '/\.([a-z0-9]+)$/', $file[ 'name' ], $m ) ? strtolower( $m[ 1 ] ) : '';
            $data[ 'size' ] = $file[ 'size' ];
            $data[ 'token' ] = Cryptography::uuid();

            /** Write file */

            $file[ 'id' ] = ( new Sql( 'main' ) )

                -> insert( 'files', $data )
                -> execute( )[ 'id' ];

            /** Store file */

            ( new FileSystem\Storage ) -> write(

                $file[ 'id' ] . ( $data[ 'extension' ] ? '.' . $data[ 'extension' ] : '' ),
                file_get_contents( $file[ 'tmp_name' ] ), [], false );

            /** Remember */

            $files[] = array_merge( $file, $data ); }

        /** Return */

        return $files; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function delete( $id ){

        /** Get file */

        $file = ( new Sql( 'main', false ) )

            -> select( 'files' )
            -> where( 'id:' . $id )
            -> execute( )[ 0 ] ?? new Error( 404 );

         /** Remove file from storage */

        ( new FileSystem\Storage ) -> delete(

            $file[ 'id' ] . ( $file[ 'extension' ] ? '.' . $file[ 'extension' ] : '' ) );

        /** Delete SQL */

        return ( new Sql( 'main' ) )

            -> delete( 'files' )
            -> where( 'id:' . $id )
            -> execute( ); }

}

?>