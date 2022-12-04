<?php

/**
 * 
 */

namespace CC\Api;

class Models {

    /** Cache */

    private static $classMap = [];

    /** Contruct */

    public function __construct(){

        /** Return */

        return $this; }

    /**
     * Check if model exists
     * @param  string $modelName Nme
     * @return boolean        
     */
    
    public static function exists( string $modelName ){

        /** Merge default namespace with custom namespaces */

        $modelsSources = self::sources();

        /** Create classMap */

        if( empty( self::$classMap ) ){

            foreach( $modelsSources as $directory => $namespace ){

                foreach( scandir( $directory ) as $modelFile ){

                    if( preg_match( '/^(.+)\.php/i', $modelFile, $match ) ){

                        self::$classMap[ strtolower( $match[ 1 ] ) ] = $namespace . $match[ 1 ]; } } } }

        /** Find classname in multiple models sources */

        if( isset( self::$classMap[ strtolower( $modelName ) ] ) ){

            return self::$classMap[ strtolower( $modelName ) ]; }
        
        /** Return false */
            
        return false; }
    
    /**
     * Get model or die
     * @param  string  $modelName Name
     * @param  boolean $softFail  Return false or error
     * @return object             Model
     */
    
    public static function get( string $modelName, $softFail = false ){

        /** Check if class exists */

        $className = self::exists( $modelName ); if( ! $className ){

            return $softFail ? false : new Error( sprintf( _( 'Model "%s" does not exist' ), $modelName ), 400 ); }

        /** Return model instance */

        return new $className; }

    /**
     * Return model sources
     *
     * @return array
     */

    public static function sources(): array {
    
        return $modelsSources = array_merge([ __DIR__ . '/Models' => __NAMESPACE__ . '\Models\\' ], Options::set( 'models.directories' ) ?? [] ); }

    /**
     * Describe model
     *
     * @param string $modelName
     * @return void
     */
     
    public static function describe( string $modelName ){

        /** Check if model instance exists */
        
        $Model = self::get( $modelName );

        /** Return model description */

        return [ 

            'name' => $Model::$name ?? '',
            'nameSingle' => $Model::$nameSingle ?? '',
            'database' => $Model::$database ?? '',
            'table' => $Model::$table ?? '',
            'key' => $Model::$key ?? '',
            'fields' => $Model::$fields ?? [],
            'routes' => $Model::$routes ?? [] ]; }

    /**
     *
     * Parent methods
     * 
     */
    
    /**
     * Get joins from model
     * @param  string $join    Join Key
     * @param  array  $options Model options (optional)
     * @return boolean          True/false
     */
    
    public static function requireJoin( $join, $options = [] ){

        return preg_match( '/(^|,)(hosts|all)(,|$)/i', $options[ 'join' ] ?? '' ) || Request::join( 'all' ) || Request::join( 'hosts' ); }
    
    /**
     * Get parents of model
     * @param  integer $id             ID of child
     * @param  array  $options        Options
     * @param  array  $cache          
     * @param  array  $loopProtection 
     * @return array/object                 Child + Parents OR flat array
     */
    
    public static function getParents( $id, $options = [], $cache = [], $loopProtection = [] ){

        /** Parse options */

        $options = array_merge([

            'database' => 'main',
            'model' => 'void',
            'key' => 'id',
            'parent' => 'parent',
            'multiDimensional' => true ], $options );

        /** Validate */

        if( ! $id || in_array( $id, $loopProtection ) ){

            return $cache; }

        /** Get organization */

        $loopProtection[] = $id; $item = ( new Sql( $options[ 'database' ] ) )

            -> select( $options[ 'model' ] )
            -> where( $options[ 'key' ] . ':' . $id )
            -> execute( )[ 0 ] ?? false;

        if( ! $item || $id === $item[ $options[ 'parent' ] ] ){

            return $cache; }

        /** Append to parent */

        if( $options[ 'multiDimensional' ] ){

            if( count( $loopProtection ) === 1 ){

                $item = $item[ $options[ 'parent' ] ] ? self::getParents( $item[ $options[ 'parent' ] ], $options, $cache, $loopProtection ) : null; }

            else {

                $item[ 'parents' ] = $item[ $options[ 'parent' ] ] ? self::getParents( $item[ $options[ 'parent' ] ], $options, $cache, $loopProtection ) : null; }}

        else {

            return self::getParents( $item[ $options[ 'parent' ] ], $options, array_merge( $cache, count( $loopProtection ) === 1 ? [] : [ $item ] ), $loopProtection ); }

        /** Return parent or fetch parent */

    return $item; }

    /**
     * Get all children of organization
     * @param  string $id             Organization ID
     * @param  array  $cache       Cache
     * @param  array  $loopProtection Cache
     * @return array                 Organizations
     */
    
    public static function getChildren( $id, $options = [], $cache = [], $loopProtection = [] ){

        /** Parse options */

        $options = array_merge([

            'database' => 'main',
            'model' => 'void',
            'parent' => 'parent',
            'key' => 'id',
            'multiDimensional' => true ], $options );

        /** Validate */

        if( ! $id || in_array( $id, $loopProtection ) ){

            return $cache; }

        /** Get items */

        $loopProtection[] = $id; $items = ( new Sql( $options[ 'database' ] ) )

            -> select( $options[ 'model' ] )
            -> where( $options[ 'parent' ] . ':' . $id )
            -> execute( );

        if( ! count( $items ) ){

            return $cache; }

        /** Append to children */

        $flat = []; foreach( $items as &$item ){

            if( $options[ 'multiDimensional' ] ){

                $item[ 'children' ] = self::getChildren( $item[ $options[ 'key' ] ], $options, $cache, $loopProtection ); }

            else {

                $flat = array_merge( self::getChildren( $item[ $options[ 'key' ] ], $options, $cache, $loopProtection ) ); }}

        /** Return children or fetch children */

    return $options[ 'multiDimensional' ] ? $items : array_merge( $items, $flat ); }

    /**
     * Get all children of organization
     * @param  string $id             Organization ID
     * @param  array  $cache       Cache
     * @param  array  $loopProtection Cache
     * @return array                 Organizations
     */
    
    public static function getSiblings( $id, $parent, $options = [] ){

        /** Parse options */

        $options = array_merge([

            'database' => 'main',
            'model' => 'void',
            'parent' => 'parent',
            'key' => 'id' ], $options );

        /** Validate */

        if( ! $id ){

            return []; }

        /** Get items */

        return ( new Sql( $options[ 'database' ] ) )

            -> select( $options[ 'model' ] )
            -> where( $options[ 'key' ] . '!' . $id . ' and ' . $options[ 'parent' ] . ':' . ( $parent ? $parent : 'null' ) )
            -> execute( ); }

}

?>