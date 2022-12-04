<?php

/**
 * 
 */

namespace CC\Api\Models;

abstract class Model {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'Resources';
	public static $nameSingle = 'Resource';

	/** ORM */

	public static $database = 'local';
	public static $table = 'resources';
    public static $key = 'id';
    public static $parent = null;
	public static $fields = [];

    /** Contruct */

    public function __construct( $options = null ){
    
        return $this; }

    /** Cache */

    private $options = [];
    private $cache = [];

    /**
     * Get all
     * @param  string   $id Group ID hash
     * @return array    Group(s)
     */
    
    abstract public function get( $id = null, $options = null ) : array;
    abstract public function put( string $id, $options = null ) : array;
    abstract public function post( $options = null ) : array;
    abstract public function delete( string $id, $options = null ) : array;
    
    /**
     * Parse option parameter
     *
     * @param array $options
     * @param string $key
     * @return void
     */

    public function setOptions( $options = [], $defaults = [] ){

        $this -> options = array_merge( $defaults, $options ); return $this; }

    public function getOption( string $key ){

        /** Enumerate options & match keys */

        foreach( $this -> options as $option => $value ){

            if( strtolower( $option ) === strtolower( $key ) ){

                return $value; } }
        
        /** Not found, return null */

        return null; }

}

?>