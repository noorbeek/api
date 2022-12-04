<?php

/**
 * 
 */

namespace CC\Api\Models;

    use CC\Api\Models;
    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;

class Notifications {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Notifications';
	public static $nameSingle = 'Notification';

	/** ORM */

	public static $database = 'local';
	public static $table = 'notifications';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'integer', 'readonly' => true ],
        'scope' => [ 'column' => 'scope', 'datatype' => 'string' ],
        'scopeId' => [ 'column' => 'scopeId', 'datatype' => 'integer' ],
        'from' => [ 'column' => 'from', 'datatype' => 'string' ],
        'to' => [ 'column' => 'to', 'datatype' => 'array' ],
        'cc' => [ 'column' => 'cc', 'datatype' => 'array' ],
        'bcc' => [ 'column' => 'bcc', 'datatype' => 'array' ],
        'subject' => [ 'column' => 'subject', 'datatype' => 'string' ],
        'description' => [ 'column' => 'description', 'datatype' => 'string' ],
        'action' => [ 'column' => 'action', 'datatype' => 'string' ],
        'actionText' => [ 'column' => 'actionText', 'datatype' => 'string' ],
        'body' => [ 'column' => 'body' ],
        'attachments' => [ 'column' => 'attachments', 'datatype' => 'array' ],
        'priority' => [ 'column' => 'priority', 'datatype' => 'switch', 'switch' => [

            3 => [ 'value' => 'Low', 'default' => true ],
            2 => [ 'value' => 'Medium' ],
            1 => [ 'value' => 'High' ] ]],

        'callback' => [ 'column' => 'callback', 'datatype' => 'string' ],
        'sendAt' => [ 'column' => 'sendAt', 'datatype' => 'datetime' ],
        'mediums' => [ 'column' => 'mediums', 'datatype' => 'array' ] ];

    /** Construct */

    public function __construct(){

        /** Set routes */

        if( empty( self::$routes ) || empty( self::$routes ) ){ self::$routes = [

            'GET/notifications' => [ 'roles' => 'user', 'write' => function(){ return $this -> get(null, [ 'allow' => true ]); }],
            'GET/notifications/:id' => [ 'roles' => 'user', 'write' => function( $id ){ return $this -> get( $id, [ 'allow' => [ 'join', 'order' ] ] ); }],
            'POST/notifications' => [ 'roles' => 'w:administrator', 'write' => function(){ return $this -> post(); }],
            'PUT/notifications/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> put( $id ); }],
            'DELETE/notifications/:id' => [ 'roles' => 'w:administrator', 'write' => function( $id ){ return $this -> delete( $id ); }] ]; }

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

        /** Build WHERE clause */

        $where = 'id' . ( $id ? ':' . $id : '!null' );
        $where .= isset( $options[ 'where' ] ) ? ' and ( ' . $options[ 'where' ] . ' )' : '';

        /** Convert 'id' to options */

        $options = is_array( $id ) ? $id : $options;
        $id = is_array( $id ) ? null : $id;

        /** Get from cache or database */

        $notifications = $id && isset( $this -> cache[ $id ] ) ?

            [ $this -> cache[ $id ] ] :

        ( new Sql( 'main' ) )

            -> allow( $options[ 'allow' ] ?? false )
            -> select( 'notifications', $options[ 'select' ] ?? null )
            -> where( $where )
            -> order( $options[ 'order' ] ?? 'sendAt desc' )
            -> execute( );

        /** Eumerate */

        foreach( $notifications as &$notification ){

            /** Add model to cache */

            $this -> cache[ $notification[ 'id' ] ] = $notification; }

        /** Return */

        return $id ? ( $notifications[ 0 ] ?? null ) : $notifications; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function put( string $id, $data = null ){

        /** Check if record exists */

        $notification = $this -> get( $id ) ?? new Error( 404 );

        /** Parse data */

        $data = $this -> parse( array_merge([ 'ip' => $notification[ 'ip' ], 'cidr' => $notification[ 'cidr' ] ], $data ? $data : Request::data() ) );

        /** Return */

        $result = ( new Sql( 'main' ) )

            -> update( 'notifications', $data )
            -> where( 'id:' . $id )
            -> execute( );

        /** Return */

        return $result; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function post( $data = null ){

        /** Parse data */

        $data = $this -> parse( $data ? $data : Request::data() );

        /** Write data */

        $result = ( new Sql( 'main' ) )

            -> insert( 'notifications', $data )
            -> execute( );

        /** Return */

        return $result; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function delete( string $id ){

        /** Delete addresses */

        ( new ipAddresses )

            -> deleteNetwork( $id );

        /** Delete network */

        return ( new Sql( 'main' ) )

            -> delete( 'notifications' )
            -> where( 'id:' . $id )
            -> execute( ); }

    /**
     * Parse IP data
     * @param  array $data Optional data override
     * @return array       Parsed data
     */
    
    public function parse( $data ){

        $data = '<p><label>Joehoe</label><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAABvAAAAbwHxotxDAAAAGXRFWHRTb2Z0d2FyZQB3d3cuaW5rc2NhcGUub3Jnm+48GgAAAF1QTFRF/////4AA/3QA/48A/78A/5YA/3EA/3MA/3QA/8sA/8YA/3IA/8wA/5IA/5QA/80A/88A/3QA/3MA/3UA/84A/3MA/88A/3MA/3QA/3MA/3QA/5MA/5QA/9AA/88AI+xdWQAAAB50Uk5TAAILEBAREhQhIiQmKC8yOEBcXV5eX2V8fX5/k5j3lHS54gAAAIlJREFUGFdlT+0OgyAQq3wMEBRwDuZU3v8xx4csJuuPS665XlugwVrc8YBzeVyYOCJhjGzgUyX4QaU3ZtH04O2E7hhDGLHTpt/0sJ4pnesgY/1DXirvmVFPUvwcMyFVBMOc/SeyZOkSXyQQH432FPItuq0KQXXbHszLHmy+okfwudcRpZzAHb/6XyVaCaxfQ/SAAAAAAElFTkSuQmApi"></p>';

        /** Data-URL regex */

        $regex = '/"data:([a-z\/]+);([a-z0-9]+),([^"]+)"/i';

        /** Find all data URI's */

        preg_match_all( $regex, $data, $matches );
        list( $string, $mime, $encoding, $data ) = $matches;

        /** Upload file & replace URI with embed code */

        $rsp = []; for( $i = 0; $i < count( $mime ?? [] ); $i++ ){

            /** Create file */

            $rsp[] = [ 'mime' => $mime[ $i ], 'encoding' => $encoding[ $i ], 'data' => $data[ $i ] ];

            /** Get file extension */

            if( ! preg_match( '/(png|jpg|jpeg|svg|gif)/i', $mime[ $i ], $m ) ){

                continue; } $extension = $m[ 0 ];

            /** Write file */

            $tmp = tempnam( sys_get_temp_dir(), 'file' ); @file_put_contents( $tmp, base64_decode( $data[ $i ] ) );

            /** Set files */

            $file = ( new Models\Files ) -> post([

                'scope' => 'notifications',
                'scopeId' => 0

            ],[[

                'name' => 'file_' . $i . '.' . $extension,
                'type' => $mime[ $i ],
                'tmp_name' => $tmp,
                'error' => 0,
                'size' => filesize( $tmp ) ]] )[ 0 ];

            /** Replace data by file embed */

            $rsp[] = [ $file, preg_replace( $regex, '"file:' . $file[ 'id' ] . '"', $data, 1 ) ]; }

        /** Return */

        return $rsp; }

}

?>